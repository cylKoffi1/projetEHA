<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\DecoupageAdminPays;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\Financer;
use App\Models\GroupeProjetPaysUser;
use App\Models\LocalitesPays;
use App\Models\Pays;
use App\Models\Projet;
use App\Models\ProjetStatut;
use App\Models\SousDomaine;
use App\Models\TypeStatut;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SigAdminInfrastructureController extends Controller
{
    /* ------------------------------
     * PAGE
     * ------------------------------ */
    public function pageInfras(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $user  = Auth::user();

        $alpha3 = session('pays_selectionne');
        if (!$alpha3) return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de pays.');

        $pays = Pays::where('alpha3', $alpha3)->first();
        if (!$pays) return redirect()->route('projets.index')->with('error', 'Le pays sélectionné est introuvable.');

        $codeAlpha3 = $pays->alpha3;
        $codeZoom   = Pays::select('minZoom', 'maxZoom')->where('alpha3', $codeAlpha3)->first();

        $groupeSelectionne = session('projet_selectionne');
        if (!$groupeSelectionne) return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de groupe projet.');

        $gp = GroupeProjetPaysUser::where('groupe_projet_id', $groupeSelectionne)->with('groupeProjet')->first();
        if (!$gp) return redirect()->route('projets.index')->with('error', 'Le groupe projet n\'existe pas.');

        $codeGroupeProjet = $gp->groupe_projet_id;


        $domainesAssocie = Domaine::where('groupe_projet_code', $codeGroupeProjet)
            ->select('code','libelle')->orderBy('libelle')->get();

        $niveau = DecoupageAdminPays::where('id_pays', $pays->id)
            ->join('decoupage_administratif', 'decoupage_admin_pays.code_decoupage', '=', 'decoupage_administratif.code_decoupage')
            ->select('decoupage_admin_pays.code_decoupage','decoupage_admin_pays.num_niveau_decoupage','decoupage_administratif.libelle_decoupage')
            ->orderBy('num_niveau_decoupage')->get();

        $Bailleurs    = Acteur::whereHas('bailleurs')->get();
        $TypesStatuts = TypeStatut::all();

        return view('GestionSig.sigInfra', compact(
            'ecran', 'codeZoom', 'niveau', 'codeAlpha3', 'codeGroupeProjet',
            'domainesAssocie', 'Bailleurs', 'TypesStatuts'
        ));
    }

    /* ------------------------------
     * LÉGENDE dynamique
     * - typeFin 1 = métrique "count" (nb d’infras bénéficiaires)
     * - typeFin 2 = "cost"
     * ------------------------------ */
    public function legendByGroupInfras($groupe)
    {
        $typeFin = (int) request('typeFin', 1);
        $fallback = [
            'label'  => $typeFin === 2 ? 'Montant réparti des projets' : 'Nombre d’infrastructures bénéficiaires',
            'seuils' => $typeFin === 2
                ? [
                    ['borneInf'=>0,'borneSup'=>0,'couleur'=>'#f1f5f9'],
                    ['borneInf'=>1_000_000,'borneSup'=>500_000_000,'couleur'=>'#fde68a'],
                    ['borneInf'=>500_000_000,'borneSup'=>2_000_000_000,'couleur'=>'#fbbf24'],
                    ['borneInf'=>2_000_000_000,'borneSup'=>5_000_000_000,'couleur'=>'#f59e0b'],
                    ['borneInf'=>5_000_000_000,'borneSup'=>null,'couleur'=>'#d97706'],
                ]
                : [
                    ['borneInf'=>0,'borneSup'=>0,'couleur'=>'#f1f5f9'],
                    ['borneInf'=>1,'borneSup'=>2,'couleur'=>'#c7d2fe'],
                    ['borneInf'=>3,'borneSup'=>5,'couleur'=>'#93c5fd'],
                    ['borneInf'=>6,'borneSup'=>10,'couleur'=>'#60a5fa'],
                    ['borneInf'=>11,'borneSup'=>null,'couleur'=>'#2563eb'],
                ],
        ];

        $lg = DB::table('legende_carte as l')
            ->where('l.groupe_projet', $typeFin === 2 ? 'COMMUN' : session('projet_selectionne'))
            ->where('l.typeFin', $typeFin)
            ->leftJoin('seuil_legende as s','s.idLegende','=','l.id')
            ->select('l.label','s.borneInf','s.borneSup','s.couleur')
            ->orderBy('s.borneInf')
            ->get();

        if ($lg->isEmpty()) return response()->json($fallback);

        $label = $lg->first()->label ?: $fallback['label'];
        $seuils = $lg->map(fn($r)=>['borneInf'=>$r->borneInf,'borneSup'=>$r->borneSup,'couleur'=>$r->couleur])->values();

        return response()->json(['label'=>$label,'seuils'=>$seuils]);
    }

    /* ------------------------------
     * AGRÉGAT (nb d’infras bénéficiaires) pour la carte
     * ------------------------------ */
    public function aggregateProjectsInfras(Request $request)
    {
        $alpha3 = session('pays_selectionne');
        $group  = session('projet_selectionne');
        if (!$alpha3 || !$group) return response()->json(['error'=>'Contexte pays/groupe manquant'], 400);

        $rows = DB::table('jouir as j')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->join('infrastructures as i', 'i.code', '=', 'j.code_Infrastructure')
            ->where('i.code_pays', $alpha3)
            ->select([
                'p.code_projet','p.cout_projet','p.code_sous_domaine',
                DB::raw("SUBSTRING(p.code_projet,4,3) as gcode"),
                DB::raw("SUBSTRING(p.code_projet,7,1) as type_fin"),
                'i.code as infra_code','i.code_localite'
            ])->get();

        if ($rows->isEmpty()) return response()->json([]);

        $infraByProject=[]; foreach($rows as $r){ $infraByProject[$r->code_projet]=($infraByProject[$r->code_projet]??0)+1; }

        $agg=[]; $seen=[]; $seenDom=[];
        foreach($rows as $r){
            if ($r->gcode !== $group && $group !== 'BTP') continue;

            $alloc = (!empty($r->cout_projet) && $infraByProject[$r->code_projet]>0) ? (float)$r->cout_projet/$infraByProject[$r->code_projet] : 0;
            $isPublic = ((string)$r->type_fin) === '1';
            $dom2 = substr($r->code_sous_domaine ?? '00', 0, 2);

            $niv1 = substr($r->code_localite ?? '', 0, 2);
            $niv2 = substr($r->code_localite ?? '', 0, 4);
            $niv3 = substr($r->code_localite ?? '', 0, 6);

            foreach([1=>$niv1,2=>$niv2,3=>$niv3] as $level=>$code){
                if (!$code) continue;
                if (!isset($agg[$code])) $agg[$code]=['name'=>null,'level'=>$level,'code'=>$code,'count'=>0,'public'=>0,'private'=>0,'cost'=>0.0,'byDomain'=>[]];

                $k=$code.'|'.$r->infra_code;
                if (!isset($seen[$k])){ $agg[$code]['count']++; $isPublic?$agg[$code]['public']++:$agg[$code]['private']++; $seen[$k]=1; }
                $agg[$code]['cost'] += $alloc;

                if (!isset($agg[$code]['byDomain'][$dom2])) $agg[$code]['byDomain'][$dom2]=['count'=>0,'cost'=>0.0,'public'=>0,'private'=>0];
                $kd=$code.'|'.$dom2.'|'.$r->infra_code;
                if (!isset($seenDom[$kd])){ $agg[$code]['byDomain'][$dom2]['count']++; $isPublic?$agg[$code]['byDomain'][$dom2]['public']++:$agg[$code]['byDomain'][$dom2]['private']++; $seenDom[$kd]=1; }
                $agg[$code]['byDomain'][$dom2]['cost'] += $alloc;
            }
        }

        // libellés
        $pays = Pays::where('alpha3',$alpha3)->first();
        $locs = LocalitesPays::where('id_pays',$pays->id)->get(['code_rattachement','libelle']);
        $names=[]; foreach($locs as $l){ $names[$l->code_rattachement]=$l->libelle; }
        foreach($agg as $k=>$v){ $agg[$k]['name']=$names[$v['code']] ?? $v['code']; }

        return response()->json(array_values($agg));
    }

    /* ------------------------------
     * MARKERS agrégés (icône/couleur) par niveau
     * - level: 1,2,3
     * - code: préfixe de localité (ex 01, 0101, 010101)
     * - status: all | done | todo  (basé sur i.IsOver)
     * - logique BTP: niveau 1 = domaines, 2 = sous-domaines, 3 = sous-domaines
     * - logique autre groupe: 1 = groupes, 2 = domaines, 3 = sous-domaines
     * ------------------------------ */
    public function markersInfras(Request $request)
    {
        $alpha3  = session('pays_selectionne');
        $group   = session('projet_selectionne'); // peut être 'BTP'
        if (!$alpha3) return response()->json(['markers'=>[]]);

        $level  = max(1, min(3, (int) $request->input('level', 1)));
        $prefix = (string) $request->input('code', '');
        $status = $request->input('status', 'all'); // all|done|todo

        // jointure infra ↔ jouir ↔ projets (pour récupérer sous-domaine & groupe projet)
        $q = DB::table('infrastructures as i')
            ->join('jouir as j', 'j.code_Infrastructure', '=', 'i.code')
            ->join('projets as p', 'p.code_projet', '=', 'j.code_projet')
            ->where('i.code_pays', $alpha3)
            ->when($prefix !== '', function($qq) use ($level,$prefix){
                $len = $level * 2;
                $qq->where(DB::raw("LEFT(i.code_localite, {$len})"), $prefix);
            });

        if ($status === 'done') $q->where('i.IsOver', 1);
        if ($status === 'todo') $q->where(function($s){ $s->whereNull('i.IsOver')->orWhere('i.IsOver', 0); });

        // champs utiles
        $rows = $q->select([
                'i.latitude','i.longitude','i.code_localite','i.IsOver',
                DB::raw("SUBSTRING(p.code_projet,4,3) as gcode"),
                DB::raw("LEFT(p.code_sous_domaine,2) as domain2"),
                'p.code_sous_domaine as sdomaine'
            ])->whereNotNull('i.latitude')->whereNotNull('i.longitude')->get();

        if ($rows->isEmpty()) return response()->json(['markers'=>[]]);

        // agrégation : on calcule un centroïde SIMPLE (moyenne lat/lng) par clef
        $isBTP = ($group === 'BTP');

        $buckets = []; // key => ['sumLat','sumLng','n','count','label','class','code']
        foreach ($rows as $r) {
            if (!$isBTP) {
                if ($level === 1) { $class='group'; $code=$r->gcode;     $label=$r->gcode; }
                elseif ($level === 2){ $class='domain'; $code=$r->domain2; $label=$r->domain2; }
                else { $class='sub'; $code=$r->sdomaine; $label=$r->sdomaine; }
            } else {
                // BTP = niveau 1 => domaines
                if ($level === 1) { $class='domain'; $code=$r->domain2; $label=$r->domain2; }
                elseif ($level === 2){ $class='sub'; $code=$r->sdomaine; $label=$r->sdomaine; }
                else { $class='sub'; $code=$r->sdomaine; $label=$r->sdomaine; }
            }

            $k = $class.'|'.$code;
            if (!isset($buckets[$k])) $buckets[$k]=['sumLat'=>0,'sumLng'=>0,'n'=>0,'count'=>0,'class'=>$class,'code'=>$code,'label'=>$label];
            $buckets[$k]['sumLat'] += (float)$r->latitude;
            $buckets[$k]['sumLng'] += (float)$r->longitude;
            $buckets[$k]['n']++;
            $buckets[$k]['count']++;
        }

        $markers = [];
        foreach ($buckets as $b) {
            $markers[] = [
                'lat'   => $b['sumLat'] / max(1,$b['n']),
                'lng'   => $b['sumLng'] / max(1,$b['n']),
                'count' => $b['count'],
                'class' => $b['class'],     // group|domain|sub
                'code'  => $b['code'],
                'label' => $b['label'],
            ];
        }

        return response()->json(['markers'=>$markers]);
    }

    /* ------------------------------
     * Détails projets (tiroir latéral)
     * ------------------------------ */
    public function projectDetailsInfras(Request $request)
    {
        $country = session('pays_selectionne');
        $group   = session('projet_selectionne');
        if (!$country || !$group) return response()->json(['error'=>'Contexte manquant'],400);

        $locPrefix     = $request->input('code');
        $financeFilter = $request->input('filter','cumul'); // cumul|public|private
        $domainPrefix  = $request->input('domain');
        $limit         = (int) $request->input('limit', 1000);
        if (!$locPrefix) return response()->json(['error'=>'code requis'],422);

        $codePattern = $country.$group.'%';
        $projects = Projet::where('code_projet','like',$codePattern)
            ->when($domainPrefix, fn($q)=>$q->where('code_sous_domaine','like',$domainPrefix.'%'))
            ->limit($limit)->get();

        $res=[];
        foreach($projects as $p){
            $cp = $p->code_projet;
            $isPublic = substr($cp,6,1)==='1';
            if ($financeFilter==='public' && !$isPublic) continue;
            if ($financeFilter==='private' &&  $isPublic) continue;

            $loc = explode('_',$cp)[1] ?? '0000';
            if (strpos($loc, $locPrefix)!==0) continue;

            $res[]=[
                'code_projet'=>$cp,
                'libelle_projet'=>$p->libelle_projet,
                'cout_projet'=>$p->cout_projet ?? 0,
                'is_public'=>$isPublic,
                'code_sous_domaine'=>$p->code_sous_domaine,
                'code_localisation'=>$loc,
                'date_demarrage_prevue'=>$p->date_demarrage_prevue,
                'date_fin_prevue'=>$p->date_fin_prevue,
                'code_devise'=>$p->code_devise
            ];
        }

        return response()->json(['count'=>count($res),'projects'=>$res]);
    }

    /* ------------------------------
     * Filtres (bailleurs/statuts) — réutilisable
     * ------------------------------ */
    public function filterOptionsAndAggregateInfras(Request $request)
    {
        $country = session('pays_selectionne');
        $group   = session('projet_selectionne');
        if (!$country || !$group) return response()->json(['error'=>'Contexte manquant'], 400);

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

        // renvoie les mêmes agrégats que aggregateProjects() pour homogénéité
        return $this->aggregateProjectsInfras($request);
    }

    /* ------------------------------
     * Carte Afrique (simple) — si besoin ailleurs
     * ------------------------------ */
    public function allProjectsInfras()
    {
        $projects = Projet::with('pays')->get()->map(function ($p) {
            return [
                'code_projet' => $p->code_projet,
                'is_public'   => substr($p->code_projet, 6, 1) === '1',
                'country_name'=> optional($p->pays)->libelle ?? substr($p->code_projet, 0, 3)
            ];
        });
        return response()->json($projects);
    }
}
