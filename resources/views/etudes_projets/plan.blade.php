@extends('layouts.app')

<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
    <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
    <style>

        #gantt_here {
            width: 100%;
            height: 40vh;
        }
        .gantt-info{
            display: none !important;
            visibility: none !important;
        }

        .red_color { background: red; }
        .blue_color { background: blue; }
        .gray_color { background: gray; }
        .gantt_task_progress { background-color: rgba(33, 33, 33, 0.17); }
    </style>
@section('content')

@if (session('success'))
<script>
    $('#alertMessage').text("{{ session('success') }}");
    $('#alertModal').modal('show');
</script>
@endif
<section id="multiple-column-form">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Etudes projets</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Planification projet</li>
                        </ol>
                        <div class="row">
                            <script>
                                setInterval(function() {
                                    document.getElementById('date-now').textContent = getCurrentDate();
                                }, 1000);

                                function getCurrentDate() {
                                    var currentDate = new Date();
                                    return currentDate.toLocaleString();
                                }
                            </script>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Planifcation  de projet</h5>

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            </div>
            <div class="card-content">
                <div class="col-12">
                    <div class="row">
                        <div class="col-4">
                            <label for="projectSelect">Code Projets :</label>
                            <select id="projectSelect" class="form-select" name="projectSelect">
                                <option value="">-- Sélectionner un projet --</option>
                                @foreach ($projects as $project)
                                        <option value="{{ $project->CodeProjet }}">{{ $project->CodeProjet }}</option>
                                    @endforeach
                                </select>

                            <br>
                        </div>

                        <div class="col-2">
                            <div id="controls">
                            <label for="scale_select">Echelle :</label>
                                <select id="scale_select" class="form-control">
                                    <option value="day">Jour</option>
                                    <option value="week">Semaine</option>
                                    <option value="month">Mois</option>
                                    <option value="quarter">Trimestre</option>
                                    <option value="year">Année</option>
                                </select>
                            </div>
                        </div>
                    </div>


                    <!-- Conteneur Gantt -->
                    <div id="gantt_here" ></div>

                </div>
            </div>
        </div>
    </div>
</section>
    <script type="text/javascript">
        gantt.config.xml_date = "%Y-%m-%d %H:%i:%s"; // format pour charger les données
        gantt.config.date_format = "%d %F %Y"; // format d'affichage des dates dans les tâches
        // Configurer la locale en français
        gantt.i18n.setLocale({
            date: {
                month_full: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
                    "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
                month_short: ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil",
                    "Aoû", "Sep", "Oct", "Nov", "Déc"],
                day_full: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
                day_short: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"]
            },
            labels: {
                new_task: "Nouvelle tâche",
                icon_save: "Enregistrer",
                icon_cancel: "Annuler",
                icon_details: "Détails",
                icon_edit: "Modifier",
                icon_delete: "Supprimer",
                gantt_save_btn: "Enregistrer",
                gantt_cancel_btn: "Annuler",
                gantt_delete_btn: "Supprimer",
                confirm_closing: "Vos modifications seront perdues, êtes-vous sûr ?",
                confirm_deleting: "La tâche sera supprimée définitivement, êtes-vous sûr ?",
                section_description: "Description",
                section_time: "Période",
                section_type: "Type",

                /* Colonnes de la grille */
                column_wbs: "WBS",
                column_text: "Nom de la tâche",
                column_start_date: "Date de début",
                column_duration: "Durée",
                column_add: "",

                /* Confirmation pour les liens */
                link: "Lien",
                confirm_link_deleting: "sera supprimé",
                link_start: " (début)",
                link_end: " (fin)",

                type_task: "Tâche",
                type_project: "Projet",
                type_milestone: "Jalon",

                minutes: "Minutes",
                hours: "Heures",
                days: "Jours",
                weeks: "Semaines",
                months: "Mois",
                years: "Années",

                /* Popup de messages */
                message_ok: "OK",
                message_cancel: "Annuler",

                /* Contraintes */
                section_constraint: "Contrainte",
                constraint_type: "Type de contrainte",
                constraint_date: "Date de contrainte",
                asap: "Dès que possible",
                alap: "Aussi tard que possible",
                snet: "Ne pas commencer avant",
                snlt: "Ne pas commencer après",
                fnet: "Ne pas terminer avant",
                fnlt: "Ne pas terminer après",
                mso: "Doit commencer le",
                mfo: "Doit terminer le",

                /* Gestion des ressources */
                resources_filter_placeholder: "tapez pour filtrer",
                resources_filter_label: "cacher les vides"
            }
        });

        // Configuration de l'échelle de temps
        gantt.config.start_date = new Date(new Date().getFullYear() - 10, 0, 1);  // 25 ans dans le passé
        gantt.config.end_date = new Date(new Date().getFullYear() + 35, 11, 31);  // 25 ans dans le futur

        gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";
        gantt.init("gantt_here");

        // Charge les données de l'API
        gantt.load("/api/data");

        // Ajoute un écouteur d'événement sur l'ajout d'une nouvelle tâche


        // Configure le dataProcessor pour synchroniser les actions (CRUD)
        var dp = new gantt.dataProcessor("/api/");

        dp.init(gantt);

        // Ajoute CodeProjet à la tâche avant l'envoi des données
        dp.attachEvent("onBeforeUpdate", function(id, state, data) {
            // Récupère le CodeProjet sélectionné dans le menu déroulant
            var codeProjet = document.getElementById('projectSelect').value;

            // Ajoute le CodeProjet à l'objet de tâche
            data.CodeProjet = codeProjet;
            //console.log("Enregistrement avec CodeProjet:", data);

            // Renvoie true pour continuer le processus de mise à jour/enregistrement
            return true;
        });
        gantt.attachEvent("onTaskCreated", function(task) {
            // Récupère le CodeProjet sélectionné
            var codeProjet = document.getElementById('projectSelect').value;

            // Ajoute le CodeProjet à la tâche
            task.CodeProjet = codeProjet;

            // Ajoute les autres données de la tâche ici
            dp.sendData();  // Envoie les données au serveur
            return true;
        });


        gantt.attachEvent("onTaskCreated", function(link) {
            // Récupère le CodeProjet sélectionné
            var codeProjet = document.getElementById('projectSelect').value;

            // Ajoute le CodeProjet à la tâche
            link.CodeProjet = codeProjet;

            // Ajoute les autres données de la tâche ici
            dp.sendData();  // Envoie les données au serveur
            return true;
        });

        dp.setTransactionMode("REST");

        dp.attachEvent("onAfterUpdate", function(id, action, tid, response) {
            if (action === "inserted") {
                $('#alertMessage').text('Ajout éffectué.');
                $('#alertModal').modal('show');
                // console.log("Succès:", response);
            } else if (action === "updated") {
                $('#alertMessage').text('Modification effectué.');
                $('#alertModal').modal('show');
            } else if (action === "deleted") {
                $('#alertMessage').text('Suppression effectué.');
                $('#alertModal').modal('show');
            } else if (action === "error") {
                //console.log("Échec:", response);
                alert("Une erreur est survenue lors de l'opération : " + response); // Alerte pour les erreurs
            }
        });


        // Nouvelle méthode pour configurer les échelles
        function setScaleConfig(scale) {
            switch (scale) {
                case "day":
                    gantt.config.scales = [
                        {unit: "day", step: 1, format: "%d %M"},
                    ];
                    break;
                case "week":
                    gantt.config.scales = [
                        {unit: "week", step: 1, format: "Semaine #%W"},
                        {unit: "day", step: 1, format: "%d %M"}
                    ];
                    break;
                case "month":
                    gantt.config.scales = [
                        {unit: "month", step: 1, format: "%F %Y"},
                        {unit: "week", step: 1, format: "Semaine #%W"}
                    ];
                    break;
                case "quarter":
                    gantt.config.scales = [
                        {unit: "quarter", step: 1, format: function(date) {
                            var month = date.getMonth();
                            var q_num = Math.floor(month / 3) + 1;
                            return "T" + q_num;  // Trimestre
                        }},
                        {unit: "month", step: 1, format: "%M"}
                    ];
                    break;
                case "year":
                    gantt.config.scales = [
                        {unit: "year", step: 1, format: "%Y"},
                        {unit: "month", step: 1, format: "%M"}
                    ];
                    break;
            }
            gantt.render();  // Réinitialise l'échelle avec les nouvelles configurations
        }

        // Liste déroulante pour le changement d'échelle
        document.getElementById("scale_select").addEventListener("change", function() {
            var scale = this.value;
            setScaleConfig(scale);
        });

        // Définir l'échelle initiale
        setScaleConfig("day");


    </script>
    <script>
        document.getElementById("projectSelect").addEventListener("change", function() {
            var codeProjet = this.value;

            // Effacer les données précédentes
            gantt.clearAll();

            if (codeProjet) {
                // Charger les données associées au projet sélectionné
                gantt.load(`/api/data?CodeProjet=${codeProjet}`, "json")
                    .then(function() {
                        //console.log("Données chargées avec succès.");
                        //console.log(codeProjet)
                    })
                    .catch(function(error) {
                        //console.error("Erreur lors du chargement des données:", error);
                        alert("Erreur lors du chargement des données pour ce projet.");
                    });
            }
        });
        var projectCode = document.getElementById("projectSelect").value;



    </script>
@endsection
