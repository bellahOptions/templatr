<?php

namespace App\Services\Download;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DownloadSecurityManager
{
    protected PaymentManager $paymentManager;

    public function __construct(PaymentManager $paymentManager)
    {
        $this->paymentManager = $paymentManager;
    }

    /**
     * Verify and authorize a download request with multiple security layers.
     *
     * @throws HttpException
     */
    public function authorizeDownload(Product $product): DownloadAuthorization
    {
        $authorization = new DownloadAuthorization();
        $authorization->product = $product;

        // ── Layer 1: Rate limiting by IP ──
        $ip = request()->ip();
        $rateLimitKey = "download_rate_limit:{$ip}";
        $attempts = Cache::get($rateLimitKey, 0);

        if ($attempts >= 10) {
            Log::warning("Download rate limit exceeded for IP: {$ip}");
            throw new HttpException(429, 'Too many download attempts. Please wait and try again.');
        }
        Cache::put($rateLimitKey, $attempts + 1, now()->addMinutes(15));

        // ── Layer 2: Check if the product exists and is published ──
        if (!$product->is_published) {
            throw new HttpException(404, 'Product not found.');
        }

        // ── Layer 3: Check file exists ──
        if (!$product->file_path || !\Illuminate\Support\Facades\Storage::exists($product->file_path)) {
            Log::error("Download failed: File not found for product #{$product->id} - {$product->file_path}");
            throw new HttpException(500, 'The requested file is unavailable. Please contact support.');
        }

        // ── Layer 4: Authentication check ──
        $user = Auth::user();

        // ── Layer 5: Find the order item ──
        $orderItem = null;

        if ($user) {
            // Authenticated user: find their paid order item
            $orderItem = OrderItem::where('product_id', $product->id)
                ->whereHas('order', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                      ->where('payment_status', 'paid');
                })
                ->first();
        } else {
            // Guest user: check via download token in request
            $token = request()->query('token');
            if ($token) {
                $orderItem = OrderItem::where('product_id', $product->id)
                    ->where('download_token', $token)
                    ->whereHas('order', function ($q) {
                        $q->whereNull('user_id')
                          ->where('payment_status', 'paid');
                    })
                    ->first();
            }
        }

        if (!$orderItem) {
            // Log the failed attempt
            Log::warning("Unauthorized download attempt for product #{$product->id} by " . ($user ? "user #{$user->id}" : "guest IP {$ip}"));

            if ($user && $user->isAdmin()) {
                // Admins bypass purchase check
                $authorization->isAuthorized = true;
                $authorization->orderItem = null;
                $authorization->isAdminBypass = true;
                return $authorization;
            }

            if (!$user) {
                throw new HttpException(401, 'You must provide a valid download token. Please check your email for the download link.');
            }

            throw new HttpException(403, 'You have not purchased this item. Please purchase it first to download.');
        }

        // ── Layer 6: Verify payment with the gateway (double-check) ──
        if ($orderItem->order->payment_method !== 'direct') {
            $paymentVerified = $this->verifyPaymentWithGateway($orderItem->order);
            if (!$paymentVerified) {
                Log::warning("Payment verification failed for order #{$orderItem->order->id} - marking as unpaid.");
                $orderItem->order->update(['payment_status' => 'unpaid']);
                throw new HttpException(403, 'Payment could not be verified. Please contact support.');
            }
        }

        // ── Layer 7: Check download limits ──
        if (!$orderItem->isDownloadable()) {
            $remaining = $orderItem->remaining_downloads;
            $maxMsg = $orderItem->order->user_id ? '2' : '1';
            throw new HttpException(403, "Download limit reached. You have used all {$maxMsg} allowed download(s) for this item.");
        }

        // ── Layer 8: Check download token expiration ──
        if ($orderItem->download_token_expires_at && now()->greaterThan($orderItem->download_token_expires_at)) {
            throw new HttpException(410, 'Your download link has expired. Please contact support for a new link.');
        }

        // ── All checks passed ──
        $authorization->isAuthorized = true;
        $authorization->orderItem = $orderItem;

        return $authorization;
    }

    /**
     * Generate a secure download token for a guest order item.
     */
    public function generateDownloadToken(OrderItem $orderItem, int $expiresInHours = 72): string
    {
        $token = Str::random(64);
        $orderItem->update([
            'download_token' => hash('sha256', $token),
            'download_token_expires_at' => now()->addHours($expiresInHours),
        ]);

        return $token;
    }

    /**
     * Record a download event with full audit trail.
     */
    public function recordDownload(DownloadAuthorization $authorization): void
    {
        if (!$authorization->orderItem) {
            return; // Admin bypass, no tracking needed
        }

        $orderItem = $authorization->orderItem;
        $now = now();

        $orderItem->update([
            'download_count' => $orderItem->download_count + 1,
            'first_downloaded_at' => $orderItem->first_downloaded_at ?? $now,
            'last_downloaded_at' => $now,
        ]);

        // Increment product download count
        $orderItem->product->increment('download_count');

        // Log the download
        Log::info("Download recorded: product #{$orderItem->product_id}, order item #{$orderItem->id}, " .
            "user: " . ($orderItem->order->user_id ? "user #{$orderItem->order->user_id}" : "guest"),
            [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'download_count' => $orderItem->download_count,
            ]
        );
    }

    /**
     * Verify payment status with the payment gateway provider.
     */
    protected function verifyPaymentWithGateway(Order $order): bool
    {
        try {
            $gateway = $this->paymentManager->gateway($order->payment_method);
            $verification = $gateway->verifyPayment($order->order_number);

            return $verification['success'] ?? false;
        } catch (\Exception $e) {
            // If gateway verification fails, fall back to our own status
            Log::warning("Gateway payment verification failed for order #{$order->id}: " . $e->getMessage());
            return $order->payment_status === 'paid';
        }
    }

    /**
     * Get secure download URL (signed or temporary).
     */
    public function getSecureDownloadUrl(OrderItem $orderItem): string
    {
        $product = $orderItem->product;

        if ($orderItem->order->user_id) {
            // Authenticated user: route with CSRF
            return route('products.download', ['product' => $product->slug]);
        }

        // Guest: use signed temporary URL with token
        $token = $this->generateDownloadToken($orderItem);

        return route('products.download', [
            'product' => $product->slug,
            'token' => $token,
        ]);
    }
}
