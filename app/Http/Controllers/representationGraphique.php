<?php

namespace App\Http\Controllers;

use App\Models\ActionMener;
use App\Models\AgenceExecution;
use App\Models\Bailleur;
use App\Models\Beneficiaire;
use App\Models\Departement;
use App\Models\DepenseRealisee;
use App\Models\Devise;
use App\Models\District;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\Infrastructure;
use App\Models\Localite;
use App\Models\Ministere;
use App\Models\NatureTravaux;
use App\Models\Pays;
use App\Models\ProjetEha;
use App\Models\ProjetEha2;
use App\Models\ProjetStatutProjet;
use App\Models\Region;
use App\Models\Sous_prefecture;
use App\Models\SousDomaine;
use App\Models\TypeEtablissement;
use App\Models\UniteMesure;
use App\Models\uniteVolume;
use Illuminate\Http\Request;

class representationGraphique extends Controller
{
    function graphique(Request $request)    {
        $depenses = DepenseRealisee::all();
        $projets = ProjetEha2::all();
        $projets_prevus = ProjetStatutProjet::where("code_statut_projet","01")->get();
        $projets_en_cours = ProjetStatutProjet::where("code_statut_projet","02")->get();
        $projets_annulé = ProjetStatutProjet::where("code_statut_projet","03")->get();
        $projets_cloture = ProjetStatutProjet::where("code_statut_projet","04")->get();
        $projets_suspendus = ProjetStatutProjet::where("code_statut_projet","05")->get();
        $projets_redemarrer = ProjetStatutProjet::where("code_statut_projet","05")->get();
        $categories = $depenses->pluck('annee')->toArray();
       $ecran = Ecran::find($request->input('ecran_id'));
        $dataTotalEHA = [];
        $dataExterieurEHA = [];
        $dataTresorCIVEHA = [];
        $depensesPrevueTresor = $depenses->pluck('depense_prevue_tresor')->toArray();
        $depensesPrevueExt = $depenses->pluck('depense_prevue_ext')->toArray();
        $totalDepensesPrevue = [];
        foreach ($depenses as $depense) {
            $dataTotalEHA[] = $depense->depense_realisee_tresor + $depense->depense_realisee_ext;
            $totalDepensesPrevue[] = $depense->depense_prevue_tresor + $depense->depense_prevue_ext;
            $dataExterieurEHA[] = $depense->depense_realisee_ext;
            $dataTresorCIVEHA[] = $depense->depense_realisee_tresor;
        }
        return view('representationGraphique', compact('ecran',  'categories','projets_prevus', 'projets_en_cours', 'projets_annulé', 'projets_cloture', 'projets_suspendus', 'projets_redemarrer', 'projets', 'dataTotalEHA', 'dataExterieurEHA', 'dataTresorCIVEHA', 'depensesPrevueTresor', 'depensesPrevueExt', 'totalDepensesPrevue'));}
}
