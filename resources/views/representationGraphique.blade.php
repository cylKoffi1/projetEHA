@extends('layouts.app')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-12">
                <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion financière </h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="">Gestion financière</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Représentation graphique</li>

                    </ol>
                </nav>
                <div class="row">
                    <script>
                        setInterval(function() {
                            document.getElementById('date-now').textContent = getCurrentDate();
                        }, 1000);

                        function getCurrentDate() {
                            // Implémentez la logique pour obtenir la date actuelle au format souhaité
                            var currentDate = new Date();
                            return currentDate.toLocaleString(); // Vous pouvez utiliser une autre méthode pour le formatage
                        }

                    </script>

                </div>
            </div>
        </div>
    </div>
</div>
<div class="page-content">
    <section class="row">
        <div class="col-12">
            <section class="section">

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
                                        xaxis: {
                                            categories: {!! json_encode($categories) !!}
                                        },
                                        yaxis: {
                                            reversed: false,
                                            title: {
                                                text: 'Montant en FCFA'
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
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Dépenses réalisées Secteur
                                    EHA</h4>
                            </div>
                            <div class="card-body">
                                <div id="bar"></div>

                                <div id="chart"></div>

                                <script>
                                    var options = {
                                        chart: {
                                            type: 'bar'
                                        },
                                        series: [{
                                            name: 'DR Total EHA',
                                            data: {!! json_encode($dataTotalEHA) !!}
                                        }, {
                                            name: 'DR Extérieur EHA',
                                            data: {!! json_encode($dataExterieurEHA) !!}
                                        }, {
                                            name: 'DR Trésor CIV EHA',
                                            data: {!! json_encode($dataTresorCIVEHA) !!}
                                        }],
                                        xaxis: {
                                            categories: {!! json_encode($categories) !!}
                                        },
                                        yaxis: {
                                            reversed: false,
                                            title: {
                                                text: 'Montant en FCFA'
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

                                    var chart = new ApexCharts(document.querySelector("#chart"), options);
                                    chart.render();
                                </script>


                                </body>
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
