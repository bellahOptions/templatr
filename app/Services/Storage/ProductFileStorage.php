<?php

namespace App\Services\Storage;

use App\Models\Product;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Resolves where a product's purchased original lives.
 *
 * Originals are private by default (`private_assets`, outside the public
 * symlink). A legacy `public` location is still honoured for rows that have not
 * been migrated yet, so no existing product breaks — but every new upload goes
 * to the private disk and no path is ever reachable by direct URL.
 */
class ProductFileStorage
{
    public const DISK_PRIVATE = 'private_assets';

    public const DISK_PUBLIC_LEGACY = 'public';

    /**
     * The disk new originals should be written to.
     */
    public function privateDiskName(): string
    {
        return (string) config('filesystems.product_originals_disk', self::DISK_PRIVATE);
    }

    public function privateDisk(): Filesystem
    {
        return Storage::disk($this->privateDiskName());
    }

    /**
     * The disk that actually holds a given product's original.
     */
    public function diskNameFor(Product $product): ?string
    {
        $path = (string) $product->file_path;

        if ($path === '') {
            return null;
        }

        $declared = $product->storage_disk ?: null;

        if ($declared !== null && $this->diskHas($declared, $path)) {
            return $declared;
        }

        foreach ([$this->privateDiskName(), self::DISK_PUBLIC_LEGACY, 'local'] as $candidate) {
            if ($this->diskHas($candidate, $path)) {
                return $candidate;
            }
        }

        return $declared;
    }

    public function diskFor(Product $product): ?Filesystem
    {
        $name = $this->diskNameFor($product);

        return $name ? Storage::disk($name) : null;
    }

    /**
     * Absolute filesystem path of a product's original, or null when missing.
     */
    public function absolutePath(Product $product): ?string
    {
        $path = $this->safePath($product->file_path);

        if ($path === null) {
            return null;
        }

        $disk = $this->diskFor($product);

        if (! $disk || ! $disk->exists($path)) {
            return null;
        }

        $absolute = $disk->path($path);

        return is_file($absolute) ? $absolute : null;
    }

    public function exists(Product $product): bool
    {
        return $this->absolutePath($product) !== null;
    }

    /**
     * Write an uploaded/temporary file into private storage.
     *
     * Returns the stored relative path together with the disk it was written to.
     *
     * @return array{path: string, disk: string}
     */
    public function storePrivate(string $sourceAbsolutePath, string $originalName, string $extension): array
    {
        $diskName = $this->privateDiskName();
        $disk = Storage::disk($diskName);

        $base = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'asset';
        $directory = 'products/files';
        $target = $directory.'/'.$base.'-'.Str::random(6).'.'.strtolower($extension);

        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory, 0755, true);
        }

        $stream = @fopen($sourceAbsolutePath, 'rb');

        if ($stream === false) {
            throw new RuntimeException('Unable to read the uploaded file.');
        }

        try {
            $written = $disk->writeStream($target, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if (! $written) {
            throw new RuntimeException('Unable to store the uploaded file.');
        }

        return ['path' => $target, 'disk' => $diskName];
    }

    /**
     * Bind an already-stored relative path (e.g. a completed chunk assembly).
     *
     * When the file is on another disk it is streamed across, so a 100MB upload
     * never has to be held in memory.
     *
     * @return array{path: string, disk: string}
     */
    public function adoptIntoPrivate(string $sourceDisk, string $sourcePath, string $originalName, string $extension, ?string $targetDirectory = null): array
    {
        $sourcePath = $this->safePath($sourcePath);

        if ($sourcePath === null) {
            throw new RuntimeException('Invalid source path.');
        }

        $target = $this->storeFromDisk($sourceDisk, $sourcePath, $originalName, $extension, $targetDirectory);

        Storage::disk($sourceDisk)->delete($sourcePath);

        return $target;
    }

    /**
     * @return array{path: string, disk: string}
     */
    public function storeFromDisk(string $sourceDisk, string $sourcePath, string $originalName, string $extension, ?string $targetDirectory = null): array
    {        $destinationDiskName = $this->privateDiskName();
        $destinationDisk = Storage::disk($destinationDiskName);
        $source = Storage::disk($sourceDisk);

        $base = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'asset';
        $directory = $targetDirectory ?: 'products/files';
        $target = $directory.'/'.$base.'-'.Str::random(6).'.'.strtolower($extension);

        if (! $destinationDisk->exists($directory)) {
            $destinationDisk->makeDirectory($directory, 0755, true);
        }

        $stream = $source->readStream($sourcePath);

        if ($stream === false || $stream === null) {
            throw new RuntimeException('Unable to read the source file.');
        }

        try {
            $written = $destinationDisk->writeStream($target, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if (! $written) {
            throw new RuntimeException('Unable to write the file to private storage.');
        }

        $expected = (int) $source->size($sourcePath);
        $actual = (int) $destinationDisk->size($target);

        if ($expected > 0 && $actual !== $expected) {
            $destinationDisk->delete($target);

            throw new RuntimeException('Copied file size does not match the source; aborting.');
        }

        return ['path' => $target, 'disk' => $destinationDiskName];
    }

    /**
     * Reject absolute paths and parent-directory traversal before any disk call.
     */
    public function safePath(?string $path): ?string
    {
        $path = (string) $path;

        if ($path === '') {
            return null;
        }

        $normalized = str_replace('\\', '/', $path);

        if (str_contains($normalized, "\0")) {
            return null;
        }

        if (str_starts_with($normalized, '/') || preg_match('~^[A-Za-z]:/~', $normalized) === 1) {
            return null;
        }

        foreach (explode('/', $normalized) as $segment) {
            if ($segment === '..') {
                return null;
            }
        }

        return ltrim($normalized, '/');
    }

    protected function diskHas(string $diskName, string $path): bool
    {
        try {
            return Storage::disk($diskName)->exists($path);
        } catch (\Throwable) {
            return false;
        }
    }
}
