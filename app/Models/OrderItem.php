<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'price', 'author_earnings',
        'download_count', 'first_downloaded_at', 'last_downloaded_at',
        'download_token', 'download_token_expires_at',
    ];

    protected $casts = [
        'first_downloaded_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'download_token_expires_at' => 'datetime',
        'download_count' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if the item is available for download.
     */
    public function isDownloadable(): bool
    {
        if ($this->download_count >= 4) {
            return false;
        }

        if ($this->download_token_expires_at && now()->greaterThan($this->download_token_expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Get remaining downloads for display.
     */
    public function getRemainingDownloadsAttribute(): int
    {
        return max(0, 4 - $this->download_count);
    }
}
