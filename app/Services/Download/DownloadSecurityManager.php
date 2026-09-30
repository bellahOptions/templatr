<?php

namespace App\Services\Download;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Payment\PaymentManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DownloadSecurityManager
{
    public function __construct(
        protected PaymentManager $paymentManager,
    ) {}

    /**
     * Verify and authorize a download request with multiple security layers.
     *
     * Every layer is ordered cheapest-first so an unauthorized or throttled
     * request does no database or network work. On shared cPanel hosting that
     * ordering is what keeps 50 concurrent buyers inside the account's CPU and
     * entry-process limits.
     *
     * @throws HttpException
     */
    public function authorizeDownload(Product $product): DownloadAuthorization
    {
        $authorization = new DownloadAuthorization;
        $authorization->product = $product;

        $ip = request()->ip();
        $user = Auth::user();

        // ── Layer 1: Per-account concurrency guard ──
        // A single buyer opening dozens of parallel connections is what actually
        // exhausts a shared account, not 50 distinct buyers downloading at once.
        $concurrencyKey = 'download_concurrency:'.($user ? 'user:'.$user->id : 'ip:'.$ip);
        $concurrencyLimit = (int) config('watermark.max_concurrent_per_user', 4);
        $slotToken = $this->acquireDownloadSlot($concurrencyKey, $concurrencyLimit);

        if ($slotToken === null) {
            Log::warning("Download concurrency limit reached for {$concurrencyKey}");
            throw new HttpException(429, 'You already have several downloads running. Please wait for them to finish or try again shortly.');
        }

        $authorization->slotToken = $slotToken;
        $authorization->concurrencyKey = $concurrencyKey;

        try {
            return $this->authorizeWithSlot($product, $authorization, $user, $ip);
        } catch (\Throwable $e) {
            // Any failure releases the concurrency slot immediately; a successful
            // download holds it until the response has been sent.
            $this->releaseDownloadSlot($authorization);
            throw $e;
        }
    }

    /**
     * @throws HttpException
     */
    protected function authorizeWithSlot(Product $product, DownloadAuthorization $authorization, mixed $user, ?string $ip): DownloadAuthorization
    {
        // ── Layer 2: IP throttle for unauthenticated probing ──
        $rateLimitKey = 'download_rate_limit:'.$ip;
        $attempts = (int) Cache::get($rateLimitKey, 0);
        $maxAttempts = (int) config('watermark.rate_limit_attempts', 30);

        if ($attempts >= $maxAttempts) {
            Log::warning("Download rate limit exceeded for IP: {$ip}");
            throw new HttpException(429, 'Too many download attempts. Please wait and try again.');
        }

        Cache::put($rateLimitKey, $attempts + 1, now()->addMinutes((int) config('watermark.rate_limit_minutes', 15)));

        // ── Layer 3: Product is published ──
        if (! $product->is_published) {
            throw new HttpException(404, 'Product not found.');
        }

        // ── Layer 4: Admin bypass ──
        if ($user?->isAdmin()) {
            $authorization->isAuthorized = true;
            $authorization->orderItem = null;
            $authorization->isAdminBypass = true;

            return $authorization;
        }

        // ── Layer 5: File is present ──
        if (! $product->file_path) {
            Log::error("Download failed: No file set for product #{$product->id}");
            throw new HttpException(500, 'The requested file is unavailable. Please contact support.');
        }

        if (! Storage::disk('public')->exists($product->file_path)) {
            Log::error("Download failed: File not found for product #{$product->id} - {$product->file_path}");
            throw new HttpException(500, 'The requested file is unavailable. Please contact support.');
        }

        // ── Layer 6: Locate the paid order item ──
        $orderItem = $this->resolveOrderItem($product, $user);

        if (! $orderItem) {
            Log::warning("Unauthorized download attempt for product #{$product->id} by ".($user ? "user #{$user->id}" : "guest IP {$ip}"));

            if (! $user) {
                throw new HttpException(401, 'You must provide a valid download token. Please check your email for the download link.');
            }

            throw new HttpException(403, 'You have not purchased this item. Please purchase it first to download.');
        }

        // ── Layer 7: Token expiration (guests) ──
        if ($orderItem->download_token_expires_at && now()->greaterThan($orderItem->download_token_expires_at)) {
            throw new HttpException(410, 'Your download link has expired. Please contact support for a new link.');
        }

        // ── Layer 8: Download limit ──
        if (! $orderItem->isDownloadable()) {
            throw new HttpException(403, 'Download limit reached. You have used all '.OrderItem::MAX_DOWNLOADS.' allowed downloads for this item.');
        }

        // ── Layer 9: Cached gateway re-verification ──
        // Cached so a repeat download costs zero HTTP round-trips to Paystack or
        // Flutterwave — the dominant latency and CPU cost on a shared host.
        $this->verifyPaymentWithGateway($orderItem->order);

        $authorization->isAuthorized = true;
        $authorization->orderItem = $orderItem;

        return $authorization;
    }

    /**
     * Find the buyer's paid order item, supporting signed-in users and guests.
     */
    protected function resolveOrderItem(Product $product, mixed $user): ?OrderItem
    {
        if ($user) {
            return OrderItem::query()
                ->select(['id', 'order_id', 'product_id', 'download_count', 'download_token_expires_at'])
                ->with('order')
                ->where('product_id', $product->id)
                ->whereHas('order', function ($q) use ($user) {
                    $q->where('user_id', $user->id)->where('payment_status', 'paid');
                })
                ->first();
        }

        // Guests authenticate with a token whose SHA-256 hash is stored on the
        // order item. The plain token is only ever visible in the emailed link.
        $token = (string) request()->query('token', '');

        if ($token === '') {
            return null;
        }

        return OrderItem::query()
            ->select(['id', 'order_id', 'product_id', 'download_count', 'download_token_expires_at'])
            ->with('order')
            ->where('product_id', $product->id)
            ->where('download_token', hash('sha256', $token))
            ->whereHas('order', function ($q) {
                $q->whereNull('user_id')->where('payment_status', 'paid');
            })
            ->first();
    }

    /**
     * Reserve one of the account's concurrent download slots.
     *
     * Returns a release token, or null when the limit is already saturated.
     */
    protected function acquireDownloadSlot(string $key, int $limit): ?string
    {
        if ($limit <= 0) {
            return 'unlimited';
        }

        $token = Str::random(16);
        $ttl = now()->addSeconds((int) config('watermark.concurrency_lock_seconds', 300));

        // Fast path: no lock contention.
        if (Cache::add($key, [$token => now()->getTimestamp()], $ttl)) {
            return $token;
        }

        // Windows file cache has no atomic read-modify-write, so retry under a
        // short-lived mutex. On Linux (the production target) flock makes this
        // first attempt win for the overwhelming majority of requests.
        $mutex = Cache::lock($key.':mutex', 5);

        try {
            $mutex->block(3, function () use ($key, $limit, $token, $ttl): void {
                $slots = $this->pruneSlots($this->slotPayload($key));

                if (count($slots) >= $limit) {
                    throw new HttpException(429, 'Too many concurrent downloads on this account. Please wait for one to finish.');
                }

                $slots[$token] = now()->getTimestamp();
                Cache::put($key, $slots, $ttl);
            });
        } catch (HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // If the lock backend is unavailable, allow the download rather than
            // blocking a paying customer.
            Log::warning('Download slot lock unavailable: '.$e->getMessage());

            return $token;
        }

        return $token;
    }

    public function releaseDownloadSlot(DownloadAuthorization $authorization): void
    {
        if (! $authorization->slotToken || ! $authorization->concurrencyKey) {
            return;
        }

        if ($authorization->slotToken === 'unlimited') {
            return;
        }

        $key = $authorization->concurrencyKey;

        try {
            $slots = $this->slotPayload($key);

            if ($slots === null) {
                return;
            }

            unset($slots[$authorization->slotToken]);
            $slots = $this->pruneSlots($slots);

            if ($slots === []) {
                Cache::forget($key);
            } else {
                Cache::put($key, $slots, now()->addSeconds((int) config('watermark.concurrency_lock_seconds', 300)));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to release download slot: '.$e->getMessage());
        }

        $authorization->slotToken = null;
    }

    /**
     * @return array<string, int>|null
     */
    protected function slotPayload(string $key): ?array
    {
        $value = Cache::get($key);

        return is_array($value) ? $value : null;
    }

    /**
     * Drop slots whose holder died without releasing (browser closed mid-stream).
     *
     * @param  array<string, int>|null  $slots
     * @return array<string, int>
     */
    protected function pruneSlots(?array $slots): array
    {
        if ($slots === null) {
            return [];
        }

        $cutoff = now()->subSeconds((int) config('watermark.concurrency_lock_seconds', 300))->getTimestamp();

        return array_filter($slots, fn (int $startedAt) => $startedAt >= $cutoff);
    }

    /**
     * Generate a secure download token for a guest order item.
     *
     * Only the hash is persisted; the plain token is returned once for the
     * download link that is emailed to the buyer.
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
        if (! $authorization->orderItem) {
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
        $orderItem->product()->increment('download_count');

        // Log the download
        Log::info("Download recorded: product #{$orderItem->product_id}, order item #{$orderItem->id}, ".
            'user: '.($orderItem->order->user_id ? "user #{$orderItem->order->user_id}" : 'guest'), [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'download_count' => $orderItem->download_count,
            ]
        );
    }

    /**
     * Verify payment status with the payment gateway provider, cached.
     *
     * A paid order is only re-checked with the provider once per cache window.
     * The check is advisory: our own webhook-verified status remains the source
     * of truth, so a gateway outage never blocks a legitimate download.
     */
    protected function verifyPaymentWithGateway(Order $order): bool
    {
        if ($order->payment_method === 'direct' || ! $order->payment_reference) {
            return $order->payment_status === 'paid';
        }

        $cacheKey = 'gateway_verify:order:'.$order->id;
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return (bool) $cached;
        }

        $verified = $order->payment_status === 'paid';

        try {
            $gateway = $this->paymentManager->gateway($order->payment_method);
            $verification = $gateway->verifyPayment($order->payment_reference);
            $verified = (bool) ($verification['success'] ?? $verified);

            if (! $verified) {
                Log::warning("Payment re-verification failed for order #{$order->id} (reference: {$order->payment_reference}). Allowing download — investigate if fraudulent.");
            }
        } catch (\Exception $e) {
            // Gateway unreachable: trust our own webhook-verified status.
            Log::warning("Gateway payment verification failed for order #{$order->id}: ".$e->getMessage());
        }

        Cache::put(
            $cacheKey,
            $verified,
            now()->addSeconds((int) config('watermark.gateway_recheck_seconds', 86400))
        );

        return $verified;
    }

    /**
     * Build the tokenised guest download URL for an order item.
     *
     * Issues a fresh token and returns the URL carrying the plain value. This is
     * what should be embedded in the order-receipt email, which is the only way a
     * guest can retrieve their download after the confirmation session ends.
     */
    public function createGuestDownloadUrl(OrderItem $orderItem, int $expiresInHours = 72): string
    {
        $token = $this->generateDownloadToken($orderItem, $expiresInHours);

        return route('products.download.guest', [
            'product' => $orderItem->product->slug,
            'token' => $token,
        ]);
    }
}
