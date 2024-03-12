<?php

namespace App\Providers;

use App\Models\Ecran;
use App\Models\Pays;
use App\Models\Role;
use App\Models\RoleHasRubrique;
use App\Models\Rubriques;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

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
            $rubriques = Rubriques::with('sousMenus.sousSousMenusRecursive.ecrans')->with('ecrans')->with('sousMenus.ecrans')->get();

            // Obtenir les rubriques associées aux rôles de l'utilisateur connecté
            if (auth()->check()) {
                // Récupérer les rôles de l'utilisateur connecté
                $userRoles = auth()->user()->roles->pluck('id');

                // Récupérer les rubriques associées aux rôles de l'utilisateur
                $rubriqueIdsByRole = RoleHasRubrique::whereIn('role_id', $userRoles)->pluck('rubrique_id');

                // Charger les rubriques associées aux rôles de l'utilisateur
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
                ])->whereIn('code', $rubriqueIdsByRole)->orderBy('ordre')->get();
            } else {
                $rubriquesByAuthRole = collect(); // Retourner une collection vide si aucun utilisateur n'est connecté
            }

            $view->with([
                'rubriques' => $rubriques,
                'rubriquesByAuthRole' => $rubriquesByAuthRole
            ]);
        });
    }
}
