<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\GroupeProjetPaysUser;
use Illuminate\Support\Facades\Auth;

class HeaderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Partager la variable `$pays` avec toutes les vues
        View::composer('*', function ($view) {
            $user = Auth::user();
           // Récupérer les pays associés à l'utilisateur
           if($user?->acteur_id){
           $payss = GroupeProjetPaysUser::where('user_id', $user->acteur_id)
           ->join('pays', 'groupe_projet_pays_user.pays_code', '=', 'pays.alpha3')
           ->distinct()
           ->get(['pays.alpha3', 'pays.nom_fr_fr']);
            $view->with('payss', $payss);
           }
        });
    }
}
