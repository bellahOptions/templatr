<?php

namespace App\Console\Commands;

use App\Jobs\FulfillOrderJob;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Recovery net for fulfilment.
 *
 * Finds orders that are paid but never fulfilled — the state a crash between
 * "marked paid" and "credited the authors" used to leave behind permanently —
 * and queues fulfilment again. Fulfilment is idempotent, so re-queueing an
 * order whose items were already credited cannot pay a creator twice.
 */
class RecoverFulfillmentCommand extends Command
{
    protected $signature = 'orders:recover-fulfillment
                            {--minutes=5 : Only consider orders paid at least this many minutes ago}
                            {--limit=200 : Maximum number of orders to re-queue in one run}
                            {--dry-run : List what would be re-queued without dispatching}';

    protected $description = 'Re-queue fulfilment for paid orders that were never fulfilled';

    public function handle(): int
    {
        $minutes = max(0, (int) $this->option('minutes'));
        $limit = max(1, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        $orders = Order::query()
            ->where('payment_status', 'paid')
            ->where(function ($query) {
                $query->whereNull('fulfilled_at')
                    ->orWhereNull('fulfillment_status')
                    ->orWhereIn('fulfillment_status', ['pending', 'failed']);
            })
            ->where('paid_at', '<=', now()->subMinutes($minutes))
            ->orderBy('paid_at')
            ->limit($limit)
            ->get(['id', 'order_number', 'fulfillment_status', 'paid_at']);

        if ($orders->isEmpty()) {
            $this->info('No unfulfilled paid orders found.');

            return self::SUCCESS;
        }

        foreach ($orders as $order) {
            $this->line(sprintf(
                '%s order %s (status: %s, paid: %s)',
                $dryRun ? 'would re-queue' : 're-queueing',
                $order->order_number,
                $order->fulfillment_status ?? 'unknown',
                $order->paid_at?->toDateTimeString() ?? 'unknown',
            ));

            if (! $dryRun) {
                FulfillOrderJob::dispatch($order->id);

                Order::whereKey($order->id)->update([
                    'fulfillment_status' => 'pending',
                    'fulfillment_error' => null,
                ]);
            }
        }

        Log::info('Fulfilment recovery run', [
            'orders' => $orders->pluck('id')->all(),
            'dry_run' => $dryRun,
        ]);

        $this->info(($dryRun ? 'Would re-queue ' : 'Re-queued ').$orders->count().' order(s).');

        return self::SUCCESS;
    }
}
