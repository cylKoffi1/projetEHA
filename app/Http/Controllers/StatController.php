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

class StatController extends Controller
{
    /**
     * Détecter les rôles de l’acteur connecté (peut en avoir plusieurs)
     */
    private function detectRoles(string $codeActeur): array
    {
        $roles = [];

        if (Executer::where('code_acteur', $codeActeur)->where('is_active', 1)->exists()) {
            $roles[] = 'chef_projet';
        }
        if (Controler::where('code_acteur', $codeActeur)->where('is_active', 1)->exists()) {
            $roles[] = 'moe';
        }
        if (Posseder::where('code_acteur', $codeActeur)->where('is_active', 1)->where('isAssistant', 0)->exists()) {
            $roles[] = 'mo';
        }
        if (Financer::where('code_acteur', $codeActeur)->where('is_active', 1)->exists()) {
            $roles[] = 'bailleur';
        }

        Log::info("👤 Acteur {$codeActeur} → rôles détectés: " . implode(',', $roles));
        return $roles;
    }

    /**
     * Dernier statut par projet (filtré par pays+projet)
     */
    private function latestStatuts()
    {
        $prefix = session('pays_selectionne') . session('projet_selectionne') . '%';
        Log::info("📊 Filtrage des projets par prefix: {$prefix}");

        $latest = DB::table('projet_statut as ps1')
            ->select('ps1.code_projet', DB::raw('MAX(ps1.date_statut) as max_date'))
            ->where('ps1.code_projet', 'like', $prefix)
            ->groupBy('ps1.code_projet');

        return DB::table('projet_statut as ps')
            ->joinSub($latest, 'lp', function ($j) {
                $j->on('ps.code_projet', '=', 'lp.code_projet')
                  ->on('ps.date_statut', '=', 'lp.max_date');
            })
            ->join('type_statut as ts', 'ts.id', '=', 'ps.type_statut')
            ->select('ps.code_projet', 'ts.libelle as statut');
    }

    /**
     * Page globale – Nombre de projets
     */
    public function statNombreProjet(Request $request)
    {
        $ecran   = Ecran::find($request->input('ecran_id'));
        $country = session('pays_selectionne');
        $group   = session('projet_selectionne');
        $prefix  = ($country ?? '').($group ?? '').'%';
    
        Log::info('📋 Nb Projets (TOUS STATUTS) | ecran_id='.($ecran->id ?? 'NULL').' | prefix='.$prefix);
    
        // ---- Définition des statuts (synonymes inclus) + ordre d’affichage ----
        $statusOrder  = ['prevu','en_cours','cloture','termine','redemarre','suspendu','annule'];
        $statusTitles = [
            'prevu'      => 'Prévu',
            'en_cours'   => 'En cours',
            'cloture'    => 'Clôturé',
            'termine'    => 'Terminé',
            'redemarre'  => 'Redémarré',
            'suspendu'   => 'Suspendu',
            'annule'     => 'Annulé',
        ];
        $statusMap = [
            'prevu'     => ["'Prévu'"],
            'en_cours'  => ["'En cours'"],
            'cloture'   => ["'Clôturé'","'Clôturés'"],
            'termine'   => ["'Terminé'"],
            'redemarre' => ["'Redémarré'"],
            'suspendu'  => ["'Suspendu'"],
            'annule'    => ["'Annulé'"],
        ];
    
        // ---- Gabarit vide pour toutes les colonnes (par statut) ----
        $empty = function() use ($statusOrder) {
            $bag = [];
            foreach ($statusOrder as $k) {
                $bag["total_{$k}"]  = 0;
                $bag["public_{$k}"] = 0;
                $bag["prive_{$k}"]  = 0;
            }
            return $bag;
        };
    
        // ---- Base: dernier statut par projet (filtré par prefix) ----
        $baseLatest = function() use ($prefix) {
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
        };
    
        $isPublic = "SUBSTR(ps.code_projet,7,1)='1'";
        $isPrive  = "SUBSTR(ps.code_projet,7,1)='2'";
    
        // ---- Agrégateur dynamique (génère le SELECT avec tous les statuts) ----
        $aggregate = function($builder) use ($statusMap,$statusOrder,$isPublic,$isPrive,$empty) {
            $parts = [];
            foreach ($statusOrder as $k) {
                $in = '('.implode(',', $statusMap[$k]).')';
                $parts[] = "SUM(CASE WHEN ts.libelle IN $in THEN 1 ELSE 0 END) as total_{$k}";
                $parts[] = "SUM(CASE WHEN ts.libelle IN $in AND $isPublic THEN 1 ELSE 0 END) as public_{$k}";
                $parts[] = "SUM(CASE WHEN ts.libelle IN $in AND $isPrive  THEN 1 ELSE 0 END) as prive_{$k}";
            }
            $row = $builder->selectRaw(implode(",\n", $parts))->first();
            if (!$row) return $empty();
    
            $bag = $empty();
            foreach ($statusOrder as $k) {
                $bag["total_{$k}"]  = (int)($row->{"total_{$k}"}  ?? 0);
                $bag["public_{$k}"] = (int)($row->{"public_{$k}"} ?? 0);
                $bag["prive_{$k}"]  = (int)($row->{"prive_{$k}"}  ?? 0);
            }
            return $bag;
        };
    
        // ---------- National (tous projets, sans filtre acteur) ----------
        $stats = [];
        $stats['National'] = $aggregate($baseLatest());
        Log::info('📊 National (all statuses)', $stats['National']);
    
        // ---------- Détection des rôles de l’acteur ----------
        $roles = [];
        $codeActeur = auth()->user()?->acteur?->code_acteur;
    
        if ($codeActeur) {
            if (Executer::where('code_acteur',$codeActeur)->where('is_active',1)->exists())                         $roles[]='chef_projet';
            if (Controler::where('code_acteur',$codeActeur)->where('is_active',1)->exists())                        $roles[]='moe';
            if (Posseder::where('code_acteur',$codeActeur)->where('is_active',1)->where('isAssistant',0)->exists()) $roles[]='mo';
            if (Financer::where('code_acteur',$codeActeur)->where('is_active',1)->exists())                         $roles[]='bailleur';
        }
        $roles = array_values(array_unique($roles));
        Log::info("👤 Acteur {$codeActeur} → rôles", $roles);
    
        // Helper: projets de l’acteur par rôle
        $idsForRole = function(string $role) use ($codeActeur,$prefix) {
            return match($role) {
                'chef_projet' => Executer::where('code_acteur',$codeActeur)->where('is_active',1)
                                    ->where('code_projet','like',$prefix)->pluck('code_projet'),
                'moe'         => Controler::where('code_acteur',$codeActeur)->where('is_active',1)
                                    ->where('code_projet','like',$prefix)->pluck('code_projet'),
                'mo'          => Posseder::where('code_acteur',$codeActeur)->where('is_active',1)->where('isAssistant',0)
                                    ->where('code_projet','like',$prefix)->pluck('code_projet'),
                'bailleur'    => Financer::where('code_acteur',$codeActeur)->where('is_active',1)
                                    ->where('code_projet','like',$prefix)->pluck('code_projet'),
                default       => collect(),
            };
        };
    
        // Somme de deux sacs de stats
        $sumBags = function(array $a, array $b) {
            foreach ($b as $k => $v) $a[$k] = ($a[$k] ?? 0) + $v;
            return $a;
        };
    
        // ---------- Mes rôles ----------
        $stats['Moi'] = [];
        $mine = $empty();
    
        foreach ($roles as $role) {
            $ids = $idsForRole($role);
            Log::info("📌 {$role} → ".$ids->count()." projets");
            if ($ids->isEmpty()) {
                $stats['Moi'][$role] = $empty();
                continue;
            }
            $bag = $aggregate(
                $baseLatest()->whereIn('ps.code_projet',$ids->all())
            );
            $stats['Moi'][$role] = $bag;
            $mine = $sumBags($mine, $bag);
            Log::info("📊 {$role}", $bag);
        }
    
        // ---------- Ratio = (Somme de mes rôles) / National, par colonne ----------
        $stats['Ratio'] = $empty();
        foreach (array_keys($stats['Ratio']) as $col) {
            $den = $stats['National'][$col] ?? 0;
            $num = $mine[$col] ?? 0;
            $stats['Ratio'][$col] = $den ? round(($num / $den) * 100, 0) : 0;
        }
    
        return view('TableauBord.stat_nombre_projet_vue',
            compact('ecran','stats','roles','statusOrder','statusTitles')
        );
    }
    
    /**
     * Page globale – Finances
     */
    // ===================== PAGE GLOBALE — FINANCE (sommes cout_projet) =====================
    public function statFinance(Request $request)
    {
        $ecran   = Ecran::find($request->input('ecran_id'));
        $country = session('pays_selectionne');
        $group   = session('projet_selectionne');
        $prefix  = ($country ?? '').($group ?? '').'%';

        Log::info('💰 Finance (TOUS STATUTS, sommes cout_projet) | ecran_id=' . ($ecran->id ?? 'NULL') . ' | prefix=' . $prefix);

        // mêmes statuts/ordre que la page "Nombre"
        $statusOrder  = ['prevu','en_cours','cloture','termine','redemarre','suspendu','annule'];
        $statusTitles = [
            'prevu'      => 'Prévu',
            'en_cours'   => 'En cours',
            'cloture'    => 'Clôturé',
            'termine'    => 'Terminé',
            'redemarre'  => 'Redémarré',
            'suspendu'   => 'Suspendu',
            'annule'     => 'Annulé',
        ];
        $statusMap = [
            'prevu'     => ["'Prévu'"],
            'en_cours'  => ["'En cours'"],
            'cloture'   => ["'Clôturé'","'Clôturés'"],
            'termine'   => ["'Terminé'"],
            'redemarre' => ["'Redémarré'"],
            'suspendu'  => ["'Suspendu'"],
            'annule'    => ["'Annulé'"],
        ];

        // gabarit vide (montants)
        $empty = function() use ($statusOrder) {
            $bag = [];
            foreach ($statusOrder as $k) {
                $bag["total_{$k}"]  = 0.0;
                $bag["public_{$k}"] = 0.0;
                $bag["prive_{$k}"]  = 0.0;
            }
            return $bag;
        };

        // dernière ligne de statut + jointure projet (pour cout_projet)
        $baseLatest = function() use ($prefix) {
            $latest = DB::table('projet_statut as ps1')
                ->select('ps1.code_projet', DB::raw('MAX(ps1.date_statut) as max_date'))
                ->groupBy('ps1.code_projet');

            return DB::table('projet_statut as ps')
                ->joinSub($latest, 'lp', function ($j) {
                    $j->on('ps.code_projet','=','lp.code_projet')
                    ->on('ps.date_statut','=','lp.max_date');
                })
                ->join('type_statut as ts','ts.id','=','ps.type_statut')
                ->join('projets as p','p.code_projet','=','ps.code_projet')
                ->where('ps.code_projet','like',$prefix);
        };

        $isPublic = "SUBSTR(ps.code_projet,7,1)='1'";
        $isPrive  = "SUBSTR(ps.code_projet,7,1)='2'";

        // agrégateur : SOMME(cout_projet) par statut / public / privé
        $aggregateAmounts = function($builder) use ($statusMap,$statusOrder,$isPublic,$isPrive,$empty) {
            $parts = [];
            foreach ($statusOrder as $k) {
                $in = '('.implode(',', $statusMap[$k]).')';
                $parts[] = "SUM(CASE WHEN ts.libelle IN $in THEN COALESCE(p.cout_projet,0) ELSE 0 END) as total_{$k}";
                $parts[] = "SUM(CASE WHEN ts.libelle IN $in AND $isPublic THEN COALESCE(p.cout_projet,0) ELSE 0 END) as public_{$k}";
                $parts[] = "SUM(CASE WHEN ts.libelle IN $in AND $isPrive  THEN COALESCE(p.cout_projet,0) ELSE 0 END) as prive_{$k}";
            }
            $row = $builder->selectRaw(implode(",\n", $parts))->first();
            if (!$row) return $empty();

            $bag = $empty();
            foreach ($statusOrder as $k) {
                $bag["total_{$k}"]  = (float)($row->{"total_{$k}"}  ?? 0);
                $bag["public_{$k}"] = (float)($row->{"public_{$k}"} ?? 0);
                $bag["prive_{$k}"]  = (float)($row->{"prive_{$k}"}  ?? 0);
            }
            return $bag;
        };

        // ---------- National (sans filtre acteur) ----------
        $stats = [];
        $stats['National'] = $aggregateAmounts($baseLatest());
        Log::info('💰 National (montants)', $stats['National']);

        // ---------- Rôles de l’acteur ----------
        $roles = [];
        $codeActeur = auth()->user()?->acteur?->code_acteur;
        if ($codeActeur) {
            if (Executer::where('code_acteur',$codeActeur)->where('is_active',1)->exists())                         $roles[]='chef_projet';
            if (Controler::where('code_acteur',$codeActeur)->where('is_active',1)->exists())                        $roles[]='moe';
            if (Posseder::where('code_acteur',$codeActeur)->where('is_active',1)->where('isAssistant',0)->exists()) $roles[]='mo';
            if (Financer::where('code_acteur',$codeActeur)->where('is_active',1)->exists())                         $roles[]='bailleur';
        }
        $roles = array_values(array_unique($roles));
        Log::info("👤 Acteur {$codeActeur} → rôles", $roles);

        // IDs projets par rôle
        $idsForRole = function(string $role) use ($codeActeur,$prefix) {
            return match($role) {
                'chef_projet' => Executer::where('code_acteur',$codeActeur)->where('is_active',1)
                                    ->where('code_projet','like',$prefix)->pluck('code_projet'),
                'moe'         => Controler::where('code_acteur',$codeActeur)->where('is_active',1)
                                    ->where('code_projet','like',$prefix)->pluck('code_projet'),
                'mo'          => Posseder::where('code_acteur',$codeActeur)->where('is_active',1)->where('isAssistant',0)
                                    ->where('code_projet','like',$prefix)->pluck('code_projet'),
                'bailleur'    => Financer::where('code_acteur',$codeActeur)->where('is_active',1)
                                    ->where('code_projet','like',$prefix)->pluck('code_projet'),
                default       => collect(),
            };
        };

        // somme de sacs
        $sumBags = function(array $a, array $b) {
            foreach ($b as $k => $v) $a[$k] = round(($a[$k] ?? 0) + ($v ?? 0), 2);
            return $a;
        };

        // ---------- Mes rôles (en montants) ----------
        $stats['Moi'] = [];
        $mine = $empty();

        foreach ($roles as $role) {
            $ids = $idsForRole($role);
            Log::info("📌 {$role} → ".$ids->count()." projets (finance)");
            if ($ids->isEmpty()) { $stats['Moi'][$role] = $empty(); continue; }

            $bag = $aggregateAmounts(
                $baseLatest()->whereIn('ps.code_projet',$ids->all())
            );
            $stats['Moi'][$role] = $bag;
            $mine = $sumBags($mine, $bag);
            Log::info("💰 {$role}", $bag);
        }

        // ---------- Ratio (montants) ----------
        $stats['Ratio'] = $empty();
        foreach (array_keys($stats['Ratio']) as $col) {
            $den = $stats['National'][$col] ?? 0.0;
            $num = $mine[$col] ?? 0.0;
            $stats['Ratio'][$col] = $den > 0 ? round(($num / $den) * 100, 0) : 0;
        }

        // on réutilise la même vue (ou une vue finance dédiée si tu en as une autre)
        // n’oublie pas de passer $statusOrder et $statusTitles si ta vue finance les utilise.
        return view('TableauBord.stat_fincance',
            compact('ecran','stats','roles','statusOrder','statusTitles')
        );
    }


    /**
     * Vue détail – Nombre de projets
     */
    public function statNombreData(Request $request)
    {
        $ecran   = Ecran::find($request->input('ecran_id'));
        $type    = $request->input('type');          // national | personnel
        $role    = $request->input('role');          // chef_projet | moe | mo | bailleur (si personnel)
        $statutK = $request->input('statut');        // prevu | en_cours | cloture | termine | redemarre | suspendu | annule
        $segment = $request->input('segment');       // total | public | prive
    
        $country = session('pays_selectionne');
        $group   = session('projet_selectionne');
        $prefix  = ($country ?? '').($group ?? '').'%';
    
        // mapping identique à la page principale
        $statusMap = [
            'prevu'     => ["Prévu"],
            'en_cours'  => ["En cours"],
            'cloture'   => ["Clôturé","Clôturés"],
            'termine'   => ["Terminé"],
            'redemarre' => ["Redémarré"],
            'suspendu'  => ["Suspendu"],
            'annule'    => ["Annulé"],
        ];
    
        // 1) Construire la base "dernier statut par projet" sur le périmètre (prefix)
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
            ->where('ps.code_projet','like',$prefix);
    
        // 2) Filtre sur le STATUT cliqué (si transmis)
        if ($statutK && isset($statusMap[$statutK])) {
            $base->whereIn('ts.libelle', $statusMap[$statutK]);
        }
    
        // 3) Filtre sur le SEGMENT cliqué (public/privé)
        if ($segment === 'public') {
            $base->whereRaw("SUBSTR(ps.code_projet,7,1)='1'");
        } elseif ($segment === 'prive') {
            $base->whereRaw("SUBSTR(ps.code_projet,7,1)='2'");
        }
        // (segment === total => pas de filtre supplémentaire)
    
        // 4) Périmètre ACTEUR si type=personnel
        if ($type === 'personnel') {
            $codeActeur = auth()->user()->acteur->code_acteur ?? null;
    
            $ids = collect();
            if ($codeActeur) {
                $ids = match ($role) {
                    'chef_projet' => Executer::where('code_acteur',$codeActeur)->where('is_active',1)->where('code_projet','like',$prefix)->pluck('code_projet'),
                    'moe'         => Controler::where('code_acteur',$codeActeur)->where('is_active',1)->where('code_projet','like',$prefix)->pluck('code_projet'),
                    'mo'          => Posseder::where('code_acteur',$codeActeur)->where('is_active',1)->where('isAssistant',0)->where('code_projet','like',$prefix)->pluck('code_projet'),
                    'bailleur'    => Financer::where('code_acteur',$codeActeur)->where('is_active',1)->where('code_projet','like',$prefix)->pluck('code_projet'),
                    default       => collect(),
                };
            }
    
            // si aucun projet pour le rôle → renvoyer vide
            if ($ids->isEmpty()) {
                $statutsProjets = collect();
                return view('TableauBord.stat_nombre_projet_lien', compact('ecran','statutsProjets'))
                       ->with('Statuts', collect());
            }
    
            $base->whereIn('ps.code_projet', $ids->all());
        }
    
        // 5) Récupérer la liste EXACTE des projets correspondant au clic
        $codes = $base->distinct()->pluck('ps.code_projet');
    
        // 6) Charger les projets et leurs infos pour l’affichage
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
    
        Log::info("🔎 Détail -> type=$type role=$role statut=$statutK segment=$segment | total=".$statutsProjets->count());
    
        // (la vue n’a pas besoin de $Statuts si tu utilises $projet->dernierStatut)
        $Statuts = ProjetStatut::with('statut')->whereIn('code_projet',$codes)->get();
    
        return view('TableauBord.stat_nombre_projet_lien', compact('ecran','statutsProjets','Statuts'));
    }
    

    /**
     * Vue détail – Finances
     */
    // ===================== PAGE DÉTAIL — FINANCE (sommes) =====================

    public function statFinanceData(Request $request)
    {
        $ecran   = Ecran::find($request->input('ecran_id'));
        $type    = $request->input('type');          // national | personnel
        $role    = $request->input('role');          // chef_projet | moe | mo | bailleur (si personnel)
        $statutK = $request->input('statut');        // prevu | en_cours | cloture | termine | redemarre | suspendu | annule
        $segment = $request->input('segment');       // total | public | prive

        $country = session('pays_selectionne');
        $group   = session('projet_selectionne');
        $prefix  = ($country ?? '').($group ?? '').'%';

        $statusMap = [
            'prevu'     => ["Prévu"],
            'en_cours'  => ["En cours"],
            'cloture'   => ["Clôturé","Clôturés"],
            'termine'   => ["Terminé"],
            'redemarre' => ["Redémarré"],
            'suspendu'  => ["Suspendu"],
            'annule'    => ["Annulé"],
        ];

        // base: dernier statut par projet + jointure projet
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

        if ($statutK && isset($statusMap[$statutK])) {
            $base->whereIn('ts.libelle', $statusMap[$statutK]);
        }

        if ($segment === 'public') {
            $base->whereRaw("SUBSTR(ps.code_projet,7,1)='1'");
        } elseif ($segment === 'prive') {
            $base->whereRaw("SUBSTR(ps.code_projet,7,1)='2'");
        }

        if ($type === 'personnel') {
            $codeActeur = auth()->user()->acteur->code_acteur ?? null;

            $ids = collect();
            if ($codeActeur) {
                $ids = match ($role) {
                    'chef_projet' => Executer::where('code_acteur',$codeActeur)->where('is_active',1)->where('code_projet','like',$prefix)->pluck('code_projet'),
                    'moe'         => Controler::where('code_acteur',$codeActeur)->where('is_active',1)->where('code_projet','like',$prefix)->pluck('code_projet'),
                    'mo'          => Posseder::where('code_acteur',$codeActeur)->where('is_active',1)->where('isAssistant',0)->where('code_projet','like',$prefix)->pluck('code_projet'),
                    'bailleur'    => Financer::where('code_acteur',$codeActeur)->where('is_active',1)->where('code_projet','like',$prefix)->pluck('code_projet'),
                    default       => collect(),
                };
            }

            if ($ids->isEmpty()) {
                $statutsProjets = collect();
                return view('TableauBord.stat_fincance_lien', compact('ecran','statutsProjets'));
            }

            $base->whereIn('ps.code_projet', $ids->all());
        }

        // codes projets correspondant au clic
        $codes = $base->distinct()->pluck('ps.code_projet');

        // charge les projets pour la liste (la vue détail finance peut être la même que détail nombre)
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

        Log::info("🔎 Détail FINANCE -> type=$type role=$role statut=$statutK segment=$segment | total projets=".$statutsProjets->count());

        return view('TableauBord.stat_fincance_lien', compact('ecran','statutsProjets'));
    }

}
