<?php

namespace App\Services\Order;

use App\Mail\OrderReceipt;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewPurchaseAdminNotification;
use App\Services\Webhook\WebhookService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

/**
 * Turns a paid order into deliverables, creator earnings and notifications.
 *
 * Design guarantees:
 *  - Every side effect is guarded by a durable marker, so calling `fulfill()`
 *    twice (webhook replay, callback race, retry after a crash) never credits
 *    an item or sends a receipt twice.
 *  - The database is never held open across e-mail, notification or outbound
 *    webhook calls: those run after the transaction commits.
 *  - Failures are recorded on the order so `retry()` (and the recovery command)
 *    can pick the order up again instead of leaving it silently unfulfilled.
 */
class OrderFulfillmentService
{
    public function __construct(
        protected WebhookService $webhookService,
        protected \App\Services\Earnings\CreatorEarningsService $earningsService,
    ) {}

    /**
     * Queue fulfilment for a paid order.
     *
     * Dispatched through `afterCommit` so the job can never be picked up before
     * the transaction that marked the order paid has been committed — and so a
     * rollback can never credit an author for an order that is not paid.
     */
    public function dispatch(Order $order): void
    {
        $orderId = $order->id;

        DB::afterCommit(function () use ($orderId): void {
            // `afterCommit` outside a transaction runs immediately; inside one it
            // runs once the outermost transaction commits.
            try {
                \App\Jobs\FulfillOrderJob::dispatch($orderId);
            } catch (\Throwable $e) {
                // A queue outage must not lose the fulfilment: record the failure
                // so `orders:recover-fulfillment` can pick it up.
                Log::error('Unable to queue fulfilment job; order needs recovery', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                ]);

                Order::whereKey($orderId)->update([
                    'fulfillment_status' => 'pending',
                    'fulfillment_error' => 'queue_dispatch_failed: '.mb_substr($e->getMessage(), 0, 500),
                ]);
            }
        });
    }

    /**
     * Run fulfilment for a paid order.
     *
     * @return bool true when the order is fully fulfilled
     */
    public function fulfill(Order $order): bool
    {
        if (! $order->isPaid()) {
            Log::warning('Fulfilment skipped: order is not paid', ['order_id' => $order->id]);

            return false;
        }

        try {
            $this->creditCreators($order);
        } catch (\Throwable $e) {
            $this->recordFailure($order, $e->getMessage());

            Log::error('Order fulfilment failed while crediting creators', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        // Everything below is post-commit and non-fatal: a mail or webhook
        // outage must never mark a paid order as failed and retry the money.
        $this->sendNotifications($order);

        return true;
    }

    /**
     * Credit each order item's author exactly once.
     *
     * @return bool true when at least one new earning was recorded
     */
    protected function creditCreators(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            /** @var Order $locked */
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $locked->load('items.product.author');

            $creditedAny = false;

            foreach ($locked->items as $item) {
                if ($this->earningsService->recordForItem($item, $locked)) {
                    $creditedAny = true;
                }
            }

            if (! $creditedAny) {
                Log::info('Order fulfilment re-run; all items were already credited', [
                    'order_id' => $locked->id,
                ]);
            }

            return $creditedAny;
        });
    }

    /**
     * Deliver the post-payment side effects, exactly once per order.
     *
     * Runs after the crediting transaction has committed and never inside it:
     * mail, notifications and outbound webhooks must not hold a database
     * transaction open (or roll back a legitimate payment).
     */
    protected function sendNotifications(Order $order): void
    {
        $order->refresh()->load('items.product.author');

        // Already announced: a retry must not spam the buyer or the admins.
        if ($order->fulfillment_notified_at !== null) {
            $order->forceFill([
                'fulfilled_at' => $order->fulfilled_at ?? now(),
                'fulfillment_status' => 'fulfilled',
                'fulfillment_error' => null,
            ])->save();

            return;
        }

        try {
            if ($order->customer_email) {
                Mail::to($order->customer_email)->queue(new OrderReceipt($order));
            }
        } catch (\Throwable $e) {
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
        } catch (\Throwable $e) {
            Log::warning('Failed to send new purchase notification: '.$e->getMessage());
        }

        try {
            $this->webhookService->fire('order.paid', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'amount' => (float) $order->total_amount,
                'currency' => $order->currencyCode(),
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
        } catch (\Throwable $e) {
            Log::warning('Failed to fire order.paid webhook: '.$e->getMessage());
        }

        $order->forceFill([
            'fulfilled_at' => $order->fulfilled_at ?? now(),
            'fulfillment_status' => 'fulfilled',
            'fulfillment_error' => null,
            'fulfillment_notified_at' => now(),
        ])->save();
    }

    protected function recordFailure(Order $order, string $message): void
    {
        try {
            $order->forceFill([
                'fulfillment_status' => 'failed',
                'fulfillment_error' => mb_substr($message, 0, 1000),
            ])->increment('fulfillment_attempts');
        } catch (\Throwable $e) {
            Log::error('Unable to record fulfilment failure', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
