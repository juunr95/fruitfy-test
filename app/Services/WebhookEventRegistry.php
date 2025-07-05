<?php

namespace App\Services;

use App\Contracts\WebhookEventInterface;
use InvalidArgumentException;

class WebhookEventRegistry
{
    private static array $eventHandlers = [];

    /**
     * Register an event handler
     */
    public static function register(string $eventClass, callable $handler): void
    {
        self::$eventHandlers[$eventClass] = $handler;
    }

    /**
     * Handle an event using registered handler
     */
    public static function handle(object $event): array
    {
        $eventClass = get_class($event);

        // If event implements WebhookEventInterface, use it directly
        if ($event instanceof WebhookEventInterface) {
            return [
                'identifier' => $event->getEventIdentifier(),
                'payload' => $event->getWebhookPayload(),
                'metadata' => $event->getWebhookMetadata()
            ];
        }

        // Try to find registered handler
        if (isset(self::$eventHandlers[$eventClass])) {
            return self::$eventHandlers[$eventClass]($event);
        }

        // Fallback to generic handler
        return self::genericHandler($event);
    }

    /**
     * Get event identifier from class name
     */
    public static function getEventIdentifier(object $event): string
    {
        if ($event instanceof WebhookEventInterface) {
            return $event->getEventIdentifier();
        }

        // Convert class name to dot notation identifier
        $className = class_basename($event);
        $identifier = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
        
        return "contact.{$identifier}";
    }

    /**
     * Generic handler for events without specific handler
     */
    private static function genericHandler(object $event): array
    {
        $identifier = self::getEventIdentifier($event);
        $payload = [];
        $metadata = [
            'event_class' => get_class($event),
            'handled_by' => 'generic_handler'
        ];

        // Try to extract common properties
        if (property_exists($event, 'contact') && $event->contact) {
            $payload['contact'] = method_exists($event->contact, 'toArray') 
                ? $event->contact->toArray() 
                : (array) $event->contact;
        }

        if (property_exists($event, 'contactData') && $event->contactData) {
            $payload['contact'] = $event->contactData;
        }

        if (property_exists($event, 'oldData') && $event->oldData) {
            $payload['old_data'] = $event->oldData;
        }

        return [
            'identifier' => $identifier,
            'payload' => $payload,
            'metadata' => $metadata
        ];
    }

    /**
     * Get all registered handlers
     */
    public static function getRegisteredHandlers(): array
    {
        return self::$eventHandlers;
    }

    /**
     * Clear all registered handlers (useful for testing)
     */
    public static function clear(): void
    {
        self::$eventHandlers = [];
    }
} 