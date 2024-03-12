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
                        <li class="breadcrumb-item active" aria-current="page">Etablissements</li>

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
                    Ajout d'un établissement
                    <a  href="#" data-toggle="modal" data-target="#bailleur-modal" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
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
                <h5 class="card-title"> Liste des établissements</h5>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" id="table1">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Nom court</th>
                        <th>Accessibilité</th>
                        <th>Genre</th>
                        <th>Type</th>
                        <th>Niveaux</th>
                        <th>Localité</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($etablissements as $p)
                    <tr>
                        <td>{{ $p->code }}</td>
                        <td>{{ $p->nom_etablissement }}</td>
                        <td>{{ $p->nom_court }}</td>
                        <td>{{ $p->accessibilite ? $p->accessibilite->libelle : ''}}</td>
                        <td>{{ $p->genre ? $p->genre->libelle_genre : ''}}</td>
                        <td>{{ $p->niveaux ? $p->niveaux->typeEtablissement->libelle : ''}}</td>
                        <td>{{ $p->niveaux ? $p->niveaux->libelle_long : ''}}</td>
                        <td>{{ $p->localite ? $p->localite->localite : '' }}</td>

                        <td>
                            <div class="dropdown">
                                <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                    <span style="color: white"></span>
                                </a>
                                <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" onclick="showEditEtablissement('{{ $p->code }}')" href="#"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                    <li><a class="dropdown-item" onclick="deleteEtablissement('{{ $p->code }}')" href="#"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
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
    <div class="modal fade" id="bailleur-modal" tabindex="-1"  role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Enregistrement d'un etablissement</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>etablissement
                </div>
                <div class="modal-body">
                    <form class="form" method="POST" action="{{ route('etablissement.store') }}" data-parsley-validate>
                        @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="code">Code :</label>
                                    <input type="text" class="form-control" id="code" name="code" placeholder="Code de l'etablissement" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Nom :</label>
                                    <input type="text" class="form-control" id="nom_etablissement" name="nom_etablissement" placeholder="Nom de l'etablissement" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Nom court:</label>
                                    <input type="text" class="form-control" id="nom_court" name="nom_court" placeholder="Nom de l'etablissement" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Public :</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="public" name="public">
                                        <label class="form-check-label" for="public">Etablissement public</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="code_genre">Genre :</label>
                                    <select class="form-select" id="code_genre" name="code_genre" required>
                                        <option value="">Sélectionner le genre</option>
                                        @foreach ($genres as $genre)
                                        <option value="{{ $genre->code_genre }}">{{ $genre->libelle_genre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="id_tb">Localité :</label>
                                    <select class="form-select" id="code_localite" name="code_localite" required>
                                        <option value="">Sélectionner la localité</option>
                                        @foreach ($localites as $localite)
                                        <option value="{{ $localite->code }}">{{ $localite->localite }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="code_niveau">Type etablissement :</label>
                                    <select class="form-select" id="type_etablissement" name="type_etablissement" required>
                                        <option value="">Sélectionner le type</option>
                                        @foreach ($type_etablissements as $type)
                                        <option value="{{ $type->code }}">{{ $type->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="code_niveau">Niveaux etablissement :</label>
                                    <select class="form-select" id="code_niveau" name="code_niveau" required>
                                        <option value="">Sélectionner un niveaux</option>
                                        @foreach ($niveaux as $niveau)
                                        <option value="{{ $niveau->code }}">{{ $niveau->libelle_long }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <input type="submit" class="btn btn-primary" value="Enregistrer" id="enregistrerEtablissement">
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="edit-etablissement-modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Modification d'un etablissement</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>etablissement
                </div>
                <div class="modal-body">
                    <form class="form" method="POST" action="{{ route('etablissement.update') }}" data-parsley-validate>
                        @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="code">Code :</label>
                                    <input type="text" class="form-control" id="edit_code" name="edit_code" readonly placeholder="Code de l'etablissement" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Nom :</label>
                                    <input type="text" class="form-control" id="edit_nom_etablissement" name="edit_nom_etablissement" placeholder="Nom de l'etablissement" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Nom court:</label>
                                    <input type="text" class="form-control" id="edit_nom_court" name="edit_nom_court" placeholder="Nom de l'etablissement" >
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="libelle">Public :</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="edit_public" name="edit_public">
                                        <label class="form-check-label" for="edit_public">Etablissement public</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="code_genre">Genre :</label>
                                    <select class="form-select" id="edit_code_genre" name="edit_code_genre" required>
                                        <option value="">Sélectionner le genre</option>
                                        @foreach ($genres as $genre)
                                        <option value="{{ $genre->code_genre }}">{{ $genre->libelle_genre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="id_tb">Localité :</label>
                                    <select class="form-select" id="edit_code_localite" name="edit_code_localite" >
                                        <option value="">Sélectionner la localité</option>
                                        @foreach ($localites as $localite)
                                        <option value="{{ $localite->code }}">{{ $localite->localite }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="code_niveau">Type etablissement :</label>
                                    <select class="form-select" id="edit_type_etablissement" name="edit_type_etablissement" required>
                                        <option value="">Sélectionner le type</option>
                                        @foreach ($type_etablissements as $type)
                                        <option value="{{ $type->code }}">{{ $type->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="code_niveau">Niveaux etablissement :</label>
                                    <select class="form-select" id="edit_code_niveau" name="edit_code_niveau" required>
                                        <option value="">Sélectionner un niveaux</option>
                                        @foreach ($niveaux as $niveau)
                                        <option value="{{ $niveau->code }}">{{ $niveau->libelle_long }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <input type="submit" class="btn btn-primary" value="Enregistrer" id="edit_enregistrerEtablissement">
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
    function showEditEtablissement(code) {
        $('#edit-etablissement-modal').modal('show');
        $.ajax({
            type: 'GET'
            , url: '/admin/etablissement/' + code
            , success: function(data) {
                console.log(data);
                // Remplir le formulaire modal avec les données du district
                $('#edit_code').val(data.code); // Utilisez l'ID du champ d'édition
                $('#edit_nom_etablissement').val(data.nom_etablissement);
                $('#edit_nom_court').val(data.nom_court);
                $('#edit_type_etablissement').val(data.niveaux.code_type_etablissement);
                $('#edit_type_etablissement').trigger('change');
                $('#edit_code_genre').val(data.code_genre);
                $('#edit_code_localite').val(data.code_localite);
                if (data.public) {
                    $('#edit_public').prop('checked', true);
                } else {
                    $('#edit_public').prop('checked', false);
                }
                $('#edit_code_niveau').val(data.code_niveau);
                $('#edit_code_niveau').trigger('change');
            }
        });
    }

    function deleteEtablissement(code) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cet etablissement ?")) {
            $.ajax({
                url: '/admin/etablissement/delete/' + code
                , method: 'DELETE', // Utilisez la méthode DELETE pour la suppression
                data: {
                    _token: '{{ csrf_token() }}' // Assurez-vous d'envoyer le jeton CSRF
                }
                , success: function(response) {
                    var message = "etablissement supprimé avec succès.";
                    showPopup(message);
                    // Rechargez la page actuelle en ignorant le cache du navigateur
                    window.location.reload(true);

                }
                , error: function() {
                    // Gérer les erreurs de la requête AJAX
                    console.log("Erreur lors de la suppression de l'etablissement.");
                }
            });
        }
    }

</script>

<script>
    function updateNiveaux(selectElement, changeElementId) {
        var selectedType = selectElement.val();
        // Effectuez une requête AJAX pour obtenir les sous-domaines
        $.ajax({
            type: 'GET'
            , url: '/admin/get-niveaux/' + selectedType
            , success: function(data) {
                console.log(data);
                var sousNiveauxSelect = $("#" + changeElementId); // Correction: Utilisation de l'ID directement
                sousNiveauxSelect.empty(); // Effacez les options précédentes

                // Ajoutez les options des sous-domaines récupérés
                $.each(data.niveaux, function(key, value) {
                    sousNiveauxSelect.append($('<option>', {
                        value: key
                        , text: value
                    }));
                });

                sousNiveauxSelect.trigger('change');
            }
        });
    }
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste des établissements')
        $('#code').on('input', function() {
            // Get the input value
            var code = $(this).val();

            // Send an AJAX request to check if the code already exists
            $.ajax({
                url: '/check-etablissement-code', // Replace with the actual URL in your Laravel routes
                method: 'POST'
                , data: {
                    _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                    code: code
                }
                , success: function(response) {
                    if (response.exists) {
                        $('#code').removeClass('is-valid').addClass('is-invalid');
                        $('#enregistrerEtablissement').prop('disabled', true);
                    } else {
                        $('#code').removeClass('is-invalid').addClass('is-valid');
                        $('#enregistrerEtablissement').prop('disabled', false);
                    }
                }
            });
        });

        $('#type_etablissement').on('change', function() {
            updateNiveaux($(this), 'code_niveau');
        });
        $('#edit_type_etablissement').on('change', function() {
            updateNiveaux($(this), 'edit_code_niveau');
        });

    });

</script>
@endsection
