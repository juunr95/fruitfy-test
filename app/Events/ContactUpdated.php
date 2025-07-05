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

class ContactUpdated implements WebhookEventInterface
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Contact $contact,
        public array $oldData
    ) {}

    /**
     * Get the unique identifier for this event type
     */
    public function getEventIdentifier(): string
    {
        return 'contact.contact_updated';
    }

    /**
     * Get the webhook payload for this event
     */
    public function getWebhookPayload(): array
    {
        return [
            'contact' => $this->contact->toArray(),
            'old_data' => $this->oldData,
            'action' => 'updated',
            'changes' => $this->getChanges()
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
            'version' => '1.0',
            'has_changes' => !empty($this->getChanges())
        ];
    }

    /**
     * Get the changes between old and new data
     */
    private function getChanges(): array
    {
        $currentData = $this->contact->toArray();
        $changes = [];

        foreach ($this->oldData as $key => $oldValue) {
            if (isset($currentData[$key]) && $currentData[$key] !== $oldValue) {
                $changes[$key] = [
                    'from' => $oldValue,
                    'to' => $currentData[$key]
                ];
            }
        }

        return $changes;
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