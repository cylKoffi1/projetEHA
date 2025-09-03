<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRenforcementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre'                     => ['required','string','max:255'],
            'actionTypeId'              => ['required','integer','exists:actionType,id'],
            'thematique'                => ['nullable','string','max:255'],
            'public_cible'              => ['nullable','string','max:255'],
            'description'               => ['nullable','string','max:5000'],
            'date_debut'                => ['required','date'],
            'date_fin'                  => ['required','date','after_or_equal:date_debut'],
            'lieu'                      => ['nullable','string','max:255'],
            'modaliteId'                => ['nullable','integer','exists:modalite,id'],
            'organisme'                 => ['nullable','string','max:255'],
            'intervenants'              => ['nullable','string','max:255'],
            'nb_participants_prev'      => ['nullable','integer','min:0'],
            'nb_participants_effectif'  => ['nullable','integer','min:0'],
            'cout_previsionnel'         => ['nullable','numeric','min:0'],
            'cout_reel'                 => ['nullable','numeric','min:0'],
            'source_financement'        => ['nullable','string','max:255'],
            'pretest_moy'               => ['nullable','numeric','min:0'],
            'posttest_moy'              => ['nullable','numeric','min:0'],
            'beneficiaires'             => ['required','array','min:1'],
            'beneficiaires.*'           => ['integer','exists:acteur,code_acteur'],
            'projets'                   => ['nullable','array'],
            'projets.*'                 => ['string','exists:projets,code_projet'],
            'pieces.*'                  => ['nullable','file','max:10240'], // 10 Mo
        ];
    }

    public function attributes(): array
    {
        return [
            'date_debut'    => 'date de début',
            'date_fin'      => 'date de fin',
            'beneficiaires' => 'bénéficiaires',
        ];
    }
}
