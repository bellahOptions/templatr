<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the payment bookkeeping needed to validate a transaction end to end
 * without breaking any existing order row:
 *
 *  - `currency` is nullable and only compared when present, so legacy orders
 *    (created before this migration) keep working exactly as before.
 *  - `gateway` records which provider a reference belongs to, so a reference
 *    created for Paystack can never be settled by a Flutterwave webhook.
 *  - `gateway_transaction_id` lets Flutterwave verification address the
 *    transaction by its own id instead of guessing from a client value.
 *  - `paid_at` (backfilled from `updated_at` for already-paid orders) is the
 *    immutable moment payment was confirmed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 3)->nullable()->after('total_amount');
            }

            if (! Schema::hasColumn('orders', 'gateway')) {
                $table->string('gateway', 32)->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('orders', 'gateway_transaction_id')) {
                $table->string('gateway_transaction_id')->nullable()->after('payment_reference');
            }

            if (! Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('orders', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('guest_phone');
            }
        });

        // Backfill only; never rewrite existing statuses.
        DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereNull('paid_at')
            ->update(['paid_at' => DB::raw('updated_at')]);

        // Existing paid rows keep working without a currency comparison; new
        // orders always store 'NGN'.
        DB::table('orders')
            ->whereNull('currency')
            ->update(['currency' => 'NGN']);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['currency', 'gateway', 'gateway_transaction_id', 'paid_at', 'admin_note'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
