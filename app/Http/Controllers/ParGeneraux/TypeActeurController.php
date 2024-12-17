<?php

namespace App\Http\Controllers\ParGeneraux;

use App\Http\Controllers\Controller;
use App\Models\Ecran;
use App\Models\TypeActeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TypeActeurController extends Controller
{
    public function index(Request $request)
    {
        try {
            $ecran = Ecran::find($request->input('ecran_id'));
            $typesActeurs = TypeActeur::all();
            return view('parGeneraux.typeActeurs', compact('ecran','typesActeurs'));
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des types d'acteurs : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors du chargement des types d\'acteurs.');
        }
    }

    public function store(Request $request)
    {
        try {
            // Définir les règles de validation pour la création
            $request->validate([
                'cd_type_acteur' => 'required|string|max:3|unique:type_acteur,cd_type_acteur',
                'libelle_type_acteur' => 'required|string|max:255',
            ]);

            // Création d'un nouveau type d'acteur
            TypeActeur::create([
                'cd_type_acteur' => $request->cd_type_acteur,
                'libelle_type_acteur' => $request->libelle_type_acteur,
            ]);

            return redirect()->back()->with('success', 'Type d\'acteur ajouté avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'enregistrement d'un type d'acteur : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de l\'enregistrement du type d\'acteur.');
        }
    }

    public function update(Request $request, $cd_type_acteur)
    {
        try {
            // Définir les règles de validation pour la mise à jour
            $request->validate([
                'libelle_type_acteur' => 'required|string|max:255',
            ]);

            // Rechercher l'enregistrement par `cd_type_acteur`
            $typeActeur = TypeActeur::where('cd_type_acteur', $cd_type_acteur)->firstOrFail();

            // Mise à jour de l'enregistrement
            $typeActeur->update([
                'libelle_type_acteur' => $request->libelle_type_acteur,
            ]);

            return redirect()->back()->with('success', 'Type d\'acteur mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour d'un type d'acteur : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de la mise à jour du type d\'acteur.');
        }
    }


    public function destroy($cd_type_acteur)
{
    try {
        // Rechercher le type d'acteur par `cd_type_acteur`
        $typeActeur = TypeActeur::where('cd_type_acteur', $cd_type_acteur)->firstOrFail();

        // Suppression
        $typeActeur->delete();

        return redirect()->back()->with('success', 'Type d\'acteur supprimé avec succès.');
    } catch (\Exception $e) {
        Log::error("Erreur lors de la suppression du type d'acteur : " . $e->getMessage());
        return redirect()->back()->withErrors('Une erreur est survenue lors de la suppression du type d\'acteur.');
    }
}


}
