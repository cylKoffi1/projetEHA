<?php

namespace App\Http\Controllers;

use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\SousDomaine;
use Illuminate\Http\Request;
use App\Models\Devise;
use App\Models\Pays;
use App\Models\District;
use App\Models\Region;
use App\Models\Departement;
use App\Models\Sous_prefecture;
use App\Models\Localite;
use App\Services\FileProcService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PaysController extends Controller
{

    public function pays(Request $request)
    {
        $ecran = Ecran::find($request->input('ecran_id'));
        $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
        $code_devises = Devise::whereNotNull('libelle')
            ->where('libelle', '!=', '')
            ->whereNotNull('code_long')
            ->where('code_long', '!=', '')
            ->orderBy('libelle', 'asc')
            ->get();

        return view('parSpecifique.pays', ['code_devises' => $code_devises,'pays' => $pays, 'ecran' => $ecran,]);
    }
    public function checkPaysCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = Pays::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function storePays(Request $request){

        $pays = new Pays;

        $pays->id = time();
        $pays->code = $request->input('code');
        $pays->alpha2 = $request->input('alpha2');
        $pays->alpha3 = $request->input('alpha3');
        $pays->nom_en_gb = $request->input('nom_en_gb');
        $pays->nom_fr_fr = $request->input('nom_fr_fr');
        $pays->codeTel = $request->input('codeTel');
        $pays->code_devise = $request->input('code_devise');
        $pays->minZoom = $request->input('zoomMi');
        $pays->maxZoom = $request->input('zoomMa');
          // ✅ Armoirie -> GridFS (armoirie = ID fichiers)
          if ($request->hasFile('armoirie')) {
            $res = app(FileProcService::class)->handle([
                'owner_type'        => 'Pays',
                'owner_id'          => (int)$pays->id,
                'categorie'         => 'ARMOIRIE',
                'file'              => $request->file('armoirie'),
                'uploaded_by'       => optional($request->user())->id,
                'uniquePerCategory' => true,
            ]);
            $pays->armoirie = (string)$res['id'];
        }

        // ✅ Drapeau -> GridFS (flag = ID fichiers)
        if ($request->hasFile('flag')) {
            $res = app(FileProcService::class)->handle([
                'owner_type'        => 'Pays',
                'owner_id'          => (int)$pays->id,
                'categorie'         => 'DRAPEAU',
                'file'              => $request->file('flag'),
                'uploaded_by'       => optional($request->user())->id,
                'uniquePerCategory' => true,
            ]);
            $pays->flag = (string)$res['id'];
        }



        $pays->save();
        $ecran_id = $request->input('ecran_id');
    }
    public function update(Request $request, $id)
    {
        try {
            $pays = Pays::findOrFail($id);
            
            $data = $request->validate([
                'code' => 'required|unique:pays,code,'.$id,
                'alpha2' => 'required',
                'alpha3' => 'required',
                'nom_en_gb' => 'required',
                'nom_fr_fr' => 'required',
                'codeTel' => 'required',
                'code_devise' => 'required',
                'zoomMi' => 'nullable|numeric',
                'zoomMa' => 'nullable|numeric'
            ]);
    
            $pays->code = $data['code'];
            $pays->alpha2 = $data['alpha2'];
            $pays->alpha3 = $data['alpha3'];
            $pays->nom_en_gb = $data['nom_en_gb'];
            $pays->nom_fr_fr = $data['nom_fr_fr'];
            $pays->codeTel = $data['codeTel'];
            $pays->code_devise = $data['code_devise'];
            $pays->minZoom = $request->input('zoomMi');
            $pays->maxZoom = $request->input('zoomMa');
    
            // Gestion des fichiers
            if ($request->hasFile('armoirie')) {
                $res = app(FileProcService::class)->handle([
                    'owner_type'        => 'Pays',
                    'owner_id'          => (int)$pays->id,
                    'categorie'         => 'ARMOIRIE',
                    'file'              => $request->file('armoirie'),
                    'uploaded_by'       => optional($request->user())->id,
                    'uniquePerCategory' => true,
                ]);
                $pays->armoirie = (string)$res['id'];
            }
    
            if ($request->hasFile('flag')) {
                $res = app(FileProcService::class)->handle([
                    'owner_type'        => 'Pays',
                    'owner_id'          => (int)$pays->id,
                    'categorie'         => 'DRAPEAU',
                    'file'              => $request->file('flag'),
                    'uploaded_by'       => optional($request->user())->id,
                    'uniquePerCategory' => true,
                ]);
                $pays->flag = (string)$res['id'];
            }
    
            $pays->save();
    
            return response()->json(['success' => 'Pays mis à jour avec succès']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function edit($id)
    {
        $pays = Pays::find($id);
        if (!$pays) {
            return response()->json(['error' => 'Pays non trouvé'], 404);
        }
        return response()->json($pays);
    }
    



    public function deletePays($id)
    {
        try {
            $pays = Pays::findOrFail($id);
            $pays->delete();

            return redirect()->back()->with('success', 'Pays supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error("Erreur lors de la suppression du pays : " . $e->getMessage());
            return redirect()->back()->withErrors('Erreur lors de la suppression.');
        }
    }





    // ********************* GESTION DISTRICT *************************//
    public function district(Request $request)
    {
        $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
       $ecran = Ecran::find($request->input('ecran_id'));
        $districts = District::where('id_pays', config('app_settings.id_pays'))->get();

        return view('parSpecifique.district', ['districts' => $districts, 'pays' => $pays, 'ecran' => $ecran]);
    }
    public function checkDistrictCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = District::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function storeDistrict(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $district = new District;
        $district->code = $request->input('code');
        $district->libelle = $request->input('libelle');
        $district->id_pays = $request->input('id_pays');

        $district->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parSpecifique.district', ['ecran_id' => $ecran_id])->with('success', 'District enregistré avec succès.');
    }
    public function getDistrict($code)
    {
        $district = District::find($code);

        if (!$district) {
            return response()->json(['error' => 'District non trouvé'], 404);
        }

        return response()->json($district);
    }
    public function updateDistrict(Request $request)
    {
        $district = District::find($request->input('code'));

        if (!$district) {
            return response()->json(['error' => 'District non trouvé'], 404);
        }

        $district->libelle = $request->input('libelle');
        $district->id_pays = $request->input('id_pays');

        // Vous pouvez également valider les données ici si nécessaire

        $district->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parSpecifique.district', ['ecran_id' => $ecran_id])->with('success', 'District mis à jour avec succès.');
    }
    public function deleteDistrict($code)
    {
        $district = District::find($code);

        if (!$district) {
            return response()->json(['error' => 'District non trouvé'], 404);
        }

        $district->delete();

        return response()->json(['success' => 'District supprimé avec succès']);
    }



    // ********************* FIN GESTION DISTRICT *************************//















    // ********************* GESTION REGION *************************//

    public function region(Request $request)
    {
        $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
       $ecran = Ecran::find($request->input('ecran_id'));
        $districts = District::where('id_pays', config('app_settings.id_pays'))->get();
        $regions = Region::whereHas('district', function ($query) {
            $query->where('id_pays', config('app_settings.id_pays'));
        })->get();

        return view('parSpecifique.region', ['pays' => $pays, 'ecran' => $ecran, 'districts' => $districts, 'regions' => $regions, ]);
    }
    public function getDistricts(Request $request, $pays)
    {
        // Utilisez le modèle District pour récupérer les districts en fonction du pays
        $districts = District::where('id_pays', $pays)->get();

        // Créez un tableau d'options pour les districts
        $districtOptions = [];
        foreach ($districts as $district) {
            $districtOptions[$district->code] = $district->libelle;
        }

        return response()->json(['districts' => $districtOptions]);
    }
    public function checkRegionCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = Region::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }


    public function storeRegion(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $region = new Region;
        $region->code = $request->input('code');
        $region->libelle = $request->input('libelle');
        $region->code_district = $request->input('id_district');
        $region->save();
        $ecran_id = $request->input('ecran_id');

        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parSpecifique.region', ['ecran_id' => $ecran_id])->with('success', 'Region enregistré avec succès.');
    }
    public function getRegion($code)
    {
        $region = Region::with('district.pays')->find($code);

        return response()->json($region);
    }

    public function updateRegion(Request $request)
    {
        echo($request);
        $Region = Region::find($request->input('editCode'));

        if (!$Region) {
            return response()->json(['error' => 'District non trouvé'], 404);
        }

        $Region->libelle = $request->input('editLibelle');
        $Region->code_district = $request->input('editDistrict');

        // Vous pouvez également valider les données ici si nécessaire

        $Region->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parSpecifique.region', ['ecran_id' => $ecran_id])->with('success', 'Région mis à jour avec succès.');
    }


    public function deleteRegion($code)
    {
        $region = Region::find($code);

        if (!$region) {
            return response()->json(['error' => 'Région non trouvé'], 404);
        }

        $region->delete();

        return response()->json(['success' => 'Région supprimée avec succès']);
    }

    // ********************* FIN GESTION REGION *************************//






















// ********************* GESTION DEPARTEMENT *************************//

    public function departement(Request $request)
    {
        $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
       $ecran = Ecran::find($request->input('ecran_id'));
        $districts = District::where('id_pays', config('app_settings.id_pays'))->get();
        $regions = Region::whereHas('district', function ($query) {
            $query->where('id_pays', config('app_settings.id_pays'));
        })->get();
        $departements = Departement::whereHas('region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();


        return view('parSpecifique.departement', ['ecran' => $ecran,'departements' => $departements, 'pays' => $pays, 'districts' => $districts, 'regions' => $regions]);
    }

    public function getRegions(Request $request, $districtId)
    {
        // Utilisez le modèle District pour récupérer les districts en fonction du pays
        $regions = Region::where('code_district', $districtId)->get();

        // Créez un tableau d'options pour les districts
        $regionsOptions = [];
        foreach ($regions as $region) {
            $regionsOptions[$region->code] = $region->libelle;
        }

        return response()->json(['regions' => $regionsOptions]);
    }

    public function storeDepartement(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $departement = new Departement;
        $departement->code = $request->input('code');
        $departement->libelle = $request->input('libelle');
        $departement->code_region = $request->input('id_region');

        $departement->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parSpecifique.departement', ['ecran_id' => $ecran_id])->with('success', 'Département enregistré avec succès.');
    }


    public function checkDepartementCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = Departement::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function getDepartement($code)
    {
        $departement = Departement::with('region.district.pays')->find($code);

        return response()->json($departement);
    }


    public function updateDepartement(Request $request)
    {

        $departement = Departement::find($request->input('edit_code'));

        if (!$departement) {
            return response()->json(['error' => 'Département non trouvé'], 404);
        }

        $departement->libelle = $request->input('edit_libelle');
        $departement->code_region = $request->input('edit_id_region');

        // Vous pouvez également valider les données ici si nécessaire

        $departement->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parSpecifique.departement', ['ecran_id' => $ecran_id])->with('success', 'Département mis à jour avec succès.');
    }
    public function deleteDepartement($code)
    {
        $departement = Departement::find($code);

        if (!$departement) {
            return response()->json(['error' => 'Département non trouvé'], 404);
        }

        $departement->delete();

        return response()->json(['success' => 'Départemention supprimée avec succès']);
    }


    // ********************* FIN GESTION DEPARTEMENT *************************//















 // ********************* GESTION SOUS-PRÉFECTURE *************************//
    public function sous_prefecture(Request $request)
    {
        $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
       $ecran = Ecran::find($request->input('ecran_id'));
        $districts = District::where('id_pays', config('app_settings.id_pays'))->get();
        $regions = Region::whereHas('district', function ($query) {
            $query->where('id_pays', config('app_settings.id_pays'));
        })->get();
        $departements = Departement::whereHas('region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        $sous_prefectures = Sous_prefecture::whereHas('departement.region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        return view('parSpecifique.sous_prefecture', ['ecran'=>$ecran,'sous_prefectures' => $sous_prefectures, 'departements' => $departements, 'pays' => $pays, 'districts' => $districts, 'regions' => $regions]);
    }

    public function storeSous_prefecture(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $S_P = new Sous_prefecture;
        $S_P->code = $request->input('code');
        $S_P->libelle = $request->input('libelle');
        $S_P->code_departement = $request->input('id_departement');

        $S_P->save();

        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parSpecifique.sous_prefecture', ['ecran_id' => $ecran_id])->with('success', 'Sous_prefecture enregistré avec succès.');
    }


    public function updateSous_prefecture(Request $request)
    {
        $s_p = Sous_prefecture::find($request->input('edit_code'));

        if (!$s_p) {
            return response()->json(['error' => 'Sous-préfecture non trouvé'], 404);
        }

        $s_p->libelle = $request->input('edit_libelle');
        $s_p->code_departement = $request->input('edit_id_departement');

        // Vous pouvez également valider les données ici si nécessaire

        $s_p->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('parSpecifique.sous_prefecture', ['ecran_id' => $ecran_id])->with('success', 'Sous-préfecture  mis à jour avec succès.');
    }


    public function getSous_prefecture($code)
    {
        $s_p = Sous_prefecture::with('departement.region.district.pays')->find($code);

        return response()->json($s_p);
    }


    public function getDepartements(Request $request, $regionId)
    {
        // Utilisez le modèle District pour récupérer les districts en fonction du pays
        $departements = Departement::where('code_region', $regionId)->get();

        // Créez un tableau d'options pour les districts
        $departementsOptions = [];
        foreach ($departements as $departement) {
            $departementsOptions[$departement->code] = $departement->libelle;
        }

        return response()->json(['departements' => $departementsOptions]);
    }

    public function deleteSous_prefecture($code)
    {
        $s_p = Sous_prefecture::find($code);

        if (!$s_p) {
            return response()->json(['error' => 'Sous-préfecture non trouvéé'], 404);
        }

        $s_p->delete();

        return response()->json(['success' => 'Sous-préfecture supprimée avec succès']);
    }


    public function checkSous_prefectureCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = Sous_prefecture::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }
    // ********************* GESTION SOUS-PRÉFECTURE *************************//











// ********************* GESTION LOCALITÉS *************************//
    public function localite(Request $request)
    {

        $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
       $ecran = Ecran::find($request->input('ecran_id'));
        $districts = District::where('id_pays', config('app_settings.id_pays'))->get();
        $regions = Region::whereHas('district', function ($query) {
            $query->where('id_pays', config('app_settings.id_pays'));
        })->get();
        $departements = Departement::whereHas('region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        $sous_prefectures = Sous_prefecture::whereHas('departement.region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        $localites = Localite::whereHas('sous_prefecture.departement.region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        return view('parSpecifique.localite', ['ecran'=>$ecran,'localites' => $localites, 'sous_prefectures' => $sous_prefectures, 'departements' => $departements, 'pays' => $pays, 'districts' => $districts, 'regions' => $regions]);

    }

    public function getSous_prefectures(Request $request, $departementId)
    {
        // Utilisez le modèle District pour récupérer les districts en fonction du pays
        $sous_prefectures = Sous_prefecture::where('code_sous_prefecture', $departementId)->get();

        // Créez un tableau d'options pour les districts
        $sous_prefecturesOptions = [];
        foreach ($sous_prefectures as $sous_prefecture) {
            $sous_prefecturesOptions[$sous_prefecture->code] = $sous_prefecture->libelle;
        }

        return response()->json(['sous_prefectures' => $sous_prefecturesOptions]);
    }

    public function updateLocalite(Request $request)
    {
        $localite = Localite::find($request->input('edit_code'));

        if (!$localite) {
            return response()->json(['error' => 'Localité non trouvé'], 404);
        }

        $localite->libelle = $request->input('edit_libelle');
        $localite->code_sous_prefecture = $request->input('edit_id_sous_prefecture');

        // Vous pouvez également valider les données ici si nécessaire

        $localite->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('localite', ['ecran_id' => $ecran_id])->with('success', 'Localité mis à jour avec succès.');
    }


    public function storeLocalite(Request $request)
    {
        // Validez les données du formulaire ici (par exemple, en utilisant les règles de validation).

        // Créez un nouveau district dans la base de données.
        $localite = new Localite;
        $localite->code = $request->input('code');
        $localite->libelle = $request->input('libelle');
        $localite->code_sous_prefecture = $request->input('id_sous_prefecture');

        $localite->save();
        $ecran_id = $request->input('ecran_id');
        // Redirigez l'utilisateur vers une page de succès ou d'affichage du district.
        return redirect()->route('localite', ['ecran_id' => $ecran_id])->with('success', 'Localite enregistré avec succès.');
    }

    public function deleteLocalite($code)
    {
        $localite = Localite::find($code);

        if (!$localite) {
            return response()->json(['error' => 'Localite non trouvé'], 404);
        }

        $localite->delete();

        return response()->json(['success' => 'Localite supprimée avec succès']);
    }


    public function checkLocaliteCode(Request $request)
    {
        $code = $request->input('code');

        // Check if a district with the provided code already exists in your database
        $exists = Localite::where('code', $code)->exists();

        return response()->json(['exists' => $exists]);
    }


    public function getLocalite($code)
    {
        $localite = Localite::with('sous_prefecture.departement.region.district.pays')->find($code);

        return response()->json($localite);
    }

// ********************* FIN GESTION LOCALITÉS *************************//



}



