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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Utilisateurs </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">Utilisateurs</a></li>

                            <li class="breadcrumb-item active" aria-current="page">liste utilisateurs</li>

                        </ol>
                    </nav>
                    <div class="row">
                        <span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-right: 10px;"></span>
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
            @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    <div class="row match-height">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                        @can("ajouter_ecran_" . $ecran->id)
                        <h5 class="card-title">
                            Ajout d'un utilisateur
                            <a href="{{ route('users.create') }}?ecran_id={{ $ecran->id }}" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
                        </h5>
                        @endcan


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
                        <h5 class="card-title"> Liste des utilisateurs</h5>
                    </div>
                </div>
                <div class="card-body" style=" padding-top: 7px">
                    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Photo</th>
                                <th>Nom utilisateur</th>
                                <th>Fonction</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($users as $user)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" class="form-check-input checkbox-selection" data-code="{{ $user->id }}">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($user->personnel->photo)
                                    <img style="width: 40px; height: 40px; border-radius: 50px;" src="{{ asset("users/".$user->personnel->photo) }}" alt="Photo">
                                    @else
                                    <img style="width: 40px; height: 40px; border-radius: 50px;" src="{{ asset("users/user.png") }}" alt="Photo">
                                    @endif
                                </td>
                                <td>{{ $user->login }}</td>
                                @if ($user->latestFonction && $user->latestFonction->fonctionUtilisateur)
                                <td>{{ $user->latestFonction->fonctionUtilisateur->libelle_fonction }}</td>
                                @else
                                <td></td>
                                @endif
                                <td>{{ $user->personnel->email }}</td>
                                <td>{{ $user->personnel->telephone }}</td>
                                <td>
                                    <div class="dropdown">
                                        <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                            <span style="color: white"></span>
                                        </a>
                                        <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                            @can("modifier_ecran_" . $ecran->id)
                                            <li><a class="dropdown-item" href="/admin/users/get-user/{{ $user->id }}?ecran_id={{ $ecran->id }}"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                            @endcan
                                            @can("supprimer_ecran_" . $ecran->id)
                                            <a class="dropdown-item" onclick="deleteUser(['{{ $user->id }}'])" href="#"><i class="bi bi-trash3-fill me-3"></i> Supprimer</a>
                                            @endcan
                                            @can("supprimer_ecran_" . $ecran->id)
                                            <li><a class="dropdown-item" href="/admin/users/details-user/{{ $user->id }}?ecran_id={{ $ecran->id }}"><i class="bi bi-plus-circle me-3"></i> Détails</a></li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @can("supprimer_ecran_" . $ecran->id)
                    <button type="button" id="deleteSelectedRows" class="btn btn-danger" style="float: right; margin-top: 15px;">Supprimer les lignes sélectionnées</button>
                    @endcan
                </div>
            </div>

        </div>
    </div>
</section>


<script>
function deleteUser(id) {
    // Confirmez avec l'utilisateur s'il veut vraiment supprimer les lignes
    if (!confirm('Voulez-vous vraiment supprimer cet utilisateur ?')) {
        return;
    }

    $.ajax({
        url: '/admin/delete-user/' + id,
        method: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            // Réponse réussie de votre endpoint
            $('#alertMessage').text("Utilisateur avec le code " + id + " supprimé avec succès.");
            $('#alertModal').modal('show');

            // Actualiser la page après la suppression
            location.reload();
        },
        error: function(error) {
            console.error('Erreur lors de la suppression de l\'utilisateur avec le code ' + id + ':', error);
            // Gérez les erreurs ici
        }
    });
}

    $(document).ready(function() {

        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste des utilisateurs')


        $('#deleteSelectedRows').on('click', function() {
            // Récupérez les cases à cocher sélectionnées
            var selectedRows = $('.checkbox-selection:checked');

            // Récupérez les valeurs (codes) associées aux cases à cocher sélectionnées
            var selectedCodes = selectedRows.map(function() {
                return $(this).data('code');
            }).get();

            // Effectuez l'action souhaitée, par exemple, supprimez les lignes avec les codes sélectionnés
            if (selectedCodes.length > 0) {
                // Appelez votre fonction de suppression avec les codes sélectionnés
                deleteSelectedRows(selectedCodes);
            } else {
                alert('Aucune ligne sélectionnée.');
            }
        });


        // Fonction de suppression
        function deleteSelectedRows(selectedCodes) {
            // Vérifiez s'il y a des lignes sélectionnées
            if (selectedCodes.length === 0) {
                alert('Aucune ligne sélectionnée.');
                return;
            }

            // Confirmez avec l'utilisateur s'il veut vraiment supprimer les lignes
            if (!confirm('Voulez-vous vraiment supprimer les lignes sélectionnées?')) {
                return;
            }

            // Parcourez les codes sélectionnés et effectuez une requête Ajax pour supprimer chaque utilisateur
            selectedCodes.forEach(function(code) {
                $.ajax({
                    url: '/admin/delete-user/' + code, // Remplacez par l'URL correcte de votre endpoint de suppression
                    method: 'DELETE', // Utilisez la méthode HTTP appropriée (peut être POST ou autre en fonction de votre API)
                    data: {
                        _token: '{{ csrf_token() }}' // Assurez-vous d'envoyer le jeton CSRF
                    }, // Envoyez les données nécessaires pour identifier l'utilisateur à supprimer
                    success: function(response) {
                        // Réponse réussie de votre endpoint
                        console.log('Utilisateur avec le code ' + code + ' supprimé avec succès.');
                        // Vous pouvez mettre à jour la table ou effectuer d'autres actions si nécessaire
                    }
                    , error: function(error) {
                        console.error('Erreur lors de la suppression de l\'utilisateur avec le code ' + code + ':', error);
                        // Gérez les erreurs ici
                    }
                });
            });
        }



    });

</script>

@endsection
