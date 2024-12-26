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
            $typeActeurCode = $request->input('type_acteur_code'); // Récupérer le code sélectionné
            $selectedFonctions = [];

            if ($typeActeurCode) {
                $selectedFonctions = FonctionTypeActeur::where('type_acteur_code', $typeActeurCode)
                    ->pluck('fonction_code')
                    ->toArray();
            }
            return view('parGeneraux.fonctionTypeActeur', compact('ecran', 'fonctionTypeActeurs', 'fonctions', 'typesActeurs', 'selectedFonctions'));
        } catch (\Exception $e) {
            Log::error("Erreur lors du chargement des fonctions par type d'acteur : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors du chargement.');
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Données reçues : ', $request->all());

            $request->validate([
                'fonction_code' => 'required|array',
                'fonction_code.*' => 'exists:fonction_utilisateur,code',
                'type_acteur_code' => 'required|exists:type_acteur,cd_type_acteur',
            ]);

            $typeActeurCode = $request->input('type_acteur_code');
            $fonctionCodes = $request->input('fonction_code');
            $addedFunctions = []; // Stocker les fonctions ajoutées
            $skippedFunctions = []; // Stocker les fonctions déjà existantes

            foreach ($fonctionCodes as $fonctionCode) {
                // Vérifier si l'association existe déjà
                $exists = FonctionTypeActeur::where('type_acteur_code', $typeActeurCode)
                    ->where('fonction_code', $fonctionCode)
                    ->exists();

                if (!$exists) {
                    // Ajouter l'association si elle n'existe pas
                    FonctionTypeActeur::create([
                        'fonction_code' => $fonctionCode,
                        'type_acteur_code' => $typeActeurCode,
                    ]);
                    $addedFunctions[] = $fonctionCode;
                } else {
                    // Si elle existe déjà, ne pas l'ajouter
                    $skippedFunctions[] = $fonctionCode;
                }
            }

            // Construire un message pour l'utilisateur
            $message = count($addedFunctions) > 0
                ? count($addedFunctions) . ' fonction(s) ajoutée(s) avec succès.'
                : 'Aucune nouvelle fonction ajoutée.';
            if (count($skippedFunctions) > 0) {
                $message .= ' ' . count($skippedFunctions) . ' fonction(s) déjà existante(s) ont été ignorée(s).';
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'enregistrement des associations : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de l\'enregistrement des associations.');
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
