<?php

namespace App\Http\Controllers;

use App\Models\ActionBeneficiairesProjet;
use App\Models\ActionMener;
use App\Models\AgenceExecution;
use App\Models\BailleursProjet;
use App\Models\Caracteristique;
use App\Models\Departement;
use App\Models\District;
use App\Models\Ecran;
use App\Models\Localite;
use App\Models\NatureTravaux;
use App\Models\ProjetAgence;
use App\Models\ProjetEha2;
use App\Models\Region;
use App\Models\Sous_prefecture;
use App\Models\SousDomaine;
use App\Models\SousDomaineTypeCaract;
use App\Models\typeCaptage;
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
    public function annexe3(Request $request)
    {
        // 1. Récupérer l'écran en fonction de l'ID passé dans la requête
        $ecran = Ecran::find($request->input('ecran_id'));

        // 2. Récupérer tous les sous-domaines
        $sousDomaines = SousDomaine::all();

        // 3. Extraire les années disponibles depuis les codes projets
        $projets = ProjetEha2::all();
        $years = $projets->pluck('CodeProjet')->map(function ($code) {
            // Extraire l'année en utilisant substr pour prendre les bons caractères
            return substr($code, 17, 4); // Extraire l'année à partir de la position 17, longueur de 4 caractères
        })->unique()->filter(); // Filtrer pour ne conserver que les années distinctes et non nulles

        // 4. Extraire également les codes sous-domaine depuis le CodeProjet (position 11 à 15)
        $codeSousDomaines = $projets->pluck('CodeProjet')->map(function ($code) {
            return substr($code, 12, 4); // Extraire le code sous-domaine à partir de la position 11, longueur de 4 caractères
        })->unique()->filter(); // Filtrer pour avoir des codes distincts

        // 5. Passer les données récupérées à la vue
        return view('annexe3', compact('ecran', 'sousDomaines', 'years', 'codeSousDomaines'));
    }
    public function filterAnnexe(Request $request){
    try {
        // Pour vérifier les données envoyées, ajoutez des logs
        \Log::info('Données reçues: ', $request->all());

        // 1. Validation des entrées
        $validatedData = $request->validate([
            'sous_domaine' => 'required|string',
            'year' => 'required|integer',
            'ecran_id' => 'required|integer'
        ]);

        // Logique pour récupérer les données en fonction des paramètres
        $sousDomaine = $validatedData['sous_domaine'];
        $year = $validatedData['year'];
        $ecranId = $validatedData['ecran_id'];

        $selectedSousDomaineCode = $validatedData['sous_domaine'];
        $selectedYear = $validatedData['year'];
        // 2. Récupérer tous les sous-domaines
        $sousDomaines = SousDomaine::all(); // Initialisation ici

        // 3. Extraire les projets
        $projets = ProjetEha2::all();

        // Filtrer les projets en fonction de l'année et du sous-domaine sélectionnés
        $projetsFiltres = $projets->filter(function ($projet) use ($sousDomaine, $year) {
            $codeSousDomaine = substr($projet->CodeProjet, 12, 4);
            $projectYear = substr($projet->CodeProjet, 17, 4);
            return $codeSousDomaine == $sousDomaine && $projectYear == $year;
        });

        if ($projetsFiltres->isEmpty()) {
            return back()->withErrors(['error' => 'Aucun projet trouvé pour l\'année et le sous-domaine sélectionnés.']);
        }

        // 4. Récupérer les caractéristiques liées aux projets
        $caracteristiques = Caracteristique::whereIn('CodeProjet', $projetsFiltres->pluck('CodeProjet'))->get();

        if ($caracteristiques->isEmpty()) {
            return back()->withErrors(['error' => 'Aucune caractéristique trouvée pour les projets sélectionnés.']);
        }
        // Récupérer les tables associées au sous-domaine sélectionné
        $caracts = SousDomaineTypeCaract::where('CodeSousDomaine', $selectedSousDomaineCode)->get();
        if ($caracts->isEmpty()) {
            return back()->withErrors(['error' => 'Aucun type de table trouvé pour ce sous-domaine.']);
        }
        // 5. Préparer les résultats et les en-têtes
        $resultats = [];
        $headerConfig = [];
        $codeCaractFamilles = $caracteristiques->pluck('CodeCaractFamille');


            foreach ($caracts as $caract) {
                $tableName = $caract->CaractTypeTable;
                $modelClass = "App\\Models\\" . ucfirst($tableName);

                if (!class_exists($modelClass)) {
                    return back()->withErrors(['error' => "Le modèle pour la table $tableName n'existe pas."]);
                }

                $model = app($modelClass);
                $data = $model::whereIn('CodeCaractFamille', $codeCaractFamilles)->get();

                // Remplacement des libellés pour natureTravaux et typeCaptage
                $data->each(function ($row) {
                    if (isset($row->natureTravaux)) {
                        $row->natureTravaux = NatureTravaux::getLibelleByCode($row->natureTravaux) ?: $row->natureTravaux;
                    }

                    if (isset($row->typeCaptage)) {
                        $row->typeCaptage = TypeCaptage::getLibelleByCode($row->typeCaptage) ?: $row->typeCaptage;
                    }
                });

                $columns = \Schema::getColumnListing($model->getTable());
                $columns = array_filter($columns, fn($column) => $column !== 'CodeCaractFamille');

                $headerName = $this->formatHeaderName($tableName);
                $headerConfig[] = [
                    'name' => $headerName,
                    'colspan' => count($columns),
                ];

                $resultats[$headerName] = [
                    'data' => $data,
                    'columns' => $columns,
                ];
            }

            // 6. Récupérer les bénéficiaires liés aux projets
            $beneficiaires = ActionBeneficiairesProjet::whereIn('CodeProjet', $projetsFiltres->pluck('CodeProjet'))->get();

            foreach ($beneficiaires as $beneficiaire) {
                $typeBeneficiaire = $beneficiaire->type_beneficiaire;
                $columns = ['N°', 'Districts', 'Régions', 'Départements', 'Sous-préfectures/Communes'];
                $data = [];

                // Récupérer les données spécifiques en fonction du type de bénéficiaire
                switch ($typeBeneficiaire) {
                    case 'district':
                        $district = District::where('code', $beneficiaire->beneficiaire_id)->first(); // Utilisation de first() pour un seul objet
                        if ($district) {
                            $data[] = [
                                'Districts' => $district->libelle,
                                'Régions' => '',
                                'Départements' => '',
                                'Sous-préfectures/Communes' => ''
                            ];
                        }
                        break;

                    case 'departement':
                        $departement = Departement::where('code', $beneficiaire->beneficiaire_id)->first(); // Utilisation de first()
                        if ($departement) {
                            $region = $departement->region; // Assurez-vous que la relation est définie
                            $district = $region->district; // Assurez-vous que la relation est définie

                            $data[] = [
                                'Districts' => $district->libelle ?? '', // Utilisation de ?? pour éviter les erreurs si null
                                'Régions' => $region->libelle ?? '',
                                'Départements' => $departement->libelle,
                                'Sous-préfectures/Communes' => ''
                            ];
                        }
                        break;

                    case 'region':
                        $region = Region::where('code', $beneficiaire->beneficiaire_id)->first(); // Utilisation de first()
                        if ($region) {
                            $district = $region->district; // Assurez-vous que la relation est définie
                            $data[] = [
                                'Districts' => $district->libelle ?? '',
                                'Régions' => $region->libelle,
                                'Départements' => '',
                                'Sous-préfectures/Communes' => ''
                            ];
                        }
                        break;

                    case 'sous_prefecture':
                        $sousPrefecture = Sous_prefecture::where('code', $beneficiaire->beneficiaire_id)->first(); // Utilisation de first()
                        if ($sousPrefecture) {
                            $departement = $sousPrefecture->departement; // Vérifiez que la relation est correcte
                            $region = $departement->region; // Vérifiez que la relation est correcte
                            $district = $region->district; // Vérifiez que la relation est correcte

                            $data[] = [
                                'Districts' => $district->libelle ?? '',
                                'Régions' => $region->libelle,
                                'Départements' => $departement->libelle,
                                'Sous-préfectures/Communes' => $sousPrefecture->libelle
                            ];
                        }
                        break;

                    case 'localite':
                        $localite = Localite::where('code', $beneficiaire->beneficiaire_id)->first(); // Utilisation de first()
                        if ($localite) {
                            $sousPrefecture = Sous_prefecture::find($localite->code_sous_prefecture); // Utilisation de find() pour récupérer la sous-préfecture
                            if ($sousPrefecture) {
                                $departement = $sousPrefecture->departement; // Vérifiez que la relation est correcte
                                $region = $departement->region; // Vérifiez que la relation est correcte
                                $district = $region->district; // Vérifiez que la relation est correcte

                                $data[] = [
                                    'Districts' => $district->libelle ?? '',
                                    'Régions' => $region->libelle,
                                    'Départements' => $departement->libelle,
                                    'Sous-préfectures/Communes' => $localite->libelle
                                ];
                            }
                        }
                        break;

                    case 'etablissement':
                        // Ajouter la logique pour les établissements ici si nécessaire
                        break;

                    default:
                        // Gérer le cas par défaut si nécessaire
                        break;
                }


                $resultats[$typeBeneficiaire] = [
                    'data' => $data,
                    'columns' => $columns,
                ];

                $headerConfig[] = [
                    'name' => ucfirst($typeBeneficiaire),
                    'colspan' => count($columns),
                ];
            }

                  // Retourner une réponse JSON
            return response()->json([
                'success' => true,
                'headerConfig' => $headerConfig,
                'resultats' => $resultats
            ]);
            return view('annexe3', compact('headerConfig','resultats'));
        } catch (\Exception $e) {
        \Log::error('Erreur dans filterAnnexe: ' . $e->getMessage());
        return response()->json(['error' => 'Une erreur est survenue lors du traitement : ' . $e->getMessage()], 500);
        }
    }












    private function formatHeaderName($type)
    {
        // Supprimer le préfixe 'caract' et transformer en format lisible
        if (strpos($type, 'caract') === 0) {
            $cleanedName = substr($type, 6); // Enlever 'caract'
            // Remplacer les underscores par des espaces et mettre en forme
            $cleanedName = str_replace('_', ' ', $cleanedName);
            return ucfirst($cleanedName);
        }
        return $type; // Retourner tel quel si le préfixe ne correspond pas
    }
}

