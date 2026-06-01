<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    protected $fillable = [
        'title', 'content', 'image_url', 'button_text', 'button_url',
        'trigger_type', 'trigger_delay', 'display_frequency',
        'target_pages', 'is_active', 'starts_at', 'ends_at',
        'display_count', 'click_count'
    ];

    protected function casts(): array
    {
        return [
            'target_pages' => 'array',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function incrementDisplay(): void
    {
        $this->increment('display_count');
    }

    public function incrementClick(): void
    {
        $this->increment('click_count');
    }
}
