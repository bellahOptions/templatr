<?php

namespace App\Services\Media;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    protected ?Cloudinary $cloudinary = null;

    public function __construct()
    {
        $cloudName = config('services.cloudinary.cloud_name');
        $apiKey = config('services.cloudinary.api_key');
        $apiSecret = config('services.cloudinary.api_secret');

        if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
            Log::warning('Cloudinary not configured - set CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, and CLOUDINARY_API_SECRET');

            return;
        }

        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
            'url' => [
                'secure' => true,
            ],
        ]);
    }

    public function isAvailable(): bool
    {
        return $this->cloudinary !== null;
    }

    public function uploadImage(
        UploadedFile $file,
        string $folder = 'products',
        ?int $width = null,
        ?int $height = null,
        string $format = 'auto'
    ): ?string {
        if (! $this->isAvailable()) {
            Log::warning('CloudinaryService: Cannot upload image - not configured.');

            return null;
        }

        try {
            $options = [
                'folder' => "templatr/{$folder}",
                'resource_type' => 'image',
                'format' => $format,
                'quality' => 'auto:best',
            ];

            if ($width && $height) {
                $options['transformation'] = [
                    ['width' => $width, 'height' => $height, 'crop' => 'fill', 'gravity' => 'auto'],
                ];
            } elseif ($width) {
                $options['transformation'] = [
                    ['width' => $width, 'crop' => 'scale'],
                ];
            }

            $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), $options);

            return $result['secure_url'] ?? null;
        } catch (\Exception $e) {
            Log::error('Cloudinary upload failed: '.$e->getMessage());

            return null;
        }
    }

    public function uploadFile(UploadedFile $file, string $folder = 'files'): ?string
    {
        if (! $this->isAvailable()) {
            Log::warning('CloudinaryService: Cannot upload file - not configured.');

            return null;
        }

        try {
            $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => "templatr/{$folder}",
                'resource_type' => 'auto',
            ]);

            return $result['secure_url'] ?? null;
        } catch (\Exception $e) {
            Log::error('Cloudinary file upload failed: '.$e->getMessage());

            return null;
        }
    }

    public function uploadAvatar(UploadedFile $file): ?string
    {
        return $this->uploadImage($file, 'avatars', 200, 200);
    }

    public function delete(string $urlOrPublicId): bool
    {
        if (! $this->isAvailable()) {
            return false;
        }

        try {
            $publicId = $this->isCloudinaryUrl($urlOrPublicId)
                ? $this->getPublicIdFromUrl($urlOrPublicId)
                : $urlOrPublicId;

            if (! $publicId) {
                return false;
            }

            $this->cloudinary->uploadApi()->destroy($publicId);

            return true;
        } catch (\Exception $e) {
            Log::error('Cloudinary delete failed: '.$e->getMessage());

            return false;
        }
    }

    public function getImageUrl(string $url, int $width = 300, ?int $height = null): string
    {
        if (empty($url)) {
            return '';
        }

        if (! $this->isCloudinaryUrl($url)) {
            return $url;
        }

        $transformations = 'q_auto,f_auto';
        if ($width) {
            $transformations .= ",w_{$width}";
        }
        if ($height) {
            $transformations .= ",h_{$height},c_fill,g_auto";
        }

        return preg_replace(
            '/(\/image\/upload\/)(v?\d+\/)?/',
            '$1'.$transformations.'/$2',
            $url,
            1
        );
    }

    public function getThumbnailUrl(string $url, int $width = 100, int $height = 100): string
    {
        return $this->getImageUrl($url, $width, $height);
    }

    protected function isCloudinaryUrl(string $url): bool
    {
        return str_contains($url, 'cloudinary.com');
    }

    protected function getPublicIdFromUrl(string $url): ?string
    {
        if (! $this->isCloudinaryUrl($url)) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! $path) {
            return null;
        }

        $path = preg_replace('/\.[^.]+$/', '', $path);
        $path = preg_replace('#^/[^/]+/image/upload/#', '', $path);
        $path = preg_replace('#^v\d+/#', '', $path);

        return $path ?: null;
    }
}
