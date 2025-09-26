<?php

namespace App\Http\Controllers;

use App\Models\Infrastructure;
use App\Models\FamilleInfrastructure;
use App\Models\LocalitesPays;
use App\Models\Ecran;
use Illuminate\Http\Request;

class InfrastructureMapController extends Controller
{
    public function showMap(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $paysSelectionne = session('pays_selectionne');
        $groupeProjetSelectionne = session('projet_selectionne');

        if (!$paysSelectionne || !$groupeProjetSelectionne) {
            return redirect()->back()->with('error', 'Veuillez sélectionner un pays et un groupe projet');
        }

        $familles = FamilleInfrastructure::where('code_groupe_projet', $groupeProjetSelectionne)
            ->with(['domaine', 'sousdomaine'])
            ->get();

        return view('infrastructureMap', compact('familles', 'ecran'));
    }

    public function getInfrastructuresGeoJson()
    {
        $paysCode = session('pays_selectionne');
        $groupeProjet = session('projet_selectionne');

        $infrastructures = Infrastructure::with(['familleInfrastructure', 'localisation'])
            ->where('code_pays', $paysCode)
            ->where('code_groupe_projet', $groupeProjet)
            ->get();

        $features = [];
        
        foreach ($infrastructures as $infra) {
            if (!$infra->localisation || !$infra->localisation->latitude || !$infra->localisation->longitude) {
                continue;
            }

            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'id' => $infra->id,
                    'code' => $infra->code,
                    'libelle' => $infra->libelle,
                    'famille' => $infra->familleInfrastructure->libelleFamille ?? 'Inconnue',
                    'famille_code' => $infra->code_famille_infrastructure,
                    'localisation' => $infra->localisation->libelle ?? 'Inconnue',
                    'photo' => $infra->imageInfras ? asset($infra->imageInfras) : null,
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        (float)$infra->localisation->longitude,
                        (float)$infra->localisation->latitude
                    ]
                ]
            ];
        }

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }

    public function getFamillesColors()
    {
        $familles = FamilleInfrastructure::where('code_groupe_projet', session('projet_selectionne'))
            ->get();

        $colors = [
            '#FF5733', '#33FF57', '#3357FF', '#F033FF', '#FF33A8',
            '#33FFF5', '#FF8C33', '#8C33FF', '#33FF8C', '#FF338C',
            '#33A8FF', '#A8FF33', '#FF33F0', '#57FF33', '#5733FF'
        ];

        $result = [];
        $colorIndex = 0;

        foreach ($familles as $famille) {
            $result[$famille->code_famille] = [
                'libelle' => $famille->libelleFamille,
                'color' => $colors[$colorIndex % count($colors)],
                'icon' => $this->getIconForFamille($famille->code_famille)
            ];
            $colorIndex++;
        }

        return response()->json($result);
    }

    private function getIconForFamille($familleCode)
    {
        // Vous pouvez personnaliser les icônes par famille ici
        $icons = [
            'ECOLE' => 'school',
            'HOPITAL' => 'local_hospital',
            'ROUTE' => 'directions_car',
            'PONT' => 'account_balance',
            'EAU' => 'water_drop',
            'ENERGIE' => 'bolt',
            // Ajoutez d'autres correspondances selon vos besoins
        ];

        $defaultIcon = 'place';

        foreach ($icons as $key => $icon) {
            if (strpos($familleCode, $key) !== false) {
                return $icon;
            }
        }

        return $defaultIcon;
    }
}