<?php

namespace App\Services\Webhook;

use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Fire a webhook event to all registered webhooks, or to a specific webhook.
     */
    public function fire(string $event, array $payload = [], ?Webhook $specificWebhook = null): void
    {
        if ($specificWebhook) {
            // Fire to a specific webhook (for testing)
            if ($specificWebhook->is_active) {
                $this->send($specificWebhook, $event, $payload);
            }
            return;
        }

        $webhooks = Webhook::where('is_active', true)->get();

        foreach ($webhooks as $webhook) {
            if (!$webhook->shouldFire($event)) {
                continue;
            }

            $this->send($webhook, $event, $payload);
        }
    }

    /**
     * Send webhook payload to a specific webhook endpoint.
     */
    public function send(Webhook $webhook, string $event, array $payload): void
    {
        $data = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'payload' => $payload,
        ];

        $jsonPayload = json_encode($data);
        $signature = $webhook->generateSignature($jsonPayload);

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'Templatr-Webhook/1.0',
            'X-Webhook-Event' => $event,
        ];

        if (!empty($signature)) {
            $headers['X-Webhook-Signature'] = $signature;
        }

        $log = WebhookLog::create([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'status' => 'pending',
            'request_payload' => $data,
        ]);

        try {
            $response = Http::timeout(15)
                ->withHeaders($headers)
                ->post($webhook->url, $data);

            $log->update([
                'status' => $response->successful() ? 'success' : 'failed',
                'response' => [
                    'body' => $response->body(),
                    'headers' => $response->headers(),
                ],
                'response_code' => $response->status(),
            ]);

            if (!$response->successful()) {
                Log::warning('Webhook delivery failed', [
                    'webhook_id' => $webhook->id,
                    'event' => $event,
                    'url' => $webhook->url,
                    'status' => $response->status(),
                ]);
            }
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'response' => [
                    'error' => $e->getMessage(),
                ],
                'response_code' => 0,
            ]);

            Log::error('Webhook delivery exception', [
                'webhook_id' => $webhook->id,
                'event' => $event,
                'url' => $webhook->url,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Retry a failed webhook delivery.
     */
    public function retry(WebhookLog $log): bool
    {
        $webhook = $log->webhook;
        if (!$webhook) {
            return false;
        }

        $payload = $log->request_payload ?? [];

        $this->send(
            $webhook,
            $payload['event'] ?? $log->event,
            $payload['payload'] ?? []
        );

        return true;
    }
}

