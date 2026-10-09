<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    /**
     * Downloads allowed per purchased item before the buyer must contact support.
     */
    public const MAX_DOWNLOADS = 4;

    protected $fillable = [
        'order_id', 'product_id', 'author_id', 'price', 'author_earnings',
        'platform_commission',
        'download_count', 'first_downloaded_at', 'last_downloaded_at',
        'download_token', 'download_token_expires_at',
    ];

    protected $casts = [
        'first_downloaded_at' => 'datetime',
        'last_downloaded_at' => 'datetime',
        'download_token_expires_at' => 'datetime',
        'download_count' => 'integer',
        'price' => 'decimal:2',
        'author_earnings' => 'decimal:2',
        'platform_commission' => 'decimal:2',
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
     * The creator this sale is attributed to, snapshotted at order creation.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function earning(): HasMany
    {
        return $this->hasMany(CreatorEarning::class);
    }

    /**
     * Check if the item is available for download.
     */
    public function isDownloadable(): bool
    {
        if ($this->download_count >= self::MAX_DOWNLOADS) {
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
        return max(0, self::MAX_DOWNLOADS - $this->download_count);
    }
}
