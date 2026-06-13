<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Order\OrderFulfillmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(protected OrderFulfillmentService $fulfillmentService) {}

    public function handle(Request $request, string $gateway): Response
    {
        $rawPayload = $request->getContent();

        return match ($gateway) {
            'paystack' => $this->handlePaystack($request, $rawPayload),
            'flutterwave' => $this->handleFlutterwave($request, $rawPayload),
            default => response('Unsupported gateway', 400),
        };
    }

    protected function handlePaystack(Request $request, string $rawPayload): Response
    {
        $secretKey = config('services.paystack.secret', env('PAYSTACK_SECRET_KEY', ''));
        $signature = $request->header('X-Paystack-Signature', '');
        $expectedSignature = hash_hmac('sha512', $rawPayload, $secretKey);

        if (! hash_equals($expectedSignature, $signature)) {
            Log::warning('Paystack webhook: invalid signature', ['ip' => $request->ip()]);

            return response('Unauthorized', 401);
        }

        $event = $request->json('event');
        $data = $request->json('data');

        if ($event === 'charge.success' && ($data['status'] ?? '') === 'success') {
            $this->markOrderPaid(
                reference: $data['reference'],
                gateway: 'paystack',
                amount: ($data['amount'] ?? 0) / 100,
            );
        }

        return response('OK', 200);
    }

    protected function handleFlutterwave(Request $request, string $rawPayload): Response
    {
        $secretHash = env('FLW_SECRET_HASH', '');
        $signature = $request->header('verif-hash', '');

        if (empty($secretHash) || ! hash_equals($secretHash, $signature)) {
            Log::warning('Flutterwave webhook: invalid signature', ['ip' => $request->ip()]);

            return response('Unauthorized', 401);
        }

        $event = $request->json('event');
        $data = $request->json('data');

        if ($event === 'charge.completed' && ($data['status'] ?? '') === 'successful') {
            $this->markOrderPaid(
                reference: $data['tx_ref'],
                gateway: 'flutterwave',
                amount: $data['amount'] ?? 0,
            );
        }

        return response('OK', 200);
    }

    protected function markOrderPaid(string $reference, string $gateway, float $amount): void
    {
        $order = Order::where('payment_reference', $reference)->first();

        if (! $order) {
            Log::warning('Payment webhook received for unknown reference', [
                'reference' => $reference,
                'gateway' => $gateway,
                'amount' => $amount,
            ]);

            return;
        }

        if ($order->payment_status === 'paid') {
            return;
        }

        $expectedAmount = (float) $order->total_amount;
        if (abs($amount - $expectedAmount) > 0.01) {
            Log::warning('Payment webhook amount mismatch — possible fraud attempt', [
                'reference' => $reference,
                'gateway' => $gateway,
                'webhook_amount' => $amount,
                'order_amount' => $expectedAmount,
                'order_number' => $order->order_number,
            ]);

            return;
        }

        // Atomic update — guards against the callback arriving at the same time.
        $marked = Order::where('id', $order->id)
            ->where('payment_status', '!=', 'paid')
            ->update(['payment_status' => 'paid', 'status' => 'completed']);

        if (! $marked) {
            return;
        }

        Log::info("Order {$order->order_number} confirmed paid via {$gateway} webhook", [
            'reference' => $reference,
            'amount' => $amount,
        ]);

        $order->refresh();
        $this->fulfillmentService->fulfill($order);
    }
}
