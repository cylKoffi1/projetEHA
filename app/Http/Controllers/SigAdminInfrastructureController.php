<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\Bailleur;
use App\Models\DecoupageAdminPays;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\FamilleInfrastructure;
use App\Models\Financer;
use App\Models\GroupeProjetPaysUser;
use App\Models\Infrastructure;
use App\Models\Jouir;
use App\Models\LegendeCarte;
use App\Models\LocalitesPays;
use App\Models\Pays;
use App\Models\Projet;
use App\Models\ProjetStatut;
use App\Models\SousDomaine;
use App\Models\StatutProjet;
use App\Models\TypeStatut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SigAdminInfrastructureController extends Controller
{
    // -------------------------------------------------------
    // 🔸 LÉGENDE dynamique (utilisée par map.js -> /api/legende/{groupe}?typeFin=)
    //   - typeFin 1 = métrique "count" (ici = nb d'infrastructures bénéficiaires)
    //   - typeFin 2 = métrique "cost"
    // -------------------------------------------------------
    public function getByGroupe($groupe)
    {
        $typeFin = request()->input('typeFin', 1);
        $groupeLegende = $typeFin == 2 ? 'COMMUN' : session('projet_selectionne');

        $legende = LegendeCarte::where('groupe_projet', $groupeLegende)
            ->where('typeFin', $typeFin)
            ->with(['seuils' => function ($q) {
                $q->orderBy('borneInf');
            }])
            ->first();

        if (!$legende) {
            return response()->json([
                'debug' => ['groupe' => $groupe, 'typeFin' => $typeFin],
                'groupe_projet' => $groupe,
                'label' => $typeFin == 2 ? 'Montant agrégé (réparti)' : 'Nombre d’infrastructures bénéficiaires',
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
                    'couleur'  => $s->couleur
                ];
            })->values()
        ]);
    }

    // -------------------------------------------------------
    // 🔸 Page principale "Autrecarte" (vue sigAdmin.blade.php)
    // -------------------------------------------------------


    // -------------------------------------------------------
    // 🔸 Page infrastructures (optionnelle – utilisée par une autre vue)
    // -------------------------------------------------------
    public function page(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $user  = Auth::user();

        $alpha3 = session('pays_selectionne');
        if (!$alpha3) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de pays.');
        }

        $pays = Pays::where('alpha3', $alpha3)->first();
        if (!$pays) {
            return redirect()->route('projets.index')->with('error', 'Le pays sélectionné est introuvable.');
        }

        $codeAlpha3 = $pays->alpha3;
        $codeZoom   = Pays::select('minZoom', 'maxZoom')->where('alpha3', $codeAlpha3)->first();

        $niveau = DB::table('decoupage_admin_pays')
            ->join('decoupage_administratif', 'decoupage_admin_pays.code_decoupage', '=', 'decoupage_administratif.code_decoupage')
            ->where('id_pays', $pays->id)
            ->select('decoupage_admin_pays.code_decoupage','decoupage_admin_pays.num_niveau_decoupage','decoupage_administratif.libelle_decoupage')
            ->orderBy('num_niveau_decoupage')
            ->get();

        return view('GestionSig.sigInfra', compact('ecran','codeZoom','niveau','codeAlpha3'));
    }

    // -------------------------------------------------------
    // 🔸 Utilitaires code projet
    // -------------------------------------------------------
    private function reconstruireCodeProjet($project)
    {
        $country = $project->code_alpha3_pays ?? 'CIV';
        $group   = session('projet_selectionne') ?? 'TIC';
        $typeFinancement = '1';
        $groupeEtType = $group.$typeFinancement;
        $locCode = $project->code_localisation ?? '0101';
        $sousDomaine = $project->code_sous_domaine ?? '0000';
        $annee = $project->date_demarrage_prevue
            ? \Carbon\Carbon::parse($project->date_demarrage_prevue)->format('Y')
            : '0000';
        $ordre = '01';
        return "{$country}{$groupeEtType}_{$locCode}_{$sousDomaine}_{$annee}_{$ordre}";
    }

    private function decomposerCodeProjet($codeProjet)
    {
        $parts = explode('_', $codeProjet);
        return [
            'pays'               => substr($codeProjet, 0, 3),
            'groupe_projet'      => substr($codeProjet, 3, 3),
            'type_financement'   => substr($codeProjet, 6, 1),
            'code_localisation'  => $parts[1] ?? '0000',
            'code_domaine'       => substr($parts[2] ?? '0000', 0, 2),
            'code_sous_domaine'  => $parts[2] ?? '0000',
            'annee'              => $parts[3] ?? '0000',
            'ordre'              => $parts[4] ?? '01',
        ];
    }

    // -------------------------------------------------------
    // 🔸 AGRÉGAT principal pour la carte (utilisé par map.js → /api/projects)
    //     ➜ Unité = NOMBRE D’INFRASTRUCTURES bénéficiaires
    //     ➜ Répartition coût = coût projet / nb d’infras bénéficiaires du projet
    //     ➜ Découpage = codes 2 / 4 / 6 de la localité
    // -------------------------------------------------------
    public function getProjects(Request $request)
    {
        $alpha3 = session('pays_selectionne');
        $group  = session('projet_selectionne');

        if (!$alpha3 || !$group) {
            return response()->json(['error' => 'Contexte pays/groupe manquant'], 400);
        }

        // Jointure Jouir (projet↔infra) + Infrastructures pour obtenir la localité
        $rows = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->where('i.code_pays', $alpha3)
            ->where(DB::raw('SUBSTRING(p.code_projet,4,3)'), $group)
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
            ])
            ->get();

        if ($rows->isEmpty()) return response()->json([]);

        // nombre d’infras par projet → pour répartir le coût
        $infraByProject = [];
        foreach ($rows as $r) {
            $infraByProject[$r->code_projet] = ($infraByProject[$r->code_projet] ?? 0) + 1;
        }

        $agg = [];
        $seenInfraLevel = [];   // dédoublonnage "infra comptée 1 fois par zone"
        $seenInfraDom   = [];   // dédoublonnage pour byDomain

        foreach ($rows as $r) {
            $alloc = 0;
            if (!empty($r->cout_projet) && $infraByProject[$r->code_projet] > 0) {
                $alloc = (float)$r->cout_projet / $infraByProject[$r->code_projet];
            }

            $domain2  = substr($r->code_sous_domaine ?? '00', 0, 2);
            $isPublic = ((string)$r->type_fin) === '1';

            $niv1 = substr($r->code_localite ?? '', 0, 2);
            $niv2 = substr($r->code_localite ?? '', 0, 4);
            $niv3 = substr($r->code_localite ?? '', 0, 6);

            foreach ([1 => $niv1, 2 => $niv2, 3 => $niv3] as $level => $code) {
                if (!$code) continue;
                if (!isset($agg[$code])) {
                    $agg[$code] = [
                        'name'     => null,
                        'level'    => $level,
                        'code'     => $code,
                        'count'    => 0,     // 👉 nb d’infras bénéficiaires
                        'public'   => 0,     // idem, ventilés par type
                        'private'  => 0,
                        'cost'     => 0.0,   // coût réparti
                        'byDomain' => [],    // '01'=>{count,cost,public,private}
                    ];
                }

                // dedup infra au sein d'un level
                $k = $code.'|'.$r->infra_code;
                if (!isset($seenInfraLevel[$k])) {
                    $agg[$code]['count']++;
                    $isPublic ? $agg[$code]['public']++ : $agg[$code]['private']++;
                    $seenInfraLevel[$k] = 1;
                }
                $agg[$code]['cost'] += $alloc;

                if (!isset($agg[$code]['byDomain'][$domain2])) {
                    $agg[$code]['byDomain'][$domain2] = ['count'=>0,'cost'=>0.0,'public'=>0,'private'=>0];
                }
                $dk = $code.'|'.$domain2.'|'.$r->infra_code;
                if (!isset($seenInfraDom[$dk])) {
                    $agg[$code]['byDomain'][$domain2]['count']++;
                    $isPublic
                        ? $agg[$code]['byDomain'][$domain2]['public']++
                        : $agg[$code]['byDomain'][$domain2]['private']++;
                    $seenInfraDom[$dk] = 1;
                }
                $agg[$code]['byDomain'][$domain2]['cost'] += $alloc;
            }
        }

        // noms de localités
        $pays = Pays::where('alpha3', $alpha3)->first();
        $locs = LocalitesPays::where('id_pays', $pays->id)->get(['id_niveau','code_rattachement','libelle']);
        $names = [];
        foreach ($locs as $l) $names[$l->code_rattachement] = $l->libelle;

        foreach ($agg as $k => $v) {
            $agg[$k]['name'] = $names[$v['code']] ?? $v['code'];
        }

        // map.js attend un tableau (values)
        return response()->json(array_values($agg));
    }

    // -------------------------------------------------------
    // 🔸 Options filtrables (bailleurs/statuts) – au besoin
    // -------------------------------------------------------
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

        $filteredCodes = $query->pluck('code_projet');

        $statuts = ProjetStatut::whereIn('code_projet', $filteredCodes)
            ->with('statut')
            ->get()
            ->map(fn($ps) => [
                'id'      => $ps->type_statut,
                'libelle' => $ps->statut->libelle ?? 'Statut inconnu',
            ])
            ->unique('id')
            ->values();

        $bailleurs = Financer::whereIn('code_projet', $filteredCodes)
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

    // -------------------------------------------------------
    // 🔸 Filtres + agrégat (retourne la même structure que /api/projects)
    // -------------------------------------------------------
    public function getFiltreOptionsEtProjets(Request $request)
    {
        $country = session('pays_selectionne');
        $group   = session('projet_selectionne');
        if (!$country || !$group) {
            return response()->json(['error' => 'Contexte pays/groupe manquant'], 400);
        }

        $pays = Pays::where('alpha3', $country)->first();
        if (!$pays) return response()->json(['error' => 'Pays inconnu'], 404);

        $start  = $request->input('start_date');
        $end    = $request->input('end_date');
        $type   = $request->input('date_type');
        $statut = $request->input('status');
        $bail   = $request->input('bailleur');

        $q = Projet::where('code_projet', 'like', $country.$group.'%');

        if ($start || $end) $type = $type ?: 'prévisionnelles';
        if ($type === 'prévisionnelles') {
            if ($start) $q->where('date_demarrage_prevue', '>=', $start);
            if ($end)   $q->where('date_fin_prevue',      '<=', $end);
        } elseif ($type === 'effectives') {
            $q->whereHas('dateEffective', function ($qq) use ($start, $end) {
                if ($start) $qq->where('date_debut_effective', '>=', $start);
                if ($end)   $qq->where('date_fin_effective',   '<=', $end);
            });
        }

        if (!empty($bail))   $q->whereHas('financements', fn($qq) => $qq->where('code_acteur', $bail));
        if (!empty($statut)) $q->whereHas('statuts',      fn($qq) => $qq->where('type_statut', $statut));

        $filtered = $q->get();
        if ($filtered->isEmpty()) {
            return response()->json([
                'projets'      => [],
                'bailleurs'    => [],
                'statuts'      => [],
                'public_cost'  => 0,
                'private_cost' => 0,
                'total_cost'   => 0,
            ]);
        }

        $codes = $filtered->pluck('code_projet');

        // Récupérer les liaisons projet↔infra sur ce sous-ensemble
        $rows = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->whereIn('j.code_projet', $codes)
            ->select([
                'p.code_projet','p.cout_projet','p.code_sous_domaine',
                DB::raw("SUBSTRING(p.code_projet,7,1) as type_fin"),
                'i.code as infra_code','i.code_localite'
            ])
            ->get();

        // nombre d’infras par projet (pour allocation coût)
        $infraByProject = [];
        foreach ($rows as $r) $infraByProject[$r->code_projet] = ($infraByProject[$r->code_projet] ?? 0) + 1;

        $agg = []; $pubCost=0; $privCost=0;
        $seenInfraLevel = []; $seenInfraDom = [];

        foreach ($rows as $r) {
            $alloc = 0;
            if (!empty($r->cout_projet) && $infraByProject[$r->code_projet] > 0) {
                $alloc = (float)$r->cout_projet / $infraByProject[$r->code_projet];
            }
            $isPublic = ((string)$r->type_fin) === '1';
            $isPublic ? $pubCost += $alloc : $privCost += $alloc;

            $dom2 = substr($r->code_sous_domaine ?? '00', 0, 2);
            $niv1 = substr($r->code_localite ?? '', 0, 2);
            $niv2 = substr($r->code_localite ?? '', 0, 4);
            $niv3 = substr($r->code_localite ?? '', 0, 6);

            foreach ([1=>$niv1,2=>$niv2,3=>$niv3] as $level=>$code){
                if (!$code) continue;
                if (!isset($agg[$code])) {
                    $agg[$code] = [
                        'name'=>null,'level'=>$level,'code'=>$code,
                        'count'=>0,'public'=>0,'private'=>0,'cost'=>0.0,'byDomain'=>[]
                    ];
                }

                $k = $code.'|'.$r->infra_code;
                if (!isset($seenInfraLevel[$k])) {
                    $agg[$code]['count']++;
                    $isPublic ? $agg[$code]['public']++ : $agg[$code]['private']++;
                    $seenInfraLevel[$k] = 1;
                }
                $agg[$code]['cost'] += $alloc;

                if (!isset($agg[$code]['byDomain'][$dom2])) {
                    $agg[$code]['byDomain'][$dom2] = ['count'=>0,'cost'=>0.0,'public'=>0,'private'=>0];
                }
                $dk = $code.'|'.$dom2.'|'.$r->infra_code;
                if (!isset($seenInfraDom[$dk])) {
                    $agg[$code]['byDomain'][$dom2]['count']++;
                    $isPublic ? $agg[$code]['byDomain'][$dom2]['public']++ : $agg[$code]['byDomain'][$dom2]['private']++;
                    $seenInfraDom[$dk] = 1;
                }
                $agg[$code]['byDomain'][$dom2]['cost'] += $alloc;
            }
        }

        $locs = LocalitesPays::where('id_pays', $pays->id)->get(['id_niveau','code_rattachement','libelle']);
        $names = [];
        foreach ($locs as $l) $names[$l->code_rattachement] = $l->libelle;
        foreach ($agg as $k=>$v) $agg[$k]['name'] = $names[$v['code']] ?? $v['code'];

        $bailleursList = Financer::whereIn('code_projet', $codes)
            ->with('bailleur')
            ->get()
            ->map(fn($f)=>[
                'code_acteur'=>$f->code_acteur,
                'nom'=>$f->bailleur->libelle_court ?? 'Bailleur'
            ])->unique('code_acteur')->values();

        $statutsList = ProjetStatut::whereIn('code_projet', $codes)
            ->with('statut')
            ->get()
            ->map(fn($s)=>['id'=>$s->type_statut,'libelle'=>$s->statut->libelle ?? 'Statut'])
            ->unique('id')->values();

        return response()->json([
            'projets'      => array_values($agg),
            'bailleurs'    => $bailleursList,
            'statuts'      => $statutsList,
            'public_cost'  => $pubCost,
            'private_cost' => $privCost,
            'total_cost'   => $pubCost + $privCost,
        ]);
    }

    // -------------------------------------------------------
    // 🔸 Liste projets "brute" (utile pour la carte Afrique)
    // -------------------------------------------------------
    public function getAllProjects()
    {
        $projects = Projet::with('pays')->get()->map(function ($project) {
            return [
                'code_projet' => $project->code_projet,
                'is_public'   => substr($project->code_projet, 6, 1) === '1',
                'country_name'=> optional($project->pays)->libelle ?? substr($project->code_projet, 0, 3)
            ];
        });
        return response()->json($projects);
    }

    // -------------------------------------------------------
    // 🔸 Détails projets pour un code de localisation (tiroir)
    //     /api/project-details?code=01[..]&filter=cumul|public|private&domain=01&limit=1000
    // -------------------------------------------------------
    public function getProjectDetails(Request $request)
    {
        $country = session('pays_selectionne');
        $group   = session('projet_selectionne');

        if (!$country || !$group) {
            return response()->json(['error' => 'Contexte pays/groupe manquant dans la session.'], 400);
        }

        $locPrefix     = $request->input('code');
        $financeFilter = $request->input('filter', 'cumul'); // cumul|public|private
        $domainPrefix  = $request->input('domain');          // ex "01"
        $limit         = (int) $request->input('limit', 1000);

        if (!$locPrefix) {
            return response()->json(['error' => 'Paramètre code (localisation) requis.'], 422);
        }

        // on part des projets du groupe/pays puis on filtre par localisation via code_projet
        $codePattern = $country.$group.'%';

        try {
            $projects = Projet::where('code_projet', 'like', $codePattern)
                ->when($domainPrefix, fn($q) => $q->where('code_sous_domaine', 'like', $domainPrefix.'%'))
                ->limit($limit)
                ->get();

            $results = [];
            foreach ($projects as $p) {
                $codeProjet = $p->code_projet ?: $this->reconstruireCodeProjet($p);
                $c = $this->decomposerCodeProjet($codeProjet);
                $loc = $c['code_localisation'];
                if (strpos($loc, $locPrefix) !== 0) continue;

                $isPublic = $c['type_financement'] === '1';
                if ($financeFilter === 'public' && !$isPublic) continue;
                if ($financeFilter === 'private' && $isPublic) continue;

                $results[] = [
                    'code_projet'         => $codeProjet,
                    'libelle_projet'      => $p->libelle_projet,
                    'cout_projet'         => $p->cout_projet ?? 0,
                    'is_public'           => $isPublic,
                    'code_sous_domaine'   => $p->code_sous_domaine,
                    'code_localisation'   => $loc,
                    'date_demarrage_prevue'=> $p->date_demarrage_prevue,
                    'date_fin_prevue'     => $p->date_fin_prevue,
                    'code_devise'         => $p->code_devise
                ];
            }

            return response()->json([
                'count'    => count($results),
                'projects' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Erreur lors de la récupération des détails des projets',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // -------------------------------------------------------
    // 🔸 API "aggregate" (infras) — utilisée par d’autres écrans
    // -------------------------------------------------------
    public function aggregate(Request $request)
    {
        $alpha3  = session('pays_selectionne');
        if (!$alpha3) return response()->json(['error'=>'pays_selectionne manquant'], 400);

        $group   = $request->input('groupe');
        $domaine = $request->input('domaine');
        $sous    = $request->input('sous');
        $dateDeb = $request->input('start_date');
        $dateFin = $request->input('end_date');
        $finance = $request->input('finance'); // public|private|cumul

        $sub = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->where('i.code_pays', $alpha3)
            ->when($group, function($q) use ($group) {
                $q->where(function($x) use ($group){
                    $x->where(DB::raw('SUBSTRING(p.code_projet,4,3)'), $group)
                      ->orWhere('i.code_groupe_projet', $group);
                });
            })
            ->when($domaine, fn($q)=>$q->where(DB::raw('LEFT(p.code_sous_domaine,2)'), $domaine))
            ->when($sous,    fn($q)=>$q->where('p.code_sous_domaine', 'like', $sous.'%'))
            ->when($dateDeb, fn($q)=>$q->whereDate('p.date_demarrage_prevue', '>=', $dateDeb))
            ->when($dateFin, fn($q)=>$q->whereDate('p.date_fin_prevue',      '<=', $dateFin))
            ->select([
                'p.code_projet','p.libelle_projet','p.cout_projet','p.code_sous_domaine','p.code_devise',
                DB::raw("SUBSTRING(p.code_projet,7,1) as type_fin"),
                'i.code as infra_code','i.libelle as infra_lib','i.code_localite',
            ])->get();

        if ($sub->isEmpty()) return response()->json(['projets'=>[]]);

        $infrasParProjet = [];
        foreach ($sub as $r) $infrasParProjet[$r->code_projet] = ($infrasParProjet[$r->code_projet] ?? 0) + 1;

        $agg = []; $seen=[]; $seenDom=[];
        foreach ($sub as $r) {
            $alloc = 0;
            if (!empty($r->cout_projet) && $infrasParProjet[$r->code_projet] > 0) {
                $alloc = (float)$r->cout_projet / $infrasParProjet[$r->code_projet];
            }
            $domain2 = substr($r->code_sous_domaine ?? '00', 0, 2);
            $isPublic = ((string)$r->type_fin) === '1';

            $niv1 = substr($r->code_localite ?? '', 0, 2);
            $niv2 = substr($r->code_localite ?? '', 0, 4);
            $niv3 = substr($r->code_localite ?? '', 0, 6);

            foreach ([1=>$niv1,2=>$niv2,3=>$niv3] as $level=>$code){
                if (!$code) continue;
                if (!isset($agg[$code])) {
                    $agg[$code] = [
                        'name'=>null,'level'=>$level,'code'=>$code,
                        'count'=>0,'public'=>0,'private'=>0,'cost'=>0.0,'byDomain'=>[]
                    ];
                }

                $okFinance = ($finance === 'public'  && $isPublic)
                          || ($finance === 'private' && !$isPublic)
                          || ($finance === 'cumul'   || empty($finance));

                if ($okFinance) {
                    $k = $code.'|'.$r->infra_code;
                    if (!isset($seen[$k])) {
                        $agg[$code]['count']++;
                        $isPublic ? $agg[$code]['public']++ : $agg[$code]['private']++;
                        $seen[$k] = 1;
                    }
                    $agg[$code]['cost'] += $alloc;

                    if (!isset($agg[$code]['byDomain'][$domain2])) {
                        $agg[$code]['byDomain'][$domain2] = ['count'=>0,'cost'=>0.0,'public'=>0,'private'=>0];
                    }
                    $kd = $code.'|'.$domain2.'|'.$r->infra_code;
                    if (!isset($seenDom[$kd])) {
                        $agg[$code]['byDomain'][$domain2]['count']++;
                        $isPublic ? $agg[$code]['byDomain'][$domain2]['public']++ : $agg[$code]['byDomain'][$domain2]['private']++;
                        $seenDom[$kd] = 1;
                    }
                    $agg[$code]['byDomain'][$domain2]['cost'] += $alloc;
                }
            }
        }

        $pays = Pays::where('alpha3', $alpha3)->first();
        $locs = LocalitesPays::where('id_pays', $pays->id)->get(['id_niveau','code_rattachement','libelle']);
        $names = [];
        foreach ($locs as $l) $names[$l->code_rattachement] = $l->libelle;
        foreach ($agg as $k=>$v) $agg[$k]['name'] = $names[$v['code']] ?? $v['code'];

        return response()->json(['projets'=>array_values($agg)]);
    }

    // -------------------------------------------------------
    // 🔸 Détails (projets & infras distinctes) – variante
    // -------------------------------------------------------
    public function details(Request $request)
    {
        $alpha3 = session('pays_selectionne');
        if (!$alpha3) return response()->json(['error'=>'pays_selectionne manquant'], 400);

        $codePrefix = $request->input('code');
        $finance    = $request->input('filter','cumul');
        $domaine2   = $request->input('domain');
        $limit      = (int) $request->input('limit', 1000);

        if (!$codePrefix) return response()->json(['error'=>'Paramètre code requis'], 422);

        $rows = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->where('i.code_pays', $alpha3)
            ->where('i.code_localite', 'like', $codePrefix.'%')
            ->when($domaine2, fn($q) => $q->where(DB::raw('LEFT(p.code_sous_domaine,2)'), $domaine2))
            ->select([
                'p.code_projet','p.libelle_projet','p.cout_projet','p.code_devise',
                DB::raw("SUBSTRING(p.code_projet,7,1) as type_fin"),
                'i.code as infra_code','i.libelle as infra_lib','i.latitude','i.longitude'
            ])
            ->limit($limit)
            ->get();

        $filtered = $rows->filter(function($r) use ($finance) {
            if ($finance === 'public')  return ((string)$r->type_fin) === '1';
            if ($finance === 'private') return ((string)$r->type_fin) !== '1';
            return true;
        })->values();

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
            'code'    => $r->infra_code,
            'libelle' => $r->infra_lib,
            'lat'     => $r->latitude,
            'lng'     => $r->longitude,
        ])->unique('code')->values();

        return response()->json([
            'count'     => $projects->count(),
            'projects'  => $projects,
            'infras'    => $infras,
        ]);
    }

    // -------------------------------------------------------
    // 🔸 Légende simple fallback (si aucune en BDD)
    // -------------------------------------------------------
    public function legend(Request $request)
    {
        $metric = $request->input('metric', 'count'); // 'count' (infras) | 'cost'

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
            'label'  => $metric === 'count'
                ? 'Nombre d’infrastructures bénéficiaires'
                : 'Montant réparti des projets',
            'seuils' => $seuils,
        ]);
    }
}
