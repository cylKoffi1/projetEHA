<?php

namespace App\Http\Controllers;

use App\Models\Bailleur;
use App\Models\DecoupageAdminPays;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\GroupeProjetPaysUser;
use App\Models\Infrastructure;
use App\Models\Pays;
use App\Models\Projet;
use App\Models\ProjetEha2;
use App\Models\ProjetStatutProjet;
use App\Models\StatutProjet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class sigAdminController extends Controller
{
    public function carte(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $user = Auth::user();

        // Vérifiez si un pays est sélectionné dans la session
        $paysSelectionne = session('pays_selectionne');
        if (!$paysSelectionne) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de pays .');
        }

        // Récupérer les informations du pays sélectionné
        $pays = Pays::where('alpha3', $paysSelectionne)->first();
        if (!$pays) {
            return redirect()->route('projets.index')->with('error', 'Le pays n\'existe pas.');
        }

        $codeAlpha3 = $pays->alpha3;
        $codeZoom = $pays->select('minZoom', 'maxZoom')
        ->where('alpha3', $codeAlpha3)
        ->first();

        // Vérifiez si un groupe projet est sélectionné dans la session
        $groupeProjetSelectionne = session('projet_selectionne');
        if (!$groupeProjetSelectionne) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de groupe projet.');
        }

        // Récupérez les informations du groupe projet sélectionné
        $groupeProjet = GroupeProjetPaysUser::where('groupe_projet_id', $groupeProjetSelectionne)
            ->with('groupeProjet')
            ->first();

        if (!$groupeProjet) {
            return redirect()->route('projets.index')->with('error', 'Le groupe projet n\'existe pas.');
        }

        $codeGroupeProjet = $groupeProjet->groupe_projet_id;

        // Récupérer les domaines associés au groupe projet
        $domainesAssocie = Domaine::where('groupe_projet_code', $codeGroupeProjet)
            ->select('code', 'libelle')
            ->get();

        // Récupérer les niveaux administratifs
        $niveau = DecoupageAdminPays::where('id_pays', $pays->id)
            ->join('decoupage_administratif', 'decoupage_admin_pays.code_decoupage', '=', 'decoupage_administratif.code_decoupage')
            ->select(
                'decoupage_admin_pays.code_decoupage',
                'decoupage_admin_pays.num_niveau_decoupage',
                'decoupage_administratif.libelle_decoupage'
            )
            ->get();
        return view('sigAdmin', compact('ecran', 'codeZoom', 'niveau', 'codeAlpha3', 'codeGroupeProjet', 'domainesAssocie'));
    }

    public function Autrecarte(Request $request){
        $ecran = Ecran::find($request->input('ecran_id'));
        $user = Auth::user();

        // Vérifiez si un pays est sélectionné dans la session
        $paysSelectionne = session('pays_selectionne');
        if (!$paysSelectionne) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de pays .');
        }

        // Récupérer les informations du pays sélectionné
        $pays = Pays::where('alpha3', $paysSelectionne)->first();
        if (!$pays) {
            return redirect()->route('projets.index')->with('error', 'Le pays n\'existe pas.');
        }

        $codeAlpha3 = $pays->alpha3;
        $codeZoom = $pays->select('minZoom', 'maxZoom')
        ->where('alpha3', $codeAlpha3)
        ->first();

        // Vérifiez si un groupe projet est sélectionné dans la session
        $groupeProjetSelectionne = session('projet_selectionne');
        if (!$groupeProjetSelectionne) {
            return redirect()->route('projets.index')->with('error', 'Vous n\'avez pas de groupe projet.');
        }

        // Récupérez les informations du groupe projet sélectionné
        $groupeProjet = GroupeProjetPaysUser::where('groupe_projet_id', $groupeProjetSelectionne)
            ->with('groupeProjet')
            ->first();

        if (!$groupeProjet) {
            return redirect()->route('projets.index')->with('error', 'Le groupe projet n\'existe pas.');
        }

        $codeGroupeProjet = $groupeProjet->groupe_projet_id;

        // Récupérer les domaines associés au groupe projet
        $domainesAssocie = Domaine::where('groupe_projet_code', $codeGroupeProjet)
            ->select('code', 'libelle')
            ->get();

        // Récupérer les niveaux administratifs
        $niveau = DecoupageAdminPays::where('id_pays', $pays->id)
            ->join('decoupage_administratif', 'decoupage_admin_pays.code_decoupage', '=', 'decoupage_administratif.code_decoupage')
            ->select(
                'decoupage_admin_pays.code_decoupage',
                'decoupage_admin_pays.num_niveau_decoupage',
                'decoupage_administratif.libelle_decoupage'
            )
            ->get();
        return view('autreCarte', compact('ecran', 'codeZoom', 'niveau', 'codeAlpha3', 'codeGroupeProjet', 'domainesAssocie'));
    }
    public function getGeoJsonWithProjectCounts(Request $request)
    {
        $user = Auth::user();
        $groupeProjetId = session('projet_selectionne');

        if (!$groupeProjetId) {
            return response()->json(['error' => 'Groupe projet non sélectionné'], 400);
        }

        $countryAlpha3 = session('pays_selectionne');

        if (!$countryAlpha3) {
            return response()->json(['error' => 'Pays non sélectionné'], 400);
        }

        // Récupérer les projets associés au groupe projet et au pays
        $projets = Projet::where('code_alpha3_pays', $countryAlpha3)
            ->whereHas('sousDomaine.domaine.groupeProjet', function ($query) use ($groupeProjetId) {
                $query->where('code', $groupeProjetId);
            })
            ->get();

        // Comptage des projets par région (par exemple `NAME_1` pour niveau 1)
        $counts = $projets->groupBy('region_name')->map->count();

        // Charger les données GeoJSON
        $geoJsonPath = storage_path("geojson/gadm41_{$countryAlpha3}_1.json"); // Exemple pour le niveau 1
        if (!file_exists($geoJsonPath)) {
            return response()->json(['error' => 'GeoJSON non trouvé'], 404);
        }

        $geoJson = json_decode(file_get_contents($geoJsonPath), true);

        // Ajouter les `projectCount` à chaque feature
        foreach ($geoJson['features'] as &$feature) {
            $regionName = $feature['properties']['NAME_1']; // Exemple pour le niveau 1
            $feature['properties']['projectCount'] = $counts[$regionName] ?? 0;
        }

        return response()->json($geoJson);
    }

}
