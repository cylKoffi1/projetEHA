<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->must_change_password) {
            return redirect()->route('password.change')->with('warning', 'Vous devez changer votre mot de passe avant d\'accéder à la plateforme.');
        }

        return $next($request);
    }
}
