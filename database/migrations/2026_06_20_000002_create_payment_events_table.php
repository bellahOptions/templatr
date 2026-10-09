<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Durable idempotency record for every inbound payment notification
 * (webhook or browser callback).
 *
 * The unique key on (gateway, event_id) makes replayed deliveries a no-op even
 * when they arrive concurrently: the second insert fails and the request is
 * answered with the recorded outcome instead of crediting the order twice.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('gateway', 32);
            $table->string('event_id', 191);
            $table->string('reference', 191)->nullable();
            $table->string('status', 32)->default('received');
            $table->string('outcome', 32)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->unique(['gateway', 'event_id']);
            $table->index('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
