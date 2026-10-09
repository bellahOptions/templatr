<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Payment\PaymentManager;
use App\Services\Payment\PaymentVerificationResult;
use App\Services\Payment\PaymentVerificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Inbound payment notifications.
 *
 * Every request must pass the provider's own authentication scheme before a
 * single byte of its payload is read as fact: a valid HMAC signature for
 * Paystack, and the secret hash for Flutterwave. When the shared secret is
 * missing the endpoint fails closed (503) instead of trusting the caller.
 *
 * A verified notification is then handed to PaymentVerificationService, which
 * re-checks the reference, gateway, amount and currency against the order and
 * settles it exactly once.
 */
class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected PaymentVerificationService $verification,
    ) {}

    public function handle(Request $request, string $gateway): Response
    {
        $rawPayload = (string) $request->getContent();

        return match ($gateway) {
            'paystack' => $this->handlePaystack($request, $rawPayload),
            'flutterwave' => $this->handleFlutterwave($request, $rawPayload),
            default => response('Unsupported gateway', 400),
        };
    }

    protected function handlePaystack(Request $request, string $rawPayload): Response
    {
        $secret = $this->paymentManager->webhookSecret('paystack');

        if (trim($secret) === '') {
            Log::error('Paystack webhook rejected: PAYSTACK_SECRET_KEY is not configured.');

            return response('Webhook not configured', 503);
        }

        $signature = (string) $request->header('X-Paystack-Signature', '');
        $expectedSignature = hash_hmac('sha512', $rawPayload, $secret);

        if ($signature === '' || ! hash_equals($expectedSignature, $signature)) {
            Log::warning('Paystack webhook: invalid signature', ['ip' => $request->ip()]);

            return response('Unauthorized', 401);
        }

        $payload = json_decode($rawPayload, true) ?: [];
        $event = $payload['event'] ?? null;
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        // Only a successful charge may settle an order.
        if ($event !== 'charge.success' || ($data['status'] ?? null) !== 'success') {
            return response('OK', 200);
        }

        $reference = (string) ($data['reference'] ?? '');

        if ($reference === '') {
            Log::warning('Paystack webhook: charge.success without a reference');

            return response('OK', 200);
        }

        // Idempotency: Paystack retries, and the callback may race this request.
        $eventId = (string) ($data['id'] ?? hash('sha256', $rawPayload));

        if (! $this->verification->recordEvent('paystack', $eventId, $reference, null, $payload)) {
            return response('OK', 200);
        }

        $order = Order::where('payment_reference', $reference)->first();

        if (! $order) {
            Log::warning('Paystack webhook: unknown reference', ['reference' => $reference]);

            return response('OK', 200);
        }

        $result = $this->verification->settleFromVerification($order, 'paystack', [
            'success' => true,
            'amount' => isset($data['amount']) ? ((float) $data['amount']) / 100 : null,
            'currency' => isset($data['currency']) ? strtoupper((string) $data['currency']) : null,
            'reference' => $reference,
            'transaction_id' => $data['id'] ?? null,
            'status' => 'success',
        ], 'webhook');

        return $this->respond($result);
    }

    protected function handleFlutterwave(Request $request, string $rawPayload): Response
    {
        $secretHash = $this->paymentManager->webhookSecret('flutterwave');

        if (trim($secretHash) === '') {
            Log::error('Flutterwave webhook rejected: FLW_SECRET_HASH is not configured.');

            return response('Webhook not configured', 503);
        }

        $signature = (string) $request->header('verif-hash', '');

        if ($signature === '' || ! hash_equals($secretHash, $signature)) {
            Log::warning('Flutterwave webhook: invalid signature', ['ip' => $request->ip()]);

            return response('Unauthorized', 401);
        }

        $payload = json_decode($rawPayload, true) ?: [];
        $event = $payload['event'] ?? null;
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        if ($event !== 'charge.completed' || ($data['status'] ?? null) !== 'successful') {
            return response('OK', 200);
        }

        $reference = (string) ($data['tx_ref'] ?? '');

        if ($reference === '') {
            Log::warning('Flutterwave webhook: charge.completed without a tx_ref');

            return response('OK', 200);
        }

        $eventId = (string) ($data['id'] ?? hash('sha256', $rawPayload));

        if (! $this->verification->recordEvent('flutterwave', $eventId, $reference, null, $payload)) {
            return response('OK', 200);
        }

        $order = Order::where('payment_reference', $reference)->first();

        if (! $order) {
            Log::warning('Flutterwave webhook: unknown reference', ['reference' => $reference]);

            return response('OK', 200);
        }

        $result = $this->verification->settleFromVerification($order, 'flutterwave', [
            'success' => true,
            'amount' => isset($data['amount']) ? (float) $data['amount'] : null,
            'currency' => isset($data['currency']) ? strtoupper((string) $data['currency']) : null,
            'reference' => $reference,
            'transaction_id' => $data['id'] ?? null,
            'status' => 'successful',
        ], 'webhook');

        return $this->respond($result);
    }

    /**
     * Ack a correctly-signed notification even when the payload is rejected:
     * retrying will never change the outcome, and the rejection is logged.
     * The order itself is simply left unpaid.
     */
    protected function respond(PaymentVerificationResult $result): Response
    {
        if (! $result->verified) {
            Log::warning('Payment notification rejected', $result->toArray());
        }

        return response('OK', 200);
    }
}
