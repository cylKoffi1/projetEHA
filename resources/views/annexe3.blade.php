@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
    alert("{{ session('success') }}");

</script>
@endif

<style>


</style>

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
                            <li class="breadcrumb-item"><a href="">Editions</a></li>
                            <li class="breadcrumb-item"><a href="">Annexe 3</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Fiche de collecte</li>

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
    <div class="row match-height">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                        <h5 class="card-title">Annexe 3: Formulaire de collecte de données</h5>
                    </div>
                    <div style="text-align: center;">
                        <h5 class="card-title"></h5>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <!-- Sélecteur pour le sous-domaine -->
                        <div class="row align-items-end">
                            <div class="col-4">
                                <label for="sous_domaine">Sous-Domaine :</label>
                                <select id="sous_domaine" name="sous_domaine" class="form-control" required>
                                    @foreach($sousDomaines as $sousDomaine)
                                        <option value="{{ $sousDomaine->code }}">{{ $sousDomaine->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Sélecteur pour l'année -->
                            <div class="col-2">
                                <label for="year">Année :</label>
                                <select id="year" name="year" class="form-control" required>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Bouton Filtrer sur la même ligne -->
                            <div class="col-2">
                                <button id="filter-button" class="btn btn-primary w-100">Filtrer</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        <div id="result-container" class="mt-4">
                            <table class="table table-striped table-bordered" id="table1" cellspacing="0" style="width: 100%">
                                <thead id="table-header">
                                    <!-- Les en-têtes seront insérés ici via JavaScript -->
                                </thead>
                                <tbody id="table-body">
                                    <!-- Les données seront insérées ici via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#filter-button').on('click', function() {
        var sousDomaine = $('#sous_domaine').val();
        var year = $('#year').val();
        var ecranId = "{{ $ecran->id }}"; // Récupération de l'ID de l'écran dynamique

        // Vérifier si les champs obligatoires sont vides avant l'envoi de la requête
        if (!sousDomaine || !year || !ecranId) {
            alert('Veuillez sélectionner tous les champs obligatoires.');
            return;
        }

        console.log("Sous-domaine: ", sousDomaine); // Ajouter un log pour le sous-domaine
        console.log("Année: ", year); // Ajouter un log pour l'année
        console.log("Ecran ID: ", ecranId); // Ajouter un log pour l'ID de l'écran

        // Envoi de la requête AJAX
        $.ajax({
            url: `{{ route("filterAnnexe") }}`, // Correct usage of template literals
            type: 'GET', // Changer à GET
            dataType: 'json',
            data: {
                sous_domaine: sousDomaine,
                year: year,
                ecran_id: ecranId
            },
            success: function(response) {
                console.log(response);

                if (response.error) {
                    alert(response.error);
                    return;
                }

                // Vider les anciens résultats
                $('#table-header').empty();
                $('#table-body').empty();

                // Construire les en-têtes du tableau
                var headerRow1 = '<tr>';
                var headerRow2 = '<tr>';
                headerRow1 += `
                    <th rowspan="2">N°</th>
                    <th rowspan="2">Districts</th>
                    <th rowspan="2">Régions</th>
                    <th rowspan="2">Départements</th>
                    <th rowspan="2">Sous-préfectures/Communes</th>
                `;

                response.headerConfig.forEach(function(header) {
                    headerRow1 += '<th colspan="' + header.colspan + '">' + header.name + '</th>';
                });

                headerRow1 += `
                    <th rowspan="2">Nb de ménages desservis</th>
                    <th rowspan="2">Coût en F CFA (XOF)</th>
                `;
                headerRow1 += '</tr>';

                // Ajouter les en-têtes des colonnes dynamiques
                headerRow2 = '<tr>';
                for (var key in response.resultats) {
                    if (response.resultats.hasOwnProperty(key)) {
                        var resultat = response.resultats[key];
                        resultat.columns.forEach(function(columnName) {
                            headerRow2 += '<th>' + columnName + '</th>';
                        });
                    }
                }
                headerRow2 += '</tr>';

                $('#table-header').append(headerRow1);
                $('#table-header').append(headerRow2);

                // Remplir le corps du tableau
                var rowIndex = 1;
                for (var key in response.resultats) {
                    if (response.resultats.hasOwnProperty(key)) {
                        var resultat = response.resultats[key];
                        resultat.data.forEach(function(dataRow) {
                            var row = '<tr>';
                            row += `<td>${rowIndex++}</td>`;
                            row += `<td>${dataRow['Districts'] || ''}</td>`;
                            row += `<td>${dataRow['Régions'] || ''}</td>`;
                            row += `<td>${dataRow['Départements'] || ''}</td>`;
                            row += `<td>${dataRow['Sous-préfectures/Communes'] || ''}</td>`;
                            resultat.columns.forEach(function(columnName) {
                                row += '<td>' + (dataRow[columnName] ?? '') + '</td>';
                            });
                            row += `<td>${dataRow['Nb de ménages desservis'] || ''}</td>`;
                            row += `<td>${dataRow['Coût en F CFA (XOF)'] || ''}</td>`;
                            row += '</tr>';
                            $('#table-body').append(row);
                        });
                    }
                }

                if ($.fn.DataTable.isDataTable('#table1')) {
                    $('#table1').DataTable().clear().destroy();
                }
                $('#table1').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            title: 'Annexe 3',
                            messageTop: '{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}'
                        }
                    ]
                });
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status,
                    headers: xhr.getAllResponseHeaders()
                });

                let errorMessage = 'Une erreur inconnue est survenue.';
                if (xhr.status === 404) {
                    errorMessage = 'Page non trouvée (404). Vérifiez l\'URL.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Erreur interne du serveur (500).';
                } else if (xhr.responseJSON) {
                    errorMessage = xhr.responseJSON.error || xhr.responseJSON.message || errorMessage;
                } else {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.error || response.message || errorMessage;
                    } catch (e) {
                        errorMessage = xhr.responseText || errorMessage;
                    }
                }
                alert('Erreur: ' + errorMessage);
            }
        });
    });
});


</script>


@endsection
