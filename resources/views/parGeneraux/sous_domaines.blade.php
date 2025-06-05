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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion des infrastructures </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Domaines & Sous-domaines</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Sous-domaines</li>

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

             

                 <!-- // Basic multiple Column Form section start -->
                 <section id="multiple-column-form">
                     <div class="row match-height">
                         <div class="col-12">
                             <div class="card">
                                    <div class="card-header">
                                        <div  style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                                            <h5 class="card-title">
                                            Enregistrement d'un sous-domaine
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
                                            <form id="sous-domaine-form" method="POST" action="{{ route('sous_domaines.store') }}" data-parsley-validate>
                                                    @csrf
                                                    <input type="hidden" name="_method" id="form-method" value="POST">
                                                    <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                                                    <input type="hidden" id="code_edit" name="code_edit" value="">
                                                                                            <div class="row">
                                                    <div class="col">
                                                        <label class="form-label" for="code">domaine :</label>
                                                       <select class="form-select" name="domaine" id="domaine">
                                                            <option value="">Selectionnez un domaine</option>
                                                            @foreach ($domaines as $domaine)
                                                                <option value="{{ $codeDomaine = $domaine->code }}">{{ $domaine->libelle }}</option>
                                                            @endforeach
                                                       </select>
                                                    </div>
                                                     <div class="col">
                                                         <label class="form-label" for="code">Code domaine:</label>
                                                         <input type="text" class="form-control" id="code" name="code" placeholder="Code domaine" readonly required>
                                                     </div>
                                                     <div class="col">
                                                         <label class="form-label" for="code">Code sous-domaine:</label>
                                                         <input type="text" class="form-control" id="codeSousdomaine" name="code" placeholder="Code sous-domaine" required>
                                                     </div>
                                                     <div class="col">
                                                         <label class="form-label" for="libelle">Libelle sous-domaine:</label>
                                                         <input type="text" class="form-control" id="libelle"
                                                             name="libelle" placeholder="Libelle" required>
                                                     </div>
                                             </div>
                                            <br>
                                            @can("ajouter_ecran_".$ecran->id)
                                             <div class="text-end mt-3">
                                                <button type="button" class="btn btn-secondary" onclick="resetSousDomaineForm()">Annuler</button>

                                                 <input type="submit" class="btn btn-primary" style="" value="Enregistrer" id="enregistrerSousDomaine">
                                             </div>
                                             @endcan
                                        </form>

                                     </div>
                                 </div>
                         </div>
                     </div>
                 </section>
             
      

        <div class="col-12">
            <div class="card">
            <div class="card-header">
                <div  style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                    <h5 class="card-title">
                        Ajout d'un sous-domaine
                        <a href="#" data-toggle="modal" data-target="#sous-domaine-modal" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
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
                   <h5 class="card-title"> Liste des sous-domaines</h5>
                </div>
            </div>
                <div class="card-content">
                    <div class="card-body">


                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                            <thead>
                                <tr>
                                    <th>Code </th>
                                    <th>Libelle</th>
                                    <th>Domaine</th>
                                    <th>action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($sous_domaines as $sd)
                                <tr>
                                    <td>{{ $sd->code_sous_domaine }}</td>
                                    <td>{{ $sd->lib_sous_domaine }}</td>
                                    <td>{{ $sd->Domaine->libelle }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                                <span style="color: white"></span>
                                            </a>
                                            <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                                <li><a class="dropdown-item" href="#"  onclick="showEditSousDomaine('{{ $sd->code_sous_domaine }}')"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                                <li><a class="dropdown-item" href="#"  onclick="deleteSousDomaine('{{ $sd->code_sous_domaine }}')"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
                                               
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
$(document).ready(function () {
    // Form submit (création ou mise à jour)
    $('#sous-domaine-form').on('submit', function (e) {
        e.preventDefault();
        let url = $('#form-method').val() === 'PUT' 
            ? '{{ route("sous_domaines.update") }}'
            : '{{ route("sous_domaines.store") }}';

        $.ajax({
            url: url,
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                alert(response.success);
                location.reload(); // recharge la liste
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    let messages = '';

                    for (const field in errors) {
                        messages += errors[field].join(' ') + '\n';
                    }

                    alert(messages, 'error'); // Affiche toutes les erreurs
                } else {
                    alert("Une erreur inconnue est survenue.", 'error');
                }
            }

        });
    });

    // Événement modification
    window.showEditSousDomaine = function (code) {
        $.ajax({
            url: '{{ url("/")}}/admin/sous-domaines/' + code,
            type: 'GET',
            success: function (data) {
                $('#code_edit').val(data.code_sous_domaine);
                $('#codeSousdomaine').val(data.code_sous_domaine).prop('readonly', true);
                $('#libelle').val(data.lib_sous_domaine);
                $('#domaine').val(data.code_domaine);
                $('#code').val(data.code_domaine);
                $('#form-method').val('PUT');
                $('#sous-domaine-form').attr('action', '{{ route("sous_domaines.update") }}');
                $('#enregistrerSousDomaine').val('Modifier');
            },
            error: function (xhr) {
                alert(xhr.responseJSON?.error || 'Erreur lors du chargement.', 'error');
            }
        });
    };

    // Réinitialisation
    window.resetSousDomaineForm = function () {
        $('#sous-domaine-form')[0].reset();
        $('#codeSousdomaine').prop('readonly', false);
        $('#code_edit').val('');
        $('#form-method').val('POST');
        $('#sous-domaine-form').attr('action', '{{ route("sous_domaines.store") }}');
        $('#enregistrerSousDomaine').val('Enregistrer');
    };
});
</script>


<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur->libelle_court }} {{ auth()->user()->acteur->libelle_long }}', 'table1', 'Liste des sous-domaines');

          $('#code').on('input', function() {
                  // Get the input value
                  var code = $(this).val();

                  // Send an AJAX request to check if the code already exists
                  $.ajax({
                      url: '{{ url("/")}}/check-sous-domaines-code', // Replace with the actual URL in your Laravel routes
                      method: 'POST',
                      data: {
                          _token: '{{ csrf_token() }}', // Add CSRF token for Laravel
                          code: code
                      },
                      success: function(response) {
                          if (response.exists) {
                              $('#code').removeClass('is-valid').addClass('is-invalid');
                              $('#enregistrerSousDomaine').prop('disabled', true);
                          } else {
                              $('#code').removeClass('is-invalid').addClass('is-valid');
                               $('#enregistrerSousDomaine').prop('disabled', false);
                          }
                      }
                  });
              });

      });

    function deleteSousDomaine(code) {
        const url = `{{ url('/') }}/admin/sous-domaines/delete/${code}`;
        confirmDelete(url, () => window.location.reload(), {
            title: 'Supprimer ce sous-domaine ?',
            text: 'Voulez-vous vraiment le supprimer ? Cette action est irréversible.',
            successMessage: 'Le sous-domaine a été supprimé avec succès.',
            errorMessage: 'Échec lors de la suppression du sous-domaine.'
        });
    }



  </script>
  <script>
    $(document).ready(function() {
        // Lorsque la sélection dans le champ "domaine" change
        $('#domaine').change(function() {
            // Obtenez la valeur sélectionnée dans le champ "domaine"
            var selectedDomaine = $(this).val();

            // Mettez à jour la valeur du champ "code" avec le code correspondant au domaine sélectionné
            $('#code').val(selectedDomaine);
        });
        $('#domaine').change(function() {
            // Obtenez la valeur sélectionnée dans le champ "domaine"
            var selectedDomaine = $(this).val();

            // Mettez à jour la valeur du champ "code" avec le code correspondant au domaine sélectionné
            $('#codeSousdomaine').val(selectedDomaine);
        });
    });
</script>
@endsection
