<?php

namespace App\Services;

use App\Models\Webhook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WebhookService
{
    public function __construct(
        private int $timeout = 10,
        private int $retryDelay = 5
    ) {}

    /**
     * Trigger webhooks for a specific event
     */
    public function triggerWebhooks(string $event, array $payload, array $metadata = []): void
    {
        $webhooks = Webhook::where('is_active', true)->get();

        foreach ($webhooks as $webhook) {
            if ($webhook->shouldTriggerFor($event)) {
                $this->sendWebhook($webhook, $event, $payload, $metadata);
            }
        }
    }

    /**
     * Send webhook to specific URL
     */
    public function sendWebhook(Webhook $webhook, string $event, array $payload, array $metadata = []): bool
    {
        try {
            $body = $this->preparePayload($event, $payload, $metadata);
            $headers = $this->prepareHeaders($webhook, $body);

            $response = Http::withHeaders($headers)
                ->timeout($this->timeout)
                ->post($webhook->url, $body);

            $statusCode = $response->status();
            $webhook->updateLastTrigger($statusCode);

            if ($response->successful()) {
                $webhook->resetRetryCount();
                Log::info("Webhook sent successfully", [
                    'webhook_id' => $webhook->id,
                    'url' => $webhook->url,
                    'event' => $event,
                    'status' => $statusCode,
                    'metadata' => $metadata
                ]);
                return true;
            } else {
                $this->handleFailedWebhook($webhook, $statusCode, $response->body());
                return false;
            }

        } catch (Exception $e) {
            $this->handleFailedWebhook($webhook, 0, $e->getMessage());
            return false;
        }
    }

    /**
     * Prepare webhook payload
     */
    private function preparePayload(string $event, array $payload, array $metadata = []): array
    {
        $webhookPayload = [
            'event' => $event,
            'timestamp' => now()->toISOString(),
            'data' => $payload,
        ];

        if (!empty($metadata)) {
            $webhookPayload['metadata'] = $metadata;
        }

        return $webhookPayload;
    }

    /**
     * Prepare webhook headers
     */
    private function prepareHeaders(Webhook $webhook, array $body): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'ContactsApp-Webhook/1.0',
        ];

        // Add signature if secret is provided
        if ($webhook->secret) {
            $signature = hash_hmac('sha256', json_encode($body), $webhook->secret);
            $headers['X-Webhook-Signature'] = 'sha256=' . $signature;
        }

        return $headers;
    }

    /**
     * Handle failed webhook delivery
     */
    private function handleFailedWebhook(Webhook $webhook, int $statusCode, string $errorMessage): void
    {
        $webhook->incrementRetryCount();
        $webhook->updateLastTrigger($statusCode);

        Log::warning("Webhook delivery failed", [
            'webhook_id' => $webhook->id,
            'url' => $webhook->url,
            'status' => $statusCode,
            'error' => $errorMessage,
            'retry_count' => $webhook->retry_count
        ]);

        // Disable webhook if max retries exceeded
        if ($webhook->maxRetriesExceeded()) {
            $webhook->update(['is_active' => false]);
            Log::error("Webhook disabled due to max retries exceeded", [
                'webhook_id' => $webhook->id,
                'url' => $webhook->url
            ]);
        }
    }

    /**
     * Test webhook connectivity
     */
    public function testWebhook(Webhook $webhook): array
    {
        $testPayload = [
            'test' => true,
            'message' => 'This is a test webhook from ContactsApp'
        ];

        try {
            $body = $this->preparePayload('webhook.test', $testPayload, ['test' => true]);
            $headers = $this->prepareHeaders($webhook, $body);

            $response = Http::withHeaders($headers)
                ->timeout($this->timeout)
                ->post($webhook->url, $body);

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'response' => $response->body(),
                'headers' => $response->headers(),
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'status' => 0,
                'response' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retry failed webhooks
     */
    public function retryFailedWebhooks(): int
    {
        $failedWebhooks = Webhook::where('is_active', true)
            ->where('retry_count', '>', 0)
            ->where('retry_count', '<', 3)
            ->where('last_triggered_at', '<', now()->subMinutes($this->retryDelay))
            ->get();

        $retryCount = 0;
        foreach ($failedWebhooks as $webhook) {
            // You would need to store the original event and payload to retry
            // For now, we'll send a retry notification
            if ($this->sendWebhook($webhook, 'webhook.retry', ['webhook_id' => $webhook->id], ['retry_attempt' => true])) {
                $retryCount++;
            }
        }

        return $retryCount;
    }
} 