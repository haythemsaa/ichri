<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeamInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        return [
            'email' => 'required_without:phone|email|nullable',
            'phone' => 'required_without:email|string|nullable',
            'role' => 'required|in:admin,manager,employee,viewer',
            'spending_limit' => 'nullable|numeric|min:0',
            'permissions' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required_without' => 'Email ou téléphone est requis',
            'phone.required_without' => 'Email ou téléphone est requis',
            'role.required' => 'Le rôle est requis',
            'role.in' => 'Rôle invalide. Choisissez: admin, manager, employee, viewer',
        ];
    }
}
