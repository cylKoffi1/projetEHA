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

    .card-header,
    .card-body,
    .list-group,
    .tab-pane,
    .row,
    .form-group,
    .list-group-item {
        background-color: #EAF2F8;
    }

</style>
<section id="multiple-column-form">
    <div class="page-heading">
        <div class="page-title">
            <div class="row" style="background-color: #DBECF8;">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>
                        Détails de la personne
                    </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">Tableau de bord</a></li>

                            <li class="breadcrumb-item active" aria-current="page">
                                Détails Personne
                            </li>

                        </ol>
                    </nav>
                    <div class="row" style="background-color: #DBECF8;">
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

            <div class="row" style="background-color: #DBECF8;">
                <!-- [ sample-page ] start -->
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header pb-0" style="background-color: white;">
                            <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#profile-1" role="tab" aria-selected="true">
                                        <i class="bi bi-person-lines-fill me-2"></i>Détails
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body" style="background-color: #EAF2F8;">
                            <div class="tab-content" style="background-color: #EAF2F8;">
                                <div class="tab-pane show active" id="profile-1" role="tabpanel" style="background-color: #EAF2F8;" aria-labelledby="profile-tab-1">
                                    <div class="row">
                                        <div class="col-lg-4 col-xxl-3">
                                            <div class="card">
                                                <div class="card-body position-relative">
                                                    <div class="text-center mt-3">
                                                        <div class="chat-avtar d-inline-flex mx-auto">
                                                            @if ($personne->photo)
                                                            <img class="rounded-circle img-fluid wid-70" style="width: 50px; height: 50px; border-radius: 50px;" src="{{ asset("users/".$personne->photo) }}" alt="User image">
                                                            @else
                                                            <img class="rounded-circle img-fluid wid-70" style="width: 50px; height: 50px; border-radius: 50px;" src="{{ asset("users/user.png") }}" alt="Photo">
                                                            @endif

                                                        </div>
                                                        {{-- <h5 class="mb-0">{{ $user->login }}</h5> --}}
                                                        {{-- <p class="text-muted text-sm">{{ $personne->latestFonction->fonctionUtilisateur->libelle_fonction }}</p> --}}
                                                        {{-- <hr class="my-3"> --}}
                                                        {{-- <div class="row g-3">
                                                            <div class="col-4">
                                                                <h5 class="mb-0">86</h5>
                                                                <small class="text-muted">Post</small>
                                                            </div>
                                                            <div class="col-4 border border-top-0 border-bottom-0">
                                                                <h5 class="mb-0">40</h5>
                                                                <small class="text-muted">Project</small>
                                                            </div>
                                                            <div class="col-4">
                                                                <h5 class="mb-0">4.5K</h5>
                                                                <small class="text-muted">Members</small>
                                                            </div>
                                                        </div> --}}
                                                        <hr class="my-3">
                                                        <div class="d-inline-flex align-items-center justify-content-between w-100 mb-3">
                                                            <i class="bi bi-envelope-at"></i>
                                                            <p class="mb-0">{{ $personne->email }}</p>
                                                        </div>
                                                        <div class="d-inline-flex align-items-center justify-content-between w-100 mb-3">
                                                            <i class="bi bi-phone"></i>
                                                            <p class="mb-0">(+225) {{ $personne->telephone }}</p>
                                                        </div>
                                                        <div class="d-inline-flex align-items-center justify-content-between w-100 mb-3">
                                                            <i class="bi bi-house"></i>
                                                            <p class="mb-0">{{ $personne->addresse }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-8 col-xxl-9">
                                            <div class="card">
                                                <div class="card-header">
                                                    <h5>Infos personnelles</h5>
                                                </div>
                                                <div class="card-body">
                                                    <ul class="list-group list-group-flush">
                                                        <li class="list-group-item px-0 pt-0">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-0 text-muted">Nom</p>
                                                                    <p class="mb-0">{{ $personne->nom }}</p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Prénom</p>
                                                                    <p class="mb-0">{{ $personne->prenom }}</p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Téléphone</p>
                                                                    <p class="mb-0">(+225) {{ $personne->telephone }}</p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Email</p>
                                                                    <p class="mb-0">{{ $personne->email }}</p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0 pt-0">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Groupe</p>
                                                                    <p class="mb-0">
                                                                        @if($user)
                                                                            {{ $user->roles->first()->name ?? '' }}
                                                                        @else
                                                                            Aucun groupe
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Fonction</p>
                                                                    <p class="mb-0">
                                                                        @if($user)
                                                                            {{ $user->latestFonction->fonctionUtilisateur->libelle_fonction }}
                                                                        @elseif ($personne)
                                                                            @if ( $personne->latestFonction)
                                                                                {{ $personne->latestFonction->fonctionUtilisateur->libelle_fonction }}
                                                                            @endif

                                                                        @else
                                                                            Aucune fonction
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0 pt-0">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Niveaux Accès</p>
                                                                    <p class="mb-0">

                                                                        @if($user)
                                                                            @if ($user->latestRegion)
                                                                                @if ($user->latestRegion->region)
                                                                                    {{ $user->niveauAcces->libelle }} : {{ $user->latestRegion->region->libelle }}
                                                                                @elseif ($user->latestRegion->departement)
                                                                                    {{ $user->niveauAcces->libelle }} : {{ $user->latestRegion->departement->libelle }}
                                                                                @elseif ($user->latestRegion->district)
                                                                                    {{ $user->niveauAcces->libelle }} : {{ $user->latestRegion->district->libelle }}
                                                                                @elseif ($user->latestRegion->pays)
                                                                                    {{ $user->niveauAcces->libelle }} : {{ $user->latestRegion->pays->nom_fr_fr }}
                                                                                @else
                                                                                @endif
                                                                            @endif
                                                                        @elseif($personne)
                                                                            @if ($personne->latestRegion)
                                                                                @if ($personne->latestRegion->region)
                                                                                    Region : {{ $personne->latestRegion->region->libelle }}
                                                                                @elseif ($personne->latestRegion->departement)
                                                                                    Département : {{ $personne->latestRegion->departement->libelle }}
                                                                                @elseif ($personne->latestRegion->district)
                                                                                    District : {{ $personne->latestRegion->district->libelle }}
                                                                                @elseif ($personne->latestRegion->pays)
                                                                                    Pays : {{ $personne->latestRegion->pays->nom_fr_fr }}
                                                                                @else
                                                                            @endif

                                                                            @endif
                                                                        @else
                                                                            Aucune fonction
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Structure de rattachement</p>
                                                                    <p class="mb-0">
                                                                        @if ($personne)
                                                                            @if($personne->lastStructure)
                                                                                @if ($personne->lastStructure->agence)
                                                                                    Agence : {{ $personne->lastStructure->agence->nom_agence }}
                                                                                @elseif ($personne->lastStructure->ministere)
                                                                                    Ministère : {{ $personne->lastStructure->ministere->libelle }}
                                                                                @elseif ($personne->lastStructure->bailleur)
                                                                                    Bailleur : {{ $personne->lastStructure->bailleur->libelle_long }}
                                                                                @endif
                                                                            @endif
                                                                        @elseif ($user)
                                                                            @if($user->lastStructure)
                                                                                @if ($user->lastStructure->agence)
                                                                                    Agence : {{ $user->lastStructure->agence->nom_agence }}
                                                                                @elseif ($user->lastStructure->ministere)
                                                                                    Ministère : {{ $user->lastStructure->ministere->libelle }}
                                                                                @elseif ($user->lastStructure->bailleur)
                                                                                    Bailleur : {{ $user->lastStructure->bailleur->libelle_long }}
                                                                                @endif
                                                                            @endif
                                                                        @else
                                                                            Aucune Structure de rattachement
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0 pb-0">
                                                            <p class="mb-1 text-muted">Adresse</p>
                                                            <p class="mb-0">{{ $personne->addresse }}</p>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- [ sample-page ] end -->
            </div>

        </div>
    </div>
</section>


<script>
    $('#table1').DataTable({
        language: {
            processing: "Traitement en cours..."
            , search: ""
            , searchPlaceholder: "Rechercher"
            , lengthMenu: "Afficher _MENU_ lignes"
            , info: "Affichage de _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments"
            , infoEmpty: "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments"
            , infoFiltered: "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)"
            , infoPostFix: ""
            , loadingRecords: "Chargement en cours..."
            , zeroRecords: "Aucun &eacute;l&eacute;ment &agrave; afficher"
            , emptyTable: "Aucune donnée disponible dans le tableau"
            , paginate: {
                first: "Premier"
                , previous: "Pr&eacute;c&eacute;dent"
                , next: "Suivant"
                , last: "Dernier"
            }
            , aria: {
                sortAscending: ": activer pour trier la colonne par ordre croissant"
                , sortDescending: ": activer pour trier la colonne par ordre décroissant"
            }
        }
        , "scrollX": true
    , });

</script>

@endsection
