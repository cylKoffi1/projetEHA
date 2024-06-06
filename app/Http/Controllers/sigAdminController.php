<?php

namespace App\Http\Controllers;

use App\Models\Bailleur;
use App\Models\Ecran;
use App\Models\Pays;
use App\Models\ProjetEha2;
use App\Models\ProjetStatutProjet;
use App\Models\StatutProjet;
use Illuminate\Http\Request;

class sigAdminController extends Controller
{
    public function carte(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
       $bailleur = Bailleur::all();
       $statut = StatutProjet::all();
        return view('sigAdmin', compact('ecran', 'bailleur', 'statut'));
    }
    


}
