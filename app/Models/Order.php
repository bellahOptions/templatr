<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'total_amount', 'currency',
        'status', 'payment_method', 'gateway', 'payment_reference',
        'gateway_transaction_id', 'payment_status', 'paid_at',
        'guest_name', 'guest_email', 'guest_phone', 'admin_note',
        'fulfillment_status', 'fulfillment_attempts', 'fulfillment_error',
        'fulfilled_at', 'fulfillment_notified_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'fulfillment_notified_at' => 'datetime',
        'fulfillment_attempts' => 'integer',
        'total_amount' => 'decimal:2',
    ];

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

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

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid->value;
    }

    /**
     * The provider this order was created against, falling back to the legacy
     * `payment_method` column for orders that predate the `gateway` column.
     */
    public function gatewayName(): ?string
    {
        return $this->gateway ?: $this->payment_method;
    }

    public function currencyCode(): string
    {
        return strtoupper((string) ($this->currency ?: 'NGN'));
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
