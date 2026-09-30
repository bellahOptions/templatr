<?php

namespace App\Services\Download;

use App\Models\OrderItem;
use App\Models\Product;

class DownloadAuthorization
{
    public bool $isAuthorized = false;

    public ?OrderItem $orderItem = null;

    public Product $product;

    public bool $isAdminBypass = false;

    /**
     * Token identifying this request's slot in the per-account concurrency
     * guard. Released when the download stream has finished.
     */
    public ?string $slotToken = null;

    public ?string $concurrencyKey = null;
}
