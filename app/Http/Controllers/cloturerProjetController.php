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
use Illuminate\Support\Facades\Log;

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
        ->whereIn('psp.type_statut', [2,6,5])
        ->select('paam.code_projet')
        ->where('projets.code_projet', 'like', $country . $group . '%')
        ->get();

        $lastStatuses = DB::table('projet_statut')
            ->select('code_projet', DB::raw('MAX(date_statut) as max_date'))
            ->groupBy('code_projet');

        $projets = Projet::joinSub($lastStatuses, 'last_status', function ($join) {
            $join->on('projets.code_projet', '=', 'last_status.code_projet');
        })
        ->join('projet_statut', function ($join) {
            $join->on('projet_statut.code_projet', '=', 'last_status.code_projet')
                 ->on('projet_statut.date_statut', '=', 'last_status.max_date');
        })
        ->where('projet_statut.type_statut', 7)
        ->where('projets.code_projet', 'like', $country . $group . '%')

        ->select('projets.*')
        ->with('dernierStatut')
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

        return view('projets.RealisationProjet.clotureProjet', ['ecran'=>$ecran,         
        'acteurs' => $acteurs,
        'localites'=> $localites,
        'infras' => $infras,
        'beneficiairesActions'=>$beneficiairesActions,
        'statutProjetStatut' => $statutProjetStatut, 'projets'=>$projets]);
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
            $validated = $request->validate([
                'code_projet'  => 'required|exists:projets,code_projet',
                'date_cloture' => 'required|date',
                'description'  => 'nullable|string|max:2000',
            ]);
        
            DB::beginTransaction();
            try {
                // 1) Une seule ligne par projet dans dates_effectives_projet → updateOrCreate
                DateEffectiveProjet::updateOrCreate(
                    ['code_projet' => $validated['code_projet']],
                    [
                        'date_fin_effective' => $validated['date_cloture'],
                        'description'        => $validated['description'] ?? null,
                    ]
                );
        
                // 2) Historiser le statut : on AJOUTE une nouvelle ligne (create)
                //    Si votre table a un index unique (code_projet, type_statut), supprimez-le ou ajoutez un champ pour éviter le conflit
                $statut = ProjetStatut::create([
                    'code_projet' => $validated['code_projet'],
                    'type_statut' => 7, // ou '07' selon votre FK ; adaptez si besoin
                    'date_statut' => $validated['date_cloture'],
                    'motif'       => $validated['description'] ?? null,
                ]);
                
        
                DB::commit();
        
                return response()->json([
                    'success' => true,
                    'message' => 'Projet clôturé avec succès.',
                    'statut_id' => $statut->id,
                ]);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Erreur clôture projet', [
                    'code_projet' => $request->input('code_projet'),
                    'error'       => $e->getMessage(),
                ]);
        
                return response()->json([
                    'success' => false,
                    'message' => "Une erreur s'est produite lors de la clôture du projet.",
                ], 500);
            }
        }



}
