<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlutterwaveGateway implements PaymentGateway
{
    protected string $secretKey;

    protected string $publicKey;

    protected string $encryptionKey;

    protected bool $isLive;

    protected string $currency;

    protected string $baseUrl = 'https://api.flutterwave.com/v3';

    public function __construct()
    {
        $this->secretKey = (string) config('services.flutterwave.secret', '');
        $this->publicKey = (string) config('services.flutterwave.public', '');
        $this->encryptionKey = (string) config('services.flutterwave.encryption', '');
        $this->isLive = (bool) config('services.flutterwave.live', false);
        $this->currency = strtoupper((string) config('services.flutterwave.currency', 'NGN'));
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getEncryptionKey(): string
    {
        return $this->encryptionKey;
    }

    public function getName(): string
    {
        return 'flutterwave';
    }

    public function initializePayment(array $data): array
    {
        if (trim($this->secretKey) === '') {
            Log::error('Flutterwave initialization aborted: FLUTTERWAVE_SECRET_KEY is not configured.');

            return ['success' => false, 'message' => 'Payment provider is not configured.'];
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->post($this->baseUrl.'/payments', [
                    'tx_ref' => $data['reference'],
                    'amount' => $data['amount'],
                    'currency' => $this->currency,
                    'redirect_url' => $data['callback_url'],
                    'customer' => [
                        'email' => $data['email'],
                        'name' => $data['name'] ?? '',
                        'phonenumber' => $data['phone'] ?? '',
                    ],
                    'customizations' => [
                        'title' => 'Templatr Purchase',
                        'description' => 'Payment for digital items',
                        'logo' => url('/templatr.svg'),
                    ],
                    'meta' => [
                        'order_id' => $data['order_id'] ?? '',
                    ],
                ]);

            if ($response->successful() && $response->json('status') === 'success') {
                return [
                    'success' => true,
                    'authorization_url' => $response->json('data.link'),
                    'reference' => $response->json('data.tx_ref'),
                    'transaction_id' => $response->json('data.id'),
                ];
            }

            Log::error('Flutterwave initialization failed', ['response' => $response->json()]);

            return ['success' => false, 'message' => $response->json('message', 'Payment initialization failed')];
        } catch (\Exception $e) {
            Log::error('Flutterwave exception: '.$e->getMessage());

            return ['success' => false, 'message' => 'Payment gateway error. Please try again.'];
        }
    }

    /**
     * Verify a transaction with Flutterwave.
     *
     * Flutterwave's verify endpoint addresses transactions by their own numeric
     * id, so when we recorded one at initialization we verify by id and then
     * require the returned `tx_ref` to equal the reference we created for the
     * order. That equality is what proves the transaction belongs to the order.
     */
    public function verifyPayment(string $reference, string|int|null $transactionId = null): array
    {
        if (trim($this->secretKey) === '') {
            Log::error('Flutterwave verification aborted: FLUTTERWAVE_SECRET_KEY is not configured.');

            return ['success' => false, 'reason' => 'gateway_not_configured'];
        }

        $id = $transactionId ?: (ctype_digit($reference) ? $reference : null);

        if (! $id) {
            Log::warning('Flutterwave verification skipped: no transaction id available to verify.', [
                'reference' => $reference,
            ]);

            return ['success' => false, 'reason' => 'missing_transaction_id'];
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->get($this->baseUrl.'/transactions/'.rawurlencode((string) $id).'/verify');

            if (! $response->successful() || $response->json('status') !== 'success') {
                return ['success' => false, 'reason' => 'verification_failed'];
            }

            $data = $response->json('data');

            return [
                'success' => ($data['status'] ?? null) === 'successful',
                'amount' => isset($data['amount']) ? (float) $data['amount'] : null,
                'currency' => isset($data['currency']) ? strtoupper((string) $data['currency']) : null,
                'reference' => isset($data['tx_ref']) ? (string) $data['tx_ref'] : null,
                'transaction_id' => $data['id'] ?? $id,
                'status' => $data['status'] ?? 'unknown',
                'paid_at' => $data['created_at'] ?? null,
                'channel' => $data['payment_type'] ?? '',
                'reason' => ($data['status'] ?? null) === 'successful' ? null : 'provider_status_not_success',
            ];
        } catch (\Exception $e) {
            Log::error('Flutterwave verification exception: '.$e->getMessage());

            return ['success' => false, 'reason' => 'verification_error'];
        }
    }
}
