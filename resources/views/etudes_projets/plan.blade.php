@extends('layouts.app')

<link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
    <script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
    <style>

        #gantt_here {
            width: 100%;
            height: 40vh;
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

                    <!-- Boutons pour Enregistrer et Supprimer -->
                    <div class="mt-3 d-flex justify-content-end">
                        <button id="saveButton" class="btn btn-primary">Enregistrer le Gantt  </button>
                        <button id="deleteButton" class="btn btn-danger">Supprimer le Projet</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
   /* document.addEventListener("DOMContentLoaded", function () {
        gantt.init("gantt_here");

            gantt.templates.task_class = function (start, end, task) {
                switch (task.priority) {
                    case "high": return "red_color";
                    case "medium": return "blue_color";
                    case "low": return "gray_color";
                }
            };
        /* Fonction pour convertir les dates au format ISO
        function formatDateForGantt(dateStr) {
            const match = dateStr.match(/^(\d{2})-(\d{2})-(\d{4}) (\d{2}):(\d{2})$/);
            if (match) {
                return `${match[3]}-${match[2]}-${match[1]}T${match[4]}:${match[5]}:00`;
            } else {
                throw new Error('Format de date invalide');
            }
        }*//*
        function formatDateForGantt(dateStr) {
            if (!dateStr) return null;  // Gérer les cas où la date est null ou undefined

            // Essayer de créer un objet Date avec la chaîne donnée
            let date = new Date(dateStr);

            // Vérifier si la date est valide
            if (isNaN(date.getTime())) {
                throw new Error('Format de date invalide: ' + dateStr);
            }

            // Retourner la date au format ISO 8601
            return date.toISOString();
        }


        // Récupérer la sélection de projet
        document.getElementById('projectSelect').addEventListener('change', function () {
            var projectId = this.value;
            var saveButton = document.getElementById('saveButton');

            if (projectId) {
                fetch(`/gantt/check/${projectId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            saveButton.textContent = 'Modifier le Gantt';
                        } else {
                            saveButton.textContent = 'Enregistrer le Gantt';
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors de la vérification des données:', error);
                        alert('Erreur lors de la vérification des données.');
                    });

                    fetch(`/gantt/load/${projectId}`)
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(`Erreur HTTP ${response.status}: ${text}`);
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.data && Array.isArray(data.data.tasks)) {
                            // Assurez-vous que les dates sont correctement formatées
                            data.data.tasks.forEach(task => {
                                if (task.start_date) {
                                    task.start_date = formatDateForGantt(task.start_date);
                                }
                                if (task.end_date) {
                                    task.end_date = formatDateForGantt(task.end_date);
                                }
                            });

                            gantt.clearAll();
                            gantt.parse(data.data);
                        } else {
                            console.error('Format de données invalide:', data);
                            alert('Erreur lors du chargement des données Gantt.');
                        }
                    })
                    .catch(error => {
                        console.error('Erreur lors du chargement des données Gantt:', error);
                        alert('Erreur lors du chargement des données Gantt : ' + error.message);
                    });

            } else {
                gantt.clearAll();
                saveButton.textContent = 'Enregistrer le Gantt';
            }
        });


        gantt.attachEvent("onAfterTaskAdd", function (id, item) {
            var projectSelect = document.getElementById('projectSelect');
            item.project_id = projectSelect.value;

            fetch('/gantt/task', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(item)
            })
            .then(response => response.json())
            .then(data => {
                if (data.tid) {
                    gantt.changeTaskId(id, data.tid);
                } else {
                    alert('Erreur: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'ajout de la tâche.');
            });
        });

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
                gantt.changeLinkId(id, data.tid);
            })
            .catch(error => {
                console.error('Erreur lors de l\'ajout du lien :', error);
            });
        });

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

        document.getElementById('saveButton').addEventListener('click', function () {
            var tasks = gantt.serialize().data;
            var links = gantt.serialize().links;

            // Exemple de traitement des tâches avant l'envoi au serveur
            tasks = tasks.map(task => {
                try {
                    // Formatage de la date de début
                    if (task.start_date) {
                        task.start_date = formatDateForGantt(task.start_date);
                    }

                    // Formatage de la date de fin
                    if (task.end_date) {
                        task.end_date = formatDateForGantt(task.end_date);
                    }

                    // Optionnel : Calculer la date de fin si elle n'est pas fournie
                    if (!task.end_date && task.start_date && task.duration) {
                        let startDate = new Date(task.start_date);
                        let endDate = new Date(startDate);
                        endDate.setDate(startDate.getDate() + task.duration); // Ajouter la durée à la date de début
                        task.end_date = endDate.toISOString();
                    }

                    return task;
                } catch (error) {
                    console.error('Date invalide pour la tâche : ', task);
                    alert('Une tâche contient une date invalide.');
                    return null;  // Exclure la tâche invalide
                }
            }).filter(task => task !== null); // Exclure les tâches invalides


            var projectId = document.getElementById('projectSelect').value;
            if (!projectId) {
                alert('Veuillez sélectionner un projet.');
                return;
            }

            fetch('/gantt/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    project_id: projectId,
                    tasks: tasks,
                    links: links
                })
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

*/
        document.addEventListener("DOMContentLoaded", function () {
            gantt.init("gantt_here");
                gantt.templates.task_class = function (start, end, task) {
                    switch (task.priority) {
                        case "high": return "red_color";
                        case "medium": return "blue_color";
                        case "low": return "gray_color";
                    }
                };




                // Initialisation de Gantt
                gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";
                gantt.config.date_format = "%Y-%m-%d %H:%i:%s";

                gantt.config.xml_date = "%Y-%m-%dT%H:%i:%s";
                gantt.config.date_format = "%Y-%m-%dT%H:%i:%s";
                gantt.init("gantt_here");
                function isValidDate(dateStr) {
                    const date = new Date(dateStr);
                    return !isNaN(date.getTime());
                }

                function formatDateForGantt(dateStr) {
                    if (!dateStr) {
                        console.error('Date non définie ou vide:', dateStr);
                        return null;
                    }

                    const date = new Date(dateStr);  // Conversion en objet Date
                    if (isNaN(date.getTime())) {
                        console.error('Date invalide:', dateStr);
                        return null;
                    }

                    return date.toISOString();  // Conversion en format ISO 8601
                }

        // Charger les données du Gantt

        document.getElementById('projectSelect').addEventListener('change', (event) => {
            const projectId = event.target.value;

            const url = `/gantt/load/${projectId}`;
            console.log(`URL appelée : ${url}`);

            fetch(url)
            .then(response => response.json())
            .then(data => {
                if (!data || !data.data || !data.links) {
                    console.error("Format de données invalide :", data);
                    return;
                }

                console.log("Données du projet avant formatage :", data);

                // Formater les dates
                data.data.forEach(task => {
                    task.start_date = formatDateForGantt(task.start_date);
                    task.end_date = formatDateForGantt(task.end_date);
                });

                console.log("Données du projet après formatage :", data);

                gantt.clearAll();
                gantt.parse(data);  // Charger les données dans Gantt
            })
            .catch(error => {
                console.error('Erreur lors du chargement des données Gantt :', error);
            });


        });

        function getDuration(startDate, endDate) {
            let start = new Date(startDate);
            let end = new Date(endDate);

            if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                console.error('Dates invalides pour le calcul de durée:', startDate, endDate);
                return null;
            }

            return (end - start) / (1000 * 60 * 60 * 24); // Durée en jours
        }

        function _getStartEndConfig(task) {
            if (!task.start_date || !task.end_date) {
                console.error('Start date ou end date manquante pour la tâche:', task);
                return null;
            }

            let start = new Date(task.start_date);
            let end = new Date(task.end_date);

            if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                console.error('Dates invalides:', task.start_date, task.end_date);
                return null;
            }

            return {
                start: start,
                end: end
            };
        }

        function saveGanttData(projectId) {
            const tasks = gantt.serialize().data;
            const links = gantt.serialize().links;

            fetch(`/gantt/save/${projectId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    data: {
                        tasks: tasks,
                        links: links
                    }
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    alert('Données sauvegardées avec succès.');
                } else {
                    console.error('Erreur lors de la sauvegarde des données Gantt:', result);
                    alert('Erreur lors de la sauvegarde des données Gantt.');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la sauvegarde des données Gantt:', error);
                alert('Erreur lors de la sauvegarde des données Gantt : ' + error.message);
            });
        }

        function deleteProject(projectId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce projet?')) return;

            fetch(`/gantt/delete/${projectId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Erreur de réseau');
                return response.json();
            })
            .then(result => {
                if (result.status === 'success') {
                    gantt.clearAll();
                    document.getElementById('projectSelect').value = '';
                    alert('Projet supprimé avec succès.');
                } else {
                    console.error('Erreur lors de la suppression du projet:', result);
                    alert('Erreur lors de la suppression du projet.');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la suppression du projet:', error);
                alert('Erreur lors de la suppression du projet : ' + error.message);
            });
        }

        document.getElementById('saveButton').addEventListener('click', () => {
            const projectId = document.getElementById('projectSelect').value;
            if (projectId) {
                saveGanttData(projectId);
            } else {
                alert('Veuillez sélectionner un projet avant de sauvegarder.');
            }
        });

        document.getElementById('deleteButton').addEventListener('click', () => {
            const projectId = document.getElementById('projectSelect').value;
            if (projectId) {
                deleteProject(projectId);
            } else {
                alert('Veuillez sélectionner un projet avant de supprimer.');
            }
        });

    });
</script>


@endsection
