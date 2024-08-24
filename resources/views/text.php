<?php
            public function filter(Request $request)
        {

            try {
                // Récupération des paramètres de la requête
                $domaine = $request->input('domaine');
                $district = $request->input('district');
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');
                $status = $request->input('status');
                $bailleur = $request->input('bailleur');
                $dateType = $request->input('date_type');

                // Initialisation de la requête Eloquent
                $query = ProjetEha2::query();

                // Vérification si aucun filtre n'est sélectionné
                $noFiltersSelected = empty($startDate) && empty($endDate) && empty($status) && empty($bailleur)  ;
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
                        if ($district) {
                            $query->whereHas('district', function ($q) use ($district) {
                                $q->where('libelle', $district);
                            });
                        }

                        if ($domaine) {
                            $query->where('code_domaine', $domaine);
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
                                'PROJET_NUM' => (int) $departmentFinan->total_project_cost,
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
                // Log the detailed error information
                Log::error('Exception Type: ' . get_class($e));
                Log::error('Error Message: ' . $e->getMessage());
                Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
                Log::error('Stack Trace: ' . $e->getTraceAsString());

                // Return a detailed error message in the response
                return response()->json([
                    'message' => 'Une erreur s\'est produite.',
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }

        }























































































        // Initialisation de la carte Leaflet
var mapFina;

function initMapJS() {
    if(mapFina){
        mapFina.remove();
    }
    map = L.map('map', {
        zoomControl: false,
        center: [-6.5, 7],
        maxZoom: 6.95,
        minZoom: 6.95,
        dragging: false,
        prefix: null
    }).setView([7.54, -5.55], 7);
    map.panBy([120, 0]);



            // Combiner les données pour les régions
            combineGeoJsonData(statesDataRegions, statesDataRegionsBD);
            // Ajout d'une couche GeoJSON pour les régions
            var statesDataRegionsGeoJs = L.geoJson(statesDataRegions, {
                style: styleRegion,
                onEachFeature: function (feature, layer) {
                    layer.on({
                        mouseover: highlightRegion,
                        mouseout: resetRegionHighlight,
                        click: zoomToRegion,
                        contextmenu: function (e) {
                            var codeRegion = feature.properties.Code_NAME_2; // Assurez-vous que cette propriété est correcte
                            if (typeof codeRegion === 'undefined') {
                                console.error('Code_NAME_2 is undefined for feature:', feature);
                            } else {
                                L.popup()
                                    .setLatLng(e.latlng)
                                    .setContent(createContextMenu(contextMenuItems, codeRegion)) // Utilisation de Code_NAME_2
                                    .openOn(map);
                            }
                        }
                    });
                }
            }).addTo(map);



            // Ajout d'une couche GeoJSON pour les départements en fonction des régions
            var statesDataDepartementsGeoJs = L.geoJson(statesDataDepartements, {
                style: styleDepartement,
                onEachFeature: function (feature, layer) {
                    layer.on({
                        mouseover: highlightDepartement,
                        mouseout: resetDepartementHighlight,
                        click: zoomToDepartement,
                        //contextmenu: afficherDepartmentData
                    });
                }
            }).addTo(map);
            // Zoom sur un département lorsqu'il est cliqué
            function zoomToDepartement(e) {

            }

            // Fonction pour combiner les propriétés de statesDataDistrictsBD dans statesDataDistricts
            function combineGeoJsonData(mainData, additionalData) {
                // Créer un dictionnaire des propriétés additionnelles en utilisant NAME_1 comme clé
                var additionalDataDict = {};
                additionalData.features.forEach(function(feature) {
                    additionalDataDict[feature.properties.NAME_1] = feature.properties;
                });

                // Ajouter les propriétés additionnelles aux caractéristiques principales
                mainData.features.forEach(function(feature) {
                    var additionalProperties = additionalDataDict[feature.properties.NAME_1];
                    if (additionalProperties) {
                        feature.properties = { ...feature.properties, ...additionalProperties };
                    }
                });
            }
            // Actions au survol de la souris
            combineGeoJsonData(statesDataDistricts, statesDataDistrictsBD);

            var statesDataDistrictsGeoJs = L.geoJson(statesDataDistricts, {
                pointToLayer: function (feature, latlng) {
                    var radius = setSizeIcon(feature.properties.nb_prod);
                    return L.circleMarker(latlng, {
                        radius: radius,
                        color: '#fff',
                        fillOpacity: 1,
                        fillColor: getColorBystatesDataDistricts(feature.properties.NAME_1)
                    });
                },
                style: styleDist,
                onEachFeature: function (feature, layer) {
                    layer.on({
                        mouseover: highlightFeature,
                        mouseout: resetHighlight,
                        click: zoomToFeature,
                        contextmenu: function (e) {
                             var codeDistrict = feature.properties.Code_NAME_1;
                            if (typeof codeDistrict === 'undefined') {
                                console.error('Code_NAME_1 is undefined for feature:', feature);
                            } else {
                                L.popup()
                                    .setLatLng(e.latlng)
                                    .setContent(createContextMenu(contextMenuItems, codeDistrict)) // Utilisation de Code_NAME_1
                                    .openOn(map);
                            }
                        }
                    });
                }
            }).addTo(map);

            // Définition du menu contextuel




            function afficherRegionData(e) {
                var layer = e.target;
                var props = layer.feature.properties;
                updateTableWithRegionData(props);
            }
            // Fonction pour mettre en surbrillance une entité au survol
            function highlightFeature(e) {
                var layer = e.target;

                layer.setStyle({
                    weight: 3,
                    color: '#666',
                    dashArray: '',
                    fillOpacity: 0.7
                });

                layer.bringToFront();
                info.update(layer.feature.properties);

                // Assombrir les régions associées au district survolé
                var districtCode = e.target.feature.properties.NAME_1;
                darkenAssociatedRegions(districtCode);
            }

            // Fonction pour mettre en surbrillance un département au survol
            function highlightDepartement(e) {
                var layer = e.target;
                layer.setStyle({
                    weight: 3,
                    color: '#666',
                    dashArray: '',
                    fillOpacity: 0.7
                });
                if (!L.Browser.ie && !L.Browser.opera) {
                    layer.bringToFront();
                }
                info.update(layer.feature.properties);
            }

            function darkenAssociatedRegions(districtCode) {
                statesDataRegionsGeoJs.eachLayer(function (layer) {
                    if (layer.feature.properties.NAME_1 === districtCode) {
                        // Assombrir seulement les régions associées au district
                        layer.setStyle({
                            weight: 3,
                            color: '#666',
                            dashArray: '',
                            fillOpacity: 0.5 // Ajustez l'opacité selon vos préférences
                        });
                    }
                });
            }
            function resetAssociatedRegions() {
                statesDataRegionsGeoJs.eachLayer(function (layer) {
                    // Réinitialiser la couleur des régions associées
                    layer.setStyle({
                        weight: 2,
                        color: 'white',
                        fillOpacity: 0.7
                    });
                });
            }


            // Fonction pour réinitialiser la surbrillance après le survol
            function resetHighlight(e) {
                statesDataDistrictsGeoJs.resetStyle();
                info.update();
                resetAssociatedRegions();
            }


            // Gestionnaire d'événements au survol de la souris
            function evenement(feature, layer) {
                layer.on({
                    mouseover: function(e) {
                        highlightFeature(e);
                        addRegionHoverEvents(); // Ajout des événements de survol pour les régions
                    },
                    mouseout: function(e) {
                        resetHighlight(e);
                    },
                    click: zoomToFeature
                });
            }


            // Zoom sur un district lorsqu'il est cliqué
            function zoomToFeature(e) {
                // Réinitialisez la couche des départements
                resetDepartementsLayer();
                // Mettre à jour la couche des régions en fonction du district cliqué
                var selectedDistrictCode = e.target.feature.properties.NAME_1;
                updateRegionsLayer(selectedDistrictCode);
            }

            function updateDepartementsLayer(selectedRegionCode) {
                // Réinitialisez la couche des départements
                resetDepartementsLayer();

                // Filtrer les départements en fonction de la région sélectionnée
                var filteredDepartments = statesDataDepartements.features.filter(function (department) {
                    return department.properties.NAME_2 === selectedRegionCode;
                });

                // Mettre à jour la couche des départements
                statesDataDepartementsGeoJs.addData({
                    type: 'FeatureCollection',
                    features: filteredDepartments
                });
            }


            // Ajoutez une fonction pour réinitialiser la couche des départements
            function resetDepartementsLayer() {
                // Réinitialisez la couche des départements à sa configuration initiale
                statesDataDepartementsGeoJs.clearLayers();
            }
            // Fonction pour mettre à jour la couche des régions en fonction du district cliqué
            function updateRegionsLayer(selectedDistrictCode) {
                // Filtrer les régions en fonction du district sélectionné
                var filteredRegions = statesDataRegions.features.filter(function (region) {
                    return region.properties.NAME_1 === selectedDistrictCode;
                });

                // Mettre à jour la couche des régions
                statesDataRegionsGeoJs.clearLayers();

                // Ajouter seulement les régions du district sélectionné
                statesDataRegionsGeoJs.addData({
                    type: 'FeatureCollection',
                    features: filteredRegions
                });

                // Ajout des événements de survol pour les régions après la mise à jour
                addRegionHoverEvents();
            }

            // Ajout de titre et d'information sur la région survolée par la souris
            info = L.control();

            info.onAdd = function (map) {
                this._div = L.DomUtil.create('div', 'info');
                this.update();
                return this._div;
            };

            // Mise à jour de la fonction info.update
            function getDistrictInfo(districtName) {
                var district = statesDataDistrictsBD.features.find(function (feature) {
                    return feature.properties.NAME_1 === districtName;
                });

                return district ? {
                    AEP: district.properties.AEP || 0,
                    AD: district.properties.AD || 0,
                    HY: district.properties.HY || 0,
                    EHAES: district.properties.EHAEE || 0,
                    EHAEE: district.properties.EHAEE || 0,
                    EHAEEn: district.properties.EHAEEn || 0,
                    REE: district.properties.REE || 0,
                    RCPE: district.properties.RCPE || 0,
                    PROJET_NUM: district.properties.PROJET_NUM || 0,
                    // Ajout des valeurs totales
                    AEP_T: district.properties.AEP_T || 0,
                    AD_T: district.properties.AD_T || 0,
                    HY_T: district.properties.HY_T || 0,
                    EHAES_T: district.properties.EHAES_T || 0,
                    EHAEE_T: district.properties.EHAEE_T || 0,
                    EHAEEn_T: district.properties.EHAEEn_T || 0,
                    REE_T: district.properties.REE_T || 0,
                    RCPE_T: district.properties.RCPE_T || 0,
                    PROJET_NUM_T: district.properties.PROJET_NUM_T || 0
                } : {
                    AEP: 0,
                    AD: 0,
                    HY: 0,
                    EHAES: 0,
                    EHAEE: 0,
                    EHAEEn: 0,
                    REE: 0,
                    RCPE: 0,
                    PROJET_NUM: 0,
                    // Valeurs totales par défaut
                    AEP_T: 0,
                    AD_T: 0,
                    HY_T: 0,
                    EHAES_T: 0,
                    EHAEE_T: 0,
                    EHAEEn_T: 0,
                    REE_T: 0,
                    RCPE_T: 0,
                    PROJET_NUM_T: 0
                };

            }
            function getRegionInfo(regionCode) {
                var region = statesDataRegionsBD.features.find(function (feature) {
                    return feature.properties.NAME_2 === regionCode;
                });

                return region ? {
                    AEP: region.properties.AEP || 0,
                    AD: region.properties.AD || 0,
                    HY: region.properties.HY || 0,
                    EHAES: region.properties.EHAES || 0,
                    EHAEE: region.properties.EHAEE || 0,
                    EHAEEn: region.properties.EHAEEn || 0,
                    REE: region.properties.REE || 0,
                    RCPE: region.properties.RCPE || 0,
                    PROJET_NUM: region.properties.PROJET_NUM || 0
                } : {
                    AEP: 0,
                    AD: 0,
                    HY: 0,
                    EHAES: 0,
                    EHAEE: 0,
                    EHAEEn: 0,
                    REE: 0,
                    RCPE: 0,
                    PROJET_NUM: 0
                };
            }

            function getDepartmentInfo(departmentName) {
                var department = statesDataDepartmentsBD.features.find(function (feature) {
                    return feature.properties.NAME_3=== departmentName;
                });

                return department ? {
                    AEP: department.properties.AEP || 0,
                    AD: department.properties.AD || 0,
                    HY: department.properties.HY || 0,
                    EHAES: department.properties.EHAEE || 0,
                    EHAEE: department.properties.EHAEE || 0,
                    EHAEEn: department.properties.EHAEEn || 0,
                    REE: department.properties.REE || 0,
                    RCPE: department.properties.RCPE || 0,
                    PROJET_NUM: department.properties.PROJET_NUM || 0,
                    // Ajout des valeurs totales
                    AEP_T: department.properties.AEP_T || 0,
                    AD_T: department.properties.AD_T || 0,
                    HY_T: department.properties.HY_T || 0,
                    EHAES_T: department.properties.EHAES_T || 0,
                    EHAEE_T: department.properties.EHAEE_T || 0,
                    EHAEEn_T: department.properties.EHAEEn_T || 0,
                    REE_T: department.properties.REE_T || 0,
                    RCPE_T: department.properties.RCPE_T || 0,
                    PROJET_NUM_T: department.properties.PROJET_NUM_T || 0
                } : {
                    AEP: 0,
                    AD: 0,
                    HY: 0,
                    EHAES: 0,
                    EHAEE: 0,
                    EHAEEn: 0,
                    REE: 0,
                    RCPE: 0,
                    PROJET_NUM: 0,
                    // Valeurs totales par défaut
                    AEP_T: 0,
                    AD_T: 0,
                    HY_T: 0,
                    EHAES_T: 0,
                    EHAEE_T: 0,
                    EHAEEn_T: 0,
                    REE_T: 0,
                    RCPE_T: 0,
                    PROJET_NUM_T: 0
                };

            }

            // Fonction pour mettre à jour les données de la région
            function updateRegionInfo(regionCode) {
                // Utilisez la couche des régions pour obtenir les informations de la région
                var region = statesDataRegions.features.find(function (feature) {
                    return feature.properties.NAME_2 === regionCode;
                });

                // Mettez à jour les informations de la région dans le panneau d'information
                info.update(region.properties);
            }


            function createContextMenu(items, codeDistrict) {
                var container = L.DomUtil.create('div', 'context-menu');
                items.forEach(function(item) {
                    var link = L.DomUtil.create('a', '', container);
                    link.href = '#';
                    link.innerHTML = item.text;
                    link.onclick = function(e) {
                        e.preventDefault();
                        if (item.callback) {
                            item.callback(codeDistrict);
                        }
                    };
                });
                return container;
            }

            var contextMenuItems = [
                {
                    text: 'Alimentation en Eau Potable <br>',
                    codeDomaine: '01',
                    callback: function(codeDistrict) {
                        window.location.href = getContextMenuLink(codeDistrict, this.codeDomaine);
                    }
                },
                {
                    text: 'Assainissement et Drainage <br>',
                    codeDomaine: '02',
                    callback: function(codeDistrict) {
                        window.location.href = getContextMenuLink(codeDistrict, this.codeDomaine);
                    }
                },
                {
                    text: 'Hygiène <br>',
                    codeDomaine: '03',
                    callback: function(codeDistrict) {
                        window.location.href = getContextMenuLink(codeDistrict, this.codeDomaine);
                    }
                },
                {
                    text: 'Ressources en Eau <br>',
                    codeDomaine: '04',
                    callback: function(codeDistrict) {
                        window.location.href = getContextMenuLink(codeDistrict, this.codeDomaine);
                    }
                },
                {
                    text: 'EHA dans les Établissements de Santé <br>',
                    codeDomaine: '05',
                    callback: function(codeDistrict) {
                        window.location.href = getContextMenuLink(codeDistrict, this.codeDomaine);
                    }
                },
                {
                    text: 'EHA dans les Établissements d\'Enseignement <br>',
                    codeDomaine: '06',
                    callback: function(codeDistrict) {
                        window.location.href = getContextMenuLink(codeDistrict, this.codeDomaine);
                    }
                },
                {
                    text: 'EHA dans les autres Entités',
                    codeDomaine: '07',
                    callback: function(codeDistrict) {
                        window.location.href = getContextMenuLink(codeDistrict, this.codeDomaine);
                    }
                }
            ];


            // Fonction pour créer le lien du menu contextuel

info.update = function (props) {
    var districtInfo = getDistrictInfo(props ? props.NAME_1 : '');
    var regionInfo = getRegionInfo(props ? props.NAME_2:'');
    var departmentInfo = getDepartmentInfo(props ? props.NAME_3:'');
    var isRegion = props && props.NAME_2;
    var isDepaterment = props && props.NAME_3;

    currentLayerData = {
        district: props ? props.NAME_1 : '---',
        region: props ? props.NAME_2 : '---',
        department: props ? props.NAME_3 : '---',
        districtInfo: districtInfo,
        regionInfo: regionInfo,
        departmentInfo: departmentInfo,
        isRegion: props && props.NAME_2 !== undefined,
        isDepartment: props && props.NAME_3 !== undefined
    };


    var calculatePercentageR = function (value, total ) {
        return (total !== 0) ? ((value / total) * 100).toFixed(2) : '-';
    };
    function displayValue(value) {
        // Vérifie si la valeur est égale à 0 ou à 0.00
        if (value === 0 || value === 0.00) {
            return '-';
        } else {
            return value;
        }
    }
    // Condition pour déterminer si c'est une région ou un district


    this._div.innerHTML = `
    <table>
        <thead>
            <tr>
                <th style="text-align: left;"></th>
                <td></td>
            </tr>
            <tr>
                <th style="text-align: left;">District: </th>
                <td>${props ? props.NAME_1 : '---'}</td>
            </tr>
            <tr>
                <th style="text-align: left;">Region :</th>
                <td>${props ? props.NAME_2 : '---'}</td>
            </tr>
            <tr>
                <th style="text-align: left;">Département :</th>
                <td>${props ? props.NAME_3 : '---'}</td>
            </tr>
        </thead>
    </table>
<table style="border-collapse: collapse; width: 100%;">
    <thead>
    <tr>
            <th ></th>
            <th ></th>
            <th colspan="3" style="border: 1px solid black; text-align: center;">%</th>

        </tr>
        <tr>
            <th class="col" style=""></th>
            <th class="col" style="border: 1px solid black; font-size:12px; text-align: center; width:40px;">Nbr</th>
            <th class="col" style="border: 1px solid black; text-align: center; font-size:12px;  width:50px;">District</th>
            <th class="col" style="border: 1px solid black; text-align: center; font-size:12px; width:50px;">Région</th>
            <th class="col" style="border: 1px solid black; text-align: center; font-size:12px;  width:50px;">Départ</th>
        </tr>

    </thead>
    <tbody>
        ${
            isDepaterment
            ? `
                <tr>
                    <th class="row22" style="text-align: right;">Alimentation en eau potable:</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(departmentInfo.AEP || '-')}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.AEP, districtInfo.AEP_T) )}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.AEP, districtInfo.AEP))}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.AEP, regionInfo.AEP))}</th>
                </tr>

            `
            : isRegion
            ?`
            <tr>
                <th class="row22" style="text-align: right;">Alimentation en eau potable:</th>
                <th class="col" style="border: 1px solid black; text-align: center;">${regionInfo.AEP || '-'}</th>
                <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.AEP, districtInfo.AEP_T) || '-'}</th>
                <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.AEP, districtInfo.AEP) || '-'}</th>
                <th class="col" style="border: 1px solid black; text-align: center;">-</th>
            </tr>
            `
            : `
                <tr>
                    <th class="row22" style="text-align: right;">Alimentation en eau potable:</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${districtInfo.AEP || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.AEP, districtInfo.AEP_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
        }
        ${
            isDepaterment
            ? `
                <tr>
                    <th class="row22" style="text-align: right;">Assainissement et drainage :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(departmentInfo.AD || '-')}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.AD, districtInfo.AD_T) )}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.AD, districtInfo.AD))}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.AD, regionInfo.AD))}</th>
                </tr>

            `
            : isRegion
            ?`
                <tr>
                    <th class="row22" style="text-align: right;">Assainissement et drainage :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${regionInfo.AD || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.AD, districtInfo.AD_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.AD, districtInfo.AD) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
            : `
                <tr>
                    <th class="row22" style="text-align: right;">Assainissement et drainage :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${districtInfo.AD || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.AD, districtInfo.AD_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
        }
        ${
            isDepaterment
            ? `
                <tr>
                    <th class="row22" style="text-align: right;">Hygiène :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(departmentInfo.HY || '-')}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.HY, districtInfo.HY_T) )}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.HY, districtInfo.HY))}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.HY, regionInfo.HY))}</th>
                </tr>

            `
            : isRegion
            ? `
                <tr>
                    <th class="row22" style="text-align: right;">Hygiène :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${regionInfo.HY || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.HY, districtInfo.HY_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.HY, districtInfo.HY) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
            : `
                <tr>
                    <th class="row22" style="text-align: right;">Hygiène :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${districtInfo.HY || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.HY, districtInfo.HY_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
        }
        ${
            isDepaterment
            ? `
                <tr>
                    <th class="row22" style="text-align: right;">Ressource en eau :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(departmentInfo.REE || '-')}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.REE, districtInfo.REE_T) )}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.REE, districtInfo.REE))}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.REE, regionInfo.REE))}</th>
                </tr>

            `
            : isRegion
            ? `
                <tr>
                    <th class="row22" style="text-align: right;">Ressource en eau :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${regionInfo.REE || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.REE, districtInfo.REE_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.REE, districtInfo.REE) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
            : `
                <tr>
                    <th class="row22" style="text-align: right;">Ressource en eau :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${districtInfo.REE || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.REE, districtInfo.REE_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
        }

        ${
            isDepaterment
            ? `
                <tr>
                    <th class="row22" style="text-align: right;">EHA établissement de Santé :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(departmentInfo.EHAES || '-')}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAES, districtInfo.EHAES_T) )}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAES, districtInfo.EHAES))}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAES, regionInfo.EHAES))}</th>
                </tr>

            `
            : isRegion
            ? `
                <tr>
                    <th class="row22" style="text-align: right;">EHA établissement de Santé :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${regionInfo.EHAES || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.EHAES, districtInfo.EHAES_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.EHAES, districtInfo.EHAES) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
            : `
                <tr>
                    <th class="row22" style="text-align: right;">EHA établissement de Santé :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${districtInfo.EHAES || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.EHAES, districtInfo.EHAES_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
        }

        ${
            isDepaterment
            ? `
                <tr>
                    <th class="row22" style="text-align: right;">EHA établissemet d’Enseignement :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(departmentInfo.EHAEE || '-')}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAEE, districtInfo.EHAEE_T) )}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAEE, districtInfo.EHAEE))}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAEE, regionInfo.EHAEE))}</th>
                </tr>

            `
            : isRegion
            ? `
                <tr>
                    <th class="row22" style="text-align: right;">EHA établissement d’Enseignement :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${regionInfo.EHAEE || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.EHAEE, districtInfo.EHAEE_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.EHAEE, districtInfo.EHAEE) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
            : `
                <tr>
                    <th class="row22" style="text-align: right;">EHA établissemet d’Enseignement :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${districtInfo.EHAEE || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.EHAEE, districtInfo.EHAEE_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
        }

        ${
            isDepaterment
            ? `
                <tr>
                    <th class="row22" style="text-align: right;">EHA autres Entités :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(departmentInfo.EHAEEn || '-')}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAEEn, districtInfo.EHAEEn_T) )}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAEEn, districtInfo.EHAEEn))}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAEEn, regionInfo.EHAEEn))}</th>
                </tr>

            `
            : isRegion
            ? `
                <tr>
                    <th class="row22" style="text-align: right;">EHA autres Entités :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${regionInfo.EHAEEn || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.EHAEEn, districtInfo.EHAEEn_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.EHAEEn, districtInfo.EHAEEn) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
            : `
                <tr>
                    <th class="row22" style="text-align: right;">EHA autres Entités :</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${districtInfo.EHAEEn || '-'}  </th>
                    <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.EHAEEn, districtInfo.EHAEEn_T) || '-'}</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                </tr>
            `
        }


    </tbody>
</table>

`;

// Ajoutez du style CSS pour aligner les ":" à droite
this._div.querySelector('th').style.textAlign = 'left';
this._div.querySelector('td').style.textAlign = 'left';

};


info.addTo(map);


// Add context menu to the map
/*
map.on('contextmenu', event => {
    // position where user right clicked / long pressed
    let latlng = event.latlng;

    // Define your array of entities
    const entities = [
      'EHA autres Entités',
      'EHA établissement de Santé',
      'EHA établissement d’Enseignement',
      'Alimentation en eau potable',
      'Assainissement et drainage',
      'Hygiène',
      'Ressource en eau'
    ];

    // Remove any existing context menu
    let existingMenu = document.querySelector('.custom-context-menu');
    if (existingMenu) {
      existingMenu.remove();
    }

    // Create a list element to hold menu items and add custom class
    let items = document.createElement('ul');
    items.className = 'custom-context-menu';

    // Create a div to position the context menu
    let contextMenuDiv = document.createElement('div');
    contextMenuDiv.style.position = 'absolute';
    contextMenuDiv.style.top = event.originalEvent.clientY + 'px';
    contextMenuDiv.style.left = event.originalEvent.clientX + 'px';
    contextMenuDiv.appendChild(items);

    document.body.appendChild(contextMenuDiv);

    // Iterate over the entities array and create a menu item for each
    entities.forEach(entity => {
      let item = document.createElement('li');
      let link = document.createElement('a');
      link.textContent = entity;
      link.href = "#";
      link.addEventListener('click', e => {
        // Perform action based on entity clicked
        // Example action: add a marker
        this.scene.add(new Marker(latlng.lat, latlng.lng));
        contextMenuDiv.remove();
        e.preventDefault();
      }, false);
      item.appendChild(link);
      items.appendChild(item);
    });

    // Remove the context menu when clicking outside of it
    document.addEventListener('click', function handler(e) {
      if (!contextMenuDiv.contains(e.target)) {
        contextMenuDiv.remove();
        document.removeEventListener('click', handler);
      }
    });
  });
    */


            // Fonction de style pour les régions
            function styleRegion(feature) {
                return { // Couleur d'orange pour les régions
                    weight: 2,
                    opacity: 1,
                    color: 'white',

                    fillColor:'#87CEEB',
                    fillOpacity: 0.7 // Réduisez l'opacité pour atténuer la couleur
                };
            }

                // Fonction de style pour les départements
                function styleDepartement(feature) {
                    return {
                        weight: 2,
                        opacity: 1,
                        color: 'white',
                        fillColor: '#87CE01', // Couleur de remplissage pour les départements
                        fillOpacity: 0.7
                    };
                }

            // Fonction pour mettre en surbrillance une région au survol
            function highlightRegion(e) {
                var layer = e.target;
                layer.setStyle({
                    weight: 3,
                    color: '#666',
                    dashArray: '',
                    fillOpacity: 0.7
                });
                if (!L.Browser.ie && !L.Browser.opera) {
                    layer.bringToFront();
                }
                info.update(layer.feature.properties);
            }

            // Fonction pour réinitialiser la surbrillance de la région après le survol
            function resetRegionHighlight(e) {
                statesDataRegionsGeoJs.resetStyle();
                info.update();
            }

            // Fonction pour réinitialiser la surbrillance du département
            function resetDepartementHighlight(e) {
                statesDataDepartementsGeoJs.resetStyle();
                info.update();
            }
            // Gestionnaire d'événements au survol de la souris pour les régions
            function addRegionHoverEvents() {
                statesDataRegionsGeoJs.eachLayer(function (layer) {
                    layer.on({
                        mouseover: function (e) {
                            highlightRegion(e);
                            updateRegionInfo(e.target.feature.properties.NAME_2); // Mise à jour des informations de la région au survol
                        },
                        mouseout: function (e) {
                            resetRegionHighlight(e);
                        },
                        click: zoomToRegion
                    });
                });
            }

            // Zoom sur une région lorsqu'elle est cliquée
            function zoomToRegion(e) {
                // Mettre à jour la couche des départements en fonction de la région cliquée
                var selectedRegionCode = e.target.feature.properties.NAME_2;
                updateDepartementsLayer(selectedRegionCode);
            }


            // Fonction pour obtenir la couleur en fonction du nombre de projets dans le district
            function getColorByProjectCount(projectCount) {
                // Utilisez une échelle de couleurs en fonction du nombre de projets
                var colorScale = chroma.scale(['#ebebb9', '#c9c943', '#6495ed', '#af6eeb', '#32cd32', '#eaff00', '#ffba00', '#ff0000']).mode('lch').colors(8); // Utilise Chroma.js pour créer une échelle de couleurs
                var colorIndex;

                if (projectCount >= 88) {
                    colorIndex = 7; // 300 et plus rouge
                } else if (projectCount >= 75) {
                    colorIndex = 6; // 155 à 299 orange
                } else if (projectCount >= 62) {
                    colorIndex = 5; // 100 à 154 jaune
                } else if (projectCount >= 50) {
                    colorIndex = 4; // 60 à 99 vert
                } else if (projectCount >= 38) {
                    colorIndex = 3; // 40 à 59 violet
                } else if (projectCount >= 25) {
                    colorIndex = 2; // 20 à 39 bleu
                } else if (projectCount >= 12) {
                    colorIndex = 1; // 1 à 19 beige foncé
                } else {
                    colorIndex = 0; // 0 beige
                }

                return colorScale[colorIndex];
            }

            // Fonction de style pour les districts en utilisant le dégradé de couleurs
            function styleDist(feature) {
                var isHighlighted = feature.properties.highlighted; // Vérifiez si le district est en surbrillance

                if (isHighlighted) {
                    // Si le district est en surbrillance, retournez le style avec la couleur fixe
                    return {
                        fillColor: '#ff9900', // Couleur fixe pour les districts en surbrillance
                        weight: 3,
                        opacity: 1,
                        color: '#fff',
                        fillOpacity: 0.7
                    };
                } else {
                    // Sinon, retournez le style basé sur le nombre de projets
                    var projectCount = getProjectCount(feature.properties.NAME_1);
                    return {
                        fillColor: getColorByProjectCount(projectCount),
                        weight: 2,
                        opacity: 1,
                        color: 'white',
                        fillOpacity: 0.7
                    };
                }
            }


            // à supprimer
            function getProjectCount(districtName) {
                var district = statesDataDistrictsBD.features.find(function (feature) {
                    return feature.properties.NAME_1 === districtName;
                });

                return district ? district.properties.PROJET_NUM : 0; // Retourne 0 s'il n'y a pas de projet
            }

            // Ajout de la légende à la carte
            function addLegend() {
                var legend = L.control({ position: 'bottomright' });

                legend.onAdd = function (map) {
                    var div = L.DomUtil.create('div', 'legend');
                    var labels = [];

                    // Ajouter le titre de la légende
                    div.innerHTML += '<h4>LEGENDE</h4>';
                    div.innerHTML += '<p>Nombre de projet</p>';


                    // Ajouter les couleurs et les étiquettes
                    var colorScale = chroma.scale(['#ebebb9', '#c9c943', '#6495ed', '#af6eeb', '#32cd32', '#eaff00', '#ffba00', '#ff0000']).mode('lch').colors(8); // Utilise Chroma.js pour créer une échelle de couleurs
                    var projectRanges = ['0', '12', '25', '38', '50', '62', '75', '88'];

                    for (var i = 0; i < colorScale.length; i++) {
                        div.innerHTML +=
                            '<i style="background:' + colorScale[i] + '"></i> ' +
                            projectRanges[i] + (projectRanges[i + 1] ? '&ndash;' + projectRanges[i + 1] + '<br>' : '+');
                    }

                    return div;
                };

                legend.addTo(map);
            }

            addLegend();
        }
// Ajoutez une variable globale pour stocker le layer actuel
var currentLayer = 'Nombre';

// Fonction pour changer la couche en fonction de la sélection de l'utilisateur
function changeMapLayerJS(layerType) {
    // Mettez à jour la variable globale currentLayer
    currentLayer = layerType;

    // Supprimez toutes les couches existantes sauf la carte
    map.eachLayer(function (layerType) {
        if (layerType !== map) {
            map.removeLayer(layerType);
        }
    });

    // Ajouter la nouvelle couche GeoJSON
    switch (layerType) {
        case 'Finance':
            initMapFina();
            break;
        case 'Nombre':
            initMapJS();
            break;
        // Ajouter d'autres cas au besoin

        default:
            // Ajouter une couche par défaut si nécessaire
            break;
    }
}

