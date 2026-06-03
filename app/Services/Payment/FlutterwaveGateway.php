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

    public function __construct()
    {
        $this->secretKey = config('services.flutterwave.secret', env('FLUTTERWAVE_SECRET_KEY', ''));
        $this->publicKey = config('services.flutterwave.public', env('FLUTTERWAVE_PUBLIC_KEY', ''));
        $this->encryptionKey = config('services.flutterwave.encryption', env('FLUTTERWAVE_ENCRYPTION_KEY', ''));
        $this->isLive = env('FLUTTERWAVE_LIVE', false);
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
        try {
            $response = Http::withToken($this->secretKey)
                ->post('https://api.flutterwave.com/v3/payments', [
                    'tx_ref' => $data['reference'],
                    'amount' => $data['amount'],
                    'currency' => 'NGN',
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
                    ]
                ]);

            if ($response->successful() && $response->json('status') === 'success') {
                return [
                    'success' => true,
                    'authorization_url' => $response->json('data.link'),
                    'reference' => $response->json('data.tx_ref'),
                ];
            }

            Log::error('Flutterwave initialization failed', ['response' => $response->json()]);
            return ['success' => false, 'message' => $response->json('message', 'Payment initialization failed')];
        } catch (\Exception $e) {
            Log::error('Flutterwave exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Payment gateway error. Please try again.'];
        }
    }

    public function verifyPayment(string $reference): array
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->get("https://api.flutterwave.com/v3/transactions/{$reference}/verify");

            if ($response->successful() && $response->json('status') === 'success') {
                $data = $response->json('data');
                return [
                    'success' => $data['status'] === 'successful',
                    'amount' => $data['amount'],
                    'reference' => $data['tx_ref'],
                    'status' => $data['status'],
                    'paid_at' => $data['created_at'] ?? null,
                    'channel' => $data['payment_type'] ?? '',
                ];
            }

            return ['success' => false, 'message' => 'Verification failed'];
        } catch (\Exception $e) {
            Log::error('Flutterwave verification exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Verification error'];
        }
    }
}
