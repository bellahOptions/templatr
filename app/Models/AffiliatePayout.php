<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliatePayout extends Model
{
    const COIN_VALUE = 200; // 1 coin = ₦200

    protected $fillable = [
        'user_id', 'coins_redeemed', 'amount', 'payment_method',
        'payment_details', 'status', 'notes', 'processed_at'
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public static function calculateAmount(int $coins): float
    {
        return $coins * self::COIN_VALUE;
    }

    public static function coinsToCurrency(int $coins, string $currency = 'NGN'): float
    {
        // 1 coin = ₦200 (or equivalent in other currencies)
        $rates = [
            'NGN' => 200,
            'USD' => 0.13,
            'GBP' => 0.10,
            'EUR' => 0.12,
            'KES' => 25,
            'GHS' => 2,
            'ZAR' => 2.5,
        ];
        return $coins * ($rates[$currency] ?? 200);
    }
}
