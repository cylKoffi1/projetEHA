<?php

namespace App\Http\Controllers;

use App\Models\Bailleur;
use App\Models\DecoupageAdminPays;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\GroupeProjetPaysUser;
use App\Models\Infrastructure;
use App\Models\Pays;
use App\Models\Projet;
use App\Models\ProjetEha2;
use App\Models\Acteur;
use App\Models\ProjetStatutProjet;
use App\Models\LocalitesPays;
use App\Models\StatutProjet;
use App\Models\TypeStatut;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class sigAdminController extends Controller
{
    public function carte(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $user = Auth::user();
        
        $Bailleurs = Acteur::whereHas('bailleurs')->get();

        $statuts = TypeStatut::all();
        // Vérifiez si un pays est sélectionné dans la session
        $paysSelectionne = session('pays_selectionne');
        if (!$paysSelectionne) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de pays .');
        }

        // Récupérer les informations du pays sélectionné
        $pays = Pays::where('alpha3', $paysSelectionne)->first();
        if (!$pays) {
            return redirect()->route('projets.index')->with('error', 'Le pays n\'existe pas.');
        }

        $codeAlpha3 = $pays->alpha3;
        $codeZoom = $pays->select('minZoom', 'maxZoom')
        ->where('alpha3', $codeAlpha3)
        ->first();

        // Vérifiez si un groupe projet est sélectionné dans la session
        $groupeProjetSelectionne = session('projet_selectionne');
        if (!$groupeProjetSelectionne) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de groupe projet.');
        }

        // Récupérez les informations du groupe projet sélectionné
        $groupeProjet = GroupeProjetPaysUser::where('groupe_projet_id', $groupeProjetSelectionne)
            ->with('groupeProjet')
            ->first();

        if (!$groupeProjet) {
            return redirect()->route('projets.index')->with('error', 'Le groupe projet n\'existe pas.');
        }

        $codeGroupeProjet = $groupeProjet->groupe_projet_id;

        // Récupérer les domaines associés au groupe projet
        $domainesAssocie = Domaine::where('groupe_projet_code', $codeGroupeProjet)
            ->select('code', 'libelle')
            ->get();

        // Récupérer les niveaux administratifs
        $niveau = DecoupageAdminPays::where('id_pays', $pays->id)
            ->join('decoupage_administratif', 'decoupage_admin_pays.code_decoupage', '=', 'decoupage_administratif.code_decoupage')
            ->select(
                'decoupage_admin_pays.code_decoupage',
                'decoupage_admin_pays.num_niveau_decoupage',
                'decoupage_administratif.libelle_decoupage'
            )
            ->get();
        return view('sigAdmin', compact('ecran', 'codeZoom', 'niveau', 'codeAlpha3', 'codeGroupeProjet', 'domainesAssocie', 'Bailleurs', 'statuts'));
    }

    public function Autrecarte(Request $request){
        $ecran = Ecran::find($request->input('ecran_id'));
        $user = Auth::user();

        // Vérifiez si un pays est sélectionné dans la session
        $paysSelectionne = session('pays_selectionne');
        if (!$paysSelectionne) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de pays .');
        }

        // Récupérer les informations du pays sélectionné
        $pays = Pays::where('alpha3', $paysSelectionne)->first();
        if (!$pays) {
            return redirect()->route('projets.index')->with('error', 'Le pays n\'existe pas.');
        }

        $codeAlpha3 = $pays->alpha3;
        $codeZoom = $pays->select('minZoom', 'maxZoom')
        ->where('alpha3', $codeAlpha3)
        ->first();

        // Vérifiez si un groupe projet est sélectionné dans la session
        $groupeProjetSelectionne = session('projet_selectionne');
        if (!$groupeProjetSelectionne) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de groupe projet.');
        }

        // Récupérez les informations du groupe projet sélectionné
        $groupeProjet = GroupeProjetPaysUser::where('groupe_projet_id', $groupeProjetSelectionne)
            ->with('groupeProjet')
            ->first();

        if (!$groupeProjet) {
            return redirect()->route('projets.index')->with('error', 'Le groupe projet n\'existe pas.');
        }

        $codeGroupeProjet = $groupeProjet->groupe_projet_id;

        // Récupérer les domaines associés au groupe projet
        $domainesAssocie = Domaine::where('groupe_projet_code', $codeGroupeProjet)
            ->select('code', 'libelle')
            ->get();

        // Récupérer les niveaux administratifs
        $niveau = DecoupageAdminPays::where('id_pays', $pays->id)
            ->join('decoupage_administratif', 'decoupage_admin_pays.code_decoupage', '=', 'decoupage_administratif.code_decoupage')
            ->select(
                'decoupage_admin_pays.code_decoupage',
                'decoupage_admin_pays.num_niveau_decoupage',
                'decoupage_administratif.libelle_decoupage'
            )
            ->get();
        return view('autreCarte', compact('ecran', 'codeZoom', 'niveau', 'codeAlpha3', 'codeGroupeProjet', 'domainesAssocie'));
    }

    private function reconstruireCodeProjet($project)
    {
        $country = $project->code_alpha3_pays ?? 'CIV';
    
        $group = session('projet_selectionne') ?? 'TIC';
        $typeFinancement = '1'; // par défaut public
    
        // Tu peux déterminer le type de financement ici si stocké ailleurs
        // Exemple : $typeFinancement = $project->type_financement ?? '1';
    
        $groupeEtType = $group . $typeFinancement; // "TIC" + "2" = "TIC2"
    
        $locCode = $project->code_localisation ?? '0101';
        $sousDomaine = $project->code_sous_domaine ?? '0000';
    
        $annee = $project->date_demarrage_prevue
        ? \Carbon\Carbon::parse($project->date_demarrage_prevue)->format('Y')
        : '0000';

    // Numérotation = à faire évoluer selon les doublons, pour l’instant fixe à "01"
    $ordre = '01';

    return "{$country}{$groupeEtType}_{$locCode}_{$sousDomaine}_{$annee}_{$ordre}";
}
    public function getGeoJsonWithProjectCounts(Request $request)
    {
        $groupeProjetId = session('projet_selectionne');
        $countryAlpha3 = session('pays_selectionne');
    
        if (!$groupeProjetId || !$countryAlpha3) {
            return response()->json(['error' => 'Session data missing'], 400);
        }
    
        // Récupérer les projets
        $projets = Projet::where('code_alpha3_pays', $countryAlpha3)
            ->where('code_projet', 'like', $countryAlpha3 . $groupeProjetId . '%')
            ->get();
    
        // Charger le GeoJSON
        $geoJsonPath = storage_path("geojson/gadm41_{$countryAlpha3}_1.json");
        if (!file_exists($geoJsonPath)) {
            return response()->json(['error' => 'GeoJSON not found'], 404);
        }
    
        $geoJson = json_decode(file_get_contents($geoJsonPath), true);
    
        // Compter les projets par région
        $counts = [];
        foreach ($projets as $projet) {
            $regionName = $projet->region_name;
            if ($regionName) {
                $counts[$regionName] = ($counts[$regionName] ?? 0) + 1;
            }
        }
    
        // Ajouter les comptes au GeoJSON
        foreach ($geoJson['features'] as &$feature) {
            $regionName = $feature['properties']['NAME_1'];
            $feature['properties']['projectCount'] = $counts[$regionName] ?? 0;
        }
    
        return response()->json($geoJson);
    }
    public function getProjects(Request $request)
    {
        $country = session('pays_selectionne');
        $group = session('projet_selectionne');
    
        if (!$country || !$group) {
            return response()->json([
                'error' => 'Les paramètres country et group sont obligatoires'
            ], 400);
        }
    
        $codePattern = $country . $group . '%';
    
        try {
            // Localités indexées
            $localites = LocalitesPays::where('id_pays', $country)->get();
            $indexedLocalites = [];
            foreach ($localites as $loc) {
                $indexedLocalites[$loc->id_niveau][$loc->code_rattachement] = $loc->libelle;
            }
    
            $projects = Projet::where('code_projet', 'like', $codePattern)->get();
    
            $results = [];
    
            foreach ($projects as $project) {
                $codeProjet = $project->code_projet ?: $this->reconstruireCodeProjet($project);
                $components = $this->decomposerCodeProjet($codeProjet);
                $locCode = $components['code_localisation'];
                $domainCode = substr($components['code_sous_domaine'], 0, 2);
                $isPublic = $components['type_financement'] === '1';
    
                // Générer les codes par niveau
                $niv1 = substr($locCode, 0, 2);
                $niv2 = substr($locCode, 0, 4);
                $niv3 = $locCode;
    
                // Préparer les niveaux
                $levels = [
                    1 => ['code' => $niv1, 'name' => $indexedLocalites[1][$niv1] ?? null],
                    2 => ['code' => $niv2, 'name' => $indexedLocalites[2][$niv2] ?? null],
                    3 => ['code' => $niv3, 'name' => $indexedLocalites[3][$niv3] ?? null],
                ];
    
                foreach ($levels as $level => $info) {
                    if (!$info['name']) continue;
                    $normName = $this->normalizeName($info['name']);
    
                    if (!isset($results[$normName])) {
                        $results[$normName] = [
                            'name' => $info['name'],
                            'level' => $level,
                            'code' => $info['code'],
                            'count' => 0,
                            'public' => 0,
                            'private' => 0,
                            'byDomain' => []
                        ];
                    }
    
                    $results[$normName]['count']++;
                    $isPublic ? $results[$normName]['public']++ : $results[$normName]['private']++;
    
                    if (!isset($results[$normName]['byDomain'][$domainCode])) {
                        $results[$normName]['byDomain'][$domainCode] = [
                            'count' => 0,
                            'public' => 0,
                            'private' => 0
                        ];
                    }
    
                    $results[$normName]['byDomain'][$domainCode]['count']++;
                    $isPublic
                        ? $results[$normName]['byDomain'][$domainCode]['public']++
                        : $results[$normName]['byDomain'][$domainCode]['private']++;
                }
            }
    
            // Agrégation ascendante (niveau 3 -> 2 -> 1)
            foreach ($results as $normName => &$data) {
                $level = $data['level'];
                $parentCode = null;
    
                if ($level === 3) $parentCode = substr($data['code'], 0, 4);
                elseif ($level === 2) $parentCode = substr($data['code'], 0, 2);
                else continue;
    
                $parent = collect($results)->first(function ($v) use ($parentCode) {
                    return $v['code'] === $parentCode;
                });
    
                if ($parent) {
                    $parentNorm = $this->normalizeName($parent['name']);
    
                    $results[$parentNorm]['count'] += $data['count'];
                    $results[$parentNorm]['public'] += $data['public'];
                    $results[$parentNorm]['private'] += $data['private'];
    
                    foreach ($data['byDomain'] as $dom => $stat) {
                        if (!isset($results[$parentNorm]['byDomain'][$dom])) {
                            $results[$parentNorm]['byDomain'][$dom] = ['count' => 0, 'public' => 0, 'private' => 0];
                        }
                        $results[$parentNorm]['byDomain'][$dom]['count'] += $stat['count'];
                        $results[$parentNorm]['byDomain'][$dom]['public'] += $stat['public'];
                        $results[$parentNorm]['byDomain'][$dom]['private'] += $stat['private'];
                    }
                }
            }
    
            return response()->json(array_values($results)); // on envoie un tableau simple
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des projets',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    
    private function normalizeName($str)
    {
        if (!$str) return '';
        $str = strtolower(trim($str));
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str); // supprime les accents
        $str = preg_replace('/\s+/', ' ', $str); // espaces multiples
        return $str;
    }

    
    
    private function decomposerCodeProjet($codeProjet)
    {
        // Format: CIVTIC2_0101_0000_2024_01
        $parts = explode('_', $codeProjet);
        
        return [
            'pays' => substr($codeProjet, 0, 3), // CIV
            'groupe_projet' => substr($codeProjet, 3, 3), // TIC
            'type_financement' => substr($codeProjet, 6, 1), // 1 ou 2
            'code_localisation' => $parts[1] ?? '0000', // 0101
            'code_domaine' => substr($parts[2] ?? '0000', 0, 2), // 00 (2 premiers caractères)
            'code_sous_domaine' => $parts[2] ?? '0000', // 0000
            'annee' => $parts[3] ?? '0000',
            'ordre' => $parts[4] ?? '01'
        ];
    }

    
    public function filterMap(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');
        $bailleur = $request->input('bailleur');
        $dateType = $request->input('date_type');
        
        // Récupération du pays et groupe projet depuis la session
        $country = session('pays_selectionne');
        $group = session('projet_selectionne');
        
        if (!$country || !$group) {
            return response()->json([
                'error' => 'Pays ou groupe projet non défini en session'
            ], 400);
        }
    
        // Construction de la requête de base
        $query = Projet::where('code_alpha3_pays', $country)
            ->where('code_projet', 'like', $country . $group . '%');
    
        // Application des filtres
        if ($dateType === 'prévisionnelles') {
            if ($startDate) {
                $query->where('date_demarrage_prevue', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('date_fin_prevue', '<=', $endDate);
            }
        } elseif ($dateType === 'effectives') {
            if ($startDate) {
                $query->where('date_demarrage_effective', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('date_fin_effective', '<=', $endDate);
            }
        }
    
        if ($bailleur) {
            $query->whereHas('financements', function($q) use ($bailleur) {
                $q->where('code_acteur', $bailleur);
            });
        }
        try {
            $projects = $query->get()
                ->map(function($project) {
                    $typeFinancement = substr($project->code_projet, 6, 1);
                    $isPublic = $typeFinancement === '1';
                    $locCode = substr($project->code_projet, 7, 4);
                    
                    return [
                        'code_projet' => $project->code_projet,
                        'code_sous_domaine' => $project->code_sous_domaine,
                        'is_public' => $isPublic,
                        'loc_code' => $locCode
                    ];
                });
    
            return response()->json($projects);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Une erreur est survenue lors du filtrage des projets',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getAllProjects()
    {
        $projects = Projet::with('pays')->get()->map(function ($project) {
            return [
                'code_projet' => $project->code_projet,
                'is_public' => substr($project->code_projet, 6, 1) === '1',
                'country_name' => optional($project->pays)->libelle ?? substr($project->code_projet, 0, 3)
            ];
        });

        return response()->json($projects);
    }

}
