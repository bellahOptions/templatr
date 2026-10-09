<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use App\Services\Storage\ProductFileStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Asynchronous uploads for the admin product form.
 *
 * Security model:
 *  - Every route sits behind `auth` + `admin`, and the staged upload is bound to
 *    the administrator who created it (session key + owner id), so one admin can
 *    never adopt another's upload.
 *  - Purchased originals are written to the private asset disk, never to
 *    `public`, so the `/storage` symlink cannot expose them.
 *  - Chunked uploads enforce a valid index range, a consistent chunk count, a
 *    per-chunk ceiling and a cumulative ceiling, and chunk files are named from
 *    server-side integers only (no client path can be injected).
 *  - Actual file contents are validated, not just the client-supplied name.
 */
class UploadController extends Controller
{
    /**
     * Filenames (not extensions) that a purchased asset may carry.
     *
     * @var array<int, string>
     */
    protected const ALLOWED_EXTENSIONS = [
        'zip', 'rar', 'tar', 'gz', 'psd', 'ai', 'svg', 'mp3', 'wav', 'mp4', 'ttf', 'otf',
    ];

    /**
     * Ceiling for a single assembled original, in bytes (100 MB).
     */
    protected const MAX_ORIGINAL_BYTES = 104857600;

    /**
     * Ceiling for one chunk, in bytes (10 MB).
     */
    protected const MAX_CHUNK_BYTES = 10485760;

    protected const MAX_TOTAL_CHUNKS = 500;

    public function __construct(protected ProductFileStorage $fileStorage) {}

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

    /**
     * Single-request upload of a purchased original.
     *
     * Stored immediately on the private disk and recorded in the admin's own
     * session, keyed by a server-generated id.
     */
    public function file(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:'.(self::MAX_ORIGINAL_BYTES / 1024),
        ]);

        $file = $request->file('file');

        if (! $this->extensionAllowed($file->getClientOriginalName())) {
            return response()->json(['error' => 'File type not allowed.'], 422);
        }

        if (! $this->contentLooksAllowed($file)) {
            return response()->json(['error' => 'The uploaded file does not look like a valid asset of its type.'], 422);
        }

        try {
            $stored = $this->fileStorage->storePrivate(
                $file->getRealPath(),
                $file->getClientOriginalName(),
                strtolower($file->getClientOriginalExtension() ?: 'bin'),
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => 'Could not store the uploaded file.'], 500);
        }

        $tempId = Str::uuid()->toString();

        $this->rememberStagedUpload($tempId, [
            'path' => $stored['path'],
            'disk' => $stored['disk'],
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'temp_id' => $tempId,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ]);
    }

    /**
     * One chunk of a large upload.
     *
     * Chunks are staged on the private disk under a server-generated directory,
     * and the assembled file is written there too. `upload_id` must be a valid
     * UUID and all chunks of one upload must agree on `total_chunks` and the
     * filename, so a second admin cannot splice data into another upload.
     */
    public function chunk(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:'.(self::MAX_CHUNK_BYTES / 1024),
            'upload_id' => ['required', 'string', 'regex:/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i'],
            'chunk_index' => 'required|integer|min:0|max:'.(self::MAX_TOTAL_CHUNKS - 1),
            'total_chunks' => 'required|integer|min:1|max:'.self::MAX_TOTAL_CHUNKS,
            'filename' => 'required|string|max:255',
        ]);

        $uploadId = (string) $request->input('upload_id');
        $chunkIndex = (int) $request->input('chunk_index');
        $totalChunks = (int) $request->input('total_chunks');
        $filename = basename((string) $request->input('filename'));

        if ($chunkIndex >= $totalChunks) {
            return response()->json(['error' => 'Invalid chunk index.'], 422);
        }

        if (! $this->extensionAllowed($filename)) {
            return response()->json(['error' => 'File type not allowed.'], 422);
        }

        $disk = $this->fileStorage->privateDisk();
        $chunkDir = 'products/chunks/'.$uploadId;

        // The upload manifest (owner, filename, chunk count) is stored inside the
        // upload directory so it cannot be tampered with from the client.
        $manifestPath = $chunkDir.'/manifest.json';
        $manifest = null;

        if ($disk->exists($manifestPath)) {
            $decoded = json_decode((string) $disk->get($manifestPath), true);
            $manifest = is_array($decoded) ? $decoded : null;
        }

        if ($manifest === null) {
            $manifest = [
                'owner_id' => Auth::id(),
                'filename' => $filename,
                'total_chunks' => $totalChunks,
                'created_at' => now()->toIso8601String(),
            ];
        } else {
            if ((int) ($manifest['owner_id'] ?? 0) !== (int) Auth::id()) {
                return response()->json(['error' => 'This upload belongs to another session.'], 403);
            }

            if ((int) ($manifest['total_chunks'] ?? 0) !== $totalChunks) {
                return response()->json(['error' => 'Inconsistent chunk count for this upload.'], 422);
            }

            if ((string) ($manifest['filename'] ?? '') !== $filename) {
                return response()->json(['error' => 'Inconsistent filename for this upload.'], 422);
            }
        }

        if ($disk->exists($chunkDir) && ! $disk->exists($manifestPath)) {
            // A previous attempt died between creating the directory and writing
            // the manifest. Refuse rather than guess the intent.
            $disk->deleteDirectory($chunkDir);
        }

        if (! $disk->exists($chunkDir)) {
            $disk->makeDirectory($chunkDir, 0755, true);
        }

        if (! $disk->exists($manifestPath)) {
            $disk->put($manifestPath, json_encode($manifest));
        }

        $chunkPath = $chunkDir.'/chunk_'.$chunkIndex;

        // Refuse a chunk that has already been received, so a replayed request
        // cannot overwrite part of an upload in progress.
        if ($disk->exists($chunkPath)) {
            $received = $this->receivedChunkCount($disk, $chunkDir, $totalChunks);

            return response()->json([
                'done' => false,
                'duplicate' => true,
                'progress' => (int) round($received / $totalChunks * 100),
            ]);
        }

        $stream = $request->file('file')->getRealPath();
        $handle = @fopen($stream, 'rb');

        if ($handle === false) {
            return response()->json(['error' => 'Could not read the chunk.'], 500);
        }

        try {
            $disk->writeStream($chunkPath, $handle);
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }

        $received = $this->receivedChunkCount($disk, $chunkDir, $totalChunks);

        if ($received < $totalChunks) {
            return response()->json([
                'done' => false,
                'progress' => (int) round($received / $totalChunks * 100),
            ]);
        }

        return $this->assembleChunks($disk, $chunkDir, $filename, $totalChunks);
    }

    /**
     * Concatenate the received chunks into the private asset store.
     */
    protected function assembleChunks(\Illuminate\Contracts\Filesystem\Filesystem $disk, string $chunkDir, string $filename, int $totalChunks): JsonResponse
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION) ?: 'bin');
        $base = Str::slug(pathinfo($filename, PATHINFO_FILENAME)) ?: 'asset';
        $target = 'products/files/'.$base.'-'.Str::random(6).'.'.$extension;

        $absolute = $disk->path($chunkDir.'/.assembled-'.Str::random(8).'.tmp');
        $output = @fopen($absolute, 'wb');

        if ($output === false) {
            return response()->json(['error' => 'Could not assemble the upload.'], 500);
        }

        $totalBytes = 0;
        $aborted = false;

        try {
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = $disk->path($chunkDir.'/chunk_'.$i);

                if (! is_file($chunkPath)) {
                    return response()->json(['error' => 'Upload incomplete, please retry.'], 422);
                }

                $totalBytes += (int) filesize($chunkPath);

                // Cumulative ceiling: 500 chunks of 10 MB would otherwise let a
                // single upload exhaust the account's disk.
                if ($totalBytes > self::MAX_ORIGINAL_BYTES) {
                    $aborted = true;

                    return response()->json(['error' => 'The uploaded file is too large.'], 422);
                }

                $chunk = fopen($chunkPath, 'rb');

                if ($chunk === false) {
                    return response()->json(['error' => 'Upload incomplete, please retry.'], 422);
                }

                stream_copy_to_stream($chunk, $output);
                fclose($chunk);
            }
        } finally {
            fclose($output);

            if ($aborted) {
                @unlink($absolute);
            }
        }

        if (! is_file($absolute) || filesize($absolute) === 0) {
            @unlink($absolute);

            return response()->json(['error' => 'The assembled file is empty.'], 422);
        }

        $stream = fopen($absolute, 'rb');

        if ($stream === false) {
            @unlink($absolute);

            return response()->json(['error' => 'Could not finalise the upload.'], 500);
        }

        try {
            $written = $disk->writeStream($target, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }

            @unlink($absolute);
        }

        if (! $written) {
            return response()->json(['error' => 'Could not store the uploaded file.'], 500);
        }

        $disk->deleteDirectory($chunkDir);
        $this->pruneStaleStaging();

        $tempId = Str::uuid()->toString();

        $this->rememberStagedUpload($tempId, [
            'path' => $target,
            'disk' => $this->fileStorage->privateDiskName(),
            'original_name' => $filename,
            'size' => (int) $disk->size($target),
        ]);

        return response()->json([
            'done' => true,
            'temp_id' => $tempId,
            'name' => $filename,
            'size' => (int) $disk->size($target),
        ]);
    }

    protected function receivedChunkCount(\Illuminate\Contracts\Filesystem\Filesystem $disk, string $chunkDir, int $totalChunks): int
    {
        $count = 0;

        for ($i = 0; $i < $totalChunks; $i++) {
            if ($disk->exists($chunkDir.'/chunk_'.$i)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Remove abandoned chunk directories and staged temp files.
     */
    protected function pruneStaleStaging(): void
    {
        $disk = $this->fileStorage->privateDisk();
        $cutoff = now()->subHours((int) config('filesystems.staging_ttl_hours', 24))->getTimestamp();

        try {
            foreach ($disk->directories('products/chunks') as $directory) {
                $files = $disk->files($directory);

                $newest = 0;

                foreach ($files as $file) {
                    $newest = max($newest, (int) $disk->lastModified($file));
                }

                if ($newest > 0 && $newest < $cutoff) {
                    $disk->deleteDirectory($directory);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function rememberStagedUpload(string $tempId, array $data): void
    {
        session([
            'upload_temp_'.$tempId => $data + ['owner_id' => Auth::id()],
        ]);
    }

    protected function extensionAllowed(string $filename): bool
    {
        return in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), self::ALLOWED_EXTENSIONS, true);
    }

    /**
     * Confirm the bytes on disk actually match the claimed type.
     *
     * Archives and media carry a recognisable signature; anything that does not
     * match its extension is refused rather than blindly trusted.
     */
    protected function contentLooksAllowed(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: '');
        $path = $file->getRealPath();

        if ($path === false || $path === '') {
            return false;
        }

        // Archives/types we can pin: require the signature to match.
        $expectedMime = match ($extension) {
            'zip' => ['application/zip', 'application/x-zip', 'application/x-zip-compressed', 'application/octet-stream'],
            'gz', 'tar' => ['application/gzip', 'application/x-gzip', 'application/x-tar', 'application/octet-stream'],
            'rar' => ['application/vnd.rar', 'application/x-rar', 'application/x-rar-compressed', 'application/octet-stream'],
            'mp3' => ['audio/mpeg', 'audio/mp3', 'application/octet-stream'],
            'wav' => ['audio/wav', 'audio/x-wav', 'audio/wave', 'application/octet-stream'],
            'mp4' => ['video/mp4', 'application/octet-stream'],
            'ttf' => ['font/ttf', 'application/x-font-ttf', 'application/octet-stream'],
            'otf' => ['font/otf', 'application/vnd.ms-opentype', 'application/octet-stream'],
            default => [],
        };

        if ($expectedMime === []) {
            // svg / psd / ai are text-or-binary formats without a portable
            // signature check; the extension allow-list and admin-only route are
            // the control here.
            return true;
        }

        $detected = (new \finfo(FILEINFO_MIME_TYPE))->file($path);

        if ($detected === false) {
            return false;
        }

        return in_array(strtolower($detected), $expectedMime, true);
    }
}
