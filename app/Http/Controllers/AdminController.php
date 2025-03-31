<?php

namespace App\Http\Controllers;

use App\Models\DepenseRealisee;
use App\Models\Ecran;
use App\Models\ProjetEha2;
use App\Models\ProjetStatutProjet;
use App\Models\Rubriques;
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
    
        // Projets
        $totalProjects = DB::table('projets')->count();
    
        $projectStatusCounts = DB::table('projet_statut')
            ->join('type_statut', 'projet_statut.type_statut', '=', 'type_statut.id')
            ->select('type_statut.libelle', DB::raw('count(*) as total'))
            ->groupBy('type_statut.libelle')
            ->pluck('total', 'libelle')
            ->toArray();
    
        if (empty($projectStatusCounts)) {
            $projectStatusCounts = [
                'Prévu' => 10,
                'En cours' => 25,
                'Clôturés' => 5,
                'Suspendu' => 3,
                'Annulé' => 2,
                'Terminé' => 4,
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
        $financements = DB::table('financer')
            ->select('FinancementType', DB::raw('count(*) as total'))
            ->groupBy('FinancementType')
            ->pluck('total', 'FinancementType')
            ->toArray();
    
        if (empty($financements)) {
            $financements = [
                1 => 12, // Public
                2 => 7,  // Privé
            ];
        }
    
        // Projets par année
        $projectsParAnnee = DB::table('projets')
            ->select(DB::raw('YEAR(created_at) as annee'), DB::raw('count(*) as total'))
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
        $budgetsParMois = DB::table('financer')
            ->select(DB::raw('MONTH(created_at) as mois'), DB::raw('SUM(montant_finance) as total'))
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('total', 'mois')
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
            'budgetsParMois'
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
