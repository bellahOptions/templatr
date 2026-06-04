<?php

namespace App\Services\Media;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key' => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true,
            ],
        ]);
    }

    /**
     * Upload an image to Cloudinary with optimization.
     */
    public function uploadImage(
        UploadedFile $file,
        string $folder = 'products',
        int $width = 600,
        int $height = 450,
        string $format = 'webp'
    ): ?string {
        try {
            $result = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => "templatr/{$folder}",
                    'resource_type' => 'image',
                    'format' => $format,
                    'transformation' => [
                        [
                            'width' => $width,
                            'height' => $height,
                            'crop' => 'fill',
                            'gravity' => 'auto',
                            'quality' => 'auto:best',
                        ],
                    ],
                ]
            );

            return $result['secure_url'] ?? null;
        } catch (\Exception $e) {
            Log::error('Cloudinary upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload a file (not image) to Cloudinary.
     */
    public function uploadFile(UploadedFile $file, string $folder = 'files'): ?string
    {
        try {
            $result = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => "templatr/{$folder}",
                    'resource_type' => 'auto',
                ]
            );

            return $result['secure_url'] ?? null;
        } catch (\Exception $e) {
            Log::error('Cloudinary file upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload a user avatar to Cloudinary.
     */
    public function uploadAvatar(UploadedFile $file): ?string
    {
        return $this->uploadImage($file, 'avatars', 200, 200);
    }

    /**
     * Delete a file from Cloudinary by URL.
     */
    public function deleteByUrl(string $url): bool
    {
        try {
            // Extract public ID from URL
            $publicId = $this->getPublicIdFromUrl($url);
            if (!$publicId) {
                return false;
            }

            $this->cloudinary->uploadApi()->destroy($publicId);
            return true;
        } catch (\Exception $e) {
            Log::error('Cloudinary delete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get an optimized image URL with transformations.
     */
    public function getImageUrl(string $url, int $width = 300, int $height = null): string
    {
        // If it's not a Cloudinary URL, return as-is
        if (!$this->isCloudinaryUrl($url)) {
            return $url;
        }

        try {
            return $this->cloudinary->image($url)
                ->secure()
                ->quality('auto')
                ->format('webp')
                ->width($width)
                ->when($height, fn($img) => $img->height($height))
                ->toUrl();
        } catch (\Exception $e) {
            return $url;
        }
    }

    /**
     * Get a thumbnail URL for an image.
     */
    public function getThumbnailUrl(string $url, int $width = 100, int $height = 100): string
    {
        return $this->getImageUrl($url, $width, $height);
    }

    /**
     * Check if a URL is from Cloudinary.
     */
    protected function isCloudinaryUrl(string $url): bool
    {
        return str_contains($url, 'cloudinary.com');
    }

    /**
     * Extract the public ID from a Cloudinary URL.
     */
    protected function getPublicIdFromUrl(string $url): ?string
    {
        if (!$this->isCloudinaryUrl($url)) {
            return null;
        }

        // Parse Cloudinary URL to get path
        $path = parse_url($url, PHP_URL_PATH);
        if (!$path) {
            return null;
        }

        // Remove extension
        $path = preg_replace('/\.[^.]+$/', '', $path);

        // Remove version prefix (v12345/) if present
        $path = preg_replace('#^/(?:v\d+/)?#', '', $path);

        return $path;
    }
}
