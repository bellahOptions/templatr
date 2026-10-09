<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One immutable earning entry per sold order item.
 *
 * Amounts are stored in minor units (kobo) so a duplicated, retried or reversed
 * credit can always be reconciled with the author's balance to the cent.
 */
class CreatorEarning extends Model
{
    public const STATUS_CREDITED = 'credited';

    public const STATUS_REVERSED = 'reversed';

    protected $fillable = [
        'creator_id', 'order_id', 'order_item_id', 'product_id',
        'gross_amount', 'platform_commission', 'creator_amount',
        'currency', 'status', 'reference', 'reversal_reason',
        'reversed_at', 'credited_at',
    ];

    protected $casts = [
        'reversed_at' => 'datetime',
        'credited_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Major-unit (naira) representations, for display only.
     */
    public function gross(): float
    {
        return $this->gross_amount / 100;
    }

    public function commission(): float
    {
        return $this->platform_commission / 100;
    }

    public function creatorAmount(): float
    {
        return $this->creator_amount / 100;
    }
}
