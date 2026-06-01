<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserActivity extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'session_id', 'ip_address', 'activity_type',
        'subject_type', 'subject_id', 'data'
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Record user activity
     */
    public static function record(
        string $type,
        ?Model $subject = null,
        ?User $user = null,
        array $data = []
    ): self {
        return static::create([
            'user_id' => $user?->id,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'activity_type' => $type,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'data' => $data,
        ]);
    }

    /**
     * Get product recommendations based on user activity
     */
    public static function getRecommendationsForUser(?User $user, int $limit = 8)
    {
        $query = Product::query()->where('is_published', true);

        if ($user) {
            // Get categories from user's past purchases
            $purchasedCategoryIds = \App\Models\OrderItem::whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'completed');
            })->join('products', 'order_items.product_id', '=', 'products.id')
              ->pluck('products.category_id')
              ->unique()
              ->toArray();

            // Get categories from recent views
            $viewedCategoryIds = static::where('user_id', $user->id)
                ->where('activity_type', 'view')
                ->where('subject_type', Product::class)
                ->latest()
                ->take(20)
                ->get()
                ->map(fn($a) => optional($a->subject)->category_id)
                ->filter()
                ->unique()
                ->toArray();

            // Get search terms
            $searchTerms = static::where('user_id', $user->id)
                ->where('activity_type', 'search')
                ->latest()
                ->take(10)
                ->get()
                ->pluck('data')
                ->map(fn($d) => $d['query'] ?? null)
                ->filter()
->toArray();

            // Get wishlisted product IDs
            $wishlistIds = \App\Models\Wishlist::where('user_id', $user->id)->pluck('product_id')->toArray();

            $allCategoryIds = array_unique(array_merge($purchasedCategoryIds, $viewedCategoryIds));

            if (!empty($allCategoryIds)) {
                $query->whereIn('category_id', $allCategoryIds);
            }

            if (!empty($searchTerms)) {
                $query->orWhere(function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $q->orWhere('title', 'LIKE', "%{$term}%")
                          ->orWhere('description', 'LIKE', "%{$term}%")
                          ->orWhere('tags', 'LIKE', "%{$term}%");
                    }
                });
            }

            // Exclude already purchased
            $purchasedIds = \App\Models\OrderItem::whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'completed');
            })->pluck('product_id')->toArray();

            $query->whereNotIn('id', $purchasedIds);

            // Prioritize similar to wishlisted
            if (!empty($wishlistIds)) {
                $query->orderByRaw("CASE WHEN id IN (" . implode(',', $wishlistIds) . ") THEN 0 ELSE 1 END");
            }
        } else {
            // Guest: use session-based activities
            $sessionId = session()->getId();
            $viewedIds = static::where('session_id', $sessionId)
                ->where('activity_type', 'view')
                ->where('subject_type', Product::class)
                ->latest()
                ->take(20)
                ->pluck('subject_id')
                ->filter()
                ->toArray();

            if (!empty($viewedIds)) {
                $viewedCategories = Product::whereIn('id', $viewedIds)->pluck('category_id')->unique()->toArray();
                if (!empty($viewedCategories)) {
                    $query->whereIn('category_id', $viewedCategories);
                }
            }

            $query->whereNotIn('id', $viewedIds);
        }

        return $query->inRandomOrder()->take($limit)->get();
    }
}
