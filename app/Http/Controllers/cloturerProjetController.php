<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\ActionBeneficiairesProjet;
use App\Models\CouvrirRegion;
use App\Models\DateEffectiveProjet;
use App\Models\Departement;
use App\Models\District;
use App\Models\Ecran;
use App\Models\Etablissement;
use App\Models\Infrastructure;
use App\Models\Localite;
use App\Models\LocalitesPays;
use App\Models\NiveauAccesDonnees;
use App\Models\NiveauAvancement;
use App\Models\Pays;
use App\Models\Projet;
use App\Models\ProjetStatut;
use App\Models\Region;
use App\Models\Sous_prefecture;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class cloturerProjetController extends Controller
{
   public function cloturer(Request $request){
    $country = session('pays_selectionne');
    $group = session('projet_selectionne');
    // Récupérer l'utilisateur actuellement connecté
    $user = auth()->user();
    // Récupérer les données de l'utilisateur à partir de son code_personnel
    $userData = User::with('acteur')->where('acteur_id', $user->acteur_id)->first();
    // Vérifier si l'utilisateur existe
    if (!$userData) {
        // Gérer le cas où l'utilisateur n'est pas trouvé
        return redirect()->route('users.users')->with('error', 'Utilisateur non trouvé.');
    }
    
    $statutProjetStatut = DB::table('projet_action_a_mener as paam')
        ->join('projet_statut as psp', 'psp.code_projet', '=', 'paam.code_projet')
        ->join('projets', 'projets.code_projet', '=', 'psp.code_projet')
        ->where('psp.type_statut', 2)
        ->select('paam.code_projet')
        ->where('projets.code_projet', 'like', $country . $group . '%')
        ->get();
    $projets = Projet::where('code_projet', 'like', $country . $group . '%')
        ->get();

        $statuts = DB::table('projet_statut')
        ->join('type_statut', 'type_statut.id', '=', 'projet_statut.type_statut')
        ->join('projets', 'projets.code_projet', '=', 'projet_statut.code_projet')
        ->where('projets.code_projet', 'like', $country . $group . '%') // FILTRAGE
        ->select('projet_statut.id', 'projets.code_projet', 'projet_statut.type_statut as codeSStatu', 'projet_statut.date_statut', 'type_statut.libelle as statut_libelle')
        ->get();

        
        $acteurs = Acteur::all();
        $localites = LocalitesPays::all();
        $infras = Infrastructure::all();
        $code_projet = $request->input('code_projet');
        $beneficiairesActions =  Projet::join('profiter', 'profiter.code_projet', '=', 'projets.code_projet')
        ->join('jouir', 'jouir.code_projet', '=', 'projets.code_projet')
        ->join('beneficier', 'beneficier.code_projet', '=', 'projets.code_projet')
        ->where('projets.code_projet', $code_projet)
        ->where('projets.code_projet', 'like', $country . $group . '%')->get();

       $ecran = Ecran::find($request->input('ecran_id'));

        return view('clotureProjet', ['ecran'=>$ecran,         
        'acteurs' => $acteurs,
        'localites'=> $localites,
        'infras' => $infras,
        'beneficiairesActions'=>$beneficiairesActions,
        'statutProjetStatut' => $statutProjetStatut, 'projets'=>$projets,'statuts' => $statuts]);
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
    public function enregistrerDateEffectiveProjet(Request $request)
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

            // Recherche ou création d'une instance du modèle DateEffectiveProjet
            $DateEffectiveProjet = DateEffectiveProjet::firstOrNew([
                'code_projet' => $request->input('code_projetModal'),
                'date' => $request->input('date_fin_Modal'),
            ]);

            // Mise à jour des valeurs
            $DateEffectiveProjet->commentaire = $request->input('commentaire_Modal');

            // Supprimer les séparateurs d'espaces dans la valeur du coût
            $cout_effectif = str_replace(' ', '', $request->input('coutEffective_Modal'));

            // Assurez-vous que le coût est un nombre décimal
            $DateEffectiveProjet->cout_effectif = is_numeric($cout_effectif) ? $cout_effectif : 0;

            $DateEffectiveProjet->devise = $request->input('devise_Modal');

            // Sauvegarde du modèle
            $DateEffectiveProjet->save();

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
                DateEffectiveProjet::create([
                    'code_projet' => $request->code_projet,
                    'date_fin_effective' => $request->date_cloture,
                    'description' =>$request->descriptionCloture,
                ]);

                // Mise à jour du statut du projet dans la table projet_statut
                // Assurez-vous de définir le bon code de statut pour la clôture du projet
                $projetStatut = ProjetStatut::where('code_projet', $request->code_projet)->first();
                $projetStatut->update(['type_statut' => '04']);

                return redirect()->back()->with('success', 'Projet clôturé. ');

            } catch (\Exception $e) {
                // En cas d'erreur, annulez la transaction
                DB::rollback();

                return redirect()->back()->with('error', 'Une erreur s\'est produite lors de la clôture du projet..');
            }
        }



}
