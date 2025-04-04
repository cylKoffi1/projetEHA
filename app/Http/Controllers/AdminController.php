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
        $groupeProjet = session('projet_selectionne'); // exemple "TRP"

        // Projets
        $totalProjects = DB::table('projets')
            ->whereNotNull('code_projet')
            ->whereRaw("SUBSTRING(code_projet, 4, 3) = ?", [$groupeProjet])
            ->count();

        $projectStatusCounts = DB::table('projet_statut')
            ->join('type_statut', 'projet_statut.type_statut', '=', 'type_statut.id')
            ->join('projets', 'projets.code_projet', '=', 'projet_statut.code_projet')
            ->whereNotNull('projets.code_projet')
            ->whereRaw("SUBSTRING(projets.code_projet, 4, 3) = ?", [$groupeProjet])
            ->select('type_statut.libelle', DB::raw('count(*) as total'))
            ->groupBy('type_statut.libelle')
            ->pluck('total', 'libelle')
            ->toArray();
        
    
        if (empty($projectStatusCounts)) {
            $projectStatusCounts = [
                'Prévu' => 0,
                'En cours' => 0,
                'Clôturés' => 0,
                'Suspendu' => 0,
                'Annulé' => 0,
                'Terminé' => 0,
            ];
        }
    
        // Acteurs
        $actorsCounts = [
            'Maîtres d’Ouvrage' => DB::table('posseder')->count(),
            'Maîtres d’Œuvre' => DB::table('controler')->where('responsabilite', 'MOE')->count(),
            'Bailleurs' => DB::table('financer')->count(),
            'Chefs de Projet' => DB::table('controler')->where('responsabilite', 'CP')->count(),
            'Bénéficiaires' => DB::table('beneficier')->count(),
        ];
    
        if (array_sum($actorsCounts) === 0) {
            $actorsCounts = [
                'Maîtres d’Ouvrage' => 8,
                'Maîtres d’Œuvre' => 5,
                'Bailleurs' => 3,
                'Chefs de Projet' => 6,
                'Bénéficiaires' => 10,
            ];
        }
    
        // Financement
        $results = DB::table('projets')
            ->selectRaw('SUBSTRING(code_projet, 7, 1) AS type_financement')
            ->whereNotNull('code_projet')
            ->whereRaw('LENGTH(code_projet) >= 7')
            ->get()
            ->groupBy('type_financement')
            ->map(function ($group) {
                return count($group);
            });

        // Ensuite on joint avec la table type_financement
        $financements = DB::table('type_financement')
            ->select('type_financement.code_type_financement', 'type_financement.libelle', DB::raw('COUNT(p.code_projet) as total_projets'))
            ->join(DB::raw("(
                SELECT code_projet, SUBSTRING(code_projet, 7, 1) AS type_financement
                FROM projets
                WHERE code_projet IS NOT NULL
                AND LENGTH(code_projet) >= 7
                AND SUBSTRING(code_projet, 4, 3) = '{$groupeProjet}'
            ) as p"), 'p.type_financement', '=', 'type_financement.code_type_financement')
            ->groupBy('type_financement.code_type_financement', 'type_financement.libelle')
            ->orderBy('type_financement.code_type_financement')
            ->get();

    
        // Projets par année
        $projectsParAnnee = DB::table('projets')
            ->selectRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(code_projet, '_', 4), '_', -1) as annee, COUNT(*) as total")
            ->whereNotNull('code_projet')
            ->where('code_projet', 'like', '%\_%\_%\_%\_%')
            ->whereRaw("SUBSTRING(code_projet, 4, 3) = ?", [$groupeProjet])
            ->groupBy('annee')
            ->orderBy('annee')
            ->pluck('total', 'annee')
            ->toArray();

    
    
        if (empty($projectsParAnnee)) {
            $projectsParAnnee = [
                2021 => 12,
                2022 => 18,
                2023 => 23,
            ];
        }
    
        // Budget mensuel
        $budgetsParAnnee = DB::table('projets')
                ->selectRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(code_projet, '_', 4), '_', -1) as annee, SUM(cout_projet) as total")
                ->whereNotNull('code_projet')
                ->where('code_projet', 'like', '%\_%\_%\_%\_%')
                ->whereRaw("SUBSTRING(code_projet, 4, 3) = ?", [$groupeProjet])
                ->groupBy('annee')
                ->orderBy('annee')
                ->pluck('total', 'annee')
                ->toArray();
    


    
        if (empty($budgetsParMois)) {
            $budgetsParMois = [
                1 => 1000000,
                2 => 2000000,
                3 => 3000000,
                4 => 2500000,
                5 => 1500000,
            ];
        }
    
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
