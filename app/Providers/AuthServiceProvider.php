<?php

namespace App\Providers;

use App\Http\Controllers\WorkflowValidationController;
use App\Models\InstanceEtape;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // \App\Models\WorkflowApprobation::class => \App\Policies\WorkflowApprobationPolicy::class,
    ];

    public function boot(): void
    {
        //---------------------------------------
        // 1. Gates personnalisés (Workflow)
        //---------------------------------------
        Gate::define('approval.act', function (User $user, $siId) {
            $si = InstanceEtape::find($siId);
            if (!$si) return false;
    
            $actorCode = optional($user->acteur)->code_acteur;
    
            return app(\App\Http\Controllers\WorkflowValidationController::class)
                ->actorCanActOnStep($actorCode, $si);
        });
    
        Gate::define('approval.view', fn (User $user, $instanceId) => true);
        Gate::define('approval.start', fn (User $user) => true);
    
    
        //---------------------------------------
        // 2. Passe-droit administrateur
        //---------------------------------------
        Gate::before(function (User $user, string $ability) {
            return (method_exists($user, 'is_admin') && $user->is_admin)
                ? true
                : null;
        });
    
    
        //---------------------------------------
        // 3. Permissions Spécifiques PAR PAYS
        //---------------------------------------
        Gate::before(function (User $user, string $ability) {
    
            // Si ce n'est pas une permission Spatie, laisser passer
            if (!\Spatie\Permission\Models\Permission::where('name', $ability)->exists()) {
                return null;
            }
    
            // Pays courant
            $pays = session('pays_selectionne');
            if (!$pays) {
                return false;  // Aucun pays → aucune permission
            }
    
            // Permission Spatie
            $perm = \Spatie\Permission\Models\Permission::where('name', $ability)->first();
            if (!$perm) return false;
    
            // Vérifie dans role_permission_pays
            return DB::table('role_permission_pays')
                ->where('role_code', $user->groupeUtilisateur->code)   // Ton rôle custom
                ->where('permission_id', $perm->id)
                ->where('pays_alpha3', $pays)
                ->exists();
        });
    
    
        //---------------------------------------
        // 4. ProjetType (Spatie)
        //---------------------------------------
        Gate::define('projettype.select', function (User $user, string $typeCode) {
            return $user->can('projettype.select.' . strtoupper($typeCode));
        });
    }
    
}
