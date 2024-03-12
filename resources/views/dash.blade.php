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
            <div id="chartContainer"></div>

            <script>
                // Fonction pour mettre à jour le graphique en fonction du type sélectionné
                function updateChart() {
                    // Nettoyer le conteneur du graphique précédent
                    document.getElementById("chartContainer").innerHTML = "";

                    // Récupérer la valeur sélectionnée dans le select
                    var selectedChartType = document.getElementById("chartTypeSelect").value;

                    // Configuration commune aux deux types de graphiques
                    var commonOptions = {
                        xaxis: {
                            categories: {!! json_encode($categories) !!}
                        },
                        yaxis: {
                            reversed: false,
                            title: {
                                text: 'Montant en XOF'
                            }
                        }
                    };

                    // Configurations spécifiques à chaque type de graphique
                    var chartOptions = {};
                    if (selectedChartType === 'bar') {
                        chartOptions = {
                            chart: {
                                type: 'bar'
                            },
                            series: [
                                {
                                    name: 'DP Total EHA',
                                    data: {!! json_encode($totalDepensesPrevue) !!}
                                },
                                {
                                    name: 'DP Extérieur EHA',
                                    data: {!! json_encode($depensesPrevueExt) !!}
                                },
                                {
                                    name: 'DP Trésor CIV EHA',
                                    data: {!! json_encode($depensesPrevueTresor) !!}
                                }
                            ],
                            plotOptions: {
                                            bar: {
                                                dataLabels: {
                                                    position: 'center',
                                                    orientation: 'vertical',
                                                    textAnchor: 'middle',
                                                }
                                            }
                                        }
                        };
                    } else if (selectedChartType === 'area') {

                        chartOptions = {
                            chart: {
                                type: 'area'
                            },
                            series: [
                                {
                                    name: 'DP Total EHA',
                                    data: {!! json_encode($totalDepensesPrevue) !!}
                                },
                                {
                                    name: 'DP Extérieur EHA',
                                    data: {!! json_encode($depensesPrevueExt) !!}
                                },
                                {
                                    name: 'DP Trésor CIV EHA',
                                    data: {!! json_encode($depensesPrevueTresor) !!}
                                }
                            ]
                        };
                    }

                    else if(selectedChartType === 'multi-axis') {
                        chartOptions = {
                chart: {
                    type: 'line',
                },
                series: [
                    {
                        name: 'DP Total EHA',
                        data: {!! json_encode($totalDepensesPrevue) !!}
                    },
                    {
                        name: 'DP Extérieur EHA',
                        data: {!! json_encode($depensesPrevueExt) !!}
                    },
                    {
                        name: 'DP Trésor CIV EHA',
                        data: {!! json_encode($depensesPrevueTresor) !!}
                    }
                ],
                yaxis: [
                    {
                        title: {
                            text: 'Montant DP Total EHA',
                        },
                    },
                    {
                        title: {
                            text: 'Montant DP Extérieur EHA',
                        },
                    },
                    {
                        opposite: true,
                        title: {
                            text: 'Montant DP Trésor CIV EHA',
                        },
                    },
                ],
            };
                        }


                    // Fusionner les options communes et spécifiques
                    var options = { ...commonOptions, ...chartOptions };

                    // Créer et rendre le graphique
                    var chart = new ApexCharts(document.querySelector("#chartContainer"), options);
                    chart.render();
                }

                // Appeler la fonction pour afficher initialement le graphique à barres
                updateChart();
            </script>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



                 <!--debut chart bar
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Dépenses Prévues Secteur
                                    EHA</h4>
                            </div>
                            <div class="card-body">

                                <div id="chart_prevue"></div>
                                <script>
                                    var options = {
                                        chart: {
                                            type: 'bar',

                                        },
                                        series: [
                                             {
                                            name: 'DP Total EHA',
                                            data: {!! json_encode($totalDepensesPrevue) !!}
                                        },
                                        {
                                            name: 'DP Extérieur EHA',
                                            data: {!! json_encode($depensesPrevueExt) !!}
                                        },
                                        {
                                            name: 'DP Trésor CIV EHA',
                                            data: {!! json_encode($depensesPrevueTresor) !!}
                                        }
                                        ],
                                        xaxis: {
                                            categories: {!! json_encode($categories) !!}
                                        },
                                        yaxis: {
                                            reversed: false,
                                            title: {
                                                text: 'Montant en XOF'
                                            }
                                        },
                                        plotOptions: {
                                            bar: {
                                                dataLabels: {
                                                    position: 'center',
                                                    orientation: 'vertical',
                                                    textAnchor: 'middle',
                                                }
                                            }
                                        }
                                    };

                                    var chart = new ApexCharts(document.querySelector("#chart_prevue"), options);
                                    chart.render();
                                </script>


                                </body>
                            </div>
                        </div>
                    </div>
                </div>


                 fin chart bar -->

                 <!-- FIN DES GRAPHES DE PROSPECTION -->



                <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Dépenses réalisées Secteur
                                EHA
                            </h4>
                            <div class="card-body">

                 <select id="chartTypeSelect_dr" onchange="updateChart_dr()">
                    <option value="bar">Graphique à Barres</option>
                    <option value="area">Graphique en Aires</option>
                    <option value="multi-axis">Graphique à Axes Multiples</option>

                </select>

            <!-- Conteneur pour le graphique -->
            <div id="chartContainer_dr"></div>
                 <script>
                function updateChart_dr() {
                    // Nettoyer le conteneur du graphique précédent
                    document.getElementById("chartContainer_dr").innerHTML = "";

                    // Récupérer la valeur sélectionnée dans le select
                    var selectedChartType = document.getElementById("chartTypeSelect_dr").value;

                    // Configuration commune aux deux types de graphiques
                    var commonOptions = {
                        xaxis: {
                            categories: {!! json_encode($categories) !!}
                        },
                        yaxis: {
                            reversed: false,
                            title: {
                                text: 'Montant en XOF'
                            }
                        }
                    };

                    // Configurations spécifiques à chaque type de graphique
                    var chartOptions = {};
                    if (selectedChartType === 'bar') {
                        chartOptions = {
                            chart: {
                                type: 'bar'
                            },
                            series: [
                                {
                                    name: 'DP Total EHA',
                                    data: {!! json_encode($totalDepensesPrevue) !!}
                                },
                                {
                                    name: 'DP Extérieur EHA',
                                    data: {!! json_encode($depensesPrevueExt) !!}
                                },
                                {
                                    name: 'DP Trésor CIV EHA',
                                    data: {!! json_encode($depensesPrevueTresor) !!}
                                }
                            ],
                            plotOptions: {
                                            bar: {
                                                dataLabels: {
                                                    position: 'center',
                                                    orientation: 'vertical',
                                                    textAnchor: 'middle',
                                                }
                                            }
                                        }
                        };
                    } else if (selectedChartType === 'area') {

                        chartOptions = {
                            chart: {
                                type: 'area'
                            },
                            series: [
                                {
                                    name: 'DP Total EHA',
                                    data: {!! json_encode($totalDepensesPrevue) !!}
                                },
                                {
                                    name: 'DP Extérieur EHA',
                                    data: {!! json_encode($depensesPrevueExt) !!}
                                },
                                {
                                    name: 'DP Trésor CIV EHA',
                                    data: {!! json_encode($depensesPrevueTresor) !!}
                                }
                            ]
                        };
                    }

                    else if(selectedChartType === 'multi-axis') {
                        chartOptions = {
                chart: {
                    type: 'line',
                },
                series: [
                    {
                        name: 'DP Total EHA',
                        data: {!! json_encode($dataTotalEHA) !!}
                    },
                    {
                        name: 'DP Extérieur EHA',
                        data: {!! json_encode($dataExterieurEHA) !!}
                    },
                    {
                        name: 'DP Trésor CIV EHA',
                        data: {!! json_encode($dataTresorCIVEHA) !!}
                    }
                ],
                yaxis: [
                    {
                        title: {
                            text: 'Montant DP Total EHA',
                        },
                    },
                    {
                        title: {
                            text: 'Montant DP Extérieur EHA',
                        },
                    },
                    {
                        opposite: true,
                        title: {
                            text: 'Montant DP Trésor CIV EHA',
                        },
                    },
                ],
            };
                        }


                    // Fusionner les options communes et spécifiques
                    var options = { ...commonOptions, ...chartOptions };

                    // Créer et rendre le graphique
                    var chart = new ApexCharts(document.querySelector("#chartContainer_dr"), options);
                    chart.render();
                }

                // Appeler la fonction pour afficher initialement le graphique à barres
                updateChart_dr();
            </script>
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
@endsection
