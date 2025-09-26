<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // à adapter (policy/permission). Au minimum : authentifié.
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'role' => 'required|string|exists:groupe_utilisateur,code',

            'consulterRubrique'        => 'nullable|array',
            'consulterRubrique.*'      => 'string',   // code (string)

            'consulterRubriqueEcran'   => 'nullable|array',
            'consulterRubriqueEcran.*' => 'integer',  // id (int)

            'consulterSousMenu'        => 'nullable|array',
            'consulterSousMenu.*'      => 'string',   // code (string)

            'consulterSousMenuEcran'   => 'nullable|array',
            'consulterSousMenuEcran.*' => 'integer',

            'ajouterRubriqueEcran'     => 'nullable|array',
            'ajouterRubriqueEcran.*'   => 'integer',

            'modifierRubriqueEcran'    => 'nullable|array',
            'modifierRubriqueEcran.*'  => 'integer',

            'supprimerRubriqueEcran'   => 'nullable|array',
            'supprimerRubriqueEcran.*' => 'integer',

            'ajouterSousMenuEcran'     => 'nullable|array',
            'ajouterSousMenuEcran.*'   => 'integer',

            'modifierSousMenuEcran'    => 'nullable|array',
            'modifierSousMenuEcran.*'  => 'integer',

            'supprimerSousMenuEcran'   => 'nullable|array',
            'supprimerSousMenuEcran.*' => 'integer',

            'permissionsAsupprimer'    => 'nullable|array',
            'permissionsAsupprimer.*'  => 'string',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();
        // Normalise en tableaux vides pour éviter les nulls
        foreach ([
            'consulterRubrique','consulterRubriqueEcran','consulterSousMenu','consulterSousMenuEcran',
            'ajouterRubriqueEcran','modifierRubriqueEcran','supprimerRubriqueEcran',
            'ajouterSousMenuEcran','modifierSousMenuEcran','supprimerSousMenuEcran',
            'permissionsAsupprimer'
        ] as $k) {
            $data[$k] = $data[$k] ?? [];
        }
        return $data;
    }
}
