<?php

namespace App\Http\Controllers;

use App\Models\Bailleur;
use App\Models\ProjetEha2;
use App\Models\StatutProjet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class GeoJSONController extends Controller
{
    private $filePaths;

    public function __construct()
    {
        // Initialize file paths
        $this->filePaths = [
            'district' => Storage::disk('geojson')->path('District.geojson.js'),
            'region' => Storage::disk('geojson')->path('Region.geojson.js'),
            'cout_district' => Storage::disk('geojson')->path('Cout.geojson.js'),
            'cout_region' => Storage::disk('geojson')->path('CoutRegion.geojson.js'),
            'department' => Storage::disk('geojson')->path('Department.geojson.js'),
            'department_cout' => Storage::disk('geojson')->path('CoutDepartment.geojson.js'),
            //Les fichiers temporaires
            'district_temp' => Storage::disk('geojson')->path('District_temp.geojson.js'),
            'region_temp' => Storage::disk('geojson')->path('Region_temp.geojson.js'),
            'cout_district_temp' => Storage::disk('geojson')->path('Cout_temp.geojson.js'),
            'cout_region_temp' => Storage::disk('geojson')->path('CoutRegion_temp.geojson.js'),
        ];

        // Create the directory when the controller is instantiated
        if (!Storage::disk('geojson')->exists('')) {
            Storage::disk('geojson')->makeDirectory('');
        }
    }
    public function showSIG(Request $request)
    {

         // Vérifiez les filtres dans la requête
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $status = $request->input('status');
        $bailleur = $request->input('bailleur');
        $dateType = $request->input('date_type');

        $filtersApplied = $startDate || $endDate || $status || $bailleur || $dateType;

        if ($filtersApplied) {
            // Si des filtres sont présents, appliquez-les
            return $this->filter($request);
        }else{
            $this->addDistrictToGeoJSON();
            $this->addRegionToGeoJSON();
            $this->addCoutProjetDistrictToGeoJSON();
            $this->addCoutProjetRegionToGeoJSON();
            $this->addDepartmentToGeoJSON();
            $this->addDepartmentCoutToGeoJSON();
        }

        $bailleur = Bailleur::all();
        $statut = StatutProjet::all();

        return view('sig', compact('bailleur', 'statut', 'filtersApplied'));
    }

    //DISTRICT
    public function addDistrictToGeoJSON()
    {
        try {

            $districts = DB::table('district')
                ->select(
                    'district.libelle as district_name',
                    DB::raw('COUNT(projet_eha2.CodeProjet) as total_projects'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')

                )
                ->leftJoin('region', 'region.code_district', '=', 'district.code')
                ->leftJoin('projet_eha2', 'region.code', '=', 'projet_eha2.code_region')
                ->groupBy('district.libelle')
                ->get();

            $districts_total = DB::table('projet_eha2')
                ->select(
                    DB::raw('COUNT(projet_eha2.CodeProjet) AS total_projects_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités_total')
                )
                ->get();

            if ($districts->isEmpty() || $districts_total->isEmpty()) {
                return 'Aucune donnée à ajouter.';
            }

            $filePath = Storage::disk('geojson')->path('District.geojson.js');
            $geojsonContent = file_get_contents($filePath);
            $geojsonData = $geojsonContent ? json_decode($geojsonContent, true) : null;

            if ($geojsonData === null) {
                $geojsonData = [
                    'type' => 'FeatureCollection',
                    'features' => [],
                ];
            }

            // Initialiser les valeurs totales une seule fois
            $district_total = isset($districts_total[0]) ? $districts_total[0] : null;

            foreach ($districts as $district) {
                $newFeature = [
                    'type' => 'Feature',
                    'properties' => [
                        'NAME_1' => $district->district_name,
                        'PROJET_NUM' => (int) $district->total_projects,
                        'AEP' => (int) $district->Alimentation_en_Eau_Potable,
                        'AD' => (int) $district->Assainissement_et_Drainage,
                        'HY' => (int) $district->Hygiène,
                        'REE' => (int) $district->Ressources_en_Eau,
                        'EHAES' => (int) $district->EHA_dans_les_Etablissements_de_Santé,
                        'EHAEE' => (int) $district->EHA_dans_les_Etablissements_d_Enseignement,
                        'EHAEEn' => (int) $district->EHA_dans_les_autres_Entités,
                        // Totaux
                        'PROJET_NUM_T' => $district_total ? (int) $district_total->total_projects_total : 0,
                        'AEP_T' => $district_total ? (int) $district_total->Alimentation_en_Eau_Potable_total : 0,
                        'AD_T' => $district_total ? (int) $district_total->Assainissement_et_Drainage_total : 0,
                        'HY_T' => $district_total ? (int) $district_total->Hygiène_total : 0,
                        'REE_T' => $district_total ? (int) $district_total->Ressources_en_Eau_total : 0,
                        'EHAES_T' => $district_total ? (int) $district_total->EHA_dans_les_Etablissements_de_Santé_total : 0,
                        'EHAEE_T' => $district_total ? (int) $district_total->EHA_dans_les_Etablissements_d_Enseignement_total : 0,
                        'EHAEEn_T' => $district_total ? (int) $district_total->EHA_dans_les_autres_Entités_total : 0,
                    ],
                    'geometry' => [
                        'type' => 'MultiPolygon',  // Remplissez les coordonnées ici
                    ],
                ];

                $geojsonData['features'][] = $newFeature;
            }

            file_put_contents($this->filePaths['district'], 'var statesDataDistrictsBD = ' . json_encode($geojsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');

            return view('district');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return 'Une erreur s\'est produite.';
        }
    }

    public function addCoutProjetDistrictToGeoJSON()
    {
        try {
            // Obtenir les données des projets par région
            $montantDistrict_cout = DB::table('district')
                ->select(
                    'district.libelle as district_name',
                    DB::raw('COUNT(projet_eha2.cout_projet) as total_projects'),
                    DB::raw('SUM(projet_eha2.cout_projet) as coutProjet'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')
                )
                ->leftJoin('region', 'region.code_district', '=', 'district.code')
                ->leftJoin('projet_eha2', 'region.code', '=', 'projet_eha2.code_region')
                ->groupBy('district.libelle')
                ->get();

                $districts_total_cout = DB::table('projet_eha2')
                    ->selectRaw('COUNT(*) AS total_projects_total')
                    ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 1 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable_total')
                    ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 2 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage_total')
                    ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 3 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS Hygiène_total')
                    ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 4 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau_total')
                    ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 5 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé_total')
                    ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 6 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement_total')
                    ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 7 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités_total')
                    ->first();


            // Vérifier s'il y a des données à ajouter
            if (empty($montantDistrict_cout) || empty($districts_total_cout)) {

                return 'Aucune donnée à ajouter.';
            }

            // Charger le contenu du fichier GeoJSON existant
            $filePath_cout = Storage::disk('geojson')->path('Cout.geojson.js');
            $geojsonContent_cout = file_get_contents($filePath_cout);

            // Vérifier si $geojsonData est null et initialiser si nécessaire
            $geojsonData_cout = $geojsonContent_cout ? json_decode($geojsonContent_cout, true) : null;

            if ($geojsonData_cout === null) {
                // Initialiser $geojsonData si c'est null
                $geojsonData_cout = [
                    'type' => 'FeatureCollection',
                    'features' => [],
                ];
            }

            // Initialiser les valeurs totales une seule fois
            $district_total_cout = $districts_total_cout ? $districts_total_cout : null;


            foreach ($montantDistrict_cout as $district) {
                $newFeature = [
                    'type' => 'Feature',
                    'properties' => [
                        'NAME_1' => $district->district_name,
                        'MontantTotal' => $district->total_projects,
                        'CoutProjet' => (int) $district->coutProjet,
                        'AEP' => (int) $district->Alimentation_en_Eau_Potable,
                        'AD' => (int) $district->Assainissement_et_Drainage,
                        'HY' => (int) $district->Hygiène,
                        'REE' => (int) $district->Ressources_en_Eau,
                        'EHAES' => (int) $district->EHA_dans_les_Etablissements_de_Santé,
                        'EHAEE' => (int) $district->EHA_dans_les_Etablissements_d_Enseignement,
                        'EHAEEn' => (int) $district->EHA_dans_les_autres_Entités,
                        // Totaux
                        'MontantTotal_T' => $district_total_cout ? (int) $district_total_cout->total_projects_total : 0,
                        'AEP_T' => $district_total_cout ? (int) $district_total_cout->Alimentation_en_Eau_Potable_total : 0,
                        'AD_T' => $district_total_cout ? (int) $district_total_cout->Assainissement_et_Drainage_total : 0,
                        'HY_T' => $district_total_cout ? (int) $district_total_cout->Hygiène_total : 0,
                        'REE_T' => $district_total_cout ? (int) $district_total_cout->Ressources_en_Eau_total : 0,
                        'EHAES_T' => $district_total_cout ? (int) $district_total_cout->EHA_dans_les_Etablissements_de_Santé_total : 0,
                        'EHAEE_T' => $district_total_cout ? (int) $district_total_cout->EHA_dans_les_Etablissements_d_Enseignement_total : 0,
                        'EHAEEn_T' => $district_total_cout ? (int) $district_total_cout->EHA_dans_les_autres_Entités_total : 0,
                    ],
                    'geometry' => [
                        'type' => 'MultiPolygon',  // Remplissez les coordonnées ici
                    ],
                ];

                $geojsonData_cout['features'][] = $newFeature;
            }

            // Sauvegarder le fichier GeoJSON mis à jour
            file_put_contents($this->filePaths['cout_district'], 'var montantBD = ' . json_encode($geojsonData_cout, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');
            return View::make('cout_district');
        } catch (\Exception $e) {
            // Imprimez l'erreur dans les logs
            Log::error($e->getMessage());
            return 'Une erreur s\'est produite.';
        }
    }
    //REGION
    public function addRegionToGeoJSON()
    {
        try {
            // Obtenir les données des projets par région
            $regions_nbr = DB::table('region')
                ->select(
                    'district.libelle as district_name',
                    'region.libelle as region_name',
                    DB::raw('COUNT(projet_eha2.CodeProjet) as total_projects'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')

                )
                ->leftJoin('district', 'region.code_district', '=', 'district.code')
                ->leftJoin('projet_eha2', 'region.code', '=', 'projet_eha2.code_region')
                ->groupBy('district.libelle', 'region.libelle')
                ->get();

            // Vérifier s'il y a des données à ajouter
            if ($regions_nbr->isEmpty()) {
                return 'Aucune donnée à ajouter.';
            }

            // Charger le contenu du fichier GeoJSON existant
            $filePath_region_nbr = Storage::disk('geojson')->path('Region.geojson.js');
            $geojsonContent_region_nbr = file_get_contents($filePath_region_nbr);

            // Vérifier si $geojsonData est null et initialiser si nécessaire
            $geojsonData_region_nbr = $geojsonContent_region_nbr ? json_decode($geojsonContent_region_nbr, true) : null;

            if ($geojsonData_region_nbr === null) {
                // Initialiser $geojsonData si c'est null
                $geojsonData_region_nbr = [
                    'type' => 'FeatureCollection',
                    'features' => [],
                ];
            }

            // Ajouter de nouvelles données à la propriété features
            foreach ($regions_nbr as $region) {
                $newFeature = [
                    'type' => 'Feature',
                    'properties' => [
                        'NAME_1' => $region->district_name,
                        'NAME_2' => $region->region_name,
                        'PROJET_NUM' => (int) $region->total_projects,
                        'AEP' => (int) $region->Alimentation_en_Eau_Potable,
                        'AD' => (int) $region->Assainissement_et_Drainage,
                        'HY' => (int) $region->Hygiène,
                        'REE' => (int) $region->Ressources_en_Eau,
                        'EHAES' => (int) $region->EHA_dans_les_Etablissements_de_Santé,
                        'EHAEE' => (int) $region->EHA_dans_les_Etablissements_d_Enseignement,
                        'EHAEEn' => (int) $region->EHA_dans_les_autres_Entités,
                    ],
                    'geometry' => [
                        'type' => 'MultiPolygon',  // Vous devrez remplir les coordonnées ici
                    ],
                ];

                $geojsonData_region_nbr['features'][] = $newFeature;
            }

            // Sauvegarder le fichier GeoJSON mis à jour
            file_put_contents($this->filePaths['region'], 'var statesDataRegionsBD = ' . json_encode($geojsonData_region_nbr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');
            return View::make('region');
        } catch (\Exception $e) {
            // Imprimez l'erreur dans les logs
            Log::error($e->getMessage());
            return 'Une erreur s\'est produite.';
        }
    }

    public function addCoutProjetRegionToGeoJSON()
    {
        try {
            // Obtenir les données des projets par région
            $montantRegion_cout_mon = DB::table('projet_eha2')
                ->join('region', 'region.code', '=', 'projet_eha2.code_region')
                ->join('district', 'district.code', '=', 'region.code_district')
                ->select(
                    DB::raw('SUM(projet_eha2.cout_projet) AS coutProjet'),
                    'district.libelle AS nomDistrict',
                    'region.libelle AS nomRegion',
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS Alimentation_en_Eau_Potable'),
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS Assainissement_et_Drainage'),
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS Hygiène'),
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS Ressources_en_Eau'),
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN projet_eha2.cout_projet ELSE 0 END) AS EHA_dans_les_Etablissements_de_Santé'),
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN projet_eha2.cout_projet ELSE 0 END) AS EHA_dans_les_Etablissements_d_Enseignement'),
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN projet_eha2.cout_projet ELSE 0 END) AS EHA_dans_les_autres_Entités')

                )
                ->groupBy('district.libelle', 'region.libelle')
                ->get();

            // Vérifier s'il y a des données à ajouter
            if ($montantRegion_cout_mon->isEmpty()) {
                return 'Aucune donnée à ajouter.';
            }

            // Charger le contenu du fichier GeoJSON existant
            $filePath_cout_mon = Storage::disk('geojson')->path('CoutRegion.geojson.js');
            $geojsonContent_cout_mon = file_get_contents($filePath_cout_mon);

            // Vérifier si $geojsonData est null et initialiser si nécessaire
            $geojsonData_cout_mon = $geojsonContent_cout_mon ? json_decode($geojsonContent_cout_mon, true) : null;

            if ($geojsonData_cout_mon === null) {
                // Initialiser $geojsonData si c'est null
                $geojsonData_cout_mon = [
                    'type' => 'FeatureCollection',
                    'features' => [],
                ];
            }

            // Ajouter de nouvelles données à la propriété features
            foreach ($montantRegion_cout_mon as $montantR) {
                $newFeature = [
                    'type' => 'Feature',
                    'properties' => [
                        'NAME_1' => $montantR->nomDistrict,
                        'NAME_2' => $montantR->nomRegion,
                        'MontantTotal' => $montantR->coutProjet,
                        'AEP' => $montantR->Alimentation_en_Eau_Potable,
                        'AD' =>  $montantR->Assainissement_et_Drainage,
                        'HY' => $montantR->Hygiène,
                        'REE' => $montantR->Ressources_en_Eau,
                        'EHAES' => $montantR->EHA_dans_les_Etablissements_de_Santé,
                        'EHAEE' => $montantR->EHA_dans_les_Etablissements_d_Enseignement,
                        'EHAEEn' => $montantR->EHA_dans_les_autres_Entités,
                    ],
                ];

                $geojsonData_cout_mon['features'][] = $newFeature;
            }

            // Sauvegarder le fichier GeoJSON mis à jour
            file_put_contents($this->filePaths['cout_region'], 'var montantRegion = ' . json_encode($geojsonData_cout_mon, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');

        } catch (\Exception $e) {
            // Imprimez l'erreur dans les logs
            Log::error($e->getMessage());
            return 'Une erreur s\'est produite.';
        }
    }

    //DEPARTEMENT
    //en nombre de projet
    public function addDepartmentToGeoJSON()
    {
        try {
            // Requête pour obtenir les données des départements et des projets associés
            $departments = DB::table('action_beneficiaires_projet')
                ->select(
                    'departement.libelle as department_name',
                    DB::raw('COUNT(projet_eha2.CodeProjet) as total_projects'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')
                )
                ->join('departement', 'departement.code', '=', 'action_beneficiaires_projet.beneficiaire_id')
                ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'action_beneficiaires_projet.CodeProjet')
                ->where('action_beneficiaires_projet.type_beneficiaire', 'departement')
                ->groupBy('departement.libelle')
                ->get();

            // Requête pour obtenir les totaux globaux pour les projets
            $departments_total = DB::table('action_beneficiaires_projet')
                ->select(
                    DB::raw('COUNT(projet_eha2.CodeProjet) AS total_projects_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement_total'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités_total')
                )
                ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'action_beneficiaires_projet.CodeProjet')
                ->where('action_beneficiaires_projet.type_beneficiaire', 'departement')
                ->get();


                if ($departments->isEmpty() || $departments_total->isEmpty()) {
                    return 'Aucune donnée à ajouter.';
                }

                // Charger le contenu GeoJSON existant
                $filePath = Storage::disk('geojson')->path('Department.geojson.js');
                $geojsonContent = file_get_contents($filePath);
                $geojsonData_depart = $geojsonContent ? json_decode($geojsonContent, true) : null;

                if ($geojsonData_depart === null) {
                    $geojsonData_depart = [
                        'type' => 'FeatureCollection',
                        'features' => [],
                    ];
                }

                // Initialiser les valeurs totales une seule fois
                $department_total = isset($departments_total[0]) ? $departments_total[0] : null;

                foreach ($departments as $department) {
                    $newFeature = [
                        'type' => 'Feature',
                        'properties' => [
                            'NAME_1' => $department->districts_name,
                            'NAME_2' => $department->regions_name,
                            'NAME_3' => $department->department_name,
                            'PROJET_NUM' => (int) $department->total_projects,
                            'AEP' => (int) $department->Alimentation_en_Eau_Potable,
                            'AD' => (int) $department->Assainissement_et_Drainage,
                            'HY' => (int) $department->Hygiène,
                            'REE' => (int) $department->Ressources_en_Eau,
                            'EHAES' => (int) $department->EHA_dans_les_Etablissements_de_Santé,
                            'EHAEE' => (int) $department->EHA_dans_les_Etablissements_d_Enseignement,
                            'EHAEEn' => (int) $department->EHA_dans_les_autres_Entités,
                            // Totaux
                            'PROJET_NUM_T' => $department_total ? (int) $department_total->total_projects_total : 0,
                            'AEP_T' => $department_total ? (int) $department_total->Alimentation_en_Eau_Potable_total : 0,
                            'AD_T' => $department_total ? (int) $department_total->Assainissement_et_Drainage_total : 0,
                            'HY_T' => $department_total ? (int) $department_total->Hygiène_total : 0,
                            'REE_T' => $department_total ? (int) $department_total->Ressources_en_Eau_total : 0,
                            'EHAES_T' => $department_total ? (int) $department_total->EHA_dans_les_Etablissements_de_Santé_total : 0,
                            'EHAEE_T' => $department_total ? (int) $department_total->EHA_dans_les_Etablissements_d_Enseignement_total : 0,
                            'EHAEEn_T' => $department_total ? (int) $department_total->EHA_dans_les_autres_Entités_total : 0,
                        ],
                        'geometry' => [
                            'type' => 'MultiPolygon',  // Remplissez les coordonnées ici
                        ],
                    ];

                    $geojsonData_depart['features'][] = $newFeature;
                }

                // Écrire les données GeoJSON dans le fichier
                file_put_contents($this->filePaths['department'], 'var statesDataDepartmentsBD = ' . json_encode($geojsonData_depart, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');
                return View::make('department');

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return 'Une erreur s\'est produite.';
        }
    }

    //en financement
    public function addDepartmentCoutToGeoJSON()
    {
        try{
            // Requête pour obtenir les données par département
            $departmentsFinan = DB::table('action_beneficiaires_projet')
            ->select(
                'departement.libelle as department_name',
                DB::raw('SUM(projet_eha2.cout_projet) as total_project_cost'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')
            )
            ->join('departement', 'departement.code', '=', 'action_beneficiaires_projet.beneficiaire_id')
            ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'action_beneficiaires_projet.CodeProjet')
            ->where('action_beneficiaires_projet.type_beneficiaire', 'departement')
            ->groupBy('departement.libelle')
            ->get();

            // Requête pour obtenir les totaux globaux pour les projets
            $departments_totalFinan = DB::table('action_beneficiaires_projet')
            ->select(
                DB::raw('SUM(projet_eha2.cout_projet) AS total_project_cost_total'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable_total'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage_total'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Hygiène_total'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau_total'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé_total'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement_total'),
                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités_total')
            )
            ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'action_beneficiaires_projet.CodeProjet')
            ->where('action_beneficiaires_projet.type_beneficiaire', 'departement')
            ->get();

            if ($departmentsFinan->isEmpty() || $departments_totalFinan->isEmpty()) {
                return 'Aucune donnée à ajouter.';
            }

            // Charger le contenu GeoJSON existant
            $filePath = Storage::disk('geojson')->path('CoutDepartment.geojson.js');
            $geojsonContent = file_get_contents($filePath);
            $geojsonData_departFinan = $geojsonContent ? json_decode($geojsonContent, true) : null;

            if ($geojsonData_departFinan === null) {
            $geojsonData_departFinan = [
                'type' => 'FeatureCollection',
                'features' => [],
            ];
            }

            // Initialiser les valeurs totales une seule fois
            $department_totalFinan = isset($departments_totalFinan[0]) ? $departments_totalFinan[0] : null;

            foreach ($departmentsFinan as $departmentFinan) {
            $newFeature = [
                'type' => 'Feature',
                'properties' => [
                    'NAME_3' => $departmentFinan->department_name,
                    'PROJET_NUM' => (int) $departmentFinan->total_project_cost, // Corrigé de 'total_projects'
                    'AEP' => (int) $departmentFinan->Alimentation_en_Eau_Potable,
                    'AD' => (int) $departmentFinan->Assainissement_et_Drainage,
                    'HY' => (int) $departmentFinan->Hygiène,
                    'REE' => (int) $departmentFinan->Ressources_en_Eau,
                    'EHAES' => (int) $departmentFinan->EHA_dans_les_Etablissements_de_Santé,
                    'EHAEE' => (int) $departmentFinan->EHA_dans_les_Etablissements_d_Enseignement,
                    'EHAEEn' => (int) $departmentFinan->EHA_dans_les_autres_Entités,
                    // Totaux
                    'PROJET_NUM_T' => $department_totalFinan ? (int) $department_totalFinan->total_project_cost_total : 0,
                    'AEP_T' => $department_totalFinan ? (int) $department_totalFinan->Alimentation_en_Eau_Potable_total : 0,
                    'AD_T' => $department_totalFinan ? (int) $department_totalFinan->Assainissement_et_Drainage_total : 0,
                    'HY_T' => $department_totalFinan ? (int) $department_totalFinan->Hygiène_total : 0,
                    'REE_T' => $department_totalFinan ? (int) $department_totalFinan->Ressources_en_Eau_total : 0,
                    'EHAES_T' => $department_totalFinan ? (int) $department_totalFinan->EHA_dans_les_Etablissements_de_Santé_total : 0,
                    'EHAEE_T' => $department_totalFinan ? (int) $department_totalFinan->EHA_dans_les_Etablissements_d_Enseignement_total : 0,
                    'EHAEEn_T' => $department_totalFinan ? (int) $department_totalFinan->EHA_dans_les_autres_Entités_total : 0,
                ],
                'geometry' => [
                    'type' => 'MultiPolygon',  // Remplissez les coordonnées ici
                ],
            ];

            $geojsonData_departFinan['features'][] = $newFeature;
            }

            // Écrire les données GeoJSON dans le fichier
            file_put_contents($filePath, 'var statesDataDepartmentsCoutBD = ' . json_encode($geojsonData_departFinan, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');

            return View::make('department_cout');

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return 'Une erreur s\'est produite.';
        }
    }



    /////////////////////////////////////////FILTRE//////////////////////////////////////////



    public function filter(Request $request)
    {

        try {
            // Récupération des paramètres de la requête
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $status = $request->input('status');
            $bailleur = $request->input('bailleur');
            $dateType = $request->input('date_type');

            // Initialisation de la requête Eloquent
            $query = ProjetEha2::query();

            // Vérification si aucun filtre n'est sélectionné
            $noFiltersSelected = empty($startDate) && empty($endDate) && empty($status) && empty($bailleur) ;
            $filtersApplied = $startDate || $endDate || $status || $bailleur || $dateType;

            if($noFiltersSelected){
                if ($dateType == 'Tous'){
                    //Tous les projets
                    $projects = ProjetEha2::all();

                    // Récupérer les IDs des projets filtrés
                    $projectIds = $projects->pluck('CodeProjet');

                    ///////////DISTRICT NOMBRE///////////
                        // Calcul des statistiques des districts basées sur les projets filtrés
                        $districts = DB::table('district')
                            ->select(
                                'district.libelle as district_name',
                                DB::raw('COUNT(projet_eha2.CodeProjet) as total_projects'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')
                            )
                            ->leftJoin('region', 'region.code_district', '=', 'district.code')
                            ->leftJoin('projet_eha2', 'region.code', '=', 'projet_eha2.code_region')
                            ->whereIn('projet_eha2.CodeProjet', $projectIds)
                            ->groupBy('district.libelle')
                            ->get();

                        $districts_total = DB::table('projet_eha2')
                            ->select(
                                DB::raw('COUNT(projet_eha2.CodeProjet) AS total_projects_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités_total')
                            )
                            ->whereIn('CodeProjet', $projectIds)
                            ->get();
                    ///////////FIN DISTRICT NOMBRE///////

                    ///////////REGION NOMBRE/////////
                        // Obtenir les données des projets par région
                        $regions_nbr = DB::table('region')
                            ->select(
                                'district.libelle as district_name',
                                'region.libelle as region_name',
                                DB::raw('COUNT(projet_eha2.CodeProjet) as total_projects'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')

                            )
                            ->leftJoin('district', 'region.code_district', '=', 'district.code')
                            ->leftJoin('projet_eha2', 'region.code', '=', 'projet_eha2.code_region')
                            ->whereIn('projet_eha2.CodeProjet', $projectIds)
                            ->groupBy('district.libelle', 'region.libelle')
                            ->get();
                    ///////////FIN REGION NOMBRE//////

                    ///////////DISTRICT COUT//////////
                        $montantDistrict_cout = DB::table('district')
                        ->select(
                            'district.libelle as district_name',
                            DB::raw('COUNT(projet_eha2.cout_projet) as total_projects'),
                            DB::raw('SUM(projet_eha2.cout_projet) as coutProjet'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')
                        )
                        ->leftJoin('region', 'region.code_district', '=', 'district.code')
                        ->leftJoin('projet_eha2', 'region.code', '=', 'projet_eha2.code_region')
                        ->whereIn('projet_eha2.CodeProjet', $projectIds)
                        ->groupBy('district.libelle')
                        ->get();

                        $districts_total_cout = DB::table('projet_eha2')
                            ->selectRaw('COUNT(*) AS total_projects_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 1 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 2 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 3 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS Hygiène_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 4 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 5 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 6 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 7 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités_total')
                            ->whereIn('projet_eha2.CodeProjet', $projectIds)
                            ->first();
                    //////////FIN DISTRICT COUT////////

                    /////////REGION COUT///////////
                        $montantRegion_cout_mon = DB::table('projet_eha2')
                        ->join('region', 'region.code', '=', 'projet_eha2.code_region')
                        ->join('district', 'district.code', '=', 'region.code_district')
                        ->select(
                            DB::raw('SUM(projet_eha2.cout_projet) AS coutProjet'),
                            'district.libelle AS nomDistrict',
                            'region.libelle AS nomRegion',
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS Alimentation_en_Eau_Potable'),
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS Assainissement_et_Drainage'),
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS Hygiène'),
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS Ressources_en_Eau'),
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN projet_eha2.cout_projet ELSE 0 END) AS EHA_dans_les_Etablissements_de_Santé'),
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN projet_eha2.cout_projet ELSE 0 END) AS EHA_dans_les_Etablissements_d_Enseignement'),
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN projet_eha2.cout_projet ELSE 0 END) AS EHA_dans_les_autres_Entités')

                        )
                        ->whereIn('projet_eha2.CodeProjet', $projectIds)
                        ->groupBy('district.libelle', 'region.libelle')
                        ->get();

                    ////////REGION COUT/////////

                    ///////// DEPARTEMENT NOMBRE /////////
                      $departments = DB::table('action_beneficiaires_projet')
                        ->select(
                            'departement.libelle as department_name',
                            'district.libelle as districts_name',
                            'region.libelle as regions_name',
                            DB::raw('COUNT(projet_eha2.CodeProjet) as total_projects'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')
                        )
                        ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'action_beneficiaires_projet.CodeProjet')
                        ->join('departement', 'departement.code', '=', 'action_beneficiaires_projet.beneficiaire_id')
                        ->join('region', 'region.code', '=', 'departement.code_region')
                        ->join('district', 'district.code', '=', 'region.code_district')
                        ->where('action_beneficiaires_projet.type_beneficiaire', 'departement')
                        ->whereIn('projet_eha2.CodeProjet', $projectIds)
                        ->groupBy('departement.libelle', 'district.libelle', 'region.libelle')
                        ->get();

                        // Requête pour obtenir les totaux globaux pour les projets
                        $departments_total = DB::table('action_beneficiaires_projet')
                        ->select(
                            DB::raw('COUNT(projet_eha2.CodeProjet) AS total_projects_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités_total')
                        )
                        ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'action_beneficiaires_projet.CodeProjet')
                        ->join('region', 'region.code', '=', 'projet_eha2.code_region')
                        ->join('district', 'district.code', '=', 'region.code_district')
                        ->whereIn('projet_eha2.CodeProjet', $projectIds)
                        ->where('action_beneficiaires_projet.type_beneficiaire', 'departement')
                        ->get();

                    ///////// FIN DEPARTEMENT NOMBRE /////////

                    /////////DEPARTEMENT COUT /////////
                        $departmentsFinan = DB::table('action_beneficiaires_projet')
                        ->select(
                        'departement.libelle as department_name',
                        DB::raw('SUM(projet_eha2.cout_projet) as total_project_cost'),
                        DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                        DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                        DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                        DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                        DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                        DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                        DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')
                        )
                        ->join('departement', 'departement.code', '=', 'action_beneficiaires_projet.beneficiaire_id')
                        ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'action_beneficiaires_projet.CodeProjet')
                        ->whereIn('projet_eha2.CodeProjet', $projectIds)
                        ->where('action_beneficiaires_projet.type_beneficiaire', 'departement')
                        ->groupBy('departement.libelle')
                        ->get();

                        // Requête pour obtenir les totaux globaux pour les projets
                        $departments_totalFinan = DB::table('action_beneficiaires_projet')
                            ->select(
                            DB::raw('SUM(projet_eha2.cout_projet) AS total_project_cost_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Hygiène_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités_total')
                           )
                           ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'action_beneficiaires_projet.CodeProjet')
                           ->whereIn('projet_eha2.CodeProjet', $projectIds)
                           ->where('action_beneficiaires_projet.type_beneficiaire', 'departement')
                           ->get();

                    /////////FIN DEPARTEMENT COUT /////////


                }

            }else{
                    // Filtrage des projets en fonction du type de date sélectionné
                    if ($dateType == 'prévisionnelles' && $startDate && $endDate) {
                        $query->where('Date_demarrage_prevue', '>=', $startDate)
                            ->where('date_fin_prevue', '<=', $endDate);
                    } elseif ($dateType == 'effectives' && $startDate && $endDate) {
                        $query->whereHas('dateDebutEffective', function ($q) use ($startDate) {
                                    $q->where('date', '>=', $startDate);
                                })
                                ->whereHas('dateFinEffective', function ($q) use ($endDate) {
                                    $q->where('date', '<=', $endDate);
                                });
                    }

                    // Filtrage par statut
                    if ($status) {
                        $query->whereHas('projetStatutProjet', function ($q) use ($status) {
                            $q->where('code_statut_projet', $status);
                        });
                    }

                    // Filtrage par bailleur
                    if ($bailleur) {
                        $query->whereHas('bailleursProjets', function ($q) use ($bailleur) {
                            $q->where('code_bailleur', $bailleur);
                        });
                    }
                    // Récupération des projets filtrés
                    $projects = $query->get();

                    // Récupérer les IDs des projets filtrés
                    $projectIds = $projects->pluck('CodeProjet');

                    ////////////DISTRICT NOMBRE///////////
                        // Calcul des statistiques des districts basées sur les projets filtrés
                        $districts = DB::table('district')
                            ->select(
                                'district.libelle as district_name',
                                DB::raw('COUNT(projet_eha2.CodeProjet) as total_projects'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')
                            )
                            ->leftJoin('region', 'region.code_district', '=', 'district.code')
                            ->leftJoin('projet_eha2', 'region.code', '=', 'projet_eha2.code_region')
                            ->whereIn('projet_eha2.CodeProjet', $projectIds)
                            ->groupBy('district.libelle')
                            ->get();

                        $districts_total = DB::table('projet_eha2')
                            ->select(
                                DB::raw('COUNT(projet_eha2.CodeProjet) AS total_projects_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités_total')
                            )
                            ->whereIn('CodeProjet', $projectIds)
                            ->get();
                    ////////////FIN DISTRICT NOMBRE///////////

                    ////////////REGION NOMBRE//////////
                        // Calcul des statistiques des regions basées sur les projets filtrés
                        $regions_nbr = DB::table('region')
                            ->select(
                                'district.libelle as district_name',
                                'region.libelle as region_name',
                                DB::raw('COUNT(projet_eha2.CodeProjet) as total_projects'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')

                            )
                            ->leftJoin('district', 'region.code_district', '=', 'district.code')
                            ->leftJoin('projet_eha2', 'region.code', '=', 'projet_eha2.code_region')
                            ->whereIn('projet_eha2.CodeProjet', $projectIds)
                            ->groupBy('district.libelle', 'region.libelle')
                            ->get();

                    //////////FIN REGION NOMBRE/////////

                    ///////////DISTRICT COUT//////////
                        $montantDistrict_cout = DB::table('district')
                        ->select(
                            'district.libelle as district_name',
                            DB::raw('COUNT(projet_eha2.cout_projet) as total_projects'),
                            DB::raw('SUM(projet_eha2.cout_projet) as coutProjet'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')
                        )
                        ->leftJoin('region', 'region.code_district', '=', 'district.code')
                        ->leftJoin('projet_eha2', 'region.code', '=', 'projet_eha2.code_region')
                        ->whereIn('projet_eha2.CodeProjet', $projectIds)
                        ->groupBy('district.libelle')
                        ->get();

                        $districts_total_cout = DB::table('projet_eha2')
                            ->selectRaw('COUNT(*) AS total_projects_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 1 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 2 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 3 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS Hygiène_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 4 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 5 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 6 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement_total')
                            ->selectRaw('CAST(SUM(CASE WHEN code_domaine = 7 THEN cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités_total')
                            ->whereIn('projet_eha2.CodeProjet', $projectIds)
                            ->first();
                    //////////FIN DISTRICT COUT////////

                    /////////REGION COUT///////////
                        $montantRegion_cout_mon = DB::table('projet_eha2')
                        ->join('region', 'region.code', '=', 'projet_eha2.code_region')
                        ->join('district', 'district.code', '=', 'region.code_district')
                        ->select(
                            DB::raw('SUM(projet_eha2.cout_projet) AS coutProjet'),
                            'district.libelle AS nomDistrict',
                            'region.libelle AS nomRegion',
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS Alimentation_en_Eau_Potable'),
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS Assainissement_et_Drainage'),
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS Hygiène'),
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS Ressources_en_Eau'),
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN projet_eha2.cout_projet ELSE 0 END) AS EHA_dans_les_Etablissements_de_Santé'),
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN projet_eha2.cout_projet ELSE 0 END) AS EHA_dans_les_Etablissements_d_Enseignement'),
                            DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN projet_eha2.cout_projet ELSE 0 END) AS EHA_dans_les_autres_Entités')

                        )
                        ->whereIn('projet_eha2.CodeProjet', $projectIds)
                        ->groupBy('district.libelle', 'region.libelle')
                        ->get();

                    ////////REGION COUT/////////

                    ///////// DEPARTEMENT NOMBRE /////////
                        // Requête pour obtenir les données des départements et des projets associés
                        $departments = DB::table('action_beneficiaires_projet')
                            ->select(
                                'departement.libelle as department_name',
                                'district.libelle as districts_name',
                                'region.libelle as regions_name',
                                DB::raw('COUNT(projet_eha2.CodeProjet) as total_projects'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')
                            )
                            ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'action_beneficiaires_projet.CodeProjet')
                            ->join('departement', 'departement.code', '=', 'action_beneficiaires_projet.beneficiaire_id')
                            ->join('region', 'region.code', '=', 'departement.code_region')
                            ->join('district', 'district.code', '=', 'region.code_district')
                            ->where('action_beneficiaires_projet.type_beneficiaire', 'departement')
                            ->whereIn('projet_eha2.CodeProjet', $projectIds)
                            ->groupBy('departement.libelle', 'district.libelle', 'region.libelle')
                            ->get();

                        // Requête pour obtenir les totaux globaux pour les projets
                        $departments_total = DB::table('action_beneficiaires_projet')
                            ->select(
                                DB::raw('COUNT(projet_eha2.CodeProjet) AS total_projects_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement_total'),
                                DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN 1 ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités_total')
                            )
                            ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'action_beneficiaires_projet.CodeProjet')
                            ->join('region', 'region.code', '=', 'projet_eha2.code_region')
                            ->join('district', 'district.code', '=', 'region.code_district')
                            ->whereIn('projet_eha2.CodeProjet', $projectIds)
                            ->where('action_beneficiaires_projet.type_beneficiaire', 'departement')
                            ->get();

                    ///////// FIN DEPARTEMENT NOMBRE /////////

                    /////////DEPARTEMENT COUT /////////
                        $departmentsFinan = DB::table('action_beneficiaires_projet')
                        ->select(
                            'departement.libelle as department_name',
                            DB::raw('SUM(projet_eha2.cout_projet) as total_project_cost'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités')
                        )
                        ->join('departement', 'departement.code', '=', 'action_beneficiaires_projet.beneficiaire_id')
                        ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'action_beneficiaires_projet.CodeProjet')
                        ->whereIn('projet_eha2.CodeProjet', $projectIds)
                        ->where('action_beneficiaires_projet.type_beneficiaire', 'departement')
                        ->groupBy('departement.libelle')
                        ->get();

                        // Requête pour obtenir les totaux globaux pour les projets
                        $departments_totalFinan = DB::table('action_beneficiaires_projet')
                            ->select(
                            DB::raw('SUM(projet_eha2.cout_projet) AS total_project_cost_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Hygiène_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_de_Santé_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 6 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_Etablissements_d_Enseignement_total'),
                            DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 7 THEN projet_eha2.cout_projet ELSE 0 END) AS UNSIGNED) AS EHA_dans_les_autres_Entités_total')
                        )
                        ->join('projet_eha2', 'projet_eha2.CodeProjet', '=', 'action_beneficiaires_projet.CodeProjet')
                        ->whereIn('projet_eha2.CodeProjet', $projectIds)
                        ->where('action_beneficiaires_projet.type_beneficiaire', 'departement')
                        ->get();

                    /////////FIN DEPARTEMENT COUT /////////
            }

            /////////////////////DISTRICT NOMBRE//////////////////
                if ($districts->isEmpty() || $districts_total->isEmpty()) {
                    return response()->json(['message' => 'Aucune donnée à ajouter.']);
                }

                $filePath = Storage::disk('geojson')->path('District.geojson.js');
                $geojsonContent = file_get_contents($filePath);
                $geojsonData = $geojsonContent ? json_decode($geojsonContent, true) : null;

                if ($geojsonData === null) {
                    $geojsonData = [
                        'type' => 'FeatureCollection',
                        'features' => [],
                    ];
                }

                // Initialiser les valeurs totales une seule fois
                $district_total = isset($districts_total[0]) ? $districts_total[0] : null;

                foreach ($districts as $district) {
                    $newFeature = [
                        'type' => 'Feature',
                        'properties' => [
                            'NAME_1' => $district->district_name,
                            'PROJET_NUM' => (int) $district->total_projects,
                            'AEP' => (int) $district->Alimentation_en_Eau_Potable,
                            'AD' => (int) $district->Assainissement_et_Drainage,
                            'HY' => (int) $district->Hygiène,
                            'REE' => (int) $district->Ressources_en_Eau,
                            'EHAES' => (int) $district->EHA_dans_les_Etablissements_de_Santé,
                            'EHAEE' => (int) $district->EHA_dans_les_Etablissements_d_Enseignement,
                            'EHAEEn' => (int) $district->EHA_dans_les_autres_Entités,
                            // Totaux
                            'PROJET_NUM_T' => $district_total ? (int) $district_total->total_projects_total : 0,
                            'AEP_T' => $district_total ? (int) $district_total->Alimentation_en_Eau_Potable_total : 0,
                            'AD_T' => $district_total ? (int) $district_total->Assainissement_et_Drainage_total : 0,
                            'HY_T' => $district_total ? (int) $district_total->Hygiène_total : 0,
                            'REE_T' => $district_total ? (int) $district_total->Ressources_en_Eau_total : 0,
                            'EHAES_T' => $district_total ? (int) $district_total->EHA_dans_les_Etablissements_de_Santé_total : 0,
                            'EHAEE_T' => $district_total ? (int) $district_total->EHA_dans_les_Etablissements_d_Enseignement_total : 0,
                            'EHAEEn_T' => $district_total ? (int) $district_total->EHA_dans_les_autres_Entités_total : 0,
                        ],
                        'geometry' => [
                            'type' => 'MultiPolygon',  // Remplissez les coordonnées ici
                        ],
                    ];

                    $geojsonData['features'][] = $newFeature;
                }

                file_put_contents($this->filePaths['district_temp'], 'var statesDataDistrictsBD = ' . json_encode($geojsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');
            /////////////////////FIN DISTRICT NOMBRE//////////////

            //////////////REGION NOMBRE////////////
               // Vérifier s'il y a des données à ajouter
                if ($regions_nbr->isEmpty()) {
                    return 'Aucune donnée à ajouter.';
                }

                // Charger le contenu du fichier GeoJSON existant
                $filePath_region_nbr = Storage::disk('geojson')->path('Region.geojson.js');
                $geojsonContent_region_nbr = file_get_contents($filePath_region_nbr);

                // Vérifier si $geojsonData est null et initialiser si nécessaire
                $geojsonData_region_nbr = $geojsonContent_region_nbr ? json_decode($geojsonContent_region_nbr, true) : null;

                if ($geojsonData_region_nbr === null) {
                    // Initialiser $geojsonData si c'est null
                    $geojsonData_region_nbr = [
                        'type' => 'FeatureCollection',
                        'features' => [],
                    ];
                }

                // Ajouter de nouvelles données à la propriété features
                foreach ($regions_nbr as $region) {
                    $newFeature = [
                        'type' => 'Feature',
                        'properties' => [
                            'NAME_1' => $region->district_name,
                            'NAME_2' => $region->region_name,
                            'PROJET_NUM' => (int) $region->total_projects,
                            'AEP' => (int) $region->Alimentation_en_Eau_Potable,
                            'AD' => (int) $region->Assainissement_et_Drainage,
                            'HY' => (int) $region->Hygiène,
                            'REE' => (int) $region->Ressources_en_Eau,
                            'EHAES' => (int) $region->EHA_dans_les_Etablissements_de_Santé,
                            'EHAEE' => (int) $region->EHA_dans_les_Etablissements_d_Enseignement,
                            'EHAEEn' => (int) $region->EHA_dans_les_autres_Entités,
                        ],
                        'geometry' => [
                            'type' => 'MultiPolygon',  // Vous devrez remplir les coordonnées ici
                        ],
                    ];

                    $geojsonData_region_nbr['features'][] = $newFeature;
                }

                // Sauvegarder le fichier GeoJSON mis à jour
                file_put_contents($this->filePaths['region_temp'], 'var statesDataRegionsBD = ' . json_encode($geojsonData_region_nbr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');

            //////////////FIN REGION NOMBRE//////////


            //////////////////DISTRICT COUT///////////////////
                // Vérifier s'il y a des données à ajouter
                if (empty($montantDistrict_cout) || empty($districts_total_cout)) {

                    return 'Aucune donnée à ajouter.';
                }

                // Charger le contenu du fichier GeoJSON existant
                $filePath_cout = Storage::disk('geojson')->path('Cout.geojson.js');
                $geojsonContent_cout = file_get_contents($filePath_cout);

                // Vérifier si $geojsonData est null et initialiser si nécessaire
                $geojsonData_cout = $geojsonContent_cout ? json_decode($geojsonContent_cout, true) : null;

                if ($geojsonData_cout === null) {
                    // Initialiser $geojsonData si c'est null
                    $geojsonData_cout = [
                        'type' => 'FeatureCollection',
                        'features' => [],
                    ];
                }

                // Initialiser les valeurs totales une seule fois
                $district_total_cout = $districts_total_cout ? $districts_total_cout : null;


                foreach ($montantDistrict_cout as $district) {
                    $newFeature = [
                        'type' => 'Feature',
                        'properties' => [
                            'NAME_1' => $district->district_name,
                            'MontantTotal' => $district->total_projects,
                            'CoutProjet' => (int) $district->coutProjet,
                            'AEP' => (int) $district->Alimentation_en_Eau_Potable,
                            'AD' => (int) $district->Assainissement_et_Drainage,
                            'HY' => (int) $district->Hygiène,
                            'REE' => (int) $district->Ressources_en_Eau,
                            'EHAES' => (int) $district->EHA_dans_les_Etablissements_de_Santé,
                            'EHAEE' => (int) $district->EHA_dans_les_Etablissements_d_Enseignement,
                            'EHAEEn' => (int) $district->EHA_dans_les_autres_Entités,
                            // Totaux
                            'MontantTotal_T' => $district_total_cout ? (int) $district_total_cout->total_projects_total : 0,
                            'AEP_T' => $district_total_cout ? (int) $district_total_cout->Alimentation_en_Eau_Potable_total : 0,
                            'AD_T' => $district_total_cout ? (int) $district_total_cout->Assainissement_et_Drainage_total : 0,
                            'HY_T' => $district_total_cout ? (int) $district_total_cout->Hygiène_total : 0,
                            'REE_T' => $district_total_cout ? (int) $district_total_cout->Ressources_en_Eau_total : 0,
                            'EHAES_T' => $district_total_cout ? (int) $district_total_cout->EHA_dans_les_Etablissements_de_Santé_total : 0,
                            'EHAEE_T' => $district_total_cout ? (int) $district_total_cout->EHA_dans_les_Etablissements_d_Enseignement_total : 0,
                            'EHAEEn_T' => $district_total_cout ? (int) $district_total_cout->EHA_dans_les_autres_Entités_total : 0,
                        ],
                        'geometry' => [
                            'type' => 'MultiPolygon',  // Remplissez les coordonnées ici
                        ],
                    ];

                    $geojsonData_cout['features'][] = $newFeature;
                }

                // Sauvegarder le fichier GeoJSON mis à jour
                file_put_contents($this->filePaths['cout_district_temp'], 'var montantBD = ' . json_encode($geojsonData_cout, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');


            /////////////////FIN DISTRICT COUT//////////////

            ////////////////REGION COUT//////////////
                // Vérifier s'il y a des données à ajouter
                if ($montantRegion_cout_mon->isEmpty()) {
                    return 'Aucune donnée à ajouter.';
                }

                // Charger le contenu du fichier GeoJSON existant
                $filePath_cout_mon = Storage::disk('geojson')->path('CoutRegion.geojson.js');
                $geojsonContent_cout_mon = file_get_contents($filePath_cout_mon);

                // Vérifier si $geojsonData est null et initialiser si nécessaire
                $geojsonData_cout_mon = $geojsonContent_cout_mon ? json_decode($geojsonContent_cout_mon, true) : null;

                if ($geojsonData_cout_mon === null) {
                    // Initialiser $geojsonData si c'est null
                    $geojsonData_cout_mon = [
                        'type' => 'FeatureCollection',
                        'features' => [],
                    ];
                }

                // Ajouter de nouvelles données à la propriété features
                foreach ($montantRegion_cout_mon as $montantR) {
                    $newFeature = [
                        'type' => 'Feature',
                        'properties' => [
                            'NAME_1' => $montantR->nomDistrict,
                            'NAME_2' => $montantR->nomRegion,
                            'MontantTotal' => $montantR->coutProjet,
                            'AEP' => $montantR->Alimentation_en_Eau_Potable,
                            'AD' =>  $montantR->Assainissement_et_Drainage,
                            'HY' => $montantR->Hygiène,
                            'REE' => $montantR->Ressources_en_Eau,
                            'EHAES' => $montantR->EHA_dans_les_Etablissements_de_Santé,
                            'EHAEE' => $montantR->EHA_dans_les_Etablissements_d_Enseignement,
                            'EHAEEn' => $montantR->EHA_dans_les_autres_Entités,
                        ],
                    ];

                    $geojsonData_cout_mon['features'][] = $newFeature;
                }

                // Sauvegarder le fichier GeoJSON mis à jour
                file_put_contents($this->filePaths['cout_region_temp'], 'var montantRegion = ' . json_encode($geojsonData_cout_mon, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');

            ///////////////FIN REGION COUT///////////

            ///////////////DEPARTEMENT NOMBRE///////////
                if ($departments->isEmpty() || $departments_total->isEmpty()) {
                    return 'Aucune donnée à ajouter.';
                }

                // Charger le contenu GeoJSON existant
                $filePath = Storage::disk('geojson')->path('Department.geojson.js');
                $geojsonContent = file_get_contents($filePath);
                $geojsonData_depart = $geojsonContent ? json_decode($geojsonContent, true) : null;

                if ($geojsonData_depart === null) {
                    $geojsonData_depart = [
                        'type' => 'FeatureCollection',
                        'features' => [],
                    ];
                }

                // Initialiser les valeurs totales une seule fois
                $department_total = isset($departments_total[0]) ? $departments_total[0] : null;

                foreach ($departments as $department) {
                    $newFeature = [
                        'type' => 'Feature',
                        'properties' => [
                            'NAME_1' => $department->districts_name,
                            'NAME_2' => $department->regions_name,
                            'NAME_3' => $department->department_name,
                            'PROJET_NUM' => (int) $department->total_projects,
                            'AEP' => (int) $department->Alimentation_en_Eau_Potable,
                            'AD' => (int) $department->Assainissement_et_Drainage,
                            'HY' => (int) $department->Hygiène,
                            'REE' => (int) $department->Ressources_en_Eau,
                            'EHAES' => (int) $department->EHA_dans_les_Etablissements_de_Santé,
                            'EHAEE' => (int) $department->EHA_dans_les_Etablissements_d_Enseignement,
                            'EHAEEn' => (int) $department->EHA_dans_les_autres_Entités,
                            // Totaux
                            'PROJET_NUM_T' => $department_total ? (int) $department_total->total_projects_total : 0,
                            'AEP_T' => $department_total ? (int) $department_total->Alimentation_en_Eau_Potable_total : 0,
                            'AD_T' => $department_total ? (int) $department_total->Assainissement_et_Drainage_total : 0,
                            'HY_T' => $department_total ? (int) $department_total->Hygiène_total : 0,
                            'REE_T' => $department_total ? (int) $department_total->Ressources_en_Eau_total : 0,
                            'EHAES_T' => $department_total ? (int) $department_total->EHA_dans_les_Etablissements_de_Santé_total : 0,
                            'EHAEE_T' => $department_total ? (int) $department_total->EHA_dans_les_Etablissements_d_Enseignement_total : 0,
                            'EHAEEn_T' => $department_total ? (int) $department_total->EHA_dans_les_autres_Entités_total : 0,
                        ],
                        'geometry' => [
                            'type' => 'MultiPolygon',  // Remplissez les coordonnées ici
                        ],
                    ];

                    $geojsonData_depart['features'][] = $newFeature;
                }

                // Écrire les données GeoJSON dans le fichier
                file_put_contents($this->filePaths['department'], 'var statesDataDepartmentsBD = ' . json_encode($geojsonData_depart, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');

            ///////////////FIN DEPARTEMENT NOMBRE///////////

            ///////////////DEPARTEMENT COUT///////////

                if ($departmentsFinan->isEmpty() || $departments_totalFinan->isEmpty()) {
                    return 'Aucune donnée à ajouter.';
                }

                // Charger le contenu GeoJSON existant
                $filePath = Storage::disk('geojson')->path('CoutDepartment.geojson.js');
                $geojsonContent = file_get_contents($filePath);
                $geojsonData_departFinan = $geojsonContent ? json_decode($geojsonContent, true) : null;

                if ($geojsonData_departFinan === null) {
                    $geojsonData_departFinan = [
                        'type' => 'FeatureCollection',
                        'features' => [],
                    ];
                }

                // Initialiser les valeurs totales une seule fois
                $department_totalFinan = isset($departments_totalFinan[0]) ? $departments_totalFinan[0] : null;

                foreach ($departmentsFinan as $departmentFinan) {
                    $newFeature = [
                        'type' => 'Feature',
                        'properties' => [
                            'NAME_3' => $departmentFinan->department_name,
                            'PROJET_NUM' => (int) $departmentFinan->total_projects,
                            'AEP' => (int) $departmentFinan->Alimentation_en_Eau_Potable,
                            'AD' => (int) $departmentFinan->Assainissement_et_Drainage,
                            'HY' => (int) $departmentFinan->Hygiène,
                            'REE' => (int) $departmentFinan->Ressources_en_Eau,
                            'EHAES' => (int) $departmentFinan->EHA_dans_les_Etablissements_de_Santé,
                            'EHAEE' => (int) $departmentFinan->EHA_dans_les_Etablissements_d_Enseignement,
                            'EHAEEn' => (int) $departmentFinan->EHA_dans_les_autres_Entités,
                            // Totaux
                            'PROJET_NUM_T' => $department_totalFinan ? (int) $department_totalFinan->total_projects_total : 0,
                            'AEP_T' => $department_totalFinan ? (int) $department_totalFinan->Alimentation_en_Eau_Potable_total : 0,
                            'AD_T' => $department_totalFinan ? (int) $department_totalFinan->Assainissement_et_Drainage_total : 0,
                            'HY_T' => $department_totalFinan ? (int) $department_totalFinan->Hygiène_total : 0,
                            'REE_T' => $department_totalFinan ? (int) $department_totalFinan->Ressources_en_Eau_total : 0,
                            'EHAES_T' => $department_totalFinan ? (int) $department_totalFinan->EHA_dans_les_Etablissements_de_Santé_total : 0,
                            'EHAEE_T' => $department_totalFinan ? (int) $department_totalFinan->EHA_dans_les_Etablissements_d_Enseignement_total : 0,
                            'EHAEEn_T' => $department_totalFinan ? (int) $department_totalFinan->EHA_dans_les_autres_Entités_total : 0,
                        ],
                        'geometry' => [
                            'type' => 'MultiPolygon',  // Remplissez les coordonnées ici
                            ],
                        ];

                        $geojsonData_departFinan['features'][] = $newFeature;
                }

                // Écrire les données GeoJSON dans le fichier
                file_put_contents($this->filePaths['department_cout'], 'var statesDataDepartmentsCoutBD = ' . json_encode($geojsonData_departFinan, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');

            ///////////////DEPARTEMENT COUT///////////





            return response()->json([
                'geojsonData' => $geojsonData,
                'geojsonData_region_nbr' =>$geojsonData_region_nbr,
                'geojsonData_cout'=> $geojsonData_cout,
                'geojsonData_cout_mon' => $geojsonData_cout_mon,
                'geojsonData_depart'=>$geojsonData_depart,
                'geojsonData_departFinan'=>$geojsonData_departFinan,
                'filtersApplied'=>$filtersApplied
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Une erreur s\'est produite.' . $e->getMessage()]);
        }
    }
}
