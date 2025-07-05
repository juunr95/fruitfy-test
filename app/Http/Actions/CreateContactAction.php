<?php

namespace App\Http\Actions;

use App\Models\Contact;
use App\Events\ContactCreated;

class CreateContactAction
{
    public function execute(array $data): Contact
    {
        $contact = Contact::create($data);

        event(new ContactCreated($contact));

        return $contact;
    }
} 