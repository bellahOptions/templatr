<?php

namespace App\Services\Download;

/**
 * Immutable description of the file a buyer should receive.
 *
 * "path" is always an absolute filesystem path: either the cached watermarked
 * artifact or, when watermarking was not possible, the original stored file.
 * "cacheKey" (relative to the watermark disk) is set when the deliverable is a
 * cached artifact, so callers can page it out or evict it.
 */
class WatermarkResult
{
    public function __construct(
        public readonly string $path,
        public readonly string $fileName,
        public readonly bool $watermarked,
        public readonly string $strategy,
        public readonly ?string $cacheKey = null,
    ) {}

    public function size(): int
    {
        $size = @filesize($this->path);

        return $size === false ? 0 : $size;
    }
}
