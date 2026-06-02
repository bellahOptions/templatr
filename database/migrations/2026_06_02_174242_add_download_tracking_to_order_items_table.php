<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Download tracking for secure, limited downloads
            $table->integer('download_count')->default(0)->after('author_earnings');
            $table->timestamp('first_downloaded_at')->nullable()->after('download_count');
            $table->timestamp('last_downloaded_at')->nullable()->after('first_downloaded_at');
            $table->string('download_token', 64)->nullable()->unique()->after('last_downloaded_at');
            $table->timestamp('download_token_expires_at')->nullable()->after('download_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'download_count',
                'first_downloaded_at',
                'last_downloaded_at',
                'download_token',
                'download_token_expires_at',
            ]);
        });
    }
};
