<?php

namespace App\Services\Download;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

/**
 * Stamps the "Purchased from www.templatr.site" notice onto every deliverable.
 *
 * Design notes for shared cPanel hosting:
 *  - The notice text is identical for every buyer, so the rendered artifact is
 *    cached per product and reused. Only the filename is branded per order.
 *  - Rendering happens at most once; the request that renders does so behind a
 *    lock so parallel first-hits collapse onto a single render.
 *  - Nothing is loaded fully into memory: archives are copied with ZipArchive,
 *    raster images are capped by pixel count, everything is streamed on disk.
 *  - Any failure degrades to delivering the original file. A download is never
 *    blocked because the branding step failed.
 */
class WatermarkManager
{
    public function __construct(
        protected int $version,
        protected string $label,
        protected string $site,
        protected string $brand,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            (int) config('watermark.version', 1),
            (string) config('watermark.label', 'Purchased from www.templatr.site'),
            (string) config('watermark.site', 'www.templatr.site'),
            (string) config('watermark.brand', 'Templatr'),
        );
    }

    /**
     * The purchase notice shown to buyers and stamped onto every file.
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * Resolve the absolute path and branded filename for a buyer's download.
     */
    public function prepare(Product $product, ?OrderItem $orderItem = null): WatermarkResult
    {
        $source = $this->sourcePath($product);
        $ext = strtolower(pathinfo((string) $product->file_path, PATHINFO_EXTENSION) ?: 'zip');
        $fileName = $this->brandedFileName($product, $this->deliveryExtension($ext));

        if (! $source || ! is_file($source)) {
            return new WatermarkResult($source ?? '', $fileName, false, 'missing');
        }

        if (! config('watermark.enabled', true)) {
            return new WatermarkResult($source, $fileName, false, 'disabled');
        }

        $size = (int) @filesize($source);

        if ($size <= 0 || $size > (int) config('watermark.max_bytes', 134217728)) {
            return new WatermarkResult($source, $fileName, false, 'too-large');
        }

        $cacheKey = $this->artifactKey($product, $orderItem);
        $disk = $this->disk();

        try {
            $cached = $this->cachedArtifact($cacheKey, $source);

            if ($cached !== null) {
                return new WatermarkResult($disk->path($cached), $fileName, true, 'cache', $cached);
            }

            $fresh = $this->renderWithLock($cacheKey, $source, $product, $ext, $orderItem);

            if ($fresh !== null) {
                return new WatermarkResult($disk->path($fresh), $fileName, true, 'rendered', $fresh);
            }
        } catch (Throwable $e) {
            Log::warning('Watermark rendering failed, serving original file.', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }

        return new WatermarkResult($source, $fileName, false, 'fallback-original');
    }

    /**
     * Branded download name, e.g. "my-kit-PURCHASED-FROM-www.templatr.site.zip".
     */
    public function brandedFileName(Product $product, string $extension): string
    {
        $base = Str::slug((string) ($product->slug ?: $product->title)) ?: 'download';
        $base = Str::limit($base, 60, '');

        if (! config('watermark.brand_filename', true)) {
            return $base.'.'.$extension;
        }

        $suffix = (string) config('watermark.filename_suffix', 'PURCHASED-FROM-www.templatr.site');

        return $base.'-'.$suffix.'.'.$extension;
    }

    /**
     * The notice that is injected into every artifact.
     */
    public function notice(Product $product, ?OrderItem $orderItem = null): string
    {
        $orderNumber = $orderItem?->order?->order_number;
        // Guest orders carry guest_name; registered orders carry the account name.
        $buyer = $orderItem?->order?->customer_name;

        $lines = [
            $this->label,
            str_repeat('=', max(48, strlen($this->label))),
            '',
            'Product:  '.$product->title,
            'Item ID:  '.$product->id,
            'Licensed: '.now()->toDayDateTimeString(),
        ];

        if ($orderNumber) {
            $lines[] = 'Order:    '.$orderNumber;
        }

        if ($buyer) {
            $lines[] = 'Licensee: '.$buyer;
        }

        $lines = array_merge($lines, [
            '',
            'This file was purchased from '.$this->site.' and is licensed for the',
            'buyer named above. The commercial licence covers unlimited personal and',
            'client projects; reselling, redistributing or re-uploading the original',
            'files (or this archive) is not permitted.',
            '',
            'Every copy delivered by '.$this->site.' carries this notice. If you',
            'received this file from anywhere else, it was not licensed to you.',
            '',
            '© '.now()->year.' '.$this->brand.'. All rights reserved.',
        ]);

        return implode("\n", $lines)."\n";
    }

    /**
     * Watermarked artifact for a product, or null when unavailable.
     */
    public function artifactPath(Product $product): ?string
    {
        $source = $this->sourcePath($product);

        if (! $source || ! is_file($source)) {
            return null;
        }

        return $this->cachedArtifact($this->cacheKey($product), $source);
    }

    /**
     * Cache identity for a product's watermarked artifact.
     *
     * The artifact is keyed by order as well as product, because the injected
     * notice names the licensee and the order number. That keeps every recorded
     * copy traceable to the sale it came from, while repeat downloads of the
     * same purchase still reuse a single rendered file.
     */
    public function artifactKey(Product $product, ?OrderItem $orderItem = null): string
    {
        $fingerprint = implode('|', [
            'v'.$this->version,
            (string) $product->file_path,
            $orderItem ? 'order:'.$orderItem->order_id : 'shared',
        ]);

        return 'p'.$product->id.'-'.substr(hash('sha256', $fingerprint), 0, 20);
    }

    public function cacheKey(Product $product): string
    {
        return $this->artifactKey($product);
    }

    /**
     * @return array{files: int, bytes: int, path: string}
     */
    public function cacheStats(): array
    {
        $disk = $this->disk();
        $root = (string) config('watermark.cache_path', 'watermarked');
        $files = 0;
        $bytes = 0;

        if (! $disk->exists($root)) {
            return ['files' => 0, 'bytes' => 0, 'path' => $disk->path($root)];
        }

        foreach ($disk->allFiles($root) as $file) {
            $files++;
            $bytes += (int) $disk->size($file);
        }

        return ['files' => $files, 'bytes' => $bytes, 'path' => $disk->path($root)];
    }

    /**
     * Delete cached artifacts, optionally only those older than N days.
     *
     * @return int number of files removed
     */
    public function prune(int $olderThanDays = 0, ?Product $keep = null): int
    {
        $disk = $this->disk();
        $root = (string) config('watermark.cache_path', 'watermarked');
        $keepKey = $keep ? $this->cacheKey($keep) : null;
        $cutoff = $olderThanDays > 0 ? now()->subDays($olderThanDays)->getTimestamp() : null;
        $removed = 0;

        foreach ($disk->allFiles($root) as $file) {
            if ($keepKey && str_contains($file, $keepKey)) {
                continue;
            }

            if ($cutoff !== null && (int) $disk->lastModified($file) > $cutoff) {
                continue;
            }

            if ($disk->delete($file)) {
                $removed++;
            }
        }

        return $removed;
    }

    protected function renderWithLock(string $cacheKey, string $source, Product $product, string $ext, ?OrderItem $orderItem = null): ?string
    {
        $disk = $this->disk();
        $destination = $this->artifactPathFor($cacheKey, $ext);
        $lockPath = $this->cacheRoot().'/'.$cacheKey.'.lock';
        $lock = $disk->path($lockPath);
        $handle = @fopen($lock, 'c+');

        if ($handle === false) {
            return $this->render($source, $product, $ext, $destination, $orderItem);
        }

        $lockSeconds = max(10, (int) config('watermark.lock_seconds', 120));
        $deadline = microtime(true) + $lockSeconds;
        $holdsLock = false;

        try {
            if (flock($handle, LOCK_EX | LOCK_NB)) {
                $holdsLock = true;
            } else {
                // Another worker is rendering this same artifact. Wait for it to
                // land instead of duplicating the work, backing off progressively
                // so a burst of concurrent first-hits does not busy-poll the host.
                $waited = 0.0;
                $sleep = 0.25;

                while ($waited < $lockSeconds) {
                    usleep((int) ($sleep * 1_000_000));
                    $waited += $sleep;
                    $sleep = min($sleep * 1.5, 2.0);

                    if ($this->isUsableArtifact($destination, $source)) {
                        return $destination;
                    }

                    if (flock($handle, LOCK_EX | LOCK_NB)) {
                        $holdsLock = true;

                        break;
                    }
                }

                // Could not acquire, or the renderer died mid-write: produce our
                // own copy so the buyer is never left without a branded file.
                if ($this->isUsableArtifact($destination, $source)) {
                    return $destination;
                }
            }

            // Re-check: another worker may have finished while we waited.
            if ($this->isUsableArtifact($destination, $source)) {
                return $destination;
            }

            $result = $this->render($source, $product, $ext, $destination, $orderItem);

            if (microtime(true) > $deadline) {
                Log::warning('Watermark render exceeded its lock window.', ['product_id' => $product->id]);
            }

            return $result;
        } finally {
            if ($holdsLock) {
                @flock($handle, LOCK_UN);
            }

            @fclose($handle);

            // The lock file is only removed by the worker that held it, so a
            // waiter that gave up never unlinks the renderer's lock.
            if ($holdsLock) {
                @unlink($lock);
            }
        }
    }

    /**
     * Render the watermarked artifact into $destination. Returns the path or null.
     */
    protected function render(string $source, Product $product, string $ext, string $destination, ?OrderItem $orderItem = null): ?string
    {
        $disk = $this->disk();
        $directory = dirname($destination);

        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory, 0755, true);
        }

        $target = $disk->path($destination);
        $temp = dirname($target).'/.'.basename($target).'.'.Str::random(8).'.tmp';
        $notice = $this->notice($product, $orderItem);

        $made = match (true) {
            $ext === 'zip' => $this->markZip($source, $temp, $notice, $product),
            $ext === 'svg' => $this->wrapInZip($source, $temp, $notice, $product, $ext),
            in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'], true) => $this->markImage($source, $temp, $notice),
            $ext === 'pdf' => $this->markPdf($source, $temp, $notice, $product),
            default => $this->wrapInZip($source, $temp, $notice, $product, $ext),
        };

        if (! $made || ! is_file($temp) || filesize($temp) === 0) {
            @unlink($temp);

            return null;
        }

        @chmod($temp, 0644);

        if (! @rename($temp, $target)) {
            @unlink($temp);

            return null;
        }

        return $destination;
    }

    /**
     * Inject the notice into an existing archive (the common case).
     */
    protected function markZip(string $source, string $temp, string $notice, Product $product): bool
    {
        if (! class_exists(ZipArchive::class)) {
            return $this->wrapInZip($source, $temp, $notice, $product, 'zip');
        }

        if (! @copy($source, $temp)) {
            return false;
        }

        $zip = new ZipArchive;

        if ($zip->open($temp) !== true) {
            @unlink($temp);

            return false;
        }

        // Remove stale notices from an earlier watermark version.
        foreach ($this->knownNoticeNames() as $name) {
            if ($zip->locateName($name) !== false) {
                $zip->deleteName($name);
            }
        }

        $names = $this->noticeFileNames();
        $zip->addFromString($names['text'], $notice);
        $zip->addFromString($names['html'], $this->noticeHtml($notice, $product));
        $ok = $zip->close();

        return $ok || is_file($temp);
    }

    /**
     * Deliver a non-editable asset inside a branded archive with the notice.
     */
    protected function wrapInZip(string $source, string $temp, string $notice, Product $product, string $ext): bool
    {
        if (! class_exists(ZipArchive::class)) {
            return false;
        }

        $zip = new ZipArchive;

        if ($zip->open($temp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return false;
        }

        $original = $this->originalEntryName($product, $ext);

        if (! $zip->addFile($source, $original)) {
            $zip->close();
            @unlink($temp);

            return false;
        }

        $names = $this->noticeFileNames();
        $zip->addFromString($names['text'], $notice);
        $zip->addFromString($names['html'], $this->noticeHtml($notice, $product));

        return $zip->close() || is_file($temp);
    }

    /**
     * Burn a visible mark into raster images using GD.
     */
    protected function markImage(string $source, string $temp, string $notice): bool
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return false;
        }

        $info = @getimagesize($source);

        if ($info === false) {
            return false;
        }

        [$width, $height] = $info;

        // Guard the shared host: refuse pathological pixel counts.
        if ($width * $height > 40_000_000 || $width < 48 || $height < 48) {
            return false;
        }

        $data = @file_get_contents($source);

        if ($data === false) {
            return false;
        }

        $image = @imagecreatefromstring($data);

        if ($image === false) {
            return false;
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $text = $this->label;
        $font = 3;
        $charWidth = imagefontwidth($font);
        $lineHeight = imagefontheight($font);
        $textWidth = $charWidth * strlen($text);
        $padding = max(6, (int) round(min($width, $height) * 0.02));
        $black = imagecolorallocatealpha($image, 0, 0, 0, 45);
        $white = imagecolorallocatealpha($image, 255, 255, 255, 30);

        // Primary mark: bottom-right banner, always legible.
        $boxWidth = $textWidth + $padding * 2;
        $boxHeight = $lineHeight + $padding;
        $boxX = max(0, $width - $boxWidth - $padding);
        $boxY = max(0, $height - $boxHeight - $padding);

        imagefilledrectangle($image, $boxX, $boxY, $width - $padding, $height - $padding, $black);
        imagestring($image, $font, $boxX + $padding, $boxY + (int) ($padding / 2), $text, $white);

        // Secondary mark: faint diagonal tiling, survives cropping the corner.
        $tileStep = max(180, (int) round($width / 3));
        $faint = imagecolorallocatealpha($image, 255, 255, 255, 105);

        for ($y = (int) ($lineHeight * 2); $y < $height; $y += $tileStep) {
            for ($x = -$textWidth; $x < $width; $x += $tileStep) {
                imagestring($image, $font, $x, $y, $text, $faint);
            }
        }

        $ok = $this->writeImage($image, $temp, $info[2]);
        imagedestroy($image);

        return $ok;
    }

    protected function writeImage(\GdImage $image, string $temp, int $type): bool
    {
        return match ($type) {
            IMAGETYPE_PNG => imagepng($image, $temp, 6),
            IMAGETYPE_GIF => imagegif($image, $temp),
            IMAGETYPE_WEBP => function_exists('imagewebp') && imagewebp($image, $temp, 82),
            // JPEG covers jpg/jpeg/bmp and any other raster GD can decode.
            default => imagejpeg($image, $temp, 88),
        };
    }

    /**
     * Append a watermark layer to a PDF via an incremental update.
     *
     * No extra PHP extension is required and the original page content is left
     * byte-for-byte untouched, so the document still opens in any reader.
     */
    protected function markPdf(string $source, string $temp, string $notice, Product $product): bool
    {
        $pdf = @file_get_contents($source);

        if ($pdf === false || strlen($pdf) < 64) {
            return false;
        }

        // Encrypted PDFs cannot be safely extended.
        if (preg_match('~/Encrypt\b~', $pdf) === 1) {
            return false;
        }

        if (preg_match('~startxref\s+(\d+)~', $pdf, $m, PREG_OFFSET_CAPTURE) !== 1) {
            return false;
        }

        $previousXref = (int) $m[1][0];
        $rootObject = $this->findRootObject($pdf);

        if ($rootObject === null) {
            return false;
        }

        $maxObject = 0;

        if (preg_match_all('~(\d+)\s+\d+\s+obj~', $pdf, $objects) > 0) {
            $maxObject = max(array_map('intval', $objects[1]));
        }

        $stamp = $this->buildWatermarkStamp($product, $notice);

        if ($stamp === null) {
            return false;
        }

        $maskObject = $maxObject + 1;
        $imageObject = $maxObject + 2;
        $contentObject = $maxObject + 3;
        $pageObject = $maxObject + 4;

        $content = $this->pdfWatermarkContent();
        $pagesReference = $this->pdfPagesReference($pdf);
        $fontReference = $this->pdfFontResource($pdf);

        $body = $pdf."\n";
        $offsets = [];

        $offsets[$maskObject] = strlen($body);
        $body .= $maskObject." 0 obj\n<< /Type /XObject /Subtype /Image /Width {$stamp['width']} /Height {$stamp['height']}".
            ' /ColorSpace /DeviceGray /BitsPerComponent 8 /Filter /FlateDecode /Length '.strlen($stamp['mask'])." >>\nstream\n".
            $stamp['mask']."\nendstream\nendobj\n";

        $offsets[$imageObject] = strlen($body);
        $body .= $imageObject." 0 obj\n<< /Type /XObject /Subtype /Image /Width {$stamp['width']} /Height {$stamp['height']}".
            " /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /FlateDecode /SMask {$maskObject} 0 R /Length ".strlen($stamp['rgb'])." >>\nstream\n".
            $stamp['rgb']."\nendstream\nendobj\n";

        $offsets[$contentObject] = strlen($body);
        $body .= $contentObject." 0 obj\n<< /Length ".strlen($content)." >>\nstream\n".$content."\nendstream\nendobj\n";

        // Overlay page: /MediaBox comes before /Contents on purpose, because the
        // content stream scales itself against the width the layout engine
        // inherits from this box.
        $offsets[$pageObject] = strlen($body);
        $body .= $pageObject." 0 obj\n<< /Type /Page /Parent {$pagesReference} /MediaBox [0 0 595 842] ".
            "/Resources << /XObject << /Wm0 {$imageObject} 0 R >>{$fontReference} >> /Contents {$contentObject} 0 R >>\nendobj\n";

        $xrefOffset = strlen($body);
        $body .= "xref\n{$maskObject} 4\n";

        foreach ([$maskObject, $imageObject, $contentObject, $pageObject] as $object) {
            $body .= sprintf("%010d 00000 n \n", $offsets[$object]);
        }

        $body .= 'trailer'."\n<< /Size ".($pageObject + 1)." /Root {$rootObject} 0 R /Prev {$previousXref} >>\n".
            "startxref\n{$xrefOffset}\n%%EOF\n";

        if (@file_put_contents($temp, $body) === false) {
            return false;
        }

        return true;
    }

    protected function findRootObject(string $pdf): ?int
    {
        // Prefer the newest trailer that declares /Root.
        if (preg_match_all('~/Root\s+(\d+)\s+\d+\s+R~', $pdf, $matches) > 0) {
            return (int) end($matches[1]);
        }

        return null;
    }

    /**
     * Point the appended page at the document catalog's /Pages node.
     */
    protected function pdfPagesReference(string $pdf): string
    {
        $root = $this->findRootObject($pdf);

        if ($root !== null && preg_match('~(?:^|[^0-9])'.$root.'\s+0\s+obj(.*?)endobj~s', $pdf, $catalog) === 1) {
            if (preg_match('~/Pages\s+(\d+)\s+\d+\s+R~', $catalog[1], $pages) === 1) {
                return $pages[1].' 0 R';
            }
        }

        return '1 0 R';
    }

    /**
     * Reuse a bold font already embedded in the document; otherwise register the
     * standard Helvetica-Bold font for our overlay page only.
     */
    protected function pdfFontResource(string $pdf): string
    {
        if (preg_match('~/(F\d+|TT\d+)\s+(\d+)\s+\d+\s+R~', $pdf, $matches) === 1) {
            return ' /Font << /WA '.$matches[1].' '.$matches[2].' 0 R >>';
        }

        return ' /Font << /WA << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >> >>';
    }

    /**
     * Build the transparent stamp drawn onto the appended PDF page.
     *
     * The RGB channels and the alpha channel are emitted as two Flate-compressed
     * images, which keeps the overlay transparent in every reader without
     * pulling in an extension such as Imagick or FPDI.
     *
     * @return array{rgb: string, mask: string, width: int, height: int}|null
     */
    protected function buildWatermarkStamp(Product $product, string $notice): ?array
    {
        if (! function_exists('imagecreatetruecolor') || ! function_exists('gzcompress')) {
            return null;
        }

        $lines = $this->pdfNoticeLines($product, $notice);
        $font = 3;
        $charWidth = imagefontwidth($font);
        $lineHeight = imagefontheight($font) + 5;
        $maxChars = 0;

        foreach ($lines as $line) {
            $maxChars = max($maxChars, strlen($line));
        }

        $maxChars = min($maxChars, 100);
        $padding = 14;
        $width = $maxChars * $charWidth + $padding * 2;
        $height = count($lines) * $lineHeight + $padding * 2;
        $width = max(240, min(1200, $width));

        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        // Alpha 0 = opaque, 127 = fully transparent (GD convention).
        $sheet = imagecolorallocatealpha($image, 255, 255, 255, 12);
        imagefilledrectangle($image, 0, 0, $width, $height, $sheet);

        $frame = imagecolorallocatealpha($image, 190, 30, 30, 40);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $frame);

        $ink = imagecolorallocatealpha($image, 140, 15, 15, 15);
        $y = $padding;
        $rgb = '';
        $mask = '';

        foreach ($lines as $index => $line) {
            imagestring($image, $font, $padding, $y, $line, $ink);

            if ($index === 0) {
                // Headline: over-strike so it reads clearly on any background.
                imagestring($image, $font, $padding + 1, $y, $line, $ink);
                imagestring($image, $font, $padding, $y + 1, $line, $ink);
            }

            $y += $lineHeight;
        }

        for ($py = 0; $py < $height; $py++) {
            for ($px = 0; $px < $width; $px++) {
                $color = imagecolorat($image, $px, $py);
                $alpha = ($color >> 24) & 0x7F;
                $rgb .= chr(($color >> 16) & 0xFF).chr(($color >> 8) & 0xFF).chr($color & 0xFF);
                $mask .= chr((int) round((127 - $alpha) * 255 / 127));
            }
        }

        imagedestroy($image);

        return [
            'rgb' => gzcompress($rgb, 6),
            'mask' => gzcompress($mask, 6),
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * The overlay page content: a full-page watermark bar plus a margin note.
     */
    protected function pdfWatermarkContent(): string
    {
        $boxWidth = 345;
        $boxHeight = 34;
        $boxX = 595 - $boxWidth - 26;
        $boxY = 26;

        $content = "q\n/Wm0 Do\nQ\n";
        $content .= "q\n0.2 0.2 0.2 RG 0.6 w\n{$boxX} {$boxY} {$boxWidth} {$boxHeight} re S\nQ\n";
        $content .= "BT\n/WA 7 Tf\n0.35 g\n0.35 0.35 0.35 rg\n";
        $content .= '1 0 0 1 '.($boxX + 8).' '.($boxY + $boxHeight - 12)." Tm\n";
        $content .= '('.$this->pdfEscape($this->label).") Tj\n";
        $content .= "ET\n";

        return $content;
    }

    /**
     * @return array<int, string>
     */
    protected function pdfNoticeLines(Product $product, string $notice): array
    {
        $lines = [
            $this->pdfAscii($this->label),
            $this->pdfAscii('Product: '.$product->title),
            $this->pdfAscii('Item ID: '.$product->id.'   Licensed: '.now()->toDateString()),
        ];

        foreach (array_slice(explode("\n", $notice), 6) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $lines[] = $this->pdfAscii($line);
        }

        return array_slice($lines, 0, 12);
    }

    protected function pdfAscii(string $value): string
    {
        $value = Str::ascii($value);
        $value = preg_replace('~[^\x20-\x7E]~', ' ', $value) ?? '';

        return Str::limit(trim(preg_replace('~\s+~', ' ', $value) ?? ''), 96, '');
    }

    protected function pdfEscape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }

    protected function noticeFileNames(): array
    {
        return [
            'text' => 'WATERMARK.txt',
            'html' => 'PURCHASED-FROM-www.templatr.site.html',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function knownNoticeNames(): array
    {
        $names = array_values($this->noticeFileNames());

        // Names used by earlier watermark versions, cleaned up on re-render.
        return array_merge($names, [
            'LICENSE.txt',
            'PURCHASE-NOTICE.txt',
            'PURCHASE-NOTICE.html',
        ]);
    }

    protected function noticeHtml(string $notice, Product $product): string
    {
        $body = htmlspecialchars($notice, ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($product->title, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$title} — purchased from {$this->site}</title>
<style>
    body { background:#0f0f10; color:#f5f5f5; font-family:-apple-system,Segoe UI,Roboto,sans-serif; margin:0; padding:48px 24px; }
    .card { max-width:640px; margin:0 auto; background:#17171a; border:1px solid #2c2c31; border-radius:16px; padding:32px; }
    h1 { color:#FFC300; font-size:22px; margin:0 0 8px; }
    p.sub { color:#9a9aa2; margin:0 0 24px; }
    pre { white-space:pre-wrap; word-wrap:break-word; background:#101012; border:1px solid #2c2c31; border-radius:12px; padding:20px; font-size:13px; line-height:1.6; }
</style>
</head>
<body>
<div class="card">
    <h1>{$this->label}</h1>
    <p class="sub">Watermark record for <strong>{$title}</strong> · {$this->site}</p>
    <pre>{$body}</pre>
</div>
</body>
</html>
HTML;
    }

    protected function originalEntryName(Product $product, string $ext): string
    {
        $name = $product->original_file_name
            ? basename((string) $product->original_file_name)
            : (Str::slug((string) ($product->slug ?: $product->title)) ?: 'download').'.'.$ext;

        return $name !== '' ? $name : 'download.'.$ext;
    }

    /**
     * Non-editable formats are delivered as a branded archive.
     */
    protected function deliveryExtension(string $ext): string
    {
        return match ($ext) {
            'zip' => 'zip',
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp' => $ext,
            'pdf' => 'pdf',
            default => 'zip',
        };
    }

    protected function sourcePath(Product $product): ?string
    {
        if (! $product->file_path) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($product->file_path)) {
            return null;
        }

        return $disk->path($product->file_path);
    }

    protected function isUsableArtifact(string $relative, string $source): bool
    {
        $disk = $this->disk();

        if (! $disk->exists($relative)) {
            return false;
        }

        if ((int) $disk->size($relative) <= 0) {
            return false;
        }

        $ttl = (int) config('watermark.cache_ttl', 0);

        if ($ttl > 0 && (int) $disk->lastModified($relative) < now()->subSeconds($ttl)->getTimestamp()) {
            return false;
        }

        // Invalidate when the source product file changes on disk.
        $sourceModified = (int) @filemtime($source);
        $artifactModified = (int) $disk->lastModified($relative);

        return $sourceModified === 0 || $artifactModified >= $sourceModified;
    }

    protected function cachedArtifact(string $cacheKey, string $source): ?string
    {
        foreach (['zip', 'pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'] as $ext) {
            $relative = $this->artifactPathFor($cacheKey, $ext);

            if ($this->isUsableArtifact($relative, $source)) {
                return $relative;
            }
        }

        return null;
    }

    protected function artifactPathFor(string $cacheKey, string $ext): string
    {
        return $this->cacheRoot().'/'.substr($cacheKey, 0, 2).'/'.$cacheKey.'.'.$ext;
    }

    protected function cacheRoot(): string
    {
        return trim((string) config('watermark.cache_path', 'watermarked'), '/');
    }

    protected function disk(): Filesystem
    {
        return Storage::disk((string) config('watermark.disk', 'local'));
    }
}
