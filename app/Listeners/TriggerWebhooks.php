<?php

namespace App\Listeners;

use App\Services\WebhookService;
use App\Services\WebhookEventRegistry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class TriggerWebhooks implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private WebhookService $webhookService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $eventData = WebhookEventRegistry::handle($event);
        
        $this->webhookService->triggerWebhooks(
            $eventData['identifier'],
            $eventData['payload'],
            $eventData['metadata']
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(object $event, \Throwable $exception): void
    {
        \Log::error('Webhook listener failed', [
            'event' => get_class($event),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
} 