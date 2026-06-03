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
        $this->clientId = config('services.interswitch.client_id', env('INTERSWITCH_CLIENT_ID', ''));
        $this->clientSecret = config('services.interswitch.client_secret', env('INTERSWITCH_CLIENT_SECRET', ''));
        $this->merchantCode = config('services.interswitch.merchant_code', env('INTERSWITCH_MERCHANT_CODE', ''));
        $this->isLive = env('INTERSWITCH_LIVE', false);
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
                'description' => 'Templatr Purchase - Order #' . ($data['order_id'] ?? ''),
                'pay_item_id' => env('INTERSWITCH_PAY_ITEM_ID', '101'),
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
            Log::error('Interswitch exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment gateway error. Please try again.'];
        }
    }

    public function verifyPayment(string $reference): array
    {
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
                return [
                    'success' => ($data['status'] ?? '') === 'SUCCESS',
                    'amount' => $data['amount'] / 100,
                    'reference' => $data['transaction_reference'],
                    'status' => $data['status'] ?? 'FAILED',
                ];
            }

            return ['success' => false, 'message' => 'Verification failed'];
        } catch (\Exception $e) {
            Log::error('Interswitch verification exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Verification error'];
        }
    }

    protected function generateAuthToken(array $data): string
    {
        // Simple token generation - in production, use proper Interswitch auth
        return base64_encode($this->clientId . ':' . $this->clientSecret);
    }
}
