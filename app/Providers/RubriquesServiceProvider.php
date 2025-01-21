<?php

namespace App\Providers;

use App\Models\Ecran;
use App\Models\Pays;
use App\Models\RoleHasRubrique;
use App\Models\Rubriques;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
                $userGroup = auth()->user()->groupeUtilisateur;

                if ($userGroup) {
                    // Obtenir les rubriques associées aux rôles de l'utilisateur connecté
                    $rubriqueIdsByRole = RoleHasRubrique::where('role_id', $userGroup->code)
                        ->pluck('rubrique_id');

                    // Charger les rubriques autorisées par rôle avec leurs relations et triées par ordre
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
                    ->whereIn('code', $rubriqueIdsByRole)
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
