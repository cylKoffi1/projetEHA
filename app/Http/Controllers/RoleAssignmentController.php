<?php

// app/Http/Controllers/RoleAssignmentController.php

namespace App\Http\Controllers;

use App\Models\Ecran;
use App\Models\GroupeUtilisateur;
use App\Models\Pays;
use App\Models\RoleHasRubrique;
use App\Models\Rubriques;
use App\Models\SousMenu;
use App\Models\View;
use Illuminate\Http\Request;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class RoleAssignmentController extends Controller
{

    public function index(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $groupes = GroupeUtilisateur::all();
        $roles = GroupeUtilisateur::all();

        return view('habilitations.role-assignment', compact('groupes', 'ecran',  'roles'));
    }


    public function assignRoles(Request $request)
    {

        try {
            set_time_limit(600);
            Log::info("Début de la fonction assignRoles");
            // Récupérer les données du formulaire
            $role_id = $request->input('role');
            $consulterRubrique = json_decode($request->input('consulterRubrique'));
            $consulterRubriqueEcran = json_decode($request->input('consulterRubriqueEcran'));
            $consulterSousMenu = json_decode($request->input('consulterSousMenu'));
            $consulterSousMenuEcran = json_decode($request->input('consulterSousMenuEcran'));

            $ajouterRubriqueEcran = json_decode($request->input('ajouterRubriqueEcran'));
            $modifierRubriqueEcran = json_decode($request->input('modifierRubriqueEcran'));
            $supprimerRubriqueEcran = json_decode($request->input('supprimerRubriqueEcran'));

            $ajouterSousMenuEcran = json_decode($request->input('ajouterSousMenuEcran'));
            $modifierSousMenuEcran = json_decode($request->input('modifierSousMenuEcran'));
            $supprimerSousMenuEcran = json_decode($request->input('supprimerSousMenuEcran'));

            $permissionsAsupprimer = json_decode($request->input('permissionsAsupprimer'));

            Log::info("Données récupérées du formulaire", [
                'role_id' => $role_id,
                'consulterRubrique' => $consulterRubrique,
                'consulterRubriqueEcran' => $consulterRubriqueEcran,
                'consulterSousMenu' => $consulterSousMenu,
                'consulterSousMenuEcran' => $consulterSousMenuEcran,
                'ajouterRubriqueEcran' => $ajouterRubriqueEcran,
                'modifierRubriqueEcran' => $modifierRubriqueEcran,
                'supprimerRubriqueEcran' => $supprimerRubriqueEcran,
                'ajouterSousMenuEcran' => $ajouterSousMenuEcran,
                'modifierSousMenuEcran' => $modifierSousMenuEcran,
                'supprimerSousMenuEcran' => $supprimerSousMenuEcran,
                'permissionsAsupprimer' => $permissionsAsupprimer,
            ]);

            // Récupérer le rôle
            $role = GroupeUtilisateur::where('code', $role_id)->firstOrFail();
            if (!$role) {
                Log::error("Le rôle avec l'ID {$role_id} n'existe pas.");
                return response()->json(['error' => "Rôle non trouvé"], 404);
            }
            $users = $role->utilisateurs;

            Log::info("Rôle et utilisateurs associés récupérés", ['role_id' => $role_id, 'user_count' => count($users)]);

            // Supprimer les associations non cochées
            RoleHasRubrique::where('role_id', $role_id)
                ->whereNotIn('rubrique_id', $consulterRubrique)
                ->delete();
            Log::info("Associations non cochées supprimées pour le rôle", ['role_id' => $role_id]);

            Log::info("Début du traitement de consulterRubrique");
            // Parcourir et enregistrer chaque ID dans le tableau consulterRubrique
            foreach ($consulterRubrique as $id) {
                // Vérifier si une association existe déjà pour ce rôle et cette rubrique
                $existingAssociation = RoleHasRubrique::where('role_id', $role_id)
                    ->where('rubrique_id', $id)
                    ->first();

                // Si aucune association n'existe, créez-en une nouvelle
                if (!$existingAssociation) {
                    $roleHasRubrique = new RoleHasRubrique;
                    $roleHasRubrique->rubrique_id = $id;
                    $roleHasRubrique->role_id = $role_id;
                    $roleHasRubrique->save();
                    Log::info("Nouvelle association ajoutée pour rubrique", ['role_id' => $role_id, 'rubrique_id' => $id]);

                    // Ajouter la permission à synchroniser
                    $rubrique = Rubriques::find($id);
                    $permission = Permission::findById($rubrique->permission_id);
                    $role->givePermissionTo($permission->name);
                    Log::info("Permission accordée pour rubrique", ['rubrique_id' => $id, 'permission' => $permission->name]);
                    Log::info("Traitement de rubrique {$id}");
                    foreach ($users as $user) {
                        // $user->givePermissionTo($permission->name);
                        $user->assignRole($role->name);
                    }
                } else {
                    // Ajouter la permission à synchroniser
                    $rubrique = Rubriques::find($id);
                    $permission = Permission::findById($rubrique->permission_id);
                    $role->givePermissionTo($permission->name);
                    Log::info("Permission accordée pour rubrique", ['rubrique_id' => $id, 'permission' => $permission->name]);
                    Log::info("Traitement de rubrique {$id}");

                    foreach ($users as $user) {
                        //$user->givePermissionTo($permission->name);
                        $user->assignRole($role->name);
                    }
                }
            }
            Log::info("Fin du traitement de consulterRubrique");

            // Parcourir et enregistrer chaque ID dans le tableau consulterRubriqueEcran
            foreach ($consulterRubriqueEcran as $id) {
                $ecran = Ecran::find($id);
                $permissionName = $ecran->permission->name;
                $permission = Permission::findByName($permissionName);
                $role->givePermissionTo($permission);
                Log::info("Permission accordée pour écran rubrique", ['ecran_id' => $id, 'permission' => $permissionName]);
                foreach ($users as $user) {
                    $user->assignRole($role->name);
                }
            }

            // Parcourir et enregistrer chaque ID dans le tableau consulterSousMenu
            foreach ($consulterSousMenu as $id) {
                // Ajouter la permission à synchroniser
                $sous_menu = SousMenu::find($id);
                $permissionName = $sous_menu->permission->name;
                $permission = Permission::findByName($permissionName);
                $role->givePermissionTo($permission);
                Log::info("Permission accordée pour sous-menu", ['sous_menu_id' => $id, 'permission' => $permissionName]);

                foreach ($users as $user) {
                    $user->assignRole($role->name);
                }
            }

            // Parcourir et enregistrer chaque ID dans le tableau consulterSousMenuEcran
            foreach ($consulterSousMenuEcran as $id) {
                $ecran = Ecran::find($id);
                $permissionName = $ecran->permission->name;
                $permission = Permission::findByName($permissionName);
                $role->givePermissionTo($permission);
                Log::info("Permission accordée pour écran sous-menu", ['ecran_id' => $id, 'permission' => $permissionName]);

                foreach ($users as $user) {
                    $user->assignRole($role->name);
                }
            }

            // Parcourir et accorder la permission pour chaque écran associé à une action dans une rubrique
            foreach ($ajouterRubriqueEcran as $id) {
                $permissionName = 'ajouter_ecran_' . $id;
                $permission = Permission::findOrCreate($permissionName);
                $role->givePermissionTo($permission->name);
                Log::info("Permission 'ajouter' accordée pour écran rubrique", ['ecran_id' => $id, 'permission' => $permissionName]);
            }

            foreach ($modifierRubriqueEcran as $id) {
                $permissionName = 'modifier_ecran_' . $id;
                $permission = Permission::findOrCreate($permissionName);
                $role->givePermissionTo($permission->name);
                Log::info("Permission 'modifier' accordée pour écran rubrique", ['ecran_id' => $id, 'permission' => $permissionName]);
            }

            foreach ($supprimerRubriqueEcran as $id) {
                $permissionName = 'supprimer_ecran_' . $id;
                $permission = Permission::findOrCreate($permissionName);
                $role->givePermissionTo($permission->name);
                Log::info("Permission 'supprimer' accordée pour écran rubrique", ['ecran_id' => $id, 'permission' => $permissionName]);
            }

            // Parcourir et accorder la permission pour chaque écran associé à une action dans un sous-menu
            foreach ($ajouterSousMenuEcran as $id) {
                $permissionName = 'ajouter_ecran_' . $id;
                $permission = Permission::findOrCreate($permissionName);
                $role->givePermissionTo($permission->name);
                Log::info("Permission 'ajouter' accordée pour écran sous-menu", ['ecran_id' => $id, 'permission' => $permissionName]);
            }

            foreach ($modifierSousMenuEcran as $id) {
                $permissionName = 'modifier_ecran_' . $id;
                $permission = Permission::findOrCreate($permissionName);
                $role->givePermissionTo($permission->name);
                Log::info("Permission 'modifier' accordée pour écran sous-menu", ['ecran_id' => $id, 'permission' => $permissionName]);
            }

            foreach ($supprimerSousMenuEcran as $id) {
                $permissionName = 'supprimer_ecran_' . $id;
                $permission = Permission::findOrCreate($permissionName);
                $role->givePermissionTo($permission->name);
                Log::info("Permission 'supprimer' accordée pour écran sous-menu", ['ecran_id' => $id, 'permission' => $permissionName]);
            }

            $permissionsConsulterSousMenuAsupprimer = SousMenu::whereNotIn('code', $consulterSousMenu)->get();
            $permissionsConsulterRubriquesAsupprimer = Rubriques::whereNotIn('code', $consulterRubrique)->get();

            foreach ($permissionsConsulterRubriquesAsupprimer as $rubrique) {
                try {
                    if ($rubrique->permission && $role->hasPermissionTo($rubrique->permission->name)) {
                        // Révoquer la permission
                        $permission = Permission::findByName($rubrique->permission->name);
                        $role->revokePermissionTo($permission);
                        Log::info("Permission révoquée pour rubrique", ['rubrique_id' => $rubrique->id, 'permission' => $rubrique->permission->name]);
                    }
                } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                    Log::error("Permission '{$rubrique->permission->name}' does not exist: " . $e->getMessage());
                }
            }

            foreach ($permissionsConsulterSousMenuAsupprimer as $sous_menu) {
                try {
                    if ($sous_menu->permission && $role->hasPermissionTo($sous_menu->permission->name)) {
                        // Révoquer la permission
                        $permission = Permission::findByName($sous_menu->permission->name);
                        $role->revokePermissionTo($permission);
                        Log::info("Permission révoquée pour sous-menu", ['sous_menu_id' => $sous_menu->id, 'permission' => $sous_menu->permission->name]);
                    }
                } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                    Log::error("Permission '{$sous_menu->permission->name}' does not exist: " . $e->getMessage());
                }
            }

            foreach ($permissionsAsupprimer as $permis) {
                try {
                    if ($role->hasPermissionTo($permis)) {
                        // Révoquer la permission
                        $permission = Permission::findByName($permis);
                        $role->revokePermissionTo($permission);
                        Log::info("Permission révoquée", ['permission' => $permis]);
                    }
                } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                    Log::error("Permission '{$permis}' does not exist: " . $e->getMessage());
                }
            }

            // Retourner une réponse JSON
            return response()->json([
                'message' => 'Données enregistrées avec succès.',
                'donnee' => $permissionsAsupprimer,
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'assignation des rôles: " . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }



public function getRolePermissions($roleId)
{
  // Récupérer le rôle
  $role = GroupeUtilisateur::findOrFail($roleId);


  // Récupérer les autorisations du rôle
  $permissions = $role->permissions()->pluck('name');
  $permissions_id = $role->permissions()->pluck('id');

  $sous_menusAcocher = SousMenu::whereIn('permission_id', $permissions_id)->get();
  $ecransAcocher = Ecran::whereIn('permission_id', $permissions_id)->get();
  $rubriquesAcocher = Rubriques::whereIn('permission_id', $permissions_id)->get();


  // Renvoyer les autorisations et les ID des rubriques à cocher au format JSON
  return response()->json([
      'permissions' => $permissions,
      'rubriquesAcocher' =>  $rubriquesAcocher,
      'sous_menusAcocher' => $sous_menusAcocher,
      'ecransAcocher' => $ecransAcocher
  ]);
}







    /******************** HABILITATIONS ******************* */

    public function habilitations(Request $request)
    {
        $views = View::all();
        $ecran = Ecran::find($request->input('ecran_id'));
        $groupes = GroupeUtilisateur::all();
        $roles = GroupeUtilisateur::all();
        return view('habilitations.habilitations', compact('groupes', 'ecran', 'roles',  'views'));
    }





    /******************** RUBRIQUES ******************* */
    public function rubriques(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $rubriques = Rubriques::all();
        $rubriquePlusGrandOrdre = Rubriques::orderBy('ordre', 'desc')->first();
        return view('habilitations.rubriques', compact('rubriques', 'ecran',  'rubriquePlusGrandOrdre'));
    }


    public function storeRubrique(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $rubrique = new Rubriques;
        $rubrique->libelle = $request->input('libelle');
        $rubrique->ordre = $request->input('ordre');
        $rubrique->class_icone = $request->input('class_icone');

        // Supprimer les accents et les caractères spéciaux du libellé
        function removeAccent($string) {
            return preg_replace('/[^\x20-\x7E]/u', '', iconv('UTF-8', 'ASCII//TRANSLIT', $string));
        }

        $libelle = $request->input('libelle');
        $libelleSansEspaces = preg_replace('/\s+/', '', $libelle);
        $permissionName = removeAccent(preg_replace('/[^A-Za-z]/', '', $libelleSansEspaces));

        // Créer ou récupérer la permission correspondante
        $permission = Permission::where('name', $permissionName)->where('guard_name', 'web')->first();

        if (!$permission) {
            $permission = Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $rubrique->permission_id = $permission->id; // Assurez-vous que l'attribut correct est utilisé pour l'ID de la permission

        $rubrique->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('rubriques.index', ['ecran_id' => $ecran_id])->with('success', 'Rubrique enregistrée avec succès.');
    }





    public function getRubrique($code)
    {
        $rubrique = Rubriques::find($code);

        if (!$rubrique) {
            return response()->json(['error' => 'Rubrique non trouvé'], 404);
        }

        return response()->json($rubrique);
    }

    public function updateRubrique(Request $request)
    {

        $rubrique = Rubriques::find($request->input('edit_code'));

        if (!$rubrique) {
            return response()->json(['error' => 'Rubrique non trouvé'], 404);
        }

        $rubrique->libelle = $request->input('edit_libelle');
        $rubrique->ordre = $request->input('edit_ordre');
        $rubrique->class_icone = $request->input('edit_class_icone');
        $rubrique->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('rubriques.index', ['ecran_id' => $ecran_id])->with('success', 'Rubrique mise à jour avec succès.');
    }

    public function deleteRubrique($code)
    {
        $rubrique = Rubriques::find($code);

        if (!$rubrique) {
            return response()->json(['error' => 'Rubrique non trouvée'], 404);
        }

        // Récupérer les sous-menus et écrans associés à la rubrique
        $sousMenus = $rubrique->sousMenus;
        $ecrans = $rubrique->sousMenus->flatMap->ecrans;

        // Supprimer les écrans associés
        foreach ($ecrans as $ecran) {
            $ecran->delete();
        }

        // Supprimer les sous-menus associés
        foreach ($sousMenus as $sousMenu) {
            $sousMenu->delete();
        }

        // Supprimer la rubrique elle-même
        $rubrique->delete();

        return response()->json(['success' => 'Rubrique supprimée avec succès']);
    }

    public function getSousMenus($rubriqueId)
    {
        $sousMenus = SousMenu::where('code_rubrique', $rubriqueId)->where('niveau', 1)
            ->with('sousSousMenusRecursive')->with('sousSousMenus')->with('ecrans')
            ->get();
        return response()->json($sousMenus);
    }


    /******************** SOUS-MENUS ******************* */

    public function sous_menus(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $sous_menus = SousMenu::all();
        $smPlusGrandOrdre = SousMenu::orderBy('ordre', 'desc')->first();
        return view('habilitations.sous_menus', compact('ecran',  'sous_menus', 'smPlusGrandOrdre'));
    }


    public function storeSous_menu(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $sous_menus = new SousMenu;
        $sous_menus->libelle = $request->input('libelle');
        $sous_menus->ordre = $request->input('ordre');
        $sous_menus->niveau = $request->input('niveau');
        $sous_menus->code_rubrique = $request->input('code_rubrique');
        $sous_menus->sous_menu_parent = $request->input('sous_menu_parent');

        // Supprimer les accents et les caractères spéciaux du libellé
        function removeAccents($string) {
            return preg_replace('/[^\x20-\x7E]/u', '', iconv('UTF-8', 'ASCII//TRANSLIT', $string));
        }

        $libelle = $request->input('libelle');
        $libelleSansEspaces = preg_replace('/\s+/', '', $libelle);
        $permissionName = removeAccents(preg_replace('/[^A-Za-z]/', '', $libelleSansEspaces));

        // Créer ou récupérer la permission correspondante
        $permission = Permission::where('name', $permissionName)->where('guard_name', 'web')->first();

        if (!$permission) {
            $permission = Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $sous_menus->permission_id = $permission->id; // Assurez-vous que l'attribut correct est utilisé pour l'ID de la permission

        $sous_menus->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('sous_menu.index', ['ecran_id' => $ecran_id])->with('success', 'Sous-menu enregistré avec succès.');
    }


    public function getSous_menu($code)
    {
        $sous_menu = SousMenu::find($code);

        if (!$sous_menu) {
            return response()->json(['error' => 'Sous-menu non trouvé'], 404);
        }

        return response()->json($sous_menu);
    }

    public function updateSous_menu(Request $request)
    {

        $sous_menu = SousMenu::find($request->input('edit_code'));

        if (!$sous_menu) {
            return response()->json(['error' => 'Sous-menu non trouvé'], 404);
        }

        $sous_menu->libelle = $request->input('edit_libelle');
        $sous_menu->ordre = $request->input('edit_ordre');
        $sous_menu->niveau = $request->input('edit_niveau');
        $sous_menu->code_rubrique = $request->input('edit_code_rubrique');
        $sous_menu->sous_menu_parent = $request->input('edit_sous_menu_parent');
        $sous_menu->save();

        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('sous_menu.index', ['ecran_id' => $ecran_id])->with('success', 'Sous-menu mis à jour avec succès.');
    }


    public function deleteSous_menu($code)
    {
        $sous_menu = SousMenu::find($code);

        if (!$sous_menu) {
            return response()->json(['error' => 'Sous-menu non trouvé'], 404);
        }

        $ecrans = $sous_menu->ecrans;

        // Supprimer les écrans associés
        foreach ($ecrans as $ecran) {
            $ecran->delete();
        }

        // Supprimer la rubrique elle-même
        $sous_menu->delete();

        return response()->json(['success' => 'Sous-menu supprimé avec succès']);
    }



    /******************** ECRANS ******************* */

    public function ecrans(Request $request)
    {
        $sous_menus = SousMenu::all();
       $ecran = Ecran::find($request->input('ecran_id'));
        $ecrans = Ecran::all();
        $permissions = Permission::orderBy('name', 'desc')->get();
        return view('habilitations.ecrans', compact('ecran','ecrans',  'sous_menus', 'permissions'));
    }



    public function storeEcran(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $ecran = new Ecran;
        $ecran->libelle = $request->input('libelle');
        $ecran->ordre = $request->input('ordre');
        $ecran->path = $request->input('path');
        $ecran->code_sous_menu = $request->input('code_sous_menu');
        $ecran->code_rubrique = $request->input('code_rubrique');

        $permissionName = 'consulter_ecran_' . $ecran->id;
        $permission = Permission::where('name', $permissionName)->where('guard_name', 'web')->first();
        if (!$permission) {
            $permission = Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
        }

        $ecran->permission_id = $permission->id;

        try {
            Permission::findOrCreate('ajouter_ecran_' . $ecran->id);
            Permission::findOrCreate('modifier_ecran_' . $ecran->id);
            Permission::findOrCreate('supprimer_ecran_' . $ecran->id);
        } catch (\Throwable $th) {
            //throw $th;
        }
        $ecran->save();

        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('ecran.index', ['ecran_id' => $ecran_id])->with('success', 'Ecran enregistré avec succès.');
    }


    public function getEcran($code)
    {
        $ecran = Ecran::find($code);

        if (!$ecran) {
            return response()->json(['error' => 'Ecran non trouvé'], 404);
        }

        return response()->json($ecran);
    }

    public function updateEcran(Request $request)
    {

        $ecran = Ecran::find($request->input('edit_code'));

        if (!$ecran) {
            return response()->json(['error' => 'Ecran non trouvé'], 404);
        }

        $ecran->libelle = $request->input('edit_libelle');
        $ecran->ordre = $request->input('edit_ordre');
        $ecran->path = $request->input('edit_path');
        $ecran->code_sous_menu = $request->input('edit_code_sous_menu');
        $ecran->code_rubrique = $request->input('edit_code_rubrique');

        $permissionName = 'consulter_ecran_' . $request->input('edit_code');
        $permission = Permission::findOrCreate($permissionName, 'web');
        $ecran->permission_id = $permission->id;
        try {
            Permission::findOrCreate('ajouter_ecran_' . $ecran->code);
            Permission::findOrCreate('modifier_ecran_' . $ecran->code);
            Permission::findOrCreate('supprimer_ecran_' . $ecran->code);
        } catch (\Throwable $th) {
            //throw $th;
        }
        $ecran->save();

        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('ecran.index', ['ecran_id' => $ecran_id])->with('success', 'Ecran mis à jour avec succès.');
    }


    public function deleteEcran($code)
    {
        $ecran = Ecran::find($code);

        if (!$ecran) {
            return response()->json(['error' => 'ecran non trouvé'], 404);
        }
        $ecran->delete();

        return response()->json(['success' => 'Ecran supprimé avec succès']);
    }
}
