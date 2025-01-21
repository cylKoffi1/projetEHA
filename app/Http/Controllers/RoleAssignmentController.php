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
            set_time_limit(600); // Étendre la limite d'exécution à 10 minutes
            Log::info("Début de la fonction assignRoles.");

            // Récupération des données du formulaire
            $groupe_utilisateur_id = $request->input('role');
            Log::info("ID du groupe utilisateur sélectionné : $groupe_utilisateur_id");

            $groupe = GroupeUtilisateur::where('code', $groupe_utilisateur_id)->firstOrFail();
            Log::info("Groupe utilisateur récupéré : " . $groupe->libelle_groupe);
            $progressKey = "assign_roles_progress_{$groupe->libelle_groupe}";
            Cache::put($progressKey, 0, 300); // Initialiser la progression à 0%

            // Vérifiez si le rôle existe dans Spatie, sinon créez-le
            Role::findOrCreate($groupe->libelle_groupe, 'web');

            $users = $groupe->utilisateurs;
            Log::info("Nombre d'utilisateurs dans le groupe : " . count($users));

            // Extraire les permissions à assigner ou supprimer
            $permissionsData = [
                'consulterRubrique' => json_decode($request->input('consulterRubrique'), true) ?? [],
                'consulterRubriqueEcran' => json_decode($request->input('consulterRubriqueEcran'), true) ?? [],
                'consulterSousMenu' => json_decode($request->input('consulterSousMenu'), true) ?? [],
                'consulterSousMenuEcran' => json_decode($request->input('consulterSousMenuEcran'), true) ?? [],
                'ajouterRubriqueEcran' => json_decode($request->input('ajouterRubriqueEcran'), true) ?? [],
                'modifierRubriqueEcran' => json_decode($request->input('modifierRubriqueEcran'), true) ?? [],
                'supprimerRubriqueEcran' => json_decode($request->input('supprimerRubriqueEcran'), true) ?? [],
                'ajouterSousMenuEcran' => json_decode($request->input('ajouterSousMenuEcran'), true) ?? [],
                'modifierSousMenuEcran' => json_decode($request->input('modifierSousMenuEcran'), true) ?? [],
                'supprimerSousMenuEcran' => json_decode($request->input('supprimerSousMenuEcran'), true) ?? [],
                'permissionsAsupprimer' => json_decode($request->input('permissionsAsupprimer'), true) ?? [],
            ];

            Log::info("Permissions récupérées depuis le formulaire.", $permissionsData);

            $totalSteps = 6;
            $stepProgress = (int)(100 / $totalSteps);


            // Vérifiez si des permissions sont à traiter
            if (empty(array_filter($permissionsData))) {
                Log::warning("Aucune permission trouvée pour l'assignation.");
                return response()->json(['error' => "Aucune permission à assigner."], 400);
            }

            // **Assignation des permissions pour Rubriques et leurs Écrans**
            $this->assignPermissions($groupe, $users, $permissionsData['consulterRubrique'], 'consulter_rubrique_');
            Cache::increment($progressKey, $stepProgress);
            $this->assignPermissions($groupe, $users, $permissionsData['consulterRubriqueEcran']);
            Cache::increment($progressKey, $stepProgress);
            $this->assignPermissions($groupe, $users, $permissionsData['ajouterRubriqueEcran'], 'ajouter_ecran_');
            Cache::increment($progressKey, $stepProgress);
            $this->assignPermissions($groupe, $users, $permissionsData['modifierRubriqueEcran'], 'modifier_ecran_');
            Cache::increment($progressKey, $stepProgress);
            $this->assignPermissions($groupe, $users, $permissionsData['supprimerRubriqueEcran'], 'supprimer_ecran_');
            Cache::increment($progressKey, $stepProgress);
            Log::info("Assignation des permissions pour rubriques et écrans effectuée.");

            // **Assignation des permissions pour Sous-menus et leurs Écrans**
            $this->assignPermissions($groupe, $users, $permissionsData['consulterSousMenu'], 'consulter_sous_menu_');
            Cache::increment($progressKey, $stepProgress);
            $this->assignPermissions($groupe, $users, $permissionsData['consulterSousMenuEcran']);
            Cache::increment($progressKey, $stepProgress);
            $this->assignPermissions($groupe, $users, $permissionsData['ajouterSousMenuEcran'], 'ajouter_ecran_');
            Cache::increment($progressKey, $stepProgress);
            $this->assignPermissions($groupe, $users, $permissionsData['modifierSousMenuEcran'], 'modifier_ecran_');
            Cache::increment($progressKey, $stepProgress);
            $this->assignPermissions($groupe, $users, $permissionsData['supprimerSousMenuEcran'], 'supprimer_ecran_');
            Cache::increment($progressKey, $stepProgress);
            Log::info("Assignation des permissions pour sous-menus et écrans effectuée.");

            // Synchronisation des rubriques
            $this->synchronizeRubriques($groupe, $users, $permissionsData['consulterRubrique']);
            Cache::increment($progressKey, $stepProgress);
            Log::info("Synchronisation des rubriques effectuée.");

            // Synchronisation des sous menus
            $this->synchronizeSousMenu($groupe, $users, $permissionsData['consulterSousMenu']);
            Cache::increment($progressKey, $stepProgress);
            Log::info("Synchronisation des sous-menus effectuée.");

            // Synchronisation des écrans
            $this->synchronizeSousMenuEcran($groupe, $users, $permissionsData['consulterSousMenuEcran']);
            Cache::increment($progressKey, $stepProgress);
            Log::info("Synchronisation des sous-menus et écrans effectuée.");


            // Suppression des permissions non cochées
            if (!empty($permissionsData['permissionsAsupprimer'])) {
                $this->removePermissions($groupe, $users, $permissionsData['permissionsAsupprimer']);
                Cache::increment($progressKey, $stepProgress);
                Log::info("Suppression des permissions non cochées effectuée.");
            }

            Cache::put($progressKey, 100, 300); // Marquer comme terminé
            return response()->json(['message' => 'Données enregistrées avec succès.']);
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'assignation des rôles : " . $e->getMessage());
            Cache::forget($progressKey);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }





    private function synchronizeRubriques($groupe, $users, $rubriques)
    {
        Log::info("Début de la synchronisation des rubriques.");

        // Supprimer les rubriques non cochées
        RoleHasRubrique::where('role_id', $groupe->code)
            ->whereNotIn('rubrique_id', $rubriques)
            ->delete();
        Log::info("Rubriques non cochées supprimées.");

        foreach ($rubriques as $id) {
            // Vérifier si une association existe déjà pour ce rôle et cette rubrique
            $existingAssociation = RoleHasRubrique::where('role_id', $groupe->code)
                ->where('rubrique_id', $id)
                ->first();

            // Si aucune association n'existe, créez-en une nouvelle
            if (!$existingAssociation) {
                $roleHasRubrique = new RoleHasRubrique;
                $roleHasRubrique->rubrique_id = $id;
                $roleHasRubrique->role_id = $groupe->code;
                $roleHasRubrique->save();
                Log::info("Nouvelle association ajoutée pour rubrique", ['role_id' => $groupe->code, 'rubrique_id' => $id]);

                // Ajouter la permission à synchroniser
                $rubrique = Rubriques::find($id);
                $permission = Permission::findById($rubrique->permission_id);
                $groupe->givePermissionTo($permission->name);
                Log::info("Permission accordée pour rubrique", ['rubrique_id' => $id, 'permission' => $permission->name]);
                Log::info("Traitement de rubrique {$id}");
                foreach ($users as $user) {
                    // $user->givePermissionTo($permission->name);
                    $user->assignRole($groupe->libelle_groupe);
                }
            } else {
                // Ajouter la permission à synchroniser
                $rubrique = Rubriques::find($id);
                $permission = Permission::findById($rubrique->permission_id);
                $groupe->givePermissionTo($permission->name);
                Log::info("Permission accordée pour rubrique", ['rubrique_id' => $id, 'permission' => $permission->name]);
                Log::info("Traitement de rubrique {$id}");

                foreach ($users as $user) {
                    //$user->givePermissionTo($permission->name);
                    $user->assignRole($groupe->libelle_groupe);
                }
            }
        }
        Log::info("Fin du traitement de consulterRubrique");

        // Assigner les rubriques cochées
        foreach ($rubriques as $rubriqueId) {
            $rubrique = Rubriques::find($rubriqueId);

            if ($rubrique) {
                $permission = $rubrique->permission;
                if ($permission) {
                    $groupe->givePermissionTo($permission->name);
                    foreach ($users as $user) {
                        $user->assignRole($groupe->libelle_groupe);
                    }
                    Log::info("Rubrique synchronisée avec succès.", ['rubrique_id' => $rubriqueId]);
                }
            }
        }
    }

    private function synchronizeSousMenu($groupe, $users, $sousMenus)
    {
        foreach ($sousMenus as $sousMenuId) {
            $sousMenu = SousMenu::find($sousMenuId);
            if ($sousMenu) {
                $permission = $sousMenu->permission;
                if ($permission) {
                    $permissionN = Permission::findByName($permission->name);
                    $groupe->givePermissionTo($permissionN);
                    foreach ($users as $user) {
                        $user->assignRole($groupe->libelle_groupe);
                    }
                    Log::info("Sous-menu synchronisé avec succès.", ['sous_menu_id' => $sousMenuId]);
                }
            }
        }
    }

    private function synchronizeSousMenuEcran($groupe, $users, $ecrans)
    {
        foreach ($ecrans as $ecranId) {
            $ecran = Ecran::find($ecranId);
            if ($ecran) {
                $permission = $ecran->permission;
                if ($permission) {
                    $permissionN = Permission::findByName($permission->name);
                    $groupe->givePermissionTo($permissionN);
                    foreach ($users as $user) {
                        $user->assignRole($groupe->libelle_groupe);
                    }
                    Log::info("Écran synchronisé avec succès.", ['ecran_id' => $ecranId]);
                }
            }
        }
    }

    private function assignPermissions($groupe, $users, $items, $prefix = '')
    {
        foreach ($items as $id) {
            $permissionName = $prefix ? $prefix . $id : Ecran::find($id)->permission->name ?? null;

            if ($permissionName) {
                $permission = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
                $groupe->givePermissionTo($permission->name);

                foreach ($users as $user) {
                    $user->assignRole($groupe->libelle_groupe);
                }

                Log::info("Permission assignée.", ['permission' => $permissionName]);
            }
        }
    }

    private function removePermissions($groupe, $users, $permissionsAsupprimer)
{
    foreach ($permissionsAsupprimer as $permis) {
        try {
            if (!$groupe->hasPermissionTo($permis)) {
                Log::warning("La permission à supprimer n'est pas attribuée au groupe.", ['permission' => $permis]);
                continue;
            }

            $permission = Permission::where('name', $permis)->where('guard_name', 'web')->first();
            if ($permission) {
                $groupe->revokePermissionTo($permission);
                Log::info("Permission révoquée avec succès.", ['permission' => $permis]);
            }

            foreach ($users as $user) {
                $user->removeRole($groupe->libelle_groupe);
            }
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            Log::error("La permission '{$permis}' n'existe pas : " . $e->getMessage());
        }
    }
}


public function getRolePermissions($roleId)
{
    try {
        // Trouver le groupe utilisateur par ID ou lancer une exception si non trouvé
        $groupe = GroupeUtilisateur::findOrFail($roleId);

        // Récupérer toutes les permissions associées au groupe utilisateur
        $permissions = $groupe->permissions()->pluck('name');
        $permissionsId = $groupe->permissions()->pluck('id');

        // Récupérer les rubriques associées
        $rubriques = Rubriques::whereIn('permission_id', $permissionsId)->with(['ecrans'])->get();

        // Récupérer les sous-menus et leurs sous-sous-menus ainsi que les écrans associés
        $sousMenus = SousMenu::whereIn('permission_id', $permissionsId)
            ->with(['ecrans', 'sousSousMenusRecursive.ecrans'])
            ->get();

        // Récupérer tous les écrans directement reliés aux permissions
        $ecrans = Ecran::whereIn('permission_id', $permissionsId)->get();

        // Filtrer les permissions pour les actions spécifiques (Ajouter, Modifier, Supprimer)
        $ajouterRubriqueEcran = $permissions
            ->filter(fn($perm) => str_starts_with($perm, 'ajouter_ecran_'))
            ->map(fn($perm) => str_replace('ajouter_ecran_', '', $perm));

        $modifierRubriqueEcran = $permissions
            ->filter(fn($perm) => str_starts_with($perm, 'modifier_ecran_'))
            ->map(fn($perm) => str_replace('modifier_ecran_', '', $perm));

        $supprimerRubriqueEcran = $permissions
            ->filter(fn($perm) => str_starts_with($perm, 'supprimer_ecran_'))
            ->map(fn($perm) => str_replace('supprimer_ecran_', '', $perm));

        // Filtrer pour les sous-menus écrans
        $ajouterSousMenuEcran = $permissions
            ->filter(fn($perm) => str_starts_with($perm, 'ajouter_ecran_'))
            ->map(fn($perm) => str_replace('ajouter_ecran_', '', $perm));

        $modifierSousMenuEcran = $permissions
            ->filter(fn($perm) => str_starts_with($perm, 'modifier_ecran_'))
            ->map(fn($perm) => str_replace('modifier_ecran_', '', $perm));

        $supprimerSousMenuEcran = $permissions
            ->filter(fn($perm) => str_starts_with($perm, 'supprimer_ecran_'))
            ->map(fn($perm) => str_replace('supprimer_ecran_', '', $perm));

        $consulterSousMenuEcran = $permissions
            ->filter(fn($perm) => str_starts_with($perm, 'consulter_ecran_'))
            ->map(fn($perm) => str_replace('consulter_ecran_', '', $perm));

        $consulterRubrique = $permissions
            ->filter(fn($perm) => str_starts_with($perm, 'consulter_rubrique_'))
            ->map(fn($perm) => str_replace('consulter_rubrique_', '', $perm));

        // Retourner les données en JSON
        return response()->json([
            'permissions' => $permissions,
            'rubriques' => $rubriques,
            'sousMenus' => $sousMenus,
            'ecrans' => $ecrans,
            'ajouterRubriqueEcran' => $ajouterRubriqueEcran,
            'modifierRubriqueEcran' => $modifierRubriqueEcran,
            'supprimerRubriqueEcran' => $supprimerRubriqueEcran,
            'ajouterSousMenuEcran' => $ajouterSousMenuEcran,
            'modifierSousMenuEcran' => $modifierSousMenuEcran,
            'supprimerSousMenuEcran' => $supprimerSousMenuEcran,
            'consulterSousMenuEcran' => $consulterSousMenuEcran,
            'consulterRubrique' => $consulterRubrique,

        ]);
    } catch (\Exception $e) {
        // Logguer l'erreur et retourner une réponse JSON d'erreur
        Log::error("Erreur lors de la récupération des permissions pour le rôle: " . $e->getMessage());
        return response()->json(['error' => 'Erreur lors de la récupération des permissions.'], 500);
    }
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
