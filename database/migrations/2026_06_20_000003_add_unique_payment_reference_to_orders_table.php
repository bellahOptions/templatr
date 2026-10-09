<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes `orders.payment_reference` unique so a single provider reference can
 * only ever settle one order.
 *
 * This is deliberately defensive: if the live table already contains duplicate
 * non-null references, the index is skipped (with a warning) rather than
 * failing the deploy or deleting data. The application also rejects duplicate
 * references at runtime, so the constraint is a backstop, not the only guard.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        if ($this->alreadyIndexed()) {
            return;
        }

        if ($this->hasDuplicateReferences()) {
            logger()->warning(
                'Skipped unique index on orders.payment_reference: duplicate references exist. '.
                'Resolve them (see docs) and re-run this migration to enable the constraint.'
            );

            return;
        }

        Schema::table('orders', function ($table) {
            $table->unique('payment_reference', 'orders_payment_reference_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders') || ! $this->alreadyIndexed()) {
            return;
        }

        Schema::table('orders', function ($table) {
            $table->dropUnique('orders_payment_reference_unique');
        });
    }

    protected function alreadyIndexed(): bool
    {
        foreach (Schema::getIndexes('orders') as $index) {
            if (($index['name'] ?? null) === 'orders_payment_reference_unique') {
                return true;
            }
        }

        return false;
    }

    protected function hasDuplicateReferences(): bool
    {
        return DB::table('orders')
            ->select('payment_reference')
            ->whereNotNull('payment_reference')
            ->where('payment_reference', '!=', '')
            ->groupBy('payment_reference')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
    }
};
