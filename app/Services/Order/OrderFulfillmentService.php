<?php

namespace App\Services\Order;

use App\Mail\OrderReceipt;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewPurchaseAdminNotification;
use App\Services\Webhook\WebhookService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class OrderFulfillmentService
{
    public function __construct(protected WebhookService $webhookService) {}

    /**
     * Credit authors, send emails, and fire outbound webhooks for a paid order.
     * Safe to call multiple times — idempotency is enforced by the caller via
     * an atomic `UPDATE WHERE payment_status != 'paid'` before invoking this.
     */
    public function fulfill(Order $order): void
    {
        $order->load('items.product.author');

        foreach ($order->items as $item) {
            if ($item->product?->author) {
                $item->product->author->increment('balance', $item->author_earnings);
            }
        }

        try {
            Mail::to($order->customer_email)->queue(new OrderReceipt($order));
        } catch (\Exception $e) {
            Log::warning('Failed to queue order receipt email: '.$e->getMessage());
        }

        try {
            $adminUsers = User::where('role', 'admin')->get();
            foreach ($adminUsers as $admin) {
                $admin->notify(new NewPurchaseAdminNotification($order));
            }

            $specificEmail = config('services.admin.notification_email');
            if ($specificEmail && filter_var($specificEmail, FILTER_VALIDATE_EMAIL)) {
                Notification::route('mail', $specificEmail)
                    ->notify(new NewPurchaseAdminNotification($order));
            }
        } catch (\Exception $e) {
            Log::warning('Failed to send new purchase notification: '.$e->getMessage());
        }

        try {
            $this->webhookService->fire('order.paid', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'amount' => (float) $order->total_amount,
                'currency' => 'NGN',
                'payment_method' => $order->payment_method,
                'customer_email' => $order->customer_email,
                'customer_name' => $order->user?->name ?? $order->guest_name ?? 'Guest',
                'items' => $order->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'title' => $item->product?->title ?? 'Product #'.$item->product_id,
                    'price' => (float) $item->price,
                ])->toArray(),
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to fire order.paid webhook: '.$e->getMessage());
        }
    }
}
