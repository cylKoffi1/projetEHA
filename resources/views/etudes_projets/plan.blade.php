@extends('layouts.app')

<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">

<script src="https://docs.dhtmlx.com/gantt/codebase/dhtmlxgantt.js?v=9.0.3"></script>
    <!-- DHTMLX Scheduler -->
<link rel="stylesheet" href="https://cdn.dhtmlx.com/scheduler/edge/dhtmlxscheduler.css">
<script src="https://cdn.dhtmlx.com/scheduler/edge/dhtmlxscheduler.js"></script>

<!-- jQuery (nécessaire pour Bootstrap) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://export.dhtmlx.com/gantt/api.js"></script>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<style>
    .gantt_task_line.project {
        background: linear-gradient(to right, #007bff, #339af0);
        border: 1px solid #0056b3;
    }

    .gantt_task_line.task {
        background: #28a745;
        border: 1px solid #1e7e34;
    }

    .gantt_task_line.milestone {
        background: #ffc107;
        border: 1px solid #e0a800;
    }

    .gantt_task_progress {
        background-color: rgba(0, 0, 0, 0.2);
    }

    .zoom-controls {
        margin-top: 10px;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .zoom-controls button {
        font-size: 14px;
        padding: 6px 12px;
    }
</style>

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
        #gantt_here .gantt_task {
            overflow-x: auto !important;
        }
        .gantt_layout_cell {
            overflow-x: hidden !important;
        }
        .active-view {
            display: block !important;
        }
        .gantt_grid_data .gantt_cell {
            border-right: 1px solid blue;
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
@isset($ecran)
@can("consulter_ecran_" . $ecran->id)

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
                            <li class="breadcrumb-item"><a href="">Projet</a></li>
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
                                        <option value="{{ $project->code_projet }}">{{ $project->code_projet }}</option>
                                    @endforeach
                                </select>

                            <br>
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
                    @can("consulter_ecran_" . $ecran->id)
                    <div id="scheduler_here" ></div>

                    <!-- Conteneur du Gantt -->
                    <div id="gantt_here" class="active-view"></div>
                    <div class="zoom-controls">
                        @can("modifier_ecran_" . $ecran->id)
                        <button onclick="zoomIn()" class="btn btn-sm btn-outline-primary">🔍 Zoom +</button>
                        <button onclick="zoomOut()" class="btn btn-sm btn-outline-secondary">🔎 Zoom -</button>
                        <button onclick="zoomToFit()" class="btn btn-sm btn-outline-warning">🧭 Ajuster la vue</button>
                        <button onclick="resetZoom()" class="btn btn-sm btn-outline-dark">↺ Réinitialiser</button>
                        @endcan
                    
                    </div>
                    @endcan
                   
                    <div id="task_table_view" class="mt-3" style="display: none;">
                        @can("consulter_ecran_" . $ecran->id)
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tâche</th>
                                    <th>Début</th>
                                    <th>Durée</th>
                                    <th>Progression</th>
                                </tr>
                            </thead>
                            <tbody id="task_table_body"></tbody>
                        </table>
                        @endcan
                    </div>

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
@endcan
@endisset
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


function zoomToFit() {
    var project = gantt.getSubtaskDates();
    if (!project.start_date || !project.end_date) {
        gantt.message({ type: "warning", text: "Aucune tâche trouvée à afficher." });
        return;
    }

    var areaWidth = document.getElementById("gantt_here").offsetWidth;
    var availableWidth = areaWidth - gantt.config.grid_width;

    const scaleConfigs = [
        {
            name: "hour",
            min_column_width: 80,
            scales: [
                { unit: "hour", step: 1, format: "%H:%i" },
                { unit: "day", step: 1, format: "%d %M" }
            ]
        },
        {
            name: "day",
            min_column_width: 40,
            scales: [
                { unit: "day", step: 1, format: "%d %M" }
            ]
        },
        {
            name: "week",
            min_column_width: 50,
            scales: [
                { unit: "week", step: 1, format: "Semaine #%W" },
                { unit: "day", step: 1, format: "%d %M" }
            ]
        },
        {
            name: "month",
            min_column_width: 60,
            scales: [
                { unit: "month", step: 1, format: "%F %Y" },
                { unit: "week", step: 1, format: "S%W" }
            ]
        },
        {
            name: "year",
            min_column_width: 60,
            scales: [
                { unit: "year", step: 1, format: "%Y" },
                { unit: "month", step: 1, format: "%M" }
            ]
        },
        {
            name: "decade", // Ajout custom
            min_column_width: 80,
            scales: [
                {
                    unit: "year", step: 10,
                    format: function(date) {
                        let start = Math.floor(date.getFullYear() / 10) * 10;
                        return `Années ${start}s`;
                    }
                },
                { unit: "year", step: 1, format: "%Y" }
            ]
        }
    ];

    // Durée du projet en jours
    var totalDurationInDays = Math.ceil((project.end_date - project.start_date) / (1000 * 60 * 60 * 24));

    for (let config of scaleConfigs) {
        const columnsNeeded = Math.ceil(totalDurationInDays / (getDaysPerColumn(config.scales[0]) || 1));
        const neededWidth = columnsNeeded * config.min_column_width;

        if (neededWidth <= availableWidth || config.name === "decade") {
            gantt.config.scales = config.scales;
            gantt.render();
            break;
        }
    }

    function getDaysPerColumn(scale) {
        if (scale.unit === "hour") return 1 / 24;
        if (scale.unit === "day") return 1;
        if (scale.unit === "week") return 7;
        if (scale.unit === "month") return 30;
        if (scale.unit === "year") return 365;
        return 1;
    }
}




function renderTaskTable() {
    const tbody = document.getElementById("task_table_body");
    tbody.innerHTML = "";

    gantt.eachTask(function (task) {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${task.id}</td>
            <td>${task.text}</td>
            <td>${gantt.templates.date_grid(task.start_date)}</td>
            <td>${task.duration}</td>
            <td>${Math.round((task.progress || 0) * 100)}%</td>
        `;
        tbody.appendChild(row);
    });
}


</script>
    <script type="text/javascript">
        function initGantt(){
            gantt.config.xml_date = "%Y-%m-%d %H:%i:%s"; // format pour charger les données
            gantt.config.date_format = "%d %F %Y"; // format d'affichage des dates dans les tâches
            gantt.config.columns = [
                {name: "text", label: "Tâche", tree: true, width: "*", min_width: 120, resize: true},
                {name: "start_date", label: "Début", align: "center",  resize: true},
                {name: "end_date", label: "Fin", align: "center", width: 100, resize: true ,
                    template: function(task) {
                        if (!(task.start_date instanceof Date) || isNaN(task.start_date.getTime())) {
                            return "Date invalide";
                        }
                        var endDate = gantt.calculateEndDate(task.start_date, task.duration || 0);
                        return gantt.templates.date_grid(endDate);
                    }
                },
                {name: "duration", label: "Durée", align: "center", width: 70, resize: true},
                {name: "add", label: "", width: 40}
            ];
            gantt.config.scale_height = 50;
            gantt.eachTask(function(task){
                    console.log("TASK DEBUG", task.id, task.text, task.start_date);
                });

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
            // Couleur par type de tâche
            gantt.templates.task_class = function (start, end, task) {
                if (task.type === "project") return "project";
                if (task.type === "milestone") return "milestone";
                return "task"; // valeur par défaut
            };


            // Infobulle
            gantt.templates.tooltip_text = function (start, end, task) {
                return `<b>${task.text}</b><br/>
                        Début : ${gantt.templates.tooltip_date_format(start)}<br/>
                        Fin : ${gantt.templates.tooltip_date_format(end)}<br/>
                        Durée : ${task.duration} jours<br/>
                        Progression : ${(task.progress || 0) * 100}%`;
            };

            // Zooms
            window.zoomIn = function () {
                if (window.zoomIndex > 0) {
                    window.zoomIndex--;
                    setScaleConfig(window.zoomIndex);
                }
            };

            window.zoomOut = function () {
                if (window.zoomIndex < 7) {
                    window.zoomIndex++;
                    setScaleConfig(window.zoomIndex);
                }
            };

            window.resetZoom = function () {
                window.zoomIndex = 7; // Reset à 'week'
                setScaleConfig(window.zoomIndex);
            };

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
                if (!taskArea) return;

                const project = gantt.getSubtaskDates();
                if (!project.start_date || !project.end_date) return;

                const totalDays = Math.ceil((project.end_date - project.start_date) / (1000 * 60 * 60 * 24));
                const newWidth = totalDays * 40;

                taskArea.style.width = newWidth + "px";
            }

            // Ajuster au chargement et en cas de redimensionnement
            gantt.attachEvent("onDataRender", zoomToFit);
            


            // Configuration de l'échelle de temps
            
            gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";
            gantt.init("gantt_here");

            // Charge les données de l'API
            gantt.load("{{ url('/')}}/api/data", "json").then(() => {
                gantt.eachTask(function(task) {
                    if (!task.start_date || typeof task.start_date === "string") {
                        try {
                            task.start_date = gantt.date.parseDate(task.start_date, gantt.config.xml_date);
                            if (!task.start_date || isNaN(task.start_date.getTime())) {
                                throw new Error("Date invalide");
                            }
                        } catch (e) {
                            console.warn("Tâche avec date invalide :", task);
                            task.start_date = new Date();
                        }
                    }
                });
                gantt.render();
            });


            // Ajoute un écouteur d'événement sur l'ajout d'une nouvelle tâche


            // Configure le dataProcessor pour synchroniser les actions (CRUD)
            var dp = new gantt.dataProcessor("{{ url('/')}}/api/");
            dp.init(gantt);
            dp.setUpdateMode("cell");
            dp.setTransactionMode("REST"); 

            // Ajoute CodeProjet à la tâche avant l'envoi des données
            dp.attachEvent("onBeforeUpdate", function(id, state, data) {
                // Récupère le CodeProjet sélectionné dans le menu déroulant
                const selectedType = gantt.getTask(id)?.type || "task";
                data.type = selectedType;
                data.codeProjet = document.getElementById('projectSelect').value;
              
                //console.log("Enregistrement avec CodeProjet:", data);

                // Renvoie true pour continuer le processus de mise à jour/enregistrement
                return true;
            });
            gantt.attachEvent("onTaskCreated", function(task) {
                const codeProjet = document.getElementById('projectSelect').value; // récupère correctement ici

                task.codeProjet = codeProjet;
                if (!task.type) {
                    task.type = gantt.config.types.task;
                }

                dp.sendData();
                return true;
            });


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
            window.zoomIndex = 2; // 0 = ultra zoom, 5 = très large vue

            function setScaleConfig(index) {
                const configs = [
                    {
                        name: "hour",
                        scales: [
                            { unit: "hour", step: 1, format: "%H:%i" },
                            { unit: "day", step: 1, format: "%d %M" }
                        ]
                    },
                    {
                        name: "day",
                        scales: [
                            { unit: "day", step: 1, format: "%d %M" },
                            { unit: "month", step: 1, format: "%F %Y" }
                        ]
                    },
                    {
                        name: "week",
                        scales: [
                            { unit: "week", step: 1, format: "Semaine %W" },
                            { unit: "month", step: 1, format: "%F" }
                        ]
                    },
                    {
                        name: "month",
                        scales: [
                            { unit: "month", step: 1, format: "%F %Y" },
                            { unit: "year", step: 1, format: "%Y" }
                        ]
                    },
                    {
                        name: "quarter",
                        scales: [
                            {
                                unit: "quarter",
                                step: 1,
                                format: function (date) {
                                    const q = Math.floor(date.getMonth() / 3) + 1;
                                    return "T" + q + " " + date.getFullYear();
                                }
                            },
                            { unit: "year", step: 1, format: "%Y" }
                        ]
                    },
                    {
                        name: "year",
                        scales: [
                            { unit: "year", step: 1, format: "%Y" },
                            { unit: "month", step: 1, format: "%M" }
                        ]
                    },
                    {
                        name: "decade",
                        scales: [
                            {
                                unit: "year", step: 10,
                                format: function(date) {
                                    const start = Math.floor(date.getFullYear() / 10) * 10;
                                    return `Années ${start}s`;
                                }
                            },
                            { unit: "year", step: 1, format: "%Y" }
                        ]
                    }
                ];

                if (index < 0) index = 0;
                if (index >= configs.length) index = configs.length - 1;

                window.zoomIndex = index;

                gantt.config.scales = configs[index].scales;
                gantt.render();
            }


            // Définir l'échelle initiale
            setScaleConfig(6);

        }


    </script>
    <script>
        document.getElementById("projectSelect").addEventListener("change", function () {
            var codeProjet = this.value;

            // Vider le Gantt avant de charger
            gantt.clearAll();

            if (codeProjet) {
                gantt.load(`{{ url('/')}}/api/data?CodeProjet=${codeProjet}`, "json")
                    .then(function () {
                        console.log("✅ Données chargées pour :", codeProjet);

                        // 🔥 Forcer parsing correct de la date
                        gantt.eachTask(function(task) {
                            if (!task.start_date || typeof task.start_date === "string") {
                                try {
                                    task.start_date = gantt.date.parseDate(task.start_date, gantt.config.xml_date);
                                    if (!task.start_date || isNaN(task.start_date.getTime())) {
                                        throw new Error("Date invalide");
                                    }
                                } catch (e) {
                                    console.warn("Tâche avec date invalide :", task);
                                    task.start_date = new Date(); // valeur par défaut pour éviter le plantage
                                }
                            }
                        });


                        gantt.render();
                        zoomToFit(); 
                    })
                    .catch(function (error) {
                        console.error("❌ Erreur de chargement :", error);
                        alert("Erreur lors du chargement des données pour ce projet.");
                    });
            }
        });

        var projectCode = document.getElementById("projectSelect").value;



    </script>
<script>
function initScheduler() {
    scheduler.config.header = [
        "day", "week", "month", "date", "prev", "today", "next"
    ];

    // Localisation FR
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
            confirm_recurring: "Modifier toute la série ?",
            repeating_event: "Événement récurrent",
            cancel_recurring: "Annuler",
            edit_series: "Modifier la série",
            edit_occurrence: "Modifier l'occurrence",
            drag_to_create: "Glissez pour créer",
            drag_to_move: "Glissez pour déplacer",
            message_ok: "OK",
            message_cancel: "Annuler"
        }
    };

    scheduler.config.xml_date = "%Y-%m-%d %H:%i:%s";

    const codeProjet = document.getElementById("projectSelect").value;

    scheduler.clearAll();
    scheduler.init("scheduler_here", new Date(), "month");

    if (codeProjet) {
        scheduler.load(`{{ url('/')}}/api/scheduler-data?CodeProjet=${codeProjet}`, "json", function () {
            const events = scheduler.getEvents();

            if (events.length > 0) {
                // Date la plus ancienne
                const minDate = events.reduce((earliest, ev) => {
                    return ev.start_date < earliest ? ev.start_date : earliest;
                }, events[0].start_date);

                scheduler.setCurrentView(minDate, "month");

                // 📅 Affichage de la date de projet
                document.getElementById("date-now").textContent =
                    `📅 Projet ${codeProjet} – Début : ${minDate.toLocaleDateString()}`;
            } else {
                scheduler.setCurrentView(new Date(), "month");
                document.getElementById("date-now").textContent =
                    `📅 Projet ${codeProjet} – Aucune tâche`;
            }
        });

        const dpa = new scheduler.DataProcessor("{{ url('/')}}/api/scheduler/");
        dpa.init(scheduler);
        dpa.setTransactionMode("REST");

    } else {
        scheduler.setCurrentView(new Date(), "month");
        document.getElementById("date-now").textContent =
            `📅 Aucun projet sélectionné – ${new Date().toLocaleDateString()}`;
    }
}



</script>
<script>
document.getElementById("viewSelect").addEventListener("change", function () {
    const selectedView = this.value;
    const ganttContainer = document.getElementById("gantt_here");
    const schedulerContainer = document.getElementById("scheduler_here");
    const zoomControls = document.querySelector(".zoom-controls");

    if (selectedView === "scheduler") {
        schedulerContainer.classList.add("active-view");
        ganttContainer.classList.remove("active-view");
        if (zoomControls) zoomControls.style.display = "none";
        initScheduler();
    } else {
        ganttContainer.classList.add("active-view");
        schedulerContainer.classList.remove("active-view");
        if (zoomControls) zoomControls.style.display = "flex";
        initGantt();
    }
});


    // Chargement initial
    window.onload = function() {
        initGantt(); // Charge le Gantt par défaut
    };

</script>

@endsection
