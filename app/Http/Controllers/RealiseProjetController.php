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
    

        $localites = LocalitesPays::all();
        $infras = Infrastructure::all();

        $acteurs = Acteur::where('code_pays', $country)->get(); 

        return view('realise', [
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


    public function fetchProjectDetails(Request $request)
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
    }
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
                'code_projet' => 'required|string|exists:projets,code_projet',
                'date_debut' => 'required|date',
                'commentaire' => 'nullable|string',
            ]);
    
            $projet = Projet::where('code_projet', $request->code_projet)->first();
            if (!$projet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projet introuvable.'
                ], 404);
            }
    
            if ($projet->date_demarrage_prevue && $request->date_debut < $projet->date_demarrage_prevue) {
                return response()->json([
                    'success' => false,
                    'message' => 'La date effective ne peut pas être antérieure à la date prévisionnelle.'
                ], 422);
            }
    
            // Enregistrement
            DateEffectiveProjet::updateOrCreate([
                'code_projet' => $request->code_projet,
                'date_debut_effective' => $request->date_debut,
                'description' => $request->commentaire,
            ]);
    
            ProjetStatut::create([
                'code_projet' => $request->code_projet,
                'type_statut' => 2, 
                'date_statut' => now(),
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Le projet a bien été lancé avec succès.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Erreur lors du lancement du projet', ['error' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l’enregistrement.',
                'error' => $e->getMessage()
            ], 500);
        }
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
        

        $localites = LocalitesPays::all();
        $acteurs = Acteur::all();
        $infras = Infrastructure::all();
        $code_projet = $request->input('code_projet');
        $beneficiairesActions = Projet::join('profiter', 'profiter.code_projet', '=', 'projets.code_projet')
        ->join('jouir', 'jouir.code_projet', '=', 'projets.code_projet')
        ->join('beneficier', 'beneficier.code_projet', '=', 'projets.code_projet')
        ->where('projets.code_projet', $code_projet)
        ->where('projets.code_projet', 'like', $country . $group . '%')->get();

       $ecran = Ecran::find($request->input('ecran_id'));

        return view('etatAvancement', ['ecran' => $ecran,
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
                    'code_projet'        => 'required',
                    'num_ordre'          => 'required|integer',
                    'quantite_reel'      => 'required|numeric|min:0',
                    'date_avancement'    => 'required|date',
                    'photos_avancement.*'=> 'nullable|image|max:5120', // 5MB/photo
                    'date_fin_effective' => 'nullable|date',
                    'description_finale' => 'nullable|string|max:500'
                ]);
        
                $action = ProjetActionAMener::where('code_projet', $request->code_projet)
                    ->where('Num_ordre', $request->num_ordre)
                    ->first();
        
                if (!$action || (float)$action->Quantite <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La quantité prévue est introuvable ou égale à zéro.'
                    ], 400);
                }
        
                $quantiteReelle = (float)$request->quantite_reel;
                $pourcentage    = min(100, ($quantiteReelle / (float)$action->Quantite) * 100); // clamp à 100%
        
                // 1) Créer l’avancement SANS les photos (pour avoir l’ID si besoin)
                $avancement = AvancementProjet::create([
                    'code_projet'        => $request->code_projet,
                    'num_ordre'          => $request->num_ordre,
                    'quantite'           => $action->Quantite,
                    'pourcentage'        => $pourcentage,
                    'date_avancement'    => $request->date_avancement,
                    'photos'             => null, // rempli après upload
                    'date_fin_effective' => ($pourcentage >= 100) ? $request->date_fin_effective : null,
                    'description_finale' => ($pourcentage >= 100) ? $request->description_finale : null,
                    'code_acteur'        => auth()->user()->acteur_id
                ]);
        
                // 2) Upload des photos dans GridFS (facultatif)
                $photoIds = [];
                if ($request->hasFile('photos_avancement')) {
                    foreach ($request->file('photos_avancement') as $photo) {
                        if (!$photo || !$photo->isValid()) continue;
        
                        $res = app(FileProcService::class)->handle([
                            'owner_type'  => 'Projet',            // ou 'Avancement'
                            'owner_id'    => (string)$request->code_projet, // ok si owner_id est VARCHAR
                            'categorie'   => 'AVANCEMENT_PHOTO', // ajoute cette catégorie à ta whitelist
                            'file'        => $photo,
                            'uploaded_by' => optional($request->user())->id,
                        ]);
        
                        $photoIds[] = (string)$res['id']; // on stocke l’ID fichiers
                    }
                }
        
                if (!empty($photoIds)) {
                    $avancement->photos = implode(',', $photoIds); // colonne existante "photos"
                    $avancement->save();
                }
        
                // 3) Si finalisation
                if ($pourcentage >= 100 && $request->filled('date_fin_effective')) {
                    ProjetStatut::create([
                        'code_projet' => $request->code_projet,
                        'type_statut' => 7,
                        'date_statut' => now()
                    ]);
        
                    DateEffectiveProjet::updateOrCreate(
                        ['code_projet' => $request->code_projet],
                        ['date_fin_effective' => $request->date_fin_effective]
                    );
        
                    if (!empty($action->infrastructure_idCode)) {
                        Infrastructure::where('code', $action->infrastructure_idCode)
                            ->update(['isOver' => true]);
                    }
        
                    $avancement->update([
                        'date_fin_effective' => $request->date_fin_effective,
                        'description_finale' => $request->description_finale
                    ]);
                }
        
                return response()->json(['success' => true]);
        
            } catch (\Throwable $e) {
                Log::error('Erreur lors de l’enregistrement de l’avancement', [
                    'exception'    => $e->getMessage(),
                    'code_projet'  => $request->code_projet ?? null,
                    'num_ordre'    => $request->num_ordre ?? null,
                    'trace'        => $e->getTraceAsString(),
                ]);
        
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de l’enregistrement.',
                    'error'   => $e->getMessage()
                ], 500);
            }
        }
        
        
        
        public function deleteSuivi($id)
        {
            $suivi = AvancementProjet::findOrFail($id);
            
            // Supprimer les photos associées
            if ($suivi->photos) {
                foreach (explode(',', $suivi->photos) as $photo) {
                    Storage::delete('public/avancement/'.$photo);
                }
            }
            
            $suivi->delete();
            
            return response()->json(['success' => true]);
        }
        public function getDonneesFormulaireSimplifie(Request $request)
        {
            $code_projet = $request->input('code_projet');
            $num_ordre = $request->input('num_ordre');
        
            if (!$code_projet || !$num_ordre) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètres manquants.'
                ], 400);
            }
        
            $data = DB::table('projet_action_a_mener as pam')
                ->leftJoin('projets_naturetravaux as pnt', 'pam.code_projet', '=', 'pnt.code_projet')
                ->leftJoin('nature_traveaux as nt', 'nt.code', '=', 'pnt.code_nature')
                ->leftJoin('dates_effectives_projet as dde', 'pam.code_projet', '=', 'dde.code_projet')
                ->where('pam.code_projet', $code_projet)
                ->where('pam.Num_ordre', $num_ordre)
                ->select(
                    'pam.Quantite',
                    'nt.libelle as nature_travaux',
                    'dde.date_debut_effective as date_debut_effective'
                )
                ->first();
        
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune donnée trouvée pour ce projet et numéro d’ordre.'
                ], 404);
            }
        
            return response()->json([
                'success' => true,
                'result' => $data
            ]);
        }

     
        public function verifierProjetFinalisable(Request $request)
        {
            $code_projet = $request->code_projet;
        
            $actions = ProjetActionAMener::where('code_projet', $code_projet)->pluck('Num_ordre');
        
            $nonCompletes = AvancementProjet::where('code_projet', $code_projet)
                ->whereIn('num_ordre', $actions)
                ->select('num_ordre', DB::raw('MAX(pourcentage) as max_pourcentage'))
                ->groupBy('num_ordre')
                ->havingRaw('max_pourcentage < 100')
                ->count();
        
            return response()->json([
                'finalisable' => $nonCompletes == 0
            ]);
        }
        

}

