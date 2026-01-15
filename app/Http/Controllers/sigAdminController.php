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
use App\Models\AppuiProjet;
use App\Models\Financer;
use App\Models\LegendeCarte;
use App\Models\ProjetStatutProjet;
use App\Models\LocalitesPays;
use App\Models\ProjetStatut;
use App\Models\StatutProjet;
use App\Models\SousDomaine;
use App\Models\TypeStatut;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $country = session('pays_selectionne');   
        $group   = session('projet_selectionne'); 
    
        if (!$country || !$group) {
            return response()->json(['error' => 'Contexte pays/groupe manquant'], 400);
        }
    
        // Pour indexer les libellés des localités, on a besoin de l’ID du pays
        $pays = Pays::where('alpha3', $country)->first();
        if (!$pays) return response()->json(['error' => 'Pays inconnu'], 404);
    
    
        /* ============================================================
         *                 PARAMÈTRES DE FILTRAGE
         * ============================================================ */
        $start      = $request->input('start_date');  
        $end        = $request->input('end_date');    
        $type       = $request->input('date_type');   // 'prévisionnelles' | 'effectives' | null
        $statut     = $request->input('status');      // id du statut
        $bailleur   = $request->input('bailleur');    // code acteur
        $projectType = $request->input('project_type', 'cumul'); // infras | appui | cumul
    
        if (!$projectType) {
            Log::info($projectType);
            return response()->json(['error' => 'Erreur projet type manquant'], 400);
        }
        Log::info($projectType);
        /* ============================================================
         *                 FILTRE PROJETS INFRASTRUCTURES
         * ============================================================ */
        $qInfra = Projet::where('code_projet', 'like', $country.$group.'%');
    
        // Dates
        if ($start || $end) {
            $type = $type ?: 'prévisionnelles';
        }
    
        if ($type === 'prévisionnelles') {
            if ($start) $qInfra->where('date_demarrage_prevue', '>=', $start);
            if ($end)   $qInfra->where('date_fin_prevue',      '<=', $end);
        }
        elseif ($type === 'effectives') {
            $qInfra->whereHas('dateEffective', function ($qq) use ($start, $end) {
                if ($start) $qq->where('date_debut_effective', '>=', $start);
                if ($end)   $qq->where('date_fin_effective',   '<=', $end);
            });
        }
    
        // Bailleur
        if ($bailleur) {
            $qInfra->whereHas('financements', fn($qq) => $qq->where('code_acteur', $bailleur));
        }
    
        // Statut
        if ($statut) {
            $qInfra->whereHas('statuts', fn($qq) => $qq->where('type_statut', $statut));
        }
    
    
        /* ============================================================
         *                 FILTRE PROJETS APPUI
         * ============================================================ */
        $qAppui = AppuiProjet::where('code_projet_appui','like','APPUI_'.$country.'_'.$group.'%');
    
        // Dates
        if ($start || $end) {
            if ($type === 'prévisionnelles') {
                if ($start) $qAppui->where('date_debut_previsionnel', '>=', $start);
                if ($end)   $qAppui->where('date_fin_previsionnel',   '<=', $end);
            } else {
                $qAppui->whereHas('dateEffective', function ($qq) use ($start, $end) {
                    if ($start) $qq->where('date_debut_effective', '>=', $start);
                    if ($end)   $qq->where('date_fin_effective',   '<=', $end);
                });
            }
        }
    
        // Bailleur
        if ($bailleur) {
            $qAppui->whereHas('financements', fn($qq) => $qq->where('code_acteur', $bailleur));
        }
    
        // Statut
        if ($statut) {
            $qAppui->whereHas('statuts', fn($qq) => $qq->where('type_statut', $statut));
        }
    
    
        /* ============================================================
         *                 SELECTION SELON TYPE PROJET
         * ============================================================ */
        if ($projectType === 'infras') {
            $filtered = $qInfra->get();
        }
        elseif ($projectType === 'appui') {
            $filtered = $qAppui->get();
        }
        else { // cumul
            $filtered = $qInfra->get()->merge(
                $qAppui->get()
            );
        }
    
    
        /* ============================================================
         *                 AGREGATION PAR NIVEAUX
         * ============================================================ */
        // Localités indexées par: id_niveau -> code -> libelle
        $localites = LocalitesPays::where('id_pays', $pays->id)->get();
        $idx = [];
        foreach ($localites as $loc) {
            $idx[$loc->id_niveau][$loc->code_rattachement] = $loc->libelle;
        }
    
        $agg = [];
        $publicCost = 0;
        $privateCost = 0;
    
        foreach ($filtered as $p) {
    
            $codeProjet = $p->code_projet ?? $p->code_projet_appui;
            $codeProjet = $codeProjet ?: $this->reconstruireCodeProjet($p);
    
            $comp = $this->decomposerCodeProjet($codeProjet);
    
            $locCode   = $comp['code_localisation'];
            $dom2      = substr($comp['code_sous_domaine'], 0, 2);
            $isPublic  = ($comp['type_financement'] ?? '1') === '1';
            $cost      = $p->cout_projet ?? $p->montant_budget_previsionnel ?? 0;
    
            // Totaux globaux
            $isPublic ? $publicCost += $cost : $privateCost += $cost;
    
            // 3 niveaux
            $codes = [
                1 => substr($locCode, 0, 2),
                2 => substr($locCode, 0, 4),
                3 => substr($locCode, 0, 6),
            ];
    
            foreach ($codes as $level => $code) {
                if (!$code) continue;
    
                $name = $idx[$level][$code] ?? null;
                if (!$name) continue;
    
                if (!isset($agg[$code])) {
                    $agg[$code] = [
                        'code'     => $code,
                        'name'     => $name,
                        'level'    => $level,
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
                    $agg[$code]['byDomain'][$dom2] = ['count'=>0, 'cost'=>0, 'public'=>0, 'private'=>0];
                }
    
                $agg[$code]['byDomain'][$dom2]['count']++;
                $agg[$code]['byDomain'][$dom2]['cost'] += $cost;
                $isPublic
                    ? $agg[$code]['byDomain'][$dom2]['public']++
                    : $agg[$code]['byDomain'][$dom2]['private']++;
            }
        }
    
        $codes = $filtered->pluck('code_projet')
            ->merge($filtered->pluck('code_projet_appui'))
            ->filter()
            ->values();
    
    
        /* ============================================================
         *              LISTES POUR LES SELECT (UI)
         * ============================================================ */
    
        // Bailleurs filtrés
        $bailleursList = Financer::whereIn('code_projet', $codes)
            ->with('bailleur')
            ->get()
            ->map(fn($f) => [
                'code_acteur' => $f->code_acteur,
                'nom'         => $f->bailleur->libelle_court ?? 'Bailleur',
            ])
            ->unique('code_acteur')
            ->values();
    
        // Statuts filtrés
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
            'projets'      => array_values($agg),
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





        // -----------------------------
    // Page
    // -----------------------------
    // PAGE : Carte des infrastructures bénéficiaires
    // Seules les infrastructures présentes dans la table 'jouir' sont affichées
    // -----------------------------
    public function page(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $user  = Auth::user();

        $alpha3 = session('pays_selectionne');
        if (!$alpha3) {
            return redirect()->route('projets.index')
                ->with('error', 'Vous n\'avez pas de pays.');
        }

        $pays = Pays::where('alpha3', $alpha3)->first();
        if (!$pays) {
            return redirect()->route('projets.index')
                ->with('error', 'Le pays sélectionné est introuvable.');
        }

        $codeAlpha3 = $pays->alpha3;
        $codeZoom   = Pays::select('minZoom', 'maxZoom')->where('alpha3', $codeAlpha3)->first();

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
            ->select('code', 'libelle', 'groupe_projet_code')
            ->get();

        // Récupérer les sous-domaines associés au groupe projet
        $sousDomainesAssocie = SousDomaine::where('code_groupe_projet', $codeGroupeProjet)
            ->select('code_sous_domaine', 'lib_sous_domaine', 'code_domaine', 'code_groupe_projet')
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

        // Bailleurs et statuts pour les filtres
        $Bailleurs = Acteur::whereHas('bailleurs')->get();
        $TypesStatuts = TypeStatut::all();

        return view('GestionSig.sigInfra', compact(
            'ecran', 'codeZoom', 'niveau', 'codeAlpha3', 'codeGroupeProjet', 
            'domainesAssocie', 'sousDomainesAssocie', 'Bailleurs', 'TypesStatuts'
        ));
    }

    // -----------------------------
    // Filtres (Groupes, Domaines, Sous-domaines) — sans session projet
    // -----------------------------
    public function filters(Request $request)
    {
        $alpha3 = session('pays_selectionne');
        if (!$alpha3) return response()->json(['error' => 'pays_selectionne manquant'], 400);

        // Groupes projets présents soit dans infrastructures, soit dans projets liés à des infrastructures
        $groupes = DB::table('groupe_projet as g')
            ->select('g.code','g.libelle')
            ->whereIn('g.code', function($q){
                $q->from('infrastructures')->select('code_groupe_projet')->whereNotNull('code_groupe_projet');
            })
            ->orWhereIn('g.code', function($q){
                $q->from('projets')->select(DB::raw('SUBSTRING(code_projet,4,3)'));
            })
            ->orderBy('g.libelle')
            ->get();

        // Domaines/Sous-domaines (tous — on filtrera côté front)
        $domaines = Domaine::select('code','libelle','groupe_projet_code')->orderBy('libelle')->get();
        $sous = SousDomaine::select('code_sous_domaine','lib_sous_domaine','code_domaine','code_groupe_projet')->orderBy('lib_sous_domaine')->get();

        return response()->json([
            'groupes'   => $groupes,
            'domaines'  => $domaines,
            'sous'      => $sous,
        ]);
    }

    // -----------------------------
    // Agrégat pour la carte
    //   - Unité = INFRASTRUCTURE bénéficiaire (via table jouir)
    //   - Métriques:
    //       * count = nb d'infras bénéficiaires
    //       * cost  = somme des coûts de projets répartis entre les infras concernées
    //   - Découpage par niveaux = préfixes de code_localite (2 / 4 / 6)
    //   - byDomain = basé sur code_sous_domaine du PROJET (prefix 2 = domaine)
    // -----------------------------
    public function aggregate(Request $request)
    {
        $alpha3  = session('pays_selectionne');
        if (!$alpha3) return response()->json(['error'=>'pays_selectionne manquant'], 400);

        $group   = $request->input('groupe') ?: session('projet_selectionne'); // optionnel (code groupe)
        $domaine = $request->input('domaine');       // optionnel (2 chars)
        $sous    = $request->input('sous');          // optionnel (code_sous_domaine)
        $dateDeb = $request->input('start_date');    // optionnel
        $dateFin = $request->input('end_date');      // optionnel
        $dateType = $request->input('date_type', 'prévisionnelles'); // 'prévisionnelles' | 'effectives'
        $finance = $request->input('finance', 'cumul');       // 'public' | 'private' | 'cumul' (par défaut)
        $statut  = $request->input('status');        // optionnel (id statut)
        $bailleur = $request->input('bailleur');     // optionnel (code_acteur)

        // Récupérer les couples Projet-Infrastructure via JOUIR
        // IMPORTANT: Seules les infrastructures présentes dans 'jouir' sont considérées comme bénéficiaires
        $sub = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->where('i.code_pays', $alpha3)
            ->when($group, function($q) use ($group) {
                // filtre soit sur groupe du projet (dans code_projet) soit sur l'infra
                $q->where(function($x) use ($group){
                    $x->where(DB::raw('SUBSTRING(p.code_projet,4,3)'), $group)
                      ->orWhere('i.code_groupe_projet', $group);
                });
            })
            ->when($domaine, function($q) use ($domaine){
                $q->where(DB::raw('LEFT(p.code_sous_domaine,2)'), $domaine);
            })
            ->when($sous, function($q) use ($sous){
                $q->where('p.code_sous_domaine', 'like', $sous.'%');
            })
            ->when($dateDeb || $dateFin, function($q) use ($dateDeb, $dateFin, $dateType) {
                if ($dateType === 'effectives') {
                    // Filtre sur dates effectives
                    $q->whereExists(function($subq) use ($dateDeb, $dateFin) {
                        $subq->select(DB::raw(1))
                            ->from('date_effective_projet as dep')
                            ->whereColumn('dep.code_projet', 'p.code_projet');
                        if ($dateDeb) {
                            $subq->where('dep.date_debut_effective', '>=', $dateDeb);
                        }
                        if ($dateFin) {
                            $subq->where('dep.date_fin_effective', '<=', $dateFin);
                        }
                    });
                } else {
                    // Filtre sur dates prévisionnelles (par défaut)
                    if ($dateDeb) {
                        $q->whereDate('p.date_demarrage_prevue', '>=', $dateDeb);
                    }
                    if ($dateFin) {
                        $q->whereDate('p.date_fin_prevue', '<=', $dateFin);
                    }
                }
            })
            ->when($statut, function($q) use ($statut) {
                // Filtre par statut (dernier statut du projet)
                $q->whereExists(function($subq) use ($statut) {
                    $subq->select(DB::raw(1))
                        ->from('projet_statut as ps')
                        ->whereColumn('ps.code_projet', 'p.code_projet')
                        ->where('ps.type_statut', $statut)
                        ->whereRaw('ps.date_statut = (SELECT MAX(ps2.date_statut) FROM projet_statut ps2 WHERE ps2.code_projet = p.code_projet)');
                });
            })
            ->when($bailleur, function($q) use ($bailleur) {
                // Filtre par bailleur
                $q->whereExists(function($subq) use ($bailleur) {
                    $subq->select(DB::raw(1))
                        ->from('financer as f')
                        ->whereColumn('f.code_projet', 'p.code_projet')
                        ->where('f.code_acteur', $bailleur);
                });
            })
            ->select([
                'p.code_projet',
                'p.libelle_projet',
                'p.cout_projet',
                'p.code_sous_domaine',
                'p.code_devise',
                DB::raw("SUBSTRING(p.code_projet,7,1) as type_fin"), // '1' public / '2' privé
                'i.code as infra_code',
                'i.libelle as infra_lib',
                'i.code_localite',
            ]);

        $rows = $sub->get();

        if ($rows->isEmpty()) {
            return response()->json(['projets'=>[]]);
        }

        // Comptage des infras par projet pour répartir le coût
        $infrasParProjet = [];
        foreach ($rows as $r) {
            $infrasParProjet[$r->code_projet] = ($infrasParProjet[$r->code_projet] ?? 0) + 1;
        }

        // Agrégation
        $agg = [];
        $indexNames = [];
        foreach ($rows as $r) {
            // allocation coût au prorata des infras
            $alloc = 0;
            if (!empty($r->cout_projet) && $infrasParProjet[$r->code_projet] > 0) {
                $alloc = (float)$r->cout_projet / $infrasParProjet[$r->code_projet];
            }

            $domain2 = substr($r->code_sous_domaine ?? '00', 0, 2);
            $isPublic = ((string)$r->type_fin) === '1';

            // codes par niveau (préfixes)
            $niv1 = substr($r->code_localite ?? '', 0, 2);
            $niv2 = substr($r->code_localite ?? '', 0, 4);
            $niv3 = substr($r->code_localite ?? '', 0, 6);

            foreach ([
                1 => $niv1,
                2 => $niv2,
                3 => $niv3,
            ] as $level => $code) {
                if (!$code) continue;

                $key = $code;
                if (!isset($agg[$key])) {
                    $agg[$key] = [
                        'name'     => null, // complété ensuite depuis LocalitesPays
                        'level'    => $level,
                        'code'     => $code,
                        // métriques
                        'count'    => 0,      // nb d'infras
                        'public'   => 0,      // nb d'infras (classées selon type projet)
                        'private'  => 0,
                        'cost'     => 0.0,    // coût réparti
                        'byDomain' => [],     // domaine-> {count, cost, public, private}
                    ];
                }

                // filtre finance à la volée (pour cohérence avec carte)
                $okFinance = ($finance === 'public' && $isPublic)
                          || ($finance === 'private' && !$isPublic)
                          || ($finance === 'cumul' || empty($finance));

                if ($okFinance) {
                    // NB: on compte l'INFRA (clé infra_code) une seule fois par niveau+code
                    //    → on peut dédoublonner par infra_code+code de niveau
                    $uniqKey = $key.'|'.$r->infra_code;
                    static $seen = [];
                    if (!isset($seen[$uniqKey])) {
                        $agg[$key]['count'] += 1;
                        $isPublic ? $agg[$key]['public']++ : $agg[$key]['private']++;
                        $seen[$uniqKey] = true;
                    }
                    $agg[$key]['cost']  += $alloc;

                    if (!isset($agg[$key]['byDomain'][$domain2])) {
                        $agg[$key]['byDomain'][$domain2] = ['count'=>0,'cost'=>0.0,'public'=>0,'private'=>0];
                    }
                    // Dédoublonnage aussi par domaine pour la même infra
                    $uniqDomKey = $key.'|'.$domain2.'|'.$r->infra_code;
                    static $seenDom = [];
                    if (!isset($seenDom[$uniqDomKey])) {
                        $agg[$key]['byDomain'][$domain2]['count'] += 1;
                        $isPublic
                            ? $agg[$key]['byDomain'][$domain2]['public']++
                            : $agg[$key]['byDomain'][$domain2]['private']++;
                        $seenDom[$uniqDomKey] = true;
                    }
                    $agg[$key]['byDomain'][$domain2]['cost'] += $alloc;
                }
            }
        }

        // Libellés de localités
        $pays = Pays::where('alpha3', $alpha3)->first();
        $locs = LocalitesPays::where('id_pays', $pays->id)->get(['id_niveau','code_rattachement','libelle']);
        $idxNames = [];
        foreach ($locs as $l) $idxNames[$l->code_rattachement] = $l->libelle;

        foreach ($agg as $k => $v) {
            $agg[$k]['name'] = $idxNames[$v['code']] ?? $v['code'];
        }

        return response()->json([
            'projets' => array_values($agg)
        ]);
    }

    // -----------------------------
    // Détails pour le drawer
    // Seules les infrastructures bénéficiaires (via table jouir) sont retournées
    // -----------------------------
    public function details(Request $request)
    {
        $alpha3 = session('pays_selectionne');
        if (!$alpha3) return response()->json(['error'=>'pays_selectionne manquant'], 400);

        $codePrefix = $request->input('code');     // ex: 01 / 0101 / 010101
        $finance    = $request->input('filter','cumul'); // 'public'|'private'|'cumul'
        $domaine2   = $request->input('domain');   // ex: '01' (optionnel)
        $limit      = (int) $request->input('limit', 1000);
        $group      = session('projet_selectionne'); // groupe projet de la session

        if (!$codePrefix) return response()->json(['error'=>'Paramètre code requis'], 422);

        $rows = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->where('i.code_pays', $alpha3)
            ->where('i.code_localite', 'like', $codePrefix.'%')
            ->when($group, function($q) use ($group) {
                $q->where(function($x) use ($group){
                    $x->where(DB::raw('SUBSTRING(p.code_projet,4,3)'), $group)
                      ->orWhere('i.code_groupe_projet', $group);
                });
            })
            ->when($domaine2, fn($q) => $q->where(DB::raw('LEFT(p.code_sous_domaine,2)'), $domaine2))
            ->select([
                'p.code_projet','p.libelle_projet','p.cout_projet','p.code_devise',
                DB::raw("SUBSTRING(p.code_projet,7,1) as type_fin"),
                'i.code as infra_code','i.libelle as infra_lib','i.latitude','i.longitude'
            ])
            ->limit($limit)
            ->get();

        // Finance filter
        $filtered = $rows->filter(function($r) use ($finance) {
            if ($finance === 'public')  return ((string)$r->type_fin) === '1';
            if ($finance === 'private') return ((string)$r->type_fin) !== '1';
            return true;
        })->values();

        // Réponse structurée (projets + infras distinctes)
        $projects = $filtered->map(fn($r) => [
            'code_projet'    => $r->code_projet,
            'libelle_projet' => $r->libelle_projet,
            'cout_projet'    => (float)($r->cout_projet ?? 0),
            'code_devise'    => $r->code_devise,
            'is_public'      => ((string)$r->type_fin) === '1',
            'infra_code'     => $r->infra_code,
            'infra_lib'      => $r->infra_lib,
        ]);

        $infras = $filtered->map(fn($r) => [
            'code'      => $r->infra_code,
            'libelle'   => $r->infra_lib,
            'lat'       => $r->latitude,
            'lng'       => $r->longitude,
        ])->unique('code')->values();

        return response()->json([
            'count'     => $projects->count(),
            'projects'  => $projects,
            'infras'    => $infras,
        ]);
    }

    // -----------------------------
    // Légende dynamique
    //  - metric=count → seuils en nb d’infras
    //  - metric=cost  → seuils en montant (unité brute; l’affichage divisera en G si besoin)
    // -----------------------------
    public function legend(Request $request)
    {
        $metric = $request->input('metric', 'count'); // 'count' | 'cost'

        // Exemple simple : 5 classes
        $seuils = ($metric === 'count')
            ? [
                ['borneInf'=>0,'borneSup'=>0,  'couleur'=>'#f1f5f9'],
                ['borneInf'=>1,'borneSup'=>2,  'couleur'=>'#c7d2fe'],
                ['borneInf'=>3,'borneSup'=>5,  'couleur'=>'#93c5fd'],
                ['borneInf'=>6,'borneSup'=>10, 'couleur'=>'#60a5fa'],
                ['borneInf'=>11,'borneSup'=>null,'couleur'=>'#2563eb'],
              ]
            : [
                ['borneInf'=>0,            'borneSup'=>0,             'couleur'=>'#f1f5f9'],
                ['borneInf'=>1_000_000,    'borneSup'=>500_000_000,   'couleur'=>'#fde68a'],
                ['borneInf'=>500_000_000,  'borneSup'=>2_000_000_000, 'couleur'=>'#fbbf24'],
                ['borneInf'=>2_000_000_000,'borneSup'=>5_000_000_000, 'couleur'=>'#f59e0b'],
                ['borneInf'=>5_000_000_000,'borneSup'=>null,          'couleur'=>'#d97706'],
              ];

        return response()->json([
            'label'  => $metric === 'count' ? 'Nombre d’infrastructures bénéficiaires' : 'Montant réparti des projets',
            'seuils' => $seuils,
        ]);
    }
}
