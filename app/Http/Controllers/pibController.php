<?php
namespace App\Http\Controllers;

use App\Models\Devise;
use App\Models\Ecran;
use App\Models\Pays;
use App\Models\Pib;
use Illuminate\Http\Request;

class pibController extends Controller
{
    function pib(Request $request)
    {
        $pay = Pays::orderBy('nom_fr_fr', 'asc')->first(); // Utilisez first() pour obtenir le premier résultat
        $ecran = Ecran::find($request->input('ecran_id'));
        $pibs = Pib::all();
        $devises = Devise::all();
        return view('pib', compact('ecran', 'pibs', 'devises')); // Utilisez compact pour passer la variable à la vue
    }
}
