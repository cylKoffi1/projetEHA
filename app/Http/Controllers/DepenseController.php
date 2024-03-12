<?php

namespace App\Http\Controllers;

use App\Models\Ecran;
use App\Models\Pays;
use Illuminate\Http\Request;
use App\Models\DepenseRealisee;

class DepenseController extends Controller
{
    public function index(Request $request)
    {
        $depenses = DepenseRealisee::all();

        $categories = $depenses->pluck('annee')->toArray();
       $ecran = Ecran::find($request->input('ecran_id'));
        $dataTotalEHA = [];
        $dataExterieurEHA = [];
        $dataTresorCIVEHA = [];

        foreach ($depenses as $depense) {
            $dataTotalEHA[] = $depense->depense_realisee_tresor + $depense->depense_realisee_ext;
            $dataExterieurEHA[] = $depense->depense_realisee_ext;
            $dataTresorCIVEHA[] = $depense->depense_realisee_tresor;
        }

        return view('depense.index', compact('categories', 'ecran', 'dataTotalEHA', 'dataExterieurEHA', 'dataTresorCIVEHA'));
    }
}
