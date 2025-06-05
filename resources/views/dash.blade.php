@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-3">
                <div class="card shadow-sm kpi-card" style="background: linear-gradient(to right, rgba(104, 155, 225, 0.9), #e7f1ff);">
                    <div class="card-body py-3 d-flex justify-content-between align-items-center text-dark">
                        <div>
                            <h6 class="card-title mb-1">Projets Totaux</h6>
                            <h6 class="mb-0">{{ $totalProjects }}</h6>
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
                            <h6 class="mb-0">{{ $projectStatusCounts['En cours'] ?? 0 }}</h6>
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
                            <h6 class="mb-0">{{ $projectStatusCounts['Prévu'] ?? 0 }}</h6>
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
                            <h6 class="mb-0">{{ $projectStatusCounts['Clôturés'] ?? 0 }}</h6>
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
                            <h6 class="mb-0">{{ $projectStatusCounts['Redémarré'] ?? 0 }}</h6>
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
                            <h6 class="mb-0">{{ $projectStatusCounts['Suspendu'] ?? 0 }}</h6>
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
                            <h6 class="mb-0">{{ $projectStatusCounts['Terminé'] ?? 0 }}</h6>
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
                            <h6 class="mb-0">{{ $projectStatusCounts['Annulé'] ?? 0 }}</h6>
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
                        <h6 class="card-title mb-2 text-center text-dark">Répartition en nombre d'Acteurs</h6>
                        <canvas id="actorsChart" style="max-height: 200px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
    <div class="col-md-6">
        <div class="card shadow-sm p-3">
            <h6 class="text-center mb-2">Répartition par Financement</h6>
            <canvas id="financementChart" style="max-height: 200px;"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm p-3">
            <h6 class="text-center mb-2">Nombre de projet par Année</h6>
            <canvas id="anneeChart" style="max-height: 200px;"></canvas>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm p-3">
            <h6 class="text-center mb-2">Évalution du Budget Mensuel en milliard de dollar américain</h6>
            <canvas id="budgetChart" style="max-height: 200px;"></canvas>
        </div>
    </div>
</div>
@if(session('force_password_change'))
<script>
    Swal.fire({
        title: 'Bienvenue ! 👋',
        html: `
            <p>Pour sécuriser votre compte, veuillez modifier votre mot de passe :</p>
            <ol style="text-align: left;">
                <li>Cliquez sur votre photo de profil en haut à droite.</li>
                <li>Choisissez <b>“Mon compte”</b>.</li>
                <li>Allez dans l’onglet <b>“Mot de passe”</b>.</li>
                <li>Choisissez un mot de passe avec :
                    <ul>
                        <li>Une majuscule</li>
                        <li>Une minuscule</li>
                        <li>Un chiffre</li>
                        <li>Un caractère spécial (@$!%*#?&)</li>
                    </ul>
                </li>
            </ol>
            <p><strong>⚠️ Sans cela, votre compte sera bloqué à la prochaine connexion.</strong></p>
        `,
        icon: 'info',
        confirmButtonText: 'OK',
        width: '600px'
    });
</script>
@endif

       
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    setTimeout(() => {
    const alert = document.getElementById('popup-first-login');
    if (alert) alert.remove();
}, 10000); // disparaît après 10 secondes

    document.addEventListener('DOMContentLoaded', function () {
        window.charts = {}; // Pour éviter les doublons

        const createChart = (name, ctx, config) => {
            if (window.charts[name]) window.charts[name].destroy();
            window.charts[name] = new Chart(ctx, config);
        };

        const statusData = @json($projectStatusCounts);
        const actorsData = @json($actorsCounts);
        const financementData = @json($financements);
        const anneeData = @json($projectsParAnnee);
       

        // Projets par statut
        createChart('projectsStatus', document.getElementById('projectsStatusChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: ['#3498db', '#f1c40f', '#2ecc71', '#e74c3c', '#9b59b6', '#1abc9c']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Acteurs
        createChart('actorsChart', document.getElementById('actorsChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: Object.keys(actorsData),
                datasets: [{
                    label: 'Nombre',
                    data: Object.values(actorsData),
                    backgroundColor: '#87CEFA'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Financement
        // Financement
        createChart('financementChart', document.getElementById('financementChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: financementData.map(f => f.libelle),
                datasets: [{
                    data: financementData.map(f => f.total_projets),
                    backgroundColor: ['#e67e22', '#16a085', '#bdc3c7', '#8e44ad', '#2c3e50']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });


        // Projets par année
        createChart('anneeChart', document.getElementById('anneeChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: Object.keys(anneeData),
                datasets: [{
                    label: 'Projets',
                    data: Object.values(anneeData),
                    borderColor: '#2980b9',
                    fill: false,
                    tension: 0.3
                }]
            }
        });

        const budgetData = @json($budgetsParAnnee);

        // Évolution du Budget Annuel
        createChart('budgetChart', document.getElementById('budgetChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: Object.keys(budgetData), // ex: ['2019', '2020', ...]
                datasets: [{
                    label: 'Montant total ($)',
                    data: Object.values(budgetData),
                    backgroundColor: '#8e44ad'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: value => new Intl.NumberFormat().format(value) + ' $'
                        }
                    }
                }
            }
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
