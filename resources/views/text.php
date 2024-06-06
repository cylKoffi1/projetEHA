<?php
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
        }



        /////////////////////DISTRICT NOMBRE//////////////////
            if ($districts->isEmpty() || $districts_total->isEmpty()) {
                return response()->json(['message' => 'Aucune donnée à ajouter.']);
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

            file_put_contents($filePath, 'var statesDataDistrictsBD = ' . json_encode($geojsonData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . ';');
        /////////////////////FIN DISTRICT NOMBRE//////////////

        ///////////////////REGION NOMBRE//////////////////////
            // Vérifier s'il y a des données à ajouter
            if ($regions_nbr->isEmpty()) {
                return 'Aucune donnée à ajouter.';
            }

            // Charger le contenu du fichier GeoJSON existant
            $filePath_region_nbr = public_path('leaflet/geojsonTemp/Region.geojson.js');
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
        ///////////////////FIN REGION NOMBRE//////////////////

        //////////////////DISTRICT COUT///////////////////
            // Vérifier s'il y a des données à ajouter
            if (empty($montantDistrict_cout) || empty($districts_total_cout)) {

                return 'Aucune donnée à ajouter.';
            }

            // Charger le contenu du fichier GeoJSON existant
            $filePath_cout = public_path('leaflet/geojsonTemp/Cout.geojson.js');
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


        /////////////////FIN DISTRICT COUT//////////////

        ////////////////REGION COUT//////////////
            // Vérifier s'il y a des données à ajouter
            if ($montantRegion_cout_mon->isEmpty()) {
                return 'Aucune donnée à ajouter.';
            }

            // Charger le contenu du fichier GeoJSON existant
            $filePath_cout_mon = public_path('leaflet/geojsonTemp/CoutRegion.geojson.js');
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

        ///////////////FIN REGION COUT///////////

            return response()->json([
                'geojsonData' => $geojsonData,
                'geojsonData_region_nbr' =>$geojsonData_region_nbr,
                'geojsonData_cout'=> $geojsonData_cout,
                'geojsonData_cout_mon' => $geojsonData_cout_mon
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Une erreur s\'est produite.']);
        }
    }
