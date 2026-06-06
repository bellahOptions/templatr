<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function image(Request $request, CloudinaryService $cloudinary): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'type' => 'required|in:thumbnail,preview',
        ]);

        $file = $request->file('image');
        $type = $request->input('type');
        $folder = $type === 'thumbnail' ? 'templatr/thumbnails' : 'templatr/previews';
        [$width, $height] = $type === 'thumbnail' ? [600, 450] : [1200, 900];

        try {
            $url = $cloudinary->uploadImage($file->getRealPath(), $folder, $width, $height);

            return response()->json(['url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Cloudinary upload failed.'], 500);
        }
    }

    public function video(Request $request, CloudinaryService $cloudinary): JsonResponse
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,mov|max:51200',
            'type' => 'required|in:thumbnail,preview',
        ]);

        $file = $request->file('video');
        $type = $request->input('type');
        $folder = $type === 'thumbnail' ? 'templatr/thumbnails' : 'templatr/previews';

        try {
            $url = $cloudinary->uploadVideo($file->getRealPath(), $folder);

            return response()->json(['url' => $url]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Cloudinary upload failed.'], 500);
        }
    }

    public function file(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:zip,rar,tar,gz,psd,ai,svg,mp3,wav,mp4,ttf,otf|max:102400',
        ]);

        $file = $request->file('file');
        $tempId = Str::uuid()->toString();
        $ext = $file->getClientOriginalExtension();
        $path = $file->storeAs('products/temp', $tempId.'.'.$ext, 'public');

        session([
            'upload_temp_'.$tempId => [
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ],
        ]);

        return response()->json([
            'temp_id' => $tempId,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);
    }
}
