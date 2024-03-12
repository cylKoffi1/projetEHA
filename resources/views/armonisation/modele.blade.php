<!-- resources/views/users/create.blade.php -->

@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
    alert("{{ session('success') }}");

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
    .table-container {
        margin-top: 20px;
    }

    .dropdown-toggle::after {
        display: none; /* Masquer la flèche du dropdown */
    }

    .dropdown-menu {
        background-color: #f8f9fa; /* Couleur de fond du menu déroulant */
        border: 1px solid #dee2e6; /* Bordure du menu déroulant */
        border-radius: 0.25rem; /* Coins arrondis du menu déroulant */
    }

    .dropdown-item {
        color: #495057; /* Couleur du texte des éléments du menu déroulant */
    }

    .dropdown-item:hover {
        background-color: #e9ecef; /* Couleur de survol des éléments du menu déroulant */
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Grand menu </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Sous menu</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Page actuelle</li>

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
                    <h4 class="card-title">
                        Nouvelle personne
                        <a href="#" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
                    </h4>
                    <span id="create_new"></span>
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
                <div class="card-content">
                    <div class="card-body">

                        <!-- CONTENU À MODIFIER  -->

                        <!-- à suprimer -->
                        <h3>Exemple de form</h3>
                        <!-- fin à suprimer -->
                        <form class="form" id="personnelForm" enctype="multipart/form-data" action="">
                            @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                            <div class="row">
                                <div class="col">
                                    <label>Nom</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="nom" name="nom" class="form-control" placeholder="Nom">
                                        <div class="form-control-icon">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div id="nom-error" class="invalid-feedback"></div>
                                        @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col">
                                    <label>Prénom</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="nom" name="nom" class="form-control" placeholder="Nom">
                                        <div class="form-control-icon">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div id="nom-error" class="invalid-feedback"></div>
                                        @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col">
                                    <label>Adresse</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="nom" name="nom" class="form-control" placeholder="Nom">
                                        <div class="form-control-icon">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div id="nom-error" class="invalid-feedback"></div>
                                        @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label>Nom</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="nom" name="nom" class="form-control" placeholder="Nom">
                                        <div class="form-control-icon">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div id="nom-error" class="invalid-feedback"></div>
                                        @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col">
                                    <label>Prénom</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="nom" name="nom" class="form-control" placeholder="Nom">
                                        <div class="form-control-icon">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div id="nom-error" class="invalid-feedback"></div>
                                        @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col">
                                    <label>Adresse</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="nom" name="nom" class="form-control" placeholder="Nom">
                                        <div class="form-control-icon">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div id="nom-error" class="invalid-feedback"></div>
                                        @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 d-flex justify-content-end">
                                    {{-- <button type="reset" class="btn btn-light-secondary me-1 mb-1">
                                        Annuler
                                    </button> --}}
                                    <button type="button" id="soumettre_personnel" class="btn btn-primary me-1 mb-1">
                                        Enregistrer
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- à suprimer -->
                        <br>
                        <hr>
                        <br>
                        <h3>Exemple de table</h3>
                        <!-- fin à suprimer -->

                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
    <thead>
        <tr>
            <th>Nom </th>
            <th>Prénom</th>
            <th>Adresse</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        {{-- @foreach ($users as $user) --}}
        <tr>
            <td>Soro</td>
            <td>Ibrahim</td>
            <td>Abidjan</td>
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
        <tr>
            <td>Koffi</td>
            <td>Cyl</td>
            <td>Abidjan</td>
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
        {{-- @endforeach --}}
    </tbody>
</table>

<script>
    /* CODE JAVASCRIPT ICI */

    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste de la table');
    });

    function initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', tableId, title) {
        $('#' + tableId).DataTable({
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copyHtml5',
                    exportOptions: {
                        columns: ':not(:last-child)' // Exclure la dernière colonne
                    }
                },
                {
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'csvHtml5',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'pdfHtml5',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                },
                {
                    extend: 'print',
                    exportOptions: {
                        columns: ':not(:last-child)'
                    }
                }
            ],
            language: {
                paginate: {
                    first: 'Premier',
                    last: 'Dernier',
                    next: 'Suivant',
                    previous: 'Précédent'
                },
                emptyTable: 'Aucune donnée disponible dans le tableau',
                info: 'Affichage de _START_ à _END_ sur _TOTAL_ entrées',
                infoEmpty: 'Affichage de 0 à 0 sur 0 entrées',
                infoFiltered: '(filtré à partir de _MAX_ entrées au total)',
                lengthMenu: 'Afficher _MENU_ entrées',
                search: 'Rechercher :',
                zeroRecords: 'Aucun enregistrement trouvé',
                buttons: {
                    copy: 'Copier',
                    excel: 'Excel',
                    csv: 'CSV',
                    pdf: 'PDF',
                    print: 'Imprimer'
                }
            },
            initComplete: function () {
                $('.dt-buttons .buttons-collection').wrapAll('<div class="btn-group"></div>');
                $('.dt-button').removeClass('btn-secondary');
            }
        });

        // Ajoutez le titre à l'élément div avec la classe 'card-title'
        $('.card-title').text(title);
    }
</script>

@endsection
