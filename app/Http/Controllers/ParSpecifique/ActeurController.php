<?php

namespace App\Http\Controllers\ParSpecifique;


use App\Http\Controllers\Controller;
use App\Models\Acteur;
use App\Models\Ecran;
use App\Models\Pays;
use App\Models\PaysUser;
use App\Models\TypeActeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActeurController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Récupérer le pays de l'utilisateur connecté via PaysUser
            dd(auth()->user());
            $userCountry = PaysUser::where('code_user', auth()->user()->code_personnel)->first();
            $userCountryId = $userCountry ? $userCountry->code_pays : null;

            // Vérifier si l'utilisateur a un pays attribué
            if (!$userCountryId) {
                return redirect()->route('admin', ['ecran_id' => $request->input('ecran_id')])
                    ->with('error', 'Veuillez contacter l\'administrateur pour vous attribuer un pays avant de continuer.');
            }

            $pays = Pays::where('alpha3', $userCountryId)->first();
            $ecran = Ecran::find($request->input('ecran_id'));
            $TypeActeurs = TypeActeur::all();


            // Filtrer les acteurs selon le statut (activé ou désactivé)
            $filter = $request->input('filter'); // Récupérer le paramètre "filter" de la requête
            if ($filter === 'inactif') {
                // Afficher uniquement les acteurs désactivés
                $acteurs = Acteur::with(['pays', 'type'])->where('is_active', 0)->get();
            } else {
                // Afficher tous les acteurs
                $acteurs = Acteur::with(['pays', 'type'])->withInactive()->get();
            }

            return view('parSpecifique.Acteur', compact('ecran', 'TypeActeurs', 'acteurs', 'pays', 'filter'));
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des acteurs : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors du chargement des acteurs.');
        }
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'libelle_long' => 'required|string|max:255',
                'libelle_court' => 'required|string|max:255',
                'type_acteur' => 'required|string|max:5',
                'email' => 'required|email|unique:acteur,email,' . ($request->id ?? 'NULL'),
                'telephone' => 'nullable|string|max:50',
                'adresse' => 'nullable|string|max:255',
                'code_pays' => 'required|exists:pays,alpha3', // Vérification du code pays
            ]);


            Acteur::create($request->all());

            return redirect()->back()->with('success', 'Acteur ajouté avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'enregistrement d'un acteur : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de l\'enregistrement de l\'acteur.');
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'libelle_long' => 'required|string|max:255',
                'libelle_court' => 'required|string|max:255',
                'type_acteur' => 'required|string|max:5',
                'email' => 'required|email|unique:acteur,email,' . $id . ',code_acteur',
                'telephone' => 'nullable|string|max:50',
                'adresse' => 'nullable|string|max:255',
                'code_pays' => 'required|exists:pays,alpha3',
            ]);

            $acteur = Acteur::where('code_acteur', $id)->firstOrFail(); // Identifier l'acteur avec 'code_acteur'
            $acteur->update($request->all());

            return redirect()->back()->with('success', 'Acteur mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la mise à jour d'un acteur : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de la mise à jour de l\'acteur.');
        }
    }



    public function destroy($id)
    {
        try {
            // Trouver l'acteur spécifique par son ID (code_acteur)
            $acteur = Acteur::where('code_acteur', $id)->firstOrFail();

            // Désactiver l'acteur en mettant is_active à false
            $acteur->update(['is_active' => false]);

            return redirect()->back()->with('success', 'Acteur désactivé avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la désactivation d'un acteur : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de la désactivation de l\'acteur.');
        }
    }


    public function restore(Request $request, $id)
    {
        try {
            // Log pour vérifier l'ID reçu
            Log::info('ID reçu pour réactivation : ' . $id);

            // Désactiver les portées globales pour inclure les acteurs désactivés
            $acteur = Acteur::withoutGlobalScope('active')->where('code_acteur', $id)->first();

            // Si aucun acteur n'est trouvé, consignez une erreur
            if (!$acteur) {
                Log::error("Aucun acteur trouvé avec le code_acteur : " . $id);
                return redirect()->back()->withErrors('Aucun acteur trouvé pour réactivation.');
            }

            // Réactiver l'acteur
            $acteur->update(['is_active' => true]);

            return redirect()->route('acteurs.index', ['ecran_id' => $request->input('ecran_id')])
                ->with('success', 'Acteur réactivé avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la réactivation d'un acteur : " . $e->getMessage());
            return redirect()->back()->withErrors('Une erreur est survenue lors de la réactivation de l\'acteur.');
        }
    }





}
