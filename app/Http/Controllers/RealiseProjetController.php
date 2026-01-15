<?php

namespace App\Http\Controllers;

use App\Models\Acteur;
use App\Models\ActionBeneficiairesProjet;
use App\Models\AvancementProjet;
use App\Models\Beneficier;
use App\Models\DateEffectiveProjet;
use App\Models\DateFinEffective;
use App\Models\FamilleInfrastructure;
use App\Models\Infrastructure;
use App\Models\NatureTravaux;
use App\Models\NiveauAvancement;
use App\Models\Ecran;
use App\Models\Jouir;
use App\Models\LocalitesPays;
use App\Models\Profiter;
use App\Models\Projet;
use App\Models\ProjetActionAMener;
use App\Models\ProjetInfrastructure;
use App\Models\ProjetStatut;
use App\Models\TypeCaracteristique;
use App\Models\User;
use App\Models\ValeurCaracteristique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class RealiseProjetController extends Controller
{

    public function recupererCaracteristiques(Request $request)
    {
        $code = $request->code_projet;
        $ordre = $request->NumOrdre;

        $action = ProjetActionAMener::with([
            'caracteristiques.caracteristique',
            'caracteristiques.unite',
            'infrastructure'
        ])
        ->where('code_projet', $code)
        ->where('Num_ordre', $ordre)
        ->first();

        if (!$action || !$action->caracteristiques) {
            return response()->json(['caracteristiques' => [], 'infra' => null], 200);
        }

        $caracs = $action->caracteristiques->map(function ($valeur) {
            return [
                'libelle' => $valeur->caracteristique?->libelleCaracteristique ?? '—',
                'valeur' => $valeur->valeur,
                'unite' => $valeur->unite?->symbole ?? '',
            ];
        });

        return response()->json([
            'caracteristiques' => $caracs,
            'infra' => $action->infrastructure?->libelle ?? 'Inconnue'
        ]);
    }



    public function PramatreRealise(Request $request)
    {
        $infrastructures = Infrastructure::all();
        $familleInfras = FamilleInfrastructure::all();
        $natureTravaux = NatureTravaux::all();
        $ecran = Ecran::find($request->input('ecran_id'));
        // Récupérer les paramètres de la requête
        $code_projet = $request->input('code_projet');
        $codeActionMenerProjet = $request->input('codeActionMenerProjet');
        $infrastructureData = ProjetActionAMener::where('code_projet', $code_projet)
            ->where('projet_action_a_mener.code', $codeActionMenerProjet)
            ->join('infrastructures', 'projet_action_a_mener.Infrastrucrues_Id', '=', 'infrastructures.code')
            ->select('infrastructures.code_famille_infrastructure')
            ->first();
        $types = TypeCaracteristique::all();
        $codeFamilleInfrastructure = $infrastructureData?->code_famille_infrastructure;

        // Récupérer la date enregistrée pour le code_projet
        $code_projet2 = $request->input('code_projet2');
        $dateEnregistree = DateEffectiveProjet::where('code_projet', $code_projet2)->value('updated_at');
        $TypeCaracteristiques = TypeCaracteristique::all();
        // Transmettre les données à la vue
        return view('parametreRealise', ['ecran' => $ecran,
           'types' => $types,
            'code_projet2'=>$code_projet2,
            'codeFamilleInfrastructure'=>$codeFamilleInfrastructure,
            'natureTravaux' => $natureTravaux,
            'infrastructure' => $infrastructures,
            'famille' => $familleInfras,
            'infrastructures' => $infrastructures,
            'dateEnregistree' => $dateEnregistree,
            'TypeCaracteristiques' => $TypeCaracteristiques,
        ]);
    }

    public function getFamillesByProjet(Request $request)
    {
        $codeProjet = $request->input('codeProjet');

        // 1. Trouver le projet par code
        $projet = Projet::where('code_projet', $codeProjet)->first();
        if (!$projet) {
            return response()->json(['error' => 'Projet non trouvé'], 404);
        }

        $codeSDomaine = $projet->code_sous_domaine; // récupère le code sous-domaine du projet
        $codeGroupeProjet = session('projet_selectionne'); // récupère la session

        if (!$codeGroupeProjet) {
            return response()->json(['error' => 'Groupe projet non trouvé en session'], 400);
        }

        // 2. Récupérer les familles correspondant au sous-domaine et au groupe de projet
        $familles = FamilleInfrastructure::where('code_sdomaine', $codeSDomaine)
                    ->where('code_groupe_projet', $codeGroupeProjet)
                    ->get();

        return response()->json([
            'familles' => $familles
        ]);
    }

    public function getInfrastructuresByProjet(Request $request)
    {
        $codeProjet = $request->codeProjet;
    //dd($codeProjet);
        // Charger les infrastructures associées au projet
        $projetsInfra = ProjetInfrastructure::where('code_projet', $codeProjet)
            ->with(['infra' => function($q) {
                $q->with(['familleInfrastructure', 'valeursCaracteristiques.caracteristique', 'valeursCaracteristiques.unite']);
            }])
            ->get();

        $result = [];

        foreach ($projetsInfra as $projetInfra) {
            $infra = $projetInfra->infra;
            if ($infra) {
                $caracs = [];
                foreach ($infra->valeursCaracteristiques as $valeur) {
                    $caracs[] = [
                        'type' => $valeur->caracteristique->typeCaracteristique->libelleTypeCaracteristique ?? '',
                        'libelle' => $valeur->caracteristique->libelleCaracteristique ?? '',
                        'unite' => $valeur->unite->libelleUnite ?? '',
                        'valeur' => $valeur->valeur,
                    ];
                }
                $result[] = [
                    'nom_infrastructure' => $infra->libelle,
                    'famille' => $infra->familleInfrastructure->libelleFamille ?? '',
                    'caracteristiques' => $caracs,
                ];
            }
        }

        return response()->json(['infrastructures' => $result]);
    }
    public function getFamilleInfrastructure(Request $request)
    {
        $infrastructureCode = $request->infrastructureCode;
        $infrastructureInput = $request->infrastructureInput;

        // Rechercher l'infrastructure par code OU par nom
        $infra = Infrastructure::where('code', $infrastructureCode)
                    ->orWhere('libelle', 'LIKE', '%' . $infrastructureInput . '%')
                    ->with('familleInfrastructure')
                    ->first();

        if ($infra && $infra->familleInfrastructure) {
            return response()->json([
                'familleInfrastructure' => $infra->familleInfrastructure->libelleFamille
            ]);
        } else {
            return response()->json([
                'familleInfrastructure' => null
            ]);
        }
    }
    public function VoirListe(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
       $country = session('pays_selectionne');
       $group = session('projet_selectionne');

        $statutProjetStatut = ProjetStatut::all();
        $natureTravaux = NatureTravaux::all();
        $projetsNonTrouves = DB::table('projets')
            ->leftJoin('projet_action_a_mener', 'projets.code_projet', '=', 'projet_action_a_mener.code_projet')
            ->whereNull('projet_action_a_mener.code_projet')
            ->pluck('projets.code_projet')
            ->toArray();

        $code_projet = ProjetStatut::where('type_statut', 1)
        ->where('code_projet', 'like', $country . $group . '%')
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
        ->where('projet_statut.type_statut', 1)
        ->where('projets.code_projet', 'like', $country . $group . '%')
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                  ->from('projet_statut as ps2')
                  ->whereColumn('ps2.code_projet', 'projets.code_projet')
                  ->where('ps2.type_statut', '!=', 1);
        })
        ->select('projets.*')
        ->with('dernierStatut')
        ->get();


        $localites = LocalitesPays::where('id_pays', session('pays_selectionne'))->get();
        $infras = Infrastructure::where('code_pays', session('projet_selectionne'))
        ->where('code_groupe_projet', session('pays_selectionne'))->get();

        $acteurs = Acteur::where('code_pays', $country)->get();

        return view('projets.RealisationProjet.realise', [
            'ecran' => $ecran,
            'projetsNonTrouves'=>$projetsNonTrouves,
            'code_projet' => $code_projet,
            'acteurs' => $acteurs,
            'localites'=> $localites,
            'infras' => $infras,
            'projets' => $projets,
        ]);
    }
    public function updatecode_projet(Request $request)
    {
        try {
            // Récupérer le nouveau code du projet à partir de la requête
            $newcode_projet = $request->input('code_projet');

            // Effectuer une requête pour récupérer les données d'action pour le nouveau code projet
            $actions = ProjetActionAMener::where('code_projet', $newcode_projet)->get();

            // Afficher les données d'action dans la console pour le débogage
            dd($actions);

            // Retourner les données d'action en tant que réponse JSON
            return response()->json(['actions' => $actions]);
        } catch (\Exception $e) {
            // Afficher l'erreur dans la console pour le débogage
            dd($e->getMessage());

            // Retourner une réponse d'erreur
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getProjetData(Request $request)
    {
        // Récupérer le code projet depuis la requête
        $code_projet = $request->codeProjet;

        // Effectuer la requête pour récupérer les données en fonction du code projet

            $projetData = DB::table('projet_action_a_mener')
            ->select('projet_action_a_mener.code', 'code_projet', 'Num_ordre', 'action_mener.libelle as action_libelle', 'Quantite',  'infrastructures.libelle as infrastructure_libelle')
            ->join('infrastructures', 'infrastructures.id', '=', 'projet_action_a_mener.Infrastrucrues_Id')
            ->join('action_mener', 'action_mener.code', '=', 'projet_action_a_mener.Action_mener')
            ->where('code_projet', $code_projet)
            ->get();
        // Retourner les données au format JSON
        return response()->json($projetData);
    }
    public function getNumeroOrdre(Request $request)
    {
        // Récupérer les paramètres de la requête
        $code_projet = $request->codeProjet;
        $codeActionMenerProjet = $request->input('codeActionMenerProjet');
        $numeroOrdre = ProjetActionAMener::where('code_projet', $code_projet)
        ->where('code', $codeActionMenerProjet)
        ->value('Num_ordre');
        // Faites ici la logique pour récupérer le numéro d'ordre en fonction du code du projet et du code de l'action à mener
        // Par exemple, supposons que vous avez un modèle ActionMenerProjet avec une colonne 'numero_ordre'
        $infrastructureData = ProjetActionAMener::where('code_projet', $code_projet)
        ->where('projet_action_a_mener.code', $codeActionMenerProjet)
        ->join('infrastructures', 'projet_action_a_mener.Infrastrucrues_id', '=', 'infrastructures.code')
        ->select('projet_action_a_mener.Infrastrucrues_id', 'infrastructures.libelle', 'infrastructures.code_famille_infrastructure')
        ->first();

        $codeFamilleInfrastructure = $infrastructureData?->code_famille_infrastructure;
        $codeInfrastructure = $infrastructureData?->Infrastrucrues;
        $libelleInfrastructure = $infrastructureData?->libelle;

        $libelleFamilleInfrastructureData = FamilleInfrastructure::where('idFamille', $codeFamilleInfrastructure)
        ->select('libelleFamille')
        ->distinct()
        ->first();

        $libelleFamilleInfrastructure = $libelleFamilleInfrastructureData?->libelleFamille;



        // Retournez le numéro d'ordre sous forme de réponse JSON
        return response()->json([
            'numeroOrdre' => $numeroOrdre,
            'libelleInfrastructure'=>$libelleInfrastructure,
            'codeInfrastructure'=>$codeInfrastructure,
            'codeFamilleInfrastructure'=>$codeFamilleInfrastructure,
            'libelleFamilleInfrastructure'=>$libelleFamilleInfrastructure  ]);
    }




    public function getActionsByProjectCode($code_projet)
    {
        $actions = DB::table('projet_action_a_mener') // Remplacez 'actions' par votre table d'actions
            ->where('code_projet', $code_projet)
            ->get();

        return response()->json(['actions' => $actions]);
    }

    public function getBeneficiaires(Request $request)
    {
        $code_projet = $request->input('code_projet');
        $codeActionMenerProjet = $request->input('codeActionMenerProjet');

        // Effectuer la requête pour récupérer les bénéficiaires en fonction du code projet et du code de l'action à mener
        $beneficiaires = DB::table('action_beneficiaires_projet')
            ->select('beneficiaire_id')
            ->where('code_projet', $code_projet)
            ->where('code', $codeActionMenerProjet)
            ->get();

        // Retourner les bénéficiaires au format JSON
        return response()->json($beneficiaires);
    }


    /*public function fetchProjectDetails(Request $request)
    {
        // Récupérer les données de la requête
        $code_projet =  $request->input('code_projet');
        //dd($code_projet);
        // Effectuer la requête pour récupérer le code du projet en fonction des paramètres

        $code_projetData = Projet::query()
            ->join('projet_statut', 'projet_statut.code_projet', '=', 'projets.code_projet')
            ->join('type_statut', 'projet_statut.type_statut', '=', 'type_statut.id')
            ->join('devise', 'projets.code_devise', '=', 'devise.code_long')
            ->where('projets.code_projet', $code_projet)
            ->select([
                'projets.Date_demarrage_prevue',
                'projets.date_fin_prevue',
                'projets.cout_projet',
                'devise.code_long',
                'type_statut.libelle',
            ])
            ->first(); // Utilisez first() pour obtenir un seul résultat

        $date_debut = $code_projetData?->Date_demarrage_prevue;
        $date_fin = $code_projetData?->date_fin_prevue;
        $cout = $code_projetData?->cout_projet;
        $statutInput = $code_projetData?->libelle;
        $devise = $code_projetData?->code_long;



        // Récupérer les actions à mener en fonction du code du projet
        $actions = ProjetActionAMener::with('infrastructure')
        ->where('code_projet', $code_projet)
        ->get()
        ->map(function ($action) {
            return [
                'code' => $action->code,
                'Num_ordre' => $action->Num_ordre,
                'action_libelle' => $action->actionMener->libelle,
                'Quantite' => $action->Quantite,
                'Infrastrucrues_id' => $action->Infrastrucrues_id,
                'infrastructure_idCode' => $action->infrastructure?->id, // <-- ici l'ID de l'infrastructure
                'infrastructure_libelle' => $action->infrastructure?->libelle,
            ];
        });




        // Récupérer les bénéficiaires

        // Récupérer les codes des bénéficiaires en fonction du code projet, code bénéficiaire

        // Vous pouvez maintenant passer ces données à votre vue
        return response()->json([
            'codeProjet' => $code_projet,
            'date_debut'=>$date_debut,
            'date_fin'=>$date_fin,
            'cout'=>$cout,
            'statutInput'=>$statutInput,
            'devise'=>$devise,
            'actions' => $actions,

        ]);
    }*/
    private function generateCodeCaractFamille($familleCode)
    {
        // Ajoutez votre logique pour générer la valeur de 'CodeCaractFamille' en fonction de $familleCode
        // Par exemple, concaténez $familleCode avec un identifiant unique ou utilisez une autre logique appropriée.
        return $familleCode . '_' . uniqid();
    }

    public function enregistrerDatesEffectives(Request $request)
    {
        try {
            $request->validate([
                'code_projet' => 'required|string',
                'date_debut'  => 'required|date',
                'commentaire' => 'nullable|string',
            ]);
    
            $code = (string) $request->code_projet;
            $famille = $this->detectFamilyFromCode($code); // PROJET | ETUDE | APPUI
    
            // 1) Charger l’en-tête + date prévisionnelle selon le type
            $previsionnelle = null;
    
            if ($famille === 'PROJET') {
                $p = \App\Models\Projet::where('code_projet', $code)->first();
                if (!$p) return $this->jsonNotFound($request, 'Projet introuvable.');
                $previsionnelle = $p->date_demarrage_prevue;
            } elseif ($famille === 'ETUDE') {
                $e = \App\Models\EtudeProjet::where('code_projet_etude', $code)->first();
                if (!$e) return $this->jsonNotFound($request, 'Étude introuvable.');
                $previsionnelle = $e->date_debut_previsionnel;
            } else { // APPUI
                $a = \App\Models\AppuiProjet::where('code_projet_appui', $code)->first();
                if (!$a) return $this->jsonNotFound($request, 'Projet d’appui introuvable.');
                $previsionnelle = $a->date_debut_previsionnel;
            }
    
            // 2) Garde-fou sur la date (si une prévision existe)
            if ($previsionnelle && $request->date_debut < $previsionnelle) {
                $msg = 'La date effective ne peut pas être antérieure à la date prévisionnelle.';
                return $request->expectsJson()
                    ? response()->json(['success'=>false,'message'=>$msg], 422)
                    : redirect()->back()->with('error', $msg);
            }
    
            DB::beginTransaction();
    
            // 3) Enregistrer la date effective (table unifiée)
            // Si ta table `dates_effectives_projet` est commune aux 3 familles (ce que suggère ton code),
            // on peut y stocker directement:
            DateEffectiveProjet::updateOrCreate(
                ['code_projet' => $code],
                [
                    'date_debut_effective' => $request->date_debut,
                    'description'          => $request->commentaire,
                ]
            );
    
            // 4) Statut "démarré" (type_statut = 2) selon la famille
           
                ProjetStatut::updateOrCreate(
                    ['code_projet' => $code, 'type_statut' => 2],
                    ['date_statut' => now()]
                );
            
            DB::commit();
    
            $redirectUrl = route('projet.realise', ['ecran_id' => $request->ecran_id]);
    
            if ($request->expectsJson()) {
                return response()->json([
                    'success'      => true,
                    'message'      => 'Le projet a bien été lancé avec succès.',
                    'ecran_id'     => $request->ecran_id,
                    'redirect_url' => $redirectUrl,
                ]);
            }
    
            return redirect()->to($redirectUrl)->with('success', 'Le projet a bien été lancé avec succès.');
    
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erreur lors du lancement du projet', ['error' => $e->getMessage()]);
    
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de l’enregistrement.',
                    'error'   => $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Une erreur est survenue lors de l’enregistrement.');
        }
    }
    
    private function jsonNotFound(Request $request, string $msg)
    {
        return $request->expectsJson()
            ? response()->json(['success'=>false,'message'=>$msg], 404)
            : redirect()->back()->with('error', $msg);
    }
    






    /*
    OBTENIR DONNEES
    public function obtenirDonneesProjet(Request $request) {
        $code_projet2 = $request->input('code_projet2');

        $donneesDebut = DateDebutEffective::where('code_projet', $code_projet2)->first();
        $donneesFin = DateFinEffective::where('code_projet', $code_projet2)->first();

        $donnees = [
            'date_debut' => $donneesDebut ? $donneesDebut->date : null,
            'date_fin' => $donneesFin ? $donneesFin->date : null,
            'coutEffective' => $donneesFin ? $donneesFin->cout_effectif : null,
            'devise' => $donneesFin ? $donneesFin->devise : null,
            'commentaire' => $donneesDebut ? $donneesDebut->commentaire : null,
        ];

        return response()->json($donnees);
    }*/


    ///////////////////ETAT D'AVANCEMENT////////////
    public function etatAvancement(Request $request){

        $country = session('pays_selectionne');
        $group = session('projet_selectionne');

        $statuts = DB::table('projet_statut')
        ->join('type_statut', 'type_statut.id', '=', 'projet_statut.type_statut')
        ->join('projets', 'projets.code_projet', '=', 'projet_statut.code_projet')
        ->select('projet_statut.id', 'projets.code_projet', 'projet_statut.type_statut as codeSStatu', 'projet_statut.date_statut', 'type_statut.libelle as statut_libelle')
        ->where('projets.code_projet', 'like', $country . $group . '%')
        ->get();
        // Sélectionner les code_projet qui ont plus d'une Action_mener
        $projetsPlusieursActions = DB::table('projet_action_a_mener')
            ->select('code_projet')
            ->where('code_projet', 'like', $country . $group . '%')
            ->groupBy('code_projet')
            ->havingRaw('COUNT(Action_mener) > 1')
            ->pluck('code_projet')
            ->toArray();

        $projetsNonTrouves = DB::table('projets')
            ->leftJoin('projet_action_a_mener', 'projets.code_projet', '=', 'projet_action_a_mener.code_projet')
            ->whereNull('projet_action_a_mener.code_projet')
            ->where('projet_action_a_mener.code_projet', 'like', $country . $group . '%')
            ->pluck('projets.code_projet')
            ->toArray();
        // Sélectionner tous les code_projet
        // Récupérer l'utilisateur actuellement connecté
        $user = auth()->user();
        // Récupérer les données de l'utilisateur à partir de son code_personnel
        $userData = User::with('acteur')->where('acteur_id', $user->acteur_id)->first();

        // Vérifier si l'utilisateur existe
        if (!$userData) {
            // Gérer le cas où l'utilisateur n'est pas trouvé
            return redirect()->route('users.users')->with('error', 'Utilisateur non trouvé.');
        }

        $projetsAvecInfrastructures = DB::table('projet_statut')
            ->where('type_statut', 2)
            ->whereIn('code_projet', function($query) {
                $query->select('code_projet')
                      ->from('caracteristiques')
                      ->distinct();
            })
            ->where('code_projet', 'like', $country . $group . '%')
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
            ->where('projet_statut.type_statut', 2)
            ->where('projets.code_projet', 'like', $country . $group . '%')

            ->select('projets.*')
            ->with('dernierStatut')
            ->get();

            $localites = LocalitesPays::where('id_pays', session('pays_selectionne'))->get();
            $infras = Infrastructure::where('code_pays', session('projet_selectionne'))
            ->where('code_groupe_projet', session('pays_selectionne'))->get();

            $acteurs = Acteur::where('code_pays', $country)->get();

        $code_projet = $request->input('code_projet');
        $beneficiairesActions = Projet::join('profiter', 'profiter.code_projet', '=', 'projets.code_projet')
        ->join('jouir', 'jouir.code_projet', '=', 'projets.code_projet')
        ->join('beneficier', 'beneficier.code_projet', '=', 'projets.code_projet')
        ->where('projets.code_projet', $code_projet)
        ->where('projets.code_projet', 'like', $country . $group . '%')->get();

       $ecran = Ecran::find($request->input('ecran_id'));

        return view('projets.RealisationProjet.etatAvancement', ['ecran' => $ecran,
        'projets'=>$projets,
        'localites' => $localites,
        'acteurs' => $acteurs,
        'infras' => $infras,
        'beneficiairesActions'=>$beneficiairesActions,'projetsPlusieursActions' => $projetsPlusieursActions,
        'projetsAvecInfrastructures'=>$projetsAvecInfrastructures,'projetsNonTrouves'=>$projetsNonTrouves,'statuts'=>$statuts]);
    }
    //existance de code projet
    public function checkcodeProjet(Request $request)
    {
        $code_projet = $request->input('code_projet');
        $ordre = $request->input('Ordre');

        $exists = DB::table('valeurcaracteristique as vc')
            ->join('caracteristiques as c', 'vc.idCaracteristique', '=', 'c.idCaracteristique')
            ->join('projetinfrastructure as pi', 'vc.idInfrastructure', '=', 'pi.idInfrastructure')
            ->join('projet_action_a_mener as paam', 'paam.code_projet', '=', 'pi.code_projet')
            ->where('pi.code_projet', $code_projet)
            ->where('paam.Num_ordre', $ordre)
            ->exists();

        return response()->json(['exists' => $exists]);
    }


    public function enregistrerBeneficiaires(Request $request)
    {
        try {
            $projet = $request->input('CodeProjetBene');
            $ordre = $request->input('numOrdreBene');
            $beneficiaires = $request->input('beneficiaires');

            if (!is_array($beneficiaires) || empty($beneficiaires)) {
                return response()->json([
                    'error' => true,
                    'message' => 'Aucun bénéficiaire à enregistrer.',
                ], 422);
            }

            foreach ($beneficiaires as $b) {
                $code = $b['code'] ?? null;
                $type = $b['type'] ?? null;

                if (!$code || !$type) {
                    Log::warning("Bénéficiaire ignoré : informations manquantes", ['data' => $b]);
                    continue;
                }

                switch ($type) {
                    case 'acteur':
                        Beneficier::updateOrCreate([
                            'code_projet' => $projet,
                            'code_acteur' => $code,
                        ]);
                        break;

                    case 'localite':
                        Profiter::updateOrCreate([
                            'code_projet' => $projet,
                            'code_pays' => session('pays_selectionne'),
                            'code_rattachement' => $code,
                        ]);
                        break;

                    case 'infrastructure':
                        Jouir::updateOrCreate([
                            'code_projet' => $projet,
                            'code_Infrastructure' => $code,
                        ]);
                        break;

                    default:
                        Log::warning("Type de bénéficiaire inconnu : $type", ['code' => $code]);
                        break;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bénéficiaires enregistrés avec succès.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Erreur lors de l’enregistrement des bénéficiaires', [
                'exception' => $e,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l’enregistrement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function recupererBeneficiaires(Request $request)
    {
        $codeProjet = $request->input('code_projet');

        $numOrdre = $request->input('NumOrdre'); // Utilisé si tu filtres plus tard selon des actions

        $beneficiaires = [];

        // ==== 1. Acteurs (depuis `beneficier`)
        $acteurs = Beneficier::with('acteur')
            ->where('code_projet', $codeProjet)
            ->get();

        foreach ($acteurs as $item) {
            if ($item->acteur) {
                $beneficiaires[] = [
                    'code' => $item->acteur->code_acteur,
                    'type' => 'acteur',
                    'libelle_nom_etablissement' => $item->acteur->libelle_long ?? $item->acteur->libelle_court,
                ];
            }
        }

        // ==== 2. Localités (depuis `profiter`)
        $localites = Profiter::with('localite')
            ->where('code_projet', $codeProjet)
            ->get();

        foreach ($localites as $item) {
            if ($item->localite) {
                $beneficiaires[] = [
                    'code' => $item->localite->code_decoupage,
                    'type' => 'localite',
                    'libelle_nom_etablissement' => $item->localite->libelle,
                ];
            }
        }

        // ==== 3. Infrastructures (depuis `jouir`)
        $infrastructures = Jouir::with('infrastructure')
            ->where('code_projet', $codeProjet)
            ->get();

        foreach ($infrastructures as $item) {
            if ($item->infrastructure) {
                $beneficiaires[] = [
                    'code' => $item->infrastructure->code,
                    'type' => 'infrastructure',
                    'libelle_nom_etablissement' => $item->infrastructure->libelle,
                ];
            }
        }

        // ==== Réponse
        return response()->json($beneficiaires);
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
            $dateFinEffective = DateEffectiveProjet::firstOrNew([
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





        public function getHistorique(Request $request)
        {
            $request->validate([
                'code_projet' => 'required',
                'num_ordre' => 'required|integer'
            ]);

            $historique = AvancementProjet::where('code_projet', $request->code_projet)
                ->where('num_ordre', $request->num_ordre)
                ->orderBy('date_avancement', 'desc')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'date_avancement' => $item->date_avancement->format('d/m/Y'),
                        'pourcentage' => number_format($item->pourcentage, 2),
                        'photos' => $item->photos ? explode(',', $item->photos) : []
                    ];
                });

            return response()->json($historique);
        }

        public function saveAvancement(Request $request)
        {
            try {
                $request->validate([
                    'code_projet'         => 'required',
                    'num_ordre'           => 'required|integer',
                    'quantite_reel'       => 'required|numeric|min:0|max:100', // slider %
                    'date_avancement'     => 'required|date',
                    'photos_avancement'   => 'nullable|array|max:15',
                    'photos_avancement.*' => 'nullable|image|max:5120',
                    'date_fin_effective'  => 'nullable|date',
                    'description_finale'  => 'nullable|string|max:500',
        
                    // Nouveaux champs fichiers
                    'livrables'           => 'nullable|array|max:10',
                    'livrables.*'         => 'nullable|file|max:20480',             // 20 Mo
                    'rapport_appui'       => 'nullable|file|max:20480|mimes:pdf,doc,docx,odt',
                ]);
        
                $code    = (string) $request->code_projet;
                $famille = $this->detectFamilyFromCode($code);
                $pct     = max(0, min(100, (int) $request->quantite_reel));
                $numOrdre = (int) $request->num_ordre;
        
                if ($famille !== 'PROJET') {
                    // suivi global pour APPUI / ETUDE
                    $numOrdre = 0;
                }
        
                // Anti-régression
                $stats = AvancementProjet::where('code_projet', $code)
                    ->where('num_ordre', $numOrdre)
                    ->selectRaw('MAX(pourcentage) as max_pct, MAX(date_avancement) as max_date')
                    ->first();
        
                $lastPct  = (int)($stats->max_pct ?? 0);
                $lastDate = $stats->max_date ? Carbon::parse($stats->max_date) : null;
        
                if ($lastPct >= 100) {
                    throw ValidationException::withMessages([
                        'pourcentage' => "Cette action/projet est déjà à 100%. Aucun nouveau suivi n'est possible."
                    ]);
                }
                if ($pct <= $lastPct) {
                    throw ValidationException::withMessages([
                        'pourcentage' => "Le nouvel avancement ({$pct}%) doit être strictement supérieur au précédent ({$lastPct}%)."
                    ]);
                }
                if ($lastDate && Carbon::parse($request->date_avancement)->lt($lastDate)) {
                    throw ValidationException::withMessages([
                        'date_avancement' => "La date de suivi doit être postérieure ou égale à {$lastDate->format('d/m/Y')}."
                    ]);
                }
        
                DB::beginTransaction();
        
                $payload = [
                    'code_projet'     => $code,
                    'num_ordre'       => $numOrdre,
                    'quantite'        => 0, // compat (on mettra la vraie quantité pour PROJET)
                    'pourcentage'     => $pct,
                    'date_avancement' => $request->date_avancement,
                    'photos'          => null,
                    'code_acteur'     => auth()->user()->acteur_id ?? null,
                ];
        
                if ($famille === 'PROJET') {
                    $action = ProjetActionAMener::where('code_projet', $code)
                        ->where('Num_ordre', $numOrdre)
                        ->first();
        
                    if (!$action || (float) $action->Quantite <= 0) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'La quantité prévue est introuvable ou égale à zéro.'
                        ], 400);
                    }
        
                    $payload['quantite'] = (float) $action->Quantite;
        
                    // Si la colonne existe, renseigner la quantité réelle
                    try {
                        if (Schema::hasColumn((new AvancementProjet)->getTable(), 'quantite_reelle')) {
                            $payload['quantite_reelle'] = round(($pct / 100) * (float) $action->Quantite, 4);
                        }
                    } catch (\Throwable $e) {}
                } else {
                    // APPUI / ETUDE : base 100
                    try {
                        if (Schema::hasColumn((new AvancementProjet)->getTable(), 'quantite_reelle')) {
                            $payload['quantite_reelle'] = $pct; // 100 = terminé
                        }
                    } catch (\Throwable $e) {}
                }
        
                $avancement = AvancementProjet::create($payload);
        
                // ---------- UPLOAD PHOTOS ----------
                $photoPaths = [];
                if ($request->hasFile('photos_avancement')) {
                    $baseDir = public_path(
                        'Data' . DIRECTORY_SEPARATOR . 'avancement' . DIRECTORY_SEPARATOR .
                        $famille . DIRECTORY_SEPARATOR . $code . DIRECTORY_SEPARATOR .
                        date('Y') . DIRECTORY_SEPARATOR . date('m')
                    );
        
                    if (!is_dir($baseDir)) {
                        @mkdir($baseDir, 0775, true);
                    }
        
                    foreach ($request->file('photos_avancement') as $photo) {
                        if (!$photo || !$photo->isValid()) continue;
        
                        $ext = strtolower($photo->getClientOriginalExtension() ?: $photo->extension() ?: 'jpg');
                        $safeExt = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? $ext : 'jpg';
        
                        $fileName = time() . '_' . Str::random(10) . '.' . $safeExt;
                        $photo->move($baseDir, $fileName);
        
                        $relative = 'Data/avancement/' . $famille . '/' . $code . '/' . date('Y') . '/' . date('m') . '/' . $fileName;
                        $photoPaths[] = $relative;
                    }
                }
        
                if ($photoPaths) {
                    $avancement->photos = implode(',', $photoPaths);
                    $avancement->save();
                }
        
                // ---------- UPLOAD LIVRABLES (ETUDE, quand 100%) ----------
                if ($famille === 'ETUDE' && $pct >= 100 && $request->hasFile('livrables')) {
                    $livrablesPaths = [];
        
                    $baseDir = public_path(
                        'Data' . DIRECTORY_SEPARATOR . 'livrables' . DIRECTORY_SEPARATOR .
                        'ETUDE' . DIRECTORY_SEPARATOR . $code . DIRECTORY_SEPARATOR .
                        date('Y') . DIRECTORY_SEPARATOR . date('m')
                    );
                    if (!is_dir($baseDir)) {
                        @mkdir($baseDir, 0775, true);
                    }
        
                    foreach ($request->file('livrables') as $file) {
                        if (!$file || !$file->isValid()) continue;
        
                        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
                        $fileName = time() . '_' . Str::random(10) . '.' . $ext;
                        $file->move($baseDir, $fileName);
        
                        $relative = 'Data/livrables/ETUDE/' . $code . '/' . date('Y') . '/' . date('m') . '/' . $fileName;
                        $livrablesPaths[] = $relative;
                    }
        
                    if (!empty($livrablesPaths)) {
                        try {
                            if (Schema::hasColumn((new AvancementProjet)->getTable(), 'livrables')) {
                                $avancement->livrables = implode(',', $livrablesPaths);
                                $avancement->save();
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Colonne livrables absente ou erreur de sauvegarde', ['err' => $e->getMessage()]);
                        }
                    }
                }
        
                // ---------- UPLOAD RAPPORT (APPUI) ----------
                if ($famille === 'APPUI' && $request->hasFile('rapport_appui')) {
                    $rapport = $request->file('rapport_appui');
                    if ($rapport && $rapport->isValid()) {
        
                        $baseDir = public_path(
                            'Data' . DIRECTORY_SEPARATOR . 'rapports' . DIRECTORY_SEPARATOR .
                            'APPUI' . DIRECTORY_SEPARATOR . $code . DIRECTORY_SEPARATOR .
                            date('Y') . DIRECTORY_SEPARATOR . date('m')
                        );
                        if (!is_dir($baseDir)) {
                            @mkdir($baseDir, 0775, true);
                        }
        
                        $ext = strtolower($rapport->getClientOriginalExtension() ?: $rapport->extension() ?: 'pdf');
                        $fileName = time() . '_' . Str::random(10) . '.' . $ext;
                        $rapport->move($baseDir, $fileName);
        
                        $relative = 'Data/rapports/APPUI/' . $code . '/' . date('Y') . '/' . date('m') . '/' . $fileName;
        
                        try {
                            if (Schema::hasColumn((new AvancementProjet)->getTable(), 'rapport_appui')) {
                                $avancement->rapport_appui = $relative;
                                $avancement->save();
                            }
                        } catch (\Throwable $e) {
                            Log::warning('Colonne rapport_appui absente ou erreur de sauvegarde', ['err' => $e->getMessage()]);
                        }
                    }
                }
        
                // ---------- Finalisation à 100% ----------
                if ($pct >= 100) {
                    if (!$request->date_fin_effective) {
                        throw ValidationException::withMessages([
                            'date_fin_effective' => 'La date effective de fin est obligatoire pour clôturer.'
                        ]);
                    }
        
                    ProjetStatut::updateOrCreate(
                        ['code_projet' => $code, 'type_statut' => 3],
                        ['date_statut' => now()]
                    );
        
                    DateEffectiveProjet::updateOrCreate(
                        ['code_projet' => $code],
                        ['date_fin_effective' => $request->date_fin_effective]
                    );
        
                    $avancement->update([
                        'date_fin_effective' => $request->date_fin_effective,
                        'description_finale' => $request->description_finale
                    ]);
                }
        
                DB::commit();
                return response()->json(['success' => true]);
        
            } catch (\Illuminate\Validation\ValidationException $ve) {
                return response()->json([
                    'success' => false,
                    'message' => $ve->getMessage(),
                    'errors'  => $ve->errors(),
                ], 422);
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Erreur lors de saveAvancement', ['error' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de l’enregistrement.'], 500);
            }
        }
        
        public function deleteSuivi($id)
        {
            $suivi = AvancementProjet::findOrFail($id);
        
            // 1) Supprimer fichiers physiques (photos, livrables, rapport)
            $paths = [];
        
            if (!empty($suivi->photos)) {
                $paths = array_merge($paths, array_values(array_filter(array_map('trim', explode(',', (string) $suivi->photos)))));
            }
            if (!empty($suivi->livrables)) {
                $paths = array_merge($paths, array_values(array_filter(array_map('trim', explode(',', (string) $suivi->livrables)))));
            }
            if (!empty($suivi->rapport_appui)) {
                $paths[] = (string) $suivi->rapport_appui;
            }
        
            foreach ($paths as $rel) {
                if (!$rel) continue;
                $abs = public_path($rel);
                try {
                    if (is_file($abs)) @unlink($abs);
                } catch (\Throwable $e) {
                    Log::warning("Suppression fichier échouée", ['path' => $abs, 'err' => $e->getMessage()]);
                }
            }
        
            DB::beginTransaction();
            try {
                $codeProjet = $suivi->code_projet;
                $numOrdre   = $suivi->num_ordre;
        
                // Récupère action liée (PROJET)
                $action = ProjetActionAMener::where('code_projet', $codeProjet)
                    ->where('Num_ordre', $numOrdre)
                    ->first();
        
                // (a) Supprime le suivi
                $suivi->delete();
        
                // (b) Recalcule avancement max de l'action (optionnel : à ta convenance)
                $maxPctAction = (int) AvancementProjet::where('code_projet', $codeProjet)
                    ->where('num_ordre', $numOrdre)
                    ->max('pourcentage');
        
                // (c) Si infra liée → recalcul isOver (toutes actions sur la même infra à 100)
                if ($action && $action->Infrastrucrues_id) {
                    $infraId = $action->Infrastrucrues_id;
        
                    $actionsInfra = ProjetActionAMener::where('code_projet', $codeProjet)
                        ->where('Infrastrucrues_id', $infraId)
                        ->pluck('Num_ordre');
        
                    $all100 = true;
                    foreach ($actionsInfra as $ord) {
                        $pct = (int) AvancementProjet::where('code_projet', $codeProjet)
                            ->where('num_ordre', $ord)
                            ->max('pourcentage');
                        if ($pct < 100) { $all100 = false; break; }
                    }
        
                    Infrastructure::where('id', $infraId)->update(['isOver' => $all100]);
                }
        
                // (d) Recalcule la finalisation projet
                $famille = $this->detectFamilyFromCode($codeProjet);
                $projectAll100 = false;
        
                if ($famille === 'PROJET') {
                    $ordres = ProjetActionAMener::where('code_projet', $codeProjet)->pluck('Num_ordre');
                    $projectAll100 = true;
                    foreach ($ordres as $ord) {
                        $pct = (int) AvancementProjet::where('code_projet', $codeProjet)
                            ->where('num_ordre', $ord)
                            ->max('pourcentage');
                        if ($pct < 100) { $projectAll100 = false; break; }
                    }
                } else {
                    // APPUI / ETUDE : suivi global (num_ordre = 0)
                    $pct = (int) AvancementProjet::where('code_projet', $codeProjet)
                        ->where('num_ordre', 0)
                        ->max('pourcentage');
                    $projectAll100 = ($pct >= 100);
                }
        
                if ($projectAll100) {
                    ProjetStatut::updateOrCreate(
                        ['code_projet' => $codeProjet, 'type_statut' => 3],
                        ['date_statut' => now()]
                    );
                } else {
                    ProjetStatut::where('code_projet', $codeProjet)->where('type_statut', 3)->delete();
                    DateEffectiveProjet::where('code_projet', $codeProjet)->update(['date_fin_effective' => null]);
                }
        
                DB::commit();
                return response()->json(['success' => true]);
        
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Erreur suppression suivi", ['id' => $id, 'err' => $e->getMessage()]);
                return response()->json(['success' => false, 'message' => "Impossible de supprimer ce suivi."], 500);
            }
        }
        
        
        public function getDonneesFormulaireSimplifie(Request $request)
        {
            $code_projet = $request->input('code_projet');
            $num_ordre   = (int) $request->input('num_ordre');
        
            if (!$code_projet) {
                return response()->json(['success'=>false,'message'=>'Paramètres manquants.'], 400);
            }
        
            $type = $this->detectFamilyFromCode($code_projet);
        
            if ($type === 'PROJET') {
                // logique existante (légèrement consolidée)
                $data = DB::table('projet_action_a_mener as pam')
                    ->leftJoin('projets_naturetravaux as pnt', 'pam.code_projet', '=', 'pnt.code_projet')
                    ->leftJoin('nature_traveaux as nt', 'nt.code', '=', 'pnt.code_nature')
                    ->leftJoin('dates_effectives_projet as dep', 'pam.code_projet', '=', 'dep.code_projet')
                    ->where('pam.code_projet', $code_projet)
                    ->where('pam.Num_ordre', $num_ordre)
                    ->select(
                        'pam.Quantite',
                        'nt.libelle as nature_travaux',
                        'dep.date_debut_effective as date_debut_effective'
                    )
                    ->first();
        
                if (!$data) {
                    return response()->json(['success'=>false,'message'=>'Aucune donnée trouvée.'], 404);
                }
        
                // dernier %
                $lastPct = (int) AvancementProjet::where('code_projet', $code_projet)
                    ->where('num_ordre', $num_ordre)
                    ->max('pourcentage');
        
                return response()->json([
                    'success' => true,
                    'result' => [
                        'Quantite'             => $data->Quantite,
                        'nature_travaux'       => $data->nature_travaux,
                        'date_debut_effective' => $data->date_debut_effective,
                        'dernier_pourcentage'  => $lastPct,
                    ]
                ]);
            }
        
            // APPUI / ETUDE → suivi global (num_ordre = 0)
            $dateDebut = DateEffectiveProjet::where('code_projet', $code_projet)->value('date_debut_effective');
            $lastPct   = (int) AvancementProjet::where('code_projet', $code_projet)
                            ->where('num_ordre', 0)
                            ->max('pourcentage');
        
            return response()->json([
                'success' => true,
                'result' => [
                    'Quantite'             => 100,                          // base 100
                    'nature_travaux'       => 'Suivi global',
                    'date_debut_effective' => $dateDebut,
                    'dernier_pourcentage'  => $lastPct,
                ]
            ]);
        }
        


        public function verifierProjetFinalisable(Request $request)
        {
            $code = (string)$request->code_projet;
            $famille = $this->detectFamilyFromCode($code);
        
            if ($famille === 'PROJET') {
                $actions = ProjetActionAMener::where('code_projet', $code)->pluck('Num_ordre');
                $nonCompletes = AvancementProjet::where('code_projet', $code)
                    ->whereIn('num_ordre', $actions)
                    ->select('num_ordre', DB::raw('MAX(pourcentage) as max_pourcentage'))
                    ->groupBy('num_ordre')
                    ->havingRaw('max_pourcentage < 100')
                    ->count();
                return response()->json(['finalisable' => $nonCompletes == 0]);
            }
        
            // APPUI / ETUDE → suivi global
            $pct = (int) AvancementProjet::where('code_projet', $code)
                ->where('num_ordre', 0)
                ->max('pourcentage');
            return response()->json(['finalisable' => $pct >= 100]);
        }
                
        // ⬇️ Remplace ta méthode optionsProjets par celle-ci

        public function optionsProjets(Request $request)
        {
            $type   = strtoupper((string) $request->query('type', 'PROJET'));
            $statut = (int) $request->query('statut', 1); // 1 = Prévu (démarrage), 2 = En cours (suivi)
            if (!in_array($type, ['PROJET','ETUDE','APPUI'], true)) {
                return response()->json([]);
            }

            $country = (string) session('pays_selectionne');
            $group   = (string) session('projet_selectionne');
            if ($country === '' || $group === '') return response()->json([]);

            $prefixProjet = $country.$group.'%';
            $prefixEtude  = 'ET_'.$country.'_'.$group.'%';
            $prefixAppui  = 'APPUI_'.$country.'_'.$group.'%';

            $byDernierStatut = fn($q) => $q->where('type_statut', $statut);

            if ($type === 'ETUDE') {
                $rows = \App\Models\EtudeProjet::query()
                    ->selectRaw('etude_projets.code_projet_etude as code, etude_projets.intitule as label')
                    ->where('etude_projets.code_pays', $country)
                    ->where('etude_projets.code_projet_etude','like',$prefixEtude)
                    ->whereHas('dernierStatut', $byDernierStatut)
                    ->orderBy('etude_projets.code_projet_etude')
                    ->get();
                return response()->json($rows);
            }

            if ($type === 'APPUI') {
                $rows = \App\Models\AppuiProjet::query()
                    ->selectRaw('appui_projets.code_projet_appui as code, appui_projets.intitule as label')
                    ->where('appui_projets.code_pays', $country)
                    ->where('appui_projets.code_projet_appui','like',$prefixAppui)
                    ->whereHas('dernierStatut', $byDernierStatut)
                    ->orderBy('appui_projets.code_projet_appui')
                    ->get();
                return response()->json($rows);
            }

            $rows = \App\Models\Projet::query()
                ->selectRaw('projets.code_projet as code, projets.libelle_projet as label')
                ->where('projets.code_alpha3_pays', $country)
                ->where('projets.code_projet','like',$prefixProjet)
                ->whereHas('dernierStatut', $byDernierStatut)
                ->orderBy('projets.code_projet')
                ->get();

            return response()->json($rows);
        }

        private function detectFamilyFromCode(string $code): string
        {
            if (str_starts_with($code, 'ET_'))     return 'ETUDE';
            if (str_starts_with($code, 'APPUI_'))  return 'APPUI';
            return 'PROJET';
        }
        /**
         * Renvoie un "card view model" commun, inspiré de getProjetCard…
         * {
         *   code_projet, libelle_projet, nature, domaine, sousDomaine,
         *   cout, devise, date_demarrage_prevue, date_fin_prevue
         * }
         */
        private function buildUnifiedProjectCard(string $code): ?array
        {
            $famille = $this->detectFamilyFromCode($code);

            if ($famille === 'PROJET') {
                $p = \App\Models\Projet::with(['statuts.statut','sousDomaine.Domaine','devise'])
                    ->where('code_projet', $code)->first();
                if (!$p) return null;

                return [
                    'code_projet'            => $p->code_projet,
                    'libelle_projet'         => $p->libelle_projet,
                    'nature'                 => $p->statuts?->statut?->libelle,
                    'domaine'                => $p->sousDomaine?->Domaine?->libelle,
                    'sousDomaine'            => $p->sousDomaine?->lib_sous_domaine ?? $p->sousDomaine?->libelle,
                    'cout'                   => $p->cout_projet,
                    'devise'                 => $p->devise?->code_long ?? $p->code_devise,
                    'date_demarrage_prevue'  => $p->date_demarrage_prevue,
                    'date_fin_prevue'        => $p->date_fin_prevue,
                ];
            }

            if ($famille === 'ETUDE') {
                $e = \App\Models\EtudeProjet::with(['statuts.statut','sousDomaine.Domaine'])
                    ->where('code_projet_etude', $code)->first();
                if (!$e) return null;

                return [
                    'code_projet'            => $e->code_projet_etude,
                    'libelle_projet'         => $e->intitule,
                    'nature'                 => $e->statuts?->statut?->libelle,
                    'domaine'                => $e->sousDomaine?->Domaine?->libelle,
                    'sousDomaine'            => $e->sousDomaine?->lib_sous_domaine ?? $e->sousDomaine?->libelle,
                    'cout'                   => $e->montant_budget_previsionnel,
                    'devise'                 => $e->code_devise,
                    'date_demarrage_prevue'  => $e->date_debut_previsionnel,
                    'date_fin_prevue'        => $e->date_fin_previsionnel,
                ];
            }

            // APPUI
            $a = \App\Models\AppuiProjet::with(['statuts.statut','sousDomaine.Domaine'])
                ->where('code_projet_appui', $code)->first();
            if (!$a) return null;

            return [
                'code_projet'            => $a->code_projet_appui,
                'libelle_projet'         => $a->intitule,
                'nature'                 => $a->statuts?->statut?->libelle,
                'domaine'                => $a->sousDomaine?->Domaine?->libelle,
                'sousDomaine'            => $a->sousDomaine?->lib_sous_domaine ?? $a->sousDomaine?->libelle,
                'cout'                   => $a->montant_budget_previsionnel,
                'devise'                 => $a->code_devise,
                'date_demarrage_prevue'  => $a->date_debut_previsionnel,
                'date_fin_prevue'        => $a->date_fin_previsionnel,
            ];
        }
        public function listeProjetsByType(Request $request)
        {
            $type = strtoupper((string)$request->query('type', 'PROJET'));
            if (!in_array($type, ['PROJET','ETUDE','APPUI'], true)) {
                return response()->json([]);
            }
        
            $country = (string) session('pays_selectionne');
            $group   = (string) session('projet_selectionne');
            if ($country === '' || $group === '') return response()->json([]);
        
            $prefixProjet = $country.$group.'%';
            $prefixEtude  = 'ET_'.$country.'_'.$group.'%';
            $prefixAppui  = 'APPUI_'.$country.'_'.$group.'%';
        
            // Dernier statut = 1 (Prévu). Tu peux élargir si besoin.
            if ($type === 'ETUDE') {
                $rows = \App\Models\EtudeProjet::query()
                    ->with(['sousDomaine.Domaine'])
                    ->where('code_pays',$country)
                    ->where('code_projet_etude','like',$prefixEtude)
                    ->whereHas('dernierStatut', fn($q)=>$q->where('type_statut',1))
                    ->orderBy('code_projet_etude')
                    ->get()
                    ->map(function($e){
                        return [
                            'code'    => $e->code_projet_etude,
                            'domaine' => $e->sousDomaine?->Domaine?->libelle,
                            'date_debut' => $e->date_debut_previsionnel,
                            'date_fin'   => $e->date_fin_previsionnel,
                            'cout'    => $e->montant_budget_previsionnel,
                            'devise'  => $e->code_devise,
                            'statut'  => $e->dernierStatut?->statut?->libelle ?? 'Prévu',
                        ];
                    });
        
                return response()->json($rows);
            }
        
            if ($type === 'APPUI') {
                $rows = \App\Models\AppuiProjet::query()
                    ->with(['sousDomaine.Domaine'])
                    ->where('code_pays',$country)
                    ->where('code_projet_appui','like',$prefixAppui)
                    ->whereHas('dernierStatut', fn($q)=>$q->where('type_statut',1))
                    ->orderBy('code_projet_appui')
                    ->get()
                    ->map(function($a){
                        return [
                            'code'    => $a->code_projet_appui,
                            'domaine' => $a->sousDomaine?->Domaine?->libelle,
                            'date_debut' => $a->date_debut_previsionnel,
                            'date_fin'   => $a->date_fin_previsionnel,
                            'cout'    => $a->montant_budget_previsionnel,
                            'devise'  => $a->code_devise,
                            'statut'  => $a->dernierStatut?->statut?->libelle ?? 'Prévu',
                        ];
                    });
        
                return response()->json($rows);
            }
        
            // PROJET
            $rows = \App\Models\Projet::query()
                ->with(['sousDomaine.Domaine','devise','statuts.statut'])
                ->where('code_alpha3_pays',$country)
                ->where('code_projet','like',$prefixProjet)
                ->whereHas('dernierStatut', fn($q)=>$q->where('type_statut',1))
                ->orderBy('code_projet')
                ->get()
                ->map(function($p){
                    return [
                        'code'    => $p->code_projet,
                        'domaine' => $p->sousDomaine?->Domaine?->libelle,
                        'date_debut' => $p->date_demarrage_prevue,
                        'date_fin'   => $p->date_fin_prevue,
                        'cout'    => $p->cout_projet,
                        'devise'  => $p->devise?->code_long ?? $p->code_devise,
                        'statut'  => $p->statuts?->statut?->libelle ?? 'Prévu',
                    ];
                });
        
            return response()->json($rows);
        }
        public function projectCard(string $code)
        {
            $card = $this->buildUnifiedProjectCard($code);
            if (!$card) {
                return response()->json(null, 404);
            }
            return response()->json($card);
        }
        
        // ⬇️ Remplace beneficiairesAppui par cette version

        public function beneficiairesAppui(string $code_appui)
        {
            $appui = \App\Models\AppuiProjet::with([
                'projets.beneficiairesActeurs.acteur',
                'projets.beneficiairesLocalites.localite',
                'projets.beneficiairesInfrastructures.infrastructure',
            ])->where('code_projet_appui', $code_appui)->first();

            if (!$appui) return response()->json([], 404);

            $list = collect();

            foreach ($appui->projets as $p) {
                foreach ($p->beneficiairesActeurs as $b) {
                    if ($b->acteur) {
                        $list->push([
                            'code'   => $b->acteur->code_acteur,
                            'type'   => 'acteur',
                            'libelle'=> $b->acteur->libelle_long ?? $b->acteur->libelle_court ?? '',
                            'projet_source' => ['code'=>$p->code_projet,'libelle'=>$p->libelle_projet],
                        ]);
                    }
                }
                foreach ($p->beneficiairesLocalites as $b) {
                    if ($b->localite) {
                        $list->push([
                            'code'   => $b->localite->code_decoupage ?? $b->localite->code_rattachement,
                            'type'   => 'localite',
                            'libelle'=> $b->localite->libelle ?? '',
                            'projet_source' => ['code'=>$p->code_projet,'libelle'=>$p->libelle_projet],
                        ]);
                    }
                }
                foreach ($p->beneficiairesInfrastructures as $b) {
                    if ($b->infrastructure) {
                        $list->push([
                            'code'   => $b->infrastructure->code,
                            'type'   => 'infrastructure',
                            'libelle'=> $b->infrastructure->libelle ?? '',
                            'projet_source' => ['code'=>$p->code_projet,'libelle'=>$p->libelle_projet],
                        ]);
                    }
                }
            }

            return response()->json($list->unique(fn($x)=>$x['type'].'#'.$x['code'])->values());
        }


        public function fetchProjectDetails(Request $request)
        {
            $code_projet = (string) $request->code_projet;
            $type = $this->detectFamilyFromCode($code_projet);
        
            $card = $this->buildUnifiedProjectCard($code_projet); // ta méthode unifiée si déjà ajoutée
            if (!$card) {
                return response()->json(['message' => 'Projet introuvable.'], 404);
            }
        
            // Par défaut : pas d’actions
            $actions = collect();
        
            if ($type === 'PROJET') {
                $actions = \App\Models\ProjetActionAMener::with('infrastructure','actionMener')
                    ->where('code_projet', $code_projet)
                    ->get()
                    ->map(function ($action) {
                        return [
                            'code'                  => $action->code,
                            'Num_ordre'             => $action->Num_ordre,
                            'action_libelle'        => $action->actionMener?->libelle,
                            'Quantite'              => $action->Quantite,
                            'Infrastrucrues_id'     => $action->Infrastrucrues_id,
                            'infrastructure_idCode' => $action->infrastructure?->id,
                            'infrastructure_libelle'=> $action->infrastructure?->libelle,
                        ];
                    });
            }
        
            return response()->json([
                'type'        => $type,
                'codeProjet'  => $card['code_projet'],
                'date_debut'  => $card['date_demarrage_prevue'],
                'date_fin'    => $card['date_fin_prevue'],
                'cout'        => $card['cout'],
                'statutInput' => $card['nature'] ?? 'Prévu',
                'devise'      => $card['devise'],
                'libelle_projet' => $card['libelle_projet'],
                'domaine'        => $card['domaine'],
                'sousDomaine'    => $card['sousDomaine'],
                'actions'     => $actions, // vide pour ETUDE/APPUI
            ]);
        }
        
}

