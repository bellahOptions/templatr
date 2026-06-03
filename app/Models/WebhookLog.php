<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    protected $fillable = [
        'webhook_id',
        'event',
        'status',
        'request_payload',
        'response',
        'response_code',
    ];

    protected function casts(): array
    {
        return [
            'request_payload' => 'array',
            'response' => 'array',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
