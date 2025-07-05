<?php

namespace App\Http\Controllers;

use App\Http\Filters\ContactPipeline;
use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IndexContactController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = Contact::query();

        // Apply filters using Pipeline pattern
        $pipeline = new ContactPipeline($request);
        $query = $pipeline->apply($query);

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $contacts = $query->paginate($perPage)
                         ->withQueryString();

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'filters' => $request->only(['search', 'sort_by', 'sort_direction', 'per_page']),
        ]);
    }
} 