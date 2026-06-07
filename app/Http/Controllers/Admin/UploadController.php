<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function chunk(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file',
            'upload_id' => ['required', 'string', 'regex:/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i'],
            'chunk_index' => 'required|integer|min:0|max:999',
            'total_chunks' => 'required|integer|min:1|max:500',
            'filename' => 'required|string|max:255',
        ]);

        $uploadId = $request->input('upload_id');
        $chunkIndex = (int) $request->input('chunk_index');
        $totalChunks = (int) $request->input('total_chunks');
        $filename = basename($request->input('filename'));
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $allowedExts = ['zip', 'rar', 'tar', 'gz', 'psd', 'ai', 'svg', 'mp3', 'wav', 'mp4', 'ttf', 'otf'];
        if (! in_array($ext, $allowedExts)) {
            return response()->json(['error' => 'File type not allowed.'], 422);
        }

        $chunkDir = 'products/chunks/'.$uploadId;
        Storage::disk('local')->makeDirectory($chunkDir);
        $request->file('file')->storeAs($chunkDir, 'chunk_'.$chunkIndex, 'local');

        if ($chunkIndex < $totalChunks - 1) {
            return response()->json([
                'done' => false,
                'progress' => (int) round(($chunkIndex + 1) / $totalChunks * 100),
            ]);
        }

        // All chunks received — assemble into final temp file
        $tempId = Str::uuid()->toString();
        $finalPath = 'products/temp/'.$tempId.'.'.$ext;

        Storage::disk('public')->makeDirectory('products/temp');
        $absPath = Storage::disk('public')->path($finalPath);

        $output = fopen($absPath, 'wb');
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = Storage::disk('local')->path($chunkDir.'/chunk_'.$i);
            if (! file_exists($chunkPath)) {
                fclose($output);
                Storage::disk('local')->deleteDirectory($chunkDir);
                @unlink($absPath);

                return response()->json(['error' => 'Upload incomplete, please retry.'], 422);
            }
            $chunk = fopen($chunkPath, 'rb');
            stream_copy_to_stream($chunk, $output);
            fclose($chunk);
        }
        fclose($output);

        Storage::disk('local')->deleteDirectory($chunkDir);

        $fileSize = filesize($absPath);

        session([
            'upload_temp_'.$tempId => [
                'path' => $finalPath,
                'original_name' => $filename,
                'size' => $fileSize,
            ],
        ]);

        return response()->json([
            'done' => true,
            'temp_id' => $tempId,
            'name' => $filename,
            'size' => $fileSize,
        ]);
    }
}
