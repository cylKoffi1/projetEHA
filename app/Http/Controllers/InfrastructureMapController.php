<?php

namespace App\Http\Controllers;

use App\Models\Infrastructure;
use App\Models\FamilleInfrastructure;
use App\Models\LocalitesPays;
use App\Models\Ecran;
use App\Models\Domaine;
use App\Models\SousDomaine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $familles = FamilleInfrastructure::join('famille_domaine', 'familleinfrastructure.code_Ssys', '=', 'famille_domaine.code_Ssys')
            ->where('famille_domaine.code_groupe_projet', $groupeProjetSelectionne)
            ->select('familleinfrastructure.*')
            ->distinct()
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

    /**
     * Retourne les marqueurs par niveau pour les infrastructures bénéficiaires
     * Niveau 1: Domaines
     * Niveau 2: Sous-domaines
     * Niveau 3: Familles d'infrastructures
     * Niveau 4+: Infrastructures individuelles
     */
    public function getMarkersByLevel(Request $request)
    {
        $paysCode = session('pays_selectionne');
        $groupeProjet = session('projet_selectionne');
        $level = (int) $request->input('level', 1);
        $prefix = (string) $request->input('code', ''); // Préfixe de localité pour filtrer

        if (!$paysCode || !$groupeProjet) {
            return response()->json(['markers' => []], 400);
        }

        // Niveau 4 et plus : infrastructures individuelles
        if ($level >= 4) {
            $query = DB::table('infrastructures as i')
                ->join('jouir as j', 'j.code_Infrastructure', '=', 'i.code')
                ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
                ->where('i.code_pays', $paysCode)
                ->where('i.code_groupe_projet', $groupeProjet)
                ->whereNotNull('i.latitude')
                ->whereNotNull('i.longitude')
                ->select([
                    'i.code as infra_code',
                    'i.libelle as infra_lib',
                    'i.latitude',
                    'i.longitude',
                    'i.code_Ssys as famille_code',
                    DB::raw('COUNT(DISTINCT p.code_projet) as nb_projets')
                ])
                ->groupBy('i.code', 'i.libelle', 'i.latitude', 'i.longitude', 'i.code_Ssys');

            if ($prefix !== '') {
                $len = min(strlen($prefix) + 2, 6);
                $query->where(DB::raw("LEFT(i.code_localite, {$len})"), 'like', $prefix . '%');
            }

            $rows = $query->get();
            $markers = [];
            foreach ($rows as $r) {
                $markers[] = [
                    'lat' => (float) $r->latitude,
                    'lng' => (float) $r->longitude,
                    'count' => (int) $r->nb_projets,
                    'class' => 'infra',
                    'code' => $r->infra_code,
                    'label' => $r->infra_lib,
                    'famille_code' => $r->famille_code,
                ];
            }
            return response()->json(['markers' => $markers]);
        }

        // Niveaux 1, 2, 3 : agrégation
        $query = DB::table('infrastructures as i')
            ->join('jouir as j', 'j.code_Infrastructure', '=', 'i.code')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('sous_domaine as sd', 'sd.code_sous_domaine', '=', 'p.code_sous_domaine')
            ->join('domaine_intervention as d', 'd.code', '=', 'sd.code_domaine')
            ->where('i.code_pays', $paysCode)
            ->where('i.code_groupe_projet', $groupeProjet)
            ->whereNotNull('i.latitude')
            ->whereNotNull('i.longitude');

        if ($prefix !== '') {
            $len = $level * 2;
            $query->where(DB::raw("LEFT(i.code_localite, {$len})"), 'like', $prefix . '%');
        }

        $rows = $query->select([
            'i.latitude',
            'i.longitude',
            'i.code_Ssys as famille_code',
            'd.code as domaine_code',
            'd.libelle as domaine_libelle',
            'sd.code_sous_domaine as sous_domaine_code',
            'sd.lib_sous_domaine as sous_domaine_libelle',
        ])->get();

        if ($rows->isEmpty()) {
            return response()->json(['markers' => []]);
        }

        // Agrégation selon le niveau
        $buckets = [];
        foreach ($rows as $r) {
            $key = null;
            
            if ($level === 1) {
                // Niveau 1 : Domaines
                $key = 'domain|' . $r->domaine_code;
                if (!isset($buckets[$key])) {
                    $buckets[$key] = [
                        'sumLat' => 0,
                        'sumLng' => 0,
                        'n' => 0,
                        'count' => 0,
                        'class' => 'domain',
                        'code' => $r->domaine_code,
                        'label' => $r->domaine_libelle ?? $r->domaine_code,
                    ];
                }
            } elseif ($level === 2) {
                // Niveau 2 : Sous-domaines
                $key = 'subdomain|' . $r->sous_domaine_code;
                if (!isset($buckets[$key])) {
                    $buckets[$key] = [
                        'sumLat' => 0,
                        'sumLng' => 0,
                        'n' => 0,
                        'count' => 0,
                        'class' => 'subdomain',
                        'code' => $r->sous_domaine_code,
                        'label' => $r->sous_domaine_libelle ?? $r->sous_domaine_code,
                    ];
                }
            } elseif ($level === 3) {
                // Niveau 3 : Familles d'infrastructures
                $key = 'family|' . $r->famille_code;
                if (!isset($buckets[$key])) {
                    $famille = FamilleInfrastructure::where('code_Ssys', $r->famille_code)->first();
                    $buckets[$key] = [
                        'sumLat' => 0,
                        'sumLng' => 0,
                        'n' => 0,
                        'count' => 0,
                        'class' => 'family',
                        'code' => $r->famille_code,
                        'label' => $famille->libelleFamille ?? $r->famille_code,
                    ];
                }
            }

            // Ajouter les coordonnées au bucket si la clé est définie
            if ($key && isset($buckets[$key])) {
                $buckets[$key]['sumLat'] += (float) $r->latitude;
                $buckets[$key]['sumLng'] += (float) $r->longitude;
                $buckets[$key]['n']++;
                $buckets[$key]['count']++;
            }
        }

        $markers = [];
        foreach ($buckets as $b) {
            $markers[] = [
                'lat' => $b['sumLat'] / max(1, $b['n']),
                'lng' => $b['sumLng'] / max(1, $b['n']),
                'count' => $b['count'],
                'class' => $b['class'],
                'code' => $b['code'],
                'label' => $b['label'],
            ];
        }

        return response()->json(['markers' => $markers]);
    }
}