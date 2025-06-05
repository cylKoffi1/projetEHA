<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function index()
{
    $user = Auth::user();
    $acteur = $user->acteur()->with(['personnePhysique', 'pays'])->first();
    return view('utilisateur.profil', compact('user', 'acteur'));
}

public function update(Request $request)
{
    $user = Auth::user();
    $acteur = $user->acteur;

    if ($request->hasFile('photo')) {
        $file = $request->file('photo');
        $filename = 'profil_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'Data/profils/';
        $file->move(public_path($path), $filename);
        $acteur->photo = $path . $filename;
        $acteur->save();
    }

    $user->email = $request->email;
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }
    $user->save();

    if ($acteur->personnePhysique) {
        $acteur->personnePhysique->update([
            'telephone_mobile' => $request->telephone_mobile,
            'telephone_bureau' => $request->telephone_bureau,
            'adresse_postale' => $request->adresse_postale,
        ]);
    }

    return redirect()->route('profil.index')->with('success', 'Profil mis à jour avec succès.');
}

}
