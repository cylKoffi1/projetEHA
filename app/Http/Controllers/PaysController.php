<?php

namespace App\Http\Controllers;

use App\Models\Ecran;
use Illuminate\Http\Request;
use App\Models\Devise;
use App\Models\Pays;
use App\Services\FileProcService;
use Illuminate\Support\Facades\Log;

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
}



