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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Liste des personnes </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">Tableau de bord</a></li>

                            <li class="breadcrumb-item active" aria-current="page">Personnel</li>

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
                            Ajout d'une personne
                            <a  href="{{ route('personnel.create') }}?ecran_id={{ $ecran->id }}" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
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
                        <h5 class="card-title"> Liste des personnes</h5>
                    </div>
                </div>
                <div class="card-body" style=" padding-top: 7px">
                    <table class="table table-striped table-bordered" class="display" cellspacing="0" style="width: 100%;" id="table1">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Code</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Addresse</th>
                                <th>Fonction</th>
                                <th>Structure</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($personnel as $user)
                            <tr style="border: none !important;">
                                <td>
                                    @if ($user->photo)
                                    <img style="width: 40px; height: 40px; border-radius: 50px;" src="{{ asset("users/".$user->photo) }}" alt="Photo">
                                    @else
                                    <img style="width: 40px; height: 40px; border-radius: 50px; font-weight: bold;" src="{{ asset("users/user.png") }}" alt="Photo">
                                    @endif
                                </td>
                                <td>{{ $user->code_personnel }}</td>
                                <td>{{ $user->nom }}</td>
                                <td>{{ $user->prenom }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->telephone }}</td>
                                <td>{{ $user->addresse }}</td>
                                <td>
                                    @if ($user->latestFonction)
                                        {{ $user->latestFonction->fonctionUtilisateur->libelle_fonction }}
                                    @endif
                                </td>

                                <td>
                                    @foreach ($structureRattachement as $structure)
                                        @if ($structure->code_personnel == $user->code_personnel)
                                            @if ($structure->type_structure == 'bailleurss')
                                                {{ $bailleurs->where('code_bailleur', $structure->code_structure)->first()->libelle_long }}
                                            @elseif ($structure->type_structure == 'agence_execution')
                                                {{ $agences->where('code_agence_execution', $structure->code_structure)->first()->nom_agence }}
                                            @elseif ($structure->type_structure == 'ministere')
                                                {{ $ministeres->where('code', $structure->code_structure)->first()->libelle }}
                                            @endif
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                            <span style="color: white"></span>
                                        </a>
                                        <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                            @if (auth()->user()->code_personnel==$user->code_personnel)
                                            <li><a class="dropdown-item" href="/admin/users/get-user/{{ auth()->user()->id }}?ecran_id={{ $ecran->id }}"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                            @else
                                            <li><a class="dropdown-item" href="/admin/personnel/get-personne/{{ $user->code_personnel }}?ecran_id={{ $ecran->id }}"><i class="bi bi-pencil-square me-3"></i> Modifier</a></li>
                                            @endif
                                            <li><a class="dropdown-item" onclick="deleteUser('{{ $user->code_personnel }}')" href="#"> <i class="bi bi-trash3-fill me-3"></i> Supprimer</a></li>
                                            @if (auth()->user()->code_personnel==$user->code_personnel)
                                            <li><a class="dropdown-item" href="/admin/users/details-user/{{ auth()->user()->id }}?ecran_id={{ $ecran->id }}"><i class="bi bi-plus-circle me-3"></i> Détails</a></li>
                                            @else
                                            <li><a class="dropdown-item" href="/admin/personnel/details-personne/{{ $user->code_personnel }}?ecran_id={{ $ecran->id }}"><i class="bi bi-plus-circle me-3"></i> Détails</a></li>
                                            @endif

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
</section>


<script>
    function deleteUser(code_personnel) {
        if (confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) {
            // Envoyer une requête AJAX pour supprimer l'utilisateur
            $.ajax({
                type: 'DELETE',
                url: '/admin/personnel/' + code_personnel,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Rediriger vers une autre page ou rafraîchir la page actuelle si nécessaire
                    $('#alertMessage').text("Utilisateur avec le code " + code_personnel + " supprimé avec succès.");
                    $('#alertModal').modal('show');
                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    alert('Une erreur s\'est produite lors de la suppression de l\'utilisateur.');
                }
            });
        }
    }

    function getBase64Image(imgUrl) {
        return new Promise((resolve, reject) => {
            var img = new Image();
            img.crossOrigin = 'Anonymous'; // Enable CORS if needed
            img.onload = function() {
                var canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, img.width, img.height);
                var dataURL = canvas.toDataURL('image/png');
                resolve(dataURL);
            };
            img.onerror = function(error) {
                reject(error);
            };
            img.src = imgUrl;
        });
    }


    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Liste du personnel')
    });

</script>





@endsection
