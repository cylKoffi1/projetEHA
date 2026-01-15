<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Ecran;
use App\Models\Executer;
use App\Models\Controler;
use App\Models\Posseder;
use App\Models\Financer;
use App\Models\Projet;
use App\Models\ProjetStatut;
use App\Models\AppuiProjet;
use App\Models\EtudeProjet as ModelsEtudeProjet;

class StatController extends Controller
{
    /* --------------------------------------------------------------
     |  Détecter les rôles de l’acteur connecté
     |-------------------------------------------------------------- */
    private function detectRoles(string $codeActeur): array
    {
        $roles = [];

        if (Executer::where('code_acteur',$codeActeur)->where('is_active',1)->exists())
            $roles[] = 'chef_projet';

        if (Controler::where('code_acteur',$codeActeur)->where('is_active',1)->exists())
            $roles[] = 'moe';

        if (Posseder::where('code_acteur',$codeActeur)->where('is_active',1)->where('isAssistant',0)->exists())
            $roles[] = 'mo';

        if (Financer::where('code_acteur',$codeActeur)->where('is_active',1)->exists())
            $roles[] = 'bailleur';

        return $roles;
    }

    /* --------------------------------------------------------------
     |  Génère la map des statuts
     |-------------------------------------------------------------- */
    private function statusMap()
    {
        return [
            'prevu'     => ["Prévu"],
            'en_cours'  => ["En cours"],
            'cloture'   => ["Clôturé","Clôturés"],
            'termine'   => ["Terminé"],
            'redemarre' => ["Redémarré"],
            'suspendu'  => ["Suspendu"],
            'annule'    => ["Annulé"],
        ];
    }

    /* --------------------------------------------------------------
     |  Ordre des statuts + libellés affichés
     |-------------------------------------------------------------- */
    private function statusMeta()
    {
        return [
            'order' => ['prevu','en_cours','cloture','termine','redemarre','suspendu','annule'],
            'titles' => [
                'prevu'      => 'Prévu',
                'en_cours'   => 'En cours',
                'cloture'    => 'Clôturé',
                'termine'    => 'Terminé',
                'redemarre'  => 'Redémarré',
                'suspendu'   => 'Suspendu',
                'annule'     => 'Annulé',
            ]
        ];
    }

    /* --------------------------------------------------------------
     |  Gabarit vide : utilisé pour nombre ET finance
     |-------------------------------------------------------------- */
    private function emptyBag(array $statusOrder)
    {
        $bag = [];
        foreach ($statusOrder as $k) {
            $bag["total_$k"]  = 0;
            $bag["public_$k"] = 0;
            $bag["prive_$k"]  = 0;
        }
        return $bag;
    }

    /* --------------------------------------------------------------
     |  Base : dernier statut par projet (pour un prefix donné)
     |-------------------------------------------------------------- */
    private function baseLatest(string $prefix)
    {
        $latest = DB::table('projet_statut as ps1')
            ->select('ps1.code_projet', DB::raw('MAX(ps1.date_statut) as max_date'))
            ->groupBy('ps1.code_projet');

        return DB::table('projet_statut as ps')
            ->joinSub($latest, 'lp', function ($j) {
                $j->on('ps.code_projet','=','lp.code_projet')
                  ->on('ps.date_statut','=','lp.max_date');
            })
            ->join('type_statut as ts','ts.id','=','ps.type_statut')
            ->where('ps.code_projet','like',$prefix);
    }

    /* --------------------------------------------------------------
     |  Agrégateur NOMBRE
     |-------------------------------------------------------------- */
    private function aggregateCount($builder, array $statusMap, array $statusOrder)
    {
        $empty = $this->emptyBag($statusOrder);

        $isPublic = "SUBSTR(ps.code_projet,7,1)='1'";
        $isPrive  = "SUBSTR(ps.code_projet,7,1)='2'";

        $parts = [];

        foreach ($statusOrder as $k) {
            $in = "('" . implode("','", $statusMap[$k]) . "')";
            $parts[] = "SUM(CASE WHEN ts.libelle IN $in THEN 1 ELSE 0 END) as total_$k";
            $parts[] = "SUM(CASE WHEN ts.libelle IN $in AND $isPublic THEN 1 ELSE 0 END) as public_$k";
            $parts[] = "SUM(CASE WHEN ts.libelle IN $in AND $isPrive  THEN 1 ELSE 0 END) as prive_$k";
        }

        $row = $builder->selectRaw(implode(",\n", $parts))->first();
        if (!$row) return $empty;

        foreach ($empty as $key => $val) {
            $empty[$key] = (int)($row->$key ?? 0);
        }

        return $empty;
    }

    /* --------------------------------------------------------------
     |  Agrégateur FINANCE
     |-------------------------------------------------------------- */
    private function aggregateFinance($builder, array $statusMap, array $statusOrder)
    {
        $empty = $this->emptyBag($statusOrder);

        $isPublic = "SUBSTR(ps.code_projet,7,1)='1'";
        $isPrive  = "SUBSTR(ps.code_projet,7,1)='2'";

        $parts = [];

        foreach ($statusOrder as $k) {
            $in = "('" . implode("','", $statusMap[$k]) . "')";
            $parts[] = "SUM(CASE WHEN ts.libelle IN $in THEN COALESCE(p.cout_projet,0) ELSE 0 END) as total_$k";
            $parts[] = "SUM(CASE WHEN ts.libelle IN $in AND $isPublic THEN COALESCE(p.cout_projet,0) ELSE 0 END) as public_$k";
            $parts[] = "SUM(CASE WHEN ts.libelle IN $in AND $isPrive  THEN COALESCE(p.cout_projet,0) ELSE 0 END) as prive_$k";
        }

        $row = $builder->selectRaw(implode(",\n", $parts))->first();
        if (!$row) return $empty;

        foreach ($empty as $key => $val) {
            $empty[$key] = (float)($row->$key ?? 0);
        }

        return $empty;
    }

    /* ==============================================================
     |  PAGE PRINCIPALE FUSIONNÉE (Nombre + Finance)
     |  -> Affiche le tableau avec switch entre Nombre / Finance
     |============================================================== */
         public function statDashboard(Request $request)
         {
             /* ============================
              *       PARAMETRES
              * ============================ */
             $ecran   = Ecran::find($request->input('ecran_id'));
             $typeVue = $request->input('vue', 'nombre'); // nombre | finance
             $mode    = $request->input('mode', 'table'); // table | graph
     
             $country = session('pays_selectionne');
             $group   = session('projet_selectionne');
             $prefix  = ($country ?? '') . ($group ?? '') . '%';
     
             /* === DATES (prévisionnelles + effectives) === */
             $dateDebutPrev = $request->input('date_debut_prev');
             $dateFinPrev   = $request->input('date_fin_prev');
             $dateDebutEff  = $request->input('date_debut_effectif');
             $dateFinEff    = $request->input('date_fin_effectif');
     
             /* ============================
              *      META STATUTS
              * ============================ */
             $meta         = $this->statusMeta();
             $statusOrder  = $meta['order'];
             $statusTitles = $meta['titles'];
             $statusMap    = $this->statusMap();
     
             $empty = $this->emptyBag($statusOrder);
     
             /* ============================
              *  BASE = dernier statut / projet
              * ============================ */
             $baseLatest = $this->baseLatest($prefix)
                                 ->join('projets as p', 'p.code_projet', '=', 'ps.code_projet');
     
             /* === FILTRE PAR DATES (INFRASTRUCTURE) === */
             if ($dateDebutPrev) {
                 $baseLatest->where('p.date_demarrage_prevue', '>=', $dateDebutPrev);
             }
             if ($dateFinPrev) {
                 $baseLatest->where('p.date_fin_prevue', '<=', $dateFinPrev);
             }
     
             if ($dateDebutEff || $dateFinEff) {
                 $baseLatest->leftJoin('dates_effectives_projet as dep', 'dep.code_projet', '=', 'p.code_projet');
     
                 if ($dateDebutEff) {
                     $baseLatest->where('dep.date_debut_effective', '>=', $dateDebutEff);
                 }
                 if ($dateFinEff) {
                     $baseLatest->where('dep.date_fin_effective', '<=', $dateFinEff);
                 }
             }
     
             /* =
              * Pour la Finance, besoin du montant aussi
              * ============================ */
             $baseLatestFinance = clone $baseLatest;
     
             /* ============================
              *       NATIONAL
              * ============================ */
             if ($typeVue === 'finance') {
                 $national = $this->aggregateFinance($baseLatestFinance, $statusMap, $statusOrder);
             } else {
                 $national = $this->aggregateCount($baseLatest, $statusMap, $statusOrder);
             }
     
             /* ============================
              *       ROLES
              * ============================ */
             $roles = [];
             $codeActeur = auth()->user()->acteur->code_acteur ?? null;
             if ($codeActeur) $roles = $this->detectRoles($codeActeur);
     
             $idsForRole = function(string $role) use ($codeActeur, $prefix) {
                 return match($role) {
                     'chef_projet' => Executer::where('code_acteur',$codeActeur)
                                         ->where('is_active',1)->where('code_projet','like',$prefix)->pluck('code_projet'),
                     'moe'         => Controler::where('code_acteur',$codeActeur)
                                         ->where('is_active',1)->where('code_projet','like',$prefix)->pluck('code_projet'),
                     'mo'          => Posseder::where('code_acteur',$codeActeur)
                                         ->where('isAssistant',0)->where('is_active',1)
                                         ->where('code_projet','like',$prefix)->pluck('code_projet'),
                     'bailleur'    => Financer::where('code_acteur',$codeActeur)
                                         ->where('is_active',1)->where('code_projet','like',$prefix)->pluck('code_projet'),
                     default       => collect(),
                 };
             };
     
             $mine = $empty;
             $statsMoi = [];
     
             foreach ($roles as $role) {
                 $ids = $idsForRole($role);
     
                 if ($ids->isEmpty()) {
                     $statsMoi[$role] = $empty;
                     continue;
                 }
     
                 if ($typeVue === 'finance') {
                     $bag = $this->aggregateFinance(
                         (clone $baseLatestFinance)->whereIn('ps.code_projet', $ids),
                         $statusMap,
                         $statusOrder
                     );
                 } else {
                     $bag = $this->aggregateCount(
                         (clone $baseLatest)->whereIn('ps.code_projet', $ids),
                         $statusMap,
                         $statusOrder
                     );
                 }
     
                 $statsMoi[$role] = $bag;
     
                 foreach ($bag as $k => $v) {
                     $mine[$k] += $v;
                 }
             }
     
             /* ============================
              *       RATIO
              * ============================ */
             $ratio = $empty;
             foreach ($ratio as $k => $v) {
                 $den = $national[$k] ?? 0;
                 $num = $mine[$k] ?? 0;
                 $ratio[$k] = $den > 0 ? round(($num / $den) * 100, 0) : 0;
             }
     
             /* ============================
              *       PRÉVISUALISATION
              * ============================ */
             $previsuData = $this->buildPrevisualisationGraphiqueData(
                 $dateDebutPrev, $dateFinPrev, $dateDebutEff, $dateFinEff
             );
     
             /* ============================
              *        VIEW
              * ============================ */
             return view('TableauBord.EtudeProjet',
                 array_merge(
                     [
                         'ecran'        => $ecran,
                         'roles'        => $roles,
                         'statusOrder'  => $statusOrder,
                         'statusTitles' => $statusTitles,
                         'typeVue'      => $typeVue,
                         'national'     => $national,
                         'statsMoi'     => $statsMoi,
                         'ratio'        => $ratio,
                         'mode'         => $mode,
                     ],
                     $previsuData
                 )
             );
         }
         
    /* ==============================================================
     |  PAGE PRINCIPALE FUSIONNÉE POUR PROJETS (Nombre + Finance)
     |  -> Affiche le tableau avec switch entre Nombre / Finance
     |============================================================== */
    public function statProjetDashboard(Request $request)
    {
        /* ============================
         *       PARAMETRES
         * ============================ */
        $ecran   = Ecran::find($request->input('ecran_id'));
        $typeVue = $request->input('vue', 'nombre'); // nombre | finance
        $mode    = $request->input('mode', 'table'); // table | graph

            $country = session('pays_selectionne');
            $group   = session('projet_selectionne');
            $prefix  = ($country ?? '') . ($group ?? '') . '%';

        /* === DATES (prévisionnelles + effectives) === */
        $dateDebutPrev = $request->input('date_debut_prev');
        $dateFinPrev   = $request->input('date_fin_prev');
        $dateDebutEff  = $request->input('date_debut_effectif');
        $dateFinEff    = $request->input('date_fin_effectif');

        /* ============================
         *      META STATUTS
         * ============================ */
        $meta         = $this->statusMeta();
        $statusOrder  = $meta['order'];
        $statusTitles = $meta['titles'];
        $statusMap    = $this->statusMap();

        $empty = $this->emptyBag($statusOrder);

        /* ============================
         *  BASE = dernier statut / projet
         * ============================ */
        $baseLatest = $this->baseLatest($prefix)
                            ->join('projets as p', 'p.code_projet', '=', 'ps.code_projet');

        /* === FILTRE PAR DATES === */
        if ($dateDebutPrev) {
            $baseLatest->where('p.date_demarrage_prevue', '>=', $dateDebutPrev);
        }
        if ($dateFinPrev) {
            $baseLatest->where('p.date_fin_prevue', '<=', $dateFinPrev);
        }

        if ($dateDebutEff || $dateFinEff) {
            $baseLatest->leftJoin('dates_effectives_projet as dep', 'dep.code_projet', '=', 'p.code_projet');

            if ($dateDebutEff) {
                $baseLatest->where('dep.date_debut_effective', '>=', $dateDebutEff);
            }
            if ($dateFinEff) {
                $baseLatest->where('dep.date_fin_effective', '<=', $dateFinEff);
            }
        }

        /* ============================
         *  Pour la Finance, besoin du montant aussi
         * ============================ */
        $baseLatestFinance = clone $baseLatest;

        /* ============================
         *       NATIONAL
         * ============================ */
        if ($typeVue === 'finance') {
            $national = $this->aggregateFinance($baseLatestFinance, $statusMap, $statusOrder);
        } else {
            $national = $this->aggregateCount($baseLatest, $statusMap, $statusOrder);
        }

        /* ============================
         *       ROLES
         * ============================ */
        $roles = [];
        $codeActeur = auth()->user()->acteur->code_acteur ?? null;
        if ($codeActeur) $roles = $this->detectRoles($codeActeur);

        $idsForRole = function(string $role) use ($codeActeur, $prefix) {
            return match($role) {
                'chef_projet' => Executer::where('code_acteur',$codeActeur)
                                    ->where('is_active',1)->where('code_projet','like',$prefix)->pluck('code_projet'),
                'moe'         => Controler::where('code_acteur',$codeActeur)
                                    ->where('is_active',1)->where('code_projet','like',$prefix)->pluck('code_projet'),
                'mo'          => Posseder::where('code_acteur',$codeActeur)
                                    ->where('isAssistant',0)->where('is_active',1)
                                    ->where('code_projet','like',$prefix)->pluck('code_projet'),
                'bailleur'    => Financer::where('code_acteur',$codeActeur)
                                    ->where('is_active',1)->where('code_projet','like',$prefix)->pluck('code_projet'),
                default       => collect(),
            };
        };

        $mine = $empty;
        $statsMoi = [];

        foreach ($roles as $role) {
            $ids = $idsForRole($role);

            if ($ids->isEmpty()) {
                $statsMoi[$role] = $empty;
                continue;
            }

            if ($typeVue === 'finance') {
                $bag = $this->aggregateFinance(
                    (clone $baseLatestFinance)->whereIn('ps.code_projet', $ids),
                    $statusMap,
                    $statusOrder
                );
            } else {
                $bag = $this->aggregateCount(
                    (clone $baseLatest)->whereIn('ps.code_projet', $ids),
                    $statusMap,
                    $statusOrder
                );
            }

            $statsMoi[$role] = $bag;

            foreach ($bag as $k => $v) {
                $mine[$k] += $v;
            }
        }

        /* ============================
         *       RATIO
         * ============================ */
        $ratio = $empty;
        foreach ($ratio as $k => $v) {
            $den = $national[$k] ?? 0;
            $num = $mine[$k] ?? 0;
            $ratio[$k] = $den > 0 ? round(($num / $den) * 100, 0) : 0;
        }

        /* ============================
         *       PROJECT STATUS COUNTS (pour KPI)
         * ============================ */
        $statusKeys = ['Prévu','En cours','Clôturés','Suspendu','Annulé','Terminé','Redémarré'];
            $latestForCounts = DB::table('projet_statut as ps1')
                ->select('ps1.code_projet', DB::raw('MAX(ps1.date_statut) as max_date'))
                ->where('ps1.code_projet','like',$prefix)
                ->groupBy('ps1.code_projet');

            $statusCounts = DB::table('projet_statut AS ps')
                ->joinSub($latestForCounts, 'last', function ($j) {
                    $j->on('ps.code_projet','=','last.code_projet')
                      ->on('ps.date_statut','=','last.max_date');
                })
                ->join('type_statut AS ts','ps.type_statut','=','ts.id')
                ->join('projets AS p','p.code_projet','=','ps.code_projet')
            ->where('p.code_projet','like',$prefix);

        /* Appliquer les filtres de dates pour les counts aussi */
        if ($dateDebutPrev) {
            $statusCounts->where('p.date_demarrage_prevue', '>=', $dateDebutPrev);
        }
        if ($dateFinPrev) {
            $statusCounts->where('p.date_fin_prevue', '<=', $dateFinPrev);
        }
        if ($dateDebutEff || $dateFinEff) {
            $statusCounts->leftJoin('dates_effectives_projet as dep', 'dep.code_projet', '=', 'p.code_projet');
            if ($dateDebutEff) {
                $statusCounts->where('dep.date_debut_effective', '>=', $dateDebutEff);
            }
            if ($dateFinEff) {
                $statusCounts->where('dep.date_fin_effective', '<=', $dateFinEff);
            }
        }

        $statusCounts = $statusCounts->select('ts.libelle', DB::raw('COUNT(*) AS total'))
            ->groupBy('ts.libelle')
            ->pluck('total','libelle')->toArray();

        $projectStatusCounts = array_fill_keys($statusKeys, 0);
        foreach ($statusCounts as $lib => $n) {
            if ($lib === 'Clôturé') $lib = 'Clôturés';
            if (isset($projectStatusCounts[$lib])) {
                $projectStatusCounts[$lib] = $n;
            }
        }

        /* ============================
         *       PRÉVISUALISATION GRAPHIQUE
         * ============================ */
        $previsuData = $this->buildPrevisualisationGraphiqueDataProjet(
            $dateDebutPrev, $dateFinPrev, $dateDebutEff, $dateFinEff
        );

        /* ============================
         *   DONNÉES GRAPHIQUES GLOBAL (comme dash)
         * ============================ */
        $enabledFamilies = ['PROJET'];
        $statusKeys  = ['Prévu','En cours','Clôturés','Suspendu','Annulé','Terminé','Redémarré'];
        $emptyStatus = fn() => array_fill_keys($statusKeys, 0);

        $byFamily = [
            'totaux'      => [],
            'status'      => [],
            'financement' => [],
            'parAnnee'    => [],
            'budgets'     => [],
        ];

        // PROJET uniquement
        $pref = $prefix;
        $byFamily['totaux']['PROJET'] = DB::table('projets')
            ->whereNotNull('code_projet')
            ->where('code_projet','like',$pref)
            ->count();

        $latestProj = DB::table('projet_statut')
            ->select('code_projet', DB::raw('MAX(date_statut) AS max_date'))
            ->groupBy('code_projet');

        $statusCountsFam = DB::table('projet_statut AS ps')
            ->joinSub($latestProj, 'last', function ($j) {
                $j->on('ps.code_projet','=','last.code_projet')
                  ->on('ps.date_statut','=','last.max_date');
            })
            ->join('type_statut AS ts','ps.type_statut','=','ts.id')
            ->join('projets AS p','p.code_projet','=','ps.code_projet')
            ->where('p.code_projet','like',$pref)
                ->select('ts.libelle', DB::raw('COUNT(*) AS total'))
                ->groupBy('ts.libelle')
                ->pluck('total','libelle')->toArray();

        $bagFam = $emptyStatus();
        foreach ($statusCountsFam as $lib=>$n) {
            if ($lib === 'Clôturé') $lib = 'Clôturés';
            if (isset($bagFam[$lib])) $bagFam[$lib] = $n;
        }
        $byFamily['status']['PROJET'] = $bagFam;

        $byFamily['financement']['PROJET'] = DB::table('type_financement AS tf')
            ->leftJoin(DB::raw("
                (
                    SELECT code_projet,
                           SUBSTRING(code_projet, 7, 1) AS type_financement
                    FROM projets
                    WHERE code_projet IS NOT NULL
                      AND LENGTH(code_projet) >= 7
                      AND code_projet LIKE '{$pref}'
                ) AS p
            "), 'p.type_financement','=','tf.code_type_financement')
            ->select('tf.libelle', DB::raw('COUNT(p.code_projet) AS total_projets'))
            ->groupBy('tf.libelle')->orderBy('tf.libelle')->get();

        $byFamily['parAnnee']['PROJET'] = DB::table('projets')
            ->selectRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(code_projet, '_', 4), '_', -1) AS annee, COUNT(*) AS total")
            ->whereNotNull('code_projet')->where('code_projet','like',$pref)
            ->groupBy('annee')->orderBy('annee')
            ->pluck('total','annee')->toArray();

        $byFamily['budgets']['PROJET'] = DB::table('projets')
            ->selectRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(code_projet, '_', 4), '_', -1) AS annee, SUM(cout_projet)/1000000000 AS total")
            ->whereNotNull('code_projet')->where('code_projet','like',$pref)
            ->groupBy('annee')->orderBy('annee')
            ->pluck('total','annee')->toArray();

        // Acteurs globaux
        $actorsCounts = [
            'Maîtres d’Ouvrage' => DB::table('posseder AS mo')
                ->join('projets AS p','mo.code_projet','=','p.code_projet')
                ->where('p.code_projet','like',$pref)->count(),
            'Maîtres d’Œuvre' => DB::table('executer AS ex')
                ->join('projets AS p','ex.code_projet','=','p.code_projet')
                ->where('p.code_projet','like',$pref)->count(),
            'Bailleurs' => DB::table('financer AS f')
                ->join('projets AS p','f.code_projet','=','p.code_projet')
                ->where('p.code_projet','like',$pref)->count(),
            'Chefs de Projet' => DB::table('controler AS cp')
                ->join('projets AS p','cp.code_projet','=','p.code_projet')
                ->where('p.code_projet','like',$pref)->count(),
            'Bénéficiaires' => DB::table('beneficier AS b')
                ->join('projets AS p','b.code_projet','=','p.code_projet')
                ->where('p.code_projet','like',$pref)->count(),
        ];

        // Financements globaux (agrégation déjà unique ici)
        $financements = $byFamily['financement']['PROJET'];

        // Séries annuelles globales
        $projectsParAnnee = $byFamily['parAnnee']['PROJET'] ?? [];
        ksort($projectsParAnnee);
        $budgetsParAnnee  = $byFamily['budgets']['PROJET'] ?? [];
        ksort($budgetsParAnnee);

        /* ============================
         *        VIEW
         * ============================ */
        return view('TableauBord.projet',
            array_merge(
                [
                    'ecran'                => $ecran,
                    'roles'                => $roles,
                    'statusOrder'          => $statusOrder,
                    'statusTitles'         => $statusTitles,
                    'typeVue'              => $typeVue,
                    'national'             => $national,
                    'statsMoi'             => $statsMoi,
                    'ratio'                => $ratio,
                    'mode'                 => $mode,
                    'projectStatusCounts'  => $projectStatusCounts,
                    'enabledFamilies'      => $enabledFamilies,
                    'byFamily'             => $byFamily,
                    'actorsCounts'         => $actorsCounts,
                    'financements'         => $financements,
                    'projectsParAnnee'     => $projectsParAnnee,
                    'budgetsParAnnee'      => $budgetsParAnnee,
                ],
                $previsuData
            )
        );
    }

    public function statProjet(Request $request)
    {
        /* ============================
         *       PARAMÈTRES DE BASE
         * ============================ */
        $ecran   = Ecran::find($request->input('ecran_id'));
    
        // Valeurs attendues par la vue
        $typeVue = $request->input('vue', 'nombre'); // nombre | finance
        $mode    = $request->input('mode', 'table'); // table | graph
    
        $type    = $request->input('type');   // national | personnel
        $role    = $request->input('role');   // chef_projet | moe | mo | bailleur
        $statutK = $request->input('statut'); // prevu | en_cours | ...
        $segment = $request->input('segment');// total | public | prive
    
        $country = session('pays_selectionne');
        $group   = session('projet_selectionne');
        $prefix  = ($country ?? '') . ($group ?? '') . '%';
    
        /* ============================
         *      META STATUTS (OBLIGATOIRE)
         * ============================ */
        $meta         = $this->statusMeta();
        $statusOrder  = $meta['order'];
        $statusTitles = $meta['titles'];
        $statusMap    = $this->statusMap();
    
        /* ============================
         *       ROLES UTILISATEUR
         * ============================ */
        $roles = [];
        $codeActeur = auth()->user()->acteur->code_acteur ?? null;
        if ($codeActeur) {
            $roles = $this->detectRoles($codeActeur);
        }
    
        /* ============================
         *       KPI – COUNTS STATUTS
         * ============================ */
        $statusKeys = ['Prévu','En cours','Clôturés','Suspendu','Annulé','Terminé','Redémarré'];
    
        $latestForCounts = DB::table('projet_statut as ps1')
            ->select('ps1.code_projet', DB::raw('MAX(ps1.date_statut) as max_date'))
            ->where('ps1.code_projet','like',$prefix)
            ->groupBy('ps1.code_projet');
    
        $statusCounts = DB::table('projet_statut AS ps')
            ->joinSub($latestForCounts, 'last', function ($j) {
                $j->on('ps.code_projet','=','last.code_projet')
                  ->on('ps.date_statut','=','last.max_date');
            })
            ->join('type_statut AS ts','ps.type_statut','=','ts.id')
            ->where('ps.code_projet','like',$prefix)
            ->select('ts.libelle', DB::raw('COUNT(*) AS total'))
            ->groupBy('ts.libelle')
            ->pluck('total','libelle')
            ->toArray();

            $projectStatusCounts = array_fill_keys($statusKeys, 0);
            foreach ($statusCounts as $lib => $n) {
                if ($lib === 'Clôturé') $lib = 'Clôturés';
                if (isset($projectStatusCounts[$lib])) {
                    $projectStatusCounts[$lib] = $n;
                }
            }

        /* ============================
         *       BASE : DERNIER STATUT
         * ============================ */
            $latest = DB::table('projet_statut as ps1')
                ->select('ps1.code_projet', DB::raw('MAX(ps1.date_statut) as max_date'))
                ->where('ps1.code_projet','like',$prefix)
                ->groupBy('ps1.code_projet');

            $base = DB::table('projet_statut as ps')
                ->joinSub($latest,'lp', function($j){
                    $j->on('ps.code_projet','=','lp.code_projet')
                      ->on('ps.date_statut','=','lp.max_date');
                })
                ->join('type_statut as ts','ts.id','=','ps.type_statut')
                ->join('projets as p','p.code_projet','=','ps.code_projet')
                ->where('ps.code_projet','like',$prefix);

        /* ============================
         *       FILTRES STATUT / SEGMENT
         * ============================ */
            if ($statutK && isset($statusMap[$statutK])) {
                $base->whereIn('ts.libelle', $statusMap[$statutK]);
            }

            if ($segment === 'public') {
                $base->whereRaw("SUBSTR(ps.code_projet,7,1)='1'");
            } elseif ($segment === 'prive') {
                $base->whereRaw("SUBSTR(ps.code_projet,7,1)='2'");
            }
            
        /* ============================
         *       FILTRE PERSONNEL
         * ============================ */
            $ids = collect();
                
        if ($type === 'personnel' && $codeActeur) {
                    $ids = match ($role) {
                'chef_projet' => Executer::where('code_acteur',$codeActeur)
                                    ->where('is_active',1)->pluck('code_projet'),
                'moe'         => Controler::where('code_acteur',$codeActeur)
                                    ->where('is_active',1)->pluck('code_projet'),
                'mo'          => Posseder::where('code_acteur',$codeActeur)
                                    ->where('is_active',1)->where('isAssistant',0)->pluck('code_projet'),
                'bailleur'    => Financer::where('code_acteur',$codeActeur)
                                    ->where('is_active',1)->pluck('code_projet'),
                        default       => collect(),
                    };
    
            if ($ids->isNotEmpty()) {
                $base->whereIn('ps.code_projet', $ids);
                }
            }

        /* ============================
         *       PROJETS FINAUX
         * ============================ */
                $codes = $base->distinct()->pluck('ps.code_projet');

        $statutsProjets = Projet::with([
                        'dernierStatut.statut',
                        'sousDomaine',
                        'devise',
                        'localisations.localite.decoupage',
                    ])
                    ->whereIn('code_projet', $codes)
                    ->orderBy('code_projet')
                    ->get();
    
        /* ============================
         *       RETOUR VUE
         * ============================ */
        return view('TableauBord.projet', [
            'ecran'               => $ecran,
            'typeVue'             => $typeVue,
            'mode'                => $mode,
            'roles'               => $roles,
            'statusOrder'         => $statusOrder,
            'statusTitles'        => $statusTitles,
            'statutsProjets'      => $statutsProjets,
            'projectStatusCounts' => $projectStatusCounts,
        ]);
    }
        
     
     
     /* ========================================================
      *     GRAPHIQUES PROJETS — avec filtres de dates
      * ======================================================== */
     private function buildPrevisualisationGraphiqueDataProjet(
         $dateDebutPrev,
         $dateFinPrev,  
         $dateDebutEff,
         $dateFinEff
      ): array
     {
         $groupeProjet = session('projet_selectionne');
         $codePays     = session('pays_selectionne');
         $prefix       = $codePays . $groupeProjet . '%';

         $rows = collect();
         $targetStatus = 'Prévu';

         /* ====================================================
          *     PROJETS INFRASTRUCTURE
          * ==================================================== */
         $latest = DB::table('projet_statut')
             ->select('code_projet', DB::raw('MAX(date_statut) as max_date'))
             ->groupBy('code_projet');

         $p = DB::table('projets as p')
             ->joinSub($latest, 'last', function ($j) {
                 $j->on('p.code_projet','=','last.code_projet');
             })
             ->join('projet_statut as ps', function ($j) {
                 $j->on('ps.code_projet','=','last.code_projet')
                   ->on('ps.date_statut','=','last.max_date');
             })
             ->join('type_statut as ts', 'ps.type_statut', '=', 'ts.id')
             ->where('p.code_projet','like',$prefix);

         /* === FILTRES PREVISIONNELS === */
         if ($dateDebutPrev) $p->where('p.date_demarrage_prevue', '>=', $dateDebutPrev);
         if ($dateFinPrev)   $p->where('p.date_fin_prevue', '<=', $dateFinPrev);

         /* === FILTRES EFFECTIFS === */
         if ($dateDebutEff || $dateFinEff) {
             $p->leftJoin('dates_effectives_projet as dep', 'dep.code_projet', '=', 'p.code_projet');
             if ($dateDebutEff) $p->where('dep.date_debut_effective', '>=', $dateDebutEff);
             if ($dateFinEff)   $p->where('dep.date_fin_effective', '<=', $dateFinEff);
         }

         $p->where('ts.libelle','=',$targetStatus);

         $projets = $p->select([
             'p.code_projet as code',
             'p.libelle_projet as intitule',
             DB::raw("'INFRASTRUCTURE' as famille"),
             'ts.libelle as statut',
             'p.date_demarrage_prevue as date_debut',
             'p.date_fin_prevue as date_fin',
             'p.cout_projet as montant',
         ])->get();

         $rows = $rows->concat($projets);

         /* ========= Agrégations ========= */
         $byFamily = $rows->groupBy('famille')->map->count()->toArray();

         /* === Création par année === */
         $deriveYear = function ($code, $dateDebut) {
             if (!empty($dateDebut)) {
                 try { return (int) date('Y', strtotime($dateDebut)); } catch (\Throwable $e) {}
             }
             if (preg_match('/(19|20)\d{2}/', (string)$code, $m)) {
                 return (int) $m[0];
         }      
             return null;
         };

         $yearCounts = [];
         foreach ($rows as $r) {
             $y = $deriveYear($r->code, $r->date_debut);
             if ($y) $yearCounts[$y] = ($yearCounts[$y] ?? 0) + 1;
         }
         ksort($yearCounts);

         /* === Acteurs === */
         $codesAll  = $rows->pluck('code')->values()->all();

         $countActors = function(array $codes) {
             if (empty($codes)) {
                 return [
                     "Maîtres d'Ouvrage" => 0,
                     "Maîtres d'Œuvre"   => 0,
                     "Bailleurs"         => 0,
                     "Chefs de Projet"   => 0,
                     "Bénéficiaires"     => 0,
                 ];
             }
             return [
                 "Maîtres d'Ouvrage" => DB::table('posseder')->whereIn('code_projet', $codes)->count(),
                 "Maîtres d'Œuvre"   => DB::table('executer')->whereIn('code_projet', $codes)->count(),
                 "Bailleurs"         => DB::table('financer')->whereIn('code_projet', $codes)->count(),
                 "Chefs de Projet"   => DB::table('controler')->whereIn('code_projet', $codes)->count(),
                 "Bénéficiaires"     => DB::table('beneficier')->whereIn('code_projet', $codes)->count(),
             ];
         };

         $actorCounts = $countActors($codesAll);
         $actorCountsByFamily = [
             'INFRASTRUCTURE' => $countActors($codesAll),
         ];

         return [
             'rows'                => $rows,
             'byFamily'            => $byFamily,
             'yearCounts'          => $yearCounts,
             'totalPV'             => $rows->count(),
             'actorCounts'         => $actorCounts,
             'actorCountsByFamily' => $actorCountsByFamily,
         ];
     }
     
         /* ========================================================
          *     GRAPHIQUES — avec filtres de dates
          * ======================================================== */
         private function buildPrevisualisationGraphiqueData(
             $dateDebutPrev,
             $dateFinPrev,  
             $dateDebutEff,
             $dateFinEff
          ): array 
         {
     
             $groupeProjet = session('projet_selectionne');
             $codePays     = session('pays_selectionne');
     
             $enabled = ['INFRASTRUCTURE','ETUDE','APPUI'];
     
             $prefixes = [
                 'INFRASTRUCTURE' => $codePays . $groupeProjet . '%',
                 'ETUDE'          => 'ET_' . $codePays . '_' . $groupeProjet . '%',
                 'APPUI'          => 'APPUI_' . $codePays . '_' . $groupeProjet . '%',
             ];
     
             $rows = collect();
             $targetStatus = 'Prévu';
     
     
             /* ====================================================
              *     INFRASTRUCTURE
              * ==================================================== */
             if (in_array('INFRASTRUCTURE', $enabled, true)) {
     
                 $latest = DB::table('projet_statut')
                     ->select('code_projet', DB::raw('MAX(date_statut) as max_date'))
                     ->groupBy('code_projet');
     
                 $p = DB::table('projets as p')
                     ->joinSub($latest, 'last', function ($j) {
                         $j->on('p.code_projet','=','last.code_projet');
                     })
                     ->join('projet_statut as ps', function ($j) {
                         $j->on('ps.code_projet','=','last.code_projet')
                           ->on('ps.date_statut','=','last.max_date');
                     })
                     ->join('type_statut as ts', 'ps.type_statut', '=', 'ts.id')
                     ->where('p.code_projet','like',$prefixes['INFRASTRUCTURE']);
     
                 /* === FILTRES PREVISIONNELS === */
                 if ($dateDebutPrev) $p->where('p.date_demarrage_prevue', '>=', $dateDebutPrev);
                 if ($dateFinPrev)   $p->where('p.date_fin_prevue', '<=', $dateFinPrev);
     
                 /* === FILTRES EFFECTIFS === */
                 if ($dateDebutEff || $dateFinEff) {
                     $p->leftJoin('dates_effectives_projet as dep', 'dep.code_projet', '=', 'p.code_projet');
                     if ($dateDebutEff) $p->where('dep.date_debut_effective', '>=', $dateDebutEff);
                     if ($dateFinEff)   $p->where('dep.date_fin_effective', '<=', $dateFinEff);
                 }
     
                 $p->where('ts.libelle','=',$targetStatus);
     
                 $infra = $p->select([
                     'p.code_projet as code',
                     'p.libelle_projet as intitule',
                     DB::raw("'INFRASTRUCTURE' as famille"),
                     'ts.libelle as statut',
                     'p.date_demarrage_prevue as date_debut',
                     'p.date_fin_prevue as date_fin',
                     'p.cout_projet as montant',
                 ])->get();
     
                 $rows = $rows->concat($infra);
             }
     
     
             /* ====================================================
              *     ETUDE DE PROJET
              * ==================================================== */
             if (in_array('ETUDE', $enabled, true)) {
     
                 $e = ModelsEtudeProjet::with('dernierStatut.statut')
                     ->where('code_projet_etude','like',$prefixes['ETUDE']);
     
                 /* === PREVISIONNEL === */
                 if ($dateDebutPrev) $e->where('date_debut_previsionnel', '>=', $dateDebutPrev);
                 if ($dateFinPrev)   $e->where('date_fin_previsionnel', '<=', $dateFinPrev);
     
                 /* === EFFECTIF === */
                 if ($dateDebutEff || $dateFinEff) {
                     $e->leftJoin('dates_effectives_projet as dep',
                         'dep.code_projet', '=', 'etude_projets.code_projet_etude');
     
                     if ($dateDebutEff) $e->where('dep.date_debut_effective', '>=', $dateDebutEff);
                     if ($dateFinEff)   $e->where('dep.date_fin_effective', '<=', $dateFinEff);
                 }
     
                 $etudes = $e->get()->filter(function ($e) use ($targetStatus) {
                     return optional(optional($e->dernierStatut)->statut)->libelle === $targetStatus
                            || is_null(optional($e->dernierStatut)->statut);
                 });
     
                 $rows = $rows->concat($etudes->map(function ($e) use ($targetStatus) {
                     return (object)[
                         'code'       => $e->code_projet_etude,
                         'intitule'   => $e->intitule,
                         'famille'    => 'ETUDE',
                         'statut'     => optional(optional($e->dernierStatut)->statut)->libelle ?? $targetStatus,
                         'date_debut' => $e->date_debut_previsionnel,
                         'date_fin'   => $e->date_fin_previsionnel,
                         'montant'    => $e->montant_budget_previsionnel,
                     ];
                 }));
             }
     
     
             /* ====================================================
              *     APPUI PROJET
              * ==================================================== */
             if (in_array('APPUI', $enabled, true)) {
     
                 $a = AppuiProjet::with('dernierStatut.statut')
                     ->where('code_projet_appui','like',$prefixes['APPUI']);
     
                 /* === PREVISIONNEL === */
                 if ($dateDebutPrev) $a->where('date_debut_previsionnel', '>=', $dateDebutPrev);
                 if ($dateFinPrev)   $a->where('date_fin_previsionnel', '<=', $dateFinPrev);
     
                 /* === EFFECTIF === */
                 if ($dateDebutEff || $dateFinEff) {
                     $a->leftJoin('dates_effectives_projet as dep',
                         'dep.code_projet', '=', 'appui_projets.code_projet_appui');
     
                     if ($dateDebutEff) $a->where('dep.date_debut_effective', '>=', $dateDebutEff);
                     if ($dateFinEff)   $a->where('dep.date_fin_effective', '<=', $dateFinEff);
                 }
     
                 $appuis = $a->get()->filter(function ($a) use ($targetStatus) {
                     return optional(optional($a->dernierStatut)->statut)->libelle === $targetStatus
                            || is_null(optional($a->dernierStatut)->statut);
                 });
     
                 $rows = $rows->concat($appuis->map(function ($a) use ($targetStatus) {
                     return (object)[
                         'code'       => $a->code_projet_appui,
                         'intitule'   => $a->intitule,
                         'famille'    => 'APPUI',
                         'statut'     => optional(optional($a->dernierStatut)->statut)->libelle ?? $targetStatus,
                         'date_debut' => $a->date_debut_previsionnel,
                         'date_fin'   => $a->date_fin_previsionnel,
                         'montant'    => $a->montant_budget_previsionnel,
                     ];
                 }));
             }
     
             /* ========= Agrégations ========= */
             $byFamily = $rows->groupBy('famille')->map->count()->toArray();
     
             /* === Création par année === */
             $deriveYear = function ($code, $dateDebut) {
                 if (!empty($dateDebut)) {
                     try { return (int) date('Y', strtotime($dateDebut)); } catch (\Throwable $e) {}
                 }
                 if (preg_match('/(19|20)\d{2}/', (string)$code, $m)) {
                     return (int) $m[0];
                 }
                 return null;
             };
     
             $yearCounts = [];
             foreach ($rows as $r) {
                 $y = $deriveYear($r->code, $r->date_debut);
                 if ($y) $yearCounts[$y] = ($yearCounts[$y] ?? 0) + 1;
             }
             ksort($yearCounts);
     
             /* === Acteurs === */
             $codesAll  = $rows->pluck('code')->values()->all();
             $codesProj = $rows->where('famille','INFRASTRUCTURE')->pluck('code')->values()->all();
             $codesEtud = $rows->where('famille','ETUDE')->pluck('code')->values()->all();
             $codesApp  = $rows->where('famille','APPUI')->pluck('code')->values()->all();
     
             $countActors = function(array $codes) {
                 if (empty($codes)) {
                     return [
                         "Maîtres d’Ouvrage" => 0,
                         "Maîtres d’Œuvre"   => 0,
                         "Bailleurs"         => 0,
                         "Chefs de Projet"   => 0,
                         "Bénéficiaires"     => 0,
                     ];
                 }
                 return [
                     "Maîtres d’Ouvrage" => DB::table('posseder')->whereIn('code_projet', $codes)->count(),
                     "Maîtres d’Œuvre"   => DB::table('executer')->whereIn('code_projet', $codes)->count(),
                     "Bailleurs"         => DB::table('financer')->whereIn('code_projet', $codes)->count(),
                     "Chefs de Projet"   => DB::table('controler')->whereIn('code_projet', $codes)->count(),
                     "Bénéficiaires"     => DB::table('beneficier')->whereIn('code_projet', $codes)->count(),
                 ];
             };
     
             $actorCounts = $countActors($codesAll);
             $actorCountsByFamily = [
                 'INFRASTRUCTURE' => $countActors($codesProj),
                 'ETUDE'          => $countActors($codesEtud),
                 'APPUI'          => $countActors($codesApp),
             ];
     
             return [
                 'rows'                => $rows,
                 'byFamily'            => $byFamily,
                 'yearCounts'          => $yearCounts,
                 'totalPV'             => $rows->count(),
                 'actorCounts'         => $actorCounts,
                 'actorCountsByFamily' => $actorCountsByFamily,
             ];
         }
     
     
        
    /* ============================================================
     |    VUE DÉTAIL — NOMBRE DE PROJETS
     |============================================================ */
     public function statNombreData(Request $request)
     {
         $ecran   = Ecran::find($request->input('ecran_id'));
         $type    = $request->input('type');         // national | personnel
         $role    = $request->input('role');         // chef_projet | moe | mo | bailleur
         $statutK = $request->input('statut');       // prevu | en_cours | cloture | termine | redemarre | suspendu | annule
         $segment = $request->input('segment');      // total | public | prive
 
         $country = session('pays_selectionne');
         $group   = session('projet_selectionne');
         $prefix  = ($country ?? '') . ($group ?? '') . '%';
 
         $statusMap = $this->statusMap();    // ex: 'en_cours' => ["En cours"]
 
         /* ---------------- BASE ---------------- */
         $latest = DB::table('projet_statut as ps1')
             ->select('ps1.code_projet', DB::raw('MAX(ps1.date_statut) as max_date'))
             ->where('ps1.code_projet','like',$prefix)
             ->groupBy('ps1.code_projet');
 
         $base = DB::table('projet_statut as ps')
             ->joinSub($latest, 'lp', function($j){
                 $j->on('ps.code_projet','=','lp.code_projet')
                   ->on('ps.date_statut','=','lp.max_date');
             })
             ->join('type_statut as ts','ts.id','=','ps.type_statut')
             ->where('ps.code_projet','like',$prefix);
 
         /* ---------------- FILTRES ---------------- */
         if ($statutK && isset($statusMap[$statutK])) {
             $base->whereIn('ts.libelle', $statusMap[$statutK]);
         }
 
         if ($segment === 'public') {
             $base->whereRaw("SUBSTR(ps.code_projet,7,1)='1'");
         } elseif ($segment === 'prive') {
             $base->whereRaw("SUBSTR(ps.code_projet,7,1)='2'");
         }
 
         // filtre personnel ?
         if ($type === 'personnel') {
 
             $codeActeur = auth()->user()->acteur->code_acteur ?? null;
             $ids = collect();
 
             if ($codeActeur) {
                 $ids = match ($role) {
                     'chef_projet' => Executer::where('code_acteur',$codeActeur)->where('is_active',1)
                                         ->where('code_projet','like',$prefix)->pluck('code_projet'),
                     'moe'         => Controler::where('code_acteur',$codeActeur)->where('is_active',1)
                                         ->where('code_projet','like',$prefix)->pluck('code_projet'),
                     'mo'          => Posseder::where('code_acteur',$codeActeur)->where('is_active',1)
                                         ->where('isAssistant',0)->where('code_projet','like',$prefix)->pluck('code_projet'),
                     'bailleur'    => Financer::where('code_acteur',$codeActeur)->where('is_active',1)
                                         ->where('code_projet','like',$prefix)->pluck('code_projet'),
                     default       => collect(),
                 };
             }
 
             if ($ids->isEmpty()) {
                 return view('TableauBord.stat_nombre_projet_lien', [
                     'ecran' => $ecran,
                     'statutsProjets' => collect(),
                     'Statuts' => collect()
                 ]);
             }
 
             $base->whereIn('ps.code_projet', $ids->all());
         }
 
         /* ---------------- PROJETS EXACTS ---------------- */
         $codes = $base->distinct()->pluck('ps.code_projet');
 
         $statutsProjets = Projet::query()
             ->with([
                 'dernierStatut.statut',
                 'sousDomaine',
                 'devise',
                 'localisations.localite.decoupage',
             ])
             ->whereIn('code_projet', $codes)
             ->orderBy('code_projet')
             ->get();
 
         $Statuts = ProjetStatut::with('statut')
             ->whereIn('code_projet', $codes)->get();
 
         return view('TableauBord.stat_nombre_projet_lien',
             compact('ecran','statutsProjets','Statuts')
         );
     }
 
 
 
     /* ============================================================
      |    VUE DÉTAIL — FINANCE
      |============================================================ */
     public function statFinanceData(Request $request)
     {
         $ecran   = Ecran::find($request->input('ecran_id'));
         $type    = $request->input('type');
         $role    = $request->input('role');
         $statutK = $request->input('statut');
         $segment = $request->input('segment');
 
         $country = session('pays_selectionne');
         $group   = session('projet_selectionne');
         $prefix  = ($country ?? '') . ($group ?? '') . '%';
 
         $statusMap = $this->statusMap();
 
         /* ---------------- BASE ---------------- */
         $latest = DB::table('projet_statut as ps1')
             ->select('ps1.code_projet', DB::raw('MAX(ps1.date_statut) as max_date'))
             ->where('ps1.code_projet','like',$prefix)
             ->groupBy('ps1.code_projet');
 
         $base = DB::table('projet_statut as ps')
             ->joinSub($latest,'lp', function($j){
                 $j->on('ps.code_projet','=','lp.code_projet')
                   ->on('ps.date_statut','=','lp.max_date');
             })
             ->join('type_statut as ts','ts.id','=','ps.type_statut')
             ->join('projets as p','p.code_projet','=','ps.code_projet')
             ->where('ps.code_projet','like',$prefix);
 
         /* ---------------- FILTRES ---------------- */
         if ($statutK && isset($statusMap[$statutK])) {
             $base->whereIn('ts.libelle', $statusMap[$statutK]);
         }
 
         if ($segment === 'public') {
             $base->whereRaw("SUBSTR(ps.code_projet,7,1)='1'");
         } elseif ($segment === 'prive') {
             $base->whereRaw("SUBSTR(ps.code_projet,7,1)='2'");
         }
 
         // filtre acteur ?
         if ($type === 'personnel') {
 
             $codeActeur = auth()->user()->acteur->code_acteur ?? null;
             $ids = collect();
 
             if ($codeActeur) {
                 $ids = match ($role) {
                     'chef_projet' => Executer::where('code_acteur',$codeActeur)->where('is_active',1)
                                         ->where('code_projet','like',$prefix)->pluck('code_projet'),
                     'moe'         => Controler::where('code_acteur',$codeActeur)->where('is_active',1)
                                         ->where('code_projet','like',$prefix)->pluck('code_projet'),
                     'mo'          => Posseder::where('code_acteur',$codeActeur)->where('is_active',1)
                                         ->where('isAssistant',0)->where('code_projet','like',$prefix)->pluck('code_projet'),
                     'bailleur'    => Financer::where('code_acteur',$codeActeur)->where('is_active',1)
                                         ->where('code_projet','like',$prefix)->pluck('code_projet'),
                     default       => collect(),
                 };
             }
 
             if ($ids->isEmpty()) {
                 return view('TableauBord.stat_fincance_lien', [
                     'ecran' => $ecran,
                     'statutsProjets' => collect()
                 ]);
             }
 
             $base->whereIn('ps.code_projet', $ids->all());
         }
 
         /* ---------------- PROJETS EXACTS ---------------- */
         $codes = $base->distinct()->pluck('ps.code_projet');
 
         $statutsProjets = Projet::query()
             ->with([
                 'dernierStatut.statut',
                 'sousDomaine',
                 'devise',
                 'localisations.localite.decoupage',
             ])
             ->whereIn('code_projet', $codes)
             ->orderBy('code_projet')
             ->get();
 
         return view('TableauBord.stat_fincance_lien',
             compact('ecran','statutsProjets')
         );
     }

    /* ============================================================
     |    ALIAS POUR PROJETS (utilise les mêmes méthodes)
     |============================================================ */
    public function statProjetNombreData(Request $request)
    {
        return $this->statNombreData($request);
    }

    public function statProjetFinanceData(Request $request)
    {
        return $this->statFinanceData($request);
    }
     
}  