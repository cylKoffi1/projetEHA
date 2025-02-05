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
        $devises = Devise::where('code', '71')->get();
        return view('pib', compact('ecran', 'pibs', 'devises')); // Utilisez compact pour passer la variable à la vue
    }
    public function destroy($id)
    {
        $pib = Pib::where('code',$id)->first();
        $pib->delete();

        return redirect()->back()->with('success', 'PIB supprimé avec succès.');
    }
    public function store(Request $request)
    {
        $request->validate([
            'annee' => 'required|numeric|min:1900|max:2100',
            'montant' => 'required|numeric|min:0',
            'devise' => 'required|string|max:3'
        ]);

        $paysSelectionne = session('pays_selectionne');
        $pay = Pays::where('alpha3', $paysSelectionne)->first();

        if ($pay) {
            Pib::create([
                'code_pays' => $pay->id,
                'annee' => $request->annee,
                'montant_pib' => $request->montant
            ]);

            return redirect()->back()->with('success', 'PIB ajouté avec succès.');
        }

        return redirect()->back()->with('error', 'Le pays sélectionné est invalide.');
    }

public function update(Request $request, $id)
{
    $request->validate([
        'annee' => 'required|numeric|min:1900|max:2100',
        'montant' => 'required|numeric|min:0',
        'devise' => 'required|string|max:3'
    ]);

    $pib = Pib::where('code',$id)->first();

    $pib->update([
        'annee' => $request->annee,
        'montant_pib' => $request->montant
    ]);

    return redirect()->back()->with('success', 'PIB mis à jour avec succès.');
}

}
