@extends('layouts.app')

@section('content')
{{-- @include('layouts.header') --}}

<div class="page-heading">
    <br><br><br>
</div>
<div class="page-content">
    <section class="row">
        <div class="col-12">
            <div class="row" style="justify-content: space-around;">
                <div class="container">
                  <div class="row">
                    @if($projets->count()!=0)
                    <div class="col">
                      <div class="card text-white mb-2">
                        <div class="card-body">

                            <div class="row">
                                <h6 class="text-muted font-semibold">Total Projets</h6>
                                <h6 class="font-extrabold mb-0">{{ $projets->count() }}</h6>
                            </div>
                        </div>
                      </div>
                    </div>
                    @endif
                    @if($projets_prevus->count()!=0)
                    <div class="col">
                      <div class="card text-white mb-2">
                        <div class="card-body">

                            <div class="row">
                                <h6 class="text-muted font-semibold">Prévus</h6>
                                <h6 class="font-extrabold mb-0">{{ $projets_prevus->count() }}</h6>
                            </div>
                        </div>
                      </div>
                    </div>
                    @endif
                    @if($projets_en_cours->count()!=0)
                    <div class="col">
                      <div class="card text-white mb-2">
                        <div class="card-body">

                            <div class="row">
                                <h6 class="text-muted font-semibold">En cours</h6>
                                <h6 class="font-extrabold mb-0">{{ $projets_en_cours->count() }}</h6>
                            </div>
                        </div>
                      </div>
                    </div>
                    @endif
                    @if($projets_cloture->count()!=0)
                    <div class="col">
                      <div class="card text-white mb-2">
                        <div class="card-body">

                            <div class="row">
                                <h6 class="text-muted font-semibold">Cloturés</h6>
                                <h6 class="font-extrabold mb-0">{{ $projets_cloture->count() }}</h6>
                            </div>
                        </div>
                      </div>
                    </div>
                    @endif
                    @if($projets_redemarrer->count()!=0)
                    <div class="col">
                      <div class="card text-white mb-2">
                        <div class="card-body">

                            <div class="row">
                                <h6 class="text-muted font-semibold">Redémarrer</h6>
                                <h6 class="font-extrabold mb-0">{{ $projets_redemarrer->count() }}</h6>
                            </div>
                        </div>
                      </div>
                    </div>
                    @endif
                    @if($projets_suspendus->count()!=0)
                    <div class="col">
                      <div class="card text-white mb-2">
                        <div class="card-body">

                            <div class="row">
                                <h6 class="text-muted font-semibold">Suspendus</h6>
                                <h6 class="font-extrabold mb-0">{{ $projets_suspendus->count() }}</h6>
                            </div>
                        </div>
                      </div>
                    </div>
                    @endif
                    <p hidden>{{ $Autre=0-$projets_prevus->count()-$projets_en_cours->count()-$projets_cloture->count()-$projets_annulé->count()-$projets_suspendus->count()-$projets_redemarrer->count()+$projets->count() }}</p>
                    @if($Autre!=0)
                    <div class="col">
                        <div class="card text-white mb-2">
                            <div class="card-body">

                            <div class="row">
                                <h6 class="text-muted font-semibold">Autres</h6>
                                <h6 class="font-extrabold mb-0">{{ $Autre }}</h6>
                            </div>
                            </div>
                        </div>
                    </div>
                    @endif
                  </div>
                </div>
            </div>
            <section class="section">

            <!--
                DANS CETTE SECTION LE BUT SERA DE FAIRE UN SYSTEME DE PAGINATION
                ** POUR POUVOIR DONNER L'OCCASION A L UTILISATEUR DE CHOISIR
                ** LE TYPE DE GRAPHIQUE QU IL VOUDRAIT ET LUI DONNER ENSUITE LA POSSIBILITE
                **DE LE TELECHARGER
            -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Dépenses Prévues Secteur
                                EHA
                            </h4>
                            <div class="card-body">
                                <select id="chartTypeSelect" onchange="updateChart()">
                                    <option value="bar">Graphique à Barres</option>
                                    <option value="area">Graphique en Aires</option>
                                    <option value="multi-axis">Graphique à Axes Multiples</option>
                                <!-- <option value="pie">Graphique Circulaire</option>-->
                                </select>

                                <!-- Conteneur pour le graphique -->
                                <div>
                                    <canvas id="myChart" width="400" height="400"></canvas>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>








            </section>
    </section>
</div>

<footer>
    <div class="footer clearfix mb-0 text-muted">
        <div class="float-start">
            <p>2023 &copy; EHA</p>
        </div>
        <div class="float-end">
            <p>Crée par Soro & Cyl</p>
        </div>
    </div>
</footer>
<script>
        // Récupérer les données des projets et des statuts depuis le contrôleur
        const projets = {!! json_encode($projets) !!};
        const statuts = {!! json_encode($statuts) !!};

        // Initialiser les tableaux de données pour les graphiques
        const labels = [];
        const data = [];

        // Préparer les données pour le graphique
        statuts.forEach(statut => {
            labels.push(statut.libelle);
            // Calculer le coût total pour chaque statut
            const coutTotal = projets.reduce((total, projet) => {
                if (projet.code_statut_projet === statut.code) {
                    return total + projet.cout_projet;
                }
                return total;
            }, 0);
            data.push(coutTotal);
        });

        // Créer le graphique
        const ctx = document.getElementById('myChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Coût total par statut',
                    data: data,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endsection
