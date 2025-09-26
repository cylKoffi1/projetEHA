<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\FonctionUtilisateur;
use App\Models\Pays;
use App\Models\SousDomaine;
use App\Models\User;
use App\Models\GroupeProjet;
use App\Models\LocalitesPays;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    // Méthode pour afficher le formulaire de création d'utilisateur
    public function getIndicatif($paysId)
    {
        // Récupérer l'indicatif du pays en fonction de son ID depuis la base de données
        $pays = Pays::find($paysId);

        // Vérifier si le pays existe
        if ($pays) {
            // Retourner l'indicatif du pays
            return response()->json(['indicatif' => $pays->codeTel]);
        } else {
            // Si le pays n'existe pas, retourner une réponse d'erreur
            return response()->json(['error' => 'Pays non trouvé'], 404);
        }
    }
    public function checkUsername(Request $request)
    {
        $username = $request->input('username');

        $user = User::where('login', $username)->first();

        return response()->json(['exists' => $user !== null]);
    }
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');

        // Ajoutez ces journaux pour déboguer
        Log::info('Email from request: ' . $email);
        Log::info('User exists: ' . (int) User::whereHas('acteur', function ($query) use ($email) {
            $query->where('email', $email);
        })->exists());

        $exists = User::whereHas('acteur', function ($query) use ($email) {
            $query->where('email', $email);
        })->exists();

        return response()->json(['exists' => $exists]);
    }
    public function checkEmail_personne(Request $request)
    {
        $email = $request->input('email');

        $exists = Acteur::where('email', $email)->exists();
        return response()->json(['exists' => $exists]);
    }

    public function detailsUser(Request $request, $userId)
    {
       $user = User::with('acteur')->find($userId);

        if (!$user) {
            // Gérer le cas où l'utilisateur n'est pas trouvé
            return redirect()->route('users.users')->with('error', 'Utilisateur non trouvé.');
        }
        $niveauxAcces = LocalitesPays::all();
        $groupe_utilisateur = Role::all();
        $fonctions = FonctionUtilisateur::all();
        $domaines = Domaine::all();
        $sous_domaines = SousDomaine::all();
        $groupe_projet = GroupeProjet::all();
        $personnes = Acteur::orderBy('libelle_long', 'asc')->get();
        $ecran = Ecran::find($request->input('ecran_id'));
        $groupeProjetSelectionne = auth()->user()->groupeProjetSelectionne();
        $domainesSelectionnes = auth()->user()->domainesSelectionnes();

        return view('users.user-profile', compact('ecran','domainesSelectionnes','groupeProjetSelectionne','groupe_projet','fonctions','niveauxAcces', 'domaines',  'sous_domaines', 'user', 'groupe_utilisateur'));
    }

    public function update_auth(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            $acteur = $user->acteur;
    
            $errors = [];
    
            // Email
            if ($request->filled('email') && $request->email !== $acteur->email) {
                $emailExists = DB::table('acteurs')
                    ->where('email', $request->email)
                    ->where('code_acteur', '!=', $acteur->code_acteur)
                    ->exists();
    
                if ($emailExists) {
                    $errors[] = "L'email est déjà utilisé, il n'a pas été modifié.";
                } else {
                    $acteur->email = $request->email;
                    $user->email = $request->email;
                }
            }
    
            // Login
            if ($request->filled('username') && $request->username !== $user->login) {
                $loginExists = DB::table('utilisateurs')
                    ->where('login', $request->username)
                    ->where('id', '!=', $user->id)
                    ->exists();
    
                if ($loginExists) {
                    $errors[] = "Le nom d'utilisateur est déjà utilisé, il n'a pas été modifié.";
                } else {
                    $user->login = $request->username;
                }
            }
    
            // Autres champs (non sensibles aux doublons)
            $acteur->libelle_court = $request->nom;
            $acteur->libelle_long = $request->prenom;
            $acteur->telephone = $request->tel;
            $acteur->adresse = $request->adresse;
    
            // Photo
            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = 'user_' . time() . '.' . $file->getClientOriginalExtension();
                $path = 'Data/acteur/';
                $file->move(public_path($path), $filename);
                $acteur->photo = $path . $filename;
            }
    
            // Sauvegarde
            $user->save();
            $acteur->save();
    
            // Fonction
            if ($request->filled('fonction')) {
                if ($acteur->personnePhysique) {
                    $utilisateur = $acteur->utilisateurs()->first();
                    if ($utilisateur) {
                        $utilisateur->update([
                            'fonction_utilisateur' => $request->fonction
                        ]);
                    }
    
                }
            }
            
    
            // Rôle (si admin)
            if (auth()->user()->hasRole('Administrateur plateforme') || auth()->user()->hasRole('Administrateur pays')  && $request->filled('group_user')) {
                $user->syncRoles([$request->group_user]);
            }
    
            return response()->json([
                'status' => 'success',
                'message' => 'Mise à jour partielle réussie.',
                'warnings' => $errors
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'errors' => ['Erreur serveur : ' . $e->getMessage()]
            ], 500);
        }
    }
    
    // DomaineController.php
    public function getDomainesByGroupeProjet($code)
    {
        return Domaine::where('groupe_projet_code', $code)->get();
    }

    // SousDomaineController.php
    public function getSousDomaines($codeDomaine, $codeGroupeProjet)
    {
        return SousDomaine::where('code_domaine', $codeDomaine)
                        ->where('code_groupe_projet', $codeGroupeProjet)
                        ->get();
    }


    /*public function update_auth(Request $request, $userId)
    {
        $request->validate([
            'username' => 'required',
            'nom' => 'required',
            'prenom' => 'required',
            'email' => 'required|email',
            'tel' => 'required',
            'adresse' => 'required',
        ]);

        // Mettez à jour les informations de l'utilisateur
        $user = User::find($userId);

        // Assurez-vous que l'utilisateur et la personne associée existent
        if (!$user || !$user->personnel) {
            // Gérer le cas où l'utilisateur n'est pas trouvé
            return redirect()->route('users.details', ['userId' => $userId])->with('error', 'Utilisateur non trouvé.');
        }
        // Décoder la chaîne JSON des sous-domaines
        $sousDomaines = json_decode($request->input('sd'), true);
        $domainesSel = json_decode($request->input('domS'), true);

        // Mettez à jour les informations de la personne
        $user->personnel->update([
            'nom' => $request->input('nom'),
            'prenom' => $request->input('prenom'),
            'email' => $request->input('email'),
            'telephone' => $request->input('tel'),
            'addresse' => $request->input('adresse'),
        ]);

        if ($request->input('structure') == "bai") {
            $user->personnel->update([
                'code_structure_bailleur' => $request->input('bailleur'),
                'code_structure_agence' => null,
                'code_structure_ministere' => null,
            ]);
        } else if ($request->input('structure') == "age") {
            $user->personnel->update([
                'code_structure_agence' => $request->input('agence'),
                'code_structure_bailleur' => null,
                'code_structure_ministere' => null,
            ]);
        } else {
            $user->personnel->update([
                'code_structure_ministere' => $request->input('ministere'),
                'code_structure_agence' => null,
                'code_structure_bailleur' => null,
            ]);
        }


        $newRoleId = $request->input('group_user'); // Récupérez le nouvel identifiant de rôle depuis la requête

        if($newRoleId){
            // Vérifiez si le rôle existe
            $role = Role::find($newRoleId);
            if($role){
                // Supprimez tous les rôles de l'utilisateur
                $user->roles()->detach();

                // Assignez le nouveau rôle à l'utilisateur
                $user->assignRole($role);
            }else{
                // Assignez le nouveau rôle à l'utilisateur

                $user->assignRole($role);
                //return response()->json(['error' => 'Le rôle spécifié n\'existe pas.']);
            }
        }else{
             // Aucun rôle spécifié dans la requête, vous pouvez gérer cela en conséquence
        }

        // Vérifiez et mettez à jour la fonction utilisateur si nécessaire
        if ($request->filled('fonction') && $user->latestFonction->code_fonction != $request->input('fonction')) {
            OccuperFonction::create([
                'code_personnel' => $user->personnel->code_personnel,
                'code_fonction' => $request->input('fonction'),
                'date'=>now()
            ]);
        }


        // Mettez à jour le nom d'utilisateur
        $user->update([
            'login' => $request->input('username'),
            'email' => $request->input('email'),
        ]);

        // Vrifiez si un nouveau fichier photo a été téléchargé
        if ($request->hasFile('photo')) {
            // Supprimez l'ancienne photo s'il en existe une
            if ($user->personnel->photo) {
                $oldPhotoPath = public_path("users/{$user->personnel->photo}");
                if (file_exists($oldPhotoPath)) {
                    unlink($oldPhotoPath);
                }
            }

            // Téléchargez et enregistrez la nouvelle photo
            $file = $request->file('photo');
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move('users', $filename);

            // Mettez à jour le champ de la photo dans la base de données
            $user->personnel->photo = $filename;
            $user->personnel->save();
        }


        $sous_dom = AvoirExpertise::where('code_personnel', $user->code_personnel)->get();
        $sousDomainesSelectionnes = $sousDomaines['sous_domaine'];
        $dom = UtilisateurDomaine::where('code_personnel', $user->code_personnel)->get();
        $domSEl = $domainesSel['domaine'];

        $sousDomainesExistants = $sous_dom->pluck('sous_domaine')->toArray();
        $sousDomainesASupprimer = array_diff($sousDomainesExistants, $sousDomainesSelectionnes);
        $DomainesExistants = $dom->pluck('code_domaine')->toArray();
        $DomainesASupprimer = array_diff($DomainesExistants, $domSEl);

        // Supprimez les associations qui ne sont plus sélectionnées
        AvoirExpertise::where('code_personnel', $user->code_personnel)
            ->whereIn('sous_domaine', $sousDomainesASupprimer)
            ->delete();

        // Supprimez les associations qui ne sont plus sélectionnées
        UtilisateurDomaine::where('code_personnel', $user->code_personnel)
            ->whereIn('code_domaine', $DomainesASupprimer)
            ->delete();

        // Ajoutez les nouvelles associations sélectionnées
        foreach ($sousDomainesSelectionnes as $sousDomaine) {
            AvoirExpertise::updateOrCreate(
                [
                    'code_personnel' => $user->code_personnel,
                    'sous_domaine' => $sousDomaine
                ]
            );
        }
        // Ajoutez les nouvelles associations sélectionnées
        foreach ($domSEl as $do) {
            UtilisateurDomaine::updateOrCreate(
                [
                    'code_personnel' => $user->code_personnel,
                    'code_domaine' => $do
                ]
            );
        }
        $ecran_id = $request->input('ecran_id');
        return response()->json(['success' => 'Profile  mis à jour avec succès.','ecran_id'=>$ecran_id]);
        // Rediriger avec un message de succès
    }*/



}
