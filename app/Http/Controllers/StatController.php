<?php

namespace App\Http\Controllers;

use App\Models\AgenceExecution;
use App\Models\Bailleur;
use App\Models\BailleursProjet;
use App\Models\CouvrirRegion;
use App\Models\Ecran;
use App\Models\Ministere;
use App\Models\Pays;
use App\Models\Personnel;
use App\Models\ProjetEha2;
use App\Models\ProjetStatutProjet;
use App\Models\Region;
use App\Models\StatutProjet;
use App\Models\StructureRattachement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
    function statNombreProjet(Request $request)
    {
        // Utiliser une requête SQL brute pour obtenir le montant total des projets par statut
        $resultats = DB::select("SELECT `code_statut_projet`, COUNT(`projet_eha2`.`CodeProjet`) as montant_total FROM `projet_statut_projet`
        INNER JOIN `projet_eha2` ON `projet_statut_projet`.`code_projet` = `projet_eha2`.`CodeProjet`
        GROUP BY `code_statut_projet`");

        // Convertir les résultats en tableau associatif
        $montantParStatut = [];
        foreach ($resultats as $resultat) {
        $montantParStatut[$resultat->code_statut_projet] = $resultat->montant_total;
        }

        // Récupérer les montants pour chaque statut de projet
        $projets_prevus = isset($montantParStatut['01']) ? $montantParStatut['01'] : 0;
        $projets_en_cours = isset($montantParStatut['02']) ? $montantParStatut['02'] : 0;
        $projets_annulé = isset($montantParStatut['03']) ? $montantParStatut['03'] : 0;
        $projets_cloture = isset($montantParStatut['04']) ? $montantParStatut['04'] : 0;
        $projets_suspendus = isset($montantParStatut['05']) ? $montantParStatut['05'] : 0;
        $projets_redemarrer = isset($montantParStatut['06']) ? $montantParStatut['06'] : 0;

       $ecran = Ecran::find($request->input('ecran_id'));

        // Récupérer tous les projets
        $projets = ProjetEha2::all();

        // Récupérer le code région de l'utilisateur
        $region = CouvrirRegion::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
        $code_region = $region->code_region;


        // Récupérer le code de fonction de l'utilisateur connecté
        $codeFonction = auth()->user()->latestFonction->fonctionUtilisateur->code;

        // Initialiser la variable $personnelAffiche
        $personnelAffiche = '';



        if ($codeFonction === 'cp') {
            $chefprojet = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();

            if ($chefprojet) {
                if ($chefprojet->type_structure == 'agence_execution') {
                    // Si c'est une agence d'exécution, récupérer son nom depuis la table AgenceExecution
                    $chefprojetInfo = AgenceExecution::where('code_agence_execution', $chefprojet->code_structure)->first();
                    $personnelAffiche = $chefprojetInfo ? $chefprojetInfo->nom_agence : '---';
                } else if ($chefprojet->type_structure == 'ministere') {
                    // Si c'est un ministère, récupérer son libellé depuis la table Ministere
                    $chefprojetInfo = Ministere::where('code', $chefprojet->code_structure)->first();
                    $personnelAffiche = $chefprojetInfo ? $chefprojetInfo->libelle : '---';
                }

                // Requête pour obtenir le nombre de projets dans chaque statut pour le chef de projet spécifié

                $statutsProjets = DB::table('statut_projet AS sp')
                    ->select('sp.libelle AS statut_projet')
                    ->selectRaw('COUNT(pe.CodeProjet) AS total_cout_projet2')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "01", 1, 0)) AS total_prevu')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "02", 1, 0)) AS total_en_cours')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "03", 1, 0)) AS total_annule')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "04", 1, 0)) AS total_cloture')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "05", 1, 0)) AS total_suspendu')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "06", 1, 0)) AS total_redemarre')
                    ->leftJoin('projet_statut_projet AS psp', 'sp.code', '=', 'psp.code_statut_projet')
                    ->leftJoin('projet_eha2 AS pe', 'pe.CodeProjet', '=', 'psp.code_projet')
                    ->leftJoin('projet_chef_projet AS pcp', 'pcp.code_projet', '=', 'psp.code_projet')
                    ->where('pcp.code_personnel', auth()->user()->personnel->code_personnel)
                    ->groupBy('sp.libelle')
                    ->get();

                // Créer un tableau associatif pour stocker les résultats
                $projetsParStatut = [];
                // Initialiser le tableau avec des valeurs par défaut de 0 pour chaque statut
                $statuts = ['Prévu', 'En cours', 'Annulé', 'Clôturé', 'Suspendu', 'Redémarré'];
                foreach ($statuts as $statut) {
                    $projetsParStatut[$statut] = 0;
                }
                // Mettre à jour les valeurs avec celles obtenues dans la requête
                foreach ($statutsProjets as $statutProjet) {
                    $projetsParStatut[$statutProjet->statut_projet] = [
                        'total_cout_projet2' => $statutProjet->total_cout_projet2,
                        'total_prevu' => $statutProjet->total_prevu,
                        'total_en_cours' => $statutProjet->total_en_cours,
                        'total_annule' => $statutProjet->total_annule,
                        'total_cloture' => $statutProjet->total_cloture,
                        'total_suspendu' => $statutProjet->total_suspendu,
                        'total_redemarre' => $statutProjet->total_redemarre,
                    ];
                }
            }
        }elseif ($codeFonction === 'ba') { // Bailleur
            // Récupérer les données du bailleur
            $bailleur = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();

            if ($bailleur) {
                // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                $bailleurInfo = Bailleur::where('code_bailleur', $bailleur->code_structure)->first();
                $personnelAffiche = $bailleurInfo ? $bailleurInfo->libelle_long : '---';
                $statutsProjets = DB::table('statut_projet AS sp')
                    ->select('sp.libelle AS statut_projet')
                    ->selectRaw('COUNT(pe.CodeProjet) AS total_cout_projet2')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "01", 1, 0)) AS total_prevu')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "02", 1, 0)) AS total_en_cours')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "03", 1, 0)) AS total_annule')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "04", 1, 0)) AS total_cloture')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "05", 1, 0)) AS total_suspendu')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "06", 1, 0)) AS total_redemarre')
                    ->leftJoin('projet_statut_projet AS psp', 'sp.code', '=', 'psp.code_statut_projet')
                    ->leftJoin('projet_eha2 AS pe', 'pe.CodeProjet', '=', 'psp.code_projet')
                    ->leftJoin('bailleurs_projets AS pcp', 'pcp.code_projet', '=', 'psp.code_projet')
                    ->where('pcp.code_bailleur', $bailleurInfo->code_bailleur)
                    ->groupBy('sp.libelle')
                    ->get();

                // Créer un tableau associatif pour stocker les résultats
                $projetsParStatut = [];
                // Initialiser le tableau avec des valeurs par défaut de 0 pour chaque statut
                $statuts = ['Prévu', 'En cours', 'Annulé', 'Clôturé', 'Suspendu', 'Redémarré'];
                foreach ($statuts as $statut) {
                    $projetsParStatut[$statut] = 0;
                }
                // Mettre à jour les valeurs avec celles obtenues dans la requête
                foreach ($statutsProjets as $statutProjet) {
                    $projetsParStatut[$statutProjet->statut_projet] = [
                        'total_cout_projet2' => $statutProjet->total_cout_projet2,
                        'total_prevu' => $statutProjet->total_prevu,
                        'total_en_cours' => $statutProjet->total_en_cours,
                        'total_annule' => $statutProjet->total_annule,
                        'total_cloture' => $statutProjet->total_cloture,
                        'total_suspendu' => $statutProjet->total_suspendu,
                        'total_redemarre' => $statutProjet->total_redemarre,
                    ];
                }
            }

        }elseif ($codeFonction === 'dc') { // Directeur de cabinet
            // Récupérer le ministère du directeur de cabinet
            $ministere = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
            if ($ministere) {
                // Si le ministère est trouvé, récupérer son libellé depuis la table Ministere
                $ministereInfo = Ministere::where('code', $ministere->code_structure)->first();
                $personnelAffiche = $ministereInfo ? $ministereInfo->libelle :'Directeur de cabinet';
                // Requête pour obtenir le nombre de projets dans chaque statut pour le ministère spécifié
                $statutsProjets = DB::table('statut_projet AS sp')
                                    ->select('sp.libelle AS statut_projet')
                                    ->selectRaw('COUNT(pe.CodeProjet) AS total_cout_projet2')
                                    ->selectRaw('SUM(IF(psp.code_statut_projet = "01", 1, 0)) AS total_prevu')
                                    ->selectRaw('SUM(IF(psp.code_statut_projet = "02", 1, 0)) AS total_en_cours')
                                    ->selectRaw('SUM(IF(psp.code_statut_projet = "03", 1, 0)) AS total_annule')
                                    ->selectRaw('SUM(IF(psp.code_statut_projet = "04", 1, 0)) AS total_cloture')
                                    ->selectRaw('SUM(IF(psp.code_statut_projet = "05", 1, 0)) AS total_suspendu')
                                    ->selectRaw('SUM(IF(psp.code_statut_projet = "06", 1, 0)) AS total_redemarre')
                                    ->leftJoin('projet_statut_projet AS psp', 'sp.code', '=', 'psp.code_statut_projet')
                                    ->leftJoin('projet_eha2 AS pe', 'pe.CodeProjet', '=', 'psp.code_projet')
                                    ->where('pe.code_ministere', $ministereInfo->code)
                                    ->groupBy('sp.libelle')
                                    ->get();

                // Créer un tableau associatif pour stocker les résultats
                $projetsParStatut = [];
                // Initialiser le tableau avec des valeurs par défaut de 0 pour chaque statut
                $statuts = ['Prévu', 'En cours', 'Annulé', 'Clôturé', 'Suspendu', 'Redémarré'];
                foreach ($statuts as $statut) {
                    $projetsParStatut[$statut] = 0;
                }
                // Mettre à jour les valeurs avec celles obtenues dans la requête
                foreach ($statutsProjets as $statutProjet) {
                    $projetsParStatut[$statutProjet->statut_projet] = [
                        'total_cout_projet2' => $statutProjet->total_cout_projet2,
                        'total_prevu' => $statutProjet->total_prevu,
                        'total_en_cours' => $statutProjet->total_en_cours,
                        'total_annule' => $statutProjet->total_annule,
                        'total_cloture' => $statutProjet->total_cloture,
                        'total_suspendu' => $statutProjet->total_suspendu,
                        'total_redemarre' => $statutProjet->total_redemarre,
                    ];
                }
            }
        } elseif ($codeFonction === 'dr') { // Directeur Régional
            // Récupérer le nom de la région de l'utilisateur
            $region = CouvrirRegion::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
            if ($region) {
                // Si la région est trouvée, récupérer son libellé depuis la table Region
                $regionInfo = Region::where('code', $region->code_region)->first();
                $personnelAffiche = $regionInfo ? $regionInfo->libelle : 'Directeur Régional';
                // Requête pour obtenir le nombre de projets dans chaque statut pour la région spécifiée
                $statutsProjets = DB::table('statut_projet AS sp')
                                    ->select('sp.libelle AS statut_projet')
                                    ->selectRaw('COUNT(pe.CodeProjet) AS total_cout_projet2')
                                    ->selectRaw('SUM(IF(psp.code_statut_projet = "01", 1, 0)) AS total_prevu')
                                    ->selectRaw('SUM(IF(psp.code_statut_projet = "02", 1, 0)) AS total_en_cours')
                                    ->selectRaw('SUM(IF(psp.code_statut_projet = "03", 1, 0)) AS total_annule')
                                    ->selectRaw('SUM(IF(psp.code_statut_projet = "04", 1, 0)) AS total_cloture')
                                    ->selectRaw('SUM(IF(psp.code_statut_projet = "05", 1, 0)) AS total_suspendu')
                                    ->selectRaw('SUM(IF(psp.code_statut_projet = "06", 1, 0)) AS total_redemarre')
                                    ->leftJoin('projet_statut_projet AS psp', 'sp.code', '=', 'psp.code_statut_projet')
                                    ->leftJoin('projet_eha2 AS pe', 'pe.CodeProjet', '=', 'psp.code_projet')
                                    ->where('pe.code_region', $regionInfo->code)
                                    ->groupBy('sp.libelle')
                                    ->get();
                // Créer un tableau associatif pour stocker les résultats
                $projetsParStatut = [];
                // Initialiser le tableau avec des valeurs par défaut de 0 pour chaque statut
                $statuts = ['Prévu', 'En cours', 'Annulé', 'Clôturé', 'Suspendu', 'Redémarré'];
                foreach ($statuts as $statut) {
                    $projetsParStatut[$statut] = 0;
                }
                // Mettre à jour les valeurs avec celles obtenues dans la requête
                foreach ($statutsProjets as $statutProjet) {
                    $projetsParStatut[$statutProjet->statut_projet] = [
                        'total_cout_projet2' => $statutProjet->total_cout_projet2,
                        'total_prevu' => $statutProjet->total_prevu,
                        'total_en_cours' => $statutProjet->total_en_cours,
                        'total_annule' => $statutProjet->total_annule,
                        'total_cloture' => $statutProjet->total_cloture,
                        'total_suspendu' => $statutProjet->total_suspendu,
                        'total_redemarre' => $statutProjet->total_redemarre,
                    ];
                }
            }
        }elseif($codeFonction === 'mo'){
            $maitreoeurvre = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
            if ($maitreoeurvre) {
                // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                $maitreoeurvreInfo = AgenceExecution::where('code_agence_execution', $maitreoeurvre->code_structure)->first();
                $personnelAffiche = $maitreoeurvreInfo ? $maitreoeurvreInfo->nom_agence : '---';
                $statutsProjets = DB::table('statut_projet AS sp')
                    ->select('sp.libelle AS statut_projet')
                    ->selectRaw('COUNT(pe.CodeProjet) AS total_cout_projet2')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "01", 1, 0)) AS total_prevu')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "02", 1, 0)) AS total_en_cours')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "03", 1, 0)) AS total_annule')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "04", 1, 0)) AS total_cloture')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "05", 1, 0)) AS total_suspendu')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "06", 1, 0)) AS total_redemarre')
                    ->leftJoin('projet_statut_projet AS psp', 'sp.code', '=', 'psp.code_statut_projet')
                    ->leftJoin('projet_eha2 AS pe', 'pe.CodeProjet', '=', 'psp.code_projet')
                    ->leftJoin('projet_agence AS pcp', 'pcp.code_projet', '=', 'psp.code_projet')
                    ->where('pcp.code_agence', $maitreoeurvreInfo->code_agence_execution)
                    ->where('pcp.niveau','=','2')
                    ->groupBy('sp.libelle')
                    ->get();

                // Créer un tableau associatif pour stocker les résultats
                $projetsParStatut = [];
                // Initialiser le tableau avec des valeurs par défaut de 0 pour chaque statut
                $statuts = ['Prévu', 'En cours', 'Annulé', 'Clôturé', 'Suspendu', 'Redémarré'];
                foreach ($statuts as $statut) {
                    $projetsParStatut[$statut] = 0;
                }
                // Mettre à jour les valeurs avec celles obtenues dans la requête
                foreach ($statutsProjets as $statutProjet) {
                    $projetsParStatut[$statutProjet->statut_projet] = [
                        'total_cout_projet2' => $statutProjet->total_cout_projet2,
                        'total_prevu' => $statutProjet->total_prevu,
                        'total_en_cours' => $statutProjet->total_en_cours,
                        'total_annule' => $statutProjet->total_annule,
                        'total_cloture' => $statutProjet->total_cloture,
                        'total_suspendu' => $statutProjet->total_suspendu,
                        'total_redemarre' => $statutProjet->total_redemarre,
                    ];
                }
            }
        }elseif($codeFonction === 'rf'){
            $regie = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
            if ($regie) {
                // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                $regieInfo = AgenceExecution::where('code_agence_execution', $regie->code_structure)->first();
                $personnelAffiche = $regieInfo ? $regieInfo->nom_agence : '---';
                $statutsProjets = DB::table('statut_projet AS sp')
                    ->select('sp.libelle AS statut_projet')
                    ->selectRaw('COUNT(pe.CodeProjet) AS total_cout_projet2')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "01", 1, 0)) AS total_prevu')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "02", 1, 0)) AS total_en_cours')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "03", 1, 0)) AS total_annule')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "04", 1, 0)) AS total_cloture')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "05", 1, 0)) AS total_suspendu')
                    ->selectRaw('SUM(IF(psp.code_statut_projet = "06", 1, 0)) AS total_redemarre')
                    ->leftJoin('projet_statut_projet AS psp', 'sp.code', '=', 'psp.code_statut_projet')
                    ->leftJoin('projet_eha2 AS pe', 'pe.CodeProjet', '=', 'psp.code_projet')
                    ->leftJoin('projet_agence AS pcp', 'pcp.code_projet', '=', 'psp.code_projet')
                    ->where('pcp.code_agence', $regieInfo->code_agence_execution)
                    ->where('pcp.niveau','=','1')
                    ->groupBy('sp.libelle')
                    ->get();

                // Créer un tableau associatif pour stocker les résultats
                $projetsParStatut = [];
                // Initialiser le tableau avec des valeurs par défaut de 0 pour chaque statut
                $statuts = ['Prévu', 'En cours', 'Annulé', 'Clôturé', 'Suspendu', 'Redémarré'];
                foreach ($statuts as $statut) {
                    $projetsParStatut[$statut] = 0;
                }
                // Mettre à jour les valeurs avec celles obtenues dans la requête
                foreach ($statutsProjets as $statutProjet) {
                    $projetsParStatut[$statutProjet->statut_projet] = [
                        'total_cout_projet2' => $statutProjet->total_cout_projet2,
                        'total_prevu' => $statutProjet->total_prevu,
                        'total_en_cours' => $statutProjet->total_en_cours,
                        'total_annule' => $statutProjet->total_annule,
                        'total_cloture' => $statutProjet->total_cloture,
                        'total_suspendu' => $statutProjet->total_suspendu,
                        'total_redemarre' => $statutProjet->total_redemarre,
                    ];
                }
            }
        }
        return view('stat_nombre_projet_vue', compact('ecran','projets_cloture','projets_redemarrer','projets_suspendus','projets_annulé','projets_en_cours','personnelAffiche', 'projetsParStatut','projets_prevus'));




    }
    public function statFinance(Request $request)
    {
        // Utiliser une requête SQL brute pour obtenir le montant total des projets par statut
        $resultats = DB::select("SELECT `code_statut_projet`, SUM(`projet_eha2`.`cout_projet`) as montant_total FROM `projet_statut_projet`
        INNER JOIN `projet_eha2` ON `projet_statut_projet`.`code_projet` = `projet_eha2`.`CodeProjet`
        GROUP BY `code_statut_projet`");

        // Convertir les résultats en tableau associatif
        $montantParStatut = [];
        foreach ($resultats as $resultat) {
        $montantParStatut[$resultat->code_statut_projet] = $resultat->montant_total;
        }

        // Récupérer les montants pour chaque statut de projet
        $projets_prevus = isset($montantParStatut['01']) ? $montantParStatut['01'] : 0;
        $projets_en_cours = isset($montantParStatut['02']) ? $montantParStatut['02'] : 0;
        $projets_annulé = isset($montantParStatut['03']) ? $montantParStatut['03'] : 0;
        $projets_cloture = isset($montantParStatut['04']) ? $montantParStatut['04'] : 0;
        $projets_suspendus = isset($montantParStatut['05']) ? $montantParStatut['05'] : 0;
        $projets_redemarrer = isset($montantParStatut['06']) ? $montantParStatut['06'] : 0;

        // Récupérer le pays
        $ecran = Ecran::find($request->input('ecran_id'));

        // Récupérer tous les projets
        $projets = ProjetEha2::all();

        // Récupérer le code région de l'utilisateur
        $region = CouvrirRegion::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
        // $code_region = $region->code_region;

        // Déclaration de la variable $personnelAffiche
        $personnelAffiche = '';

        $userCode = auth()->user()->latestFonction->fonctionUtilisateur->code;

        // Déterminer la valeur de $personnelAffiche en fonction du groupe utilisateur
        if ($userCode === 'cp') {
            $chefprojet = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();

            if ($chefprojet) {
                if ($chefprojet->type_structure == 'agence_execution') {
                    $chefprojetInfo = AgenceExecution::where('code_agence_execution', $chefprojet->code_structure)->first();
                    $personnelAffiche = $chefprojetInfo ? $chefprojetInfo->nom_agence : '---';
                } else if ($chefprojet->type_structure == 'ministere') {
                    $chefprojetInfo = Ministere::where('code', $chefprojet->code_structure)->first();
                    $personnelAffiche = $chefprojetInfo ? $chefprojetInfo->libelle : '---';
                }

                // Exécuter la requête pour obtenir les statistiques des projets
                $statutsProjets = DB::table('statut_projet as sp')
                    ->leftJoin('projet_statut_projet as psp', 'sp.code', '=', 'psp.code_statut_projet')
                    ->leftJoin('projet_eha2 as pe', function ($join) use ($chefprojet) {
                        $join->on('pe.CodeProjet', '=', 'psp.code_projet');
                    })
                    ->whereExists(function ($query) use ($chefprojet) {
                        $query->select(DB::raw(1))
                            ->from('projet_chef_projet as pcp')
                            ->whereRaw('pcp.code_projet = psp.code_projet')
                            ->where('pcp.code_personnel', '=', $chefprojet->code_personnel);
                    })
                    ->select(
                        'sp.libelle AS statut_projet',
                        DB::raw('SUM(IFNULL(pe.cout_projet, 0)) AS total_cout_projet2'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "01", pe.cout_projet, 0)) AS total_prevu'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "02", pe.cout_projet, 0)) AS total_en_cours'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "03", pe.cout_projet, 0)) AS total_annule'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "04", pe.cout_projet, 0)) AS total_cloture'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "05", pe.cout_projet, 0)) AS total_suspendu'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "06", pe.cout_projet, 0)) AS total_redemarre')
                    )
                    ->groupBy('sp.libelle')
                    ->get();

                // Créer un tableau associatif pour stocker les résultats
                $projetsParStatut = [];

                // Initialiser le tableau avec des valeurs par défaut de 0 pour chaque statut
                $statuts = ['Prévu', 'En cours', 'Annulé', 'Clôturé', 'Suspendu', 'Redémarré'];
                foreach ($statuts as $statut) {
                    $projetsParStatut[$statut] = [
                        'total_cout_projet2' => 0,
                        'total_prevu' => 0,
                        'total_en_cours' => 0,
                        'total_annule' => 0,
                        'total_cloture' => 0,
                        'total_suspendu' => 0,
                        'total_redemarre' => 0,
                    ];
                }

                // Mettre à jour les valeurs avec celles obtenues dans la requête
                foreach ($statutsProjets as $statutProjet) {
                    $projetsParStatut[$statutProjet->statut_projet] = [
                        'total_cout_projet2' => $statutProjet->total_cout_projet2,
                        'total_prevu' => $statutProjet->total_prevu,
                        'total_en_cours' => $statutProjet->total_en_cours,
                        'total_annule' => $statutProjet->total_annule,
                        'total_cloture' => $statutProjet->total_cloture,
                        'total_suspendu' => $statutProjet->total_suspendu,
                        'total_redemarre' => $statutProjet->total_redemarre,
                    ];
                }
            }

        }elseif ($userCode === 'ba') {
            $bailleur = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();

            if ($bailleur) {
                // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                $bailleurInfo = Bailleur::where('code_bailleur', $bailleur->code_structure)->first();
                $personnelAffiche = $bailleurInfo ? $bailleurInfo->libelle_long : '---';

                $statutsProjets = DB::table('statut_projet as sp')
                    ->leftJoin('projet_statut_projet as psp', 'sp.code', '=', 'psp.code_statut_projet')
                    ->leftJoin('projet_eha2 as pe', function ($join) use ($bailleurInfo) {
                        $join->on('pe.CodeProjet', '=', 'psp.code_projet');
                    })
                    ->whereExists(function ($query) use ($bailleurInfo) {
                        $query->select(DB::raw(1))
                            ->from('bailleurs_projets as pcp')
                            ->whereRaw('pcp.code_projet = psp.code_projet')
                            ->where('pcp.code_bailleur', '=', $bailleurInfo->code_bailleur);
                    })
                    ->select(
                        'sp.libelle AS statut_projet',
                        DB::raw('SUM(IFNULL(pe.cout_projet, 0)) AS total_cout_projet2'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "01", pe.cout_projet, 0)) AS total_prevu'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "02", pe.cout_projet, 0)) AS total_en_cours'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "03", pe.cout_projet, 0)) AS total_annule'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "04", pe.cout_projet, 0)) AS total_cloture'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "05", pe.cout_projet, 0)) AS total_suspendu'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "06", pe.cout_projet, 0)) AS total_redemarre')
                    )
                    ->groupBy('sp.libelle')
                    ->get();

                // Créer un tableau associatif pour stocker les résultats
                $projetsParStatut = [];

                // Initialiser le tableau avec des valeurs par défaut de 0 pour chaque statut
                $statuts = ['Prévu', 'En cours', 'Annulé', 'Clôturé', 'Suspendu', 'Redémarré'];
                foreach ($statuts as $statut) {
                    $projetsParStatut[$statut] = [
                        'total_cout_projet2' => 0,
                        'total_prevu' => 0,
                        'total_en_cours' => 0,
                        'total_annule' => 0,
                        'total_cloture' => 0,
                        'total_suspendu' => 0,
                        'total_redemarre' => 0,
                    ];
                }

                // Mettre à jour les valeurs avec celles obtenues dans la requête
                foreach ($statutsProjets as $statutProjet) {
                    $projetsParStatut[$statutProjet->statut_projet] = [
                        'total_cout_projet2' => $statutProjet->total_cout_projet2,
                        'total_prevu' => $statutProjet->total_prevu,
                        'total_en_cours' => $statutProjet->total_en_cours,
                        'total_annule' => $statutProjet->total_annule,
                        'total_cloture' => $statutProjet->total_cloture,
                        'total_suspendu' => $statutProjet->total_suspendu,
                        'total_redemarre' => $statutProjet->total_redemarre,
                    ];
                }
            }





        }elseif ($userCode === 'dc') {
            // Récupérer le ministère
            $ministere = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
            if ($ministere) {
                // Si le ministère est trouvé, récupérer son libellé depuis la table Ministere
                $ministereInfo = Ministere::where('code', $ministere->code_structure)->first();
                $personnelAffiche = $ministereInfo ? $ministereInfo->libelle : 'Directeur de cabinet';

                // Requête pour obtenir le nombre de projets dans chaque statut pour la région spécifiée
                $statuts = ['Prévu', 'En cours', 'Clôturé', 'Redémarré', 'Annulé', 'Suspendu'];

                // Récupérer les données des projets par statut pour la région spécifiée
                $statutsProjets = DB::table('statut_projet as sp')
                    ->leftJoin('projet_statut_projet as psp', 'sp.code', '=', 'psp.code_statut_projet')
                    ->leftJoin('projet_eha2 as pe', function ($join) use ($ministereInfo) {
                        $join->on('pe.CodeProjet', '=', 'psp.code_projet')
                            ->where('pe.code_ministere', '=', $ministereInfo->code);
                    })
                    ->select(
                        'sp.libelle AS statut_projet',
                        DB::raw('SUM(IFNULL(pe.cout_projet, 0)) AS total_cout_projet2'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "01", pe.cout_projet, 0)) AS total_prevu'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "02", pe.cout_projet, 0)) AS total_en_cours'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "03", pe.cout_projet, 0)) AS total_annule'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "04", pe.cout_projet, 0)) AS total_cloture'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "05", pe.cout_projet, 0)) AS total_suspendu'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "06", pe.cout_projet, 0)) AS total_redemarre')
                    )
                    ->groupBy('sp.libelle')
                    ->get();

                // Créer un tableau associatif pour stocker les résultats
                $projetsParStatut = [];

                // Initialiser le tableau avec des valeurs par défaut de 0 pour chaque statut
                $statuts = ['Prévu', 'En cours', 'Annulé', 'Clôturé', 'Suspendu', 'Redémarré'];
                foreach ($statuts as $statut) {
                    $projetsParStatut[$statut] = 0;
                }

                // Mettre à jour les valeurs avec celles obtenues dans la requête
                foreach ($statutsProjets as $statutProjet) {
                    $projetsParStatut[$statutProjet->statut_projet] = [
                        'total_cout_projet2' => $statutProjet->total_cout_projet2,
                        'total_prevu' => $statutProjet->total_prevu,
                        'total_en_cours' => $statutProjet->total_en_cours,
                        'total_annule' => $statutProjet->total_annule,
                        'total_cloture' => $statutProjet->total_cloture,
                        'total_suspendu' => $statutProjet->total_suspendu,
                        'total_redemarre' => $statutProjet->total_redemarre,
                    ];
                }
            }
        }elseif ($userCode === 'dr') {
            // Récupérer le nom de la région de l'utilisateur
            $region = CouvrirRegion::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
            if ($region) {
                // Si la région est trouvée, récupérer son libellé depuis la table Region
                $regionInfo = Region::where('code', $region->code_region)->first();
                $personnelAffiche = $regionInfo ? $regionInfo->libelle : 'Directeur Régional';

                // Requête pour obtenir le nombre de projets dans chaque statut pour la région spécifiée
                $statuts = ['Prévu', 'En cours', 'Clôturé', 'Redémarré', 'Annulé', 'Suspendu'];

                // Récupérer les données des projets par statut pour la région spécifiée
                $statutsProjets = DB::table('statut_projet as sp')
                    ->leftJoin('projet_statut_projet as psp', 'sp.code', '=', 'psp.code_statut_projet')
                    ->leftJoin('projet_eha2 as pe', function ($join) use ($regionInfo) {
                        $join->on('pe.CodeProjet', '=', 'psp.code_projet')
                            ->where('pe.code_region', '=', $regionInfo->code);
                    })
                    ->select(
                        'sp.libelle AS statut_projet',
                        DB::raw('SUM(IFNULL(pe.cout_projet, 0)) AS total_cout_projet2'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "01", pe.cout_projet, 0)) AS total_prevu'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "02", pe.cout_projet, 0)) AS total_en_cours'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "03", pe.cout_projet, 0)) AS total_annule'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "04", pe.cout_projet, 0)) AS total_cloture'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "05", pe.cout_projet, 0)) AS total_suspendu'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "06", pe.cout_projet, 0)) AS total_redemarre')
                    )
                    ->groupBy('sp.libelle')
                    ->get();

                // Créer un tableau associatif pour stocker les résultats
                $projetsParStatut = [];

                // Initialiser le tableau avec des valeurs par défaut de 0 pour chaque statut
                $statuts = ['Prévu', 'En cours', 'Annulé', 'Clôturé', 'Suspendu', 'Redémarré'];
                foreach ($statuts as $statut) {
                    $projetsParStatut[$statut] = 0;
                }

                // Mettre à jour les valeurs avec celles obtenues dans la requête
                foreach ($statutsProjets as $statutProjet) {
                    $projetsParStatut[$statutProjet->statut_projet] = [
                        'total_cout_projet2' => $statutProjet->total_cout_projet2,
                        'total_prevu' => $statutProjet->total_prevu,
                        'total_en_cours' => $statutProjet->total_en_cours,
                        'total_annule' => $statutProjet->total_annule,
                        'total_cloture' => $statutProjet->total_cloture,
                        'total_suspendu' => $statutProjet->total_suspendu,
                        'total_redemarre' => $statutProjet->total_redemarre,
                    ];
                }
            }

        }elseif($userCode === 'mo'){
            $maitreoeurvre = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
            if ($maitreoeurvre) {
                // Si le ministere est trouvé, récupérer son libellé depuis la table Region
                $maitreoeurvreInfo = AgenceExecution::where('code_agence_execution', $maitreoeurvre->code_structure)->first();
                $personnelAffiche = $maitreoeurvreInfo ? $maitreoeurvreInfo->nom_agence : '---';
                $statutsProjets = DB::table('statut_projet as sp')
                    ->leftJoin('projet_statut_projet as psp', 'sp.code', '=', 'psp.code_statut_projet')
                    ->leftJoin('projet_eha2 as pe', function ($join) use ($maitreoeurvreInfo) {
                        $join->on('pe.CodeProjet', '=', 'psp.code_projet');
                    })
                    ->whereExists(function ($query) use ($maitreoeurvreInfo) {
                        $query->select(DB::raw(1))
                            ->from('projet_agence as pcp')
                            ->whereRaw('pcp.code_projet = psp.code_projet')
                            ->where('pcp.code_agence', '=', $maitreoeurvreInfo->code_agence_execution)
                            ->where('pcp.niveau', '=', '2');
                    })
                    ->select(
                        'sp.libelle AS statut_projet',
                        DB::raw('SUM(IFNULL(pe.cout_projet, 0)) AS total_cout_projet2'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "01", pe.cout_projet, 0)) AS total_prevu'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "02", pe.cout_projet, 0)) AS total_en_cours'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "03", pe.cout_projet, 0)) AS total_annule'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "04", pe.cout_projet, 0)) AS total_cloture'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "05", pe.cout_projet, 0)) AS total_suspendu'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "06", pe.cout_projet, 0)) AS total_redemarre')
                    )
                    ->groupBy('sp.libelle')
                    ->get();

                // Créer un tableau associatif pour stocker les résultats
                $projetsParStatut = [];

                // Initialiser le tableau avec des valeurs par défaut de 0 pour chaque statut
                $statuts = ['Prévu', 'En cours', 'Annulé', 'Clôturé', 'Suspendu', 'Redémarré'];
                foreach ($statuts as $statut) {
                    $projetsParStatut[$statut] = [
                        'total_cout_projet2' => 0,
                        'total_prevu' => 0,
                        'total_en_cours' => 0,
                        'total_annule' => 0,
                        'total_cloture' => 0,
                        'total_suspendu' => 0,
                        'total_redemarre' => 0,
                    ];
                }

                // Mettre à jour les valeurs avec celles obtenues dans la requête
                foreach ($statutsProjets as $statutProjet) {
                    $projetsParStatut[$statutProjet->statut_projet] = [
                        'total_cout_projet2' => $statutProjet->total_cout_projet2,
                        'total_prevu' => $statutProjet->total_prevu,
                        'total_en_cours' => $statutProjet->total_en_cours,
                        'total_annule' => $statutProjet->total_annule,
                        'total_cloture' => $statutProjet->total_cloture,
                        'total_suspendu' => $statutProjet->total_suspendu,
                        'total_redemarre' => $statutProjet->total_redemarre,
                    ];
                }
            }

        }elseif($userCode === 'rf'){
            $regie = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
            if ($regie) {
                // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                $regieInfo = AgenceExecution::where('code_agence_execution', $regie->code_structure)->first();
                $personnelAffiche = $regieInfo ? $regieInfo->nom_agence : '---';
                $statutsProjets = DB::table('statut_projet as sp')
                    ->leftJoin('projet_statut_projet as psp', 'sp.code', '=', 'psp.code_statut_projet')
                    ->leftJoin('projet_eha2 as pe', function ($join) use ($regieInfo) {
                        $join->on('pe.CodeProjet', '=', 'psp.code_projet');
                    })
                    ->whereExists(function ($query) use ($regieInfo) {
                        $query->select(DB::raw(1))
                            ->from('projet_agence as pcp')
                            ->whereRaw('pcp.code_projet = psp.code_projet')
                            ->where('pcp.code_agence', '=', $regieInfo->code_agence_execution)
                            ->where('pcp.niveau', '=', '1');
                    })
                    ->select(
                        'sp.libelle AS statut_projet',
                        DB::raw('SUM(IFNULL(pe.cout_projet, 0)) AS total_cout_projet2'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "01", pe.cout_projet, 0)) AS total_prevu'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "02", pe.cout_projet, 0)) AS total_en_cours'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "03", pe.cout_projet, 0)) AS total_annule'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "04", pe.cout_projet, 0)) AS total_cloture'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "05", pe.cout_projet, 0)) AS total_suspendu'),
                        DB::raw('SUM(IF(psp.code_statut_projet = "06", pe.cout_projet, 0)) AS total_redemarre')
                    )
                    ->groupBy('sp.libelle')
                    ->get();

                // Créer un tableau associatif pour stocker les résultats
                $projetsParStatut = [];

                // Initialiser le tableau avec des valeurs par défaut de 0 pour chaque statut
                $statuts = ['Prévu', 'En cours', 'Annulé', 'Clôturé', 'Suspendu', 'Redémarré'];
                foreach ($statuts as $statut) {
                    $projetsParStatut[$statut] = [
                        'total_cout_projet2' => 0,
                        'total_prevu' => 0,
                        'total_en_cours' => 0,
                        'total_annule' => 0,
                        'total_cloture' => 0,
                        'total_suspendu' => 0,
                        'total_redemarre' => 0,
                    ];
                }

                // Mettre à jour les valeurs avec celles obtenues dans la requête
                foreach ($statutsProjets as $statutProjet) {
                    $projetsParStatut[$statutProjet->statut_projet] = [
                        'total_cout_projet2' => $statutProjet->total_cout_projet2,
                        'total_prevu' => $statutProjet->total_prevu,
                        'total_en_cours' => $statutProjet->total_en_cours,
                        'total_annule' => $statutProjet->total_annule,
                        'total_cloture' => $statutProjet->total_cloture,
                        'total_suspendu' => $statutProjet->total_suspendu,
                        'total_redemarre' => $statutProjet->total_redemarre,
                    ];
                }
            }
        }
            return view('stat_fincance', compact('ecran','projets_cloture','projets_redemarrer','projets_suspendus','projets_annulé','projets_en_cours','personnelAffiche', 'projetsParStatut','projets_prevus'));

    }


    public function statNombreData(Request $request)
    {
        try {
            $ecran = Ecran::find($request->input('ecran_id'));

            // Récupérer le type (national ou personnel)
            $type = $request->input('type');

            // Initialiser la variable $statutsProjets
            $statutsProjets = collect();
            $personnelAffiche = '';

            if ($type === 'national') {
                // Si le type est national, récupérer tous les projets sans exception
                $statutsProjets = ProjetEha2::all();
            } else {
                // Récupérer le code région de l'utilisateur
                $region = CouvrirRegion::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                $code_region = $region->code_region;

                // Récupérer le code de fonction de l'utilisateur connecté
                $codeFonction = auth()->user()->latestFonction->fonctionUtilisateur->code;

                if ($codeFonction === 'cp') {
                    $chefprojet = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();

                    if ($chefprojet) {
                        if ($chefprojet->type_structure == 'agence_execution') {
                            $chefprojetInfo = AgenceExecution::where('code_agence_execution', $chefprojet->code_structure)->first();
                            $personnelAffiche = $chefprojetInfo ? $chefprojetInfo->nom_agence : '---';
                        } else if ($chefprojet->type_structure == 'ministere') {
                            $chefprojetInfo = Ministere::where('code', $chefprojet->code_structure)->first();
                            $personnelAffiche = $chefprojetInfo ? $chefprojetInfo->libelle : '---';
                        }

                        $statutsProjets = ProjetEha2::leftJoin('projet_statut_projet AS psp', 'projet_eha2.CodeProjet', '=', 'psp.code_projet')
                            ->leftJoin('statut_projet AS sp', 'sp.code', '=', 'psp.code_statut_projet')
                            ->leftJoin('projet_chef_projet AS pcp', 'pcp.code_projet', '=', 'psp.code_projet')
                            ->where('pcp.code_personnel', auth()->user()->personnel->code_personnel)
                            ->select('projet_eha2.*', 'sp.*', 'pcp.*')
                            ->get();
                    }
                } elseif ($codeFonction === 'ba') {
                    $bailleur = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();

                    if ($bailleur) {
                        $bailleurInfo = Bailleur::where('code_bailleur', $bailleur->code_structure)->first();
                        $personnelAffiche = $bailleurInfo ? $bailleurInfo->libelle_long : '---';
                        $statutsProjets = ProjetEha2::leftJoin('projet_statut_projet AS psp', 'projet_eha2.CodeProjet', '=', 'psp.code_projet')
                            ->leftJoin('bailleurs_projets AS pcp', 'pcp.code_projet', '=', 'psp.code_projet')
                            ->where('pcp.code_bailleur', $bailleurInfo->code_bailleur)
                            ->select('projet_eha2.*', 'psp.*', 'pcp.*')
                            ->get();
                    }
                } elseif ($codeFonction === 'dc') {
                    $ministere = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                    if ($ministere) {
                        $ministereInfo = Ministere::where('code', $ministere->code_structure)->first();
                        $personnelAffiche = $ministereInfo ? $ministereInfo->libelle : 'Directeur de cabinet';
                        $statutsProjets = ProjetEha2::leftJoin('projet_statut_projet AS psp', 'projet_eha2.CodeProjet', '=', 'psp.code_projet')
                            ->where('projet_eha2.code_ministere', $ministereInfo->code)
                            ->select('projet_eha2.*')
                            ->get();
                    }
                } elseif ($codeFonction === 'dr') {
                    $region = CouvrirRegion::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                    if ($region) {
                        $regionInfo = Region::where('code', $region->code_region)->first();
                        $personnelAffiche = $regionInfo ? $regionInfo->libelle : 'Directeur Régional';
                        $statutsProjets = ProjetEha2::leftJoin('projet_statut_projet AS psp', 'projet_eha2.CodeProjet', '=', 'psp.code_projet')
                            ->where('projet_eha2.code_region', $regionInfo->code)
                            ->select('projet_eha2.*')
                            ->get();
                    }
                } elseif ($codeFonction === 'mo') {
                    $maitreoeurvre = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                    if ($maitreoeurvre) {
                        $maitreoeurvreInfo = AgenceExecution::where('code_agence_execution', $maitreoeurvre->code_structure)->first();
                        $personnelAffiche = $maitreoeurvreInfo ? $maitreoeurvreInfo->nom_agence : '---';
                        $statutsProjets = ProjetEha2::leftJoin('projet_statut_projet AS psp', 'projet_eha2.CodeProjet', '=', 'psp.code_projet')
                            ->leftJoin('statut_projet AS sp', 'sp.code', '=', 'psp.code_statut_projet')
                            ->leftJoin('projet_agence AS pcp', 'pcp.code_projet', '=', 'psp.code_projet')
                            ->where('pcp.code_agence', $maitreoeurvreInfo->code_agence_execution)
                            ->where('pcp.niveau', '=', '2')
                            ->select('projet_eha2.*')
                            ->get();
                    }
                } elseif ($codeFonction === 'rf') {
                    $regie = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                    if ($regie) {
                        $regieInfo = AgenceExecution::where('code_agence_execution', $regie->code_structure)->first();
                        $personnelAffiche = $regieInfo ? $regieInfo->nom_agence : '---';
                        $statutsProjets = ProjetEha2::leftJoin('projet_statut_projet AS psp', 'projet_eha2.CodeProjet', '=', 'psp.code_projet')
                            ->leftJoin('projet_agence AS pcp', 'pcp.code_projet', '=', 'psp.code_projet')
                            ->where('pcp.code_agence', $regieInfo->code_agence_execution)
                            ->where('pcp.niveau', '=', '1')
                            ->select('projet_eha2.*', 'psp.*', 'pcp.*')
                            ->get();
                    }
                }
            }

            $Statuts = DB::table('projet_statut_projet')
                ->join('statut_projet', 'statut_projet.code', '=', 'projet_statut_projet.code_statut_projet')
                ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'projet_statut_projet.code_projet')
                ->select('projet_statut_projet.code', 'projet_eha2.CodeProjet', 'projet_statut_projet.code_statut_projet as codeSStatu', 'projet_statut_projet.date', 'statut_projet.libelle as statut_libelle')
                ->get();

            return view('stat_nombre_projet_lien', compact('ecran', 'personnelAffiche', 'statutsProjets', 'Statuts'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()]);
        }
    }



    public function statFinanceData(Request $request)
    {
        try {
            // Récupérer le pays
            $ecran = Ecran::find($request->input('ecran_id'));

            // Récupérer tous les projets
            $projets = ProjetEha2::all();

            // Récupérer le code région de l'utilisateur
            $region = CouvrirRegion::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();

            // Initialisation des variables
            $personnelAffiche = '';
            $chefprojet = null;
            $bailleurInfo = null;
            $ministereInfo = null;
            $regionInfo = null;
            $maitreoeurvreInfo = null;
            $regieInfo = null;

            $userCode = auth()->user()->latestFonction->fonctionUtilisateur->code;

            // Déterminer la valeur de $personnelAffiche en fonction du groupe utilisateur
            if ($userCode === 'cp') {
                $chefprojet = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();

                if ($chefprojet) {
                    if ($chefprojet->type_structure == 'agence_execution') {
                        $chefprojetInfo = AgenceExecution::where('code_agence_execution', $chefprojet->code_structure)->first();
                        $personnelAffiche = $chefprojetInfo ? $chefprojetInfo->nom_agence : '---';
                    } else if ($chefprojet->type_structure == 'ministere') {
                        $chefprojetInfo = Ministere::where('code', $chefprojet->code_structure)->first();
                        $personnelAffiche = $chefprojetInfo ? $chefprojetInfo->libelle : '---';
                    }
                }
            } elseif ($userCode === 'ba') {
                $bailleur = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                if ($bailleur) {
                    $bailleurInfo = Bailleur::where('code_bailleur', $bailleur->code_structure)->first();
                    $personnelAffiche = $bailleurInfo ? $bailleurInfo->libelle_long : '---';
                }
            } elseif ($userCode === 'dc') {
                $ministere = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                if ($ministere) {
                    $ministereInfo = Ministere::where('code', $ministere->code_structure)->first();
                    $personnelAffiche = $ministereInfo ? $ministereInfo->libelle : 'Directeur de cabinet';
                }
            } elseif ($userCode === 'dr') {
                $region = CouvrirRegion::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                if ($region) {
                    $regionInfo = Region::where('code', $region->code_region)->first();
                    $personnelAffiche = $regionInfo ? $regionInfo->libelle : 'Directeur Régional';
                }
            } elseif ($userCode === 'mo') {
                $maitreoeurvre = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                if ($maitreoeurvre) {
                    $maitreoeurvreInfo = AgenceExecution::where('code_agence_execution', $maitreoeurvre->code_structure)->first();
                    $personnelAffiche = $maitreoeurvreInfo ? $maitreoeurvreInfo->nom_agence : '---';
                }
            } elseif ($userCode === 'rf') {
                $regie = StructureRattachement::where('code_personnel', auth()->user()->personnel->code_personnel)->latest('date')->first();
                if ($regie) {
                    $regieInfo = AgenceExecution::where('code_agence_execution', $regie->code_structure)->first();
                    $personnelAffiche = $regieInfo ? $regieInfo->nom_agence : '---';
                }
            }

            // Vérifier si l'utilisateur a cliqué sur "National" ou sur le lien avec $personnelAffiche
            if ($request->input('type') === 'national') {
                // Afficher toutes les données de ProjetEha2
                $statutsProjets = ProjetEha2::all();
            } else {
                // Filtrer les données en fonction de la logique spécifique
                $statutsProjets = ProjetEha2::leftJoin('projet_statut_projet as psp', 'projet_eha2.CodeProjet', '=', 'psp.code_projet')
                    ->leftJoin('statut_projet as sp', 'sp.code', '=', 'psp.code_statut_projet')
                    ->when($userCode === 'cp', function ($query) use ($chefprojet) {
                        return $query->whereExists(function ($query) use ($chefprojet) {
                            $query->select(DB::raw(1))
                                ->from('projet_chef_projet as pcp')
                                ->whereRaw('pcp.code_projet = psp.code_projet')
                                ->where('pcp.code_personnel', '=', $chefprojet->code_personnel);
                        });
                    })
                    ->when($userCode === 'ba', function ($query) use ($bailleurInfo) {
                        return $query->whereExists(function ($query) use ($bailleurInfo) {
                            $query->select(DB::raw(1))
                                ->from('bailleurs_projets as pcp')
                                ->whereRaw('pcp.code_projet = psp.code_projet')
                                ->where('pcp.code_bailleur', '=', $bailleurInfo->code_bailleur);
                        });
                    })
                    ->when($userCode === 'dc', function ($query) use ($ministereInfo) {
                        return $query->where('projet_eha2.code_ministere', '=', $ministereInfo->code);
                    })
                    ->when($userCode === 'dr', function ($query) use ($regionInfo) {
                        return $query->where('projet_eha2.code_region', '=', $regionInfo->code);
                    })
                    ->when($userCode === 'mo', function ($query) use ($maitreoeurvreInfo) {
                        return $query->whereExists(function ($query) use ($maitreoeurvreInfo) {
                            $query->select(DB::raw(1))
                                ->from('projet_agence as pcp')
                                ->whereRaw('pcp.code_projet = psp.code_projet')
                                ->where('pcp.code_agence', '=', $maitreoeurvreInfo->code_agence_execution)
                                ->where('pcp.niveau', '=', '2');
                        });
                    })
                    ->when($userCode === 'rf', function ($query) use ($regieInfo) {
                        return $query->whereExists(function ($query) use ($regieInfo) {
                            $query->select(DB::raw(1))
                                ->from('projet_agence as pcp')
                                ->whereRaw('pcp.code_projet = psp.code_projet')
                                ->where('pcp.code_agence', '=', $regieInfo->code_agence_execution)
                                ->where('pcp.niveau', '=', '1');
                        });
                    })
                    ->select('projet_eha2.*')
                    ->get();
            }

            $Statuts = DB::table('projet_statut_projet')
                ->join('statut_projet', 'statut_projet.code', '=', 'projet_statut_projet.code_statut_projet')
                ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'projet_statut_projet.code_projet')
                ->select('projet_statut_projet.code', 'projet_eha2.CodeProjet', 'projet_statut_projet.code_statut_projet as codeSStatu', 'projet_statut_projet.date', 'statut_projet.libelle as statut_libelle')
                ->get();

            return view('stat_fincance_lien', compact('ecran', 'personnelAffiche', 'statutsProjets', 'Statuts'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()]);
        }
    }




}
