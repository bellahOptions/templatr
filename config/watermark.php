<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Purchase Notice
    |--------------------------------------------------------------------------
    |
    | The notice stamped onto every downloaded file. It is deliberately built
    | from a single "label" so the wording stays identical between the branded
    | filename, the injected LICENSE/WATERMARK files and the on-image mark.
    |
    */

    'label' => env('WATERMARK_LABEL', 'Purchased from www.templatr.site'),

    /*
    | The host shown inside the injected licence/watermark documents. Falls back
    | to the host part of APP_URL so production never advertises "localhost".
    */
    'site' => env('WATERMARK_SITE', 'www.templatr.site'),

    /*
    | Human readable product name used in the injected documents.
    */
    'brand' => env('WATERMARK_BRAND', 'Templatr'),

    /*
    |--------------------------------------------------------------------------
    | Cache / Artifact Storage
    |--------------------------------------------------------------------------
    |
    | Watermarked files are rendered once per product (the notice is identical
    | for every buyer, only the filename carries per-order data) and then served
    | from cache. This keeps CPU and disk I/O flat no matter how many concurrent
    | downloads a shared cPanel account is handling.
    |
    */

    'enabled' => (bool) env('WATERMARK_ENABLED', true),

    /*
    | Bumping this value invalidates every cached artifact, e.g. after changing
    | the wording above.
    */
    'version' => (int) env('WATERMARK_VERSION', 1),

    'disk' => env('WATERMARK_DISK', 'local'),

    'cache_path' => env('WATERMARK_CACHE_PATH', 'watermarked'),

    /*
    | Reuse a freshly generated artifact until it is this many seconds old. A
    | value of 0 renders the artifact on first request and serves it from the
    | cache afterwards (recommended). A warm window lets concurrent first-hits
    | collapse onto a single render instead of racing.
    */
    'cache_ttl' => (int) env('WATERMARK_CACHE_TTL', 0),

    /*
    | Hard ceiling for in-place watermarking. Anything larger is streamed to the
    | buyer unmodified rather than risking a shared-hosting memory/time abort.
    */
    'max_bytes' => (int) env('WATERMARK_MAX_BYTES', 128 * 1024 * 1024),

    /*
    | How long a worker may hold the render lock before another request assumes
    | the worker died and takes over.
    */
    'lock_seconds' => (int) env('WATERMARK_LOCK_SECONDS', 120),

    /*
    | Distinct cache lines kept on disk. Oldest are pruned first by
    | `php artisan watermark:prune`.
    */
    'max_cached_products' => (int) env('WATERMARK_MAX_CACHED', 500),

    /*
    |--------------------------------------------------------------------------
    | Filename Branding
    |--------------------------------------------------------------------------
    */

    'brand_filename' => (bool) env('WATERMARK_BRAND_FILENAME', true),

    'filename_suffix' => env('WATERMARK_FILENAME_SUFFIX', 'PURCHASED-FROM-www.templatr.site'),

    /*
    |--------------------------------------------------------------------------
    | Download Delivery
    |--------------------------------------------------------------------------
    |
    | "stream" copies the artifact in fixed-size chunks with bounded memory and
    | full Range support (resumable downloads). Set to "offload" only when the
    | web server exposes X-Sendfile/X-Accel-Redirect/X-LiteSpeed-Location and is
    | configured with the matching internal prefix — that hands the transfer to
    | the web server and frees the PHP worker immediately.
    |
    */

    'delivery' => env('WATERMARK_DELIVERY', 'stream'),

    'chunk_bytes' => (int) env('WATERMARK_CHUNK_BYTES', 512 * 1024),

    'offload' => [
        'driver' => env('WATERMARK_OFFLOAD_DRIVER', 'none'), // none|x-sendfile|x-accel|litespeed
        'prefix' => env('WATERMARK_OFFLOAD_PREFIX', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Concurrency Guards
    |--------------------------------------------------------------------------
    */

    /*
    | Concurrent download streams allowed per buyer account before returning
    | HTTP 429. Guards the account against download-accelerator abuse (a single
    | buyer opening dozens of parallel connections is what actually exhausts a
    | shared host, not 50 distinct buyers).
    */
    'max_concurrent_per_user' => (int) env('WATERMARK_MAX_CONCURRENT_PER_USER', 4),

    'concurrency_lock_seconds' => (int) env('WATERMARK_CONCURRENCY_LOCK_SECONDS', 300),

    'rate_limit_attempts' => (int) env('WATERMARK_RATE_LIMIT_ATTEMPTS', 30),

    'rate_limit_minutes' => (int) env('WATERMARK_RATE_LIMIT_MINUTES', 15),

    /*
    | Re-verify a paid order against the payment gateway at most once per this
    | many seconds. Zero verification round-trips are made on repeat downloads,
    | which removes the single biggest latency source in the download path.
    */
    'gateway_recheck_seconds' => (int) env('WATERMARK_GATEWAY_RECHECK_SECONDS', 86400),

];
