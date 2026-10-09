<?php

namespace App\Services\Earnings;

use App\Models\CreatorEarning;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Writes the creator earnings ledger and keeps the author's balance in step.
 *
 * The ledger row and the balance change happen in the same transaction, and the
 * unique index on `creator_earnings.order_item_id` is the authority on "has this
 * sale already been credited?". A duplicate delivery therefore either sees the
 * existing row (fast path) or loses the insert race (unique violation) — in both
 * cases the balance is not touched a second time.
 */
class CreatorEarningsService
{
    /**
     * Record the earning for one order item exactly once.
     *
     * @return CreatorEarning|null the new earning, or null when it already existed
     */
    public function recordForItem(OrderItem $item, Order $order): ?CreatorEarning
    {
        return DB::transaction(function () use ($item, $order) {
            // Fast path: already credited in an earlier attempt.
            $existing = CreatorEarning::where('order_item_id', $item->id)->first();

            if ($existing) {
                return null;
            }

            $creatorId = $this->resolveCreatorId($item);

            if (! $creatorId) {
                Log::warning('No creator to credit for order item', [
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                ]);

                return null;
            }

            $gross = $this->toMinorUnits($item->price);
            $creatorAmount = $this->toMinorUnits($item->author_earnings);
            $commission = max(0, $gross - $creatorAmount);

            try {
                $earning = CreatorEarning::create([
                    'creator_id' => $creatorId,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'gross_amount' => $gross,
                    'platform_commission' => $commission,
                    'creator_amount' => $creatorAmount,
                    'currency' => $order->currencyCode(),
                    'status' => CreatorEarning::STATUS_CREDITED,
                    'reference' => 'ERN-'.strtoupper(Str::random(20)),
                    'credited_at' => now(),
                ]);
            } catch (QueryException $e) {
                if ($this->isUniqueViolation($e)) {
                    // A concurrent worker won the race; it owns the credit.
                    Log::info('Creator earning already recorded (concurrent insert)', [
                        'order_item_id' => $item->id,
                    ]);

                    return null;
                }

                throw $e;
            }

            // Balance is only moved when the ledger row was actually created.
            $this->creditBalance($creatorId, $creatorAmount);

            return $earning;
        });
    }

    /**
     * Reverse a credited earning: marks the ledger row and debits the balance.
     *
     * Used by support/admin tooling; never deletes history.
     */
    public function reverse(CreatorEarning $earning, string $reason): bool
    {
        return DB::transaction(function () use ($earning, $reason) {
            /** @var CreatorEarning $locked */
            $locked = CreatorEarning::whereKey($earning->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === CreatorEarning::STATUS_REVERSED) {
                return false;
            }

            $locked->forceFill([
                'status' => CreatorEarning::STATUS_REVERSED,
                'reversal_reason' => $reason,
                'reversed_at' => now(),
            ])->save();

            $this->creditBalance($locked->creator_id, -$locked->creator_amount);

            return true;
        });
    }

    /**
     * Total credited (major units) for a creator, from the ledger.
     */
    public function creditedTotal(int $creatorId): float
    {
        $minor = (int) CreatorEarning::where('creator_id', $creatorId)
            ->where('status', CreatorEarning::STATUS_CREDITED)
            ->sum('creator_amount');

        return $minor / 100;
    }

    protected function resolveCreatorId(OrderItem $item): ?int
    {
        if ($item->author_id) {
            return (int) $item->author_id;
        }

        return $item->product?->user_id ? (int) $item->product->user_id : null;
    }

    /**
     * Move the author's balance under a row lock so concurrent credits for the
     * same author cannot lose an update.
     */
    protected function creditBalance(int $creatorId, int $minorUnits): void
    {
        if ($minorUnits === 0) {
            return;
        }

        /** @var User|null $creator */
        $creator = User::whereKey($creatorId)->lockForUpdate()->first();

        if (! $creator) {
            return;
        }

        $delta = $minorUnits / 100;

        if ($delta >= 0) {
            $creator->increment('balance', $delta);
        } else {
            $creator->decrement('balance', abs($delta));
        }
    }

    protected function toMinorUnits(mixed $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    protected function isUniqueViolation(QueryException $e): bool
    {
        $sqlState = $e->errorInfo[0] ?? null;

        return in_array($sqlState, ['23000', '23505'], true);
    }
}
