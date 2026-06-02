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
}
