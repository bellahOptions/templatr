<?php

namespace App\Models;

use Database\Factories\SlideshowFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slideshow extends Model
{
    /** @use HasFactory<SlideshowFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'cta_text',
        'cta_url',
        'image_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
