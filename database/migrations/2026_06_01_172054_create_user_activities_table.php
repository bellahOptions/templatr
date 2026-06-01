<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('activity_type'); // view, search, purchase, cart_add, wishlist, download
            $table->string('subject_type')->nullable(); // App\Models\Product
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('data')->nullable(); // JSON for extra data like search terms, categories
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['user_id', 'activity_type']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
