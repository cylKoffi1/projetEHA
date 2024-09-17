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

class AdminController extends Controller
{

    public function index(Request $request)
    {

        $projets = ProjetEha2::all();
        $projets_prevus = ProjetStatutProjet::where("code_statut_projet", "01")->get();
        $projets_en_cours = ProjetStatutProjet::where("code_statut_projet", "02")->get();
        $projets_annulé = ProjetStatutProjet::where("code_statut_projet", "03")->get();
        $projets_cloture = ProjetStatutProjet::where("code_statut_projet", "04")->get();
        $projets_suspendus = ProjetStatutProjet::where("code_statut_projet", "05")->get();
        $projets_redemarrer = ProjetStatutProjet::where("code_statut_projet", "06")->get();

        $years = ProjetEha2::selectRaw('YEAR(Date_demarrage_prevue) as year')
        ->whereRaw('YEAR(Date_demarrage_prevue) > 0') // Exclut les années <= 0
        ->groupBy('year')
        ->pluck('year')
        ->toArray();


            // Données pour nombre de projets
            $dataProjets = $this->getProjetData();

            // Données pour les autres types
            $dataByType = [
                'domaine' => $this->getDataByType('code_domaine', 'domaine_intervention', 'libelle'),
                'sous_domaine' => $this->getDataByType('code_sous_domaine', 'sous_domaine', 'libelle'),
                'district' => $this->getDataByType('code_district', 'district', 'libelle'),
                'region' => $this->getDataByType('code_region', 'region', 'libelle'),
            ];

        // Convertir les collections en tableaux
        foreach ($dataByType as $type => $data) {
        $dataByType[$type] = $data->toArray();
        }

        $statuts = StatutProjet::all();
        $ecran = Ecran::find(29);
        $ecrans = Ecran::all();

        return view('dash', compact('years','dataByType','dataProjets','ecran', 'ecrans','statuts', 'projets_prevus', 'projets_en_cours', 'projets_annulé', 'projets_cloture', 'projets_suspendus', 'projets_redemarrer', 'projets'));
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
