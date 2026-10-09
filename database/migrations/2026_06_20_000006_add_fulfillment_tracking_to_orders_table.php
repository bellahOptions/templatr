<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fulfilment bookkeeping on the order itself.
 *
 * A crash between "marked paid" and "credited the authors" used to leave an
 * order silently unfulfilled with no way to find it. These columns make that
 * state visible (`fulfillment_status`), countable (`fulfillment_attempts`) and
 * recoverable (`orders:recover-fulfillment`).
 *
 * Existing paid orders are backfilled as already fulfilled, because their
 * authors were credited inline by the previous code path — re-running
 * fulfilment for them would double-credit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'fulfillment_status')) {
                $table->string('fulfillment_status', 32)->default('pending')->after('paid_at');
            }

            if (! Schema::hasColumn('orders', 'fulfillment_attempts')) {
                $table->unsignedInteger('fulfillment_attempts')->default(0)->after('fulfillment_status');
            }

            if (! Schema::hasColumn('orders', 'fulfillment_error')) {
                $table->text('fulfillment_error')->nullable()->after('fulfillment_attempts');
            }

            if (! Schema::hasColumn('orders', 'fulfilled_at')) {
                $table->timestamp('fulfilled_at')->nullable()->after('fulfillment_error');
            }

            if (! Schema::hasColumn('orders', 'fulfillment_notified_at')) {
                $table->timestamp('fulfillment_notified_at')->nullable()->after('fulfilled_at');
            }
        });

        // Legacy paid orders were fulfilled by the old synchronous code path.
        DB::table('orders')
            ->where('payment_status', 'paid')
            ->update([
                'fulfillment_status' => 'fulfilled',
                'fulfilled_at' => DB::raw('COALESCE(paid_at, updated_at)'),
                'fulfillment_notified_at' => DB::raw('COALESCE(paid_at, updated_at)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'fulfillment_status',
                'fulfillment_attempts',
                'fulfillment_error',
                'fulfilled_at',
                'fulfillment_notified_at',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
