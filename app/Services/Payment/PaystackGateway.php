<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackGateway implements PaymentGateway
{
    protected string $secretKey;

    protected string $publicKey;

    protected bool $isLive;

    protected string $splitCode;

    protected string $currency;

    public function __construct()
    {
        $this->secretKey = (string) config('services.paystack.secret', '');
        $this->publicKey = (string) config('services.paystack.public', '');
        $this->isLive = (bool) config('services.paystack.live', false);
        $this->splitCode = (string) config('services.paystack.split_code', '');
        $this->currency = strtoupper((string) config('services.paystack.currency', 'NGN'));
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getName(): string
    {
        return 'paystack';
    }

    public function initializePayment(array $data): array
    {
        // Fail closed: without a secret key we cannot create a transaction, and
        // must never fall through to a "successful" local path.
        if (trim($this->secretKey) === '') {
            Log::error('Paystack initialization aborted: PAYSTACK_SECRET_KEY is not configured.');

            return ['success' => false, 'message' => 'Payment provider is not configured.'];
        }

        $this->upsertCustomer($data['email'], $data['name'] ?? null, $data['phone'] ?? null);

        try {
            $customFields = [
                ['display_name' => 'Order', 'variable_name' => 'order_id', 'value' => $data['order_id'] ?? ''],
            ];

            if (! empty($data['phone'])) {
                $customFields[] = ['display_name' => 'Phone', 'variable_name' => 'phone', 'value' => $data['phone']];
            }

            $response = Http::withToken($this->secretKey)
                ->post('https://api.paystack.co/transaction/initialize', [
                    'email' => $data['email'],
                    'amount' => (int) round($data['amount'] * 100), // Paystack uses kobo
                    'currency' => $this->currency,
                    'reference' => $data['reference'],
                    'callback_url' => $data['callback_url'],
                    'metadata' => [
                        'order_id' => $data['order_id'] ?? null,
                        'custom_fields' => $customFields,
                    ],
                    ...($this->splitCode ? ['split_code' => $this->splitCode] : []),
                ]);

            if ($response->successful() && $response->json('status')) {
                return [
                    'success' => true,
                    'authorization_url' => $response->json('data.authorization_url'),
                    'reference' => $response->json('data.reference'),
                    'transaction_id' => $response->json('data.id'),
                    'access_code' => $response->json('data.access_code'),
                ];
            }

            Log::error('Paystack initialization failed', ['response' => $response->json()]);

            return ['success' => false, 'message' => $response->json('message', 'Payment initialization failed')];
        } catch (\Exception $e) {
            Log::error('Paystack exception: '.$e->getMessage());

            return ['success' => false, 'message' => 'Payment gateway error. Please try again.'];
        }
    }

    protected function upsertCustomer(string $email, ?string $name, ?string $phone): void
    {
        try {
            $nameParts = $name ? explode(' ', trim($name), 2) : [];
            $payload = array_filter([
                'email' => $email,
                'first_name' => $nameParts[0] ?? null,
                'last_name' => $nameParts[1] ?? null,
                'phone' => $phone,
            ]);

            Http::withToken($this->secretKey)
                ->post('https://api.paystack.co/customer', $payload);
        } catch (\Exception $e) {
            Log::warning('Paystack customer upsert failed: '.$e->getMessage());
        }
    }

    public function verifyPayment(string $reference, string|int|null $transactionId = null): array
    {
        if (trim($this->secretKey) === '') {
            Log::error('Paystack verification aborted: PAYSTACK_SECRET_KEY is not configured.');

            return ['success' => false, 'reason' => 'gateway_not_configured'];
        }

        if (trim($reference) === '') {
            return ['success' => false, 'reason' => 'missing_reference'];
        }

        try {
            $response = Http::withToken($this->secretKey)
                ->get('https://api.paystack.co/transaction/verify/'.rawurlencode($reference));

            if (! $response->successful() || ! $response->json('status')) {
                return ['success' => false, 'reason' => 'verification_failed'];
            }

            $data = $response->json('data');

            $providerReference = (string) ($data['reference'] ?? '');
            $currency = isset($data['currency']) ? strtoupper((string) $data['currency']) : null;
            $amount = isset($data['amount']) ? ((float) $data['amount']) / 100 : null;

            return [
                // The provider is the only authority on success, but the caller
                // still cross-checks reference, amount and currency.
                'success' => ($data['status'] ?? null) === 'success',
                'amount' => $amount,
                'currency' => $currency,
                'reference' => $providerReference,
                'transaction_id' => $data['id'] ?? null,
                'status' => $data['status'] ?? 'unknown',
                'paid_at' => $data['paid_at'] ?? null,
                'channel' => $data['channel'] ?? '',
                'card_details' => $data['authorization'] ?? null,
                'reason' => ($data['status'] ?? null) === 'success' ? null : 'provider_status_not_success',
            ];
        } catch (\Exception $e) {
            Log::error('Paystack verification exception: '.$e->getMessage());

            return ['success' => false, 'reason' => 'verification_error'];
        }
    }
}
