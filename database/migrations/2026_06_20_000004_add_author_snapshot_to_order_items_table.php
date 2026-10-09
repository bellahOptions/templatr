<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Records who earned what, per order item.
 *
 * `author_id` is snapshotted at order creation instead of being resolved from
 * the product at fulfilment time, so re-assigning a product to another author
 * can never redirect (or lose) an existing sale's earnings. `platform_commission`
 * keeps the arithmetic auditable.
 *
 * Both columns are nullable and backfilled from the data already stored on
 * `order_items` and `products`, so existing orders remain intact and are not
 * credited differently than before.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'author_id')) {
                $table->foreignId('author_id')->nullable()->after('product_id')
                    ->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('order_items', 'platform_commission')) {
                $table->decimal('platform_commission', 10, 2)->default(0)->after('author_earnings');
            }
        });

        // Backfill the author from the product that was sold.
        DB::table('order_items')
            ->whereNull('author_id')
            ->orderBy('id')
            ->chunk(500, function ($items): void {
                foreach ($items as $item) {
                    $authorId = DB::table('products')->where('id', $item->product_id)->value('user_id');

                    if ($authorId) {
                        DB::table('order_items')->where('id', $item->id)->update(['author_id' => $authorId]);
                    }
                }
            });

        // Backfill the commission so the ledger balances for legacy items.
        // (Portable CASE rather than GREATEST, which SQLite does not provide.)
        DB::table('order_items')
            ->where('platform_commission', 0)
            ->update([
                'platform_commission' => DB::raw('CASE WHEN price - author_earnings > 0 THEN price - author_earnings ELSE 0 END'),
            ]);
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            foreach (['author_id', 'platform_commission'] as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    if ($column === 'author_id') {
                        $table->dropConstrainedForeignId('author_id');
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
