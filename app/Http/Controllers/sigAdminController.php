<?php

namespace App\Http\Controllers;

use App\Models\Bailleur;
use App\Models\Ecran;
use App\Models\Infrastructure;
use App\Models\Pays;
use App\Models\ProjetEha2;
use App\Models\ProjetStatutProjet;
use App\Models\StatutProjet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class sigAdminController extends Controller
{
    public function carte(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $bailleur = Bailleur::all();
        $statut = StatutProjet::all();
        $user = Auth::user();

        // Vérifiez si l'utilisateur a un pays assigné
        if (is_null($user->paysUser) || is_null($user->paysUser->pays)) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'êtes pas assigné à un pays.');
        }

        $countryName = $user->paysUser->pays->nom_fr_fr; // Assurez-vous que ces relations existent
        $codeAlpha3 = Pays::getAlpha3Code($countryName);
        return view('sigAdmin', compact('ecran', 'bailleur', 'statut', 'codeAlpha3'));
    }



}
