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
        $paysSelectionne = session('pays_selectionne');
        $pay = Pays::orderBy('nom_fr_fr', 'asc')
        ->where('alpha3', $paysSelectionne)
        ->first(); // Utilisez first() pour obtenir le premier résultat
        $ecran = Ecran::find($request->input('ecran_id'));
        $pibs = Pib::where('code_pays', $pay->id)->get();
        $devises = Devise::all();
        return view('pib', compact('ecran', 'pibs', 'devises')); // Utilisez compact pour passer la variable à la vue
    }
}
