@extends('layouts.app')

<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
    <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
    <style>

        #gantt_here {
            width: 100%;
            height: 80vh;
        }

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
                    <div class="col-4"><br>
                        <select id="projectSelect" class="form-select" name="projectSelect">
                            <option value="">-- Sélectionner un projet --</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->CodeProjet }}">{{ $project->CodeProjet }}</option>
                            @endforeach
                        </select><br>
                    </div>

                    <!-- Conteneur Gantt -->
                    <div id="gantt_here"></div>

                    <!-- Bouton pour Enregistrer -->
                    <div class="mt-3">
                        <button id="saveButton" class="btn btn-primary">Enregistrer le Gantt</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        gantt.init("gantt_here");

        // Récupérer la sélection de projet
        var projectSelect = document.getElementById('projectSelect');

        // Charger les tâches du projet sélectionné
        document.getElementById('projectSelect').addEventListener('change', function () {
            var projectId = this.value;

            if (projectId) {
                fetch(`/gantt/load/${projectId}`)
                    .then(response => response.json())
                    .then(data => {
                        gantt.clearAll(); // Vider les anciennes données
                        gantt.parse(data); // Charger les nouvelles données du projet sélectionné
                    })
                    .catch(error => {
                        console.error('Erreur lors du chargement des tâches :', error);
                        alert('Erreur lors du chargement des tâches.');
                    });
            }
        });

        // Ajouter une tâche
        gantt.attachEvent("onAfterTaskAdd", function(id, item) {
            // Récupérer le code projet sélectionné
            var projectSelect = document.getElementById('projectSelect');
            item.project_id = projectSelect.value;  // Associer le code projet sélectionné à la tâche

            fetch('/gantt/task', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(item)  // Envoyer la tâche avec le code projet
            })
            .then(response => response.json())
            .then(data => {
                if (data.tid) {
                    gantt.changeTaskId(id, data.tid); // Mettre à jour l'ID local
                } else {
                    alert('Erreur: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout de la tâche.');
            });
        });


        // Mise à jour d'une tâche
        gantt.attachEvent("onAfterTaskUpdate", function (id, item) {
            fetch(`/gantt/task/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(item)
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour de la tâche :', error);
            });
        });

        // Suppression d'une tâche
        gantt.attachEvent("onAfterTaskDelete", function (id) {
            fetch(`/gantt/task/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .catch(error => {
                console.error('Erreur lors de la suppression de la tâche :', error);
            });
        });

        // Ajouter un lien
        gantt.attachEvent("onAfterLinkAdd", function (id, item) {
            fetch('/gantt/link', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(item)
            })
            .then(response => response.json())
            .then(data => {
                gantt.changeLinkId(id, data.tid); // Mettre à jour l'ID local
            })
            .catch(error => {
                console.error('Erreur lors de l\'ajout du lien :', error);
            });
        });

        // Mise à jour d'un lien
        gantt.attachEvent("onAfterLinkUpdate", function (id, item) {
            fetch(`/gantt/link/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(item)
            })
            .catch(error => {
                console.error('Erreur lors de la mise à jour du lien :', error);
            });
        });

        // Suppression d'un lien
        gantt.attachEvent("onAfterLinkDelete", function (id) {
            fetch(`/gantt/link/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .catch(error => {
                console.error('Erreur lors de la suppression du lien :', error);
            });
        });

        // Sauvegarde manuelle des tâches et des liens
        document.getElementById('saveButton').addEventListener('click', function () {
            var tasks = gantt.serialize().data;
            var links = gantt.serialize().links;

            // Convertir les dates au format MySQL avant de les envoyer
            tasks = tasks.map(task => {
                let date = new Date(task.start_date);

                // Vérifier si la date est valide
                if (!isNaN(date)) {
                    // Convertir la date au format YYYY-MM-DD HH:MM:SS
                    task.start_date = date.toISOString().split('T')[0] + ' ' + date.toTimeString().split(' ')[0];
                } else {
                    console.error('Date invalide pour la tâche : ', task);
                    alert('Une tâche contient une date invalide.');
                    return;  // Stopper si une date est invalide
                }
                return task;
            });

            // Envoi de la requête au serveur
            fetch('/gantt/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ tasks: tasks, links: links })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(error => {
                        throw new Error(error.error || "Erreur inconnue");
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Gantt enregistré avec succès');
                } else {
                    alert('Erreur lors de la sauvegarde');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la sauvegarde : ' + error.message);
            });
        });
    });

</script>
@endsection
