<?php

namespace App\Http\Controllers;

use App\Models\ActionBeneficiairesProjet;
use App\Models\CouvrirRegion;
use App\Models\DateFinEffective;
use App\Models\Departement;
use App\Models\District;
use App\Models\Ecran;
use App\Models\Etablissement;
use App\Models\Localite;
use App\Models\NiveauAccesDonnees;
use App\Models\NiveauAvancement;
use App\Models\Pays;
use App\Models\ProjetEha2;
use App\Models\ProjetStatutProjet;
use App\Models\Region;
use App\Models\Sous_prefecture;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class cloturerProjetController extends Controller
{
   public function cloturer(Request $request){
    // Récupérer l'utilisateur actuellement connecté
    $user = auth()->user();
    // Récupérer les données de l'utilisateur à partir de son code_personnel
    $userData = User::with('personnel')->where('code_personnel', $user->code_personnel)->first();
    // Vérifier si l'utilisateur existe
    if (!$userData) {
        // Gérer le cas où l'utilisateur n'est pas trouvé
        return redirect()->route('users.users')->with('error', 'Utilisateur non trouvé.');
    }
    // Récupérer le niveau d'accès de l'utilisateur
    $niveauAcces = NiveauAccesDonnees::find($userData->niveau_acces_id);
    // Initialiser les variables pour les régions et les districts
    $regions = [];
    $districts = [];
    // Récupérer les données des régions, des districts, etc. en fonction du niveau d'accès
    if ($niveauAcces->id == 'na') {
        // Cas où le niveau d'accès est 'NA', donc afficher tous les districts et toutes les régions
        $districts = District::all();
        $regions = Region::all();
    } elseif ($niveauAcces->id == 'di') {
        // Cas où le niveau d'accès est 'DI', donc récupérer le district de l'utilisateur
        $lastCouvrirRegion = CouvrirRegion::where('code_personnel', $user->code_personnel)
            ->latest('date', 'DESC')
            ->first();

        if ($lastCouvrirRegion) {
            $codeDistrict = $lastCouvrirRegion->code_district;
            // Récupérer les régions associées à ce district
            $regions = Region::where('code_district', $codeDistrict)->get();
            $districts = District::where('code', $codeDistrict)->get();
        } else {
            // Gérer le cas où aucune entrée correspondante n'est trouvée
        }
    } elseif ($niveauAcces->id == 're') {
        $lastCouvrirRegion = CouvrirRegion::where('code_personnel', $user->code_personnel)
            ->latest('date', 'DESC')
            ->first();
        if ($lastCouvrirRegion) {
            $codeRegions = $lastCouvrirRegion->code_region;
            // Récupérer le district associé à cette région
            $codeDistrict = Region::where('code', $codeRegions)->value('code_district');
            // Récupérer la région et le district
            $regions = Region::where('code', $codeRegions)->get();
            $districts = District::where('code', $codeDistrict)->get();
        }

    } elseif ($niveauAcces->id == 'de') {

        // Cas où le niveau d'accès est 'DE', donc récupérer le département de l'utilisateur
        $codeDepartement = CouvrirRegion::where('code_personnel', $user->code_personnel)
            ->latest('date', 'DESC')
            ->first();

        // Récupérer la région et le district associés à ce département
        $codeRegion = Departement::where('code', $codeDepartement->code_departement)->first();

        $codeDistrict = Region::where('code', $codeRegion->code_region)->first();
        // Récupérer la région et le district
        $regions = Region::where('code', $codeRegion->code_region)->get();
        $districts = District::where('code', $codeDistrict->code_district)->get();
    }
    $statutProjetStatut = DB::table('projet_action_a_mener as paam')
        ->join('projet_statut_projet as psp', 'psp.code_projet', '=', 'paam.CodeProjet')
        ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'psp.code_projet')
        ->where('psp.code_statut_projet', 2)
        ->whereIn('projet_eha2.code_district', $districts->pluck('code')->toArray())
        ->whereIn('projet_eha2.code_region', $regions->pluck('code')->toArray())
        ->select('paam.CodeProjet')
        ->distinct()
        ->get();

    $projets = ProjetEha2::whereIn('code_district', $districts->pluck('code')->toArray())
        ->whereIn('code_region', $regions->pluck('code')->toArray())
        ->get();

        $statuts = DB::table('projet_statut_projet')
        ->join('statut_projet', 'statut_projet.code', '=', 'projet_statut_projet.code_statut_projet')
        ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'projet_statut_projet.code_projet')
        ->select('projet_statut_projet.code', 'projet_eha2.CodeProjet', 'projet_statut_projet.code_statut_projet as codeSStatu', 'projet_statut_projet.date', 'statut_projet.libelle as statut_libelle')
        ->get();
        $localite = Localite::all();
        $etablissements = Etablissement::all();
        $codeProjet = $request->input('code_projet');
        $districts = District::all();
        $departements = Departement::all();
        $sous_prefecture = Sous_prefecture::all();
        $regions = Region::all();
        $beneficiairesActions = ActionBeneficiairesProjet::where('CodeProjet', $codeProjet)->get();
       $ecran = Ecran::find($request->input('ecran_id'));

        return view('clotureProjet', ['ecran'=>$ecran,'sous_prefecture'=>$sous_prefecture,'regions'=>$regions,'departements'=>$departements,'etablissements'=>$etablissements,'districts'=>$districts, 'localite'=>$localite,'beneficiairesActions'=>$beneficiairesActions,'statutProjetStatut' => $statutProjetStatut, 'projets'=>$projets,'statuts' => $statuts]);
    }
    public function checkCodeProjet(Request $request)
    {
        $code_projet = $request->input('CodeProjet');
        $ordre = $request->input('Ordre');

        // Effectuer la requête de vérification
        $result = DB::table('caracteristique')
            ->where('CodeProjet', $code_projet)
            ->where('Ordre', $ordre)
            ->first();

        $exists = !empty($result);

        return response()->json(['exists' => $exists]);
    }
    public function enregistrerNiveauAvancement(Request $request)
    {
        try {
            // Validation des données du formulaire (vous pouvez personnaliser selon vos besoins)
            $request->validate([
                'code_projet_Modal' => 'required',
                'ordre_Modal' => 'required',
                'nature_travaux_Modal' => 'required',
                'quantite_reel_Modal' => 'required|numeric',
                'pourcentage_Modal' => 'required|numeric',
                'date_realisation_Modal' => 'required|date',
                'commentaire_Niveau_Modal' => 'nullable',
            ]);

            // Recherche ou création d'une instance du modèle NiveauAvancement
            $niveauAvancement = NiveauAvancement::firstOrNew([
                'code_projet' => $request->input('code_projet_Modal'),
                'numero_ordre' => $request->input('ordre_Modal'),
                'date_realisation' => $request->input('date_realisation_Modal'),
            ]);

            // Mise à jour des valeurs
            $niveauAvancement->qt_realisee = $request->input('quantite_reel_Modal');
            $niveauAvancement->niveaux = $request->input('pourcentage_Modal');
            $niveauAvancement->commentaire = $request->input('commentaire_Niveau_Modal');

            // Sauvegarde du modèle
            $niveauAvancement->save();

            // Redirection avec un message de succès
            return redirect()->back()->with('success', 'Niveau d\'avancement enregistré avec succès');
        } catch (\Exception $e) {
            // En cas d'erreur, annulez la transaction
            DB::rollback();

            dd($e->getMessage()); // Affichez le message d'erreur exact

            return redirect()->back()->with('error', 'Une erreur s\'est produite lors de l\'enregistrement du niveau d\'avancement...');
        }
    }
    public function enregistrerDateFinEffective(Request $request)
    {
        try {
            // Validation des données du formulaire (vous pouvez personnaliser selon vos besoins)
            $request->validate([
                'code_projetModal' => 'required',
                'date_fin_Modal' => 'required|date',
                'coutEffective_Modal' => 'required',
                'devise_Modal' => 'required',
                'commentaire_Modal' => 'nullable',
            ]);

            // Recherche ou création d'une instance du modèle DateFinEffective
            $dateFinEffective = DateFinEffective::firstOrNew([
                'code_projet' => $request->input('code_projetModal'),
                'date' => $request->input('date_fin_Modal'),
            ]);

            // Mise à jour des valeurs
            $dateFinEffective->commentaire = $request->input('commentaire_Modal');

            // Supprimer les séparateurs d'espaces dans la valeur du coût
            $cout_effectif = str_replace(' ', '', $request->input('coutEffective_Modal'));

            // Assurez-vous que le coût est un nombre décimal
            $dateFinEffective->cout_effectif = is_numeric($cout_effectif) ? $cout_effectif : 0;

            $dateFinEffective->devise = $request->input('devise_Modal');

            // Sauvegarde du modèle
            $dateFinEffective->save();

            // Redirection avec un message de succès
            return redirect()->back()->with('success', 'Date Fin Effective enregistrée avec succès');
        } catch (\Exception $e) {
            // En cas d'erreur, annulez la transaction
            DB::rollback();

            dd($e->getMessage()); // Affichez le message d'erreur exact

            return redirect()->back()->with('error', 'Une erreur s\'est produite lors de l\'enregistrement de la date Fin Effective...');
        }
    }



        public function getDonneesPourFormulaire(Request $request)
        {
            try{
                $codeProjet = $request->input('code_projet_Modal');
                $ordre = $request->input('ordre_Modal');

                $result = DB::table('caracteristique')
                ->select(
                    'caracteristique.CodeProjet',
                    'caracteristique.Ordre',
                    'projet_action_a_mener.Quantite',
                    'nature_traveaux.libelle',
                    'date_debut_effective.date'
                )
                ->leftJoin('caractunitetraitement', 'caractunitetraitement.CodeCaractFamille', '=', 'caracteristique.CodeCaractFamille')
                ->leftJoin('caractreservoir', 'caractreservoir.CodeCaractFamille', '=', 'caracteristique.CodeCaractFamille')
                ->leftJoin('caractreseaucollecttransport', 'caractreseaucollecttransport.CodeCaractFamille', '=', 'caracteristique.CodeCaractFamille')
                ->leftJoin('caractreseau', 'caractreseau.CodeCaractFamille', '=', 'caracteristique.CodeCaractFamille')
                ->leftJoin('caractouvragecaptageeau', 'caractouvragecaptageeau.CodeCaractFamille', '=', 'caracteristique.CodeCaractFamille')
                ->leftJoin('caractouvragecaptage', 'caractouvragecaptage.CodeCaractFamille', '=', 'caracteristique.CodeCaractFamille')
                ->leftJoin('caractouvrageassainiss', 'caractouvrageassainiss.CodeCaractFamille', '=', 'caracteristique.CodeCaractFamille')
                ->leftJoin('caractouvrage', 'caractouvrage.CodeCaractFamille', '=', 'caracteristique.CodeCaractFamille')
                ->leftJoin('caractinstrumentation', 'caractinstrumentation.CodeCaractFamille', '=', 'caracteristique.CodeCaractFamille')
                ->join('nature_traveaux', function ($join) {
                    $join->on('nature_traveaux.code', '=', DB::raw('COALESCE(
                        caractunitetraitement.natureTraveaux,
                        caractreservoir.natureTraveaux,
                        caractreseaucollecttransport.natureTraveaux,
                        caractreseau.natureTravaux,
                        caractouvragecaptageeau.natureTraveaux,
                        caractouvragecaptage.natureTraveaux,
                        caractouvrageassainiss.natureTraveaux,
                        caractouvrage.natureTraveaux,
                        caractinstrumentation.natureTraveaux
                    )'));
                })
                ->join('projet_action_a_mener', 'projet_action_a_mener.CodeProjet', '=', 'caracteristique.CodeProjet')
                ->join('date_debut_effective', 'date_debut_effective.code_projet', '=', 'caracteristique.CodeProjet')
                ->where('caracteristique.CodeProjet', '=', $codeProjet)
                ->where('caracteristique.Ordre', '=', $ordre)
                ->get();

                // Retourner les résultats sous forme de tableau JSON
                return response()->json([
                    'result' => $result,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                ]);
            }
        }
        public function cloturerProjet(Request $request)
        {
            try {
                // Validation des données
                $request->validate([
                    'code_projet' => 'required',
                    'date_cloture' => 'required|date',
                ]);

                // Enregistrement de la date de clôture dans la table
                DateFinEffective::create([
                    'code_projet' => $request->code_projet,
                    'date' => $request->date_cloture,
                ]);

                // Mise à jour du statut du projet dans la table projet_statut_projet
                // Assurez-vous de définir le bon code de statut pour la clôture du projet
                $projetStatut = ProjetStatutProjet::where('code_projet', $request->code_projet)->first();
                $projetStatut->update(['code_statut_projet' => '04']);

                return redirect()->back()->with('success', 'Projet clôturé. ');

            } catch (\Exception $e) {
                // En cas d'erreur, annulez la transaction
                DB::rollback();

                return redirect()->back()->with('error', 'Une erreur s\'est produite lors de la clôture du projet..');
            }
        }



}
