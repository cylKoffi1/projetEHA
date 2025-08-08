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
use App\Models\Financer;
use App\Models\LegendeCarte;
use App\Models\ProjetStatutProjet;
use App\Models\LocalitesPays;
use App\Models\ProjetStatut;
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

        $TypesStatuts = TypeStatut::all();
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
        return view('sigAdmin', compact('ecran', 'codeZoom', 'niveau', 'codeAlpha3', 'codeGroupeProjet', 'domainesAssocie', 'Bailleurs', 'TypesStatuts'));
    }
    public function getByGroupe($groupe)
    {
        $typeFin = request()->input('typeFin', 1);
        $groupeLegende = $typeFin == 2 ? 'COMMUN' : session('projet_selectionne');

        $legende = Legendecarte::where('groupe_projet', $groupeLegende)
            ->where('typeFin', $typeFin)
            ->with(['seuils' => function ($query) {
                $query->orderBy('borneInf');
            }])
            ->first();

        if (!$legende) {
            return response()->json([
                'debug' => [
                    'groupe' => $groupe,
                    'typeFin' => $typeFin
                ],
                'groupe_projet' => $groupe,
                'label' => 'Aucune légende trouvée',
                'seuils' => []
            ]);
        }

        return response()->json([
            'groupe_projet' => $legende->groupe_projet,
            'label' => $legende->label,
            'seuils' => $legende->seuils->map(function ($s) {
                return [
                    'borneInf' => $s->borneInf,
                    'borneSup' => $s->borneSup,
                    'couleur' => $s->couleur
                ];
            })->values()
        ]);
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
                $cost = $project->cout_projet ?? 0;
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
                            'cost' => 0,
                            'byDomain' => []
                        ];
                    }

                    $results[$normName]['count']++;
                    $results[$normName]['cost'] += $cost;
                    $isPublic ? $results[$normName]['public']++ : $results[$normName]['private']++;

                    if (!isset($results[$normName]['byDomain'][$domainCode])) {
                        $results[$normName]['byDomain'][$domainCode] = [
                            'count' => 0,
                            'cost' => 0,
                            'public' => 0,
                            'private' => 0
                        ];
                    }

                    $results[$normName]['byDomain'][$domainCode]['count']++;
                    $results[$normName]['byDomain'][$domainCode]['cost'] += $cost;
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
            'type_financement' => substr($codeProjet, 6, 1), // 1 (public) ou 2 (privé)
            'code_localisation' => $parts[1] ?? '0000', // 0101
            'code_domaine' => substr($parts[2] ?? '0000', 0, 2), // 00 (2 premiers caractères)
            'code_sous_domaine' => $parts[2] ?? '0000', // 0000
            'annee' => $parts[3] ?? '0000',
            'ordre' => $parts[4] ?? '01'
        ];
    }
    public function getFiltreOptions(Request $request)
    {
        $country = session('pays_selectionne');
        $group = session('projet_selectionne');
        $typeDate = $request->input('date_type');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $query = Projet::where('code_alpha3_pays', $country)
            ->where('code_projet', 'like', $country . $group . '%');

        // Appliquer le filtre de date
        if ($typeDate === 'prévisionnelles') {
            if ($start) $query->where('date_demarrage_prevue', '>=', $start);
            if ($end) $query->where('date_fin_prevue', '<=', $end);
        } else {
            // Jointure avec les dates effectives
            $query->whereHas('dateEffective', function ($q) use ($start, $end) {
                if ($start) $q->where('date_debut_effective', '>=', $start);
                if ($end) $q->where('date_fin_effective', '<=', $end);
            });
        }

        $filteredProjects = $query->pluck('code_projet');

        // Statuts liés à ces projets
        $statuts = ProjetStatut::whereIn('code_projet', $filteredProjects)->with('statut')->get()
            ->map(fn($ps) => [
                'id' => $ps->type_statut,
                'libelle' => $ps->statut->libelle ?? 'Statut inconnu'
            ])
            ->unique('id')
            ->values();

        // Bailleurs liés
        $bailleurs = Financer::whereIn('code_projet', $filteredProjects)->with('acteur')->get()
            ->map(fn($f) => [
                'code_acteur' => $f->code_acteur,
                'nom' => $f->acteur->libelle_court ?? 'Bailleur'
            ])
            ->unique('code_acteur')
            ->values();

        return response()->json([
            'bailleurs' => $bailleurs,
            'statuts' => $statuts
        ]);
    }

    public function getFiltreOptionsEtProjets(Request $request)
    {
        $country = session('pays_selectionne');
        $group = session('projet_selectionne');
        $start = $request->input('start_date');
        $end = $request->input('end_date');
        $type = $request->input('date_type');
        $bailleur = $request->input('bailleur');

        $query = Projet::where('code_alpha3_pays', $country)
            ->where('code_projet', 'like', $country . $group . '%');

        if ($type === 'prévisionnelles') {
            if ($start) $query->where('date_demarrage_prevue', '>=', $start);
            if ($end) $query->where('date_fin_prevue', '<=', $end);
        } elseif ($type === 'effectives') {
            $query->whereHas('dateEffective', function ($q) use ($start, $end) {
                if ($start) $q->where('date_debut_effective', '>=', $start);
                if ($end) $q->where('date_fin_effective', '<=', $end);
            });
        }

        if ($request->filled('bailleur')) {
            $query->whereHas('financements', function ($q) use ($request) {
                $q->where('code_acteur', $bailleur);
            });
        }

        if ($request->filled('status')) {
            $query->whereHas('statuts', function ($q) use ($request) {
                $q->where('type_statut', $request->status);
            });
        }

        $filteredProjects = $query->get();

        $codes = $filteredProjects->pluck('code_projet');

        // 🔢 Calcul des coûts publics / privés
        $publicCost = 0;
        $privateCost = 0;
        foreach ($filteredProjects as $project) {
            $code = $project->code_projet ?? '';
            $isPublic = substr($code, 6, 1) === '1';
            $cost = $project->cout_projet ?? 0;

            if ($isPublic) {
                $publicCost += $cost;
            } else {
                $privateCost += $cost;
            }
        }
        // BAILLEURS
        $bailleurs = Financer::whereIn('code_projet', $codes)
            ->with('acteur')
            ->get()
            ->map(fn($f) => [
                'code_acteur' => $f->code_acteur,
                'nom' => $f->acteur->libelle_court ?? 'Bailleur'
            ])
            ->unique('code_acteur')
            ->values();

        // STATUTS
        $statuts = ProjetStatut::whereIn('code_projet', $codes)
            ->with('statut')
            ->get()
            ->map(fn($s) => [
                'id' => $s->type_statut,
                'libelle' => $s->statut->libelle ?? 'Statut inconnu'
            ])
            ->unique('id')
            ->values();

        return response()->json([
            'projets' => $filteredProjects,
            'bailleurs' => $bailleurs,
            'statuts' => $statuts,
            'public_cost' => $publicCost,
            'private_cost' => $privateCost,
            'total_cost' => $publicCost + $privateCost
        ]);
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

    /**
     * Détails des projets pour un code de localisation donné (préfixe),
     * avec filtre public/privé/cumul et domaine optionnel.
     */
    public function getProjectDetails(Request $request)
    {
        $country = session('pays_selectionne');
        $group = session('projet_selectionne');

        if (!$country || !$group) {
            return response()->json([
                'error' => 'Contexte pays/groupe manquant dans la session.'
            ], 400);
        }

        $locPrefix = $request->input('code'); // ex: 01, 0101, 010101
        $financeFilter = $request->input('filter', 'cumul'); // cumul|public|private
        $domainPrefix = $request->input('domain'); // ex: "01"
        $limit = (int) $request->input('limit', 1000);

        if (!$locPrefix) {
            return response()->json(['error' => 'Paramètre code (localisation) requis.'], 422);
        }

        $codePattern = $country . $group . '%';

        try {
            $query = Projet::where('code_projet', 'like', $codePattern);

            if ($domainPrefix) {
                $query->where('code_sous_domaine', 'like', $domainPrefix . '%');
            }

            $projects = $query->limit($limit)->get();

            $results = [];

            foreach ($projects as $project) {
                $codeProjet = $project->code_projet ?: $this->reconstruireCodeProjet($project);
                $components = $this->decomposerCodeProjet($codeProjet);
                $locCode = $components['code_localisation'];

                if (strpos($locCode, $locPrefix) !== 0) {
                    continue; // ne correspond pas au préfixe demandé
                }

                $isPublic = $components['type_financement'] === '1';
                if ($financeFilter === 'public' && !$isPublic) continue;
                if ($financeFilter === 'private' && $isPublic) continue;

                $results[] = [
                    'code_projet' => $codeProjet,
                    'libelle_projet' => $project->libelle_projet,
                    'cout_projet' => $project->cout_projet ?? 0,
                    'is_public' => $isPublic,
                    'code_sous_domaine' => $project->code_sous_domaine,
                    'code_localisation' => $locCode,
                    'date_demarrage_prevue' => $project->date_demarrage_prevue,
                    'date_fin_prevue' => $project->date_fin_prevue,
                ];
            }

            return response()->json([
                'count' => count($results),
                'projects' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des détails des projets',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
