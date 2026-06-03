<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password', 'role', 'avatar', 'bio', 'paypal_email', 'balance', 'referral_code', 'coins', 'pending_commission'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'coins' => 'integer',
            'pending_commission' => 'decimal:2',
        ];
    }
    
    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateReferralCode();
            }
        });
    }

    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    public function getReferralLinkAttribute(): string
    {
        return route('register', ['ref' => $this->referral_code]);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function affiliatePayouts(): HasMany
    {
        return $this->hasMany(AffiliatePayout::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAuthor(): bool
    {
        return $this->role === 'author' || $this->role === 'admin';
    }

    public function scopeAuthors($query)
    {
        return $query->whereIn('role', ['author', 'admin']);
    }

    // =====================
    // Email Verification
    // =====================

    public function emailVerifications(): HasMany
    {
        return $this->hasMany(EmailVerification::class);
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function markEmailAsVerified(): void
    {
        $this->update(['email_verified_at' => now()]);
    }

    // =====================
    // Admin 2FA
    // =====================

    public function admin2faTokens(): HasMany
    {
        return $this->hasMany(Admin2faToken::class);
    }

    /**
     * Award coins to referrer when referred user makes a purchase
     */
    public function awardReferralCoins(int $coins = 10): void
    {
        $this->increment('coins', $coins);
    }

    /**
     * Get coin value in Naira
     */
    public function getCoinValueInNaira(int $coins = null): float
    {
        return ($coins ?? $this->coins) * AffiliatePayout::COIN_VALUE;
    }
}

