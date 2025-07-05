<?php

namespace App\Events;

use App\Contracts\WebhookEventInterface;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactDeleted implements WebhookEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $contactData
    ) {}

    /**
     * Get the unique identifier for this event type
     */
    public function getEventIdentifier(): string
    {
        return 'contact.contact_deleted';
    }

    /**
     * Get the webhook payload for this event
     */
    public function getWebhookPayload(): array
    {
        return [
            'contact' => $this->contactData,
            'action' => 'deleted'
        ];
    }

    /**
     * Get additional metadata for the webhook
     */
    public function getWebhookMetadata(): array
    {
        return [
            'event_class' => self::class,
            'contact_id' => $this->contactData['id'] ?? null,
            'triggered_at' => now()->toISOString(),
            'version' => '1.0',
            'permanent_deletion' => true
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
} 