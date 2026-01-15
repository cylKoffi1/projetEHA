<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaracteristiquesRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Règles de validation pour la mise à jour des caractéristiques.
     */
    public function rules(): array
    {
        return [
            'caracteristiques' => [
                'nullable',
                'array',
            ],
            'caracteristiques.*' => [
                'nullable',
            ],
            'unites_derivees' => [
                'nullable',
                'array',
            ],
            'unites_derivees.*' => [
                'nullable',
                'integer',
                'exists:unites_derivees,id',
            ],
        ];
    }

    /**
     * Messages de validation personnalisés.
     */
    public function messages(): array
    {
        return [
            'unites_derivees.*.exists' => 'L\'unité dérivée sélectionnée n\'existe pas.',
        ];
    }

    /**
     * Attributs personnalisés pour les messages d'erreur.
     */
    public function attributes(): array
    {
        return [
            'caracteristiques' => 'caractéristiques',
            'unites_derivees' => 'unités dérivées',
        ];
    }
}

