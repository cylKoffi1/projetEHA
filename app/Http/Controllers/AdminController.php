<?php

namespace App\Http\Controllers;

use App\Models\DepenseRealisee;
use App\Models\Ecran;
use App\Models\ProjetEha2;
use App\Models\ProjetStatutProjet;
use App\Models\Rubriques;
use App\Models\Projet;
use App\Models\StatutProjet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
class AdminController extends Controller
{

  
    public function index()
    {
        $ecran = Ecran::find(29);
        $ecrans = Ecran::all();
    
        $groupeProjet = session('projet_selectionne');   // ex: EHA
        $CodePays     = session('pays_selectionne');     // ex: CIV
        $prefix       = $CodePays . $groupeProjet . '%'; // ex: CIVEHA%
    
        // ---------- Projets (compteur simple)
        $totalProjects = DB::table('projets')
            ->whereNotNull('code_projet')
            ->where('code_projet', 'like', $prefix)
            ->count();
    
        // ---------- Dernier statut par projet
        // latest = (code_projet, max(date_statut))
        $latest = DB::table('projet_statut')
            ->select('code_projet', DB::raw('MAX(date_statut) AS max_date'))
            ->groupBy('code_projet');
    
        // ps_latest = lignes projet_statut correspondant au dernier enregistrement
        $psLatest = DB::table('projet_statut AS ps')
            ->joinSub($latest, 'last', function ($join) {
                $join->on('ps.code_projet', '=', 'last.code_projet')
                     ->on('ps.date_statut', '=', 'last.max_date');
            })
            ->join('type_statut AS ts', 'ps.type_statut', '=', 'ts.id')
            ->join('projets AS p', 'p.code_projet', '=', 'ps.code_projet')
            ->where('p.code_projet', 'like', $prefix);
    
        // ---------- Répartition des statuts (du dernier statut)
        $projectStatusCounts = $psLatest
            ->select('ts.libelle', DB::raw('COUNT(*) AS total'))
            ->groupBy('ts.libelle')
            ->pluck('total', 'libelle')
            ->toArray();
    
        // Valeurs par défaut si rien
        $projectStatusCounts = array_merge([
            'Prévu'     => 0,
            'En cours'  => 0,
            'Clôturés'  => 0,
            'Suspendu'  => 0,
            'Annulé'    => 0,
            'Terminé'   => 0,
        ], $projectStatusCounts);
    
        // ---------- Acteurs (compteurs)
        // NB: on filtre toujours via projets.code_projet LIKE $prefix
        $actorsCounts = [
            'Maîtres d’Ouvrage' => DB::table('posseder AS mo')
                ->join('projets AS p', 'mo.code_projet', '=', 'p.code_projet')
                ->where('p.code_projet', 'like', $prefix)
                ->count(),
    
            'Maîtres d’Œuvre' => DB::table('executer AS ex')
                ->join('projets AS p', 'ex.code_projet', '=', 'p.code_projet')
                ->where('p.code_projet', 'like', $prefix)
                ->count(),
    
            'Bailleurs' => DB::table('financer AS f')
                ->join('projets AS p', 'f.code_projet', '=', 'p.code_projet')
                ->where('p.code_projet', 'like', $prefix)
                ->count(),
    
            'Chefs de Projet' => DB::table('controler AS cp')
                ->join('projets AS p', 'cp.code_projet', '=', 'p.code_projet')
                ->where('p.code_projet', 'like', $prefix)
                ->count(),
    
            'Bénéficiaires' => DB::table('beneficier AS b')
                ->join('projets AS p', 'b.code_projet', '=', 'p.code_projet')
                ->where('p.code_projet', 'like', $prefix)
                ->count(),
        ];
    
        // ---------- Financements (par type)
        // On extrait le code type financement = SUBSTRING(code_projet, 7, 1)
        $financements = DB::table('type_financement AS tf')
            ->leftJoin(DB::raw("
                (
                    SELECT code_projet,
                           SUBSTRING(code_projet, 7, 1) AS type_financement
                    FROM projets
                    WHERE code_projet IS NOT NULL
                      AND LENGTH(code_projet) >= 7
                      AND code_projet LIKE '{$prefix}'
                ) AS p
            "), 'p.type_financement', '=', 'tf.code_type_financement')
            ->select('tf.code_type_financement', 'tf.libelle', DB::raw('COUNT(p.code_projet) AS total_projets'))
            ->groupBy('tf.code_type_financement', 'tf.libelle')
            ->orderBy('tf.code_type_financement')
            ->get();
    
        // ---------- Projets par année (à partir du code)
        // Exemple de code: CIVEHA2_1402_0402_2025_...
        // L'année est la 4e section séparée par "_"
        $projectsParAnnee = DB::table('projets')
            ->selectRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(code_projet, '_', 4), '_', -1) AS annee, COUNT(*) AS total")
            ->whereNotNull('code_projet')
            ->where('code_projet', 'like', $prefix)
            ->where('code_projet', 'like', '%\_%\_%\_%\_%') // s'assure d'avoir au moins 4 underscores
            ->groupBy('annee')
            ->orderBy('annee')
            ->pluck('total', 'annee')
            ->toArray();
    
        // ---------- Budget par année (somme cout_projet)
        $budgetsParAnnee = DB::table('projets')
            ->selectRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(code_projet, '_', 4), '_', -1) AS annee, SUM(cout_projet)/1000000000 AS total")
            ->whereNotNull('code_projet')
            ->where('code_projet', 'like', $prefix)
            ->where('code_projet', 'like', '%\_%\_%\_%\_%')
            ->groupBy('annee')
            ->orderBy('annee')
            ->pluck('total', 'annee')
            ->toArray();
    
        return view('dash', compact(
            'ecran', 'ecrans',
            'totalProjects',
            'projectStatusCounts',
            'actorsCounts',
            'financements',
            'projectsParAnnee',
            'budgetsParAnnee'
        ));
    }
    
    
    

    private function getDataByType($type, $table, $label)
    {
        return ProjetEha2::selectRaw('YEAR(Date_demarrage_prevue) as year, ' . $table . '.' . $label . ' as type, SUM(cout_projet) as total')
                ->join($table, 'projet_eha2.' . $type, '=', $table . '.code')
                ->groupBy('year', 'type')
                ->orderBy('year')
                ->get();
    }
    private function getProjetData()
    {
        return ProjetEha2::selectRaw('YEAR(Date_demarrage_prevue) as year, COUNT(CodeProjet) as count')
            ->groupBy('year')
            ->whereRaw('YEAR(Date_demarrage_prevue) > 0')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => $item->year,
                    'count' => $item->count
                ];
            })
            ->toArray();
    }

    public function store(Request $request)
    {
        // Code pour enregistrer un nouvel article dans la base de données
    }

    public function show($id)
    {
        // Code pour afficher un article spécifique
    }

    public function edit($id)
    {
        // Code pour afficher un formulaire d'édition d'article
    }

    public function update(Request $request, $id)
    {
        // Code pour mettre à jour un article dans la base de données
    }

    public function destroy($id)
    {
        // Code pour supprimer un article de la base de données
    }

    public function initSidebar(Request $request)
    {
        $rubriques = Rubriques::with('sousMenus.ecrans')->orderBy('ordre')->get();

        return response()->json(['rubriques' => $rubriques]);
    }

    public function test(){
        return view('text');
    }
}
