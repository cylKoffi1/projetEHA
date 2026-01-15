<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkflowActionRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Règles de validation pour l'action sur une étape de workflow.
     */
    public function rules(): array
    {
        return [
            'action_code' => [
                'required',
                'string',
                'max:30',
                'in:APPROUVER,REJETER,DELEGUER,COMMENTER'
            ],
            'commentaire' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'meta' => [
                'nullable',
                'array',
            ],
            'meta.delegate_to' => [
                'required_if:action_code,DELEGUER',
                'string',
                'max:150',
                'exists:acteur,code_acteur',
            ],
        ];
    }

    /**
     * Messages de validation personnalisés.
     */
    public function messages(): array
    {
        return [
            'action_code.required' => 'Le code d\'action est requis.',
            'action_code.in' => 'Le code d\'action doit être l\'un des suivants : APPROUVER, REJETER, DELEGUER, COMMENTER.',
            'commentaire.max' => 'Le commentaire ne peut pas dépasser 5000 caractères.',
            'meta.delegate_to.required_if' => 'Le code acteur destinataire est requis pour la délégation.',
            'meta.delegate_to.exists' => 'Le code acteur destinataire n\'existe pas.',
        ];
    }

    /**
     * Attributs personnalisés pour les messages d'erreur.
     */
    public function attributes(): array
    {
        return [
            'action_code' => 'code d\'action',
            'commentaire' => 'commentaire',
            'meta.delegate_to' => 'acteur destinataire',
        ];
    }

    /**
     * Préparer les données pour la validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer et normaliser les données
        if ($this->has('commentaire')) {
            $this->merge([
                'commentaire' => trim($this->commentaire),
            ]);
        }

        if ($this->has('meta') && is_array($this->meta) && isset($this->meta['delegate_to'])) {
            $this->merge([
                'meta' => array_merge($this->meta, [
                    'delegate_to' => trim($this->meta['delegate_to']),
                ]),
            ]);
        }
    }
}

