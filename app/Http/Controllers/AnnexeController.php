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
use App\Models\Etablissement;
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
    public function getBeneficiaire(Request $request)
    {
        try {
            // Journalisation pour déboguer les valeurs d'entrée
            Log::info('Requête reçue: ', $request->all());

            // Filtrage des projets selon le domaine et l'année
            $sousDomaine = $request->input('sous_domaine');
            $year = $request->input('year');
            $projets = ProjetEha2::all();

            // Filtrer les projets basés sur le sous-domaine et l'année
            $projetsFiltres = $projets->filter(function ($projet) use ($sousDomaine, $year) {
                $codeSousDomaine = substr($projet->CodeProjet, 12, 4);
                $projectYear = substr($projet->CodeProjet, 17, 4);
                return $codeSousDomaine == $sousDomaine && $projectYear == $year;
            });

            // Récupérer les bénéficiaires associés aux projets filtrés
            $beneficiaires = ActionBeneficiairesProjet::whereIn('CodeProjet', $projetsFiltres->pluck('CodeProjet'))->get();

            if ($beneficiaires->isEmpty()) {
                Log::warning('Aucun bénéficiaire trouvé pour les projets filtrés.');
                return response()->json(['error' => 'Aucun bénéficiaire trouvé'], 404);
            }

            $resultats = [];

            // Pour chaque bénéficiaire, récupérer les détails selon le type
            foreach ($beneficiaires as $beneficiaire) {
                $details = $this->fetchBeneficiaireDetails($beneficiaire);

                // Ajouter les détails du bénéficiaire au résultat
                if ($details) {
                    $resultats[] = [
                        'type_beneficiaire' => $beneficiaire->type_beneficiaire,
                        'code' => $beneficiaire->CodeProjet,
                        'nom' => $details->libelle ?? $details->nom_etablissement,
                        'details_niveaux' => $details,
                    ];
                }
            }

            Log::info('Bénéficiaires trouvés: ', ['beneficiaires' => $resultats]);
            return response()->json(['beneficiaires' => $resultats], 200);

        } catch (\Exception $e) {
            Log::error('Erreur dans getBeneficiaire: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'error' => 'Une erreur est survenue lors du traitement.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Méthode auxiliaire pour obtenir les détails du bénéficiaire
    protected function fetchBeneficiaireDetails($beneficiaire)
    {
        $details = null;

        switch ($beneficiaire->type_beneficiaire) {
            case 'district':
                $details = District::where('code', $beneficiaire->beneficiaire_id)->select('libelle')->first();
                break;
            case 'departement':
                $details = Departement::where('code', $beneficiaire->beneficiaire_id)->select('libelle')->first();
                break;
            case 'region':
                $details = Region::where('code', $beneficiaire->beneficiaire_id)->select('libelle')->first();
                break;
            case 'sous_prefecture':
                $details = Sous_prefecture::where('code', $beneficiaire->beneficiaire_id)->select('libelle')->first();
                break;
            case 'localite':
                $details = Localite::where('code', $beneficiaire->beneficiaire_id)->select('libelle')->first();
                break;
            case 'etablissement':
                $details = Etablissement::where('code', $beneficiaire->beneficiaire_id)
                    ->select('nom_etablissement', 'code_localite', 'code_niveau')
                    ->first();

                // Récupérer les informations de niveau supérieurs si c'est un établissement
                if ($details) {
                    $localite = Localite::where('code', $details->code_localite)->select('libelle', 'code_sous_prefecture')->first();
                    $sousPrefecture = $localite ? Sous_prefecture::where('code', $localite->code_sous_prefecture)->select('libelle', 'code_departement')->first() : null;
                    $departement = $sousPrefecture ? Departement::where('code', $sousPrefecture->code_departement)->select('libelle', 'code_region')->first() : null;
                    $region = $departement ? Region::where('code', $departement->code_region)->select('libelle')->first() : null;

                    // Ajouter les niveaux hiérarchiques
                    $details->localite = $localite ? $localite->libelle : null;
                    $details->sous_prefecture = $sousPrefecture ? $sousPrefecture->libelle : null;
                    $details->departement = $departement ? $departement->libelle : null;
                    $details->region = $region ? $region->libelle : null;
                }
                break;
        }

        return $details;
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
                'famille' => 'required|integer',
            ]);

            $sousDomaine = $request->input('sous_domaine');
            $year = $request->input('year');
            $familleCode = $request->input('famille');

            // Étape 1 : Filtrer les projets selon le domaine et l'année
            $projets = ProjetEha2::all()->filter(function ($projet) use ($sousDomaine, $year) {
                $codeSousDomaine = substr($projet->CodeProjet, 12, 4);
                $projectYear = substr($projet->CodeProjet, 17, 4);
                return $codeSousDomaine == $sousDomaine && $projectYear == $year;
            });

            $projetCodes = $projets->pluck('CodeProjet');

            // Étape 2 : Récupérer les bénéficiaires et caractéristiques liées aux projets filtrés
            $beneficiaires = ActionBeneficiairesProjet::whereIn('CodeProjet', $projetCodes)->get();
            $caracteristiques = Caracteristique::whereIn('CodeProjet', $projetCodes)
                                ->where('codeFamille', $familleCode)
                                ->get();

            if ($beneficiaires->isEmpty() && $caracteristiques->isEmpty()) {
                return response()->json(['error' => 'Aucune donnée trouvée pour les projets sélectionnés.'], 404);
            }

            $resultats = [];

            // Étape 3 : Regrouper les bénéficiaires et leurs caractéristiques
            foreach ($caracteristiques as $caracteristique) {
                $sousTableResult = $this->getCaracteristiqueData(new Request([
                    'famille' => $familleCode,
                    'CodeCaractFamille' => $caracteristique->CodeCaractFamille
                ]));

                if (!isset($sousTableResult->original['sous_table']) || empty($sousTableResult->original['sous_table_data'])) {
                    Log::warning('Aucune sous-table trouvée pour CodeCaractFamille : ' . $caracteristique->CodeCaractFamille);
                }

                // Récupérer tous les bénéficiaires associés au CodeProjet de la caractéristique
                $beneficiairesPourProjet = $beneficiaires->where('CodeProjet', $caracteristique->CodeProjet);

                // Préparer un tableau pour stocker les détails de chaque bénéficiaire
                $beneficiairesDetails = [];

                foreach ($beneficiairesPourProjet as $beneficiaire) {
                    $details = $this->fetchBeneficiaireDetails($beneficiaire);
                    if ($details) {
                        $beneficiairesDetails[] = [
                            'type' => $beneficiaire->type_beneficiaire,
                            'beneficiaire_id' => $beneficiaire->beneficiaire_id,
                            'nom' => $details->libelle ?? $details->nom_etablissement,
                            'details_niveaux' => $details,
                        ];
                    }
                }

                $resultats[] = [
                    'caracteristique' => $caracteristique,
                    'sous_table' => $sousTableResult->original['sous_table'] ?? 'Non défini',
                    'sous_table_data' => $sousTableResult->original['sous_table_data'] ?? [],
                    'beneficiaires' => $beneficiairesDetails,
                ];
            }

            Log::info('Bénéficiaire trouvé pour CodeProjet: ', ['beneficiaire' => $beneficiairesDetails]);

            return response()->json(['status' => 'success', 'resultats' => $resultats], 200);

        } catch (\Exception $e) {

            Log::error('Erreur dans filterAnnexe: ', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Une erreur est survenue lors du traitement.', 'details' => $e->getMessage()], 500);
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

