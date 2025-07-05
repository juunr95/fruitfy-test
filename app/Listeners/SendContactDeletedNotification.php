<?php

namespace App\Listeners;

use App\Events\ContactDeleted;
use App\Mail\ContactDeletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendContactDeletedNotification
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
    public function handle(ContactDeleted $event): void
    {
        // Enviar email para o administrador
        Mail::to(config('mail.admin_email', 'admin@example.com'))
            ->send(new ContactDeletedNotification($event->contactData));
    }
}
