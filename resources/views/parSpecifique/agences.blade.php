@extends('layouts.app')


@section('content')

@if (session('success'))
<script>
    $('#alertMessage').text("{{ session('success') }}");
    $('#alertModal').modal('show');
</script>
@endif

<section class="section">
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
                            <li class="breadcrumb-item"><a href="">Paramètre spécifiques</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Agences</li>

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
    @can("ajouter_ecran_" . $ecran->id)
    <div class="modal-content">

        <div class="modal-body">

            <!-- // Basic multiple Column Form section start -->
            <section id="multiple-column-form">
                <div class="row match-height">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <div  style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                                    <h5 class="card-title">
                                    Enregistrement d'une agence
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
                            </div>
                            <div class="card-content">
                                <div class="card-body">
                                    <form class="form" method="POST" action="{{ route('agence.store') }}"data-parsley-validate>
                                        @csrf
                                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                                        <div class="row">
                                            <div class="col">
                                                <div class="form-group mandatory">
                                                    <label class="form-label" for="code">Code :</label>
                                                    <input type="text" class="form-control" id="code" name="code" placeholder="Code de l'agence" required>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="form-group mandatory">
                                                    <label class="form-label" for="libelle">Nom Agence :</label>
                                                    <input type="text" class="form-control" id="nom_agence" name="nom_agence" placeholder="Nom de l'agence" required>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="form-group mandatory">
                                                    <label class="form-label" for="libelle">Téléphone :</label>
                                                    <input type="text" class="form-control" id="tel" name="tel" placeholder="Téléphone de l'agence" required>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="form-group mandatory">
                                                    <label class="form-label" for="libelle">Email: :</label>
                                                    <input type="email" class="form-control" id="email" name="email" placeholder="Email de l'agence" required>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="form-group mandatory">
                                                    <label class="form-label" for="libelle">Addresse :</label>
                                                    <input type="text" class="form-control" id="addresse" name="addresse" placeholder="Addresse de l'agence" required>
                                                </div>
                                            </div>
                                        </div>
                                    <br>
                                        @can("ajouter_ecran_".$ecran->id)
                                        <div class="d-flex justify-content-end">
                                        <input type="submit" class="btn btn-primary" value="Enregistrer" id="enregistrerAgences">
                                        </div>
                                        @endcan
                                    </form>

                                </div>
                            </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    @endcan
    <div class="card">
        <div class="card-header">

            <div style="text-align: center;">
               <h5 class="card-title"> Liste des agences d'exécution</h5>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom agence</th>
                        <th>Téléphone</th>
                        <th>Email</th>
                        <th>Adresse</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($agences as $p)
                    <tr>
                        <td>{{ $p->code_agence_execution }}</td>
                        <td>{{ $p->nom_agence }}</td>
                        <td>{{ $p->telephone }}</td>
                        <td>{{ $p->email }}</td>
                        <td>{{ $p->addresse }}</td>
                        <td>
                            <div class="dropdown">
                                <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                    <span style="color: white"></span>
                                </a>
                                <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" onclick="showEditAgence('{{ $p->code_agence_execution }}')" href="#"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                    <li><a class="dropdown-item" onclick="deleteAgence('{{ $p->code_agence_execution }}')" href="#"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
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


    <!-- Modal -->
    <div class="modal fade" id="edit-agences-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Modification de l'agence</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form" method="POST" action="{{ route('agence.update') }}" data-parsley-validate>
                        @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="code">Code :</label>
                                    <input type="text" class="form-control" id="edit_code" name="edit_code" placeholder="Code de l'agence" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Nom Agence :</label>
                                    <input type="text" class="form-control" id="edit_nom_agence" name="edit_nom_agence" placeholder="Nom de l'agence" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Téléphone :</label>
                                    <input type="text" class="form-control" id="edit_tel" name="edit_tel" placeholder="Téléphone de l'agence" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Email: :</label>
                                    <input type="email" class="form-control" id="edit_email" name="edit_email" placeholder="Email de l'agence" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Addresse :</label>
                                    <input type="text" class="form-control" id="edit_addresse" name="edit_addresse" placeholder="Addresse de l'agence" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <input type="submit" class="btn btn-primary" value="Enregistrer" id="edit_enregistrerAgences">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>




</section>

{{--
<script>
    $(document).ready(function() {
        $('#table1').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/French.json"
            },
            autoFill: true
        });
    });

</script> --}}

<script>
    // Lorsque l'utilisateur clique sur un bouton "Modifier"
    function showEditAgence(code) {
        $('#edit-agences-modal').modal('show');
        $.ajax({
            type: 'GET'
            , url: '/admin/agences/' + code
            , success: function(data) {
                console.log(data);
                // Remplir le formulaire modal avec les données du district
                $('#edit_code').val(data.code_agence_execution); // Utilisez l'ID du champ d'édition
                $('#edit_nom_agence').val(data.nom_agence);
                $('#edit_tel').val(data.telephone);
                $('#edit_email').val(data.email);
                $('#edit_addresse').val(data.addresse);
            }
        });
    }

    function deleteAgence(code) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cette agence ?")) {
            $.ajax({
                url: '/admin/agences/delete/' + code
                , method: 'DELETE', // Utilisez la méthode DELETE pour la suppression
                data: {
                    _token: '{{ csrf_token() }}' // Assurez-vous d'envoyer le jeton CSRF
                }
                , success: function(response) {
                    var message = "Agence supprimé avec succès.";
                    showPopup(message);
                    // Rechargez la page actuelle en ignorant le cache du navigateur
                    window.location.reload(true);

                }
                , error: function() {
                    // Gérer les erreurs de la requête AJAX
                    console.log("Erreur lors de la suppression de l'agence.");
                }
            });
        }
    }

</script>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste des agences')
        $('#code').on('input', function() {
            // Get the input value
            var code = $(this).val();

            // Send an AJAX request to check if the code already exists
            $.ajax({
                url: '/check-agence-code', // Replace with the actual URL in your Laravel routes
                method: 'POST'
                , data: {
                    _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                    code: code
                }
                , success: function(response) {
                    if (response.exists) {
                        $('#code').removeClass('is-valid').addClass('is-invalid');
                        $('#enregistrerAgences').prop('disabled', true);
                    } else {
                        $('#code').removeClass('is-invalid').addClass('is-valid');
                        $('#enregistrerAgences').prop('disabled', false);
                    }
                }
            });
        });

    });

</script>
@endsection
