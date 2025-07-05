<?php

namespace App\Http\Controllers;

use App\Http\Actions\DeleteContactAction;
use App\Models\Contact;
use App\Services\FeatureToggleService;
use App\Exceptions\FeatureDisabledException;

class DeleteContactController extends Controller
{
    public function __invoke(Contact $contact, DeleteContactAction $action)
    {
        try {
            // Check if feature is enabled
            FeatureToggleService::ensureCanDeleteContacts();
            
            $contactName = $contact->name;
            
            $deleted = $action->execute($contact);
            
            if ($deleted) {
                return redirect()
                    ->route('contacts.index')
                    ->with('success', "Contato '{$contactName}' foi excluído com sucesso!");
            }
            
            return redirect()
                ->back()
                ->with('error', 'Ocorreu um erro ao excluir o contato. Tente novamente.');
                
        } catch (FeatureDisabledException $e) {
            return redirect()
                ->back()
                ->with('warning', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ocorreu um erro ao excluir o contato. Tente novamente.');
        }
    }
} 