<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id', 'referred_user_id', 'email', 'code',
        'status', 'coins_earned', 'commission_earned',
        'joined_at', 'purchased_at'
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'purchased_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function scopeByReferrer($query, $userId)
    {
        return $query->where('referrer_id', $userId);
    }

    public function scopeConverted($query)
    {
        return $query->whereIn('status', ['purchased', 'converted']);
    }

    public function awardCoins(int $coins): void
    {
        $this->increment('coins_earned', $coins);
        $this->referrer->increment('coins', $coins);
    }
}
