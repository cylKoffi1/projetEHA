@extends('layouts.app')


@section('content')


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
                        <li class="breadcrumb-item active" aria-current="page">Pays</li>

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
                    Ajout d'un pays
                    @can("ajouter_ecran_" . $ecran->id)
                        <a  href="#" data-toggle="modal" data-target="#paysModal" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
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
                <h5 class="card-title"> Liste des pays</h5>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Pays</th>
                        <th>Code tel</th>
                        <th>Armoirie</th>
                        <th>Drapeau</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($pays as $p)
                    <tr>
                        <td>{{ $p->code }}</td>
                        <td>{{ $p->nom_fr_fr }}</td>
                        <td>{{ $p->codeTel }}</td>
                        <td>
                            @if ($p->armoirie)
                                <img style="width: 30px; height: 30px;"
                                    src="{{ asset('storage/' . $p->armoirie) }}"
                                    alt="Armoirie du pays">
                            @endif
                        </td>

                        <td>
                            @if ($p->flag)
                                <img style="width: 30px; height: 30px;"
                                    src="{{ asset('storage/' . $p->flag) }}"
                                    alt="Drapeau du pays">
                            @endif
                        </td>

                        <td>
                            <div class="dropdown">
                                <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                    <span style="color: white"></span>
                                </a>
                                <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                    <li>
                                        <a class="dropdown-item edit-button" href="#"
                                        data-id="{{ $p->id }}"
                                        data-code="{{ $p->code }}"
                                        data-alpha2="{{ $p->alpha2 }}"
                                        data-alpha3="{{ $p->alpha3 }}"
                                        data-nom-en-gb="{{ $p->nom_en_gb }}"
                                        data-nom-fr-fr="{{ $p->nom_fr_fr }}"
                                        data-code-tel="{{ $p->codeTel }}">
                                            <i class="bi bi-pencil-square me-3"></i> Modifier
                                        </a>
                                    </li>
                                    <!-- Lien pour la suppression -->
                                    <li>
                                        <a class="dropdown-item delete-button" href="#"
                                        data-id="{{ $p->id }}">
                                            <i class="bi bi-trash3-fill me-3"></i> Supprimer
                                        </a>
                                    </li>
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
    <div class="modal fade" id="paysModal" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Enregistrement de Pays</h5>
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
                                            <form class="form" method="POST" enctype="multipart/form-data" data-parsley-validate action="{{ route('pays.store') }}">
                                                @csrf
                                                <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                                                <div class="row">
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group mandatory">
                                                            <label class="form-label" for="alpha2">Code
                                                                :</label>
                                                            <input type="text" class="form-control" id="code" name="code" placeholder="Code" required>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group mandatory">
                                                            <label class="form-label" for="alpha2">Code alpha-2
                                                                :</label>
                                                            <input type="text" class="form-control" id="alpha2" name="alpha2" placeholder="Alpha-2" required>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label class="form-label" for="alpha3">Code alpha-3
                                                                :</label>
                                                            <input type="text" placeholder="Code alpha-3" class="form-control" id="alpha3" name="alpha3" required>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label class="form-label" for="nom_en_gb">Nom (en anglais)
                                                                :</label>
                                                            <input type="text" class="form-control" id="nom_en_gb" name="nom_en_gb" placeholder="Nom anglais" required>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group">
                                                            <label class="form-label" for="nom_fr_fr">Nom (en français)
                                                                :</label>
                                                            <input type="text" class="form-control" id="nom_fr_fr" name="nom_fr_fr" placeholder="Nom français" required>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group mandatory">
                                                            <label for="codeTel">Code téléphonique :</label>
                                                            <input type="text" class="form-control" id="codeTel" placeholder="Code téléphonique" name="codeTel">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group mandatory">
                                                            <label for="armoirie" class="form-label">Armoirie</label>
                                                            <input class="form-control" type="file" id="armoirie" name="armoirie">

                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-12">
                                                        <div class="form-group mandatory">
                                                            <label for="drapeaux" class="form-label">Drapeaux</label>
                                                            <input class="form-control" type="file" id="drapeaux" name="flag">

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                                    <button type="submit" class="btn btn-primary" id="enregistrerPays">Enregistrer</button>
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
    <!-- Modal de modification -->
    <div class="modal fade" id="paysEditModal" tabindex="-1" role="dialog" aria-labelledby="editModalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalTitle">Modifier un Pays</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="edit-pays-form" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit-ecran-id" name="ecran_id" value="{{ $ecran->id }}">
                        <input type="hidden" id="edit-pays-id" name="id">
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit-code">Code :</label>
                                    <input type="text" class="form-control" id="edit-code-update" name="code-update" placeholder="Code" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label class="form-label" for="edit-alpha2">Code alpha-2 :</label>
                                    <input type="text" class="form-control" id="edit-alpha2-update" name="alpha2-update" placeholder="Alpha-2" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="form-label" for="edit-alpha3">Code alpha-3 :</label>
                                    <input type="text" placeholder="Code alpha-3" class="form-control" id="edit-alpha3-update" name="alpha3-update" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="form-label" for="edit-nom-en-gb">Nom (en anglais) :</label>
                                    <input type="text" class="form-control" id="edit-nom-en-gb-update" name="nom_en_gb-update" placeholder="Nom anglais" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label class="form-label" for="edit-nom-fr-fr">Nom (en français) :</label>
                                    <input type="text" class="form-control" id="edit-nom-fr-fr-update" name="nom_fr_fr-update" placeholder="Nom français" required>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label for="edit-code-tel">Code téléphonique :</label>
                                    <input type="text" class="form-control" id="edit-code-tel-update" name="codeTel-update" placeholder="Code téléphonique">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label for="edit-armoirie">Armoirie</label>
                                    <input class="form-control" type="file" id="edit-armoirie-update" name="armoirie-update">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group mandatory">
                                    <label for="edit-drapeaux">Drapeaux</label>
                                    <input class="form-control" type="file" id="edit-drapeaux-update" name="flag-update">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de suppression -->
    <div class="modal fade" id="paysDeleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalTitle">Confirmer la suppression</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer ce pays ?</p>
                    <form id="delete-pays-form" method="POST">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger" form="delete-pays-form">Supprimer</button>
                </div>
            </div>
        </div>
    </div>


</section>

<script>
    $(document).ready(function () {
        $('.edit-button').click(function () {
            const pays = $(this).data();
            $('#edit-pays-id-update').val(pays.id);
            $('#edit-code-update').val(pays.code);
            $('#edit-alpha2-update').val(pays.alpha2);
            $('#edit-alpha3-update').val(pays.alpha3);
            $('#edit-nom-en-gb-update').val(pays.nomEnGb);
            $('#edit-nom-fr-fr-update').val(pays.nomFrFr);
            $('#edit-code-tel-update').val(pays.codeTel);

            $('#edit-pays-form').attr('action', `/pays/${pays.id}/update`);

            $('#paysEditModal').modal('show');
        });

        $('.delete-button').click(function () {
            const id = $(this).data('id');
            $('#delete-pays-form').attr('action', `/pays/${id}`);
            $('#paysDeleteModal').modal('show');
        });
    });
</script>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur->code_acteur }} {{ auth()->user()->acteur->libelle_long }}', 'table1', 'Liste des pays')
        $('#code').on('input', function() {
            // Get the input value
            var code = $(this).val();

            // Send an AJAX request to check if the code already exists
            $.ajax({
                url: '/check-pays-code', // Replace with the actual URL in your Laravel routes
                method: 'POST'
                , data: {
                    _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                    code: code
                }
                , success: function(response) {
                    if (response.exists) {
                        $('#code').removeClass('is-valid').addClass('is-invalid');
                        $('#enregistrerPays').prop('disabled', true);
                    } else {
                        $('#code').removeClass('is-invalid').addClass('is-valid');
                        $('#enregistrerPays').prop('disabled', false);
                    }
                }
            });
        });
    });

</script>
@endsection
