<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('preview_image')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable(); // image, template, audio, video, font, plugin, etc.
            $table->integer('file_size')->nullable(); // in bytes
            $table->integer('download_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->json('tags')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('version')->nullable();
            $table->text('requirements')->nullable();
            $table->json('compatible_browsers')->nullable();
            $table->json('includes')->nullable();
            $table->json('columns')->nullable();
            $table->json('layouts')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
