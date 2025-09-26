@extends('layouts.app')


@section('content')
@isset($ecran)
    @can("consulter_ecran_" . $ecran->id)
@if (session('success'))
<script>
    alert("{{ session('success') }}");

</script>
{{-- <div class="alert alert-success">
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <button class="close" style="background-color: red;" data-dismiss="alert" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    {{ session('success') }}
</div> --}}
@endif


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
                        <li class="breadcrumb-item active" aria-current="page">Régions</li>

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

<section class="section">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                <h5 class="card-title">
                    Ajout d'une région
                    @can("ajouter_ecran_" . $ecran->id)
                    <a  href="#" data-toggle="modal" data-target="#region-modal" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
                    @endcan
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
                <h5 class="card-title"> Liste des régions</h5>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Région</th>
                        <th>District</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($regions as $p)
                    <tr>
                        <td>{{ $p->code }}</td>
                        <td>{{ $p->libelle }}</td>
                        <td>{{ $p->district->libelle }}</td>
                        <td>
                            <div class="dropdown">
                                <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                    <span style="color: white"></span>
                                </a>
                                <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                    @can("modifier_ecran_" . $ecran->id)
                                    <li><a class="dropdown-item" onclick="showEditRegion('{{ $p->code }}')" href="#"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                    @endcan
                                    @can("supprimer_ecran_" . $ecran->id)
                                    <li><a class="dropdown-item" onclick="deleteRegion('{{ $p->code }}')" href="#"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
                                    @endcan
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


    <!-- Enregistrement -->
    <div class="modal fade" id="region-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Enregistrement de région</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form" method="POST" action="{{ route('region.store') }}" data-parsley-validate>
                        @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="code">Code :</label>
                                    <input type="text" class="form-control" id="code" name="code" placeholder="Code de la région" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Libellé :</label>
                                    <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Libellé" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="id_district">District :</label>
                                    <select class="form-select" id="id_district" name="id_district" required>
                                        <option value="">Sélectionner un district</option>
                                        @foreach ($districts as $district)
                                        <option value="{{ $district->code }}">{{ $district->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            @can("ajouter_ecran_" . $ecran->id)
                            <input type="submit" class="btn btn-primary" value="Enregistrer" id="enregistrerRegion">
                            @endcan
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- mise à jour -->
    <div class="modal fade" id="edit-region-modal" tabindex="-1" role="dialog" aria-labelledby="editModalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalTitle">Modification de région</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form" method="POST" action="{{ route('district.update') }}" data-parsley-validate>
                        @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>

                        <div class="row">
                            <div class="col-md-12 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="editDistrict">District :</label>
                                    <select class="form-select" id="editDistrict" name="editDistrict" required>
                                        <option value="">Sélectionner un district</option>
                                        @foreach ($districts as $district)
                                        <option value="{{ $district->code }}">{{ $district->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="editCode">Code :</label>
                                    <input type="text" class="form-control" id="editCode" name="editCode" readonly placeholder="Nouveau code de la région" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="editLibelle">Libellé :</label>
                                    <input type="text" class="form-control" id="editLibelle" name="editLibelle" placeholder="Nouveau libellé" required>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            @can("modifier_ecran_" . $ecran->id)
                            <input type="submit" class="btn btn-primary" value="Enregistrer les modifications" id="enregistrerRegion">
                            @endcan
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</section>


<script>
    // Lorsque l'utilisateur clique sur un bouton "Modifier"
    function showEditRegion(code) {
        $('#edit-region-modal').modal('show');
        $.ajax({
            type: 'GET'
            , url: '/admin/region/' + code
            , success: function(data) {
                console.log(data);
                // Remplir le formulaire modal avec les données du district
                $('#editCode').val(data.code); // Utilisez l'ID du champ d'édition
                $('#editLibelle').val(data.libelle);
                $('#editDistrict').val(data.code_district);

                // Assurez-vous que les champs select sont correctement préremplis
                $('#editDistrict').trigger('change');
            }
        });
    }

    $(document).ready(function() {
       
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste des régions')
 
        $('#code').on('input', function() {
            // Get the input value
            var code = $(this).val();

            // Send an AJAX request to check if the code already exists
            $.ajax({
                url: '/check-region-code', // Replace with the actual URL in your Laravel routes
                method: 'POST'
                , data: {
                    _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                    code: code
                }
                , success: function(response) {
                    if (response.exists) {
                        $('#enregistrerRegion').prop('disabled', true);
                        $('#code').removeClass('is-valid').addClass('is-invalid');
                    } else {
                        $('#enregistrerRegion').prop('disabled', false);
                        $('#code').removeClass('is-invalid').addClass('is-valid');
                    }
                }
            });
        });

    });

    function deleteRegion(code) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cette région ?")) {
            $.ajax({
                url: '/admin/region/delete/' + code
                , method: 'DELETE', // Utilisez la méthode DELETE pour la suppression
                data: {
                    _token: '{{ csrf_token() }}', // Assurez-vous d'envoyer le jeton CSRF
                    ecran_id: '{{ $ecran->id }}'
                }
                , success: function(response) {
                    var message = "Région supprimé avec succès.";
                    showPopup(message);
                }
                , error: function() {
                    // Gérer les erreurs de la requête AJAX
                    console.log('Erreur lors de la suppression de la région.');
                }
            });
        }
    }

</script>
    @endcan
@endisset
@endsection
