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
                        <li class="breadcrumb-item"><a href="">Paramètre généraux</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Fonction groupe utilisateur</li>

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
                    Ajout de groupes à une fonction
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
                <h5 class="card-title"> Liste des Fonction par groupe utilisateur</h5>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Fonction</th>
                        <th>Groupe utilisateur</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($fonctionGroupe as $p)
                    <tr>
                        <td>{{ $p->code }}</td>
                        <td>
                            @if ($p->fonction)
                            {{ $p->fonction->libelle_fonction }}
                            @else
                            N/A
                            @endif
                        </td>
                        <td>
                            @if ($p->groupeUtilisateur)
                            {{ $p->groupeUtilisateur->name }}
                            @else
                            N/A
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                    <span style="color: white"></span>
                                </a>
                                <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="#" onclick="deleteFonctionGroupe('{{ $p->code }}')"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
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
                <h5 class="modal-title" id="modalTitle">Ajout de groupes à une fonction</h5>
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
                                        <form class="form" method="POST" id="form-fg" data-parsley-validate action="{{ route('fg.store') }}">
                                            @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group mandatory">
                                                        <label class="form-label" for="alpha2">Fonction
                                                            :</label>
                                                        <select id="fonction" name="fonction" class="form-select">
                                                            <option value="">--- ---</option>
                                                            @foreach ($fonctions as $sd)
                                                            <option value="{{ $sd->code }}">{{ $sd->libelle_fonction }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="form-group">
                                                        <label for="sous_domaine">Groupes :</label>
                                                        <select id="groupes" name="groupes" multiple>
                                                            @foreach ($groupes as $sd)
                                                            <option value="{{ $sd->id }}">{{ $sd->name }}</option>
                                                            @endforeach
                                                        </select>
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


</section>



<script>
    /* CODE JAVASCRIPT ICI */

    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste de la table');
        var groupes = $('#groupes').filterMultiSelect({

            // displayed when no options are selected
            placeholderText: "0 sélection",

            // placeholder for search field
            filterText: "Filtrer",

            // Select All text
            selectAllText: "Tout sélectionner",

            // Label text
            labelText: "",

            // the number of items able to be selected
            // 0 means no limit
            selectionLimit: 0,

            // determine if is case sensitive
            caseSensitive: false,

            // allows the user to disable and enable options programmatically
            allowEnablingAndDisabling: true,

        });

        $('#fonction').on('change', function() {
            var selectedFonction = $(this).val();
            groupes.enable();

            $.ajax({
                type: "GET"
                , url: "/admin/get-groupes/" + selectedFonction
                , success: function(data) {
                    $.each(data.gr, function(key, value) {
                        groupes.enableOption(key);
                    });
                    $.each(data.groupes, function(key, value) {
                        groupes.disableOption(key);
                    });
                }
            , });
        })

        $('#form-fg').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission
            var formData = new FormData(this);
            formData.append("groupes", groupes.getSelectedOptionsAsJson());

            var url = '/admin/fg/store/';

            $.ajax({
                url: url
                , type: 'POST'
                , data: formData
                , contentType: false, // Don't set content type (let jQuery handle it)
                processData: false, // Don't process data (let jQuery handle it)
                success: function(response) {
                    showPopup(response.success);
                    console.log(response.donnees);
                    // Rediriger l'utilisateur après une requête réussie
                    window.location.reload();
                }
                , error: function(xhr, status, error) {
                    var err = JSON.parse(xhr.responseText);
                    console.log(err); // Affichez les détails de l'erreur côté serveur dans la console
                    showPopup('Une erreur est survenue !');
                }
            });
        });
    });

    function deleteFonctionGroupe(code) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cette fonction groupe ?")) {
            $.ajax({
                url: '/admin/fonctionGroupe/delete/' + code
                , method: 'DELETE', // Utilisez la méthode DELETE pour la suppression
                data: {
                    _token: '{{ csrf_token() }}' // Assurez-vous d'envoyer le jeton CSRF
                }
                , success: function(response) {
                    showPopup(response.success);
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
