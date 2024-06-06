<?php

namespace App\Http\Controllers;

use App\Models\ActionMener;
use App\Models\AgenceExecution;
use App\Models\BailleursProjet;
use App\Models\Ecran;
use App\Models\ProjetAgence;
use App\Models\ProjetEha2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDFPDF;

class AnnexeController extends Controller
{
    public function InfosPrincip(Request $request){
        $ecran = Ecran::find($request->input('ecran_id'));
        $projet = ProjetEha2::select(
            'projet_eha2.*',
            'region.libelle AS region_libelle',
            'projet_eha2.code_region',
            'region.code AS region_code',
            'domaine_intervention.libelle AS domaine_libelle',
            'district.libelle AS district_libelle',
            DB::raw('CASE
                        WHEN LENGTH(REPLACE(SUBSTRING(CodeProjet, 13, 4), \'_\', \'\')) = 3
                        THEN CONCAT(\'0\', REPLACE(SUBSTRING(CodeProjet, 13, 4), \'_\', \'\'))
                        ELSE REPLACE(SUBSTRING(CodeProjet, 13, 4), \'_\', \'\')
                    END AS sous_domaine_code'),
            'sous_domaine.libelle AS sous_domaine_libelle',
            'devise.libelle AS devise_libelle'
        )
        ->leftJoin('region', 'region.code', '=', 'projet_eha2.code_region')
        ->leftJoin('district', 'district.code', '=', 'region.code_district')
        ->leftJoin('domaine_intervention', 'domaine_intervention.code', '=', 'projet_eha2.code_domaine')
        ->leftJoin('sous_domaine', 'sous_domaine.code', '=', DB::raw('CASE
                                                                    WHEN LENGTH(REPLACE(SUBSTRING(CodeProjet, 13, 4), \'_\', \'\')) = 3
                                                                    THEN CONCAT(\'0\', REPLACE(SUBSTRING(CodeProjet, 13, 4), \'_\', \'\'))
                                                                    ELSE REPLACE(SUBSTRING(CodeProjet, 13, 4), \'_\', \'\')
                                                                END'))
        ->leftJoin('devise', 'devise.code', '=', 'projet_eha2.code_devise')
        ->get();

        return view('etatInfoPrincipal',compact('ecran', 'projet'));
    }

    public function InfosSecond(Request $request){
        $ecran = Ecran::find($request->input('ecran_id'));

        $projets = ProjetEha2::select(
            'projet_eha2.CodeProjet',
            DB::raw('GROUP_CONCAT(DISTINCT agence_execution1.nom_agence) AS Agence_execution_niveau_1'),
            DB::raw('GROUP_CONCAT(DISTINCT agence_execution2.nom_agence) AS Agence_execution_niveau_2'),
            DB::raw('GROUP_CONCAT(DISTINCT bailleurss.libelle_long SEPARATOR ", ") AS Bailleurs'),
            'personnel.nom',
            'personnel.prenom',
            'personnel.addresse',
            'personnel.telephone',
            'personnel.email'
        )
        ->join('bailleurs_projets', 'bailleurs_projets.code_projet', '=', 'projet_eha2.CodeProjet')
        ->join('bailleurss', 'bailleurss.code_bailleur', '=', 'bailleurs_projets.code_bailleur')
        ->join('projet_agence AS pa1', function($join) {
            $join->on('pa1.code_projet', '=', 'projet_eha2.CodeProjet')
                 ->where('pa1.niveau', '=', 1);
        })
        ->join('agence_execution AS agence_execution1', 'agence_execution1.code_agence_execution', '=', 'pa1.code_agence')
        ->join('projet_agence AS pa2', function($join) {
            $join->on('pa2.code_projet', '=', 'projet_eha2.CodeProjet')
                 ->where('pa2.niveau', '=', 2);
        })
        ->join('agence_execution AS agence_execution2', 'agence_execution2.code_agence_execution', '=', 'pa2.code_agence')
        ->leftJoin('projet_chef_projet', 'projet_chef_projet.code_projet', '=', 'projet_eha2.CodeProjet')
        ->leftJoin('personnel', 'personnel.code_personnel', '=', 'projet_chef_projet.code_personnel')
        ->groupBy("personnel.nom","personnel.prenom","personnel.addresse","personnel.telephone","personnel.email",'projet_eha2.CodeProjet')
        ->get();



        return view('etatInfoSecond',compact('ecran', 'projets'));
    }
    public function InfosTert(Request $request){
        $ecran = Ecran::find($request->input('ecran_id'));
        $projets = ProjetEha2::select(
            'projet_eha2.CodeProjet',
            'projet_eha2.Date_demarrage_prevue',
            'projet_eha2.date_fin_prevue',
            'debut_effective.date as Date_debut_effective',
            'fin_effective.date as Date_fin_effective',
            DB::raw('GROUP_CONCAT(DISTINCT IF(action_beneficiaires_projet.type_beneficiaire = "district", district.libelle,
                                    IF(action_beneficiaires_projet.type_beneficiaire = "departement", departement.libelle,
                                        IF(action_beneficiaires_projet.type_beneficiaire = "region", region.libelle,
                                            IF(action_beneficiaires_projet.type_beneficiaire = "sous_prefecture", sous_prefecture.libelle,
                                                IF(action_beneficiaires_projet.type_beneficiaire = "localite", localite.libelle,
                                                    IF(action_beneficiaires_projet.type_beneficiaire = "etablissement", etablissement.nom_etablissement, ""))))))) AS Beneficiaires'),
            DB::raw('GROUP_CONCAT(DISTINCT CONCAT(infrastructures.libelle, ": ", projet_action_a_mener.Quantite, " ", projet_action_a_mener.Unite_mesure) SEPARATOR ", ") AS Infrastructures_Quantites'),
            DB::raw('GROUP_CONCAT(DISTINCT statut_projet.libelle) AS Statuts')
        )
        ->leftJoin('action_beneficiaires_projet', 'action_beneficiaires_projet.CodeProjet', '=', 'projet_eha2.CodeProjet')
        ->leftJoin('district', function ($join) {
            $join->on('action_beneficiaires_projet.type_beneficiaire', '=', DB::raw("'district'"))
                ->on('action_beneficiaires_projet.beneficiaire_id', '=', 'district.code');
        })
        ->leftJoin('departement', function ($join) {
            $join->on('action_beneficiaires_projet.type_beneficiaire', '=', DB::raw("'departement'"))
                ->on('action_beneficiaires_projet.beneficiaire_id', '=', 'departement.code');
        })
        ->leftJoin('region', function ($join) {
            $join->on('action_beneficiaires_projet.type_beneficiaire', '=', DB::raw("'region'"))
                ->on('action_beneficiaires_projet.beneficiaire_id', '=', 'region.code');
        })
        ->leftJoin('sous_prefecture', function ($join) {
            $join->on('action_beneficiaires_projet.type_beneficiaire', '=', DB::raw("'sous_prefecture'"))
                ->on('action_beneficiaires_projet.beneficiaire_id', '=', 'sous_prefecture.code');
        })
        ->leftJoin('localite', function ($join) {
            $join->on('action_beneficiaires_projet.type_beneficiaire', '=', DB::raw("'localite'"))
                ->on('action_beneficiaires_projet.beneficiaire_id', '=', 'localite.code');
        })
        ->leftJoin('etablissement', function ($join) {
            $join->on('action_beneficiaires_projet.type_beneficiaire', '=', DB::raw("'etablissement'"))
                ->on('action_beneficiaires_projet.beneficiaire_id', '=', 'etablissement.code');
        })
        ->leftJoin('projet_action_a_mener', 'projet_action_a_mener.CodeProjet', '=', 'projet_eha2.CodeProjet')
        ->leftJoin('infrastructures', 'infrastructures.code', '=', 'projet_action_a_mener.Infrastrucrues')
        ->leftJoin('date_debut_effective as debut_effective', 'debut_effective.code_projet', '=', 'projet_eha2.CodeProjet')
        ->leftJoin('date_fin_effective as fin_effective', 'fin_effective.code_projet', '=', 'projet_eha2.CodeProjet')
        ->leftJoin('projet_statut_projet', 'projet_statut_projet.code_projet', '=', 'projet_eha2.CodeProjet')
        ->leftJoin('statut_projet', 'statut_projet.code', '=', 'projet_statut_projet.code_statut_projet')
        ->groupBy('projet_eha2.CodeProjet','debut_effective.date','fin_effective.date')
        ->get();
        return view('etatInfoTert',compact('ecran', 'projets'));
    }
    public function FicheCollecte(Request $request){

        $ecran = Ecran::find($request->input('ecran_id'));
        $projets = ProjetEha2::all();
        return view('ficheCollecte', compact('ecran', 'projets'));
    }

    public function FicheCollecteImprimer($code){
        // Utilisez le code pour récupérer les données de votre base de données
        $donnees = ProjetEha2::where('CodeProjet', $code)->first();

        return view('ImprimerFiche', compact('donnees'));
    }
    public function getProjectDetails(Request $request) {
        $codeProjet = $request->input('code_projet');
        $projectDetails = ProjetEha2::with([
            'actionBeneficiaires',
            'projetActionAMener',
            'dateDebutEffective',
            'dateFinEffective',
            'projetAgence.agenceExecution',
            'bailleursProjets.Bailleurss',
            'projetChefProjet.Personne',
            'domaine',
            'sous_domaine',
            'devise',
            'ministereProjet.ministere',
            'projetStatutProjet.statut'
        ])
        ->where('CodeProjet', $codeProjet)
        ->get();


        // Retourner les détails du projet au format JSON
        return response()->json($projectDetails);
    }

}
