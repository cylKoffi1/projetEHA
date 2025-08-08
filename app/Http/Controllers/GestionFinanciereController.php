<?php

namespace App\Http\Controllers;

use App\Models\Ecran;
use Illuminate\Http\Request;

class GestionFinanciereController extends Controller
{
    public function indexDecaissement(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        return view('GestionFinanciere.Decaissement', compact('ecran'));
    }
}
