<?php

namespace App\Http\Controllers\ParGeneraux;

use App\Http\Controllers\Controller;
use App\Models\Ecran;
use App\Models\FonctionTypeActeur;
use App\Models\FonctionUtilisateur;
use App\Models\TypeActeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FonctionTypeActeurController extends Controller
{
    public function index(Request $request)
    {
        try {
            $ecran = Ecran::find($request->input('ecran_id'));
            if (!$ecran) {
                return redirect()->route('admin')->withErrors('Écran introuvable.');
            }

            $fonctionTypeActeurs = FonctionTypeActeur::with(['fonction', 'typeActeur'])->get();
            $fonctions = FonctionUtilisateur::all();
            $typesActeurs = TypeActeur::all();

            return view('parGeneraux.fonctionTypeActeur', compact('ecran', 'fonctionTypeActeurs', 'fonctions', 'typesActeurs'));
        } catch (\Exception $e) {
            Log::error("Erreur lors du chargement des fonctions par type d'acteur : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors du chargement.');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'fonction_code' => 'required|exists:fonction_utilisateur,code',
                'type_acteur_code' => 'required|exists:type_acteur,cd_type_acteur',
            ]);

            FonctionTypeActeur::create($request->only('fonction_code', 'type_acteur_code'));

            return redirect()->back()->with('success', 'Association ajoutée avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'enregistrement de l'association : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de l\'ajout.');
        }
    }

    public function destroy($id)
    {
        try {
            $fonctionTypeActeur = FonctionTypeActeur::findOrFail($id);
            $fonctionTypeActeur->delete();

            return redirect()->back()->with('success', 'Association supprimée avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la suppression de l'association : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de la suppression.');
        }
    }
}
