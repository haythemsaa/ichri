<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateLoyaltyRewardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:discount,product,cashback,free_delivery',
            'points_cost' => 'required|integer|min:1',
            'config' => 'nullable|array',
            'quantity_available' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la récompense est requis',
            'type.required' => 'Le type de récompense est requis',
            'type.in' => 'Type invalide. Choisissez: discount, product, cashback, free_delivery',
            'points_cost.required' => 'Le coût en points est requis',
            'points_cost.min' => 'Le coût doit être au moins 1 point',
            'valid_until.after' => 'La date de fin doit être après la date de début',
        ];
    }
}
