<?php

namespace App\Models;

use App\Helpers\CurrencyHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'user_id', 'title', 'slug', 'description',
        'price', 'sale_price', 'thumbnail', 'preview_image', 'file_path',
        'file_type', 'file_size', 'original_file_name', 'download_count', 'view_count',
        'is_featured', 'is_published', 'tags', 'demo_url', 'version',
        'requirements', 'features', 'compatible_browsers', 'includes', 'columns', 'layouts',
    ];

    protected $casts = [
        'tags' => 'array',
        'features' => 'array',
        'compatible_browsers' => 'array',
        'includes' => 'array',
        'columns' => 'array',
        'layouts' => 'array',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'price' => 'integer',
        'sale_price' => 'integer',
        'file_size' => 'float',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlistedBy(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->thumbnail) {
                    return null;
                }

                return str_starts_with($this->thumbnail, 'http')
                    ? $this->thumbnail
                    : Storage::disk('public')->url($this->thumbnail);
            }
        );
    }

    public function previewImageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->preview_image) {
                    return null;
                }

                return str_starts_with($this->preview_image, 'http')
                    ? $this->preview_image
                    : Storage::disk('public')->url($this->preview_image);
            }
        );
    }

    public function thumbnailIsVideo(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->thumbnail_url) {
                    return false;
                }

                $ext = strtolower(pathinfo(parse_url($this->thumbnail_url, PHP_URL_PATH), PATHINFO_EXTENSION));

                return in_array($ext, ['mp4', 'webm', 'mov', 'ogv']);
            }
        );
    }

    public function previewIsVideo(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->preview_image_url) {
                    return false;
                }

                $ext = strtolower(pathinfo(parse_url($this->preview_image_url, PHP_URL_PATH), PATHINFO_EXTENSION));

                return in_array($ext, ['mp4', 'webm', 'mov', 'ogv']);
            }
        );
    }

    public function currentPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->sale_price ?? $this->price
        );
    }

    public function formattedPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => CurrencyHelper::format($this->sale_price ?? $this->price)
        );
    }

    public function formattedOriginalPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->sale_price ? CurrencyHelper::format($this->price) : null
        );
    }

    public function averageRating(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->reviews()->avg('rating') ?? 0
        );
    }

    public function reviewCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->reviews()->count()
        );
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('file_type', $type);
    }
}
