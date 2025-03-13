<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckProjetSelection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
{
    // Debug : Afficher les sessions actives
    Log::info('Session actuelle : ', session()->all());

    // Vérifier si l'utilisateur est connecté
    if (auth()->check()) {
        // Vérifier si 'projet_selectionne' et 'pays_selectionne' existent
        if (!session()->has('projet_selectionne') || !session()->has('pays_selectionne')) {
            Log::info("Déconnexion de l'utilisateur car projet/pays non sélectionné.");
            
            auth()->logout(); // Déconnecter l'utilisateur
            return redirect()->route('login')->withErrors([
                'error' => 'Votre session a expiré. Veuillez vous reconnecter et sélectionner un projet.'
            ]);
        }
    }

    return $next($request);
}

}
