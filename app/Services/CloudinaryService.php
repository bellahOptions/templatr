<?php

namespace App\Services;

use Cloudinary\Cloudinary;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key' => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
            'url' => ['secure' => true],
        ]);
    }

    public function uploadImage(string $filePath, string $folder, int $width, int $height): string
    {
        $result = $this->cloudinary->uploadApi()->upload($filePath, [
            'folder' => $folder,
            'transformation' => [
                'width' => $width,
                'height' => $height,
                'crop' => 'fill',
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ],
        ]);

        return (string) $result['secure_url'];
    }

    public function uploadVideo(string $filePath, string $folder): string
    {
        $result = $this->cloudinary->uploadApi()->upload($filePath, [
            'folder' => $folder,
            'resource_type' => 'video',
        ]);

        return (string) $result['secure_url'];
    }

    public function deleteImage(string $publicId): void
    {
        $this->cloudinary->uploadApi()->destroy($publicId);
    }
}
