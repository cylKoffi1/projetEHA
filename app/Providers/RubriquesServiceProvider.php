<?php

namespace App\Providers;

use App\Models\GroupeUtilisateur;
use App\Models\Rubriques;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class RubriquesServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('*', function ($view) {

            $rubriques = Rubriques::with([
                'sousMenus.sousSousMenusRecursive.ecrans',
                'ecrans',
                'sousMenus.ecrans'
            ])->orderBy('ordre')->get();

            $rubriquesByAuthRole = collect();

            if (auth()->check()) {

                $pays = session('pays_selectionne');
                $roleCode = auth()->user()->groupeUtilisateur->code;

                if ($pays && $roleCode) {

                    // 🔥 Récupération correcte des permissions PAR PAYS
                    $permissions = DB::table('role_permission_pays AS rpp')
                        ->join('permissions AS p', 'p.id', '=', 'rpp.permission_id')
                        ->where('rpp.role_code', $roleCode)
                        ->where('rpp.pays_alpha3', $pays)
                        ->pluck('p.name')
                        ->toArray();

                    // 🔥 Filtre des rubriques visibles : en fonction de leur permission
                    $rubriquesByAuthRole = Rubriques::with([
                            'sousMenus' => function ($q) {
                                $q->orderBy('ordre');
                            },
                            'ecrans' => function ($q) {
                                $q->orderBy('ordre');
                            },
                            'sousMenus.ecrans' => function ($q) {
                                $q->orderBy('ordre');
                            }
                        ])
                        ->whereHas('permission', function ($q) use ($permissions) {
                            $q->whereIn('name', $permissions);
                        })
                        ->orderBy('ordre')
                        ->get();
                }
            }

            $view->with([
                'rubriques'            => $rubriques,
                'rubriquesByAuthRole'  => $rubriquesByAuthRole
            ]);
        });
    }
}
