<?php

namespace App\Services\Payment;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Services\Order\OrderFulfillmentService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Single gate through which an order can become paid.
 *
 * Responsibilities:
 *  - validate a provider transaction (gateway, reference, amount, currency,
 *    order ownership and current state) before any state change;
 *  - perform the state change atomically under a row lock, so concurrent
 *    webhook + callback deliveries settle an order exactly once;
 *  - record an idempotency row per notification, so replays are no-ops;
 *  - dispatch fulfilment only after the transaction has committed.
 *
 * Nothing here trusts a client-supplied value: every input is either read from
 * the database or produced by a provider API / signature-verified payload.
 */
class PaymentVerificationService
{
    /**
     * Tolerance when comparing a provider amount with the recorded order total.
     * Covers sub-unit rounding between a gateway's minor units and our decimal.
     */
    public const AMOUNT_TOLERANCE = 0.01;

    public function __construct(
        protected PaymentManager $paymentManager,
        protected OrderFulfillmentService $fulfillmentService,
    ) {}

    /**
     * Settle an order from an already-verified provider transaction.
     *
     * @param  array<string, mixed>  $verification  normalized gateway verification payload
     * @param  string  $source  "webhook" or "callback", for logging/audit only
     */
    public function settleFromVerification(Order $order, string $gateway, array $verification, string $source): PaymentVerificationResult
    {
        $check = $this->validateTransaction($order, $gateway, $verification);

        if (! $check->verified) {
            Log::warning('Payment verification rejected', $check->toArray() + ['source' => $source]);

            return $check;
        }

        return $this->markPaid($order, $verification, $source);
    }

    /**
     * Verify a provider reference directly with the provider, then settle.
     *
     * Used by the browser callback path. The reference must already be recorded
     * against the order; a client cannot introduce a new one.
     */
    public function settleFromProvider(Order $order, string $source): PaymentVerificationResult
    {
        if (! $order->payment_reference) {
            return PaymentVerificationResult::rejected('order_has_no_reference', $order);
        }

        $gateway = $this->resolveGateway($order);

        if ($gateway === null) {
            return PaymentVerificationResult::rejected('missing_gateway_credentials', $order);
        }

        try {
            $verification = $gateway->verifyPayment($order->payment_reference, $order->gateway_transaction_id);
        } catch (\Throwable $e) {
            Log::error('Payment verification with provider failed', [
                'order_id' => $order->id,
                'gateway' => $gateway->getName(),
                'error' => $e->getMessage(),
            ]);

            return PaymentVerificationResult::rejected('provider_unreachable', $order);
        }

        if (! ($verification['success'] ?? false)) {
            return PaymentVerificationResult::rejected(
                (string) ($verification['reason'] ?? 'provider_reported_not_successful'),
                $order,
                ['provider_status' => $verification['status'] ?? null],
            );
        }

        return $this->settleFromVerification($order, $gateway->getName(), $verification, $source);
    }

    /**
     * Validate a verified transaction against the recorded order.
     *
     * Fails closed on every mismatch: missing gateway credentials, a reference
     * that does not belong to this order, a different gateway, an amount or
     * currency mismatch, or an order that is already refunded/cancelled.
     */
    public function validateTransaction(Order $order, string $gateway, array $verification): PaymentVerificationResult
    {
        if (! $this->gatewayIsConfigured($gateway)) {
            return PaymentVerificationResult::rejected('gateway_not_configured', $order, ['gateway' => $gateway]);
        }

        // A reference created for one gateway must never be settled by another.
        $expectedGateway = $order->gateway ?: $order->payment_method;

        if ($expectedGateway && $expectedGateway !== $gateway) {
            return PaymentVerificationResult::rejected('gateway_mismatch', $order, [
                'expected' => $expectedGateway,
                'received' => $gateway,
            ]);
        }

        $orderReference = (string) $order->payment_reference;
        $providerReference = (string) ($verification['reference'] ?? '');

        if ($orderReference === '') {
            return PaymentVerificationResult::rejected('order_has_no_reference', $order);
        }

        if ($providerReference !== '' && ! hash_equals($orderReference, $providerReference)) {
            return PaymentVerificationResult::rejected('reference_mismatch', $order, [
                'expected' => $orderReference,
                'received' => $providerReference,
            ]);
        }

        $orderStatus = PaymentStatus::tryFrom((string) $order->payment_status);

        if ($orderStatus === null) {
            return PaymentVerificationResult::rejected('unknown_payment_status', $order, [
                'payment_status' => $order->payment_status,
            ]);
        }

        if ($orderStatus === PaymentStatus::Refunded) {
            return PaymentVerificationResult::rejected('order_already_refunded', $order);
        }

        if (OrderStatus::tryFrom((string) $order->status) === OrderStatus::Cancelled) {
            return PaymentVerificationResult::rejected('order_cancelled', $order);
        }

        // Amount.
        if (! array_key_exists('amount', $verification) || $verification['amount'] === null) {
            return PaymentVerificationResult::rejected('provider_amount_missing', $order);
        }

        $providerAmount = round((float) $verification['amount'], 2);
        $expectedAmount = round((float) $order->total_amount, 2);

        if (abs($providerAmount - $expectedAmount) > self::AMOUNT_TOLERANCE) {
            return PaymentVerificationResult::rejected('amount_mismatch', $order, [
                'expected' => $expectedAmount,
                'received' => $providerAmount,
            ]);
        }

        // Currency. Legacy orders created before this column existed are null and
        // are only checked when the provider tells us the currency.
        $providerCurrency = isset($verification['currency']) ? strtoupper((string) $verification['currency']) : null;
        $expectedCurrency = $order->currency ? strtoupper((string) $order->currency) : null;

        if ($providerCurrency !== null && $expectedCurrency !== null && $providerCurrency !== $expectedCurrency) {
            return PaymentVerificationResult::rejected('currency_mismatch', $order, [
                'expected' => $expectedCurrency,
                'received' => $providerCurrency,
            ]);
        }

        return PaymentVerificationResult::verified($order, [
            'amount' => $providerAmount,
            'currency' => $providerCurrency ?? $expectedCurrency,
            'provider_reference' => $providerReference,
            'gateway' => $gateway,
        ]);
    }

    /**
     * Record an inbound notification exactly once.
     *
     * Returns false when this (gateway, event_id) pair has already been seen,
     * which is the signal to answer the provider with the recorded outcome
     * rather than process the payment again.
     */
    public function recordEvent(string $gateway, string $eventId, ?string $reference, ?int $orderId, array $payload): bool
    {
        try {
            PaymentEvent::create([
                'order_id' => $orderId,
                'gateway' => $gateway,
                'event_id' => $eventId,
                'reference' => $reference,
                'status' => 'received',
                'payload' => $payload,
            ]);

            return true;
        } catch (QueryException $e) {
            if ($this->isUniqueViolation($e)) {
                Log::info('Duplicate payment notification ignored', [
                    'gateway' => $gateway,
                    'event_id' => $eventId,
                ]);

                return false;
            }

            throw $e;
        }
    }

    /**
     * Atomic paid transition.
     *
     * The row lock serialises concurrent webhook/callback deliveries; the
     * in-transaction state check makes a second settle a no-op. Fulfilment is
     * dispatched after commit so a rollback can never credit an author for an
     * order that was not marked paid.
     */
    protected function markPaid(Order $order, array $verification, string $source): PaymentVerificationResult
    {
        $alreadyPaid = false;

        DB::transaction(function () use ($order, $verification, $source, &$alreadyPaid): void {
            /** @var Order $locked */
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->payment_status === PaymentStatus::Paid->value) {
                $alreadyPaid = true;

                return;
            }

            $current = PaymentStatus::tryFrom((string) $locked->payment_status) ?? PaymentStatus::Unpaid;

            if (! $current->canTransitionTo(PaymentStatus::Paid)) {
                return;
            }

            $locked->forceFill([
                'payment_status' => PaymentStatus::Paid->value,
                'status' => OrderStatus::Processing->value,
                'paid_at' => $locked->paid_at ?? now(),
                'gateway' => $verification['gateway'] ?? $locked->gateway ?? $locked->payment_method,
                'gateway_transaction_id' => $verification['transaction_id'] ?? $locked->gateway_transaction_id,
                'currency' => $locked->currency ?: ($verification['currency'] ?? null),
            ])->save();
        });

        $order->refresh();

        Log::info('Order settled as paid', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'gateway' => $order->gateway,
            'source' => $source,
            'duplicate' => $alreadyPaid,
        ]);

        // Idempotent: fulfilment itself is guarded per order item.
        $this->fulfillmentService->dispatch($order);

        return PaymentVerificationResult::verified($order, $verification + ['already_paid' => $alreadyPaid]);
    }

    /**
     * Resolve and return the configured gateway a request should be verified
     * against. Returns null when the provider is not configured — callers must
     * fail closed.
     */
    public function resolveGateway(Order $order): ?PaymentGateway
    {
        $name = $order->gateway ?: $order->payment_method;

        if (! $this->gatewayIsConfigured((string) $name)) {
            return null;
        }

        return $this->paymentManager->gateway($name);
    }

    public function gatewayIsConfigured(string $name): bool
    {
        if ($name === '' || ! $this->paymentManager->hasGateway($name)) {
            return false;
        }

        return $this->paymentManager->isConfigured($name);
    }

    protected function isUniqueViolation(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? null;

        return in_array($sqlState, ['23000', '23505'], true);
    }
}
