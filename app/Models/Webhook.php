<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    protected $fillable = [
        'name',
        'url',
        'secret',
        'events',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class);
    }

    /**
     * Check if this webhook should fire for the given event.
     */
    public function shouldFire(string $event): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // If no specific events are set, fire for all events
        if (empty($this->events)) {
            return true;
        }

        return in_array($event, $this->events);
    }

    /**
     * Generate signature for webhook payload.
     */
    public function generateSignature(string $payload): string
    {
        if (empty($this->secret)) {
            return '';
        }

        return hash_hmac('sha256', $payload, $this->secret);
    }
}
