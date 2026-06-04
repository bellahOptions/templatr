<?php

namespace App\Helpers;

use App\Services\Media\CloudinaryService;

class CloudinaryHelper
{
    protected static ?CloudinaryService $service = null;

    protected static function service(): CloudinaryService
    {
        if (self::$service === null) {
            self::$service = app(CloudinaryService::class);
        }
        return self::$service;
    }

    /**
     * Get an optimized Cloudinary image URL.
     */
    public static function imageUrl(?string $url, int $width = 300, int $height = null): string
    {
        if (empty($url)) {
            return '';
        }

        return self::service()->getImageUrl($url, $width, $height);
    }

    /**
     * Get a thumbnail URL.
     */
    public static function thumbnail(?string $url, int $size = 100): string
    {
        if (empty($url)) {
            return '';
        }

        return self::service()->getThumbnailUrl($url, $size, $size);
    }
}
