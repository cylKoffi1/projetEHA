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

        $ecran = Ecran::find(29);
        $ecrans = Ecran::all();

        return view('dash', compact('ecran', 'ecrans'));
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
