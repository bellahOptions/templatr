<?php

use App\Services\Storage\ProductFileStorage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Records which disk holds each product's purchased original, and copies
 * existing public originals into private storage.
 *
 * This is additive and non-destructive:
 *  - `storage_disk` is nullable; a null value keeps the legacy behaviour
 *    (resolve the file at read time across the known disks), so products whose
 *    copy failed still work.
 *  - Files are *copied* to private storage and never deleted here. Removing the
 *    public copy is left to the explicit, auditable `assets:privatize` command.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'storage_disk')) {
                $table->string('storage_disk', 32)->nullable()->after('file_path');
            }
        });

        $storage = app(ProductFileStorage::class);
        $privateDiskName = $storage->privateDiskName();

        if ($privateDiskName === 'public') {
            Log::warning('Product originals disk is configured as "public"; assets would remain publicly reachable.');

            return;
        }

        DB::table('products')
            ->whereNull('storage_disk')
            ->whereNotNull('file_path')
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($storage, $privateDiskName): void {
                foreach ($products as $product) {
                    $sourcePath = $storage->safePath($product->file_path);

                    if (! $sourcePath) {
                        continue;
                    }

                    // Already private (e.g. re-run): just record the disk.
                    if ($storage->privateDisk()->exists($sourcePath)) {
                        DB::table('products')->where('id', $product->id)->update([
                            'storage_disk' => $privateDiskName,
                        ]);

                        continue;
                    }

                    try {
                        $copied = $storage->storeFromDisk(
                            'public',
                            $sourcePath,
                            $product->original_file_name ?: basename($sourcePath),
                            strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'zip'),
                        );

                        DB::table('products')->where('id', $product->id)->update([
                            'file_path' => $copied['path'],
                            'storage_disk' => $copied['disk'],
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Could not privatize product original during migration; public copy left in place', [
                            'product_id' => $product->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'storage_disk')) {
                $table->dropColumn('storage_disk');
            }
        });
    }
};
