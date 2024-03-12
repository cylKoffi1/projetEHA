<?php

namespace App\Http\Controllers;

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
            'district' => public_path('leaflet/geojsonTemp/District.geojson.js'),
            'region' => public_path('leaflet/geojsonTemp/Region.geojson.js'),
            'cout_district' => public_path('leaflet/geojsonTemp/Cout.geojson.js'),
            'cout_region' => public_path('leaflet/geojsonTemp/CoutRegion.geojson.js'),
        ];

        // Create the directory when the controller is instantiated
        Storage::makeDirectory('public/leaflet/geojsonTemp');
    }

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
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS Renforcement_des_capacités_et_Planification_Etudes')
                )
                ->leftJoin('region', 'region.code_district', '=', 'district.code')
                ->leftJoin('projet_eha2', 'region.code', '=', 'projet_eha2.code_region')
                ->groupBy('district.libelle')
                ->get();

            if ($districts->isEmpty()) {
                return 'Aucune donnée à ajouter.';
            }

            $filePath = public_path('leaflet/geojsonTemp/District.geojson.js');
            $geojsonContent = file_get_contents($filePath);
            $geojsonData = $geojsonContent ? json_decode($geojsonContent, true) : null;

            if ($geojsonData === null) {
                $geojsonData = [
                    'type' => 'FeatureCollection',
                    'features' => [],
                ];
            }

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
                        'RCPE' => (int) $district->Renforcement_des_capacités_et_Planification_Etudes,
                    ],
                    'geometry' => [
                        'type' => 'MultiPolygon',  // Remplissez les coordonnées ici
                    ],
                ];

                $geojsonData['features'][] = $newFeature;
            }

            file_put_contents($this->filePaths['district'], 'var statesDataDistrictsBD = ' . json_encode($geojsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');

            return View::make('district');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return 'Une erreur s\'est produite.';
        }
    }

    public function addRegionToGeoJSON()
    {
        try {
            // Obtenir les données des projets par région
            $regions = DB::table('region')
                ->select(
                    'district.libelle as district_name',
                    'region.libelle as region_name',
                    DB::raw('COUNT(projet_eha2.CodeProjet) as total_projects'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN 1 ELSE 0 END) AS UNSIGNED) AS Alimentation_en_Eau_Potable'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN 1 ELSE 0 END) AS UNSIGNED) AS Assainissement_et_Drainage'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN 1 ELSE 0 END) AS UNSIGNED) AS Hygiène'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN 1 ELSE 0 END) AS UNSIGNED) AS Ressources_en_Eau'),
                    DB::raw('CAST(SUM(CASE WHEN projet_eha2.code_domaine = 5 THEN 1 ELSE 0 END) AS UNSIGNED) AS Renforcement_des_capacités_et_Planification_Etudes')
                )
                ->leftJoin('district', 'region.code_district', '=', 'district.code')
                ->leftJoin('projet_eha2', 'region.code', '=', 'projet_eha2.code_region')
                ->groupBy('district.libelle', 'region.libelle')
                ->get();

            // Vérifier s'il y a des données à ajouter
            if ($regions->isEmpty()) {
                return 'Aucune donnée à ajouter.';
            }

            // Charger le contenu du fichier GeoJSON existant
            $filePath = public_path('leaflet/geojsonTemp/Region.geojson.js');
            $geojsonContent = file_get_contents($filePath);

            // Vérifier si $geojsonData est null et initialiser si nécessaire
            $geojsonData = $geojsonContent ? json_decode($geojsonContent, true) : null;

            if ($geojsonData === null) {
                // Initialiser $geojsonData si c'est null
                $geojsonData = [
                    'type' => 'FeatureCollection',
                    'features' => [],
                ];
            }

            // Ajouter de nouvelles données à la propriété features
            foreach ($regions as $region) {
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
                        'RCPE' => (int) $region->Renforcement_des_capacités_et_Planification_Etudes,
                        // Ajoutez d'autres propriétés au besoin
                    ],
                    'geometry' => [
                        'type' => 'MultiPolygon',  // Vous devrez remplir les coordonnées ici
                    ],
                ];

                $geojsonData['features'][] = $newFeature;
            }

            // Sauvegarder le fichier GeoJSON mis à jour
            file_put_contents($this->filePaths['region'], 'var statesDataRegionsBD = ' . json_encode($geojsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');
            return View::make('region');
        } catch (\Exception $e) {
            // Imprimez l'erreur dans les logs
            Log::error($e->getMessage());
            return 'Une erreur s\'est produite.';
        }
    }

    public function addCoutProjetDistrictToGeoJSON()
    {
        try {
            // Obtenir les données des projets par région
            $montantDistrict = DB::table('projet_eha2')
                ->select(
                    DB::raw('SUM(projet_eha2.cout_projet) AS coutProjet'),
                    'district.libelle AS nomDistrict',
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS Alimentation_en_Eau_Potable'),
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS Assainissement_et_Drainage'),
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS Hygiène'),
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS Ressources_en_Eau')
                )
                ->join('region', 'region.code', '=', 'projet_eha2.code_region')
                ->join('district', 'district.code', '=', 'region.code_district')
                ->groupBy('district.libelle')
                ->get();

            // Vérifier s'il y a des données à ajouter
            if ($montantDistrict->isEmpty()) {
                return 'Aucune donnée à ajouter.';
            }

            // Charger le contenu du fichier GeoJSON existant
            $filePath = public_path('leaflet/geojsonTemp/Cout.geojson.js');
            $geojsonContent = file_get_contents($filePath);

            // Vérifier si $geojsonData est null et initialiser si nécessaire
            $geojsonData = $geojsonContent ? json_decode($geojsonContent, true) : null;

            if ($geojsonData === null) {
                // Initialiser $geojsonData si c'est null
                $geojsonData = [
                    'type' => 'FeatureCollection',
                    'features' => [],
                ];
            }

            // Ajouter de nouvelles données à la propriété features
            foreach ($montantDistrict as $montantD) {
                $newFeature = [
                    'type' => 'Feature',
                    'properties' => [
                        'NAME_1' => $montantD->nomDistrict,
                        'MontantTotal' => $montantD->coutProjet,
                        'AEP' => $montantD->Alimentation_en_Eau_Potable,
                        'AD' =>  $montantD->Assainissement_et_Drainage,
                        'HY' => $montantD->Hygiène,
                        'REE' => $montantD->Ressources_en_Eau,
                    ],
                ];

                $geojsonData['features'][] = $newFeature;
            }

            // Sauvegarder le fichier GeoJSON mis à jour
            file_put_contents($this->filePaths['cout_district'], 'var montantBD = ' . json_encode($geojsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');
            return View::make('cout_district');
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
            $montantRegion = DB::table('projet_eha2')
                ->join('region', 'region.code', '=', 'projet_eha2.code_region')
                ->join('district', 'district.code', '=', 'region.code_district')
                ->select(
                    DB::raw('SUM(projet_eha2.cout_projet) AS coutProjet'),
                    'district.libelle AS nomDistrict',
                    'region.libelle AS nomRegion',
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 1 THEN projet_eha2.cout_projet ELSE 0 END) AS Alimentation_en_Eau_Potable'),
                    DB::raw('SUM(CASE                     WHEN projet_eha2.code_domaine = 2 THEN projet_eha2.cout_projet ELSE 0 END) AS Assainissement_et_Drainage'),
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 3 THEN projet_eha2.cout_projet ELSE 0 END) AS Hygiène'),
                    DB::raw('SUM(CASE WHEN projet_eha2.code_domaine = 4 THEN projet_eha2.cout_projet ELSE 0 END) AS Ressources_en_Eau')
                )
                ->groupBy('district.libelle', 'region.libelle')
                ->get();

            // Vérifier s'il y a des données à ajouter
            if ($montantRegion->isEmpty()) {
                return 'Aucune donnée à ajouter.';
            }

            // Charger le contenu du fichier GeoJSON existant
            $filePath = public_path('leaflet/geojsonTemp/CoutRegion.geojson.js');
            $geojsonContent = file_get_contents($filePath);

            // Vérifier si $geojsonData est null et initialiser si nécessaire
            $geojsonData = $geojsonContent ? json_decode($geojsonContent, true) : null;

            if ($geojsonData === null) {
                // Initialiser $geojsonData si c'est null
                $geojsonData = [
                    'type' => 'FeatureCollection',
                    'features' => [],
                ];
            }

            // Ajouter de nouvelles données à la propriété features
            foreach ($montantRegion as $montantR) {
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
                    ],
                ];

                $geojsonData['features'][] = $newFeature;
            }

            // Sauvegarder le fichier GeoJSON mis à jour
            file_put_contents($this->filePaths['cout_region'], 'var montantRegion = ' . json_encode($geojsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');
            return View::make('cout_region');
        } catch (\Exception $e) {
            // Imprimez l'erreur dans les logs
            Log::error($e->getMessage());
            return 'Une erreur s\'est produite.';
        }
    }

    public function showSIG($category = null) {
        switch ($category) {
            case 'district':
                $this->addDistrictToGeoJSON();
                break;
            case 'region':
                $this->addRegionToGeoJSON();
                break;
            case 'cout_district':
                $this->addCoutProjetDistrictToGeoJSON();
                break;
            case 'cout_region':
                $this->addCoutProjetRegionToGeoJSON();
                break;
            default:
                // Si la catégorie n'est pas spécifiée ou n'est pas reconnue, affiche le SIG pour le district par défaut
                $this->addDistrictToGeoJSON();
                $this->addRegionToGeoJSON();
                $this->addCoutProjetDistrictToGeoJSON();
                $this->addCoutProjetRegionToGeoJSON();
                break;
        }

        return View::make('sig');
    }
}
