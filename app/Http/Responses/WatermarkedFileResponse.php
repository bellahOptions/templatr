<?php

namespace App\Http\Responses;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a download in bounded chunks with full Range support.
 *
 * Why not BinaryFileResponse::deleteFileAfterSend()? Because a watermarked
 * artifact is reused by later buyers, and because this keeps peak memory at one
 * chunk instead of relying on the reader to slurp the whole file. Offering
 * Range support also lets interrupted 50 MB+ downloads resume instead of
 * restarting, which matters a great deal on metered Nigerian connections.
 */
class WatermarkedFileResponse extends StreamedResponse
{
    protected int $fileSize;

    protected int $rangeStart = 0;

    protected int $rangeEnd = 0;

    protected bool $isPartial = false;

    protected bool $isUnsatisfiable = false;

    public function __construct(
        protected string $filePath,
        protected string $downloadName,
        protected int $chunkBytes = 524288,
        string $contentType = 'application/octet-stream',
    ) {
        $size = @filesize($filePath);
        $this->fileSize = $size === false ? 0 : $size;
        $this->chunkBytes = max(65536, $chunkBytes);
        $this->rangeEnd = max(0, $this->fileSize - 1);

        parent::__construct(fn () => $this->stream(), 200, [
            'Content-Type' => $contentType,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'attachment; filename="'.addslashes($downloadName).'"',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Accept-Ranges' => 'bytes',
        ]);
    }

    public static function make(string $filePath, string $downloadName): self
    {
        return new self($filePath, $downloadName, (int) config('watermark.chunk_bytes', 524288));
    }

    /**
     * Resolve the Range request and lock the response headers.
     */
    public function prepare(Request $request): static
    {
        $this->parseRange((string) $request->headers->get('Range', ''));

        if ($this->isUnsatisfiable) {
            $this->headers->set('Content-Range', 'bytes */'.$this->fileSize);
            $this->setStatusCode(416);
        } elseif ($this->isPartial) {
            $this->headers->set('Content-Range', "bytes {$this->rangeStart}-{$this->rangeEnd}/{$this->fileSize}");
            $this->headers->set('Content-Length', (string) ($this->rangeEnd - $this->rangeStart + 1));
            $this->setStatusCode(206);
        } else {
            $this->headers->set('Content-Length', (string) $this->fileSize);
        }

        if ($request->isMethod('HEAD')) {
            $this->setContent(null);
        }

        return $this;
    }

    /**
     * Hand delivery over to the web server when the host exposes an internal
     * redirect directive. Not used by default on shared cPanel hosting.
     *
     * @return array{0: string, 1: string}|null header name and value
     */
    public function offloadHeaders(): ?array
    {
        $driver = (string) config('watermark.offload.driver', 'none');
        $prefix = (string) config('watermark.offload.prefix', '');

        if ($driver === 'none' || $prefix === '' || $this->isPartial || $this->isUnsatisfiable) {
            return null;
        }

        $internal = rtrim($prefix, '/').'/'.basename($this->filePath);

        return match ($driver) {
            'x-sendfile' => ['X-Sendfile', $this->filePath],
            'x-accel' => ['X-Accel-Redirect', $internal],
            'litespeed' => ['X-LiteSpeed-Location', $internal],
            default => null,
        };
    }

    protected function stream(): void
    {
        if ($this->isUnsatisfiable || $this->fileSize === 0) {
            return;
        }

        $handle = @fopen($this->filePath, 'rb');

        if ($handle === false) {
            return;
        }

        // Output buffering is deliberately left untouched: the test harness and
        // some deployments wrap streamed responses in their own buffer.
        ignore_user_abort(false);
        @set_time_limit(0);

        if ($this->rangeStart > 0) {
            fseek($handle, $this->rangeStart);
        }

        $remaining = $this->rangeEnd - $this->rangeStart + 1;

        while ($remaining > 0 && ! connection_aborted() && ! feof($handle)) {
            $read = (int) min($this->chunkBytes, $remaining);
            $buffer = fread($handle, $read);

            if ($buffer === false || $buffer === '') {
                break;
            }

            echo $buffer;
            flush();
            $remaining -= strlen($buffer);
        }

        fclose($handle);
    }

    protected function parseRange(string $header): void
    {
        if ($this->fileSize <= 0 || $header === '' || ! str_starts_with($header, 'bytes=')) {
            return;
        }

        // Multi-range requests are rare; serve the file whole rather than
        // building a multipart/byteranges body.
        if (str_contains($header, ',')) {
            return;
        }

        $spec = substr($header, 6);
        $parts = explode('-', $spec, 2);

        if (count($parts) !== 2) {
            return;
        }

        [$start, $end] = $parts;

        if ($start === '') {
            // Suffix range: last N bytes.
            if (! ctype_digit($end)) {
                return;
            }

            $length = (int) $end;

            if ($length <= 0) {
                $this->isUnsatisfiable = true;

                return;
            }

            $this->rangeStart = max(0, $this->fileSize - $length);
            $this->rangeEnd = $this->fileSize - 1;
        } else {
            if (! ctype_digit($start)) {
                return;
            }

            $this->rangeStart = (int) $start;
            $this->rangeEnd = ($end === '' || ! ctype_digit($end)) ? $this->fileSize - 1 : (int) $end;

            if ($this->rangeEnd >= $this->fileSize) {
                $this->rangeEnd = $this->fileSize - 1;
            }

            if ($this->rangeStart >= $this->fileSize) {
                $this->isUnsatisfiable = true;

                return;
            }

            if ($this->rangeStart > $this->rangeEnd) {
                $this->isUnsatisfiable = true;

                return;
            }
        }

        $this->isPartial = true;
    }

    public function setDownloadName(string $name): static
    {
        $this->downloadName = $name;
        $this->headers->set('Content-Disposition', 'attachment; filename="'.addslashes($name).'"');

        return $this;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function isPartial(): bool
    {
        return $this->isPartial;
    }
}
