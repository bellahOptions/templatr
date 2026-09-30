<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cached watermarked downloads are reused across buyers; prune the oldest
// artifacts weekly (Namecheap's cPanel cron runs `php artisan schedule:run`).
Schedule::command('watermark:prune --days=30')->weekly()->withoutOverlapping();
