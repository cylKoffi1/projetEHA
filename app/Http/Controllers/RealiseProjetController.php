<?php

namespace App\Http\Controllers;

use App\Models\ActionBeneficiairesProjet;
use App\Models\Beneficiaire;
use App\Models\Caracteristique;
use App\Models\CaractInstrumentation;
use App\Models\CaractOuvrage;
use App\Models\CaractOuvrageAssainiss;
use App\Models\CaractOuvrageCaptage;
use App\Models\CaractOuvrageCaptageEau;
use App\Models\CaractReseau;
use App\Models\CaractReseauCollect;
use App\Models\CaractReservoir;
use App\Models\CaractUniteTraitement;
use App\Models\CouvrirRegion;
use App\Models\DateDebutEffective;
use App\Models\DateFinEffective;
use App\Models\FamilleInfrastructure;
use App\Models\Departement;
use App\Models\District;
use App\Models\Etablissement;
use App\Models\Localite;
use App\Models\Infrastructure;
use App\Models\MaterielStockage;
use App\Models\NatureTravaux;
use App\Models\NiveauAvancement;
use App\Models\OuvrageTransport;
use App\Models\Region;
use App\Models\Sous_prefecture;
use App\Models\Pays;
use App\Models\Ecran;
use App\Models\NiveauAccesDonnees;
use App\Models\ProjetActionAMener;
use App\Models\ProjetEha2;
use App\Models\ProjetStatutProjet;
use App\Models\typeCaptage;
use App\Models\TypeInstrument;
use App\Models\TypeResaux;
use App\Models\UniteTraitement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RealiseProjetController extends Controller
{

    public function realise()
    {
        $projet = ProjetEha2::all();

        return view('realise',[
            'projet' => $projet,

        ]);
    }

    public function PramatreRealise(Request $request)
    {
        $typeCaptages = TypeCaptage::all();
        $uniteTraitements = UniteTraitement::all();
        $materielStockages = MaterielStockage::all();
        $OuvrageTransports = OuvrageTransport::all();
        $infrastructures = Infrastructure::all();
        $typeReseaux = TypeResaux::all();
        $typeInstruments = TypeInstrument::all();
        $familleInfras = FamilleInfrastructure::all();
        $natureTravaux = NatureTravaux::all();
        $ecran = Ecran::find($request->input('ecran_id'));
        // Récupérer les paramètres de la requête
        $codeProjet = $request->input('codeProjet');
        $codeActionMenerProjet = $request->input('codeActionMenerProjet');
        $infrastructureData = ProjetActionAMener::where('CodeProjet', $codeProjet)
            ->where('projet_action_a_mener.code', $codeActionMenerProjet)
            ->join('infrastructures', 'projet_action_a_mener.Infrastrucrues', '=', 'infrastructures.code')
            ->select('infrastructures.code_famille_infrastructure')
            ->first();

        $codeFamilleInfrastructure = $infrastructureData->code_famille_infrastructure;

        // Récupérer la date enregistrée pour le code_projet
        $codeProjet2 = $request->input('code_projet2');
        $dateEnregistree = DateDebutEffective::where('code_projet', $codeProjet2)->value('date');

        // Transmettre les données à la vue
        return view('parametreRealise', ['ecran' => $ecran,
            'codeProjet2'=>$codeProjet2,
            'typeInstruments' => $typeInstruments,
            'codeFamilleInfrastructure' => $codeFamilleInfrastructure,
            'natureTravaux' => $natureTravaux,
            'infrastructure' => $infrastructures,
            'famille' => $familleInfras,
            'infrastructures' => $infrastructures,
            'typeReseaux' => $typeReseaux,
            'typeCaptages' => $typeCaptages,
            'uniteTraitements' => $uniteTraitements,
            'materielStockages' => $materielStockages,
            'OuvrageTransports' => $OuvrageTransports,
            'dateEnregistree' => $dateEnregistree,
        ]);
    }


    public function VoirListe(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));

        $statutProjetStatut = ProjetStatutProjet::all();
        $natureTravaux = NatureTravaux::all();
        $projetsNonTrouves = DB::table('projet_eha2')
            ->leftJoin('projet_action_a_mener', 'projet_eha2.CodeProjet', '=', 'projet_action_a_mener.CodeProjet')
            ->whereNull('projet_action_a_mener.CodeProjet')
            ->pluck('projet_eha2.CodeProjet')
            ->toArray();
        // Sélectionner tous les CodeProjet
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

        // Utiliser les régions et les districts récupérés pour filtrer les projets
        $tousLesProjets = DB::table('projet_statut_projet')
            ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'projet_statut_projet.code_projet')
            ->where('code_statut_projet', 1)
            ->whereIn('projet_eha2.code_district', $districts->pluck('code')->toArray())
            ->whereIn('projet_eha2.code_region', $regions->pluck('code')->toArray())
            ->distinct()
            ->pluck('code_projet')
            ->toArray();
        $projets =  ProjetEha2::whereIn('code_district', $districts->pluck('code')->toArray())
        ->whereIn('code_region', $regions->pluck('code')->toArray())
        ->get();
        // Ajoutez la récupération des actions ici (remplacez par votre propre logique)
        $actions = ProjetActionAMener::all();
        // Définissez la variable $beneficiairesActions
        $beneficiairesActions = ActionBeneficiairesProjet::all(); // Assurez-vous de sélectionner les données appropriées
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

        return view('realise', [
            'ecran' => $ecran,
            'projetsNonTrouves'=>$projetsNonTrouves,
            'tousLesProjets'=>$tousLesProjets,
            'projets' => $projets,
            'statuts' => $statuts,
            'actions' => $actions,
            'statutProjetStatut' => $statutProjetStatut,
            'localite'=>$localite,
            'etablissements'=>$etablissements,
            'districts'=>$districts,
            'departements'=>$departements,
            'sous_prefecture'=>$sous_prefecture,
            'regions'=>$regions,
            'beneficiairesActions'=>$beneficiairesActions

        ]);
    }
    public function updateCodeProjet(Request $request)
    {
        try {
            // Récupérer le nouveau code du projet à partir de la requête
            $newCodeProjet = $request->input('code_projet');

            // Effectuer une requête pour récupérer les données d'action pour le nouveau code projet
            $actions = ProjetActionAMener::where('CodeProjet', $newCodeProjet)->get();

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
        $codeProjet = $request->input('codeProjet');

        // Effectuer la requête pour récupérer les données en fonction du code projet

            $projetData = DB::table('projet_action_a_mener')
            ->select('projet_action_a_mener.code', 'CodeProjet', 'Num_ordre', 'action_mener.libelle as action_libelle', 'Quantite', 'Unite_mesure', 'infrastructures.libelle as infrastructure_libelle')
            ->join('infrastructures', 'infrastructures.code', '=', 'projet_action_a_mener.Infrastrucrues')
            ->join('action_mener', 'action_mener.code', '=', 'projet_action_a_mener.Action_mener')
            ->where('CodeProjet', $codeProjet)
            ->get();
        // Retourner les données au format JSON
        return response()->json($projetData);
    }
    public function getNumeroOrdre(Request $request)
    {
        // Récupérer les paramètres de la requête
        $codeProjet = $request->input('codeProjet');
        $codeActionMenerProjet = $request->input('codeActionMenerProjet');
        $numeroOrdre = ProjetActionAMener::where('CodeProjet', $codeProjet)
        ->where('code', $codeActionMenerProjet)
        ->value('Num_ordre');
        // Faites ici la logique pour récupérer le numéro d'ordre en fonction du code du projet et du code de l'action à mener
        // Par exemple, supposons que vous avez un modèle ActionMenerProjet avec une colonne 'numero_ordre'
        $infrastructureData = ProjetActionAMener::where('CodeProjet', $codeProjet)
        ->where('projet_action_a_mener.code', $codeActionMenerProjet)
        ->join('infrastructures', 'projet_action_a_mener.Infrastrucrues', '=', 'infrastructures.code')
        ->select('projet_action_a_mener.Infrastrucrues', 'infrastructures.libelle', 'infrastructures.code_famille_infrastructure')
        ->first();

        $codeFamilleInfrastructure = $infrastructureData->code_famille_infrastructure;
        $codeInfrastructure = $infrastructureData->Infrastrucrues;
        $libelleInfrastructure = $infrastructureData->libelle;

        $libelleFamilleInfrastructureData = FamilleInfrastructure::where('code', $codeFamilleInfrastructure)
        ->select('nom_famille')
        ->distinct()
        ->first();

        $libelleFamilleInfrastructure = $libelleFamilleInfrastructureData->nom_famille;



        // Retournez le numéro d'ordre sous forme de réponse JSON
        return response()->json([
            'numeroOrdre' => $numeroOrdre,
            'libelleInfrastructure'=>$libelleInfrastructure,
            'codeInfrastructure'=>$codeInfrastructure,
            'codeFamilleInfrastructure'=>$codeFamilleInfrastructure,
            'libelleFamilleInfrastructure'=>$libelleFamilleInfrastructure  ]);
    }




    public function getActionsByProjectCode($codeProjet)
    {
        $actions = DB::table('projet_action_a_mener') // Remplacez 'actions' par votre table d'actions
            ->where('CodeProjet', $codeProjet)
            ->get();

        return response()->json(['actions' => $actions]);
    }

    public function getBeneficiaires(Request $request)
    {
        $codeProjet = $request->input('codeProjet');
        $codeActionMenerProjet = $request->input('codeActionMenerProjet');

        // Effectuer la requête pour récupérer les bénéficiaires en fonction du code projet et du code de l'action à mener
        $beneficiaires = DB::table('action_beneficiaires_projet')
            ->select('beneficiaire_id')
            ->where('CodeProjet', $codeProjet)
            ->where('code', $codeActionMenerProjet)
            ->get();

        // Retourner les bénéficiaires au format JSON
        return response()->json($beneficiaires);
    }


    public function fetchProjectDetails(Request $request)
    {
        // Récupérer les données de la requête
        $code_projet = $request->input('code_projet');

        // Effectuer la requête pour récupérer le code du projet en fonction des paramètres

        $codeProjetData = ProjetEha2::query()
            ->join('projet_statut_projet', 'projet_statut_projet.code_projet', '=', 'projet_eha2.CodeProjet')
            ->join('statut_projet', 'projet_statut_projet.code_statut_projet', '=', 'statut_projet.code')
            ->join('devise', 'projet_eha2.code_devise', '=', 'devise.code')
            ->where('projet_eha2.CodeProjet', $code_projet)
            ->select([
                'projet_eha2.Date_demarrage_prevue',
                'projet_eha2.date_fin_prevue',
                'projet_eha2.cout_projet',
                'devise.code_long',
                'statut_projet.libelle',
            ])
            ->first(); // Utilisez first() pour obtenir un seul résultat

        $date_debut = $codeProjetData->Date_demarrage_prevue;
        $date_fin = $codeProjetData->date_fin_prevue;
        $cout = $codeProjetData->cout_projet;
        $statutInput = $codeProjetData->libelle;
        $devise = $codeProjetData->code_long;



        // Récupérer les actions à mener en fonction du code du projet
        $actions = ProjetActionAMener::where('CodeProjet', $code_projet)->get();

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
    public function storeCaracteristiques(Request $request){
        try {
            DB::beginTransaction();
            $codeCaractFamGenerer =$this->generateCodeCaractFamille($request->input('Famillecode'));
            // Créez une instance de Caracteristique
            $caracteristique = Caracteristique::create([
                'CodeProjet' => $request->input('code_projet'),
                'Ordre' => $request->input('ordre'),
                'codeInfrastructure' => $request->input('infrastructurecode'),
                'codeFamille' => $request->input('Famillecode'),
                'CodeCaractFamille' => $codeCaractFamGenerer,
            ]);

            switch ($request->input('Famillecode')) {
                case 1:
                    CaractOuvrageCaptage::create([
                        'CodeCaractFamille' => $codeCaractFamGenerer,
                        'typeCaptage' => $request->input('typeCaptage1'),
                        'debitCapacite' => $request->input('debitCaptage1'),
                        'profondeur' => $request->input('profondeurCaptage1'),
                        'natureTraveaux' => $request->input('natureTravauxCaptage1'),
                    ]);
                    break;
                case 2:
                    CaractOuvrageCaptageEau::create([
                        'CodeCaractFamille' => $codeCaractFamGenerer,
                        'typeCaptage' => $request->input('typeCaptage'),
                        'debitCapacite' => $request->input('debitCaptage'),
                        'profondeur' => $request->input('profondeurCaptage'),
                        'natureTraveaux' => $request->input('natureTravauxCaptage2'),
                    ]);
                    break;
                case 3:
                    CaractUniteTraitement::create([
                        'CodeCaractFamille' => $codeCaractFamGenerer,
                        'typeUnite' => $request->input('typeUnite'),
                        'debitCapacite' => $request->input('debitUnite'),
                        'natureTraveaux' => $request->input('natureTravauxCaptage3'),
                    ]);
                    break;
                case 4:
                    CaractReservoir::create([
                        'CodeCaractFamille' => $codeCaractFamGenerer,
                        'typeReservoir' => $request->input('typeReservoir'),
                        'materiaux' => $request->input('materiauReservoir'),
                        'capacite' => $request->input('capaciteReservoir'),
                        'natureTraveaux' => $request->input('natureTravauxCaptage4'),
                    ]);
                    break;
                case 5:
                    CaractReseau::create([
                        'CodeCaractFamille' => $codeCaractFamGenerer,
                        'typeTransport' => $request->input('typeTransportReseau'),
                        'materiaux' => $request->input('materiauReseau'),
                        'Diametre' => $request->input('diametreReseau'),
                        'lineaire' => $request->input('lineaireReseau'),
                        'natureTravaux' => $request->input('natureTravauxCaptage5'),
                    ]);
                    break;
                case 6:
                    CaractOuvrageAssainiss::create([
                        'CodeCaractFamille' => $codeCaractFamGenerer,
                        'typeOuvrage' => $request->input('typeOuvrageAssainissement'),
                        'capaciteVolume' => $request->input('capaciteOuvrageAssainissement'),
                        'natureTraveaux' => $request->input('natureTravauxCaptage6'),

                    ]);
                    break;
                case 7:
                    CaractReseauCollect::create([
                        'CodeCaractFamille' => $codeCaractFamGenerer,
                        'typeOuvrage' => $request->input('typeOuvrageReseau'),
                        'typeReseau' => $request->input('typeReseauReseau'),
                        'classe	' => $request->input('classeReseau'),
                        'lineaire' => $request->input('lineaireReseauReseau'),
                        'natureTraveaux' => $request->input('natureTravauxCaptage7'),

                    ]);
                    break;
                case 9:
                    CaractOuvrage::create([
                        'CodeCaractFamille' => $codeCaractFamGenerer,
                        'typeOuvrage' => $request->input('typeOuvrage'),
                        'nombre' => $request->input('nombreOuvrage'),
                        'natureTraveaux	' => $request->input('natureTravauxCaptage8'),

                    ]);
                    break;
                case 10:
                    CaractInstrumentation::create([
                        'CodeCaractFamille' => $codeCaractFamGenerer,
                        'typeInstrument' => $request->input('typeInstrument'),
                        'nombre' => $request->input('nombreInstrument'),
                        'natureTraveaux' => $request->input('natureTravauxCaptage9'),

                    ]);
                    break;

            }
            $codeProjetModif = ProjetStatutProjet::where('code_projet', $request->input('code_projet'))->first();

            if (!$codeProjetModif) {
                return response()->json(['error' => 'Code projet non trouvé'], 404);
            }

            $codeProjetModif->code_statut_projet = '02';
            $codeProjetModif->save();

            DB::commit();

            return redirect()->back()->with('success', 'Caractéristiques enregistrées avec succès. ');



        } catch (\Exception $e) {
            // En cas d'erreur, annulez la transaction
            DB::rollback();

            return redirect()->back()->with('error', 'Une erreur s\'est produite lors de l\'enregistrement des caractéristiques...');
        }
    }

    public function enregistrerDatesEffectives(Request $request)
    {
        try {
            // Vérifier si une entrée avec le même code de projet existe déjà
            $existingDate = DateDebutEffective::where('code_projet', $request->input('code_projet2'))->first();

            if ($existingDate) {
                // Si une entrée existe, renvoyer un message d'erreur
                return redirect()->back()->with('error', 'La date de début existe déjà pour ce code de projet.');
            }

            // Si aucune entrée n'existe, créer l'objet DateDebutEffective
            $dateDebutEffective = DateDebutEffective::create([
                'date' => $request->input('date_debut'),
                'code_projet' => $request->input('code_projet2'),
                'commentaire' => $request->input('commentaire'),
            ]);

            return redirect()->back()->with('success', 'Dates effectives enregistrées avec succès');
        } catch (\Exception $e) {
            // En cas d'erreur, annulez la transaction
            DB::rollback();

            return redirect()->back()->with('error', 'Une erreur s\'est produite lors de l\'enregistrement des dates effectives...');
        }
    }


    /*
    OBTENIR DONNEES
    public function obtenirDonneesProjet(Request $request) {
        $codeProjet2 = $request->input('code_projet2');

        $donneesDebut = DateDebutEffective::where('code_projet', $codeProjet2)->first();
        $donneesFin = DateFinEffective::where('code_projet', $codeProjet2)->first();

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

        $statuts = DB::table('projet_statut_projet')
        ->join('statut_projet', 'statut_projet.code', '=', 'projet_statut_projet.code_statut_projet')
        ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'projet_statut_projet.code_projet')
        ->select('projet_statut_projet.code', 'projet_eha2.CodeProjet', 'projet_statut_projet.code_statut_projet as codeSStatu', 'projet_statut_projet.date', 'statut_projet.libelle as statut_libelle')
        ->get();
        // Sélectionner les CodeProjet qui ont plus d'une Action_mener
            $projetsPlusieursActions = DB::table('projet_action_a_mener')
            ->select('CodeProjet')
            ->groupBy('CodeProjet')
            ->havingRaw('COUNT(Action_mener) > 1')
            ->pluck('CodeProjet')
            ->toArray();

        $projetsNonTrouves = DB::table('projet_eha2')
            ->leftJoin('projet_action_a_mener', 'projet_eha2.CodeProjet', '=', 'projet_action_a_mener.CodeProjet')
            ->whereNull('projet_action_a_mener.CodeProjet')
            ->pluck('projet_eha2.CodeProjet')
            ->toArray();
        // Sélectionner tous les CodeProjet
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

        // Utiliser les régions et les districts récupérés pour filtrer les projets
        $tousLesProjets = DB::table('projet_statut_projet')
            ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'projet_statut_projet.code_projet')
            ->where('code_statut_projet', 2)
            ->whereIn('projet_eha2.code_district', $districts->pluck('code')->toArray())
            ->whereIn('projet_eha2.code_region', $regions->pluck('code')->toArray())
            ->distinct()
            ->pluck('code_projet')
            ->toArray();
        $projetsAvecInfrastructures = DB::table('projet_statut_projet')
            ->where('code_statut_projet', 2)
            ->whereIn('code_projet', function($query) {
                $query->select('CodeProjet')
                      ->from('caracteristique')
                      ->distinct();
            })
            ->distinct()
            ->pluck('code_projet')
            ->toArray();
        $projets = ProjetEha2::whereIn('code_district', $districts->pluck('code')->toArray())
        ->whereIn('code_region', $regions->pluck('code')->toArray())
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
        return view('etatAvancement', ['ecran' => $ecran,'projets'=>$projets,'sous_prefecture'=>$sous_prefecture,'regions'=>$regions,'departements'=>$departements,'etablissements'=>$etablissements,'districts'=>$districts, 'localite'=>$localite,'beneficiairesActions'=>$beneficiairesActions,'projetsPlusieursActions' => $projetsPlusieursActions,
        'tousLesProjets' => $tousLesProjets,'projetsAvecInfrastructures'=>$projetsAvecInfrastructures,'projetsNonTrouves'=>$projetsNonTrouves,'statuts'=>$statuts]);
    }
    //existance de code projet
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
    public function enregistrerBeneficiaires(Request $request) {
        try {
            $donnees = $request->all();
            $beneficiaires = [];

            // Assurez-vous que les deux tableaux ont la même longueur
            if (count($donnees['beneficiaire_code']) !== count($donnees['beneficiaire_type'])) {
                return redirect()->back()->with('',"Les tableaux beneficiaire_code et beneficiaire_type n'ont pas la même taille");
            }

            foreach ($donnees['beneficiaire_code'] as $key => $beneficiaire_code) {
                $data = [
                    'CodeProjet' => $donnees['CodeProjetBene'],
                    'numOrdre' => $donnees['numOrdreBene'],
                    'beneficiaire_id' => $beneficiaire_code,
                    'type_beneficiaire' => $donnees['beneficiaire_type'][$key],
                ];

                // Vérifiez si les données existent déjà dans la base de données
                $existingBeneficiaire = ActionBeneficiairesProjet::where([
                    'CodeProjet' => $data['CodeProjet'],
                    'numOrdre' => $data['numOrdre'],
                    'beneficiaire_id' => $data['beneficiaire_id'],
                    'type_beneficiaire' => $data['type_beneficiaire'],
                ])->first();

                if ($existingBeneficiaire) {
                    // Les données existent déjà, affichez un message ou effectuez d'autres actions si nécessaire
                    return response()->json(['error' => true, 'message' =>"Les données existent déjà pour le bénéficiaire {$existingBeneficiaire->beneficiaire_id}"]);
                    break;
                }

                // Utilisez updateOrCreate pour ajouter ou mettre à jour les données
                ActionBeneficiairesProjet::updateOrCreate(
                    ['CodeProjet' => $data['CodeProjet'], 'numOrdre' => $data['numOrdre'], 'beneficiaire_id' => $data['beneficiaire_id']],
                    $data
                );
            }

            return response()->json(['success' => true, 'message' => 'Enregistrement réussi']);
        } catch (\Exception $e) {
            return response()->json(['error' => false, 'message' => 'Une erreur s\'est produite lors de l\'enregistrement : ' . $e->getMessage()]);
        }
    }

    public function recupererBeneficiaires(Request $request) {
        $codeProjet = $request->input('CodeProjet');
        $numOrdre = $request->input('NumOrdre');

        // Exécutez votre requête SQL pour récupérer les données des bénéficiaires
        $beneficiaires = DB::select('SELECT abp.beneficiaire_id AS code, abp.type_beneficiaire AS type,
        CASE
            WHEN abp.type_beneficiaire = "district" THEN d.libelle
            WHEN abp.type_beneficiaire = "region" THEN r.libelle
            WHEN abp.type_beneficiaire = "departement" THEN dep.libelle
            WHEN abp.type_beneficiaire = "sous_prefecture" THEN sp.libelle
            WHEN abp.type_beneficiaire = "localite" THEN l.libelle
            WHEN abp.type_beneficiaire = "etablissement" THEN e.nom_etablissement
        END AS libelle_nom_etablissement
        FROM action_beneficiaires_projet abp
        LEFT JOIN district d ON abp.beneficiaire_id = d.code AND abp.type_beneficiaire = "district"
        LEFT JOIN region r ON abp.beneficiaire_id = r.code AND abp.type_beneficiaire = "region"
        LEFT JOIN departement dep ON abp.beneficiaire_id = dep.code AND abp.type_beneficiaire = "departement"
        LEFT JOIN sous_prefecture sp ON abp.beneficiaire_id = sp.code AND abp.type_beneficiaire = "sous_prefecture"
        LEFT JOIN localite l ON abp.beneficiaire_id = l.code AND abp.type_beneficiaire = "localite"
        LEFT JOIN etablissement e ON abp.beneficiaire_id = e.code AND abp.type_beneficiaire = "etablissement"
        WHERE abp.type_beneficiaire IN ("district", "region", "departement", "sous_prefecture", "localite", "etablissement")
        AND abp.CodeProjet = :codeProjet AND abp.numOrdre = :numOrdre', ['codeProjet' => $codeProjet, 'numOrdre'=>$numOrdre]
        );

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
}

