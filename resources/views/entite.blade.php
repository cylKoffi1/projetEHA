<!doctype html>
    <html class="no-js" lang="en">

    <head>
        <!-- meta data -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
        <style>
            /* Custom styles for DataTables */

            table {
                width: 100%;
                border-collapse: collapse;
                color: black;font-family: 'Nunito';
            }
            th, td {
                padding: 8px;
                border: 1px solid #ddd;
                text-align: center;
                color: black;
            }
            th {
                background-color: #f2f2f2;
                color: black;
            }
            tr{
                background-color: #A2D5C6;
            }
            .dataTables_filter input{
                color: black;
                background-color: white;
            }
            tr:nth-child(even) {
                background-color: #A2D5C6 !important;
            }
            tr:nth-child(odd) {
                background-color: #ffffff !important;
            }
            label {
                color: black;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button,
            .dataTables_wrapper .dataTables_paginate .previous,
            .dataTables_wrapper .dataTables_paginate .next,
            .dataTables_wrapper .dataTables_paginate .last,
            .dataTables_wrapper .dataTables_paginate .first {
                background-color: white !important;
                color: black !important;
                border: 1px solid #ddd;
                padding: 5px 10px;
                margin: 2px;
                border-radius: 5px;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
            .dataTables_wrapper .dataTables_paginate .previous:hover,
            .dataTables_wrapper .dataTables_paginate .next:hover,
            .dataTables_wrapper .dataTables_paginate .last:hover,
            .dataTables_wrapper .dataTables_paginate .first:hover {
                background-color: #f2f2f2 !important;
                color: black !important;
            }
            .dataTables_wrapper .dataTables_filter input {
                border: 1px solid #ddd;
                padding: 5px;
                margin-left: 5px;
            }
            .dataTables_wrapper .dataTables_length select {
                border: 1px solid #ddd;
                padding: 5px;
                margin-left: 5px;
            }
            .dataTables_wrapper .dataTables_info {
                color: black;
                margin-top: 10px;
            }
            .dataTables_wrapper .dataTables_paginate {
                margin-top: 10px;
            }

        </style>
        @include('layouts.lurl')
        <link rel="stylesheet" href="{{ asset('leaflet/leaflet.css')}}" />
    </head>


    @include('layouts.menu')

    <body>
    <div class="card-header">
                <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                </div>
                <div style="text-align: center; font-size: 15px;">
                   <h5 class="card-title"> {{ $geoName }} : @foreach ($domainess as $domaines) {{ $domaines->libelle }} @endforeach</h5>
                </div>
            </div>
            <div style="margin: 20px;">
        <table class="table" cellspacing="0" id="table1" style=" font-size: 12px;   ">
            <thead>
                <tr>
                    <th>Code Projet</th>
                    <th>Domaine</th>
                    <th>District</th>
                    <th>Région</th>
                    <th>Date de Début Prévue</th>
                    <th>Date de Fin Prévue</th>
                    <th>Coût du Projet</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                <tr>
                    <td>{{ $project->CodeProjet }}</td>
                    <td>{{ $project->domaine->libelle }}</td>
                    <td>{{ $project->district->libelle }}</td>
                    <td>{{ $project->region->libelle }}</td>
                    <td>{{ $project->Date_demarrage_prevue }}</td>
                    <td>{{ $project->date_fin_prevue }}</td>
                    <td style="text-align: right;">{{ number_format($project->cout_projet, 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table><br>
    </div>
<script>
     // Fonction de formatage générique pour les champs de nombre
     function formatNumberColumn(className) {
                var elements = document.getElementsByClassName(className);
                for (var i = 0; i < elements.length; i++) {
                    var element = elements[i];
                    element.textContent = number_format(element.textContent, 0, ' ', ' ');
                }
            }

            // Appliquer le formatage après le chargement du document
            document.addEventListener('DOMContentLoaded', function() {
                formatNumberColumn('formatted-number');
        });
</script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        $('#table1').DataTable({
            "language": {
                "lengthMenu": "Afficher _MENU_ projets",
                "zeroRecords": "Aucun projet trouvé",
                "info": "Affichage de _PAGE_ sur _PAGES_",
                "infoEmpty": "Aucun projet disponible",
                "infoFiltered": "(filtré à partir de _MAX_ projets au total)",
                "search": "Rechercher:",
                "paginate": {
                    "first": "Premier",
                    "last": "Dernier",
                    "next": "Suivant",
                    "previous": "Précédent"
                }
            }
        });
    });
</script>



    </body>
