<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Immutable creator earnings ledger.
 *
 * One row per sold order item, enforced by a unique index on `order_item_id`:
 * a duplicated webhook, a concurrent callback or a fulfilment retry can never
 * create a second credit for the same sale.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('creator_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('gross_amount'); // minor units, currency-safe
            $table->unsignedBigInteger('platform_commission');
            $table->unsignedBigInteger('creator_amount');
            $table->string('currency', 3)->default('NGN');
            $table->string('status', 32)->default('credited'); // credited | reversed
            $table->string('reference', 64)->unique();
            $table->string('reversal_reason')->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();

            // A sale can only ever produce one earning row.
            $table->unique('order_item_id');

            $table->index(['creator_id', 'status']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('creator_earnings');
    }
};
