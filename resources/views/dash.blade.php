@extends('layouts.app')

@section('content')
{{-- @include('layouts.header') --}}
<style>
    .chart-container {
        position: relative;
        width: 100%;
        height: 400px;
    }

    .chart-legend {
        margin-top: 20px;
        text-align: center;
    }

</style>

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
            <!-- Section pour le nombre de projets -->
            <section class="row">
                <div class="page-content">
                    <section class="row">
                        <div class="col-12">

                            <section class="section">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <di class="card-body">
                                                        <center><h6>Nombre de Projets par Année</h6></center>
                                                        <div class="col-2">
                                                            <label for="chartTypeSelect2">Type de graph</label>
                                                            <select id="chartTypeSelect2" class="form-control" onchange="updateChart2()">
                                                                <option value="bar">Bar</option>
                                                                <option value="line">Line</option>
                                                                <option value="pie">Pie</option>
                                                                <option value="doughnut">Doughnut</option>
                                                                <option value="radar">Radar</option>
                                                                <option value="polarArea">Polar Area</option>
                                                                <option value="bubble">Bubble</option>
                                                                <option value="scatter">Scatter</option>
                                                            </select>
                                                        </div>
                                                        <div class="col text-end">
                                                            <button class="btn btn-danger me-2" onclick="downloadPDF2()">
                                                                <i class="fa fa-file-pdf-o"></i> PDF
                                                            </button>
                                                            <button class="btn btn-success me-2" onclick="downloadExcel2()">
                                                                <i class="fa fa-file-excel-o"></i> Excel
                                                            </button>
                                                        </div>
                                                        <canvas id="projetsChart"></canvas>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </section>
                </div>
            </section>
            <!-- Section pour les autres types -->
            <section class="row">
                <div class="page-content">
                    <section class="row">
                        <div class="col-12">

                            <section class="section">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="card-body">
                                                <center><h6>Autres données par Année</h6></center>
                                                    <div class="row">
                                                        <div class="col-2">
                                                            <label for="">Type de données</label>
                                                            <select id="typeSelect" class="form-control" onchange="updateChart()">
                                                                <option value="domaine">Domaine</option>
                                                                <option value="sous_domaine">Sous Domaine</option>
                                                                <option value="district">District</option>
                                                                <option value="region">Région</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-2">
                                                            <label for="">Type de graph</label>
                                                            <select id="chartTypeSelect" class="form-control" onchange="updateChart()">
                                                                <option value="bar">Bar</option>
                                                                <option value="line">Line</option>
                                                                <option value="pie">Pie</option>
                                                                <option value="doughnut">Doughnut</option>
                                                                <option value="radar">Radar</option>
                                                                <option value="polarArea">Polar Area</option>
                                                                <option value="bubble">Bubble</option>
                                                                <option value="scatter">Scatter</option>

                                                            </select>
                                                        </div>
                                                        <div class="col text-end">
                                                            <button class="btn btn-danger me-2" onclick="downloadPDF()">
                                                                <i class="fa fa-file-pdf-o"></i> PDF
                                                            </button>
                                                            <button class="btn btn-success me-2" onclick="downloadExcel()">
                                                                <i class="fa fa-file-excel-o"></i> Excel
                                                            </button>
                                                        </div>

                                                    </div><br>



                                                    <div class="chart-container">
                                                        <canvas id="myChart" width="400" height="400"></canvas>
                                                        <div id="chartLegend" class="chart-legend"></div>
                                                    </div>


                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                    </section>
                </div>


            </section>
    </section>
</div>

<footer>
    <div class="footer clearfix mb-0 text-muted">
        <div class="float-start">
            <p>2023 &copy; EHA</p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@0.5.0-beta4/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.3.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.6.0/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<!-- Nombre de projet -->
<script>
    function createProjetsChart(data, chartType) {
        const ctx = document.getElementById('projetsChart').getContext('2d');
        new Chart(ctx, {
            type: chartType,
            data: {
                labels: data.years,
                datasets: [{
                    label: 'Nombre de Projets',
                    data: data.counts,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    function updateChart2() {
        const chartType2 = document.getElementById('chartTypeSelect2').value;
        const dataProjets = @json($dataProjets);

        // Trier les données par année
        dataProjets.sort((a, b) => a.year - b.year);

        const years = dataProjets.map(item => item.year);
        const counts = dataProjets.map(item => item.count);

        // Supprimer le graphique existant s'il y en a un
        if (window.chartInstance2) {
            window.chartInstance2.destroy();
        }

        if (chartType2) {
            const ctx = document.getElementById('projetsChart').getContext('2d');
            window.chartInstance2 = new Chart(ctx, {
                type: chartType2,
                data: {
                    labels: years,
                    datasets: [{
                        label: 'Nombre de Projets',
                        data: counts,
                        backgroundColor: getRandomColor2(),
                        borderColor: getRandomColor2(),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }

    function getRandomColor2() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateChart2();
    });

    async function downloadPDF2() {
        const { jsPDF } = window.jspdf;
        const myChart = document.getElementById('projetsChart');
        const imgData = myChart.toDataURL('image/png');

        // Crée une instance jsPDF avec orientation paysage
        const pdf = new jsPDF('l', 'mm', 'a4'); // 'l' pour landscape, 'mm' pour millimètres, 'a4' pour le format A4
        pdf.addImage(imgData, 'PNG', 10, 10, 280, 160); // Ajustez les dimensions selon vos besoins
        pdf.save('chart.pdf');
    }

    function downloadExcel2() {
        const worksheet = XLSX.utils.json_to_sheet(window.chartInstance2.data.datasets[0].data);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Sheet1');
        XLSX.writeFile(workbook, 'chart.xlsx');
    }

    // Ajouter des gestionnaires d'événements pour détecter les changements dans les sélections
    document.getElementById('chartTypeSelect2').addEventListener('change', updateChart2);

</script>

<!--Les autres type de données -->
<script>
    const years = @json($years);
    const dataByType = @json($dataByType);

    let chartInstance;

    function getData(type) {
        // Trier les années dans l'ordre croissant
        const sortedYears = [...years].sort((a, b) => a - b);
        const labels = sortedYears;
        let datasets = [];

        // Logique pour d'autres types
        const types = [...new Set(dataByType[type].map(item => item.type))];

        types.forEach(typeValue => {
            const data = sortedYears.map(year => {
                const found = dataByType[type].find(item => item.year == year && item.type == typeValue);
                return found ? found.total : 0;
            });

            datasets.push({
                label: typeValue,
                data: data,
                backgroundColor: getRandomColor(),
                borderColor: getRandomColor(),
                borderWidth: 1
            });
        });

        return { labels, datasets };
    }

    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    function updateChart() {
        const selectedType = document.getElementById('typeSelect').value;
        const chartType = document.getElementById('chartTypeSelect').value;
        const data = getData(selectedType);

        if (chartInstance) {
            chartInstance.destroy();
        }

        const ctx = document.getElementById('myChart').getContext('2d');
        chartInstance = new Chart(ctx, {
            type: chartType === 'horizontalBar' ? 'bar' : chartType,
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: chartType === 'horizontalBar' ? 'y' : 'x', // Pour barres horizontales
            }
        });
    }

    async function downloadPDF() {
        const { jsPDF } = window.jspdf;
        const myChart = document.getElementById('myChart');
        const imgData = myChart.toDataURL('image/png');

        // Crée une instance jsPDF avec orientation paysage
        const pdf = new jsPDF('l', 'mm', 'a4'); // 'l' pour landscape, 'mm' pour millimètres, 'a4' pour le format A4
        pdf.addImage(imgData, 'PNG', 10, 10, 280, 160); // Ajustez les dimensions selon vos besoins
        pdf.save('chart.pdf');
    }

    function downloadExcel() {
        const worksheet = XLSX.utils.json_to_sheet(chartInstance.data.datasets[0].data);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Sheet1');
        XLSX.writeFile(workbook, 'chart.xlsx');
    }

    // Ajouter des gestionnaires d'événements pour détecter les changements dans les sélections
    document.getElementById('typeSelect').addEventListener('change', updateChart);
    document.getElementById('chartTypeSelect').addEventListener('change', updateChart);

    // Initialiser le graphique lors du chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        updateChart();
    });
</script>

    @endsection
