<?php

namespace App\Http\Actions;

use App\Models\Contact;
use App\Events\ContactDeleted;

class DeleteContactAction
{
    public function execute(Contact $contact): bool
    {
        $contactData = $contact->toArray();
        $deleted = $contact->delete();

        if ($deleted) {
            event(new ContactDeleted($contactData));
        }

        return $deleted;
    }
} 