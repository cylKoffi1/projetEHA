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
                            <table class="table table-striped table-bordered display nowrap" id="table" cellspacing="0" style="width: 100%">
                                <thead id="table-header">
                                    <!-- Les en-têtes seront insérés ici via JavaScript -->
                                </thead>
                                <tbody id="table-body">
                                    <!-- Les données seront insérées ici via JavaScript -->
                                </tbody>
                            </table>
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


    document.addEventListener('DOMContentLoaded', function () {
        const domaineSelect = document.getElementById('domaine');
        const sousDomaineSelect = document.getElementById('sous_domaine');

        domaineSelect.addEventListener('change', function () {
            const domaineCode = domaineSelect.value;
            //console.log('Code Domaine sélectionné :', domaineCode);

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
                    //console.error('Erreur lors de la récupération des sous-domaines :', error);
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
                    //console.error('Erreur lors de la récupération des familles :', error);
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
    // Afficher toutes les données dans la console
    //console.log("Données reçues:", data);

    // Récupération des données et création des tableaux dans le conteneur 'resultsContainer'
    var resultsContainer = document.getElementById('results');
    resultsContainer.innerHTML = ''; // Vider les résultats précédents

    if (data.status === 'success') {
        data.resultats.forEach((result, index) => {
            var caracteristique = result.caracteristique || {};
            var sousTable = result.sous_table || 'Non défini';
            var sousTableData = result.sous_table_data || [];
            var beneficiaires = result.beneficiaires || [];
            var codeProjet = caracteristique.CodeProjet || 'Non défini';

            var resultDiv = document.createElement('div');
            var tableId = 'table' + index;
            var table = document.createElement('table');
            table.className = 'table table-striped table-bordered display nowrap';
            table.setAttribute("cellspacing", "0");
            table.id = tableId;
            table.style.width = '100%';

            var thead = document.createElement('thead');
            var headerRow = document.createElement('tr');
            headerRow.innerHTML = `
                <th>Code projet</th>
                <th>Bénéficiaire</th>
                ${sousTable === 'Unité de traitement' ? '<th>Nature</th><th>Unité</th><th>Débit Capacité</th>' : ''}
                ${sousTable === 'Réservoir' ? '<th>Type de Captage</th><th>Nature</th><th>Stockage</th><th>Capacité</th>' : ''}
                ${sousTable === 'Réseau de collecte et de transport' ? '<th>Réseaux</th><th>Nature</th><th>Ouvrage</th><th>Classe</th><th>Linéaire</th>' : ''}
                ${sousTable === "Ouvrage de captage d'eau" ? '<th>Type de Captage</th><th>Nature</th><th>Débit Capacité</th><th>Profondeur</th>' : ''}
                ${sousTable === "Ouvrage d'assainissement" ? '<th>Ouvrage</th><th>Nature</th><th>Capacité</th>' : ''}
                ${sousTable === 'Instrumentation' ? '<th>Instrument</th><th>Nature</th><th>Nombre</th>' : ''}
            `;
            thead.appendChild(headerRow);
            table.appendChild(thead);

            var tbody = document.createElement('tbody');
            beneficiaires.forEach(beneficiaire => {
                sousTableData.forEach(data => {
                    var row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${codeProjet}</td>
                        <td>${beneficiaire.nom} (${beneficiaire.type})</td>
                        ${sousTable === 'Unité de traitement' ? `<td>${data.nature || 'Non spécifié'}</td><td>${data.unite || 'Non spécifié'}</td><td>${data.debitCapacite || 'Non spécifié'}</td>` : ''}
                        ${sousTable === 'Réservoir' ? `<td>${data.captage || 'Non spécifié'}</td><td>${data.nature || 'Non spécifié'}</td><td>${data.Stockage || 'Non spécifié'}</td><td>${data.capacite || 'Non spécifié'}</td>` : ''}
                        ${sousTable === 'Réseau de collecte et de transport' ? `<td>${data.Reseaux || 'Non spécifié'}</td><td>${data.nature || 'Non spécifié'}</td><td>${data.ouvrage || 'Non spécifié'}</td><td>${data.classe || 'Non spécifié'}</td><td>${data.lineaire || 'Non spécifié'}</td>` : ''}
                        ${sousTable === "Ouvrage de captage d'eau" ? `<td>${data.type_captage.libelle || 'Non spécifié'}</td><td>${data.nature_travaux.libelle || 'Non spécifié'}</td><td>${data.debitCapacite || 'Non spécifié'}</td><td>${data.profondeur || 'Non spécifié'}</td>` : ''}
                        ${sousTable === "Ouvrage d'assainissement" ? `<td>${data.ouvrage || 'Non spécifié'}</td><td>${data.nature || 'Non spécifié'}</td><td>${data.capacite || 'Non spécifié'}</td>` : ''}
                        ${sousTable === 'Instrumentation' ? `<td>${data.instrument || 'Non spécifié'}</td><td>${data.nature || 'Non spécifié'}</td><td>${data.nombre || 'Non spécifié'}</td>` : ''}
                    `;
                    tbody.appendChild(row);
                });
            });
            table.appendChild(tbody);
            resultDiv.appendChild(table);
            resultsContainer.appendChild(resultDiv);

            // Initialisation de DataTables avec les boutons et autres paramètres
            $(`#${tableId}`).DataTable({
                dom: "Bfrtip",
                language: { url: '//cdn.datatables.net/plug-ins/2.1.8/i18n/fr-FR.json' },
                responsive: true,
                paging: true,
                ordering: true,
                info: true,
                scrollX: true,
                buttons: [
                    {
                        extend: 'colvis',
                        columns: ':not(.noVis)',
                        popoverTitle: 'Column visibility selector'
                    },
                    { extend: 'pageLength', text: 'Afficher les lignes' },
                    { extend: 'excelHtml5', text: 'Exporter', title: "Annexe3" },
                    {
                        extend: 'pdfHtml5',
                        text: 'Imprimer',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        filename: 'Annexe3',
                        customize: function(doc) {
                            doc.content[1].table.widths = '*'.repeat(doc.content[1].table.body[0].length).split('');
                            doc.defaultStyle.fontSize = 8;
                            doc['footer'] = function(page, pages) {
                                return {
                                    columns: [{ alignment: 'right', text: ['page ', { text: page.toString() }, ' sur ', { text: pages.toString() }] }],
                                    margin: [10, 0]
                                };
                            };
                        }
                    }
                ]
            });
        });
    } else {
        resultsContainer.innerHTML = '<p>Aucun résultat trouvé.</p>';
    }

})
.catch(error => {
    //console.error('Erreur:', error);
    document.getElementById('results').innerHTML = '<p>Une erreur est survenue lors de la récupération des données.</p>';
});



});



</script>

@endsection
