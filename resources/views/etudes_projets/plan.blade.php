@extends('layouts.app')

<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
<script src="https://docs.dhtmlx.com/gantt/codebase/dhtmlxgantt.js?v=9.0.3"></script>
    <!-- DHTMLX Scheduler -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/scheduler/edge/dhtmlxscheduler.css">
<script src="https://cdn.dhtmlx.com/scheduler/edge/dhtmlxscheduler.js"></script>

<!-- jQuery (nécessaire pour Bootstrap) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>


        #gantt_here, #scheduler_here {
            width: 100%;
            height: 70vh;
            display: none; /* Masqué par défaut */
        }
        .gantt-info{
            display: none !important;
            visibility: none !important;

        }
        .active-view {
            display: block !important;
        }
        .gantt_grid_data .gantt_cell {
            border-right: 1px solid blue;
        }
        .gantt_grid_data .gantt_cell {
            border-right: 1px dashed gray;
        }
        .gantt_grid_data .gantt_cell {
            height: 40px;
        }
        #ganttInfoModal .modal-header {
            background-color: #007bff;
            color: white;
        }
         .modal-body ul li {
            color: black;
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
                        <div class="col-3">
                            <label for="projectSelect">Code Projet :</label>
                            <select id="projectSelect" class="form-select" name="projectSelect">
                                <option value="">-- Sélectionner un projet --</option>
                                @foreach ($projects as $project)
                                        <option value="{{ $project->CodeProjet }}">{{ $project->CodeProjet }}</option>
                                    @endforeach
                                </select>

                            <br>
                        </div>
                        <div class="col-2">
                            <div id="controls" style="display: block;">
                                <label for="scale_select">Période :</label>
                                <select id="scale_select" class="form-control">
                                    <option value="day">Jour</option>
                                    <option value="week">Semaine</option>
                                    <option value="month">Mois</option>
                                    <option value="quarter">Trimestre</option>
                                    <option value="year">Année</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-2">
                            <div id="controls" style="display: block;">
                                <label for="scale_select">Echelle :</label>
                            </div>
                        </div>
                        <div class="col-3"></div>
                        <div class="col-2 d-flex flex-column">
                            <label class="text-start" for="viewSelect">Vue :</label>
                            <select id="viewSelect" class="form-select">
                                <option value="gantt">Gantt</option>
                                <option value="scheduler">Calendrier</option>
                            </select>
                        </div>


                    </div>

                    <!-- Conteneur du Scheduler -->
                    <div id="scheduler_here" ></div>

                    <!-- Conteneur du Gantt -->
                    <div id="gantt_here" class="active-view"></div>
                    <!-- Modal Explicatif -->
                    <div class="modal fade" id="ganttHelpModal" tabindex="-1" role="dialog" aria-labelledby="ganttHelpModalLabel" aria-hidden="true" style="background: transparent;">
                        <div class="modal-dialog modal-lg" role="document" >
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="ganttHelpModalLabel">📌 Guide d'Utilisation du Gantt</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" style="background: white;">
                                    <!-- Étapes du tutoriel -->
                                    <div id="gantt-step-1" class="gantt-step">
                                        <h5>1️⃣ 📂 Projets, Tâches et Jalons</h5>
                                        <p>Dans le diagramme de Gantt :</p>
                                        <ul>
                                            <li>📌 **Un projet** est un regroupement de tâches.</li>
                                            <li>✅ **Une tâche** est une action qui a une durée et une date de début.</li>
                                            <li>🚩 **Un jalon** est un point clé qui marque une étape importante (sans durée).</li>
                                        </ul>
                                        <p>🖱️ **Cliquez sur un projet pour voir ses tâches.**</p>
                                    </div>

                                    <div id="gantt-step-2" class="gantt-step" style="display: none;">
                                        <h5>2️⃣ ➕ Ajout de Tâches</h5>
                                        <p>- Cliquez sur le **"+" (Ajouter une tâche)** dans la colonne des tâches.</p>
                                        <p>- Donnez un **nom**, une **date de début** et une **durée**.</p>
                                        <p>- Appuyez sur **Entrée** pour valider.</p>
                                    </div>

                                    <div id="gantt-step-3" class="gantt-step" style="display: none;">
                                        <h5>3️⃣ ✏️ Modification des Tâches</h5>
                                        <p>- **Glissez-déposez** une tâche pour **changer sa position dans le temps**.</p>
                                        <p>- **Étirez une tâche** sur les côtés pour **modifier sa durée**.</p>
                                        <p>- **Cliquez sur une tâche** pour ouvrir un formulaire d'édition.</p>
                                    </div>

                                    <div id="gantt-step-4" class="gantt-step" style="display: none;">
                                        <h5>4️⃣ 🔗 Création de Dépendances</h5>
                                        <p>- Cliquez sur une **tâche**, puis **reliez-la** à une autre avec un **lien**.</p>
                                        <p>- Cela signifie que la **deuxième tâche dépend de la première** pour commencer.</p>
                                    </div>

                                    <div id="gantt-step-5" class="gantt-step" style="display: none;">
                                        <h5>5️⃣ 🎨 Personnalisation de l'Affichage</h5>
                                        <p>- Utilisez la liste déroulante pour **changer l'échelle de temps** :
                                            📅 **Jour, Semaine, Mois, Trimestre, Année**.</p>
                                        <p>- **Ajustez la largeur des colonnes** en **glissant les bordures**.</p>
                                        <p>- **Affichez les détails** en cliquant sur une tâche.</p>
                                    </div>

                                    <div id="gantt-step-6" class="gantt-step" style="display: none;">
                                        <h5>6️⃣ 💾 Sauvegarde et Synchronisation</h5>
                                        <p>- Les modifications sont **automatiquement enregistrées**.</p>
                                    </div>
                                </div>

                                <!-- Navigation entre les étapes -->
                                <div class="modal-footer" style="background: white;">
                                    <button type="button" class="btn btn-secondary" id="prev-step" disabled>⬅ Précédent</button>
                                    <button type="button" class="btn btn-primary" id="next-step">Suivant ➡</button>
                                    <button type="button" class="btn btn-success" id="close-tutorial" style="display: none;" data-dismiss="modal">🚀 J'ai compris</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
<script>
$(document).ready(function() {
    console.log("✅ Tutoriel interactif chargé !");

    let currentStep = 1;
    const totalSteps = 6;

    function showStep(step) {
        $(".gantt-step").hide();
        $("#gantt-step-" + step).show();

        // Gestion des boutons
        $("#prev-step").prop("disabled", step === 1);
        $("#next-step").toggle(step < totalSteps);
        $("#close-tutorial").toggle(step === totalSteps);
    }

    // Initialiser à l'étape 1
    showStep(currentStep);

    // Bouton "Suivant"
    $("#next-step").click(function() {
        if (currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        }
    });

    // Bouton "Précédent"
    $("#prev-step").click(function() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });

    // ✅ Correction : Fermer le modal proprement
    $("#close-tutorial, .close").click(function() {
        console.log("🚀 Fermeture du popup...");
        $("#ganttHelpModal").modal('hide'); // Ferme le modal
    });

    // ✅ S'assurer que Bootstrap fonctionne et afficher le modal
    if (typeof $.fn.modal === "function") {
        console.log("✅ Bootstrap Modal fonctionne !");
        $("#ganttHelpModal").modal("show"); // Afficher la popup automatiquement
    } else {
        console.error("❌ Bootstrap Modal n'est pas chargé !");
    }
});

</script>
    <script type="text/javascript">
        function initGantt(){
            gantt.config.xml_date = "%Y-%m-%d %H:%i:%s"; // format pour charger les données
            gantt.config.date_format = "%d %F %Y"; // format d'affichage des dates dans les tâches
            gantt.config.columns = [
                {name: "text", label: "Tâche", tree: true, width: "*", min_width: 120, resize: true},
                {name: "start_date", label: "Début", align: "center",  resize: true},
                {name: "end_date", label: "Fin", align: "center", width: 100, resize: true , template: function(task) {
                    var endDate = gantt.calculateEndDate(task.start_date, task.duration);
                    return gantt.templates.date_grid(endDate);
                }},
                {name: "duration", label: "Durée", align: "center", width: 70, resize: true},
                {name: "add", label: "", width: 40}
            ];
            gantt.config.scale_height = 50;

            gantt.config.types["customType"] = "type_id";
            gantt.locale.labels['type_' + "customType"] = "New Type";
            gantt.config.lightbox["customType" + "_sections"] = [
                {name: "description", height: 70, map_to: "text", type: "textarea", focus: true},
                {name: "type", type: "typeselect", map_to: "type"}
            ];


            gantt.config.scales = [
                {unit: "month", step: 1, format: "%F, %Y"},
                {unit: "day", step: 1, format: "%j, %D"}
            ];

            gantt.templates.rightside_text = function (start, end, task) {
                if (task.type == gantt.config.types.milestone) {
                    return task.text;
                }
                return "";
            };
            gantt.config.lightbox.sections = [
                {name: "description", height: 70, map_to: "text", type: "textarea", focus: true},
                {name: "type", type: "typeselect", map_to: "type"},
                {name: "time", type: "duration", map_to: "auto"}
            ];
            gantt.config.keep_grid_width = false;
            gantt.config.grid_resize = true;

            gantt.attachEvent("onColumnResizeStart", function (index, column) {
                gantt.message("Start resizing <b>" + gantt.locale.labels["column_" + column.name] + "</b>");
                return true;
            });
            var message = null;
            gantt.attachEvent("onColumnResize", function (index, column, new_width) {
                if (!message) {
                    message = gantt.message({
                        expire: -1,
                        text: "<b>" + gantt.locale.labels["column_" + column.name] + "</b> is now <b id='width_placeholder'></b><b>px</b> width"
                    });
                }
                document.getElementById("width_placeholder").innerText = new_width
            });

            // return false to discard the resize
            gantt.attachEvent("onColumnResizeEnd", function (index, column, new_width) {
                gantt.message.hide(message);
                message = null;
                gantt.message("Column <b>" + gantt.locale.labels["column_" + column.name] + "</b> is now " + new_width + "px width");
                return true;
            });

            // return false to discard the resize
            gantt.attachEvent("onGridResizeStart", function (old_width) {
                gantt.message("Start grid resizing");
                return true;
            });

            gantt.attachEvent("onGridResize", function (old_width, new_width) {
                if (!message) {
                    message = gantt.message({
                        expire: -1,
                        text: "Grid is now <b id='width_placeholder'></b><b>px</b> width"
                    });
                }
                document.getElementById("width_placeholder").innerText = new_width;
            });

            // return false to discard the resize
            gantt.attachEvent("onGridResizeEnd", function (old_width, new_width) {
                gantt.message.hide(message);
                message = null;
                gantt.message("Grid is now <b>" + new_width + "</b>px width");
                return true;
            });
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

            // Ajuster dynamiquement la largeur du Gantt en fonction des tâches affichées
            function adjustGanttWidth() {
                let taskArea = document.querySelector(".gantt_task");
                if (taskArea) {
                    let gridWidth = document.querySelector(".gantt_grid").offsetWidth; // Largeur de la grille
                    let parentWidth = document.getElementById("gantt_here").offsetWidth; // Largeur du Gantt

                    // Ajuster la largeur pour que toutes les tâches soient visibles sans scroll horizontal
                    let neededWidth = gantt.getState().max_date - gantt.getState().min_date;
                    let dayWidth = gantt.date.add(gantt.getState().min_date, 1, "day") - gantt.getState().min_date;
                    let newWidth = (neededWidth / dayWidth) * 40; // Ajuste selon la densité des jours

                    taskArea.style.width = Math.max(newWidth, parentWidth - gridWidth) + "px"; // Ajuste la largeur
                }
            }

            // Ajuster au chargement et en cas de redimensionnement
            gantt.attachEvent("onDataRender", adjustGanttWidth);
            window.addEventListener("resize", adjustGanttWidth);


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
                /*if (action === "inserted") {
                    $('#alertMessage').text('Ajout éffectué.');
                    $('#alertModal').modal('show');
                    // console.log("Succès:", response);
                } else if (action === "updated") {
                    $('#alertMessage').text('Modification effectué.');
                    $('#alertModal').modal('show');
                } else if (action === "deleted") {
                    $('#alertMessage').text('Suppression effectué.');
                    $('#alertModal').modal('show');
                } else*/ if (action === "error") {
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

        }


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
<script>
    function initScheduler() {
        scheduler.config.header = [
            "day",
            "week",
            "month",
            "date",
            "prev",
            "today",
            "next"
        ];
        //la structure du calendrier
        scheduler.locale = {
            date: {
                month_full: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin",
                    "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
                month_short: ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin",
                    "Juil", "Aoû", "Sep", "Oct", "Nov", "Déc"],
                day_full: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"],
                day_short: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam"]
            },
            labels: {
                dhx_cal_today_button: "Aujourd'hui",
                day_tab: "Jour",
                week_tab: "Semaine",
                month_tab: "Mois",
                new_event: "Nouvel événement",
                icon_save: "Enregistrer",
                icon_cancel: "Annuler",
                icon_details: "Détails",
                icon_edit: "Modifier",
                icon_delete: "Supprimer",
                dhx_delete_btn: "Supprimer",
                dhx_cancel_btn: "Annuler",
                dhx_save_btn: "Enregistrer",
                confirm_closing: "Vos modifications seront perdues, êtes-vous sûr ?",
                confirm_deleting: "L'événement sera supprimé définitivement, êtes-vous sûr ?",
                section_description: "Description",
                section_time: "Période",
                full_day: "Journée entière",
                confirm_recurring: "Voulez-vous modifier toute la série d'événements récurrents ?",
                repeating_event: "Événement récurrent",
                cancel_recurring: "Annuler",
                edit_series: "Modifier la série",
                edit_occurrence: "Modifier l'occurrence",
                agenda_tab: "Agenda",
                year_tab: "Année",
                week_agenda_tab: "Agenda semaine",
                grid_tab: "Grille",
                drag_to_create: "Glissez pour créer",
                drag_to_move: "Glissez pour déplacer",
                message_ok: "OK",
                message_cancel: "Annuler"
            }
        };

        scheduler.config.xml_date = "%Y-%m-%d %H:%i:%s";
        scheduler.init("scheduler_here", new Date(), "month");

        scheduler.load("/api/scheduler-data", "json");

        var dp = new dataProcessor("/api/scheduler/");
        dp.init(scheduler);
        dp.setTransactionMode("REST");
    }



</script>
<script>
    document.getElementById("viewSelect").addEventListener("change", function() {
        var selectedView = this.value;

        if (selectedView === "scheduler") {
            document.getElementById("scheduler_here").classList.add("active-view");
            document.getElementById("gantt_here").classList.remove("active-view");
            document.getElementById("controls").style.display = "none";
            initScheduler();
        } else {
            document.getElementById("gantt_here").classList.add("active-view");
            document.getElementById("controls").style.display = "block";
            document.getElementById("scheduler_here").classList.remove("active-view");
            initGantt();
        }
    });

    // Chargement initial
    window.onload = function() {
        initGantt(); // Charge le Gantt par défaut
    };

</script>

@endsection
