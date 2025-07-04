@extends('layouts.app')

@section('content')
<style>
    /* Style global pour centrer le contenu de la page */
    body {
        justify-content: center;
        align-items: center;
    }

    /* Style pour le conteneur principal */
    .container {
        width: 80%;
        max-width: 1200px; /* Largeur maximale du conteneur */
    }

    /* Style pour le fond du modal */
    .modal-content {
        background-color: #EAF2F8;
    }

    .modal-header,
    .modal-footer {
        background-color: #EAF2F8;
    }

    .modal-header .btn-close {
        filter: invert(1); /* Assure que le bouton de fermeture est visible */
    }

    #liste-approbateurs-modal span{
        color: #666;
    }

    /* Style pour le contenu de la page */
    #multiple-column-forms {
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
    }

    .table-container {
        width: 100%;
    }

</style>
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Plateforme </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Paramètre généraux</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Approbateur</li>
                        </ol>
                    </nav>
                    <div class="row">
                        <script>
                            setInterval(function() {
                                document.getElementById('date-now').textContent = getCurrentDate();
                            }, 1000);

                            function getCurrentDate() {
                                var currentDate = new Date();
                                return currentDate.toLocaleString();
                            }
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section id="multiple-column-forms"  style="justify-content: center;">

        <div class="col-10">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col">
                            <h5 class="card-title">Approbateur</h5>
                                @if(session('error'))
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        $('#alertMessage').text("{{ session('error') }}");
                                        $('#alertModal').modal('show');
                                    });
                                </script>
                                @endif
                                @if(session('success'))
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        $('#alertMessage').text("{{ session('success') }}");
                                        $('#alertModal').modal('show');
                                    });
                                </script>
                                @endif
                        </div>
                        <div class="col d-flex justify-content-end" style="line-height: inherit;">
                            <h6><a href="#" id="voir-liste-link" data-bs-toggle="modal" data-bs-target="#liste-approbateurs-modal">Voir la liste des approbateurs</a></h6>
                        </div>
                    </div>
                </div>
                <div class="card-content">
                    <fieldset class="border p-3 mt-5 rounded">
                        <div class="row align-items-center">

                            <div class="col-4">
                                <label for="user" class="form-label">Utilisateur:</label>
                                <select id="user" class="form-select" name="userapp">
                                    <option value="">Sélectionner les approbateurs</option>
                                    @foreach($acteurs as $acteur)
                                        <option value="{{ $acteur?->code_acteur }}">{{ $acteur?->libelle_court }} {{ $acteur?->libelle_long }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2 ms-auto">
                                <label for="nordre" class="form-label">Niveau :</label>
                                <input type="number" name="Nordre" id="nordre" value="{{ $nextOrder }}"  readonly class="form-control">
                            </div>
                            <div class="col-12 mt-3">
                                @can("ajouter_ecran_" . $ecran?->id)
                                <button type="button" class="btn btn-primary" id="addAction">
                                    <i class="fa fa-plus"></i> Ajouter
                                </button>
                                @endcan
                                <form id="approveForm" method="POST" action="{{ route('approbateur.store') }}">
                                    @csrf
                                    <input type="hidden" name="approbateurs" id="approbateursInput">
                                    @can("ajouter_ecran_" . $ecran?->id)
                                    <button type="submit" class="btn btn-primary float-end">
                                        <i class="fa fa-save"></i> Enregistrer
                                    </button>
                                    @endcan
                                </form>
                            </div>
                        </div>
                    </fieldset>
                </div>
            </div>

            <hr>
            <div class="card">
                <div class="card-body">
                    <div class="table-container">
                        <table id="tableActionMener" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Niveau d'approbation</th>
                                    <th>Nom</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Les lignes seront ajoutées ici dynamiquement -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!--Button to open the modal -->
    <div class="row">
        </div>

     <!--Modal for the list of approvers -->
    <div class="modal fade" id="liste-approbateurs-modal" tabindex="-1" aria-labelledby="listeApprobatuerModalLabel" aria-hidden="true" style="background-color: #EAF2F8;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="listeApprobatuerModalLabel">Liste des approbateurs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="liste-approbateur-table">
                        <thead>
                            <tr>

                                <th>Nom </th>
                                <th>Prénoms</th>
                                <th>Niveau approbation</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approbateurs as $approbateur)
                            <tr>

                                <td>{{ $approbateur?->Acteur?->libelle_court }}</td>
                                <td>{{ $approbateur?->Acteur?->libelle_long }}</td>
                                
                                <td>{{ $approbateur?->numOrdre }}</td>
                                <td>
                                <div class="dropdown">
                                    <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                        <span style="color: white">Options</span>
                                    </a>
                                    <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                       
                                        @can("supprimer_ecran_" . $ecran?->id)
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="deleteApprobateur('{{ $approbateur?->codeAppro }}')">
                                                <i class="bi bi-trash3-fill me-3"></i> Supprimer
                                            </a>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>

                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!--Modale Modifier approbateur-->
    <div class="modal fade" id="editApprobateurModal" tabindex="-1" aria-labelledby="editApprobateurModalLabel" aria-hidden="true" style="background-color: #EAF2F8;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editApprobateurModalLabel">Modifier Approbateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editApproveForm" method="POST" action="{{ route('approbateur.update') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="numOrdreId" id="numOrdreId">
                        <fieldset class="border p-3 mt-5 rounded">
                            <div class="row align-items-center">
                                <div class="col-2">
                                    <label for="editNordre" class="form-label">Niveau :</label>
                                    <input type="number" name="editNordre" id="editNordre" readonly class="form-control">
                                </div>
                                <div class="col-5">
                                    <label for="editUser" class="form-label">Utilisateur:</label>
                                    <select id="editUser" class="form-select" name="editUserapp">
                                        <option value="">Sélectionner l'utilisateur</option>
                                        @foreach($acteurs as $acteur)
                                        <option value="{{ $acteur?->code_acteur }}">{{ $acteur?->libelle_court }} {{ $acteur?->libelle_long }}</option>
                                        @endforeach
                                    </select>

                                </div>
                              
                                <div class="col-12 mt-3">
                                    @can("modifier_ecran_" . $ecran?->id)
                                    <button type="submit" class="btn btn-primary float-end">
                                        <i class="fa fa-save"></i> Enregistrer
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Message d'alerte -->
    <div id="alertModal" class="modal fade" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalLabel" style="color: red;">Alerte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="alertMessage"></p>
                </div>
            </div>
        </div>
    </div>
</section>
<script>

$(document).ready(function () {
    // Initialiser la DataTable si nécessaire
    initDataTable(
        '{{ auth()?->user()?->acteur?->libelle_court }} {{ auth()?->user()?->acteur?->libelle_long }}',
        'liste-approbateur-table',
        'Liste des approbateurs'
    );

    // Fonction utilitaire pour afficher une alerte dans le modal
    function showAlert(message) {
        $('#alertMessage').text(message);
        $('#alertModal').modal('show');
    }

    // Ajout d'un approbateur
    $('#addAction').on('click', function () {
        var nordre = parseInt($('#nordre').val());
        var userCode = $('#user').val();
        var userText = $('#user option:selected').text();

        if (userCode) {
            // Vérifier si l'utilisateur est déjà dans le tableau
            var existeDeja = false;
            $('#tableActionMener tbody tr').each(function () {
                var existingUser = $(this).find('td:eq(1)').text();
                if (existingUser === userText) {
                    existeDeja = true;
                    return false; // Sortir de la boucle
                }
            });

            if (!existeDeja) {
                var tableActionMener = $('#tableActionMener tbody');

                // Ajouter la ligne au tableau avec un data-user-code
                tableActionMener.append(
                    '<tr data-user-code="' + userCode + '">' +
                    '<td>' + nordre + '</td>' +
                    '<td>' + userText + '</td>' +
                    '<td><button type="button" class="btn btn-danger btn-sm delete-action">Supprimer</button></td>' +
                    '</tr>'
                );

                // Incrémenter nordre et réinitialiser le champ user
                $('#nordre').val(nordre + 1);
                $('#user').val('');
            } else {
                showAlert("Cet utilisateur est déjà dans le tableau.");
            }
        } else {
            showAlert("Veuillez sélectionner un utilisateur avant d'ajouter.");
        }
    });

    // Soumission du formulaire avec la liste des approbateurs
    $('#approveForm').on('submit', function (e) {
        e.preventDefault();

        var approbateurs = [];

        $('#tableActionMener tbody tr').each(function () {
            var nordre = $(this).find('td:eq(0)').text();
            var userText = $(this).find('td:eq(1)').text();
            var userCode = $(this).data('user-code');

            approbateurs.push({
                nordre: nordre,
                userText: userText,
                userCode: userCode
            });
        });

        if (approbateurs.length === 0) {
            showAlert("Veuillez ajouter au moins un approbateur.");
            return;
        }

        // Injecter les données dans un champ caché
        $('#approbateursInput').val(JSON.stringify(approbateurs));

        console.log('Approbateurs:', approbateurs); // Debug

        // Soumettre réellement le formulaire
        e.currentTarget.submit();
    });

    // Suppression d’un approbateur
    $('#tableActionMener').on('click', '.delete-action', function () {
        $(this).closest('tr').remove();

        // Recalcul des numéros d'ordre
        var currentOrder = parseInt({{ $nextOrder ?? 1 }});
        $('#tableActionMener tbody tr').each(function (index) {
            $(this).find('td:eq(0)').text(currentOrder + index);
        });

        // Mettre à jour nordre
        $('#nordre').val(currentOrder + $('#tableActionMener tbody tr').length);
    });
});


    function deleteApprobateur(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cet approbateur ?")) {
            $.ajax({
                url: '/approbation/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(result) {
                    $('#alertMessage').text("Approbateur supprimé avec succès.");
                    $('#alertModal').modal('show');
                    window.location.reload(true);
                },
                error: function(xhr, status, error) {
                    $('#alertMessage').text('Erreur lors de la suppression de l\'approbateur : ' + error);
                    $('#alertModal').modal('show');
                }
            });
        }
    }
    $(document).on('click', '.edit-approbateur', function() {
        var nordre = $(this).data('nordre');
        var name = $(this).data('name');
        var code = $(this).data('code');
        
        editApprobateur(nordre, name, code);
    });
    function editApprobateur(numOrdre, nomPrenom, userCode) {
        // Remplir les champs du modal avec les données existantes
        $('#editNordre').val(numOrdre);
        $('#editUser').val(userCode);
        $('#numOrdreId').val(numOrdre);

        // Ouvrir le modal
        $('#editApprobateurModal').modal('show');
    }



    $('#editApproveForm').on('submit', function(e) {
        e.preventDefault();

        // Effectuer les modifications nécessaires ici ou soumettre le formulaire
        this.submit();
    });

</script>

@endsection
