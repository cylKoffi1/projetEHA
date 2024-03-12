<?php

namespace App\Http\Controllers;

use App\Models\Ecran;
use App\Models\Pays;
use Illuminate\Http\Request;

class sigAdminController extends Controller
{
    public function carte(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        return view('sigAdmin', compact('ecran'));
    }
}
