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
                        <form id="filterForm">
                        <!-- Sélecteur pour le sous-domaine -->
                        <div class="row align-items-end">
                            <div class="col-3">
                                <label for="domaine">Domaine :</label>
                                <select id="domaine" name="domaine" class="form-control" required>
                                    <option value="">Selectionner domaine</option>
                                    @foreach($Domaines as $Domaine)
                                        <option value="{{ $Domaine->code }}">{{ $Domaine->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-3">
                                <label for="sous_domaine">Sous-Domaine :</label>
                                <select id="sous_domaine" name="sous_domaine" class="form-control" required>
                                    <option value="">Sélectionner un sous-domaine</option>
                                </select>
                            </div>

                            <div class="col-3">
                                <label for="famille">Famille Infrastructure</label>
                                <select name="famille" id="famille" class="form-select">
                                    <option value="">Sélectionner une famille</option>
                                </select>
                            </div>

                            <div class="col-1">
                                <label for="year">Année :</label>
                                <select id="year" name="year" class="form-control" required>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-2">
                                <label for="etablissement">Établissement</label><br>
                                <input type="radio" name="etablissement" value="sante" id="sante">
                                <label for="sante">Santé</label><br>
                                <input type="radio" name="etablissement" value="scolaire" id="scolaire">
                                <label for="scolaire">Scolaire</label>
                            </div>
                        </div>
                        <!-- Bouton Filtrer -->
                         <div class="row">
                            <div class="col-2 text-end">
                                <button id="filter-button" class="btn btn-primary w-100">Filtrer</button>
                            </div>
                         </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        <div id="result-container" class="mt-4">
                            <table class="table table-striped table-bordered display nowrap" id="table1" cellspacing="0" style="width: 100%">
                                <thead id="table-header">
                                    <!-- Les en-têtes seront insérés ici via JavaScript -->
                                </thead>
                                <tbody id="table-body">
                                    <!-- Les données seront insérées ici via JavaScript -->
                                </tbody>
                            </table>
                            <h2>Résultats</h2>
                            <div id="results"></div>
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
/*$(document).ready(function() {
    $('#filter-button').on('click', function() {
        var sousDomaine = $('#sous_domaine').val();
        var year = $('#year').val();
        var ecranId = "{{ $ecran->id }}"; // Récupération de l'ID de l'écran dynamique

        // Vérifier si les champs obligatoires sont vides avant l'envoi de la requête
        if (!sousDomaine || !year || !ecranId) {
            alert('Veuillez sélectionner tous les champs obligatoires.');
            return;
        }

         // Ajouter un log pour l'ID de l'écran

        // Envoi de la requête AJAX
        $.ajax({
            url: `{{ route("filterAnnexe") }}`, // Correct usage of template literals
            type: 'GET', // Changer à GET
            dataType: 'json',
            headers: {
                'Accept': 'application/json'
            },
            data: {
                sous_domaine: sousDomaine,
                year: year,
                ecran_id: ecranId
            },
            success: function(response) {
                console.log('Réponse du serveur', response);
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

                        // Convertir les colonnes en tableau
                        var columns = Object.values(resultat.columns);

                        // Utiliser forEach sur les colonnes converties en tableau
                        columns.forEach(function(columnName) {
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

                        // Convertir les colonnes en tableau pour itérer
                        var columns = Object.values(resultat.columns);

                        resultat.data.forEach(function(dataRow) {
                            var row = '<tr>';
                            row += `<td>${rowIndex++}</td>`;
                            row += `<td>${dataRow['Districts'] || ''}</td>`;
                            row += `<td>${dataRow['Régions'] || ''}</td>`;
                            row += `<td>${dataRow['Départements'] || ''}</td>`;
                            row += `<td>${dataRow['Sous-préfectures/Communes'] || ''}</td>`;

                            columns.forEach(function(columnName) {
                                row += '<td>' + (dataRow[columnName] ?? '') + '</td>';
                            });

                            row += `<td>${dataRow['Nb de ménages desservis'] || ''}</td>`;
                            row += `<td>${dataRow['Coût en F CFA (XOF)'] || ''}</td>`;
                            row += '</tr>';
                            $('#table-body').append(row);
                        });
                    }
                }

                // Détruire et réinitialiser DataTable
                if ($.fn.DataTable.isDataTable('#table1')) {
                    $('#table1').DataTable().clear().destroy();
                }
                initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Annexe 3: Fiche de collecte');
            },

            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                alert('Erreur: ' + xhr.responseText);
            }
        });

    });
});
*/

</script>

<script>

    document.addEventListener('DOMContentLoaded', function () {
        const domaineSelect = document.getElementById('domaine');
        const sousDomaineSelect = document.getElementById('sous_domaine');

        domaineSelect.addEventListener('change', function () {
            const domaineCode = domaineSelect.value;
            console.log('Code Domaine sélectionné :', domaineCode);

            // Appel AJAX pour récupérer les sous-domaines
            $.ajax({
                url: '/admin/get-sous-domaines', // Route pour récupérer les sous-domaines
                method: 'GET',
                data: { domaine: domaineCode },
                success: function (response) {
                    // Mettre à jour le select des sous-domaines avec les nouvelles données
                    sousDomaineSelect.innerHTML = '<option value="">Sélectionner un sous-domaine</option>';
                    response.sousDomaines.forEach(function (sousDomaine) {
                        const option = document.createElement('option');
                        option.value = sousDomaine.code;
                        option.textContent = sousDomaine.libelle;
                        sousDomaineSelect.appendChild(option);
                    });
                },
                error: function (error) {
                    console.error('Erreur lors de la récupération des sous-domaines :', error);
                }
            });
        });

        sousDomaineSelect.addEventListener('change', function () {
            const sousDomaineCode = sousDomaineSelect.value;

            // Appel AJAX pour récupérer les familles d'infrastructure
            $.ajax({
                url: '/admin/get-familles',
                method: 'GET',
                data: { sous_domaine: sousDomaineCode },
                success: function (response) {
                    const familleSelect = document.getElementById('famille');
                    familleSelect.innerHTML = '<option value="">Sélectionner une famille</option>'; // Ajouter une option vide par défaut

                    response.familles.forEach(function (famille) {
                        const option = document.createElement('option');
                        option.value = famille.famille_code; // Utiliser famille_code pour être cohérent avec les noms des colonnes
                        option.textContent = famille.nom_famille;
                        familleSelect.appendChild(option);
                    });
                },
                error: function (error) {
                    console.error('Erreur lors de la récupération des familles :', error);
                }
            });
        });

    });
</script>
<script>
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Empêcher le comportement par défaut du formulaire

    // Récupérer les données du formulaire
    var sousDomaine = document.getElementById('sous_domaine').value;
    var year = document.getElementById('year').value;
    var ecranId = document.getElementById('ecran_id').value;
    var famille = document.getElementById('famille').value;

    if (!famille) {
        alert("Veuillez sélectionner une famille.");
        return; // Arrêter la soumission si 'famille' est vide
    }

    // Préparer les données pour la requête AJAX
    var formData = {
        _token: '{{ csrf_token() }}',
        sous_domaine: sousDomaine,
        year: year,
        ecran_id: ecranId,
        famille: famille
    };

    // Requête AJAX vers le contrôleur pour récupérer les données JSON
    fetch('{{ route("filterAnnexe") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': formData._token
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        var resultsContainer = document.getElementById('results');
        resultsContainer.innerHTML = ''; // Vider les résultats précédents

        if (data.status === 'success') {
            data.resultats.forEach(result => {
                var caracteristique = result.caracteristique || {};
                var sousTable = result.sous_table || 'Non défini';
                var sousTableData = result.sous_table_data || [];

                // Créer un conteneur pour chaque caractéristique
                var resultDiv = document.createElement('div');
                resultDiv.innerHTML = `<h3>Caractéristique: ${caracteristique.CodeCaractFamille || 'Non défini'}</h3>
                                       <p>Sous-table: ${sousTable}</p>`;

                // Créer un tableau pour chaque type de sous-table
                var table = document.createElement('table');
                table.className = 'table table-striped table-bordered display nowrap';
                table.id = 'table1'; // Ajouter l'ID
                table.style.width = '100%';

                // Créer l'en-tête du tableau en fonction de la sous-table
                var thead = document.createElement('thead');
                var headerRow = document.createElement('tr');

                // Gestion des différentes sous-tables
                if (sousTable === 'Unité de traitement') {
                    headerRow.innerHTML = `
                        <th>Nature</th>
                        <th>Unité</th>
                        <th>Débit Capacité</th>
                    `;
                } else if (sousTable === 'Réservoir') {
                    headerRow.innerHTML = `
                        <th>Type de Captage</th>
                        <th>Nature</th>
                        <th>Stockage</th>
                        <th>Capacité</th>
                    `;
                } else if (sousTable === 'Réseau de collecte et de transport') {
                    headerRow.innerHTML = `
                        <th>Réseaux</th>
                        <th>Nature</th>
                        <th>Ouvrage</th>
                        <th>Classe</th>
                        <th>Linéraire</th>
                    `;
                } else if (sousTable === "Ouvrage de captage d'eau") {
                    headerRow.innerHTML = `
                        <th>Type de Captage</th>
                        <th>Nature</th>
                        <th>Débit Capacité</th>
                        <th>Profondeur</th>
                    `;
                } else if (sousTable === "Ouvrage d'assainissement") {
                    headerRow.innerHTML = `
                        <th>Ouvrage</th>
                        <th>Nature</th>
                        <th>Capacité</th>
                    `;
                } else if (sousTable === 'Instrumentation') {
                    headerRow.innerHTML = `
                        <th>Instrument</th>
                        <th>Nature</th>
                        <th>Nombre</th>
                    `;
                }
                thead.appendChild(headerRow);
                table.appendChild(thead);

                // Créer le corps du tableau
                var tbody = document.createElement('tbody');
                sousTableData.forEach(data => {
                    var row = document.createElement('tr');
                    if (sousTable === 'Unité de traitement') {
                        row.innerHTML = `
                            <td>${data.nature || 'Non spécifié'}</td>
                            <td>${data.unite || 'Non spécifié'}</td>
                            <td>${data.debitCapacite || 'Non spécifié'}</td>
                        `;
                    } else if (sousTable === 'Réservoir') {
                        row.innerHTML = `
                            <td>${data.captage || 'Non spécifié'}</td>
                            <td>${data.nature || 'Non spécifié'}</td>
                            <td>${data.Stockage || 'Non spécifié'}</td>
                            <td>${data.capacite || 'Non spécifié'}</td>
                        `;
                    } else if (sousTable === 'Réseau de collecte et de transport') {
                        row.innerHTML = `
                            <td>${data.Reseaux || 'Non spécifié'}</td>
                            <td>${data.nature || 'Non spécifié'}</td>
                            <td>${data.ouvrage || 'Non spécifié'}</td>
                            <td>${data.classe || 'Non spécifié'}</td>
                            <td>${data.lineaire || 'Non spécifié'}</td>
                        `;
                    } else if (sousTable === "Ouvrage de captage d'eau") {
                        row.innerHTML = `
                            <td>${data.type_captage.libelle || 'Non spécifié'}</td>
                            <td>${data.nature_travaux.libelle || 'Non spécifié'}</td>
                            <td>${data.debitCapacite || 'Non spécifié'}</td>
                            <td>${data.profondeur || 'Non spécifié'}</td>
                        `;
                    } else if (sousTable === "Ouvrage d'assainissement") {
                        row.innerHTML = `
                            <td>${data.ouvrage || 'Non spécifié'}</td>
                            <td>${data.nature || 'Non spécifié'}</td>
                            <td>${data.capacite || 'Non spécifié'}</td>
                        `;
                    } else if (sousTable === 'Instrumentation') {
                        row.innerHTML = `
                            <td>${data.instrument || 'Non spécifié'}</td>
                            <td>${data.nature || 'Non spécifié'}</td>
                            <td>${data.nombre || 'Non spécifié'}</td>
                        `;
                    }
                    tbody.appendChild(row);
                });

                table.appendChild(tbody);
                resultDiv.appendChild(table);
                resultsContainer.appendChild(resultDiv);

                // Initialiser DataTables après avoir ajouté le tableau au DOM
                $('#' + table.id).DataTable({
                    paging: true,
                    searching: true,
                    info: true,
                    responsive: true
                });
            });
        } else {
            resultsContainer.innerHTML = '<p>Aucun résultat trouvé.</p>';
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        document.getElementById('results').innerHTML = '<p>Une erreur est survenue lors de la récupération des données.</p>';
    });
});

// Initialisation de DataTables après l'ajout du tableau
$(document).ready(function() {
    $('#table1').DataTable();
});
$(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Annexe3');
    });

</script>

@endsection
