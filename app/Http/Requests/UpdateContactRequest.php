<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $contactId = $this->route('contact')->id;

        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', Rule::unique('contacts', 'email')->ignore($contactId)],
            'phone' => ['required', 'string', 'min:10', 'max:20', 'regex:/^[\d\s\(\)\-\+]+$/'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'email.unique' => 'Este e-mail já está em uso.',
            'phone.min' => 'O telefone deve ter pelo menos 10 dígitos.',
            'phone.regex' => 'O telefone deve conter apenas números, espaços, parênteses, hífens e símbolos de mais.',
        ];
    }
} 