<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AddPersonnelAffiche
{
    public function handle($request, Closure $next)
    {
        // Définir personnelAffiche sur null par défaut
        $personnelAffiche = null;

        // Vérifiez si l'utilisateur est connecté
        if (Auth::check()) {
            // Vous pouvez ajouter ici des informations par défaut si nécessaire
            $personnelAffiche = null;
        }

        // Partager la variable avec toutes les vues
        View::share('personnelAffiche', $personnelAffiche);

        return $next($request);
    }
}

