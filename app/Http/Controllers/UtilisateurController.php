<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\Acteur;
use App\Models\DecoupageAdministratif;
use App\Models\DecoupageAdminPays;
use App\Models\Ecran;
use App\Models\FonctionTypeActeur;
use App\Models\GroupeProjet;
use App\Models\GroupeUtilisateur;
use App\Models\GroupeProjetPaysUser;
use App\Models\GroupeProjetUser;
use App\Models\LocalitesPays;
use App\Models\Pays;
use App\Models\PaysUser;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\Users;
use App\Models\UtilisateurChampExercice;
use App\Models\UtilisateurLieuExercice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $user = Auth::user();

            $ecran = Ecran::find($request->input('ecran_id'));
            // Récupérer les valeurs de session
            $paysSelectionne = session('pays_selectionne');
            $groupeSelectionne = session('projet_selectionne');

            if (!$paysSelectionne) {
                return redirect()->route('admin', ['ecran_id' => $request->input('ecran_id')])
                    ->with('error', 'Veuillez contacter l\'administrateur pour vous attribuer un pays avant de continuer.');
            }

            if (!$groupeSelectionne) {
                return redirect()->route('admin', ['ecran_id' => $request->input('ecran_id')])
                    ->with('error', 'Veuillez contacter l\'administrateur pour vous attribuer un groupe avant de continuer.');
            }

            // Récupérer les utilisateurs filtrés
            $utilisateurs = User::with([
                'acteur',
                'groupeUtilisateur',
                'groupeProjets',
                'champsExercice',
                'lieuxExercice',
                'pays'
            ])
            ->whereHas('pays', function ($query) use ($paysSelectionne) {
                $query->where('alpha3', $paysSelectionne);
            })
            ->whereHas('groupeProjets', function ($query) use ($groupeSelectionne) {
                $query->where('code', $groupeSelectionne);
            })
            ->get();



            $groupeProjets = GroupeProjetPaysUser::where('user_id', $user->acteur_id)->value('groupe_projet_id');

            $pays = Pays::all();
           $roles =
            RolePermission::where('role_source', Auth::user()->groupe_utilisateur_id)
            ->join('groupe_utilisateur as gu', 'gu.code', '=', 'role_permissions.role_target')
            ->where('role_permissions.can_assign', 1)
            ->select('code','gu.libelle_groupe')
            ->get();
            $groupProjects = GroupeProjet::where('code', $groupeSelectionne)->first();

            $acteurs = Acteur::where('is_user', 0)
            ->where('type_acteur', 'etp')
            ->orderBy('libelle_court', 'asc')->get();

            $acteurUpdate = Acteur::where('is_user', 1)
            ->where('type_acteur', 'etp')
            ->orderBy('libelle_court', 'asc')->get();

            $codePays = Pays::where('alpha3', $paysSelectionne)->first();

            $champsExercice = DecoupageAdministratif::join('decoupage_admin_pays', 'decoupage_administratif.code_decoupage', '=', 'decoupage_admin_pays.code_decoupage')
                ->where('decoupage_admin_pays.id_pays', $codePays->id)
                ->get();


            $lieuxExercice = LocalitesPays::where('id_pays', $codePays->alpha3)
                ->get(['id', 'libelle', 'code_decoupage']); // Récupérer les données nécessaires pour filtrage

            return view('users.Utilisateur', compact(
                'ecran',
                'utilisateurs',
                'roles',
                'groupProjects',
                'acteurs',
                'champsExercice',
                'lieuxExercice',
                'codePays',
                'acteurUpdate'
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

            // Valider les données
            $request->validate([
                'acteur_id' => 'required|exists:acteur,code_acteur',
                'groupe_utilisateur_id' => 'exists:groupe_utilisateur,code',
                'fonction_utilisateur' => 'string|max:255',
                'champs_exercice' => 'nullable|array',
                'champs_exercice.*' => 'exists:decoupage_admin_pays,code_decoupage',
                'lieux_exercice' => 'nullable|array',
                'lieux_exercice.*' => 'exists:localites_pays,id',
            ]);

            // Générer un login et un mot de passe si non fournis
            $login = $request->input('login');
            $password = Hash::make('123456789'); // Mot de passe par défaut

            // Enregistrer l'utilisateur
            $utilisateur = User::create([
                'acteur_id' => $request->acteur_id,
                'groupe_utilisateur_id' => $request->groupe_utilisateur_id,
                'fonction_utilisateur' => $request->fonction_utilisateur,
                'groupe_projet_id' => $request->groupe_projet_id ?? session('projet_selectionne'), // Ajout du groupe projet
                'login' => $login,
                'password' => $password,
                'email' => $request->email,
                'is_active' => true, // Par défaut actif
            ]);

            Acteur::where('code_acteur', $request->acteur_id)
                ->update(['is_user' => true]);

            // Assigner les groupes projets (plusieurs groupes) avec contrôle
            if ($request->groupe_utilisateur_id) {
                if ($request->groupe_utilisateur_id === 'ab') {
                    // Administrateur de la plateforme : associer tous les pays et tous les groupes projets
                    $paysList = LocalitesPays::distinct()->pluck('id_pays'); // Récupérer tous les id_pays sans doublon
                    $groupeProjets = GroupeProjet::pluck('code'); // Récupérer tous les groupes de projet

                    foreach ($paysList as $paysCode) {
                        foreach ($groupeProjets as $groupeProjetId) {
                            GroupeProjetPaysUser::updateOrCreate([
                                'user_id' => $request->acteur_id,
                                'pays_code' => $paysCode,
                                'groupe_projet_id' => $groupeProjetId
                            ]);
                        }
                    }
                } elseif ($request->groupe_utilisateur_id === 'ad') {
                    // Administrateur pays : associer au pays sélectionné et tous les groupes projets
                    $paysSelectionne = session('pays_selectionne');
                    $groupeProjets = GroupeProjet::pluck('code');

                    foreach ($groupeProjets as $groupeProjetId) {
                        GroupeProjetPaysUser::updateOrCreate([
                            'user_id' => $request->acteur_id,
                            'pays_code' => $paysSelectionne,
                            'groupe_projet_id' => $groupeProjetId
                        ]);
                    }
                } elseif ($request->groupe_utilisateur_id === 'ag') {
                    // Administrateur groupe projet : associer au pays et groupe projet sélectionné
                    $paysSelectionne = session('pays_selectionne');
                    $groupeSelectionne = session('projet_selectionne');

                    GroupeProjetPaysUser::updateOrCreate([
                        'user_id' => $request->acteur_id,
                        'pays_code' => $paysSelectionne,
                        'groupe_projet_id' => $groupeSelectionne
                    ]);
                }else {
                    // Utilisateur : associer au pays et groupe projet sélectionné
                    $paysSelectionne = session('pays_selectionne');
                    $groupeSelectionne = session('projet_selectionne');
                    GroupeProjetPaysUser::updateOrCreate([
                        'user_id' => $request->acteur_id,
                        'pays_code' => $paysSelectionne,
                        'groupe_projet_id' => $request->groupe_projet_id
                    ]);
                }
            }


            // Assigner les champs d'exercice
            if ($request->champs_exercice) {
                foreach ($request->champs_exercice as $champExerciceId) {
                    UtilisateurChampExercice::create([
                        'utilisateur_code' => $request->acteur_id,
                        'champ_exercice_id' => $champExerciceId,
                    ]);
                }
            }

            // Assigner les lieux d'exercice
            if ($request->lieux_exercice) {
                foreach ($request->lieux_exercice as $lieuExerciceId) {
                    UtilisateurLieuExercice::create([
                        'utilisateur_code' => $request->acteur_id,
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

    /**
     * afficher un utilisateur
     */
    public function show($id)
    {
        try {
            $utilisateur = User::with(['acteur', 'groupeProjets', 'lieuxExercice', 'champsExercice'])->findOrFail($id);
            Log::info("Utilisateur récupéré avec succès : ", $utilisateur->toArray());
            return response()->json($utilisateur);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des données utilisateur : " . $e->getMessage());
            return response()->json(['error' => 'Une erreur est survenue lors de la récupération des données.'], 500);
        }
    }

    /**
     * Modifier un utilisateur
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info("Tentative de modification de l'utilisateur ID {$id}.", ['data' => $request->all()]);

            // ✅ Étape 1 : Validation des données
            $validator = \Validator::make($request->all(), [
                'acteur_id_Modifier' => 'required|exists:acteur,code_acteur',
                'groupe_utilisateur_id' => 'required|exists:groupe_utilisateur,code',
                'fonction_utilisateur' => 'nullable|string|max:255',
                'login' => 'required|string|max:255',
                'password' => [
                    'nullable',
                    'string',
                    'min:8',           // Minimum 8 caractères
                    'regex:/[A-Z]/',   // Au moins une lettre majuscule
                    'regex:/[a-z]/',   // Au moins une lettre minuscule
                    'regex:/[0-9]/',   // Au moins un chiffre
                    'regex:/[@$!%*?&]/' // Au moins un caractère spécial
                ],
                'email' => 'required|email|max:255',
                'champs_exercice' => 'nullable|array',
                'champs_exercice.*' => 'exists:decoupage_admin_pays,code_decoupage',
                'lieux_exercice' => 'nullable|array',
                'lieux_exercice.*' => 'exists:localites_pays,id',
            ]);

            // Si la validation échoue, on stoppe immédiatement
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // ✅ Étape 2 : Vérifier manuellement le mot de passe si modifié
            if (!empty($request->password)) {
                if (!preg_match('/[A-Z]/', $request->password) ||
                    !preg_match('/[a-z]/', $request->password) ||
                    !preg_match('/[0-9]/', $request->password) ||
                    !preg_match('/[@$!%*?&]/', $request->password)
                ) {
                    return redirect()->back()->withErrors(['password' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.'])->withInput();
                }
            }

            // ✅ Étape 3 : Trouver l'utilisateur et mettre à jour les informations
            $utilisateur = User::findOrFail($id);

            $updateData = [
                'acteur_id' => $request->acteur_id_Modifier,
                'groupe_utilisateur_id' => $request->groupe_utilisateur_id,
                'fonction_utilisateur' => $request->fonction_utilisateur,
                'login' => $request->login,
                'email' => $request->email,
            ];

            // Si le mot de passe est modifié, on l'ajoute à la mise à jour
            if (!empty($request->password)) {
                $updateData['password'] = Hash::make($request->password);
            }

            $utilisateur->update($updateData);

            // ✅ Étape 4 : Mise à jour des groupes projets
            $paysSelectionne = session('pays_selectionne');
            $groupeSelectionne = session('projet_selectionne');

            if ($request->groupe_utilisateur_id === 'ab') {
                // Administrateur plateforme -> Associer à tous les pays et groupes projets
                $paysList = LocalitesPays::distinct()->pluck('id_pays');
                $groupeProjets = GroupeProjet::pluck('code');

                foreach ($paysList as $paysCode) {
                    foreach ($groupeProjets as $groupeProjetId) {
                        GroupeProjetPaysUser::updateOrCreate([
                            'user_id' => $request->acteur_id_Modifier,
                            'pays_code' => $paysCode,
                            'groupe_projet_id' => $groupeProjetId
                        ]);
                    }
                }
            } elseif ($request->groupe_utilisateur_id === 'ad') {
                // Administrateur pays -> Associer au pays sélectionné et tous les groupes projets
                $groupeProjets = GroupeProjet::pluck('code');

                foreach ($groupeProjets as $groupeProjetId) {
                    GroupeProjetPaysUser::updateOrCreate([
                        'user_id' => $request->acteur_id_Modifier,
                        'pays_code' => $paysSelectionne,
                        'groupe_projet_id' => $groupeProjetId
                    ]);
                }
            } elseif ($request->groupe_utilisateur_id === 'ag') {
                // Administrateur groupe projet -> Associer au pays et groupe projet sélectionné
                GroupeProjetPaysUser::updateOrCreate([
                    'user_id' => $request->acteur_id_Modifier,
                    'pays_code' => $paysSelectionne,
                    'groupe_projet_id' => $groupeSelectionne
                ]);
            } else {
                // Utilisateur simple -> Associer au groupe projet spécifique
                GroupeProjetPaysUser::updateOrCreate([
                    'user_id' => $request->acteur_id_Modifier,
                    'pays_code' => $paysSelectionne,
                    'groupe_projet_id' => $request->groupe_projet_id
                ]);
            }

            // ✅ Étape 5 : Mise à jour des Champs d'exercice
            UtilisateurChampExercice::where('utilisateur_code', $id)->delete();
            if ($request->champs_exercice) {
                foreach ($request->champs_exercice as $champExerciceId) {
                    UtilisateurChampExercice::create([
                        'utilisateur_code' => $id,
                        'champ_exercice_id' => $champExerciceId,
                    ]);
                }
            }

            // ✅ Étape 6 : Mise à jour des Lieux d'exercice
            UtilisateurLieuExercice::where('utilisateur_code', $id)->delete();
            if ($request->lieux_exercice) {
                foreach ($request->lieux_exercice as $lieuExerciceId) {
                    UtilisateurLieuExercice::create([
                        'utilisateur_code' => $id,
                        'lieu_exercice_id' => $lieuExerciceId,
                    ]);
                }
            }

            Log::info("Utilisateur ID {$id} mis à jour avec succès.");
            return redirect()->back()->with('success', 'Utilisateur mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la modification de l'utilisateur ID {$id} : " . $e->getMessage());
            return redirect()->back()->with('error', "Une erreur est survenue lors de la modification de l'utilisateur.");
        }
    }





    /**
     * Désactiver un utilisateur
     */
    public function disable($id)
    {
        try {
            Log::info("Tentative de désactivation de l'utilisateur ID {$id}.");

            // Trouver l'utilisateur
            $utilisateur = User::findOrFail($id);

            // Désactiver l'utilisateur
            $utilisateur->update(['is_active' => false]);

            Log::info("Utilisateur ID {$id} désactivé avec succès.");
            return redirect()->back()->with('success', 'Utilisateur désactivé avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la désactivation de l'utilisateur ID {$id} : " . $e->getMessage());
            return redirect()->back()->with('error', "Une erreur est survenue lors de la désactivation de l'utilisateur.");
        }
    }

    /**
     * Réactiver un utilisateur
     */
    public function restore($id)
    {
        try {
            Log::info("Tentative de réactivation de l'utilisateur ID {$id}.");

            // Trouver l'utilisateur désactivé
            $utilisateur = User::where('id', $id)->where('is_active', false)->firstOrFail();

            // Réactiver l'utilisateur
            $utilisateur->update(['is_active' => true]);

            Log::info("Utilisateur ID {$id} réactivé avec succès.");
            return redirect()->back()->with('success', 'Utilisateur réactivé avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la réactivation de l'utilisateur ID {$id} : " . $e->getMessage());
            return redirect()->back()->with('error', "Une erreur est survenue lors de la réactivation de l'utilisateur.");
        }
    }


}
