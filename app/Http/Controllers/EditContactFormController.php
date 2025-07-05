<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Services\FeatureToggleService;
use App\Exceptions\FeatureDisabledException;
use Inertia\Inertia;
use Inertia\Response;

class EditContactFormController extends Controller
{
    public function __invoke(Contact $contact): Response
    {
        try {
            // Check if feature is enabled
            FeatureToggleService::ensureCanUpdateContacts();
            
            return Inertia::render('Contacts/Edit', [
                'contact' => $contact
            ]);
        } catch (FeatureDisabledException $e) {
            return redirect()
                ->route('contacts.index')
                ->with('warning', $e->getMessage());
        }
    }
} 