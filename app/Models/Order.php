<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'total_amount',
        'status', 'payment_method', 'payment_reference', 'payment_status',
        'guest_name', 'guest_email', 'guest_phone',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items');
    }

    /**
     * Get the customer display name (guest name or user name).
     */
    public function getCustomerNameAttribute(): string
    {
        return $this->guest_name ?? ($this->user?->name ?? 'Unknown');
    }

    /**
     * Get the customer email.
     */
    public function getCustomerEmailAttribute(): string
    {
        return $this->guest_email ?? ($this->user?->email ?? '');
    }

    /**
     * Get the customer phone.
     */
    public function getCustomerPhoneAttribute(): string
    {
        return $this->guest_phone ?? '';
    }
}
