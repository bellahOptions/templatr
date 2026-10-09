<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InterswitchGateway implements PaymentGateway
{
    protected string $clientId;

    protected string $clientSecret;

    protected string $merchantCode;

    protected bool $isLive;

    public function __construct()
    {
        $this->clientId = (string) config('services.interswitch.client_id', '');
        $this->clientSecret = (string) config('services.interswitch.client_secret', '');
        $this->merchantCode = (string) config('services.interswitch.merchant_code', '');
        $this->isLive = (bool) config('services.interswitch.live', false);
    }

    public function getMerchantCode(): string
    {
        return $this->merchantCode;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getName(): string
    {
        return 'interswitch';
    }

    public function initializePayment(array $data): array
    {
        try {
            $baseUrl = $this->isLive
                ? 'https://webpay.interswitchng.com'
                : 'https://sandbox.interswitchng.com';

            $paymentData = [
                'amount' => intval($data['amount'] * 100), // Interswitch uses kobo
                'currency' => 'NGN',
                'merchant_code' => $this->merchantCode,
                'transaction_reference' => $data['reference'],
                'redirect_url' => $data['callback_url'],
                'customer_email' => $data['email'],
                'customer_name' => $data['name'] ?? 'Customer',
                'description' => 'Templatr Purchase - Order #'.($data['order_id'] ?? ''),
                'pay_item_id' => config('services.interswitch.pay_item_id', '101'),
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => $this->generateAuthToken($paymentData),
            ])->post("{$baseUrl}/payments/api/v1/payment", $paymentData);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['transaction_reference'])) {
                    return [
                        'success' => true,
                        'authorization_url' => $responseData['url'] ?? "{$baseUrl}/payments/payment?reference={$data['reference']}",
                        'reference' => $data['reference'],
                    ];
                }
            }

            Log::error('Interswitch initialization failed', ['response' => $response->json()]);

            return ['success' => false, 'message' => 'Payment initialization failed'];
        } catch (\Exception $e) {
            Log::error('Interswitch exception: '.$e->getMessage());

            return ['success' => false, 'message' => 'Payment gateway error. Please try again.'];
        }
    }

    public function verifyPayment(string $reference, string|int|null $transactionId = null): array
    {
        if (trim($this->clientId) === '' || trim($this->clientSecret) === '') {
            Log::error('Interswitch verification aborted: credentials are not configured.');

            return ['success' => false, 'reason' => 'gateway_not_configured'];
        }

        try {
            $baseUrl = $this->isLive
                ? 'https://webpay.interswitchng.com'
                : 'https://sandbox.interswitchng.com';

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->get("{$baseUrl}/payments/api/v1/payment/query", [
                'merchant_code' => $this->merchantCode,
                'transaction_reference' => $reference,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $status = (string) ($data['status'] ?? 'FAILED');

                return [
                    'success' => $status === 'SUCCESS',
                    'amount' => isset($data['amount']) ? ((float) $data['amount']) / 100 : null,
                    'currency' => isset($data['currency']) ? strtoupper((string) $data['currency']) : 'NGN',
                    'reference' => $data['transaction_reference'] ?? $reference,
                    'transaction_id' => $data['transaction_reference'] ?? null,
                    'status' => $status,
                    'reason' => $status === 'SUCCESS' ? null : 'provider_status_not_success',
                ];
            }

            return ['success' => false, 'reason' => 'verification_failed'];
        } catch (\Exception $e) {
            Log::error('Interswitch verification exception: '.$e->getMessage());

            return ['success' => false, 'reason' => 'verification_error'];
        }
    }

    protected function generateAuthToken(array $data): string
    {
        // Simple token generation - in production, use proper Interswitch auth
        return base64_encode($this->clientId.':'.$this->clientSecret);
    }
}
