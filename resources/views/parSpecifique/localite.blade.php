@extends('layouts.app')


@section('content')

@if (session('success'))
<script>
    alert("{{ session('success') }}");

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
                        <li class="breadcrumb-item active" aria-current="page">Localités</li>

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
                    Ajout d'une localité
                     @can("ajouter_ecran_" . $ecran->id)
                    <a  href="#" data-toggle="modal" data-target="#localite-modal" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
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
                <h5 class="card-title"> Liste des localités</h5>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%"id="table1">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Localité</th>
                        <th>Sous-préfecture</th>
                        <th>Département</th>
                        <th>Région</th>
                        <th>District</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($localites as $p)
                    <tr>
                        <td>{{ $p->code }}</td>
                        <td>{{ $p->libelle }}</td>
                        <td>{{ $p->Sous_prefecture->libelle }}</td>
                        <td>{{ $p->Sous_prefecture->departement->libelle }}</td>
                        <td>{{ $p->Sous_prefecture->departement->region->libelle }}</td>
                        <td>{{ $p->Sous_prefecture->departement->region->district->libelle }}</td>
                        <td>
                            <div class="dropdown">
                                <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                    <span style="color: white"></span>
                                </a>
                                <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" onclick="showEditLocalite('{{ $p->code }}')" href="#"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                    <li><a class="dropdown-item" onclick="deleteLocalite('{{ $p->code }}')" href="#"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
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
    <div class="modal fade" id="localite-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Enregistrement de localité</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form class="form" method="POST" action="" data-parsley-validate>
                        @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                        <div class="row">
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
                                    <label class="form-label" for="id_departement">Département :</label>
                                    <select class="form-select departement-select" id="id_departement" name="id_departement" required>
                                        <option value="">Sélectionner un département</option>
                                        @foreach ($departements as $departement)
                                        <option value="{{ $departement->code }}">{{ $departement->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="id_sous_prefecture">Sous-Préfecture :</label>
                                    <select class="form-select sous_prefecture-select" id="id_sous_prefecture" name="id_sous_prefecture" required>
                                        <option value="">Sélectionner une sous-préfecture</option>
                                        @foreach ($sous_prefectures as $sous_prefecture)
                                        <option value="{{ $sous_prefecture->code }}">{{ $sous_prefecture->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="code">Code :</label>
                                    <input type="text" class="form-control" id="code" name="code" placeholder="Code de la localité" required>
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
                            <input type="submit" class="btn btn-primary" value="Enregistrer" id="enregistrerLocalite">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
<div class="modal fade" id="edit-localite-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Modification de localité</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form" method="POST" action="{{ route('localite.update') }}" data-parsley-validate>
                    @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                    <div class="row">
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
                        <div class="col-md-6 col-12">
                            <div class="form-group mandatory">
                                <label class="form-label" for="edit_id_departement">Département :</label>
                                <select class="form-select departement-select" id="edit_id_departement" name="edit_id_departement" required>
                                    <option value="">Sélectionner un département</option>
                                    @foreach ($departements as $departement)
                                        <option value="{{ $departement->code }}">{{ $departement->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group mandatory">
                                <label class="form-label" for="edit_id_sous_prefecture">Sous-Préfecture :</label>
                                <select class="form-select sous_prefecture-select" id="edit_id_sous_prefecture" name="edit_id_sous_prefecture" required>
                                    <option value="">Sélectionner une sous-préfecture</option>
                                    @foreach ($sous_prefectures as $sous_prefecture)
                                        <option value="{{ $sous_prefecture->code }}">{{ $sous_prefecture->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group mandatory">
                                <label class="form-label" for="edit_code">Code :</label>
                                <input type="text" class="form-control" id="edit_code" name="edit_code" placeholder="Code de la localité" readonly required>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group mandatory">
                                <label class="form-label" for="edit_libelle">Libellé :</label>
                                <input type="text" class="form-control" id="edit_libelle" name="edit_libelle" placeholder="Libellé" required>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        <input type="submit" class="btn btn-primary" value="Enregistrer" id="edit_enregistrerLocalite">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>




</section>



<script>
    // Lorsque l'utilisateur clique sur un bouton "Modifier"
    function showEditLocalite(code) {
        $('#edit-localite-modal').modal('show');
        $.ajax({
            type: 'GET'
            , url: '/admin/localite/' + code
            , success: function(data) {
                console.log(data);
                // Remplir le formulaire modal avec les données du district
                $('#edit_code').val(data.code); // Utilisez l'ID du champ d'édition
                $('#edit_libelle').val(data.libelle);
                $('#edit_id_district').val(data.sous_prefecture.departement.region.district.code);
                $('#edit_id_region').val(data.sous_prefecture.departement.region.code);
                $('#edit_id_departement').val(data.sous_prefecture.departement.code);
                $('#edit_id_sous_prefecture').val(data.sous_prefecture.code);
                // Assurez-vous que les champs select sont correctement préremplis
                $('#edit_id_district').trigger('change');
                $('#edit_id_region').trigger('change');
                $('#edit_id_departement').trigger('change');
                $('#edit_id_sous_prefecture').trigger('change');
            }
        });
    }

    function deleteLocalite(code) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cette localité ?")) {
            $.ajax({
                url: '/admin/localite/delete/' + code
                , method: 'DELETE', // Utilisez la méthode DELETE pour la suppression
                data: {
                    _token: '{{ csrf_token() }}' // Assurez-vous d'envoyer le jeton CSRF
                }
                , success: function(response) {
                    var message = "localité supprimé avec succès.";
                    showPopup(message);
                    // Rechargez la page actuelle en ignorant le cache du navigateur
                    window.location.reload(true);

                }
                , error: function() {
                    // Gérer les erreurs de la requête AJAX
                    console.log('Erreur lors de la suppression de la localité.');
                }
            });
        }
    }

</script>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste des localités')
        $('#code').on('input', function() {
            // Get the input value
            var code = $(this).val();

            // Send an AJAX request to check if the code already exists
            $.ajax({
                url: '/check-localite-code', // Replace with the actual URL in your Laravel routes
                method: 'POST'
                , data: {
                    _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                    code: code
                }
                , success: function(response) {
                    if (response.exists) {
                        $('#code').removeClass('is-valid').addClass('is-invalid');
                        $('#enregistrerLocalite').prop('disabled', true);
                    } else {
                        $('#code').removeClass('is-invalid').addClass('is-valid');
                        $('#enregistrerLocalite').prop('disabled', false);
                    }
                }
            });
        });

        // Lorsque la sélection du district change
        function updateRegions(selectElement) {
            var selectedDistrict = selectElement.val();

            // Effectuez une requête AJAX pour obtenir les régions
            $.ajax({
                type: 'GET'
                , url: '/admin/get-regions/' + selectedDistrict
                , success: function(data) {
                    console.log(data);
                    var regionSelect = selectElement.closest('.modal').find('.region-select');
                    regionSelect.empty(); // Effacez les options précédentes

                    // Ajoutez les options des régions récupérées
                    $.each(data.regions, function(key, value) {
                        regionSelect.append($('<option>', {
                            value: key
                            , text: value
                        }));
                    });
                    $('#id_region').trigger('change');
                }
            });
        }

        // Lorsque la sélection de la région change
        function updateDepartement(selectElement) {
            var selectedRegion = selectElement.val();

            // Effectuez une requête AJAX pour obtenir les régions
            $.ajax({
                type: 'GET'
                , url: '/admin/get-departements/' + selectedRegion
                , success: function(data) {
                    console.log(data);
                    var departementSelect = selectElement.closest('.modal').find('.departement-select');
                    departementSelect.empty(); // Effacez les options précédentes

                    // Ajoutez les options des régions récupérées
                    $.each(data.departements, function(key, value) {
                        departementSelect.append($('<option>', {
                            value: key
                            , text: value
                        }));
                    });
                    $('#id_departement').trigger('change');
                }
            });
        }
        // Lorsque la sélection de la région change
        function updateSous_prefecture(selectElement) {
            var selectedDepartement = selectElement.val();

            // Effectuez une requête AJAX pour obtenir les régions
            $.ajax({
                type: 'GET'
                , url: '/admin/get-sous_prefectures/' + selectedDepartement
                , success: function(data) {
                    console.log(data);
                    var departementSelect = selectElement.closest('.modal').find('.sous_prefecture-select');
                    departementSelect.empty(); // Effacez les options précédentes

                    // Ajoutez les options des régions récupérées
                    $.each(data.departements, function(key, value) {
                        departementSelect.append($('<option>', {
                            value: key
                            , text: value
                        }));
                    });
                    $('#id_sous_prefecture').trigger('change');
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

        // Lorsque la sélection du district change pour la modification
        $('#id_departement').on('change', function() {
            updateSous_prefecture($(this));
        });
        // Lorsque la sélection du district change pour la modification
        $('#edit_id_departement').on('change', function() {
            updateSous_prefecture($(this));
        });
        // Lorsque la sélection du district change pour la modification
        $('#id_region').on('change', function() {
            updateDepartement($(this));
        });
        $('#edit_id_region').on('change', function() {
            updateDepartement($(this));
        });
    });

</script>
@endsection
