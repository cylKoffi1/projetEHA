<?php

namespace App\Http\Controllers;

use App\Models\ActionBeneficiairesProjet;
use App\Models\ActionMener;
use App\Models\ActionMenerFinancier;
use App\Models\AgenceExecution;
use App\Models\Approbateur;
use App\Models\AvoirExpertise;
use App\Models\Bailleur;
use App\Models\BailleursProjet;
use App\Models\Beneficiaire;
use App\Models\Departement;
use App\Models\Devise;
use App\Models\District;
use App\Models\Domaine;
use App\Models\CourDeau;
use App\Models\CouvrirRegion;
use App\Models\Ecran;
use App\Models\Entreprise;
use App\Models\Etablissement;
use App\Models\EtudeProject;
use App\Models\EtudeProjectFile;
use App\Models\Infrastructure;
use App\Models\Localite;
use App\Models\Ministere;
use App\Models\MinistereProjet;
use App\Models\Motifs_changerchefprojet;
use App\Models\Motifs_changermaitreoeuvre;
use App\Models\NatureTravaux;
use App\Models\NiveauAccesDonnees;
use App\Models\Particulier;
use App\Models\Pays;
use App\Models\Personnel;
use App\Models\ProjetActionAMener;
use App\Models\ProjetAgence;
use App\Models\ProjetChefProjet;
use App\Models\ProjetEha2;
use App\Models\ProjetStatutProjet;
use App\Models\Reattribution;
use App\Models\Region;
use App\Models\Sous_prefecture;
use App\Models\SousDomaine;
use App\Models\StructureRattachement;
use App\Models\Task;
use App\Models\TypeEtablissement;
use App\Models\TypeFinancement;
use App\Models\UniteMesure;
use App\Models\uniteVolume;
use App\Models\User;
use App\Models\UtilisateurDomaine;
use App\Models\Validations;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException as ValidationValidationException;

class ProjetController extends Controller
{



    //////////////////////////////////////DEFINITION DE PROJET////////////////////////////////
    public function projet(Request $request)
    {
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
                $codeRegions =$lastCouvrirRegion->code_region;
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

        $codeProjet = $request->input('code_projet');
        $users = User::all();
        $projet = ProjetEha2::all();
        $pays = Pays::orderBy('nom_fr_fr', 'asc')->get();
       $ecran = Ecran::find($request->input('ecran_id'));
        $departements = Departement::whereHas('region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        $sous_prefectures = Sous_prefecture::whereHas('departement.region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        $localites = Localite::whereHas('sous_prefecture.departement.region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        $domaines = Domaine::all();
        $bailleurs = Bailleur::orderBy('libelle_long', 'asc')->get();
        $devises = Devise::all();
        $sous_domaines = SousDomaine::all();
        $natureTravaux = NatureTravaux::all();
        $types_etablissement = TypeEtablissement::all();
        $actionMener = ActionMener::all();
        $unite_mesure = UniteMesure::all();
        $unite_volume = uniteVolume::all();
        $sous_prefecture = Sous_prefecture::all();
        $beneficiairesActions = ActionBeneficiairesProjet::where('CodeProjet', $codeProjet)->get();
        $infrastructure = Infrastructure::all();
        $agence = AgenceExecution::all();
        $ministere = Ministere::all();
        $collectivite = Bailleur::where('code_type_bailleur', '06')->get();
        $courEau = CourDeau::all();
        $etablissement = Etablissement::all();
        $localite = Localite::all();
        $personnel = Personnel::all();
        $financements = TypeFinancement::all();
        $user = User::where('code_personnel',auth()->user()->code_personnel);

        $sous_domaine_Info = AvoirExpertise::where('code_personnel', auth()->user()->code_personnel )
        ->join('sous_domaine', 'sous_domaine.code', '=', 'avoir_expertise.sous_domaine')
        ->select('libelle', 'code')
        ->get();

        $domaine_Info = UtilisateurDomaine::where('code_personnel', auth()->user()->code_personnel )
        ->join('domaine_intervention', 'domaine_intervention.code', '=', 'utilisateur_domaine.code_domaine')
        ->select('libelle', 'code')
        ->get();

        return view('projet', ['sous_domaine_Info'=>$sous_domaine_Info,'domaine_Info'=>$domaine_Info,'domaine_Info'=>$domaine_Info,'sous_domaine_Info'=>$sous_domaine_Info,'users'=>$users,'niveauAcces'=>$niveauAcces,'sous_prefecture'=>$sous_prefecture,'beneficiairesActions'=>$beneficiairesActions,'users' => $users,'ecran' => $ecran,'projets'=>$projet,'domaines' => $domaines,        'etablissements'=>$etablissement, 'natureTravaux' => $natureTravaux, 'types_etablissement' => $types_etablissement,
        'devises' => $devises,'sous_domaines' => $sous_domaines, 'bailleurs' => $bailleurs,'localites' => $localites,
        'sous_prefectures' => $sous_prefectures, 'departements' => $departements, 'pays' => $pays, 'districts' => $districts,
        'regions' => $regions, 'actionMener' => $actionMener, 'unite_mesure' => $unite_mesure, 'uniteVol' => $unite_volume,
        'infrastruc' => $infrastructure, 'agence' => $agence, 'ministere' => $ministere, 'collectivite'=>$collectivite, 'localite'=> $localite, 'personnel' => $personnel, 'courEau' => $courEau, 'financements'=>$financements]);
    }

    public function getRegions($districtCode)
    {
        $regions = Region::where('code_district', $districtCode)->get();

        return response()->json($regions);
    }
    public function getSousDomaines($domaineCode)
    {
        $sousDomaines = SousDomaine::where('code_domaine', $domaineCode)->get();

        return response()->json($sousDomaines);
    }


    public function getCours_eau(Request $request, $eauId)
    {
        $courEau = CourDeau::where('code', $eauId)->get();

        // Créez un tableau d'options pour les sous domaines
        $courdeauOptions = [];
        foreach ($courEau as $courdeau) {
            $courdeauOptions[$courdeau->code] = $courdeau->libelle;
        }

        return response()->json(['courEau' => $courdeauOptions]);
    }
    public function getInsfrastructures(Request $request, $domaineId)
    {
        // Utilisez le modèle District pour récupérer les districts en fonction du pays
        $insfrastructures = Infrastructure::where('code_domaine', $domaineId)->get();

        // Créez un tableau d'options pour les districts
        $insfrastructuresOptions = [];
        foreach ($insfrastructures as $insfrastructure) {
            $insfrastructuresOptions[$insfrastructure->code] = $insfrastructure->libelle;
        }

        return response()->json(['insfrastructures' => $insfrastructuresOptions]);
    }


    public function getProjetData()
    {
        $projetData = ProjetEha2::all();
        return response()->json($projetData);
    }
    public function Projets(Request $request)
    {
       $ecran = Ecran::find($request->input('ecran_id'));
        $projets = ProjetEha2::select(
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


        $districts = DB::table('district')
        ->join('region', 'region.code_district', '=', 'district.code')
        ->join('projet_eha2', 'projet_eha2.code_region', '=', 'region.code')
        ->select('district.libelle')
        ->get();
        $Regions = Region::all();
        $ProjetStatutProjets = ProjetStatutProjet::all();
        $Statuts = DB::table('projet_statut_projet')
        ->join('statut_projet', 'statut_projet.code', '=', 'projet_statut_projet.code_statut_projet')
        ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'projet_statut_projet.code_projet')
        ->select('projet_statut_projet.code', 'projet_eha2.CodeProjet', 'projet_statut_projet.code_statut_projet as codeSStatu', 'projet_statut_projet.date', 'statut_projet.libelle as statut_libelle')
        ->get();

        return view('consultation', ['ProjetStatutProjets'=>$ProjetStatutProjets,'ecran' => $ecran,
        'districts'=>$districts,'Regions'=>$Regions,'Statuts'=>$Statuts  , 'projets'=>$projets]);
    }





    public function ProjetDistrict(Request $request){
        $projets = ProjetEha2::select(
            'projet_eha2.*',
            'projet_eha2.CodeProjet',
            'district.libelle as district_libelle',
            'region.libelle as region_libelle',
            'domaine_intervention.libelle as domaine_libelle',
            'sous_domaine.libelle as sous_domaine_libelle',
            'projet_eha2.cout_projet',
            'devise.libelle as devise_libelle'
        )
        ->join('district', 'district.code', '=', 'projet_eha2.code_district')
        ->join('region', 'region.code', '=', 'projet_eha2.code_region')
        ->join('domaine_intervention', 'domaine_intervention.code', '=', 'projet_eha2.code_domaine')
        ->join('sous_domaine', 'sous_domaine.code', '=', 'projet_eha2.code_sous_domaine')
        ->join('devise', 'devise.code', '=', 'projet_eha2.code_devise')
        ->whereHas('district.region', function ($query) {
            $query->where('id', config('app_settings.id_region'));
        })
        ->get();
       $ecran = Ecran::find($request->input('ecran_id'));
        $districts = District::join('projet_eha2', 'district.code', '=', 'projet_eha2.code_district')
                    ->from('district')
                    ->get('district.libelle');
        $regions = Region::whereHas('district', function ($query) {
            $query->where('id_pays', config('app_settings.id_pays'));
        })->get();

        $localites = Localite::whereHas('sous_prefecture.departement.region.district.pays', function ($query) {
            $query->where('id', config('app_settings.id_pays'));
        })->get();
        $domaines = Domaine::all();
        $bailleurs = Bailleur::orderBy('libelle_long', 'asc')->get();
        $devises = Devise::orderBy('libelle', 'asc')->get();
        $sous_domaines = SousDomaine::all();
        $natureTravaux = NatureTravaux::all();
        $types_etablissement = TypeEtablissement::all();
        $actionMener = ActionMener::all();
        $unite_mesure = UniteMesure::all();
        $unite_volume = uniteVolume::all();
        $beneficiare = Beneficiaire::all();
        $infrastructure = Infrastructure::all();

        return view('projetDistricts', ['projets'=>$projets, 'ecran' => $ecran,'districts'=>$districts,'domaines' => $domaines,'natureTravaux' => $natureTravaux, 'types_etablissement' => $types_etablissement,

        'devises' => $devises,'sous_domaines' => $sous_domaines, 'bailleurs' => $bailleurs,'localites' => $localites,
        'regions' => $regions, 'actionMener' => $actionMener, 'unite_mesure' => $unite_mesure, 'uniteVol' => $unite_volume, 'beneficaire'=> $beneficiare,
        'infrastruc' => $infrastructure]);
    }
    function verifierCodeProjet(Request $request)
    {
        $codeProjet = $request->input('code');

        // Vérifier si un code similaire existe déjà
        $projetExist = ProjetEha2::where('CodeProjet', 'LIKE', $codeProjet . '%')->exists();

        if ($projetExist) {
            // Si un code similaire existe, récupérer le dernier rang
            $dernierRang = ProjetEha2::where('CodeProjet', 'LIKE', $codeProjet . '%')
                ->max(DB::raw('CAST(SUBSTRING(CodeProjet, -2) AS UNSIGNED)'));

            return response()->json(['existe' => true, 'dernierRang' => $dernierRang]);
        } else {
            // Si aucun code similaire n'existe, retourner que le code n'existe pas
            return response()->json(['existe' => false]);
        }
    }
    public function votreFonction(Request $request)
    {
        // Logique de votre fonction ici
        $beneficiaireCode = $request->input('beneficiaire_code');
        $libelle = $request->input('libelle');
        $type = $request->input('type');


        return response()->json(['message' => 'Traitement effectué avec succès']);
    }

    public function store(Request $request)
    {
        {
            // Valider les données du formulaire
            $validatedData = $request->validate([
                'code_projet' => 'required|unique:projet_eha2,CodeProjet|max:255',
                'code_statut' => 'required',
                'district' => 'required',
                'region' => 'required',
                'domaine' => 'required',
                'sous_domaine' => 'required',
                'date_debut' => 'required|date',
                'date_fin' => 'required|date|after:date_debut',
                'cout' => 'required|numeric|min:0',
                'deviseProject' => 'required|numeric',
            ]);

            // Début de la transaction pour assurer l'intégrité des données
            DB::beginTransaction();

            try {
                // Enregistrement des données dans la table ProjetEha2
                ProjetEha2::create([
                    'CodeProjet' => $request->code_projet,
                    'code_domaine' => $request->domaine,
                    'code_sous_domaine' => $request->sous_domaine,
                    'code_region' => $request->region,
                    'Date_demarrage_prevue' => $request->date_debut,
                    'date_fin_prevue' => $request->date_fin,
                    'cout_projet' => $request->cout,
                    'code_devise' => $request->deviseProject,
                    'code_district' => $request->district,
                    // Ajoutez d'autres champs ici
                ]);

                // Enregistrement des données dans la table ProjetStatutProjet
                ProjetStatutProjet::create([
                    'code_projet' => $request->code_projet,
                    'code_statut_projet' => $request->code_statut,
                    'date' => Carbon::now()->year,
                ]);
                // Enregistrement des données dans la table ProjetActionAMener
                foreach ($request->nordre as $key => $nordre) {
                    ProjetActionAMener::create([
                        'CodeProjet' => $request->code_projet,
                        'Num_ordre' => $nordre,
                        'Action_mener' => $request->actionMener[$key],
                        'Quantite' => $request->quantite[$key],
                        'Unite_mesure' => $request->uniteMesure[$key],
                        'Infrastrucrues' => $request->infrastructure[$key],
                    ]);
                }
                // Enregistrement des données dans la table ActionBeneficiairesProjet
                foreach ($request->beneficiaire_code as $key => $beneficiaire_code) {
                    ActionBeneficiairesProjet::create([
                        'CodeProjet' => $request->code_projet,
                        'numOrdre' => $request->nordre[$key],
                        'beneficiaire_id' => $beneficiaire_code,
                        'type_beneficiaire' => $request->beneficiaire_type[$key],
                    ]);
                }
                // Enregistrement des données dans la table MinistereProjet
                foreach ($request->ministere_code as $key => $ministere_code) {
                    MinistereProjet::create([
                        'code_ministere' => $ministere_code,
                        'codeProjet' => $request->code_projet,
                    ]);
                }

                // Enregistrement des données dans la table BailleursProjet
                foreach ($request->bailleur_code as $key => $bailleur_code) {
                    BailleursProjet::create([
                        'code_bailleur' => $bailleur_code,
                        'code_projet' => $request->code_projet,
                        'code_devise' => $request->bailleur_devise[$key],
                        'montant' => $request->montant_bailleur[$key],
                        'commentaire' => $request->bailleur_commentaire[$key],
                        'partie' => $request->bailleur_partie[$key],
                        'type_financement' => $request->bailleur_financement[$key],
                        'Num_ordre' => $request->bailleur_nordre[$key],
                    ]);
                }


                // Enregistrement des données dans la table ActionMenerFinancier
                foreach ($request->bailleur_code as $key => $bailleur_code) {
                    ActionMenerFinancier::create([
                        'code_projet' => $request->code_projet,
                        'Num_ordre' => $request->bailleur_nordre[$key],
                        'code_bailleur' => $bailleur_code,
                    ]);
                }

                // Enregistrement des données dans la table ProjetAgence
                foreach ($request->inputState as $key => $agence_code) {
                    ProjetAgence::create([
                        'code_projet' => $request->code_projet,
                        'code_agence' => $agence_code,
                        'niveau' => $request->niveau[$key],
                    ]);
                }

                // Enregistrement des données dans la table ProjetChefProjet
                foreach ($request->chefProjet_code as $key => $code_personnel) {
                    ProjetChefProjet::create([
                        'code_projet' => $request->code_projet,
                        'code_personnel' => $code_personnel,
                        'date' => now()->year,
                    ]);
                }


                DB::commit();

                return response()->json(['success' => true, 'message' => 'Formulaire enregistré avec succès.']);
            } catch (\Exception $e) {
                // En cas d'erreur, annuler la transaction et renvoyer une réponse d'erreur
                DB::rollBack();
                return response()->json(['error' => true, 'message' => 'Erreur lors de l\'enregistrement du formulaire. Détails: '  ]);
            }
        }

    }




    ////////////////EDITIONS//////////////////
    public function editionProjet(Request $request)
    {
        $pay = Pays::find(config('app_settings.id_pays'));
       $ecran = Ecran::find($request->input('ecran_id'));
        return view('editionProjet',['pay'=>$pay,'ecran' => $ecran,]);
    }
    public function getTable(Request $request)
    {
        $type = $request->input('type');

        switch ($type) {
            case 'action_beneficiaires_projet':
                $data = ActionBeneficiairesProjet::all();
                break;
            case 'projet_agence':
                $data = ProjetAgence::all()->map(function ($item) {
                    $item->niveau_libelle = ($item->niveau == 1) ? 'Régie Financière' : 'Maître d\'Œuvre';
                    return $item;
                });
                break;

            case 'projet_action_a_mener':
                $data = ProjetActionAMener::join('action_mener', 'projet_action_a_mener.Action_mener', '=', 'action_mener.code')
                    ->select('projet_action_a_mener.CodeProjet', 'projet_action_a_mener.Num_ordre', 'action_mener.libelle as action_mener', 'projet_action_a_mener.Quantite', 'projet_action_a_mener.Unite_mesure', 'projet_action_a_mener.Infrastrucrues as Infrastructrure')
                    ->get();
                break;


            case 'projet_chef_projet':
                $data = ProjetChefProjet::all();
                break;
            case 'ministere_projet':
                $data = MinistereProjet::all();
                break;
            case 'bailleur_projet':
                $data = BailleursProjet::join('devise', 'bailleurs_projets.code_devise', '=', 'devise.code')
                    ->select('bailleurs_projets.code_bailleur as code bailleur','bailleurs_projets.code_projet as code projet',
                     'devise.code_long as devise','bailleurs_projets.montant', 'bailleurs_projets.public', 'bailleurs_projets.partie as en partie')
                    ->get();
                break;

            default:
                $data = [];
        }

        return response()->json($data);
    }


    public function reatributionProjet(Request $request){
        $ecran = Ecran::find($request->input('ecran_id'));
        $projets = ProjetEha2::all();
        $agences = ProjetAgence::where('niveau', 2)->get();
        $agenceExe = AgenceExecution::all();
        $chefs = ProjetChefProjet::all();

        $personnel = User::all();
        $reattributions = Reattribution::all();
        $changerChef = Motifs_changerchefprojet::all();
        $changerMaitre = Motifs_changermaitreoeuvre::all();

        return view('changementChefProjet', compact('ecran','projets','agenceExe', 'agences', 'chefs', 'reattributions','personnel','changerChef','changerMaitre'));
    }
    public function storereat(Request $request)
    {
        try {
            $ecran = Ecran::find($request->input('ecran_id'));
            $validatedData = $request->validate([
                'code_projet' => 'required',
                'changement' => 'required|date',
                'type_reattribution' => 'required',
                'chef' => 'nullable|string',
                'maitre' => 'nullable|string',
                'motifs' => 'array',
                'motif' => 'nullable|string'
            ]);

            // Créer une réattribution sans le champ 'motif'
            $reattributionData = [
                'code_projet' => $validatedData['code_projet'],
                'changement' => $validatedData['changement'],
                'type_reattribution' => $validatedData['type_reattribution'] ?? '',
                'motifs' => json_encode($validatedData['motifs'] ?? []),
                'motif' => $validatedData['motif'] ?? '',
                'code_chef' => $validatedData['chef'] ?? '',
                'code_agence' => $validatedData['maitre'] ?? ''
            ];

            // Ajouter les champs 'motif', 'code_chef' et 'code_agence' uniquement s'ils sont présents
            if (isset($validatedData['motif'])) {
                $reattributionData['motif'] = $validatedData['motif'];
            } else {
                $reattributionData['motif'] = '';
            }
            if (isset($validatedData['chef'])) {
                $reattributionData['code_chef'] = $validatedData['chef'];
            } else {
                $reattributionData['code_chef'] = '';
            }
            if (isset($validatedData['maitre'])) {
                $reattributionData['code_agence'] = $validatedData['maitre'];
            } else {
                $reattributionData['code_agence'] = '';
            }


           // Vérifier et créer un nouveau chef de projet si nécessaire
            if ($validatedData['type_reattribution'] === 'chef_projet' && isset($validatedData['chef'])) {
                $existingChef = ProjetChefProjet::where('code_projet', $validatedData['code_projet'])->first();

                if (!$existingChef) {
                    ProjetChefProjet::create([
                        'code_projet' => $validatedData['code_projet'],
                        'code_personnel' => $validatedData['chef'],
                        'date' => $validatedData['changement']
                    ]);
                }
            }

            // Vérifier et créer une nouvelle agence (maître d'œuvre) si nécessaire
            if ($validatedData['type_reattribution'] === 'maitre_oeuvre' && isset($validatedData['maitre'])) {
                $existingAgence = ProjetAgence::where('code_projet', $validatedData['code_projet'])
                                            ->where('niveau', 2)
                                            ->first();

                if (!$existingAgence) {
                    ProjetAgence::create([
                        'code_projet' => $validatedData['code_projet'],
                        'code_agence' => $validatedData['maitre'],
                        'niveau' => 2,
                        'date' => $validatedData['changement']
                    ]);
                }
            }

            // Créer une réattribution seulement si nécessaire
            if ($reattributionData['motif'] == '' && $reattributionData['motifs'] == '' && ($validatedData['chef'] || $validatedData['maitre'])) {
                Reattribution::create($reattributionData);
            }

            return redirect()->back()->with('success', 'Réattribution effectuée avec succès');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function updatereat(Request $request, $id)
    {
        $reattribution = Reattribution::findOrFail($id);
        $reattribution->update($request->all());
        return redirect()->route('reattribution.index')->with('success', 'Réattribution mise à jour avec succès');
    }

    public function destroyreat($id)
    {

        Reattribution::findOrFail($id)->delete();
        return redirect()->route('reattribution.index')->with('success', 'Réattribution supprimée avec succès');
    }
    public function getProjectDetails($codeProjet)
    {
        // Récupérer le projet par son code
        $projet = ProjetEha2::where('CodeProjet', $codeProjet)->first();

        if (!$projet) {
            return response()->json([
                'chef' => null,
                'maitre' => null
            ]);
        }

        // Récupérer les chefs de projet liés au projet spécifique
        $chefs = ProjetChefProjet::where('code_projet', $projet->CodeProjet)->latest('date')->get();
        $chefCodes = $chefs->pluck('code_personnel')->toArray(); // Liste des codes de personnel des chefs de projet

        // Récupérer les agences d'exécution liées aux projets spécifiques et de niveau 2
        $agences = ProjetAgence::where('niveau', 2)
                               ->where('code_projet', $projet->CodeProjet)
                               ->latest('date')
                               ->get();
        $maitreCodes = $agences->pluck('code_agence')->toArray(); // Liste des codes d'agence des agences d'exécution

        // Récupérer les détails des utilisateurs (chefs de projet) en fonction des codes de personnel
        $chefsDetail = User::whereIn('code_personnel', $chefCodes)->get();
        if($chefsDetail){

            $chefsDetails = Personnel::where('code_personnel', $chefCodes)->get();
        }
        // Récupérer les détails des agences d'exécution en fonction des codes d'agence
        $maitresDetails = AgenceExecution::whereIn('code_agence_execution', $maitreCodes)->get();

        return response()->json([
            'chef' => $chefsDetails,
            'maitre' => $maitresDetails
        ]);
    }



}

