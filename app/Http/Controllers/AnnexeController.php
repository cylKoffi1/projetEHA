<?php

namespace App\Http\Controllers;

use App\Models\ActionBeneficiairesProjet;
use App\Models\ActionMener;
use App\Models\AgenceExecution;
use App\Models\BailleursProjet;
use App\Models\Caracteristique;
use App\Models\CaractInstrumentation;
use App\Models\caractlatrinefamillial;
use App\Models\CaractLatrinePublique;
use App\Models\CaractOuvrageAssainiss;
use App\Models\CaractOuvrageCaptageEau;
use App\Models\CaractReseau;
use App\Models\CaractReseauCollect;
use App\Models\CaractReservoir;
use App\Models\CaractUniteTraitement;
use App\Models\Departement;
use App\Models\District;
use App\Models\Domaine;
use App\Models\Ecran;
use App\Models\FamilleInfrastructure;
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
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\Log;

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
        $Domaines = Domaine::all();
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

        //famille d'infrastructurex

        // Supposons que $sousDomaineCode contienne le code du sous-domaine (par exemple, '0101')

        $sousDomaineCode = $request->input('sous_domaine');
        $familles = FamilleInfrastructure::select('famille_infrastructure.nom_famille', 'famille_infrastructure.code as famille_code')
            ->distinct()
            ->join('infrastructures as inf', 'inf.code_famille_infrastructure', '=', 'famille_infrastructure.code')
            ->join('domaine_intervention as dom', 'dom.code', '=', 'inf.code_domaine')
            ->join('sous_domaine as sdom', 'sdom.code_domaine', '=', 'dom.code')
            ->where('sdom.code', $sousDomaineCode) // Utilisation de la variable avec les guillemets
            ->get();





        // 5. Passer les données récupérées à la vue
        return view('annexe3', compact('ecran','Domaines', 'sousDomaines', 'years', 'codeSousDomaines', 'familles'));
    }
    public function getFamilles(Request $request)
    {
        $sousDomaineCode = $request->input('sous_domaine');

        $familles = FamilleInfrastructure::select('famille_infrastructure.nom_famille', 'famille_infrastructure.code as famille_code')
            ->distinct()
            ->join('infrastructures as inf', 'inf.code_famille_infrastructure', '=', 'famille_infrastructure.code')
            ->join('domaine_intervention as dom', 'dom.code', '=', 'inf.code_domaine')
            ->join('sous_domaine as sdom', 'sdom.code_domaine', '=', 'dom.code')
            ->where('sdom.code', $sousDomaineCode)
            ->get();

        return response()->json(['familles' => $familles]);
    }
    public function getSousDomaines(Request $request)
    {
        $domaineCode = $request->input('domaine');

        // Récupérer les sous-domaines liés au domaine
        $sousDomaines = SousDomaine::where('code_domaine', $domaineCode)->get();

        // Retourner les sous-domaines au format JSON
        return response()->json(['sousDomaines' => $sousDomaines]);
    }
    public function getCaracteristiqueData(Request $request)
    {
        try {
            // Journalisation pour déboguer les valeurs d'entrée du client
            Log::info('Requête reçue: ', $request->all());

            // Récupérer la caractéristique basée sur 'famille'
            $caracteristique = Caracteristique::where('CodeFamille', $request->input('famille'))->first();

            if (!$caracteristique) {
                Log::warning('Aucune caractéristique trouvée pour la famille: ' . $request->input('famille'));
                return response()->json(['error' => 'Aucune caractéristique trouvée'], 404);
            }

            Log::info('Caractéristique trouvée: ', ['caracteristique' => $caracteristique]);

            $codeCaractFamille = $caracteristique->CodeCaractFamille;
            $subTableData = null;
            $tableName = null;

            // Exemple pour `caractunitetraitement`
            if (CaractUniteTraitement::where('CodeCaractFamille', $codeCaractFamille)->exists()) {
                Log::info('Table CaractUniteTraitement trouvée pour CodeCaractFamille: ' . $codeCaractFamille);
                $subTableData = CaractUniteTraitement::with(['uniteTraitement', 'natureTravaux'])
                    ->where('CodeCaractFamille', $codeCaractFamille)->get();

                    $formattedData = $subTableData->map(function ($item) {
                        return [
                        'nature'=> $item->natureTravaux->libelle ?? 'Non spécifié',
                        'unite'=>$item->uniteTraitement->libelle ?? 'Non spécifié',
                        'debitCapacite' => $item->debitCapacite,
                        ];
                    });
                $tableName = 'Unité de traitement';
            }

            // Exemple pour `caractreservoir`
            elseif (CaractReservoir::where('CodeCaractFamille', $codeCaractFamille)->exists()) {
                Log::info('Table CaractReservoir trouvée pour CodeCaractFamille: ' . $codeCaractFamille);
                $subTableData = CaractReservoir::with(['typeCaptage', 'natureTravaux', 'materielStockage'])
                    ->where('CodeCaractFamille', $codeCaractFamille)->get();
                    $formattedData = $subTableData->map(function ($item) {
                        return [
                        'nature'=> $item->natureTravaux->libelle ?? 'Non spécifié',
                        'captage'=>$item->typeCaptage->libelle ?? 'Non spécifié',
                        'Stockage' => $item->materielStockage->libelle ?? 'Non spécifié',
                        'capacite' => $item->capacite
                        ];
                    });

                $tableName = 'Réservoir';
            }
            // Exemple pour `caractreseaucollecttransport`
            elseif (CaractReseauCollect::where('CodeCaractFamille', $codeCaractFamille)->exists()) {
                Log::info('Table CaractReseauCollect trouvée pour CodeCaractFamille: ' . $codeCaractFamille);
                $subTableData = CaractReseauCollect::with(['typeOuvrage', 'typeReseaux', 'natureTravaux'])
                    ->where('CodeCaractFamille', $codeCaractFamille)->get();

                    // Transformez les données pour ajouter les libellés
                $formattedData = $subTableData->map(function ($item) {
                    return [
                        'Reseaux' => $item->typeReseaux->libelle ?? 'Non spécifié',
                        'nature' => $item->natureTravaux->libelle ?? 'Non spécifié',
                        'ouvrage' => $item->typeOuvrage->libelle ?? 'Non spécifié',
                        'classe' => $item->classe,
                        'lineaire' => $item->lineaire,
                    ];
                });
                $tableName = 'Réseau de collecte et de transport';
            }
            // Exemple pour `caractreseau`
            elseif (CaractReseau::where('CodeCaractFamille', $codeCaractFamille)->exists()) {
                Log::info('Table CaractReseau trouvée pour CodeCaractFamille: ' . $codeCaractFamille);
                $subTableData = CaractReseau::with(['ouvrageTransport', 'materielStockage', 'natureTravaux'])
                    ->where('CodeCaractFamille', $codeCaractFamille)->get();

                    // Transformez les données pour ajouter les libellés
                    $formattedData = $subTableData->map(function ($item) {
                        return [
                            'captage' => $item->ouvrageTransport->libelle ?? 'Non spécifié',
                            'stockage' => $item->materielStockage->libelle ?? 'Non spécifique',
                            'nature' => $item->natureTravaux->libelle ?? 'Non spécifié',
                            'Diametre' => $item->Diametre,
                            'lineaire' => $item->lineaire,
                        ];
                    });
                $tableName = 'Réseau';
            }
            // Exemple pour `caractouvragecaptageeau`
            elseif (CaractOuvrageCaptageEau::where('CodeCaractFamille', $codeCaractFamille)->exists()) {
                Log::info('Table CaractOuvrageCaptageEau trouvée pour CodeCaractFamille: ' . $codeCaractFamille);

                // Chargez les relations avec 'with()'
                $subTableData = CaractOuvrageCaptageEau::with(['typeCaptage', 'natureTravaux'])
                    ->where('CodeCaractFamille', $codeCaractFamille)
                    ->get(); // On ne spécifie pas les colonnes ici

                // Transformez les données pour ajouter les libellés
                $formattedData = $subTableData->map(function ($item) {
                    return [
                        'captage' => $item->typeCaptage->libelle ?? 'Non spécifié',
                        'nature' => $item->natureTravaux->libelle ?? 'Non spécifié',
                        'debitCapacite' => $item->debitCapacite,
                        'profondeur' => $item->profondeur,
                    ];
                });

                $tableName = 'Ouvrage de captage d\'eau';
            }
            // Exemple pour `caractouvrageassainiss`
            elseif (CaractOuvrageAssainiss::where('CodeCaractFamille', $codeCaractFamille)->exists()) {
                Log::info('Table CaractOuvrageAssainiss trouvée pour CodeCaractFamille: ' . $codeCaractFamille);
                $subTableData = CaractOuvrageAssainiss::with(['typeOuvrage', 'natureTravaux'])
                    ->where('CodeCaractFamille', $codeCaractFamille)->get();

                    // Transformez les données pour ajouter les libellés
                    $formattedData = $subTableData->map(function ($item) {
                        return [
                            'ouvrage' => $item->typeOuvrage->libelle ?? 'Non spécifié',
                            'nature' => $item->natureTravaux->libelle ?? 'Non spécifié',
                            'capacite' => $item->capaciteVolume,
                        ];
                    });
                $tableName = 'Ouvrage d\'assainissement';
            }
            // Exemple pour `caractlatrinepublique`
            elseif (CaractLatrinePublique::where('CodeCaractFamille', $codeCaractFamille)->exists()) {
                Log::info('Table CaractLatrinePublique trouvée pour CodeCaractFamille: ' . $codeCaractFamille);
                $subTableData = CaractLatrinePublique::with(['natureTravaux'])
                    ->where('CodeCaractFamille', $codeCaractFamille)->get();

                    // Transformez les données pour ajouter les libellés
                    $formattedData = $subTableData->map(function ($item) {
                        return [
                            'nature' => $item->natureTravaux->libelle ?? 'Non spécifié',
                            'nombre' => $item->nombre,
                        ];
                    });
                $tableName = 'Latrine publique';
            }
            // Exemple pour `caractlatrinefamillial`
            elseif (CaractLatrineFamillial::where('CodeCaractFamille', $codeCaractFamille)->exists()) {
                Log::info('Table CaractLatrineFamillial trouvée pour CodeCaractFamille: ' . $codeCaractFamille);
                $subTableData = CaractLatrineFamillial::with(['natureTravaux'])
                    ->where('CodeCaractFamille', $codeCaractFamille)->get();

                    // Transformez les données pour ajouter les libellés
                    $formattedData = $subTableData->map(function ($item) {
                        return [
                            'nature' => $item->natureTravaux->libelle ?? 'Non spécifié',
                            'nombre' => $item->nombre,
                        ];
                    });
                $tableName = 'Latrine familiale';
            }
            // Exemple pour `caractinstrumentation`
            elseif (CaractInstrumentation::where('CodeCaractFamille', $codeCaractFamille)->exists()) {
                Log::info('Table CaractInstrumentation trouvée pour CodeCaractFamille: ' . $codeCaractFamille);
                $subTableData = CaractInstrumentation::with(['typeInstrument', 'natureTravaux'])
                    ->where('CodeCaractFamille', $codeCaractFamille)->get();

                    // Transformez les données pour ajouter les libellés
                    $formattedData = $subTableData->map(function ($item) {
                        return [
                            'instrument' => $item->typeInstrument->libelle ?? 'Non spécifié',
                            'nature' => $item->natureTravaux->libelle ?? 'Non spécifié',
                            'nombre' => $item->nombre,
                        ];
                    });
                $tableName = 'Instrumentation';
            }

            // Journaliser si aucune sous-table correspondante n'a été trouvée
            if (!$subTableData) {
                Log::warning('Aucune sous-table ne correspond à CodeCaractFamille: ' . $codeCaractFamille);
                return response()->json(['error' => 'Aucune sous-table ne correspond à CodeCaractFamille'], 404);
            }

            // Retourner les données fusionnées (caracteristique + sous-table)
            Log::info('Données de sous-table récupérées avec succès pour ' . $tableName);

            return response()->json([
                'caracteristique' => $caracteristique,
                'sous_table' => $tableName,
                'sous_table_data' => $subTableData
            ]);

        } catch (\Exception $e) {
            // Log the error with the full stack trace for better debugging
            Log::error('Erreur dans getCaracteristiqueData: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            // Return a JSON response with the error details
            return response()->json([
                'error' => 'Une erreur est survenue lors du traitement.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function filterAnnexe(Request $request)
    {
        try {
            // Validation des entrées
            $validatedData = $request->validate([
                'sous_domaine' => 'required|string',
                'year' => 'required|integer',
                'ecran_id' => 'required|integer',
                'famille' => 'required|integer',
            ]);

            $sousDomaine = $request->input('sous_domaine');
            $year = $request->input('year');
            $familleCode = $request->input('famille');

            // Récupérer les projets
            $projets = ProjetEha2::all();
            $projetsFiltres = $projets->filter(function ($projet) use ($sousDomaine, $year) {
                $codeSousDomaine = substr($projet->CodeProjet, 12, 4);
                $projectYear = substr($projet->CodeProjet, 17, 4);
                return $codeSousDomaine == $sousDomaine && $projectYear == $year;
            });

            // Extraire les codes de projet pour filtrer les caractéristiques
            $projetCodes = $projetsFiltres->pluck('CodeProjet');

            // Récupérer les caractéristiques
            $caracteristiques = Caracteristique::whereIn('CodeProjet', $projetCodes)
                ->where('codeFamille', $familleCode)
                ->get();

            if ($caracteristiques->isEmpty()) {
                return response()->json(['error' => 'Aucune caractéristique trouvée pour les projets sélectionnés.'], 404);
            }

            $resultats = [];

            foreach ($caracteristiques as $caracteristique) {
                $sousTableResult = $this->getCaracteristiqueData(new Request([
                    'famille' => $familleCode,
                    'CodeCaractFamille' => $caracteristique->CodeCaractFamille
                ]));

                // Vérifier si la sous-table a bien été trouvée
                if (!isset($sousTableResult->original['sous_table']) || empty($sousTableResult->original['sous_table_data'])) {
                    \Log::warning('Aucune sous-table trouvée pour CodeCaractFamille : ' . $caracteristique->CodeCaractFamille);
                }

                $resultats[] = [
                    'caracteristique' => $caracteristique,
                    'sous_table' => $sousTableResult->original['sous_table'] ?? 'Non défini',
                    'sous_table_data' => $sousTableResult->original['sous_table_data'] ?? []
                ];
            }


            return response()->json(['status' => 'success', 'resultats' => $resultats], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur dans filterAnnexe: ', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Une erreur est survenue lors du traitement.', 'details' => $e->getMessage()], 500);
        }
    }


    /*public function filterAnnexe(Request $request){
    try {
        // Pour vérifier les données envoyées, ajoutez des logs
        \Log::info('Données reçues: ', $request->all());

        // 1. Validation des entrées
        $validatedData = $request->validate([
            'sous_domaine' => 'required|string',
            'year' => 'required|integer',
            'ecran_id' => 'required|integer'
        ]);
        $sousDomaine = $request->input('sous_domaine');
        $year = $request->input('year');
        $familleCode = $request->input('famille');

        // Récupérer les projets selon les critères de sous-domaine et d'année
        $projets = ProjetEha2::all();
        $projetsFiltres = $projets->filter(function ($projet) use ($sousDomaine, $year) {
            $codeSousDomaine = substr($projet->CodeProjet, 12, 4);
            $projectYear = substr($projet->CodeProjet, 17, 4);
            return $codeSousDomaine == $sousDomaine && $projectYear == $year;
        });

        // Extraire les codes de projet pour filtrer dans les caractéristiques
        $projetCodes = $projetsFiltres->pluck('CodeProjet');

        // Récupérer les caractéristiques des projets filtrés
        $caracteristiques = Caracteristique::whereIn('CodeProjet', $projetCodes)
        ->where('codeFamille', $familleCode)
        ->get();

        // Vérifier les bénéficiaires associés et les sous-caractéristiques
        if ($caracteristiques->isEmpty()) {
            return back()->withErrors(['error' => 'Aucune caractéristique trouvée pour les projets sélectionnés.']);
        }else{
            // Si des caractéristiques existent, on va traiter chaque caractéristique
            $resultats = [];

            foreach ($caracteristiques as $caracteristique) {
                // Appel de la méthode getCaracteristiqueData
                // On simule une requête pour chaque caractéristique avec les données nécessaires.
                $request = new Request();
                $request->replace([
                    'codeFamille' => $familleCode,
                    'CodeCaractFamille' => $caracteristique->CodeCaractFamille
                ]);

                // Appel de la fonction getCaracteristiqueData pour chaque caractéristique
                $resultat = $this->getCaracteristiqueData($request);

                // Stocker le résultat
                $resultats[] = $resultat;
            }

            // Retourner ou traiter les résultats
            return view('resultats', ['resultats' => $resultats]);
        }

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
    }*/

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

