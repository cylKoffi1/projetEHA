<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ecran;
use App\Models\Pays;

class GestionDemographieController extends Controller
{
    /** Page principale */
    public function habitantIndex(Request $request)
    {
        // si tu appelles via un menu avec ecran_id
        $ecran = Ecran::find($request->input('ecran_id'));
        // Années (N → N-20)
        $annees = range(now()->year, now()->year - 20);
        return view('GestionDemographie.nombreHabitant', compact('ecran', 'annees'));
    }

    /** Schéma du découpage pour le pays en session (alpha3) */
    public function schema(Request $request)
    {
        $alpha3 = session('pays_selectionne'); // ex: CIV
        abort_if(empty($alpha3), 400, 'Aucun pays sélectionné (alpha3).');

        // découpages utilisent pays.id
        $paysId = Pays::where('alpha3', $alpha3)->value('id');
        abort_if(!$paysId, 404, 'Pays introuvable.');

        $rows = DB::table('decoupage_admin_pays as dap')
            ->join('decoupage_administratif as da', 'da.code_decoupage', '=', 'dap.code_decoupage')
            ->where('dap.id_pays', $paysId)
            ->select(
                'dap.num_niveau_decoupage as niveau',
                'dap.code_decoupage',
                'da.libelle_decoupage'
            )
            ->orderBy('dap.num_niveau_decoupage')
            ->get();

        $schema = $rows->groupBy('niveau')->map(function ($grp) {
            return $grp->map(fn($r) => [
                'code_decoupage' => $r->code_decoupage,
                'libelle'        => $r->libelle_decoupage,
            ])->values();
        });

        return response()->json([
            'alpha3'     => $alpha3,
            'schema'     => $schema, // {1:[{code_decoupage,libelle},…],2:[…]}
            'niveau_min' => $rows->min('niveau'),
            'niveau_max' => $rows->max('niveau'),
        ]);
    }

    /**
     * Localités d’un niveau/type. Si parent_code est fourni, on renvoie uniquement les enfants
     * dont code_rattachement commence par ce préfixe et a longueur = len(parent)+2.
     */
    public function localites(Request $request)
    {
        $data = $request->validate([
            'niveau'         => 'required|integer',
            'code_decoupage' => 'required|string|max:10',
            'parent_code'    => 'nullable|string|max:100',
        ]);
    
        $alpha3 = session('pays_selectionne');
        abort_if(empty($alpha3), 400, 'Aucun pays sélectionné (alpha3).');
    
        $niveau  = (int)$data['niveau'];
        $type    = $data['code_decoupage'];         
        $parent  = $data['parent_code'] ?? null;
    
        // Longueur attendue à ce niveau si pas de parent
        $expectedLen = 2 * $niveau;
        $childLen    = $parent ? strlen($parent) + 2 : $expectedLen;
    
        // --------- Requête stricte
        $base = DB::table('localites_pays')
            ->where('id_pays', $alpha3)
            ->where('code_decoupage', $type);
    
        if ($parent) {
            $base->where('code_rattachement', 'LIKE', $parent.'%');
        }
    
        // Longueur stricte
        $base->whereRaw('CHAR_LENGTH(code_rattachement) = ?', [$childLen]);
    
        // Filtrage par niveau (strict)
        $strict = (clone $base)->where('id_niveau', $niveau)
            ->orderBy('libelle')
            ->get(['id','libelle','code_rattachement','code_decoupage']);
    
        if ($strict->count() > 0) {
            return response()->json($strict);
        }
    
        // --------- Fallback (au cas où id_niveau/niveau seraient décalés)
        $fallback = DB::table('localites_pays')
            ->where('id_pays', $alpha3)
            ->where('code_decoupage', $type)
            ->when($parent, function ($q) use ($parent) {
                $q->where('code_rattachement', 'LIKE', $parent.'%');
            })
            ->whereRaw('CHAR_LENGTH(code_rattachement) = ?', [$childLen])
            ->orderBy('libelle')
            ->get(['id','libelle','code_rattachement','code_decoupage']);
    
        return response()->json($fallback);
    }
    

    /** Enregistrement démographie */
    public function storeHabitants(Request $request)
    {
        $request->validate([
            'localite_id'        => 'required|integer|exists:localites_pays,id',
            'annee'              => ['required','integer','min:1900','max:'.(now()->year+1)],
            'population_totale'  => 'required|integer|min:0',
            'population_homme'   => 'nullable|integer|min:0',
            'population_femme'   => 'nullable|integer|min:0',
        ]);

        if ($request->filled(['population_homme','population_femme'])) {
            if ((int)$request->population_homme + (int)$request->population_femme > (int)$request->population_totale) {
                return response()->json([
                    'success' => false,
                    'message' => "La somme Homme+Femme dépasse la population totale."
                ], 422);
            }
        }

        DB::table('demographie_localite')->updateOrInsert(
            ['localite_id' => $request->localite_id, 'annee' => $request->annee],
            [
                'population_totale' => $request->population_totale,
                'population_homme'  => $request->population_homme,
                'population_femme'  => $request->population_femme,
                'created_by'        => optional($request->user())->id,
                'updated_at'        => now(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Données enregistrées.']);
    }

    // app/Http/Controllers/GestionDemographieController.php

    public function stats(Request $request)
    {
        $alpha3 = session('pays_selectionne');
        abort_if(empty($alpha3), 400, 'Aucun pays sélectionné (alpha3).');
    
        $rows = DB::table('demographie_localite as dl')
            ->join('localites_pays as lp', 'lp.id', '=', 'dl.localite_id')
            ->join('decoupage_administratif as da', 'da.code_decoupage', '=', 'lp.code_decoupage')
            ->where('lp.id_pays', $alpha3)
            ->groupBy('lp.id_niveau', 'lp.code_decoupage', 'da.libelle_decoupage')
            ->select([
                'lp.id_niveau as niveau',
                'lp.code_decoupage',
                'da.libelle_decoupage as libelle',
                DB::raw('COUNT(*) as nb_enregistrements'),
            ])
            ->orderBy('lp.id_niveau', 'desc')
            ->get();
    
        // Cumul “de haut en bas” (ordre desc comme ton tableau)
        $rows = $rows->sortByDesc('niveau')->values();
        $cumul = 0;
        $out = [];
        foreach ($rows as $r) {
            $cumul += (int)$r->nb_enregistrements;
            $out[] = [
                'niveau'             => (int)$r->niveau,
                'code_decoupage'     => $r->code_decoupage,
                'libelle'            => $r->libelle,
                'nb_enregistrements' => (int)$r->nb_enregistrements,
                'cumul'              => $cumul,
            ];
        }
    
        // ➕ Agrégat par niveau (total unique par niveau)
        $byLevel = collect($out)->groupBy('niveau')->map(function ($grp) {
            return [
                'niveau' => (int)$grp->first()['niveau'],
                'total'  => array_sum(array_column($grp->all(), 'nb_enregistrements')),
            ];
        })->sortKeys(); // niveaux 1,2,3,...
    
        // ✅ Cumul des 3 premiers niveaux (1 → 3)
        $top3Levels = $byLevel->filter(fn($v,$k) => $k >= 1 && $k <= 3)->values()->all();
        $cumulTop3  = array_sum(array_column($top3Levels, 'total'));
    
        return response()->json([
            'total_global'         => array_sum(array_column($out, 'nb_enregistrements')),
            'rows'                 => $out,         // pour ton tableau par type
            'by_level'             => $byLevel->values()->all(), // total par niveau
            'top3_levels'          => $top3Levels,  // [{niveau, total}, ...] pour niveaux 1..3
            'cumul_niveaux_1_3'    => $cumulTop3,   // 👈 ce que tu veux afficher
        ]);
    }
    

/**
 * Liste détaillée des enregistrements (optionnel : pour un 2e tableau ou un onglet)
 * Supports filtres simples: ?annee=2023&niveau=3
 */
public function entries(Request $request)
{
    $alpha3 = session('pays_selectionne');
    abort_if(empty($alpha3), 400, 'Aucun pays sélectionné (alpha3).');

    $q = DB::table('demographie_localite as dl')
        ->join('localites_pays as lp', 'lp.id', '=', 'dl.localite_id')
        ->leftJoin('decoupage_administratif as da', 'da.code_decoupage', '=', 'lp.code_decoupage')
        ->where('lp.id_pays', $alpha3);

    if ($request->filled('annee')) {
        $q->where('dl.annee', (int)$request->annee);
    }
    if ($request->filled('niveau')) {
        $q->where('lp.id_niveau', (int)$request->niveau);
    }

    $rows = $q->orderByDesc('dl.updated_at')
        ->limit(500) // évite de renvoyer trop gros
        ->get([
            'dl.id',
            'dl.annee',
            'dl.population_totale',
            'dl.population_homme',
            'dl.population_femme',
            'dl.updated_at',
            'lp.id as localite_id',
            'lp.libelle as localite',
            'lp.id_niveau as niveau',
            'lp.code_rattachement',
            'lp.code_decoupage',
            'da.libelle_decoupage as type_niveau',
        ]);

    return response()->json($rows);
}

}
