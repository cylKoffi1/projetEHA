<!-- resources/views/users/create.blade.php -->

@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
    $('#alertMessage').text("{{ session('success') }}");
    $('#alertModal').modal('show');
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion des infrastructures </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Domaines & Sous-domaines</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Domaines</li>

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
    <section id="multiple-column-form">
        <div class="row match-height">
            <div class="col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="card-title"> Ajout de domaine</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <form class="form" id="domaine-form" method="POST" action="{{ route('domaine.store') }}" data-parsley-validate>
                                @csrf
                                <input type="hidden" name="_method" id="form-method" value="POST"> {{-- méthode POST ou PUT --}}
                                <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                                <input type="hidden" name="code_original" id="code_original" value=""> {{-- utile pour la MAJ --}}

                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group mandatory">
                                            <label class="form-label" for="code">Code :</label>
                                            <input type="text" class="form-control" id="code" name="code" placeholder="Code du domaine" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="form-group mandatory">
                                            <label class="form-label" for="libelle">Libellé :</label>
                                            <input type="text" class="form-control" id="libelle" name="libelle" placeholder="Libellé" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end mt-3">
                                    <button type="submit" class="btn btn-primary" id="btn-save">Enregistrer</button>
                                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <div class="row match-height">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    
                       
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    <div style="text-align: center;">
                       <h5 class="card-title"> Liste des domaines</h5>
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
                                @foreach ($domaines as $domaine)
                                <tr>
                                    <td>{{ $domaine->code }}</td>
                                    <td>{{ $domaine->libelle }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                                <span style="color: white"></span>
                                            </a>
                                            <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                                <li><a class="dropdown-item" href="#" onclick="showEditDomaine('{{ $domaine->code }}')"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="deleteDomaine('{{ $domaine->code }}')"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
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
$('#domaine-form').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const url = form.attr('action');
    const isUpdate = $('#form-method').val() === 'PUT';

    const formData = form.serializeArray();

    if (isUpdate) {
        formData.push({ name: '_method', value: 'PUT' });
    }

    $.ajax({
        url: url,
        type: 'POST', // <-- Toujours POST
        data: $.param(formData),
        success: function(response) {
            alert(response.success || "Enregistrement réussi.");
            location.reload();
        },
        error: function(xhr) {
            if (xhr.responseJSON && xhr.responseJSON.error) {
                alert(xhr.responseJSON.error);
            } else {
                alert("Une erreur inconnue est survenue.");
            }
        }
    });
});


</script>
<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur->libelle_court }} {{ auth()->user()->acteur->libelle_long }}', 'table1', 'Liste des domaines')

        $('#code').on('input', function() {
            // Get the input value
            var code = $(this).val();

            // Send an AJAX request to check if the code already exists
            $.ajax({
                url: '{{ url("/")}}/check-domaine-code', // Replace with the actual URL in your Laravel routes
                method: 'POST'
                , data: {
                    _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                    code: code
                }
                , success: function(response) {
                    if (response.exists) {
                        $('#code').removeClass('is-valid').addClass('is-invalid');
                        $('#enregistrerDomaine').prop('disabled', true);
                    } else {
                        $('#code').removeClass('is-invalid').addClass('is-valid');
                        $('#enregistrerDomaine').prop('disabled', false);
                    }
                }
            });
        });

    });
    // Lorsque l'utilisateur clique sur un bouton "Modifier"
    function showEditDomaine(code) {
        $.ajax({
            url: '{{ url("/")}}/admin/domaine/' + code,
            method: 'GET',
            success: function(data) {
                $('#code').val(data.code).prop('readonly', true); // empêche la modification du code
                $('#libelle').val(data.libelle);
                $('#code_original').val(data.code); // pour l’identification à la mise à jour
                $('#domaine-form').attr('action', "{{ route('domaine.update') }}"); // vers la route de MAJ
                $('#form-method').val('PUT'); // méthode HTTP
                $('#btn-save').text('Modifier');
            },
            error: function(err) {
                alert("Erreur lors du chargement du domaine.");
            }
        });
    }

    function resetForm() {
        $('#domaine-form').trigger('reset');
        $('#code').prop('readonly', false);
        $('#domaine-form').attr('action', "{{ route('domaine.store') }}");
        $('#form-method').val('POST');
        $('#btn-save').text('Enregistrer');
    }


    function deleteDomaine(code) {
        const url = `{{ url("/") }}/admin/domaine/delete/${code}`;
        confirmDelete(url, () => window.location.reload(), {
            title: 'Supprimer ce domaine ?',
            text: 'Voulez-vous vraiment le supprimer ? Cette action est irréversible.',
            successMessage: 'Le domaine a été supprimé avec succès.',
            errorMessage: 'Échec lors de la suppression du domaine.'
        });
    }

</script>
@endsection
