<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Inertia\Inertia;

class ShowContactController extends Controller
{
    public function __invoke(Contact $contact)
    {
        return Inertia::render('Contacts/Show', [
            'contact' => $contact,
        ]);
    }
} 