<?php

namespace App\Http\Controllers;

use App\Models\Rubriques;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $projet = session('projet_selectionne');

        $images = [
            'BTP' => 'batiment.png',
            'EHA' => 'eauHygieneAssainissement.jpg',
            'ENE' => 'energie.png',
            'TRP' => 'transport.jpg',
            'INF' => 'informationTelecommunication.jpg',
            'AXU' => 'amenagementAxesUrbain.jpg',
        ];

        // Image par défaut si projet inconnu ou non défini
        $image = $images[$projet] ?? 'default.png';

        return view('dash', [
            'imageProjet' => asset('Data/ImageConnecte/' . $image),
            'projet'      => $projet,
        ]);
    }

    public function initSidebar(Request $request)
    {
        $rubriques = Rubriques::with('sousMenus.ecrans')->orderBy('ordre')->get();

        return response()->json(['rubriques' => $rubriques]);
    }

    public function test(){
        return view('text');
    }
}
