<?php

namespace App\Console\Commands;

use App\Services\Download\WatermarkManager;
use Illuminate\Console\Command;

class PruneWatermarkCache extends Command
{
    protected $signature = 'watermark:prune
                            {--days=0 : Only remove artifacts older than this many days (0 = all)}
                            {--dry-run : Report what would be removed without deleting}';

    protected $description = 'Remove cached watermarked downloads to reclaim disk space';

    public function handle(WatermarkManager $watermark): int
    {
        $days = (int) $this->option('days');
        $before = $watermark->cacheStats();

        $this->line('Cache: <info>'.$before['path'].'</info>');
        $this->line('Before: '.number_format($before['files']).' file(s), '.$this->human($before['bytes']));

        if ($this->option('dry-run')) {
            $this->comment('Dry run — nothing deleted.');

            return self::SUCCESS;
        }

        $removed = $watermark->prune($days);
        $after = $watermark->cacheStats();

        $this->info('Removed '.number_format($removed).' file(s).');
        $this->line('After:  '.number_format($after['files']).' file(s), '.$this->human($after['bytes']));

        return self::SUCCESS;
    }

    protected function human(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return round($bytes, 2).' '.$units[$index];
    }
}
