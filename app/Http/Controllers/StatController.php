<?php

namespace App\Http\Controllers;

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

        // Déterminer la valeur de $personnelAffiche en fonction du code de fonction
        if ($codeFonction === 'ad' ) { // Administrateur ou Chef de projet
            $personnelAffiche = 'Personnel';
        }

        elseif($codeFonction === 'cp'){
            $personnelAffiche = 'Personnel';
        }

        elseif ($codeFonction === 'ba') { // Bailleur
            // Récupérer les données du bailleur
            $bailleur = BailleursProjet::where('code_bailleur', auth()->user()->personnel->code)->first();
            $personnelAffiche = $bailleur ? $bailleur->libelle_long : '';
        }

        elseif ($codeFonction === 'dc') { // Directeur de cabinet
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
        if ($userCode === 'ad' ) {
            $personnelAffiche = 'Personnel';
        }elseif( $userCode === 'cp'){
            $personnelAffiche = 'Personnel';
        }
        elseif ($userCode === 'ba') {
            // Récupérer les données du bailleur
            $bailleur = BailleursProjet::where('code_bailleur', auth()->user()->personnel->code)->first();
            $personnelAffiche = $bailleur ? $bailleur->libelle_long : '';
        } elseif ($userCode === 'dc') {
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
        } elseif ($userCode === 'dr') {
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

        }
            return view('stat_fincance', compact('ecran','projets_cloture','projets_redemarrer','projets_suspendus','projets_annulé','projets_en_cours','personnelAffiche', 'projetsParStatut','projets_prevus'));

    }

}
