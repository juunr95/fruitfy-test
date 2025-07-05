<?php

namespace App\Http\Actions;

use App\Models\Contact;
use App\Events\ContactUpdated;

class UpdateContactAction
{
    public function execute(Contact $contact, array $data): Contact
    {
        $oldData = $contact->toArray();
        
        $contact->update($data);

        event(new ContactUpdated($contact, $oldData));

        return $contact;
    }
} 