<?php

namespace App\Listeners;

use App\Events\ContactCreated;
use App\Mail\ContactCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendContactCreatedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ContactCreated $event): void
    {
        // Enviar email para o administrador
        Mail::to(config('mail.admin_email', 'admin@example.com'))
            ->send(new ContactCreatedNotification($event->contact));
    }
}
