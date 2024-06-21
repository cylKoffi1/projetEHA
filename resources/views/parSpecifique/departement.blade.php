@extends('layouts.app')


@section('content')

@if (session('success'))
<script>
    $('#alertMessage').text("{{ session('success') }}");
    $('#alertModal').modal('show');
</script>
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
                        <li class="breadcrumb-item active" aria-current="page">Départements</li>

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
            <div  style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                <h5 class="card-title">
                    Ajout d'un département
                    <a  href="#" data-toggle="modal" data-target="#departement-modal"  style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
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
               <h5 class="card-title"> Liste des départements</h5>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Département</th>
                        <th>Région</th>
                        <th>District</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($departements as $p)
                    <tr>
                        <td>{{ $p->code }}</td>
                        <td>{{ $p->libelle }}</td>
                        <td>{{ $p->region->libelle }}</td>
                        <td>{{ $p->region->district->libelle }}</td>
                        <td>
                            <div class="dropdown">
                                <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                    <span style="color: white"></span>
                                </a>
                                <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" onclick="showEditDepartement('{{ $p->code }}')" href="#"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                    <li><a class="dropdown-item" onclick="deleteDepartement('{{ $p->code }}')" href="#"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
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
    <div class="modal fade" id="departement-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Enregistrement de département</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form" method="POST" action="{{ route('departement.store') }}" data-parsley-validate>
                        @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="id_pays">Pays :</label>
                                    <select class="form-select" id="id_pays" name="id_pays" required>
                                        <option value="">Sélectionner un pays</option>
                                        @foreach ($pays as $country)
                                        <option value="{{ $country->id }}">{{ $country->nom_fr_fr }}</option>
                                        @endforeach
                                    </select>
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
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="id_region">Région :</label>
                                    <select class="form-select region-select" id="id_region" name="id_region" required>
                                        <option value="">Sélectionner une région</option>
                                        @foreach ($regions as $region)
                                        <option value="{{ $region->code }}">{{ $region->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="code">Code :</label>
                                    <input type="text" class="form-control" id="code" name="code" placeholder="Code du département" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Libellé :</label>
                                    <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Libellé" required>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <input type="submit" class="btn btn-primary" value="Enregistrer" id="enregistrerDepartement">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour la modification d'un département -->
    <div class="modal fade" id="edit-departement-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Modification de département</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form" method="POST" action="{{ route('departement.update') }}" data-parsley-validate>
                        @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>

                        <!-- Utilisez la méthode PUT pour la mise à jour du département -->
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit_code">Code :</label>
                                    <input type="text" class="form-control" id="edit_code" name="edit_code" value="" placeholder="Code du département" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit_libelle">Libellé :</label>
                                    <input type="text" class="form-control" id="edit_libelle" name="edit_libelle" value="" placeholder="Libellé" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit_id_pays">Pays :</label>
                                    <select class="form-select" id="edit_id_pays" name="edit_id_pays" required>
                                        <option value="">Sélectionner un pays</option>
                                        @foreach ($pays as $country)
                                        <option value="{{ $country->id }}">{{ $country->nom_fr_fr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit_id_district">District :</label>
                                    <select class="form-select" id="edit_id_district" name="edit_id_district" required>
                                        <option value="">Sélectionner un district</option>
                                        @foreach ($districts as $district)
                                        <option value="{{ $district->code }}">{{ $district->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit_id_region">Région :</label>
                                    <select class="form-select region-select" id="edit_id_region" name="edit_id_region" required>
                                        <option value="">Sélectionner une région</option>
                                        @foreach ($regions as $region)
                                        <option value="{{ $region->code }}">{{ $region->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <input type="submit" class="btn btn-primary" value="Enregistrer" id="edit_enregistrerDepartement">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>




</section>

<script>
    // Lorsque l'utilisateur clique sur un bouton "Modifier"
    function showEditDepartement(code) {
        $('#edit-departement-modal').modal('show');
        $.ajax({
            type: 'GET'
            , url: '/admin/departement/' + code
            , success: function(data) {
                console.log(data);
                // Remplir le formulaire modal avec les données du district
                $('#edit_code').val(data.code); // Utilisez l'ID du champ d'édition
                $('#edit_libelle').val(data.libelle);
                $('#edit_id_pays').val(data.region.district.pays.id);
                $('#edit_id_district').val(data.region.district.code);
                $('#edit_id_region').val(data.code_region);
                // Assurez-vous que les champs select sont correctement préremplis
                $('#edit_id_pays').trigger('change'); // Déclenchez un événement de changement pour mettre à jour les champs dépendants, le cas échéant
                $('#edit_id_district').trigger('change');
                $('#edit_id_region').trigger('change');

            }
        });
    }

    function deleteDepartement(code) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce département ?")) {
            $.ajax({
                url: '/admin/departement/delete/' + code
                , method: 'DELETE', // Utilisez la méthode DELETE pour la suppression
                data: {
                    _token: '{{ csrf_token() }}' // Assurez-vous d'envoyer le jeton CSRF
                }
                , success: function(response) {
                    var message = "Département supprimé avec succès.";
                    showPopup(message);
                    // Rechargez la page actuelle en ignorant le cache du navigateur
                    window.location.reload(true);

                }
                , error: function() {
                    // Gérer les erreurs de la requête AJAX
                    console.log('Erreur lors de la suppression du département.');
                }
            });
        }
    }

</script>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste des départements')
        $('#code').on('input', function() {
            // Get the input value
            var code = $(this).val();

            // Send an AJAX request to check if the code already exists
            $.ajax({
                url: '/check-departement-code', // Replace with the actual URL in your Laravel routes
                method: 'POST'
                , data: {
                    _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                    code: code
                }
                , success: function(response) {
                    if (response.exists) {
                        $('#code').removeClass('is-valid').addClass('is-invalid');
                        $('#enregistrerDepartement').prop('disabled', true);
                    } else {
                        $('#code').removeClass('is-invalid').addClass('is-valid');
                        $('#enregistrerDepartement').prop('disabled', false);
                    }
                }
            });
        });

        // Lorsque la sélection du pays change
        function updateDistricts(selectElement, districtSelectElement) {
            var selectedPays = selectElement.val();

            // Effectuez une requête AJAX pour obtenir les districts
            $.ajax({
                type: 'GET'
                , url: '/admin/get-districts/' + selectedPays, // Assurez-vous d'ajuster l'URL de la route
                success: function(data) {
                    districtSelectElement.empty(); // Effacez les options précédentes

                    // Ajoutez les options des districts récupérés
                    $.each(data.districts, function(key, value) {
                        districtSelectElement.append($('<option>', {
                            value: key
                            , text: value
                        }));
                    });
                    districtSelectElement.trigger('change');

                }
            });
        }

        // Lorsque la sélection du pays change pour la création
        $('#id_pays').on('change', function() {
            updateDistricts($(this), $('#id_district'));
        });

        // Lorsque la sélection du pays change pour la modification
        $('#edit_id_pays').on('change', function() {
            updateDistricts($(this), $('#edit_id_district'));
        });

        // Lorsque la sélection du district change
        function updateRegions(selectElement) {
            var selectedDistrict = selectElement.val();

            // Effectuez une requête AJAX pour obtenir les régions
            $.ajax({
                type: 'GET'
                , url: '/admin/get-regions/' + selectedDistrict
                , success: function(data) {
                    var regionSelect = selectElement.closest('.modal').find('.region-select');
                    regionSelect.empty(); // Effacez les options précédentes

                    // Ajoutez les options des régions récupérées
                    $.each(data.regions, function(key, value) {
                        regionSelect.append($('<option>', {
                            value: key
                            , text: value
                        }));
                    });
                    $('#edit_id_region').trigger('change');
                }
            });
        }

        // Lorsque la sélection du district change pour la création
        $('#id_district').on('change', function() {
            updateRegions($(this));
        });

        // Lorsque la sélection du district change pour la modification
        $('#edit_id_district').on('change', function() {
            updateRegions($(this));
        });
    });

</script>

@endsection
