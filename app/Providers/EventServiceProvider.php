<?php

namespace App\Providers;

use App\Events\ContactCreated;
use App\Events\ContactUpdated;
use App\Events\ContactDeleted;
use App\Listeners\TriggerWebhooks;
use App\Listeners\SendContactCreatedNotification;
use App\Listeners\SendContactDeletedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ContactCreated::class => [
            TriggerWebhooks::class,
            SendContactCreatedNotification::class,
        ],
        ContactUpdated::class => [
            TriggerWebhooks::class,
        ],
        ContactDeleted::class => [
            TriggerWebhooks::class,
            SendContactDeletedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
} 