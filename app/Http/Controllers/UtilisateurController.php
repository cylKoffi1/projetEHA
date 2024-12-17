<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\Acteur;
use App\Models\DecoupageAdministratif;
use App\Models\DecoupageAdminPays;
use App\Models\Ecran;
use App\Models\FonctionTypeActeur;
use App\Models\GroupeUtilisateur;
use App\Models\GroupeProjet;
use App\Models\LocalitesPays;
use App\Models\Pays;
use App\Models\PaysUser;
use App\Models\User;
use App\Models\Users;
use App\Models\UtilisateurChampExercice;
use App\Models\UtilisateurLieuExercice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UtilisateurController extends Controller
{
    /**
     * Afficher la liste des utilisateurs
     */
    public function index(Request $request)
    {
        try {
            Log::info('Chargement de la liste des utilisateurs.');

            $ecran = Ecran::find($request->input('ecran_id'));
            $utilisateurs = User::with([
                'acteur',
                'groupeUtilisateur',
                'groupeProjet',
                'champsExercice',
                'lieuxExercice'
            ])->get();

            $roles = GroupeUtilisateur::all();
            $groupProjects = GroupeProjet::all();
            $acteurs = Acteur::orderBy('libelle_court', 'asc')->get();;

            $userCountry = Auth()->User();//PaysUser::where('code_user', )->first();
            dd($userCountry);
            $userCountryId = $userCountry ? $userCountry->code_pays : null;

            if (!$userCountryId) {
                return redirect()->route('users.personnel', ['ecran_id' => $request->input('ecran_id')])
                    ->with('error', 'Veuillez contacter l\'administrateur pour vous attribuer un code pays avant de continuer.');
            }

            $codePays = Pays::where('alpha3', $userCountryId)->first();

            $champsExercice = DecoupageAdministratif::join('decoupage_admin_pays', 'decoupage_administratif.code_decoupage', '=', 'decoupage_admin_pays.code_decoupage')
                ->where('decoupage_admin_pays.id_pays', $codePays->id)
                ->get();

            $lieuxExercice = LocalitesPays::where('id_pays', $codePays->alpha3)
                ->get(['id', 'libelle', 'code_decoupage']); // Récupérer les données nécessaires pour filtrage
                //dd($lieuxExercice);
            return view('users.Utilisateur', compact(
                'ecran',
                'utilisateurs',
                'roles',
                'groupProjects',
                'acteurs',
                'champsExercice',
                'lieuxExercice'
            ));
        } catch (\Exception $e) {
            Log::error("Erreur lors du chargement des utilisateurs : " . $e->getMessage());
            return redirect()->back()->withErrors("Une erreur est survenue lors du chargement des utilisateurs.");
        }
    }



    /**
     * Enregistrer un utilisateur
     */
    public function store(Request $request)
    {
        try {
            Log::info('Tentative d\'enregistrement d\'un utilisateur.', ['data' => $request->all()]);

            $request->validate([
                'acteur_id' => 'required|exists:acteur,code_acteur',
                'groupe_utilisateur_id' => 'required|exists:groupe_utilisateur,id',
                'groupe_projet_id' => 'nullable|array',
                'groupe_projet_id.*' => 'exists:groupe_projet,id',
                'login' => 'required|string|unique:utilisateurs,login|max:100',
                'password' => 'nullable|string|min:8',
                'fonction_utilisateur' => 'required|string|max:255',
                'champs_exercice' => 'nullable|array',
                'champs_exercice.*' => 'exists:decoupage_admin_pays,code',
                'lieux_exercice' => 'nullable|array',
                'lieux_exercice.*' => 'exists:localites_pays,id',
            ]);

            // Récupérer l'utilisateur connecté pour appliquer les règles
            $utilisateurConnecte = auth()->user();

            // Vérifier les règles pour le rôle
            if (!$this->peutAttribuerRole($utilisateurConnecte->groupeUtilisateur->code, $request->groupe_utilisateur_id)) {
                return redirect()->back()->withErrors('Vous ne pouvez pas attribuer ce rôle.');
            }

            // Vérifier les règles pour les groupes projets
            if (!$this->peutAttribuerGroupeProjet($utilisateurConnecte->groupeUtilisateur->code, $request->groupe_projet_id)) {
                return redirect()->back()->withErrors('Vous ne pouvez pas attribuer ces groupes projets.');
            }

            // Générer un mot de passe par défaut si non fourni
            $password = $request->password ?? 'password123';

            // Créer l'utilisateur
            $utilisateur = User::create([
                'acteur_id' => $request->acteur_id,
                'groupe_utilisateur_id' => $request->groupe_utilisateur_id,
                'login' => $request->login,
                'password' => Hash::make($password),
                'fonction_utilisateur' => $request->fonction_utilisateur,
            ]);

            // Assigner les groupes projets
            if ($request->groupe_projet_id) {
                $utilisateur->groupeProjet()->sync($request->groupe_projet_id);
            }

            // Assigner les champs d'exercice
            if ($request->champs_exercice) {
                foreach ($request->champs_exercice as $champExerciceId) {
                    UtilisateurChampExercice::create([
                        'utilisateur_id' => $utilisateur->id,
                        'champ_exercice_id' => $champExerciceId,
                    ]);
                }
            }

            // Assigner les lieux d'exercice
            if ($request->lieux_exercice) {
                foreach ($request->lieux_exercice as $lieuExerciceId) {
                    UtilisateurLieuExercice::create([
                        'utilisateur_id' => $utilisateur->id,
                        'lieu_exercice_id' => $lieuExerciceId,
                    ]);
                }
            }

            Log::info('Utilisateur enregistré avec succès.', ['utilisateur_id' => $utilisateur->id]);
            return redirect()->back()->with('success', 'Utilisateur enregistré avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de l\'utilisateur : ' . $e->getMessage());
            return redirect()->back()->withErrors("Une erreur est survenue lors de l'enregistrement de l'utilisateur.");
        }
    }
    public function getFonctionsByTypeActeur($typeActeur)
{
    try {
        Log::info('Chargement des fonctions pour le type acteur : ' . $typeActeur);

        // Récupérer les fonctions associées au type acteur
        $fonctions = FonctionTypeActeur::where('type_acteur_code', $typeActeur)
            ->with('fonction') // Charger les détails de la fonction
            ->get()
            ->map(function ($item) {
                return [
                    'code' => $item->fonction->code,
                    'libelle_fonction' => $item->fonction->libelle_fonction,
                ];
            });

        // Enregistrer les résultats dans le log
        Log::info('Fonctions récupérées : ', $fonctions->toArray());

        return response()->json($fonctions);
    } catch (\Exception $e) {
        // Enregistrer les erreurs dans le log
        Log::error('Erreur lors du chargement des fonctions pour le type acteur ' . $typeActeur . ': ' . $e->getMessage());

        return response()->json(['error' => 'Erreur lors du chargement des fonctions.'], 500);
    }
}


    /**
     * Modifier un utilisateur
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info("Modification de l'utilisateur ID {$id}.", ['data' => $request->all()]);

            $request->validate([
                'groupe_utilisateur_id' => 'required|exists:groupe_utilisateur,id',
                'groupe_projet_id' => 'nullable|array',
                'groupe_projet_id.*' => 'exists:groupe_projet,id',
                'fonction_utilisateur' => 'required|string|max:255',
                'champs_exercice' => 'nullable|array',
                'champs_exercice.*' => 'exists:decoupage_admin_pays,code',
                'lieux_exercice' => 'nullable|array',
                'lieux_exercice.*' => 'exists:localites_pays,id',
            ]);

            $utilisateur = User::findOrFail($id);

            // Récupérer l'utilisateur connecté pour appliquer les règles
            $utilisateurConnecte = auth()->user();

            // Vérifier les règles pour le rôle
            if (!$this->peutAttribuerRole($utilisateurConnecte->groupeUtilisateur->code, $request->groupe_utilisateur_id)) {
                return redirect()->back()->withErrors('Vous ne pouvez pas modifier ce rôle.');
            }

            // Vérifier les règles pour les groupes projets
            if (!$this->peutAttribuerGroupeProjet($utilisateurConnecte->groupeUtilisateur->code, $request->groupe_projet_id)) {
                return redirect()->back()->withErrors('Vous ne pouvez pas modifier ces groupes projets.');
            }

            $utilisateur->update($request->only('groupe_utilisateur_id', 'fonction_utilisateur'));
            $utilisateur->groupeProjet()->sync($request->groupe_projet_id);

            // Mettre à jour les champs d'exercice
            UtilisateurChampExercice::where('utilisateur_id', $id)->delete();
            if ($request->champs_exercice) {
                foreach ($request->champs_exercice as $champExerciceId) {
                    UtilisateurChampExercice::create([
                        'utilisateur_id' => $id,
                        'champ_exercice_id' => $champExerciceId,
                    ]);
                }
            }

            // Mettre à jour les lieux d'exercice
            UtilisateurLieuExercice::where('utilisateur_id', $id)->delete();
            if ($request->lieux_exercice) {
                foreach ($request->lieux_exercice as $lieuExerciceId) {
                    UtilisateurLieuExercice::create([
                        'utilisateur_id' => $id,
                        'lieu_exercice_id' => $lieuExerciceId,
                    ]);
                }
            }

            Log::info("Utilisateur ID {$id} modifié avec succès.");
            return redirect()->back()->with('success', 'Utilisateur mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la modification de l'utilisateur ID {$id} : " . $e->getMessage());
            return redirect()->back()->withErrors("Une erreur est survenue lors de la modification de l'utilisateur.");
        }
    }


    /**
     * Désactiver un utilisateur
     */
    public function destroy($id)
    {
        try {
            Log::info("Tentative de désactivation de l'utilisateur ID {$id}.");

            $utilisateur = User::findOrFail($id);
            $utilisateur->update(['is_active' => false]);

            Log::info("Utilisateur ID {$id} désactivé avec succès.");
            return redirect()->back()->with('success', 'Utilisateur désactivé avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la désactivation de l'utilisateur ID {$id} : " . $e->getMessage());
            return redirect()->back()->withErrors("Une erreur est survenue lors de la désactivation de l'utilisateur.");
        }
    }

    /**
     * Réactiver un utilisateur
     */
    public function restore($id)
    {
        try {
            Log::info("Tentative de réactivation de l'utilisateur ID {$id}.");

            $utilisateur = User::withInactive()->findOrFail($id);
            $utilisateur->update(['is_active' => true]);

            Log::info("Utilisateur ID {$id} réactivé avec succès.");
            return redirect()->back()->with('success', 'Utilisateur réactivé avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la réactivation de l'utilisateur ID {$id} : " . $e->getMessage());
            return redirect()->back()->withErrors("Une erreur est survenue lors de la réactivation de l'utilisateur.");
        }
    }
}
