
@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
    $('#alertMessage').text("{{ session('success') }}");
    $('#alertModal').modal('show');
</script>
@endif
<style>
    .national-row {
        position: sticky;
        top: 0;
        z-index: 2; /* Assurez-vous qu'elle est au-dessus des autres lignes */
        background-color: #fff; /* Assurez-vous que le fond reste blanc */
    }
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 6px;
        font-size: 80%;
        color: #dc3545;
        /* Couleur du texte pour les messages d'erreur */
    }

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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Tableau de bord  </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Financier</a></li>

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
                    <div  style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                        <h5 class="card-title">

                        </h5>

                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div style="text-align: center;">
                       <h5 class="card-title">Tableau de bord financier</h5>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body">
                    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                        <thead>
                            <tr>
                                <th style="width: 5%">Code</th>
                                <th style="width: 7%">Statut</th>
                                <th style="width: 8%">District</th>
                                <th style="width: 8%">Région</th>
                                <th style="width: 10%">Domaine</th>
                                <th style="width: 10%">Sous-domaine</th>
                                <th style="width: 10%">Date début prévue</th>
                                <th style="width: 10%">Date fin prévue</th>
                                <th style="width: 20%">Coût</th>
                                <th style="width: 5%">Dévise</th>
                                <th style="width: 5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($statutsProjets as $projet)
                            @php
                                // Filtrer les statuts pour le projet actuel
                                $statutsProjet = $Statuts->where('CodeProjet', $projet->CodeProjet);
                            @endphp
                            <tr>
                                <td>{{ $projet->CodeProjet }}</td>
                                <td>
                                    @foreach ($statutsProjet as $statut)
                                        {{ $statut->statut_libelle }} <br>
                                    @endforeach
                                </td>
                                <td>{{ $projet->district->libelle }}</td>
                                <td>{{ $projet->region->libelle }}</td>
                                <td>{{ $projet->domaine->libelle }}</td>
                                <td>{{ $projet->sous_domaine->libelle }}</td>
                                <td>{{ date('d-m-Y', strtotime($projet->Date_demarrage_prevue)) }}</td>
                                <td>{{ date('d-m-Y', strtotime($projet->date_fin_prevue)) }}</td>
                                <td style="width: 12%; text-align: right;">{{ number_format($projet->cout_projet, 0, ',', ' ') }}</td>
                                <td>{{ $projet->devise->code_long ?? '' }}</td>
                                <td>
                                    <div class="dropdown">
                                        <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                            <span style="color: white"></span>
                                        </a>
                                        <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                            <li><a class="dropdown-item" href="#"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
                                            <li><a class="dropdown-item" href="#"><i class="bi bi-plus-circle me-3"></i> Détails</a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>


                    </div>
                </div>

            </div>
        </div>
    </div>

</section>



<script>
    $(document).ready(function() {
        initDataTables('table1', 'Liste des finances')
    });
    function initDataTables(table, title) {
        var logo =
            "http://localhost:8000/betsa/assets/images/ehaImages/armoirie.png";
        var now = new Date();
        var dateTime = now.toLocaleString("fr-FR", {
            day: "numeric",
            month: "long",
            year: "numeric",
            hour: "numeric",
            minute: "numeric",
        });
        var userName = "{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}";
        $("#" + table).DataTable({
            fixedColumns: true,
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
            select: {
                items: "cell",
                info: false,
            },
            scrollX: true,
            dom: "Bfrtip",
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
                    extend: "excelHtml5",
                    text: "Exporter",
                    title: title,

                },
                {
                    extend: "print",
                    title: "",
                    text: "Imprimer",
                    orientation: "portrait",
                    pageSize: "A4",
                    exportOptions: {
                        columns: ":not(:last-child)", // Exclure la dernière colonne de l'impression
                    },
                    customize: function (win) {
                        // Récupérer le nombre de colonnes
                        var numColumns = $("#" + table)
                            .DataTable()
                            .columns()
                            .header().length;
                        $(win.document.body).append('<style>@page { size: portrait; }</style>');
                        // Changer l'orientation si le nombre de colonnes est supérieur à 6
                        var pageSize = numColumns > 6 ? 'A3' : 'A4';

                        // Chemin de l'image
                        var imagePath = logo;

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
                            "<h3>" + title + "</h3>" +
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
                        //Ajouter l'en-tête personnalisé
                        $(win.document.body).find("thead").prepend(header);


                        //Personnaliser le pied de page

                        var footer =
                            '<div style="text-align:right; margin-top: 10px;">' +
                            '<p style="font-size: 12px; margin: 0;">Date impression: ' +
                            dateTime +
                            "</p>" +
                            '<p style="font-size: 12px; margin: 0;">Imprimé par: ' +
                            userName +
                            "</p>" +
                            "</div>";
                            // Ajouter la numérotation des pages

                        // Ajouter le pied de page personnalisé
                        $(win.document.body).find("tfoot").html(footer);

                        // Appliquer l'orientation et la taille de la page
                        $(win.document.body).css({
                            'orientation': 'landscape',
                            'pageSize': pageSize
                        });
                    },
                },
            ],
        });
    }

</script>
@endsection