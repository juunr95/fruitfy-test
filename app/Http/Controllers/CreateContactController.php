<?php

namespace App\Http\Controllers;

use App\Http\Actions\CreateContactAction;
use App\Http\Requests\CreateContactRequest;
use App\Services\FeatureToggleService;
use App\Exceptions\FeatureDisabledException;

class CreateContactController extends Controller
{
    public function __invoke(CreateContactRequest $request, CreateContactAction $action)
    {
        try {
            // Check if feature is enabled
            FeatureToggleService::ensureCanCreateContacts();
            
            $contact = $action->execute($request->validated());
            
            return redirect()
                ->route('contacts.show', $contact)
                ->with('success', "Contato '{$contact->name}' foi criado com sucesso!");
                
        } catch (FeatureDisabledException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('warning', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ocorreu um erro ao criar o contato. Tente novamente.');
        }
    }
}
