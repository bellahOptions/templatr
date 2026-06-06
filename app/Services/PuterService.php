<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PuterService
{
    /**
     * Detect whether a file_path value points to Puter cloud (a full HTTPS URL)
     * rather than a local storage path.
     */
    public function isPuterFile(string $path): bool
    {
        return str_starts_with($path, 'https://') || str_starts_with($path, 'http://');
    }

    /**
     * Confirm the Puter share URL is reachable — used in Layer 3 of the download
     * security manager instead of Storage::disk('public')->exists().
     */
    public function fileAccessible(string $url): bool
    {
        try {
            $status = Http::timeout(5)->head($url)->status();

            return in_array($status, [200, 301, 302, 307, 308]);
        } catch (\Exception $e) {
            Log::warning("Puter file accessibility check failed for {$url}: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Proxy-stream a Puter public file to the customer after all security layers
     * have passed. The raw Puter URL is never exposed to the browser.
     */
    public function streamDownload(string $url, string $filename): StreamedResponse
    {
        $response = Http::withOptions(['stream' => true])->get($url);

        if (! $response->successful()) {
            abort(502, 'Could not retrieve the file from cloud storage. Please contact support.');
        }

        $contentLength = $response->header('Content-Length');

        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ];

        if ($contentLength) {
            $headers['Content-Length'] = $contentLength;
        }

        return response()->stream(function () use ($response) {
            $body = $response->toPsrResponse()->getBody();
            while (! $body->eof()) {
                echo $body->read(65536);
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        }, 200, $headers);
    }
}
