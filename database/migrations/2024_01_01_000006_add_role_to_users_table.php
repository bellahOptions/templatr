<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('email'); // user, author, admin
            $table->string('avatar')->nullable()->after('password');
            $table->text('bio')->nullable()->after('avatar');
            $table->string('paypal_email')->nullable()->after('bio');
            $table->decimal('balance', 10, 2)->default(0)->after('paypal_email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'avatar', 'bio', 'paypal_email', 'balance']);
        });
    }
};
