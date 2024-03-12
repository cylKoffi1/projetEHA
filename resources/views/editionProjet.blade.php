@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
    $('#alertMessage').text("{{ session('success') }}");
    $('#alertModal').modal('show');
</script>
@endif

<style>
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 6px;
        font-size: 80%;
        color: #dc3545;
        /* Couleur du texte pour les messages d'erreur */
    }
</style>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-12">
                <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;">
                    <span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span>
                </li>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="">Edition</a></li>
                    </ol>
                </nav>
                <div class="row">
                    <script>
                        // Function to get the current date in a user-friendly format
                        function getCurrentDate() {
                            var currentDate = new Date();
                            return currentDate.toLocaleString(); // You can customize this for specific formatting
                        }

                        // Function to update the date element
                        function updateDate() {
                            var dateElement = document.getElementById('date-now');
                            if (dateElement) {
                                dateElement.textContent = getCurrentDate();
                            }
                        }

                        // Set an initial date
                        updateDate();

                        // Update the date every second
                        setInterval(updateDate, 1000);

                        // Add an event listener for beforeprint to update the date before printing
                        window.addEventListener('beforeprint', updateDate);
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="row-12">
        <div class="col-4">
            <label for="tableType" class="form-label">Sélectionner l'édition :</label>
        </div>
        <div class="col-5">
            <select id="tableType" class="form-select">
                <option value=""></option>
                <option value="action_beneficiaires_projet">Etat des bénéficiaires par actions à mener</option>
                <option value="projet_agence">Etat des agences</option>
                <option value="projet_action_a_mener">Etat des actions à mener</option>
                <option value="ministere_projet">Etat des ministères</option>
                <option value="projet_chef_projet">Etat des chefs de projet</option>
                <option value="bailleur_projet">Etat des bailleurs</option>
            </select>
        </div>
    </div>
    <br>

    <div id="cardContainer" class="row match-height"></div>

    <table id="table1" style="width: 100%;">

    </table>
</section>

<script>
    $(document).ready(function() {

        // Définissez par défaut le type sur "projet_agence"
        var defaultType = 'projet_agence';

        // Exécutez la logique lors du chargement initial
        loadTable(defaultType);

        $('#tableType').change(function() {
            var type = $(this).val();

            // Chargez le tableau en fonction du type sélectionné
            loadTable(type);
        });
        var now = new Date();
        var dateTime = now.toLocaleString("fr-FR", {
            day: "numeric",
            month: "long",
            year: "numeric",
            hour: "numeric",
            minute: "numeric",
        });
        var imagePath='{{ asset("armoiries/$pay->armoirie") }}';
        var userName = "{{ auth()->user()->personnel->nom }}" +" " + "{{ auth()->user()->personnel->prenom }}";
        // Fonction pour charger le tableau en fonction du type
        function loadTable(type) {
            $.ajax({
                url: '/projet/getTable',
                type: 'GET',
                data: { type: type },
                success: function(data) {
                    // Remplacez le contenu de la table avec les nouvelles données
                    var tableContainer = $('#cardContainer');
                    tableContainer.empty(); // Clear the existing content

                    // Ajoutez le conteneur de la carte
                    var cardContainer = $('<div class="col-12"><div class="card"><div class="card-header"></div><div class="card-content"><div class="card-body"><table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1"></table></div></div></div></div>');
                    cardContainer.appendTo(tableContainer);

                    // Ajoutez le titre de la carte
                    var cardHeader = cardContainer.find('.card-header');
                    cardHeader.append('<div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">' +
                        '<h5 class="card-title">Liste des ' + type.replace('_', ' ') + '</h5>' +
                        '@if (count($errors) > 0)<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif' +
                        '</div><div style="text-align: center;"></div>');

                    // Ajoutez DataTable avec les boutons
                    var dataTable = cardContainer.find('#table1');
                    dataTable.DataTable({
                        data: data,
                        columns: Object.keys(data[0]).map(function(key) {
                            return { data: key, title: key };
                        }),
                        language: {
                            processing: "Traitement en cours...",
                            search: "",
                            searchPlaceholder: "Rechercher",
                            lengthMenu: "Afficher _MENU_ lignes",
                            info: "Affichage de l'&eacute;lement _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
                            infoEmpty:
                                "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
                            infoFiltered:
                                "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
                            infoPostFix: "",
                            loadingRecords: "Chargement en cours...",
                            zeroRecords: "Aucun &eacute;l&eacute;ment &agrave; afficher",
                            emptyTable: "Aucune donnée disponible dans le tableau",
                            paginate: {
                                first: "Premier",
                                previous: "Pr&eacute;c&eacute;dent",
                                next: "Suivant",
                                last: "Dernier",
                            },
                            aria: {
                                sortAscending:
                                    ": activer pour trier la colonne par ordre croissant",
                                sortDescending:
                                    ": activer pour trier la colonne par ordre décroissant",
                            },
                        },
                        dom: 'Bfrtip', // Ajoutez cette option pour activer les boutons
                        lengthMenu: [
                            [10, 25, 50, -1],
                            ["10", "25", "50", "Tout"],
                        ],
                        buttons: [
                            {
                                extend: "pageLength",
                                text(text) {
                                    return "Afficher les lignes";
                                },
                            },
                            {
                                extend: 'excelHtml5',
                                text: 'Export CSV',
                                title: 'Liste des ' + type.replace('_', ' '),

                            },
                            {
                                extend: 'print',
                                text: 'Imprimer',
                                footer: true,
                                title: ' ',

                                customize: function (win) {

                                    // Récupérer le nombre de colonnes
                                    var numColumns = $("#table1")
                                        .DataTable()
                                        .columns()
                                        .header().length;

                                    // Générer dynamiquement l'en-tête
                                    var header =
                                        "<tr><th colspan='" + numColumns + "'>" +
                                        "<div class='container'>" +
                                        "<div class='row'>" +
                                        "<div class='col text-left'>" +
                                        "<img src='" + imagePath + "' style='width: 70px; height: 50px; border-radius: 50px;' alt='Logo'>" +
                                        "</div>" +
                                        "<div class='col text-right'>" +
                                        "<h>Impression le </h>" +
                                        dateTime +
                                        "</div>" +
                                        "</div>" +

                                        "<div class='row'>" +
                                        "<div class='col text-center'>" +
                                        "<h3>Liste des " + type.replace('_', ' ') + "</h3>" +
                                        "</div>" +
                                        "</div>" +

                                        "<div class='row'>" +
                                        "<div class='col text-left'>" +
                                        "<p>GERAC-EHA</p>" +
                                        "</div>" +
                                        "<div class='col text-right'>" +
                                        "<p>Imprimé par: " + userName + "</p>" +
                                        "</div>" +
                                        "</div>" +
                                        "</div></th></tr>";

                                    // Ajouter l'en-tête personnalisé
                                    $(win.document.body).find('thead').prepend(header);



                                },


                            },

                        ]

                    });
                }
            });
        }

    });
</script>
@endsection
