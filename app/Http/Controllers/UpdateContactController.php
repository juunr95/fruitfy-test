<?php

namespace App\Http\Controllers;

use App\Http\Actions\UpdateContactAction;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Services\FeatureToggleService;
use App\Exceptions\FeatureDisabledException;
use Illuminate\Http\Request;

class UpdateContactController extends Controller
{
    public function __invoke(UpdateContactRequest $request, Contact $contact, UpdateContactAction $action)
    {
        try {
            // Check if feature is enabled
            FeatureToggleService::ensureCanUpdateContacts();
            
            $updatedContact = $action->execute($contact, $request->validated());
            
            return redirect()
                ->route('contacts.show', $updatedContact)
                ->with('success', "Contato '{$updatedContact->name}' foi atualizado com sucesso!");
                
        } catch (FeatureDisabledException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('warning', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Ocorreu um erro ao atualizar o contato. Tente novamente.');
        }
    }
} 