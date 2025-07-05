<?php

namespace App\Events;

use App\Models\Contact;
use App\Contracts\WebhookEventInterface;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactCreated implements WebhookEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Contact $contact
    ) {}

    /**
     * Get the unique identifier for this event type
     */
    public function getEventIdentifier(): string
    {
        return 'contact.contact_created';
    }

    /**
     * Get the webhook payload for this event
     */
    public function getWebhookPayload(): array
    {
        return [
            'contact' => $this->contact->toArray(),
            'action' => 'created'
        ];
    }

    /**
     * Get additional metadata for the webhook
     */
    public function getWebhookMetadata(): array
    {
        return [
            'event_class' => self::class,
            'contact_id' => $this->contact->id,
            'triggered_at' => now()->toISOString(),
            'version' => '1.0'
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