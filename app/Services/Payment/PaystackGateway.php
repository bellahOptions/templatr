<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackGateway implements PaymentGateway
{
    protected string $secretKey;
    protected string $publicKey;
    protected bool $isLive;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret', env('PAYSTACK_SECRET_KEY', ''));
        $this->publicKey = config('services.paystack.public', env('PAYSTACK_PUBLIC_KEY', ''));
        $this->isLive = env('PAYSTACK_LIVE', false);
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
        try {
            $response = Http::withToken($this->secretKey)
                ->post('https://api.paystack.co/transaction/initialize', [
                    'email' => $data['email'],
                    'amount' => $data['amount'] * 100, // Paystack uses kobo
                    'reference' => $data['reference'],
                    'callback_url' => $data['callback_url'],
                    'metadata' => [
                        'order_id' => $data['order_id'] ?? null,
                        'custom_fields' => [
                            ['display_name' => 'Order', 'variable_name' => 'order_id', 'value' => $data['order_id'] ?? '']
                        ]
                    ]
                ]);

            if ($response->successful() && $response->json('status')) {
                return [
                    'success' => true,
                    'authorization_url' => $response->json('data.authorization_url'),
                    'reference' => $response->json('data.reference'),
                    'access_code' => $response->json('data.access_code'),
                ];
            }

            Log::error('Paystack initialization failed', ['response' => $response->json()]);
            return ['success' => false, 'message' => $response->json('message', 'Payment initialization failed')];
        } catch (\Exception $e) {
            Log::error('Paystack exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment gateway error. Please try again.'];
        }
    }

    public function verifyPayment(string $reference): array
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->get("https://api.paystack.co/transaction/verify/{$reference}");

            if ($response->successful() && $response->json('status')) {
                $data = $response->json('data');
                return [
                    'success' => $data['status'] === 'success',
                    'amount' => $data['amount'] / 100,
                    'reference' => $data['reference'],
                    'status' => $data['status'],
                    'paid_at' => $data['paid_at'] ?? null,
                    'channel' => $data['channel'] ?? '',
                    'card_details' => $data['authorization'] ?? null,
                ];
            }

            return ['success' => false, 'message' => 'Verification failed'];
        } catch (\Exception $e) {
            Log::error('Paystack verification exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Verification error'];
        }
    }
}
