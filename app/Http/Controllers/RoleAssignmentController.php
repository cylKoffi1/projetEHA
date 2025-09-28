<?php

namespace App\Http\Controllers;

use App\Models\Ecran;
use App\Models\GroupeUtilisateur;
use App\Models\RoleHasRubrique;
use App\Models\Rubriques;
use App\Models\SousMenu;
use App\Models\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RoleAssignmentController extends Controller
{
    /* ===================== Helpers ===================== */

    private function makeBaseName(string $label): string
    {
        return Str::upper(Str::slug(Str::ascii($label), '_'));
    }

    private function makePermissionName(string $label, string|int $suffix = null): string
    {
        $base = $this->makeBaseName($label);
        return $suffix ? "{$base}_{$suffix}" : $base;
    }

    private function deletePermissionById(?int $permissionId): void
    {
        if (!$permissionId) return;
        try {
            if ($perm = Permission::find($permissionId)) {
                $perm->roles()->detach();
                $perm->users()->detach();
                $perm->delete();
            }
        } catch (\Throwable $e) {
            Log::error('deletePermissionById failed', ['permission_id' => $permissionId, 'error' => $e->getMessage()]);
        }
    }

    private function deletePermissionByName(string $name, string $guard = 'web'): void
    {
        try {
            if ($perm = Permission::where('name', $name)->where('guard_name', $guard)->first()) {
                $perm->roles()->detach();
                $perm->users()->detach();
                $perm->delete();
            }
        } catch (\Throwable $e) {
            Log::warning('deletePermissionByName failed', ['name' => $name, 'error' => $e->getMessage()]);
        }
    }

    /* ===================== Vue ===================== */

    public function habilitations(Request $request)
    {
        try {
            $views     = View::all();
            $ecran     = Ecran::find($request->input('ecran_id')) ?? Ecran::first();
            $roles     = GroupeUtilisateur::all();   // ⚠️ ton modèle "rôle" custom
            $rubriques = Rubriques::with(['ecrans', 'sousMenus.ecrans', 'sousMenus.sousSousMenusRecursive'])
                ->orderBy('ordre')->get();

            return view('habilitations.habilitations', compact('roles', 'ecran', 'views', 'rubriques'));
        } catch (\Throwable $e) {
            Log::error('Erreur chargement habilitations', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors("Impossible de charger la page d’habilitations.");
        }
    }

    /* ===================== Enregistrement (optimisé + logs) ===================== */

    public function assignRoles(Request $request)
    {
        $request->validate([
            'role'                       => 'required|string|exists:groupe_utilisateur,code',
            'consulterRubrique'          => 'nullable|array',
            'consulterRubrique.*'        => 'string',
            'consulterRubriqueEcran'     => 'nullable|array',
            'consulterRubriqueEcran.*'   => 'integer',
            'consulterSousMenu'          => 'nullable|array',
            'consulterSousMenu.*'        => 'string',
            'consulterSousMenuEcran'     => 'nullable|array',
            'consulterSousMenuEcran.*'   => 'integer',
            'ajouterRubriqueEcran'       => 'nullable|array',
            'ajouterRubriqueEcran.*'     => 'integer',
            'modifierRubriqueEcran'      => 'nullable|array',
            'modifierRubriqueEcran.*'    => 'integer',
            'supprimerRubriqueEcran'     => 'nullable|array',
            'supprimerRubriqueEcran.*'   => 'integer',
            'ajouterSousMenuEcran'       => 'nullable|array',
            'ajouterSousMenuEcran.*'     => 'integer',
            'modifierSousMenuEcran'      => 'nullable|array',
            'modifierSousMenuEcran.*'    => 'integer',
            'supprimerSousMenuEcran'     => 'nullable|array',
            'supprimerSousMenuEcran.*'   => 'integer',
            'ecran_id'                   => 'nullable',
        ]);
    
        $t0 = microtime(true);
    
        $role = GroupeUtilisateur::where('code', $request->input('role'))->firstOrFail();
    
        // ==== Normalisation des tableaux
        $rubriqueCodes    = $request->input('consulterRubrique', []);
        $sousMenuCodes    = $request->input('consulterSousMenu', []);
        $ecranConsulter   = array_values(array_unique(array_merge(
            $request->input('consulterRubriqueEcran', []),
            $request->input('consulterSousMenuEcran', [])
        )));
        $ecranAjouter     = array_values(array_unique(array_merge(
            $request->input('ajouterRubriqueEcran', []),
            $request->input('ajouterSousMenuEcran', [])
        )));
        $ecranModifier    = array_values(array_unique(array_merge(
            $request->input('modifierRubriqueEcran', []),
            $request->input('modifierSousMenuEcran', [])
        )));
        $ecranSupprimer   = array_values(array_unique(array_merge(
            $request->input('supprimerRubriqueEcran', []),
            $request->input('supprimerSousMenuEcran', [])
        )));
    
        $t1 = microtime(true);
    
        DB::transaction(function () use (
            $role, $rubriqueCodes, $sousMenuCodes,
            $ecranConsulter, $ecranAjouter, $ecranModifier, $ecranSupprimer
        ) {
            // ---------- 0) Assainissement / création des permissions manquantes (guard web) ----------
            $guard = 'web';
    
            // Rubriques → s'assurer que permission_id pointe vers une permission existante sinon créer
            if (!empty($rubriqueCodes)) {
                $rubriques = Rubriques::whereIn('code', $rubriqueCodes)->get();
                foreach ($rubriques as $r) {
                    $perm = $r->permission_id ? Permission::find($r->permission_id) : null;
                    if (!$perm) {
                        // même naming helper que dans ton code backend
                        $name = $this->makePermissionName($r->libelle, $r->code);
                        $perm = Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
                        $r->permission_id = $perm->id;
                        $r->save();
                        Log::info('[assignRoles] Permission rubrique créée/réparée', ['rubrique' => $r->code, 'perm' => $perm->name]);
                    } elseif ($perm->guard_name !== $guard) {
                        $perm->guard_name = $guard; $perm->save();
                        Log::warning('[assignRoles] Guard corrigé (rubrique)', ['rubrique' => $r->code, 'perm' => $perm->name]);
                    }
                }
            }
    
            // Sous-menus → idem
            if (!empty($sousMenuCodes)) {
                $sms = SousMenu::whereIn('code', $sousMenuCodes)->get();
                foreach ($sms as $sm) {
                    $perm = $sm->permission_id ? Permission::find($sm->permission_id) : null;
                    if (!$perm) {
                        $name = $this->makePermissionName($sm->libelle, $sm->code);
                        $perm = Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
                        $sm->permission_id = $perm->id;
                        $sm->save();
                        Log::info('[assignRoles] Permission sous-menu créée/réparée', ['sm' => $sm->code, 'perm' => $perm->name]);
                    } elseif ($perm->guard_name !== $guard) {
                        $perm->guard_name = $guard; $perm->save();
                        Log::warning('[assignRoles] Guard corrigé (sous-menu)', ['sm' => $sm->code, 'perm' => $perm->name]);
                    }
                }
            }
    
            // Écrans → s’assurer de la permission consulter + CRUD si demandés
            $ecranIdsTouches = array_values(array_unique(array_merge(
                $ecranConsulter, $ecranAjouter, $ecranModifier, $ecranSupprimer
            )));
    
            if (!empty($ecranIdsTouches)) {
                $ecrans = Ecran::whereIn('id', $ecranIdsTouches)->get();
    
                foreach ($ecrans as $e) {
                    // a) CONSULTER : permission liée à l'écran via permission_id
                    $perm = $e->permission_id ? Permission::find($e->permission_id) : null;
                    if (!$perm) {
                        $name = "consulter_ecran_{$e->id}";
                        $perm = Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
                        $e->permission_id = $perm->id; $e->save();
                        Log::info('[assignRoles] Permission consulter écran créée/réparée', ['ecran' => $e->id, 'perm' => $perm->name]);
                    } elseif ($perm->guard_name !== $guard) {
                        $perm->guard_name = $guard; $perm->save();
                        Log::warning('[assignRoles] Guard corrigé (écran consulter)', ['ecran' => $e->id, 'perm' => $perm->name]);
                    }
    
                    // b) CRUD : on s'assure de l'existence si l'action a été sélectionnée
                    if (in_array($e->id, $ecranAjouter)) {
                        Permission::firstOrCreate(['name' => "ajouter_ecran_{$e->id}", 'guard_name' => $guard]);
                    }
                    if (in_array($e->id, $ecranModifier)) {
                        Permission::firstOrCreate(['name' => "modifier_ecran_{$e->id}", 'guard_name' => $guard]);
                    }
                    if (in_array($e->id, $ecranSupprimer)) {
                        Permission::firstOrCreate(['name' => "supprimer_ecran_{$e->id}", 'guard_name' => $guard]);
                    }
                }
            }
    
            // ---------- 1) Construire la LISTE DES NOMS à accorder ----------
            $names = collect();
    
            if (!empty($rubriqueCodes)) {
                $names = $names->merge(
                    Rubriques::whereIn('code', $rubriqueCodes)
                        ->with('permission:id,name')
                        ->get()->pluck('permission.name')->filter()
                );
            }
            if (!empty($sousMenuCodes)) {
                $names = $names->merge(
                    SousMenu::whereIn('code', $sousMenuCodes)
                        ->with('permission:id,name')
                        ->get()->pluck('permission.name')->filter()
                );
            }
            if (!empty($ecranConsulter)) {
                $names = $names->merge(
                    Ecran::whereIn('id', $ecranConsulter)
                        ->with('permission:id,name')
                        ->get()->pluck('permission.name')->filter()
                );
            }
    
            // CRUD choisis
            foreach ($ecranAjouter as $id)   { $names->push("ajouter_ecran_$id"); }
            foreach ($ecranModifier as $id)  { $names->push("modifier_ecran_$id"); }
            foreach ($ecranSupprimer as $id) { $names->push("supprimer_ecran_$id"); }
    
            $names = $names->unique()->values();
    
            // ---------- 2) role_has_rubrique (pivot) ----------
            if (!is_null($rubriqueCodes)) {
                RoleHasRubrique::where('role_id', $role->getKey())
                    ->whereNotIn('rubrique_id', $rubriqueCodes)
                    ->delete();
    
                $existing = RoleHasRubrique::where('role_id', $role->getKey())->pluck('rubrique_id')->all();
                $toInsert = array_diff($rubriqueCodes, $existing);
                foreach ($toInsert as $code) {
                    RoleHasRubrique::firstOrCreate([
                        'role_id'     => $role->getKey(),
                        'rubrique_id' => $code,
                    ]);
                }
            }
    
            // ---------- 3) Sync des permissions par IDs ----------
            $permIdMap = Permission::whereIn('name', $names)
                ->where('guard_name', $guard)
                ->pluck('id', 'name');
    
            // petit log si un nom n'a pas été résolu en ID
            $unresolved = $names->reject(fn($n) => isset($permIdMap[$n]))->values();
            if ($unresolved->isNotEmpty()) {
                Log::warning('[assignRoles] Permissions introuvables au moment du sync', [
                    'names' => $unresolved,
                    'guard'=> $guard,
                ]);
            }
    
            $permIds = $names->map(fn($n) => $permIdMap[$n] ?? null)->filter()->values()->all();
    
            $role->permissions()->sync($permIds);
        });
    
        // ---------- 4) Cache Spatie
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    
        $t6 = microtime(true);
    
        Log::info('[assignRoles OK]', [
            'role' => $request->input('role'),
            'counts' => [
                'rubriques'        => count($rubriqueCodes),
                'sousMenus'        => count($sousMenuCodes),
                'ecrans_consulter' => count($ecranConsulter),
                'ecrans_ajouter'   => count($ecranAjouter),
                'ecrans_modifier'  => count($ecranModifier),
                'ecrans_supprimer' => count($ecranSupprimer),
            ],
            'timing_ms' => round(1000*($t6 - $t0)),
        ]);
    
        return redirect()->route('habilitations.index', ['ecran_id' => $request->input('ecran_id')])
            ->with('success', 'Habilitations enregistrées avec succès.');
    }
    

    /* ===================== Pré-cochage (lecture des droits d’un rôle) ===================== */

    public function getRolePermissions($roleId)
    {
        try {
            $role = GroupeUtilisateur::findOrFail($roleId);

            // IDs de permissions déjà associées au rôle
            $permNames = $role->permissions()->pluck('name')->all();

            // Regrouper par action
            $ecransConsulter = Ecran::whereHas('permission', function($q) use ($permNames){
                    $q->whereIn('name', $permNames);
                })
                ->pluck('id'); // ceux qui ont la perm "consulter" via permission_id

            $ecransAjouter   = Ecran::whereIn('id', $this->extractIdsByPrefix($permNames, 'ajouter_ecran_'))->pluck('id');
            $ecransModifier  = Ecran::whereIn('id', $this->extractIdsByPrefix($permNames, 'modifier_ecran_'))->pluck('id');
            $ecransSupprimer = Ecran::whereIn('id', $this->extractIdsByPrefix($permNames, 'supprimer_ecran_'))->pluck('id');

            // Rubriques / Sous-menus cochés (via leurs permission_id)
            $rubriques = Rubriques::whereHas('permission', function($q) use ($permNames){
                    $q->whereIn('name', $permNames);
                })->pluck('code');

            $sousMenus = SousMenu::whereHas('permission', function($q) use ($permNames){
                    $q->whereIn('name', $permNames);
                })->pluck('code');

            return response()->json([
                'rubriques'        => $rubriques,
                'sous_menus'       => $sousMenus,
                'ecrans_consulter' => $ecransConsulter,
                'ecrans_ajouter'   => $ecransAjouter,
                'ecrans_modifier'  => $ecransModifier,
                'ecrans_supprimer' => $ecransSupprimer,
            ]);
        } catch (\Throwable $e) {
            Log::error('getRolePermissions error', ['roleId' => $roleId, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Impossible de récupérer les permissions du rôle.'], 500);
        }
    }

    private function extractIdsByPrefix(array $names, string $prefix): array
    {
        $ids = [];
        $len = strlen($prefix);
        foreach ($names as $n) {
            if (strncmp($n, $prefix, $len) === 0) {
                $id = substr($n, $len);
                if (ctype_digit((string)$id)) $ids[] = (int)$id;
            }
        }
        return array_values(array_unique($ids));
    }


    
        /* ===================== Rubriques ===================== */
    
        public function rubriques(Request $request)
        {
            try {
                $ecran = Ecran::find($request->input('ecran_id'));
                $rubriques = Rubriques::orderBy('ordre')->get();
                $rubriquePlusGrandOrdre = Rubriques::orderBy('ordre', 'desc')->first();
    
                return view('habilitations.rubriques', compact('rubriques', 'ecran', 'rubriquePlusGrandOrdre'));
            } catch (\Throwable $e) {
                Log::error('Erreur chargement rubriques', ['error' => $e->getMessage()]);
                return redirect()->back()->withErrors("Impossible de charger les rubriques.");
            }
        }
        public function getRubrique($code)
        {
            $rubrique = Rubriques::find($code);

            if (!$rubrique) {
                return response()->json(['error' => 'Rubrique non trouvé'], 404);
            }

            return response()->json($rubrique);
        }
        public function storeRubrique(Request $request)
        {
            try {
                $request->validate([
                    'libelle'     => 'required|string|max:255',
                    'ordre'       => 'required|integer|min:1',
                    'class_icone' => 'nullable|string|max:255',
                    'ecran_id'    => 'required',
                ]);
    
                DB::transaction(function () use ($request) {
                    $rubrique = new Rubriques;
    
                    if ($request->filled('code')) {
                        $request->validate(['code' => 'string|max:50|unique:rubriques,code']);
                        $rubrique->code = $request->input('code');
                    } else {
                        $rubrique->code = Str::upper(Str::random(8));
                    }
    
                    $rubrique->libelle     = $request->input('libelle');
                    $rubrique->ordre       = $request->input('ordre');
                    $rubrique->class_icone = $request->input('class_icone', null);
                    $rubrique->save();
    
                    $permissionName = $this->makePermissionName($rubrique->libelle, $rubrique->code);
                    $perm = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
                    $rubrique->permission_id = $perm->id;
                    $rubrique->save();
                });
    
                return redirect()->route('rubriques.index', ['ecran_id' => $request->input('ecran_id')])
                    ->with('success', 'Rubrique enregistrée avec succès.');
            } catch (\Throwable $e) {
                Log::error('Erreur storeRubrique', [
                    'payload' => $request->all(),
                    'error'   => $e->getMessage(),
                ]);
                return redirect()->back()->withInput()->withErrors("Échec de l’enregistrement de la rubrique.");
            }
        }
    
        public function updateRubrique(Request $request)
        {
            try {
                $request->validate([
                    'edit_libelle'     => 'required|string|max:255',
                    'edit_ordre'       => 'required|integer|min:1',
                    'edit_class_icone' => 'nullable|string|max:255',
                    'ecran_id'         => 'required',
                ]);
    
                $rubrique = Rubriques::find($request->input('edit_code'));
                if (!$rubrique) {
                    return redirect()->back()->withErrors("Rubrique introuvable.");
                }
    
                $rubrique->libelle     = $request->input('edit_libelle');
                $rubrique->ordre       = $request->input('edit_ordre');
                $rubrique->class_icone = $request->input('edit_class_icone');
                $rubrique->save();
    
                return redirect()->route('rubriques.index', ['ecran_id' => $request->input('ecran_id')])
                    ->with('success', 'Rubrique mise à jour avec succès.');
            } catch (\Throwable $e) {
                Log::error('Erreur updateRubrique', [
                    'payload' => $request->all(),
                    'error'   => $e->getMessage(),
                ]);
                return redirect()->back()->withInput()->withErrors("Échec de la mise à jour de la rubrique.");
            }
        }
    
        public function deleteRubrique($code)
        {
            try {
                $rubrique = Rubriques::with(['sousMenus.ecrans'])->find($code);
                if (!$rubrique) {
                    return response()->json(['error' => 'Rubrique non trouvée'], 404);
                }
    
                DB::transaction(function () use ($rubrique) {
                    foreach ($rubrique->sousMenus as $sm) {
                        foreach ($sm->ecrans as $ecran) {
                            $id = $ecran->id;
                            foreach (["consulter_ecran_$id", "ajouter_ecran_$id", "modifier_ecran_$id", "supprimer_ecran_$id"] as $name) {
                                $this->deletePermissionByName($name);
                            }
                            $this->deletePermissionById($ecran->permission_id);
                            $ecran->delete();
                        }
                        $this->deletePermissionById($sm->permission_id);
                        $sm->delete();
                    }
                    $this->deletePermissionById($rubrique->permission_id);
                    $rubrique->delete();
                });
    
                return response()->json(['success' => 'Rubrique supprimée avec succès']);
            } catch (\Throwable $e) {
                Log::error('Erreur deleteRubrique', [
                    'code'  => $code,
                    'error' => $e->getMessage(),
                ]);
                return response()->json(['error' => "Impossible de supprimer la rubrique."], 500);
            }
        }
    
        /* ===================== Sous-menus ===================== */
    
        public function sous_menus(Request $request)
        {
            try {
                $ecran = Ecran::find($request->input('ecran_id'));
                $sous_menus = SousMenu::orderBy('ordre')->get();
                $smPlusGrandOrdre = SousMenu::orderBy('ordre', 'desc')->first();
    
                return view('habilitations.sous_menus', compact('ecran', 'sous_menus', 'smPlusGrandOrdre'));
            } catch (\Throwable $e) {
                Log::error('Erreur chargement sous-menus', ['error' => $e->getMessage()]);
                return redirect()->back()->withErrors("Impossible de charger les sous-menus.");
            }
        }
        // Renvoie un sous-menu par code (clé primaire string)
        public function getSous_menu($code)
        {
            $sm = SousMenu::find($code); // primaryKey = 'code'
            if (!$sm) {
                return response()->json(['error' => 'Sous-menu introuvable'], 404);
            }
            // Tu peux joindre des infos utiles au front
            return response()->json([
                'code'           => $sm->code,
                'libelle'        => $sm->libelle,
                'ordre'          => $sm->ordre,
                'niveau'         => $sm->niveau,
                'code_rubrique'  => $sm->code_rubrique,
                'sous_menu_parent'=> $sm->sous_menu_parent,
            ]);
        }

        // Liste les parents possibles: même rubrique, niveau < niveau courant, exclure le code courant optionnellement
        public function getSousMenusParents(Request $request)
        {
            $request->validate([
                'niveau'   => 'required|integer|min:1',
            ]);

            $rubrique = $request->query('rubrique');
            $niveau   = (int) $request->query('niveau');
            $exclude  = $request->query('exclude');

            $query = SousMenu::where('code_rubrique', $rubrique)
                ->where('niveau', '<', $niveau)
                ->orderBy('libelle');

            if ($exclude) {
                $query->where('code', '!=', $exclude);
            }

            $parents = $query->get(['code', 'libelle', 'niveau']);

            return response()->json($parents);
        }

        public function getSousMenus($rubriqueId)
        {
            $sousMenus = SousMenu::where('code_rubrique', $rubriqueId)->where('niveau', 1)
                ->with('sousSousMenusRecursive')->with('sousSousMenus')->with('ecrans')
                ->get();
            return response()->json($sousMenus);
        }
        public function storeSous_menu(Request $request)
        {
            try {
                $request->validate([
                    'libelle'           => 'required|string|max:255',
                    'ordre'             => 'required|integer|min:1',
                    'niveau'            => 'required|integer|min:0',
                    'ecran_id'          => 'required',
                ]);
    
                DB::transaction(function () use ($request) {
                    $sm = new SousMenu;
    
    
                    $sm->libelle          = $request->input('libelle');
                    $sm->ordre            = $request->input('ordre');
                    $sm->niveau           = $request->input('niveau');
                    $sm->code_rubrique    = $request->input('code_rubrique');
                    $sm->sous_menu_parent = $request->input('sous_menu_parent');
                    $sm->save();
    
                    $permissionName = $this->makePermissionName($sm->libelle, $sm->code);
                    $perm = Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
                    $sm->permission_id = $perm->id;
                    $sm->save();
                });
    
                return redirect()->route('sous_menu.index', ['ecran_id' => $request->input('ecran_id')])
                    ->with('success', 'Sous-menu enregistré avec succès.');
            } catch (\Throwable $e) {
                Log::error('Erreur storeSous_menu', [
                    'payload' => $request->all(),
                    'error'   => $e->getMessage(),
                ]);
                return redirect()->back()->withInput()->withErrors("Échec de l’enregistrement du sous-menu.");
            }
        }
    
        public function updateSous_menu(Request $request)
        {
            try {
                $request->validate([
                    'edit_code'            => 'required|exists:sous_menus,code',
                    'edit_libelle'         => 'required|string|max:255',
                    'edit_ordre'           => 'required|integer|min:1',
                    'edit_niveau'          => 'required|integer|min:0',
                    'ecran_id'             => 'required',
                ]);
    
                $sm = SousMenu::find($request->input('edit_code'));
                if (!$sm) {
                    return redirect()->back()->withErrors("Sous-menu introuvable.");
                }
    
                $sm->libelle          = $request->input('edit_libelle');
                $sm->ordre            = $request->input('edit_ordre');
                $sm->niveau           = $request->input('edit_niveau');
                $sm->code_rubrique    = $request->input('edit_code_rubrique');
                $sm->sous_menu_parent = $request->input('edit_sous_menu_parent');
                $sm->save();
    
                return redirect()->route('sous_menu.index', ['ecran_id' => $request->input('ecran_id')])
                    ->with('success', 'Sous-menu mis à jour avec succès.');
            } catch (\Throwable $e) {
                Log::error('Erreur updateSous_menu', [
                    'payload' => $request->all(),
                    'error'   => $e->getMessage(),
                ]);
                return redirect()->back()->withInput()->withErrors("Échec de la mise à jour du sous-menu.");
            }
        }
    
        public function deleteSous_menu($code)
        {
            try {
                $sm = SousMenu::with('ecrans')->find($code);
                if (!$sm) {
                    return response()->json(['error' => 'Sous-menu non trouvé'], 404);
                }
    
                DB::transaction(function () use ($sm) {
                    foreach ($sm->ecrans as $ecran) {
                        $id = $ecran->id;
                        foreach (["consulter_ecran_$id", "ajouter_ecran_$id", "modifier_ecran_$id", "supprimer_ecran_$id"] as $name) {
                            $this->deletePermissionByName($name);
                        }
                        $this->deletePermissionById($ecran->permission_id);
                        $ecran->delete();
                    }
                    $this->deletePermissionById($sm->permission_id);
                    $sm->delete();
                });
    
                return response()->json(['success' => 'Sous-menu supprimé avec succès']);
            } catch (\Throwable $e) {
                Log::error('Erreur deleteSous_menu', [
                    'code'  => $code,
                    'error' => $e->getMessage(),
                ]);
                return response()->json(['error' => "Impossible de supprimer le sous-menu."], 500);
            }
        }
    /* ===================== Écrans ===================== */

    public function ecrans(Request $request)
    {
        try {
            $ecran       = Ecran::find($request->input('ecran_id'));
            $ecrans      = Ecran::with(['rubrique', 'sousMenu', 'permission'])->orderBy('id')->get();
            $rubriques   = Rubriques::orderBy('libelle')->get();
            $sous_menus  = SousMenu::orderBy('libelle')->get();
            $permissions = Permission::orderBy('name', 'asc')->get();

            return view('habilitations.ecrans', compact('ecran', 'ecrans', 'rubriques', 'sous_menus', 'permissions'));
        } catch (\Throwable $e) {
            Log::error('Erreur chargement écrans', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors("Impossible de charger les écrans.");
        }
    }

    public function storeEcran(Request $request)
    {
        try {
            $request->validate([
                'libelle'        => 'required|string|max:255',
                'ordre'          => 'required|integer|min:1',
                'path'           => 'required|string|max:255',
                'ecran_id'       => 'required',
            ]);

            DB::transaction(function () use ($request) {
                $e = new Ecran;
                $e->libelle        = $request->input('libelle');
                $e->ordre          = $request->input('ordre');
                $e->path           = $request->input('path');
                $e->code_sous_menu = $request->input('code_sous_menu');
                $e->code_rubrique  = $request->input('code_rubrique');
                $e->save();

                $permName = "consulter_ecran_{$e->id}";
                $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
                $e->permission_id = $perm->id;
                $e->save();

                foreach (["ajouter", "modifier", "supprimer"] as $action) {
                    Permission::firstOrCreate(['name' => "{$action}_ecran_{$e->id}", 'guard_name' => 'web']);
                }
            });

            return redirect()->route('ecran.index', ['ecran_id' => $request->input('ecran_id')])
                ->with('success', 'Écran enregistré avec succès.');
        } catch (\Throwable $e) {
            Log::error('Erreur storeEcran', [
                'payload' => $request->all(),
                'error'   => $e->getMessage(),
            ]);
            return redirect()->back()->withInput()->withErrors("Échec de l’enregistrement de l’écran.");
        }
    }

    public function updateEcran(Request $request)
    {
        try {
            $request->validate([
                'edit_code'          => 'required|integer|exists:views,id',
                'libelle'       => 'required|string|max:255',
                'ordre'         => 'required|integer|min:1',
                'path'          => 'required|string|max:255',
                'ecran_id'           => 'required',
            ]);

            $e = Ecran::find($request->input('edit_code'));
            if (!$e) {
                return redirect()->back()->withErrors("Écran introuvable.");
            }

            $e->libelle        = $request->input('libelle');
            $e->ordre          = $request->input('ordre');
            $e->path           = $request->input('path');
            $e->code_sous_menu = $request->input('code_sous_menu');
            $e->code_rubrique  = $request->input('code_rubrique');
            $e->save();

            return redirect()->route('ecran.index', ['ecran_id' => $request->input('ecran_id')])
                ->with('success', 'Écran mis à jour avec succès.');
        } catch (\Throwable $e) {
            Log::error('Erreur updateEcran', [
                'payload' => $request->all(),
                'error'   => $e->getMessage(),
            ]);
            return redirect()->back()->withInput()->withErrors("Échec de la mise à jour de l’écran.");
        }
    }

    public function deleteEcran($id)
    {
        try {
            $ecran = Ecran::find($id);
            if (!$ecran) {
                return response()->json(['error' => 'Écran non trouvé'], 404);
            }

            DB::transaction(function () use ($ecran) {
                $eid = $ecran->id;
                foreach (["consulter_ecran_$eid", "ajouter_ecran_$eid", "modifier_ecran_$eid", "supprimer_ecran_$eid"] as $name) {
                    $this->deletePermissionByName($name);
                }
                $this->deletePermissionById($ecran->permission_id);
                $ecran->delete();
            });

            return response()->json(['success' => 'Écran supprimé avec succès']);
        } catch (\Throwable $e) {
            Log::error('Erreur deleteEcran', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => "Impossible de supprimer l’écran."], 500);
        }
    }
    public function getEcran($id)
    {
        $ecran = Ecran::find($id);
        if (!$ecran) return response()->json(['error' => 'Écran non trouvé'], 404);
        return response()->json($ecran);
    }
    public function bulkDeleteEcrans(Request $request)
    {
        try {
            $request->validate([
                'ids'      => 'required|array|min:1',
                'ids.*'    => 'integer|exists:views,id',
                'ecran_id' => 'required',
            ]);

            DB::transaction(function () use ($request) {
                $ids = $request->input('ids');

                $ecrans = Ecran::whereIn('id', $ids)->get();
                foreach ($ecrans as $ecran) {
                    $eid = $ecran->id;
                    foreach (["consulter_ecran_$eid", "ajouter_ecran_$eid", "modifier_ecran_$eid", "supprimer_ecran_$eid"] as $name) {
                        $this->deletePermissionByName($name);
                    }
                    $this->deletePermissionById($ecran->permission_id);
                    $ecran->delete();
                }
            });

            return redirect()->route('ecran.index', ['ecran_id' => $request->input('ecran_id')])
                ->with('success', 'Écrans supprimés avec succès.');
        } catch (\Throwable $e) {
            Log::error('Erreur bulkDeleteEcrans', [
                'payload' => $request->all(),
                'error'   => $e->getMessage(),
            ]);
            return redirect()->back()->withErrors("Échec de la suppression multiple des écrans.");
        }
    }  
}  
    /*public function assignRoles(Request $request)
    {
        $r  = $request;
        // Récupérer les données du formulaire
        $role_id = $request->input('role');
        $consulterRubrique = $r->input('consulterRubrique');
        $consulterRubriqueEcran = $r->input('consulterRubriqueEcran');
        $consulterSousMenu = $r->input('consulterSousMenu');
        $consulterSousMenuEcran = $r->input('consulterSousMenuEcran');

        $ajouterRubriqueEcran = $r->input('ajouterRubriqueEcran');
        $modifierRubriqueEcran = $r->input('modifierRubriqueEcran');
        $supprimerRubriqueEcran = $r->input('supprimerRubriqueEcran');

        $ajouterSousMenuEcran = $r->input('ajouterSousMenuEcran');
        $modifierSousMenuEcran = $r->input('modifierSousMenuEcran');
        $supprimerSousMenuEcran = $r->input('supprimerSousMenuEcran');


        $permissionsAsupprimer = $r->input('permissionsAsupprimer');


        // Récupérer le rôle
        $role = GroupeUtilisateur::where('code', $role_id)->firstOrFail();
        LOG::info('Role: ' . $role);
        $users = $role->users;

        // Supprimer les associations non cochées
        RoleHasRubrique::where('role_id', $role_id)
        ->whereNotIn('rubrique_id', $consulterRubrique ?? [])
        ->delete();
        // Supprimer les permissions des rubriques non cochées
        $rubriquesToRevoke = Rubriques::whereNotIn('code', $consulterRubrique ?? [])->get();
        foreach ($rubriquesToRevoke as $rubrique) {
            if ($rubrique->permission) {
                try {
                    $role->revokePermissionTo($rubrique->permission->name);
                } catch (\Exception $e) {
                    Log::error("Error revoking permission: " . $e->getMessage());
                }
            }
        }
        // 2. Gestion des sous-menus
        $sousMenusToRevoke = SousMenu::whereNotIn('code', $consulterSousMenu)->get();
        foreach ($sousMenusToRevoke as $sousMenu) {
            if ($sousMenu->permission) {
                $role->revokePermissionTo($sousMenu->permission->name);
            }
        }

        // 3. Gestion des écrans
        //$allEcransIds = array_merge($consulterRubriqueEcran, $consulterSousMenuEcran);
        $allEcransIds = array_values(array_unique(array_merge($consulterRubriqueEcran, $consulterSousMenuEcran)));
        Log::info('AssignRoles', ['role' => $r->role, 'counts' => [
        'rubriques'=>count($consulterRubrique), 'sousMenus'=>count($consulterSousMenu), 'ecrans'=>count($allEcransIds)
        ]]);
        $ecransToRevoke = Ecran::whereNotIn('id', $allEcransIds)->get();
        foreach ($ecransToRevoke as $ecran) {
            if ($ecran->permission) {
                $role->revokePermissionTo($ecran->permission->name);
            }
        }

        // 4. Gestion des permissions d'actions (ajouter/modifier/supprimer)
        $permissionsAsupprimer = $r->input('permissionsAsupprimer', '[]');
        foreach ($permissionsAsupprimer as $permissionClass) {
            // Convertir le nom de classe en nom de permission
            // Ex: "ajouter_ecran_1" devient "ajouter_ecran_1"
            $parts = explode('_', $permissionClass);
            $action = $parts[0] ?? '';
            $type = $parts[1] ?? '';
            $id = $parts[2] ?? '';
            
            if ($action && $type && $id) {
                $permissionName = "{$action}_{$type}_{$id}";
                try {
                    $role->revokePermissionTo($permissionName);
                } catch (\Exception $e) {
                    Log::error("Failed to revoke permission: {$permissionName}");
                }
            }
        }
        // Parcourir et enregistrer chaque ID dans le tableau consulterRubrique
        foreach ($consulterRubrique as $id) {
            // Vérifier si une association existe déjà pour ce rôle et cette rubrique
            /*$existingAssociation = RoleHasRubrique::where('role_id', $role_id)
                ->where('rubrique_id', $id)
                ->get();

            // Si aucune association n'existe, créez-en une nouvelle
            if (!$existingAssociation) {
                $roleHasRubrique = new RoleHasRubrique;
                $roleHasRubrique->rubrique_id = $id;
                $roleHasRubrique->role_id = $role_id;
                $roleHasRubrique->save();

                // Ajouter la permission à synchroniser
                $rubrique = Rubriques::find($id);
                $permission = Permission::findById($rubrique->permission_id);
                $role->givePermissionTo($permission->name);

                foreach ($users as $user) {
                    // $user->givePermissionTo($permission->name);
                    $user->assignRole($role->name);
                }
            } else {
                // Ajouter la permission à synchroniser
                $rubrique = Rubriques::find($id);
                $permission = Permission::findById($rubrique->permission_id);
                $role->givePermissionTo($permission->name);

                foreach ($users as $user) {
                    //$user->givePermissionTo($permission->name);
                    $user->assignRole($role->name);
                }
            }*/
            /*
            $exists = RoleHasRubrique::where('role_id',$role_id)->where('rubrique_id',$id)->exists();
            if (!$exists) {
            RoleHasRubrique::create(['role_id'=>$role_id,'rubrique_id'=>$id]);
            }

        }


        // Parcourir et enregistrer chaque ID dans le tableau consulterRubriqueEcran
        foreach ($consulterRubriqueEcran as $id) {
            $ecran = Ecran::find($id);
            $permissionName = $ecran->permission->name;
            $permission = Permission::findByName($permissionName);
            if ($permission) {
                $role->givePermissionTo($permission->name);
            } else {
                Log::error("La permission '{$permissionName}' n'existe pas dans la base de données.");
            }
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
            if ($permission) {
                $role->givePermissionTo($permission->name);
            } else {
                Log::error("La permission '{$permissionName}' n'existe pas dans la base de données.");
            }
            foreach ($users as $user) {
                $user->assignRole($role->name);
            }
        }

        // Parcourir et enregistrer chaque ID dans le tableau consulterSousMenuEcran
        foreach ($consulterSousMenuEcran as $id) {
            $ecran = Ecran::find($id);
            $permissionName = $ecran->permission->name;
            $permission = Permission::findByName($permissionName);
            if ($permission) {
                $role->givePermissionTo($permission->name);
            } else {
                Log::error("La permission '{$permissionName}' n'existe pas dans la base de données.");
            }
            foreach ($users as $user) {
                $user->assignRole($role->name);
            }
        }



        // Parcourir et accorder la permission pour chaque écran associé à une action dans une rubrique
        foreach ($ajouterRubriqueEcran as $id) {
            $permissionName = 'ajouter_ecran_' . $id;
            $permission = Permission::findOrCreate($permissionName);
            $role->givePermissionTo($permission->name);
        }

        foreach ($modifierRubriqueEcran as $id) {
            $permissionName = 'modifier_ecran_' . $id;
            $permission = Permission::findOrCreate($permissionName);
            $role->givePermissionTo($permission->name);
        }

        foreach ($supprimerRubriqueEcran as $id) {
            $permissionName = 'supprimer_ecran_' . $id;
            $permission = Permission::findOrCreate($permissionName);
            $role->givePermissionTo($permission->name);
        }

        // Parcourir et accorder la permission pour chaque écran associé à une action dans un sous-menu
        foreach ($ajouterSousMenuEcran as $id) {
            $permissionName = 'ajouter_ecran_' . $id;
            $permission = Permission::findOrCreate($permissionName);
            $role->givePermissionTo($permission->name);
        }

        foreach ($modifierSousMenuEcran as $id) {
            $permissionName = 'modifier_ecran_' . $id;
            $permission = Permission::findOrCreate($permissionName);
            $role->givePermissionTo($permission->name);
        }

        foreach ($supprimerSousMenuEcran as $id) {
            $permissionName = 'supprimer_ecran_' . $id;
            $permission = Permission::findOrCreate($permissionName);
            $role->givePermissionTo($permission->name);
        }

        $permissionsConsulterSousMenuAsupprimer = SousMenu::whereNotIn('code', $consulterSousMenu)->get();
        $permissionsConsulterRubriquesAsupprimer = Rubriques::whereNotIn('code', $consulterRubrique)->get();

        foreach ($permissionsConsulterRubriquesAsupprimer as $rubrique) {
            try {
                if ($rubrique->permission && $role->hasPermissionTo($rubrique->permission->name)) {
                    // Révoquer la permission
                    $permission = Permission::findByName($rubrique->permission->name);
                    $role->revokePermissionTo($permission);
                }
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                // Gérer l'erreur si la permission n'existe pas
                // Vous pouvez journaliser l'erreur ou effectuer toute autre action nécessaire
                // Par exemple :
                Log::error("Permission '{$permissionName}' does not exist: " . $e->getMessage());
            }
        }

        foreach ($permissionsConsulterSousMenuAsupprimer as $sous_menu) {
            try {
                if ($sous_menu->permission && $role->hasPermissionTo($sous_menu->permission->name)) {
                    // Révoquer la permission
                    $permission = Permission::findByName($sous_menu->permission->name);
                    $role->revokePermissionTo($permission);
                }
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                // Gérer l'erreur si la permission n'existe pas
                // Vous pouvez journaliser l'erreur ou effectuer toute autre action nécessaire
                // Par exemple :
                Log::error("Permission '{$permission->name}' does not exist: " . $e->getMessage());
            }
        }

        // Gestion des permissions à supprimer
        foreach ($permissionsAsupprimer as $permis) {
            try {
                // Extraire le nom de la permission à partir du nom de classe
                $permissionName = str_replace('_ecran_', ' ecran ', $permis);
                $permissionName = str_replace('ajouter', 'ajouter_ecran_', $permissionName);
                $permissionName = str_replace('modifier', 'modifier_ecran_', $permissionName);
                $permissionName = str_replace('supprimer', 'supprimer_ecran_', $permissionName);
                $permissionName = str_replace(' ', '', $permissionName);
                
                if ($role->hasPermissionTo($permissionName)) {
                    $role->revokePermissionTo($permissionName);
                }
            } catch (\Exception $e) {
                Log::error("Error revoking permission '{$permissionName}': " . $e->getMessage());
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();


        // Retourner une réponse JSON
        return response()->json([
            'message' => 'Données enregistrées avec succès.',
            'donnee' => $permissionsAsupprimer,
        ]);
    } public function getRolePermissions($roleId)
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

    












    /******************** RUBRIQUES ******************* */
   /* public function rubriques(Request $request)
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

    /*public function sous_menus(Request $request)
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

    /*public function ecrans(Request $request)
    {
        $ecran       = Ecran::find($request->input('ecran_id'));
        $ecrans      = Ecran::with(['rubrique','sousMenu','permission'])->orderBy('id')->get();
        $rubriques   = Rubriques::orderBy('libelle')->get();           // ← manquait dans ton code
        $sous_menus  = SousMenu::orderBy('libelle')->get();
        $permissions = Permission::orderBy('name', 'asc')->get();

        return view('habilitations.ecrans', compact('ecran','ecrans','rubriques','sous_menus','permissions'));
    }

    public function storeEcran(Request $request)
    {
        $request->validate([
            'libelle'        => 'required|string|max:255',
            'ordre'          => 'required|integer|min:1',
            'path'           => 'required|string|max:255',
            'code_sous_menu' => 'required|string',
            'code_rubrique'  => 'required|string',
            'ecran_id'       => 'required',
        ]);

        // 1) Créer l’écran pour obtenir son ID
        $e = new Ecran;
        $e->libelle        = $request->input('libelle');
        $e->ordre          = $request->input('ordre');
        $e->path           = $request->input('path');
        $e->code_sous_menu = $request->input('code_sous_menu');
        $e->code_rubrique  = $request->input('code_rubrique');
        $e->save();

        // 2) Créer / associer les permissions basées sur l’ID
        $permissionName = 'consulter_ecran_' . $e->id;
        $permission = Permission::where('name', $permissionName)->where('guard_name','web')->first();
        if (!$permission) {
            $permission = Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
        }
        $e->permission_id = $permission->id;
        $e->save();

        try {
            Permission::findOrCreate('ajouter_ecran_'  . $e->id, 'web');
            Permission::findOrCreate('modifier_ecran_' . $e->id, 'web');
            Permission::findOrCreate('supprimer_ecran_'. $e->id, 'web');
        } catch (\Throwable $th) {
            // silencieux
        }

        return redirect()->route('ecran.index', ['ecran_id' => $request->input('ecran_id')])
            ->with('success', 'Écran enregistré avec succès.');
    }

    public function getEcran($id)
    {
        $ecran = Ecran::find($id);
        if (!$ecran) return response()->json(['error' => 'Écran non trouvé'], 404);
        return response()->json($ecran);
    }

    public function updateEcran(Request $request)
    {
        $request->validate([
            'edit_code'       => 'required|integer|exists:ecrans,id',
            'edit_libelle'    => 'required|string|max:255',
            'edit_ordre'      => 'required|integer|min:1',
            'edit_path'       => 'required|string|max:255',
            'edit_code_sous_menu' => 'required|string',
            'edit_code_rubrique'  => 'required|string',
            'ecran_id'        => 'required',
        ]);

        $e = Ecran::find($request->input('edit_code'));
        if (!$e) return response()->json(['error'=>'Écran non trouvé'],404);

        $e->libelle        = $request->input('edit_libelle');
        $e->ordre          = $request->input('edit_ordre');
        $e->path           = $request->input('edit_path');
        $e->code_sous_menu = $request->input('edit_code_sous_menu');
        $e->code_rubrique  = $request->input('edit_code_rubrique');

        // Permission “consulter_ecran_{id}”
        $permissionName = 'consulter_ecran_' . $e->id;
        $permission = Permission::where('name', $permissionName)->where('guard_name','web')->first();
        if (!$permission) {
            $permission = Permission::create(['name' => $permissionName, 'guard_name'=>'web']);
        }
        $e->permission_id = $permission->id;

        try {
            Permission::findOrCreate('ajouter_ecran_'  . $e->id, 'web');
            Permission::findOrCreate('modifier_ecran_' . $e->id, 'web');
            Permission::findOrCreate('supprimer_ecran_'. $e->id, 'web');
        } catch (\Throwable $th) {}

        $e->save();

        return redirect()->route('ecran.index', ['ecran_id' => $request->input('ecran_id')])
            ->with('success', 'Écran mis à jour avec succès.');
    }

    public function deleteEcran($id)
    {
        $ecran = Ecran::find($id);
        if (!$ecran) return response()->json(['error' => 'Écran non trouvé'], 404);
        $ecran->delete();
        return response()->json(['success' => 'Écran supprimé avec succès']);
    }

    /** Suppression multiple */
   /* public function bulkDeleteEcrans(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return redirect()->back()->with('success', 'Aucun élément sélectionné.');
        }
        Ecran::whereIn('id', $ids)->delete();
        return redirect()->route('ecran.index', ['ecran_id' => $request->input('ecran_id')])
            ->with('success', count($ids) . ' écran(s) supprimé(s) avec succès.');
    }*/
