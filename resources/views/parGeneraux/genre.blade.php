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
                            <li class="breadcrumb-item active" aria-current="page">Genre</li>

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
                        <h5 class="card-title">
                            Ajout d'un genre
                            <a  href="#" data-toggle="modal" data-target="#genre-modal" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
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
                        <h5 class="card-title"> Liste des genre</h5>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body">


                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                            <thead>
                                <tr>
                                    <th>Code </th>
                                    <th>Libelle</th>
                                    <th>action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($genre as $genre)
                                <tr>
                                    <td>{{ $genre->code_genre }}</td>
                                    <td>{{ $genre->libelle_genre }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                                <span style="color: white"></span>
                                            </a>
                                            <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                                <li><a class="dropdown-item" href="#" onclick="showEditGenre('{{ $genre->code_genre }}')"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="deleteGenre('{{ $genre->code_genre }}')"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
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

    <!-- Modal Enregistrement -->
    <div class="modal fade" id="genre-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Enregistrement de genre</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <!-- // Basic multiple Column Form section start -->
                    <section id="multiple-column-form">
                        <div class="row match-height">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <form class="form" method="POST" action="{{ route('genre.store') }}" data-parsley-validate>
                                                @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                                                <div class="row">
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group mandatory">
                                                            <label class="form-label" for="code">Code :</label>
                                                            <input type="text" class="form-control" id="code" name="code" placeholder="code" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group mandatory">
                                                            <label class="form-label" for="libelle">Libelle :</label>
                                                            <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Libelle" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                    <input type="submit" class="btn btn-primary" value="Enregistrer" id="enregistrerGenre">
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- // Basic multiple Column Form section end -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Modification -->
    <div class="modal fade" id="genre-modal-edit" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Modification de genre</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <!-- // Basic multiple Column Form section start -->
                    <section id="multiple-column-form">
                        <div class="row match-height">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <form class="form" method="POST" action="{{ route('genre.update') }}" data-parsley-validate>
                                                @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                                                <div class="row">
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group mandatory">
                                                            <label class="form-label" for="code">Code :</label>
                                                            <input type="text" class="form-control" id="code_edit" name="code_edit" placeholder="Code" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group mandatory">
                                                            <label class="form-label" for="libelle">Libelle :</label>
                                                            <input type="text" class="form-control" id="libelle_edit" name="libelle_edit" placeholder="Libelle" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                @can("modifier_ecran_".$ecran->id)
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                    <input type="submit" class="btn btn-primary" id="submit-button-edit" value="Modifier">
                                                </div>
                                                @endcan
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <!-- // Basic multiple Column Form section end -->
                </div>
            </div>
        </div>
    </div>
</section>



<script>
    /* CODE JAVASCRIPT ICI */


    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste des genres')

        $('#code').on('input', function() {
            // Get the input value
            var code = $(this).val();

            // Send an AJAX request to check if the code already exists
            $.ajax({
                url: '/check-genre-code', // Replace with the actual URL in your Laravel routes
                method: 'POST'
                , data: {
                    _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                    code: code
                }
                , success: function(response) {
                    if (response.exists) {
                        $('#code').removeClass('is-valid').addClass('is-invalid');
                        $('#enregistrerGenre').prop('disabled', true);
                    } else {
                        $('#code').removeClass('is-invalid').addClass('is-valid');
                        $('#enregistrerGenre').prop('disabled', false);
                    }
                }
            });
        });
    });

    // Lorsque l'utilisateur clique sur un bouton "Modifier"
    function showEditGenre(code) {
        $('#genre-modal-edit').modal('show');
        $.ajax({
            type: 'GET'
            , url: '/admin/genre/' + code
            , success: function(data) {
                // Remplir le formulaire modal avec les données du district
                $('#code_edit').val(data.code_genre);
                $('#libelle_edit').val(data.libelle_genre);
            }
            , error: function(data) {
                // Gérer les erreurs de la requête AJAX
                showPopup(data.responseJSON.error);
            }
        });
    }

    function deleteGenre(code) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce genre ?")) {
            $.ajax({
                url: '/admin/genre/delete/' + code
                , method: 'DELETE', // Utilisez la méthode DELETE pour la suppression
                data: {
                    _token: '{{ csrf_token() }}' // Assurez-vous d'envoyer le jeton CSRF
                }
                , success: function(response) {
                    var message = "Genre supprimé avec succès.";
                    showPopup(message);
                    window.location.reload(true);
                }
                , error: function(response) {
                    // Gérer les erreurs de la requête AJAX
                    showPopup(response.responseJSON.error);
                }
            });
        }
    }

</script>
@endsection