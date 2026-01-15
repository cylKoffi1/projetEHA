<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInfrastructureRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Règles de validation pour la création d'une infrastructure.
     */
    public function rules(): array
    {
        return [
            'libelle' => [
                'required',
                'string',
                'max:255',
            ],
            'code_famille_infrastructure' => [
                'required',
                'string',
                'exists:familleinfrastructure,code_Ssys',
            ],
            'code_localite' => [
                'required',
                'integer',
                'exists:localites_pays,id',
            ],
            'date_operation' => [
                'nullable',
                'date',
            ],
            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'code_infras_rattacher' => [
                'nullable',
                'string',
                'exists:infrastructures,code',
            ],
            'caracteristiques' => [
                'nullable',
                'array',
            ],
            'caracteristiques.*' => [
                'nullable',
            ],
            'gallery' => [
                'nullable',
                'array',
            ],
            'gallery.*' => [
                'image',
                'mimes:jpeg,jpg,png,gif,webp',
                'max:10240', // 10MB max par image
            ],
        ];
    }

    /**
     * Messages de validation personnalisés.
     */
    public function messages(): array
    {
        return [
            'libelle.required' => 'Le libellé de l\'infrastructure est requis.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'code_famille_infrastructure.required' => 'La famille d\'infrastructure est requise.',
            'code_famille_infrastructure.exists' => 'La famille d\'infrastructure sélectionnée n\'existe pas.',
            'code_localite.required' => 'La localisation est requise.',
            'code_localite.exists' => 'La localisation sélectionnée n\'existe pas.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
            'code_infras_rattacher.exists' => 'L\'infrastructure mère sélectionnée n\'existe pas.',
            'gallery.*.image' => 'Les fichiers doivent être des images.',
            'gallery.*.mimes' => 'Les images doivent être au format : jpeg, jpg, png, gif ou webp.',
            'gallery.*.max' => 'Chaque image ne peut pas dépasser 10 Mo.',
        ];
    }

    /**
     * Attributs personnalisés pour les messages d'erreur.
     */
    public function attributes(): array
    {
        return [
            'libelle' => 'libellé',
            'code_famille_infrastructure' => 'famille d\'infrastructure',
            'code_localite' => 'localisation',
            'date_operation' => 'date d\'opération',
            'code_infras_rattacher' => 'infrastructure mère',
            'gallery' => 'galerie d\'images',
        ];
    }

    /**
     * Préparer les données pour la validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer et normaliser les données
        if ($this->has('libelle')) {
            $this->merge([
                'libelle' => trim($this->libelle),
            ]);
        }

        // Convertir latitude/longitude en float si présents
        if ($this->has('latitude') && $this->latitude !== null) {
            $this->merge([
                'latitude' => (float) $this->latitude,
            ]);
        }

        if ($this->has('longitude') && $this->longitude !== null) {
            $this->merge([
                'longitude' => (float) $this->longitude,
            ]);
        }
    }
}

