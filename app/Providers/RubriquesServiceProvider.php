<?php

namespace App\Providers;

use App\Models\Ecran;
use App\Models\GroupeUtilisateur;
use App\Models\Pays;
use App\Models\Rubriques;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Spatie\Permission\Models\Role;

class RubriquesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Utilisation du View Composer pour partager les rubriques avec toutes les vues
        View::composer('*', function ($view) {
            // Charger toutes les rubriques avec leurs relations
            $rubriques = Rubriques::with([
                'sousMenus.sousSousMenusRecursive.ecrans',
                'ecrans',
                'sousMenus.ecrans'
            ])->get();

            $rubriquesByAuthRole = collect(); // Collection vide par défaut





            // Vérification de l'utilisateur connecté
            if (auth()->check()) {
                $role_id = auth()->user()->groupe_utilisateur_id;// Récupérer le rôle de l'utilisateur connecté
                $userRoles = GroupeUtilisateur::where('code', $role_id)->get(); // Récupérer les rôles de l'utilisateur connecté

                if ($userRoles->isNotEmpty()) {
                    // Extraire les permissions des rôles de l'utilisateur
                    $permissions = $userRoles->flatMap(function ($role) {
                        return $role->permissions;
                    })->pluck('name')->toArray();

                    // Filtrer les rubriques basées sur les permissions associées
                    $rubriquesByAuthRole = Rubriques::with([
                        'sousMenus' => function ($query) {
                            $query->orderBy('ordre');
                        },
                        'ecrans' => function ($query) {
                            $query->orderBy('ordre');
                        },
                        'sousMenus.ecrans' => function ($query) {
                            $query->orderBy('ordre');
                        }
                    ])
                    ->whereHas('permission', function ($query) use ($permissions) {
                        $query->whereIn('name', $permissions);
                    })
                    ->orderBy('ordre')
                    ->get();
                }
            }

            // Partager les rubriques avec toutes les vues
            $view->with([
                'rubriques' => $rubriques,
                'rubriquesByAuthRole' => $rubriquesByAuthRole
            ]);
        });
    }
}
