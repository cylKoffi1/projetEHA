<?php

namespace App\Services;

use App\Models\Caracteristique;
use App\Models\FamilleInfrastructure;
use Illuminate\Support\Facades\Log;

class CaracteristiqueBuilderService
{
    /**
     * Génère un arbre JSON hiérarchique à partir des caractéristiques d'une famille
     */
    public function buildFromFamille(FamilleInfrastructure $famille): array
    {
        $caracteristiques = Caracteristique::with([
            'type',
            'enfants.type', 
            'enfants.unite',
            'enfants.valeursPossibles',
            'unite',
            'valeursPossibles',
        ])
        ->where('code_famille', $famille->code_Ssys)
        ->get();
    
        // Log des données brutes
        Log::debug("Données brutes des caractéristiques", [
            'total' => $caracteristiques->count(),
            'avec_enfants' => $caracteristiques->filter(fn($c) => $c->enfants->isNotEmpty())->count(),
            'exemple_enfant' => $caracteristiques->first()?->enfants->first() 
        ]);
    
        $racines = $caracteristiques->where('parent_id', null);
    
        $structure = $racines->map(function ($carac) {
            return $this->transformCaracteristique($carac);
        })->sortBy('ordre')->values()->toArray();
    
        return $structure;
    }
    

    /**
     * Transforme récursivement une caractéristique en structure JSON
     */
    protected function transformCaracteristique($carac): array
    {
        // Ajoutez un log pour vérifier la récursion
        Log::debug("Transformation caractéristique", [
            'id' => $carac->id,
            'nom' => $carac->libelleCaracteristique,
            'nb_enfants' => $carac->enfants->count()
        ]);
    
        return [
            'id' => $carac->formatted_id,
            'name' => $carac->libelleCaracteristique,
            'type' => (string) $carac->idTypeCaracteristique,
            'typeLabel' => strtolower($carac->type?->libelleTypeCaracteristique ?? 'inconnu'),
            'order' => $carac->ordre,
            'description' => $carac->description,
            'unit' => $carac->unite?->idUnite,
            'options' => $carac->isListe() ? $carac->valeursPossibles->pluck('valeur')->toArray() : [],
            'parentId' => $carac->parent_formatted_id,
            'children' => $carac->enfants
                ->sortBy('ordre')
                ->map(fn($child) => $this->transformCaracteristique($child))
                ->values()
                ->toArray()
        ];
    }
    
}
