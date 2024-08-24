@extends('layouts.app')
<link href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css" rel="stylesheet">
<script src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>

<style>
    .file-card {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        margin-bottom: 15px;
        position: relative;
        width: 150px;
        height: 150px;
    }
    .file-card img {
        max-width: 100px;
        max-height: 100px;
    }
    .file-card .file-name {
        margin-top: 100px;
        font-size: 12px;
    }
    .file-card .upload-icon {
        position: absolute;
        top: 10px;
        right: 22px;
        font-size: 24px;
        cursor: pointer;
    }
    #file-display {
        display: flex;
        flex-wrap: wrap;
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
                            <li class="breadcrumb-item active" aria-current="page">Validation</li>
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
                <h5 class="card-title">Validation de projet</h5>

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
    <div class="container">
        <!-- Formulaire pour sélectionner un projet -->
        <form method="GET" action="{{ route('planning.show') }}">
            <div class="form-group">
                <label for="project">Sélectionner un projet :</label>
                <select name="project_id" id="project" class="form-control">
                    <option value="">-- Choisir un projet --</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ isset($selectedProject) && $selectedProject->id == $project->id ? 'selected' : '' }}>
                            {{ $project->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Afficher la planification</button>
        </form>

        <!-- Afficher le tableau de planification si un projet est sélectionné -->
        @if(isset($selectedProject))
            <div class="mt-4">
                <h4>Planification pour le  projet : {{ $selectedProject->title }}</h4>
                <div id="gantt-chart"></div>
            </div>
        @endif
    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(isset($tasks) && $tasks->isNotEmpty())
            var tasks = @json($tasks);

            // Exemple d'initialisation du Gantt Chart
            // Remplacez ce code par le code spécifique à la bibliothèque de Gantt Chart que vous utilisez
            var ctx = document.getElementById('gantt-chart').getContext('2d');
            var ganttChart = new Chart(ctx, {
                type: 'bar', // Exemple de type, remplacez par 'gantt' ou autre selon votre bibliothèque
                data: {
                    labels: tasks.map(task => task.name),
                    datasets: [{
                        label: 'Planification des tâches',
                        data: tasks.map(task => task.duration),
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif
    });
</script>

@endsection
