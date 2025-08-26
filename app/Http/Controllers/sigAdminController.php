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
        return view('GestionSig.sigAdmin', compact('ecran', 'codeZoom', 'niveau', 'codeAlpha3', 'codeGroupeProjet', 'domainesAssocie', 'Bailleurs', 'TypesStatuts'));
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
        $group   = session('projet_selectionne');
    
        if (!$country || !$group) {
            return response()->json(['error' => 'Les paramètres country et group sont obligatoires'], 400);
        }
    
        $codePattern = $country . $group . '%';
    
        try {
            // Localités indexées : id_niveau -> code -> libelle
            // ⚠️ si LocalitesPays::id_pays attend l'ID numérique du pays, remplace $country par l’ID.
            $localites = LocalitesPays::where('id_pays', $country)->get();
            $indexedLocalites = [];
            foreach ($localites as $loc) {
                $indexedLocalites[$loc->id_niveau][$loc->code_rattachement] = $loc->libelle;
            }
    
            $projects = Projet::where('code_projet', 'like', $codePattern)->get();
    
            // 🔑 Indexer par CODE (pas par nom) pour éviter les collisions de libellés
            $results = [];
    
            foreach ($projects as $project) {
                $cost       = $project->cout_projet ?? 0;
                $codeProjet = $project->code_projet ?: $this->reconstruireCodeProjet($project);
                $components = $this->decomposerCodeProjet($codeProjet);
    
                $locCode    = $components['code_localisation'];
                $domainCode = substr($components['code_sous_domaine'] ?? '00', 0, 2);
                $isPublic   = ($components['type_financement'] ?? '1') === '1';
    
                // Codes par niveau
                $niv1 = substr($locCode, 0, 2);
                $niv2 = substr($locCode, 0, 4);
                $niv3 = substr($locCode, 0, 6);
    
                $levels = [
                    1 => ['code' => $niv1, 'name' => $indexedLocalites[1][$niv1] ?? null],
                    2 => ['code' => $niv2, 'name' => $indexedLocalites[2][$niv2] ?? null],
                    3 => ['code' => $niv3, 'name' => $indexedLocalites[3][$niv3] ?? null],
                ];
    
                // ➜ On incrémente EXACTEMENT UNE FOIS par niveau (pas d’agrégation après)
                foreach ($levels as $level => $info) {
                    if (!$info['name'] || !$info['code']) continue;
    
                    $key = $info['code']; // << clé stable et unique
                    if (!isset($results[$key])) {
                        $results[$key] = [
                            'name'     => $info['name'],
                            'level'    => $level,
                            'code'     => $info['code'],
                            'count'    => 0,
                            'public'   => 0,
                            'private'  => 0,
                            'cost'     => 0,
                            'byDomain' => []
                        ];
                    }
    
                    $results[$key]['count'] += 1;
                    $results[$key]['cost']  += $cost;
                    $isPublic ? $results[$key]['public']++ : $results[$key]['private']++;
    
                    if (!isset($results[$key]['byDomain'][$domainCode])) {
                        $results[$key]['byDomain'][$domainCode] = [
                            'count' => 0, 'cost' => 0, 'public' => 0, 'private' => 0
                        ];
                    }
                    $results[$key]['byDomain'][$domainCode]['count']   += 1;
                    $results[$key]['byDomain'][$domainCode]['cost']    += $cost;
                    $isPublic
                        ? $results[$key]['byDomain'][$domainCode]['public']++
                        : $results[$key]['byDomain'][$domainCode]['private']++;
                }
            }
    
            // ❌ IMPORTANT : ne PAS faire d’agrégation ascendante ici (sinon double comptage)
    
            return response()->json(array_values($results));
    
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
        $country  = session('pays_selectionne');
        $group    = session('projet_selectionne');
        $typeDate = $request->input('date_type');
        $start    = $request->input('start_date');
        $end      = $request->input('end_date');
    
        $query = Projet::where('code_alpha3_pays', $country)
            ->where('code_projet', 'like', $country.$group.'%');
    
        if ($typeDate === 'prévisionnelles') {
            if ($start) $query->where('date_demarrage_prevue', '>=', $start);
            if ($end)   $query->where('date_fin_prevue',      '<=', $end);
        } else {
            $query->whereHas('dateEffective', function ($q) use ($start, $end) {
                if ($start) $q->where('date_debut_effective', '>=', $start);
                if ($end)   $q->where('date_fin_effective',   '<=', $end);
            });
        }
    
        $filteredProjects = $query->pluck('code_projet');
    
        $statuts = ProjetStatut::whereIn('code_projet', $filteredProjects)
            ->with('statut')
            ->get()
            ->map(fn($ps) => [
                'id'      => $ps->type_statut,
                'libelle' => $ps->statut->libelle ?? 'Statut inconnu',
            ])
            ->unique('id')
            ->values();
    
        // ⚠️ ICI : relation = bailleur (pas acteur)
        $bailleurs = Financer::whereIn('code_projet', $filteredProjects)
            ->with('bailleur')
            ->get()
            ->map(fn($f) => [
                'code_acteur' => $f->code_acteur,
                'nom'         => $f->bailleur->libelle_court ?? 'Bailleur',
            ])
            ->unique('code_acteur')
            ->values();
    
        return response()->json([
            'bailleurs' => $bailleurs,
            'statuts'   => $statuts,
        ]);
    }
    
    public function getFiltreOptionsEtProjets(Request $request)
    {
        $country = session('pays_selectionne');   // ex: "CIV"
        $group   = session('projet_selectionne'); // ex: "EHA"
        if (!$country || !$group) {
            return response()->json(['error' => 'Contexte pays/groupe manquant'], 400);
        }

        // Pour indexer les libellés des localités, on a besoin de l'ID du pays
        $pays = Pays::where('alpha3', $country)->first();
        if (!$pays) return response()->json(['error' => 'Pays inconnu'], 404);

        $start    = $request->input('start_date');   // optionnel
        $end      = $request->input('end_date');     // optionnel
        $type     = $request->input('date_type');    // 'prévisionnelles' | 'effectives' | null
        $statut   = $request->input('status');       // optionnel
        $bailleur = $request->input('bailleur');     // optionnel

        $q = Projet::where('code_projet', 'like', $country.$group.'%');

        // --- DATES (toutes optionnelles) ---
        // Si au moins un des champs de date est fourni, on applique le type (prévisionnelles par défaut)
        if ($start || $end) {
            $type = $type ?: 'prévisionnelles';
        }

        if ($type === 'prévisionnelles') {
            if ($start) $q->where('date_demarrage_prevue', '>=', $start);
            if ($end)   $q->where('date_fin_prevue',      '<=', $end);
        } elseif ($type === 'effectives') {
            $q->whereHas('dateEffective', function ($qq) use ($start, $end) {
                if ($start) $qq->where('date_debut_effective', '>=', $start);
                if ($end)   $qq->where('date_fin_effective',   '<=', $end);
            });
        }
        // Si aucun start/end n’est fourni → aucune contrainte de date

        // --- BAILLEUR (optionnel) ---
        if (!empty($bailleur)) {
            $q->whereHas('financements', fn($qq) => $qq->where('code_acteur', $bailleur));
        }

        // --- STATUT (optionnel) ---
        if (!empty($statut)) {
            $q->whereHas('statuts', fn($qq) => $qq->where('type_statut', $statut));
        }

        $filtered = $q->get();

        // --- AGRÉGATION identique à /api/projects ---
        $localites = LocalitesPays::where('id_pays', $pays->id)->get();
        $idx = [];
        foreach ($localites as $loc) {
            $idx[$loc->id_niveau][$loc->code_rattachement] = $loc->libelle;
        }

        $agg = [];
        $publicCost = 0; $privateCost = 0;

        foreach ($filtered as $p) {
            $codeProjet = $p->code_projet ?: $this->reconstruireCodeProjet($p);
            $c = $this->decomposerCodeProjet($codeProjet);

            $loc       = $c['code_localisation'];
            $dom2      = substr($c['code_sous_domaine'] ?? '00', 0, 2);
            $isPublic  = ($c['type_financement'] ?? '1') === '1';
            $cost      = $p->cout_projet ?? 0;

            // coûts globaux
            $isPublic ? $publicCost += $cost : $privateCost += $cost;

            // 3 niveaux : incrément exactement une fois par niveau
            $codes = [
                1 => substr($loc, 0, 2),
                2 => substr($loc, 0, 4),
                3 => substr($loc, 0, 6),
            ];
            foreach ($codes as $level => $code) {
                if (!$code) continue;
                $name = $idx[$level][$code] ?? null;
                if (!$name) continue;

                if (!isset($agg[$code])) {
                    $agg[$code] = [
                        'name'     => $name,
                        'level'    => $level,
                        'code'     => $code,
                        'count'    => 0,
                        'public'   => 0,
                        'private'  => 0,
                        'cost'     => 0,
                        'byDomain' => [],
                    ];
                }
                $agg[$code]['count']++;
                $agg[$code]['cost'] += $cost;
                $isPublic ? $agg[$code]['public']++ : $agg[$code]['private']++;

                if (!isset($agg[$code]['byDomain'][$dom2])) {
                    $agg[$code]['byDomain'][$dom2] = ['count'=>0,'cost'=>0,'public'=>0,'private'=>0];
                }
                $agg[$code]['byDomain'][$dom2]['count']++;
                $agg[$code]['byDomain'][$dom2]['cost'] += $cost;
                $isPublic
                    ? $agg[$code]['byDomain'][$dom2]['public']++
                    : $agg[$code]['byDomain'][$dom2]['private']++;
            }
        }

        $codes = $filtered->pluck('code_projet');

        // LISTES pour les selects (non destructives)
        $bailleursList = Financer::whereIn('code_projet', $codes)
            ->with('bailleur')
            ->get()
            ->map(fn($f) => [
                'code_acteur' => $f->code_acteur,
                'nom'         => $f->bailleur->libelle_court ?? 'Bailleur',
            ])
            ->unique('code_acteur')
            ->values();

        $statutsList = ProjetStatut::whereIn('code_projet', $codes)
            ->with('statut')
            ->get()
            ->map(fn($s) => [
                'id'      => $s->type_statut,
                'libelle' => $s->statut->libelle ?? 'Statut',
            ])
            ->unique('id')
            ->values();

        return response()->json([
            'projets'      => array_values($agg),     // <-- EXACTEMENT comme /api/projects
            'bailleurs'    => $bailleursList,
            'statuts'      => $statutsList,
            'public_cost'  => $publicCost,
            'private_cost' => $privateCost,
            'total_cost'   => $publicCost + $privateCost,
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
                    'code_devise' => $project->code_devise
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
