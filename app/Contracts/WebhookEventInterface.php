<?php

namespace App\Contracts;

interface WebhookEventInterface
{
    /**
     * Get the unique identifier for this event type
     */
    public function getEventIdentifier(): string;

    /**
     * Get the webhook payload for this event
     */
    public function getWebhookPayload(): array;

    /**
     * Get additional metadata for the webhook
     */
    public function getWebhookMetadata(): array;
} 