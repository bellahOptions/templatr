<?php

namespace App\Console\Commands;

use App\Services\Storage\ProductFileStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Deletes abandoned upload staging: chunk directories whose upload never
 * completed, and private temp files no product ever adopted.
 *
 * Safe by construction: only `products/chunks/**` and `products/temp/**` on the
 * private disk are touched, and only entries older than the TTL.
 */
class PruneUploadsCommand extends Command
{
    protected $signature = 'uploads:prune
                            {--hours=24 : Age in hours after which staging data is removed}
                            {--dry-run : Report what would be removed without deleting}';

    protected $description = 'Delete abandoned chunk and temporary upload data';

    public function handle(ProductFileStorage $storage): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subHours($hours)->getTimestamp();
        $disk = $storage->privateDisk();

        $removedDirectories = 0;
        $removedFiles = 0;

        foreach (['products/chunks', 'products/temp'] as $root) {
            if (! $disk->exists($root)) {
                continue;
            }

            foreach ($disk->directories($root) as $directory) {
                $files = $disk->files($directory);
                $newest = 0;

                foreach ($files as $file) {
                    $newest = max($newest, (int) $disk->lastModified($file));
                }

                if ($newest === 0 || $newest >= $cutoff) {
                    continue;
                }

                $this->line(($dryRun ? 'would remove ' : 'removing ').$directory);

                if (! $dryRun) {
                    $disk->deleteDirectory($directory);
                }

                $removedDirectories++;
            }

            // Loose files directly under the root (single-request staging).
            foreach ($disk->files($root) as $file) {
                if ((int) $disk->lastModified($file) >= $cutoff) {
                    continue;
                }

                $this->line(($dryRun ? 'would remove ' : 'removing ').$file);

                if (! $dryRun) {
                    $disk->delete($file);
                }

                $removedFiles++;
            }
        }

        Log::info('Upload staging pruned', [
            'directories' => $removedDirectories,
            'files' => $removedFiles,
            'dry_run' => $dryRun,
        ]);

        $this->info(($dryRun ? 'Would remove ' : 'Removed ').$removedDirectories.' directory(ies) and '.$removedFiles.' file(s).');

        return self::SUCCESS;
    }
}
