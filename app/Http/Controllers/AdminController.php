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

    public function index()
    {

        $projets = ProjetEha2::all();
        $projets_prevus = ProjetStatutProjet::where("code_statut_projet", "01")->get();
        $projets_en_cours = ProjetStatutProjet::where("code_statut_projet", "02")->get();
        $projets_annulé = ProjetStatutProjet::where("code_statut_projet", "03")->get();
        $projets_cloture = ProjetStatutProjet::where("code_statut_projet", "04")->get();
        $projets_suspendus = ProjetStatutProjet::where("code_statut_projet", "05")->get();
        $projets_redemarrer = ProjetStatutProjet::where("code_statut_projet", "06")->get();


        $statuts = StatutProjet::all();


        $ecran = Ecran::find(29);
        $ecrans = Ecran::all();
        return view('dash', compact( 'ecran', 'ecrans','statuts', 'projets_prevus', 'projets_en_cours', 'projets_annulé', 'projets_cloture', 'projets_suspendus', 'projets_redemarrer', 'projets'));
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


}
