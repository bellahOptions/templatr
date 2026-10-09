<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Order\OrderFulfillmentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Durable fulfilment for a paid order.
 *
 * Queued (not run inline) so a webhook response is never blocked by mail or
 * outbound webhook delivery, and so a transient failure is retried by the queue
 * instead of leaving the order permanently unfulfilled. `ShouldBeUnique` keeps
 * concurrent webhook/callback deliveries from stacking duplicate work.
 */
class FulfillOrderJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $uniqueFor = 3600;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 120, 600];

    public function __construct(public int $orderId) {}

    public function uniqueId(): string
    {
        return 'order-fulfillment:'.$this->orderId;
    }

    /**
     * A failed job must not be retried once the order is already fulfilled.
     */
    public function retryUntil(): \DateTimeInterface
    {
        return now()->addDay();
    }

    public function handle(OrderFulfillmentService $fulfillment): void
    {
        $order = Order::find($this->orderId);

        if (! $order) {
            Log::warning('Fulfilment job: order no longer exists', ['order_id' => $this->orderId]);

            return;
        }

        if (! $order->isPaid()) {
            Log::warning('Fulfilment job: order is not paid, nothing to do', ['order_id' => $this->orderId]);

            return;
        }

        if ($fulfillment->fulfill($order)) {
            return;
        }

        throw new \RuntimeException("Fulfilment failed for order #{$this->orderId}");
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Fulfilment job permanently failed', [
            'order_id' => $this->orderId,
            'error' => $exception->getMessage(),
        ]);

        Order::whereKey($this->orderId)->update([
            'fulfillment_status' => 'failed',
            'fulfillment_error' => mb_substr($exception->getMessage(), 0, 1000),
        ]);
    }
}
