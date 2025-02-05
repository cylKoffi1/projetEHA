@extends('layouts.app')

@section('content')
<div class="container-fluid">

<div class="row">
    <div class="col-md-3">
        <div class="card shadow-sm kpi-card" style="background: linear-gradient(to right, rgba(104, 155, 225, 0.9), #e7f1ff);">
            <div class="card-body py-3 d-flex justify-content-between align-items-center text-dark">
                <div>
                    <h6 class="card-title mb-1">Projets Totaux</h6>
                    <h6 class="mb-0">45</h6>
                </div>
                <i class="fas fa-layer-group fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm kpi-card" style="background: linear-gradient(to right, rgba(104, 155, 225, 0.9), #e7f1ff);">
            <div class="card-body py-3 d-flex justify-content-between align-items-center text-dark">
                <div>
                    <h6 class="card-title mb-1">Projets en Cours</h6>
                    <h6 class="mb-0">25</h6>
                </div>
                <i class="fas fa-spinner fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm kpi-card" style="background: linear-gradient(to right, rgba(104, 155, 225, 0.9), #e7f1ff);">
            <div class="card-body py-3 d-flex justify-content-between align-items-center text-dark">
                <div>
                    <h6 class="card-title mb-1">Projets Prévus</h6>
                    <h6 class="mb-0">10</h6>
                </div>
                <i class="fas fa-calendar-check fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm kpi-card" style="background: linear-gradient(to right, rgba(104, 155, 225, 0.9), #e7f1ff);">
            <div class="card-body py-3 d-flex justify-content-between align-items-center text-dark">
                <div>
                    <h6 class="card-title mb-1">Projets Clôturés</h6>
                    <h6 class="mb-0">5</h6>
                </div>
                <i class="fas fa-check-circle fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm kpi-card" style="background: linear-gradient(to right, rgba(104, 155, 225, 0.9), #e7f1ff);">
            <div class="card-body py-3 d-flex justify-content-between align-items-center text-dark">
                <div>
                    <h6 class="card-title mb-1">Projets Redémarrés</h6>
                    <h6 class="mb-0">45</h6>
                </div>
                <i class="fas fa-redo-alt fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm kpi-card" style="background: linear-gradient(to right, rgba(104, 155, 225, 0.9), #e7f1ff);">
            <div class="card-body py-3 d-flex justify-content-between align-items-center text-dark">
                <div>
                    <h6 class="card-title mb-1">Projets Suspendus</h6>
                    <h6 class="mb-0">25</h6>
                </div>
                <i class="fas fa-pause-circle fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm kpi-card" style="background: linear-gradient(to right, rgba(104, 155, 225, 0.9), #e7f1ff);">
            <div class="card-body py-3 d-flex justify-content-between align-items-center text-dark">
                <div>
                    <h6 class="card-title mb-1">Projets Terminés</h6>
                    <h6 class="mb-0">10</h6>
                </div>
                <i class="fas fa-flag-checkered fa-2x"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm kpi-card" style="background: linear-gradient(to right, rgba(104, 155, 225, 0.9), #e7f1ff);">
            <div class="card-body py-3 d-flex justify-content-between align-items-center text-dark">
                <div>
                    <h6 class="card-title mb-1">Projets Annulés</h6>
                    <h6 class="mb-0">5</h6>
                </div>
                <i class="fas fa-times-circle fa-2x"></i>
            </div>
        </div>
    </div>
</div>


    <!-- Charts Section -->

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm" style="background-color: rgba(250, 250, 250, 0.9);">
                <div class="card-body p-3">
                    <h6 class="card-title mb-2 text-center text-dark">Répartition des Projets par Statut</h6>
                    <canvas id="projectsStatusChart" style="max-height: 200px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm" style="background-color: rgba(250, 250, 250, 0.9);">
                <div class="card-body p-3">
                    <h6 class="card-title mb-2 text-center text-dark">Répartition des Acteurs</h6>
                    <canvas id="actorsChart" style="max-height: 200px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Multi-Graph Section -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card shadow-sm" style="background-color: rgba(250, 250, 250, 0.9);">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-2 text-dark">Statistiques Avancées</h6>
                        <select id="chartSelector" class="form-select form-select-sm" style="width: 250px;">
                            <option value="line" selected>Budget des Projets</option>
                            <option value="bar">Projets par Année</option>
                            <option value="pie">Répartition Financière</option>
                            <option value="radar">Performance Acteurs</option>
                            <option value="polarArea">Contributions Régionales</option>
                            <option value="doughnut">Répartition des Dépenses</option>
                            <option value="scatter">Analyse de Rentabilité</option>
                            <option value="bubble">Progression des Objectifs</option>
                            <option value="stackedBar">Projets Complétés</option>
                            <option value="stackedLine">Croissance Cumulée</option>
                        </select>
                    </div>
                    <canvas id="advancedChart" style="max-height: 200px;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const projectsStatusCtx = document.getElementById('projectsStatusChart').getContext('2d');
        const actorsCtx = document.getElementById('actorsChart').getContext('2d');
        const advancedCtx = document.getElementById('advancedChart').getContext('2d');

        // Pie Chart for Projects Status
        new Chart(projectsStatusCtx, {
            type: 'pie',
            data: {
                labels: ['En cours', 'Prévus', 'Clôturés', 'Suspendus','Annulés','Redémarrés'],
                datasets: [{
                    data: [45, 25, 10, 5,20, 9],
                    backgroundColor: ['rgba(54, 162, 235, 0.5)', 'rgba(75, 192, 192, 0.5)', 'rgba(255, 206, 86, 0.5)', 'rgba(255, 99, 132, 0.5)', 'rgba(151, 99, 255, 0.5)', 'rgba(109, 255, 99, 0.5)'],
                }]
            }
        });

        // Bar Chart for Actors
        new Chart(actorsCtx, {
            type: 'bar',
            data: {
                labels: ['Maîtres d’Ouvrage', 'Maîtres d’Œuvre', 'Bailleurs', 'Bénéficiaires', 'Chefs de Projet'],
                datasets: [{
                    data: [15, 35, 8, 30, 10],
                    backgroundColor: ['rgba(54, 162, 235, 0.5)', 'rgba(110, 41, 41, 0.5)', 'rgba(75, 192, 192, 0.5)', 'rgba(255, 206, 86, 0.5)', 'rgba(153, 102, 255, 0.5)']
                }]
            }
        });

        // Dynamic Multi-Graph
        let advancedChart = new Chart(advancedCtx, {
            type: 'line',
            data: {
                labels: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai'],
                datasets: [{
                    label: 'Budget Alloué ($M)',
                    data: [10, 12, 15, 18, 20],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 0.8)',
                    tension: 0.4,
                    fill: true
                }]
            }
        });

        document.getElementById('chartSelector').addEventListener('change', function (event) {
            const chartType = event.target.value;
            advancedChart.destroy();
            advancedChart = new Chart(advancedCtx, {
                type: chartType,
                data: {
                    labels: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai'],
                    datasets: [{
                        label: 'Données dynamiques',
                        data: [10, 12, 15, 18, 20],
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 0.8)',
                        tension: 0.4,
                        fill: true
                    }]
                }
            });
        });
    });
</script>

<style>
    .kpi-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
</style>
@endsection
