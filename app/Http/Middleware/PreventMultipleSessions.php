<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventMultipleSessions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->last_session_id !== session()->getId()) {
                Auth::logout();
                return redirect()->route('login')->withErrors(['login' => 'Vous avez été déconnecté, car votre compte est utilisé ailleurs.']);
            }
        }
    
        return $next($request);
    }
    
}
