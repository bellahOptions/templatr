<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Idempotency record for one inbound payment notification.
 *
 * `event_id` is the provider's own identifier for the notification (Paystack
 * event id / Flutterwave transaction id) or, when a provider does not supply
 * one, a stable hash of the authenticated payload.
 */
class PaymentEvent extends Model
{
    protected $fillable = [
        'order_id', 'gateway', 'event_id', 'reference', 'status', 'outcome', 'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
