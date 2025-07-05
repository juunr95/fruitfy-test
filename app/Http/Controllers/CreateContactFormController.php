<?php

namespace App\Http\Controllers;

use App\Services\FeatureToggleService;
use App\Exceptions\FeatureDisabledException;
use Inertia\Inertia;
use Inertia\Response;

class CreateContactFormController extends Controller
{
    public function __invoke(): Response
    {
        try {
            // Check if feature is enabled
            FeatureToggleService::ensureCanCreateContacts();
            
            return Inertia::render('Contacts/Create');
        } catch (FeatureDisabledException $e) {
            return redirect()
                ->route('contacts.index')
                ->with('warning', $e->getMessage());
        }
    }
} 