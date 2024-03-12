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
            <div class="row" style="background-color: #DBECF8">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;">
                        <span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row" style="background-color: #DBECF8;">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>
                        {{-- @if (auth()->user()->id!=$user->id)
                        Détails de l'utilisateur
                        @else
                        Mon compte
                        @endif --}}
                        Utilisateurs
                    </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">Utilisateur</a></li>

                            <li class="breadcrumb-item active" aria-current="page">
                                @if (auth()->user()->id!=$user->id)
                                Détails Utilisateurs
                                @else
                                Mon compte
                                @endif
                            </li>

                        </ol>
                    </nav>
                    <div class="row" style="background-color: #DBECF8;">
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
                                @if (auth()->user()->id==$user->id)
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab" href="#profile-2" role="tab" aria-selected="false" tabindex="-1">
                                        <i class="bi bi-file-person me-2"></i>Infos Personnelles
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" id="profile-tab-4" data-bs-toggle="tab" href="#profile-4" role="tab" aria-selected="false" tabindex="-1">
                                        <i class="bi bi-lock-fill me-2"></i>Mot de passe
                                    </a>
                                </li>
                                @endif
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
                                                            @if ($user->personnel->photo)
                                                            <img class="rounded-circle img-fluid wid-70" style="width: 50px; height: 50px; border-radius: 50px;" src="{{ asset("users/".$user->personnel->photo) }}" alt="User image">
                                                            @else
                                                            <img class="rounded-circle img-fluid wid-70" style="width: 50px; height: 50px; border-radius: 50px;" src="{{ asset("users/user.png") }}" alt="Photo">
                                                            @endif

                                                        </div>
                                                        <h5 class="mb-0">{{ $user->login }}</h5>
                                                        <p class="text-muted text-sm">{{ $user->latestFonction->fonctionUtilisateur->libelle_fonction ?? ""}}</p>
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
                                                            <p class="mb-0">{{ $user->personnel->email }}</p>
                                                        </div>
                                                        <div class="d-inline-flex align-items-center justify-content-between w-100 mb-3">
                                                            <i class="bi bi-phone"></i>
                                                            <p class="mb-0">(+225) {{ $user->personnel->telephone }}</p>
                                                        </div>
                                                        <div class="d-inline-flex align-items-center justify-content-between w-100 mb-3">
                                                            <i class="bi bi-house"></i>
                                                            <p class="mb-0">{{ $user->personnel->addresse }}</p>
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
                                                                    <p class="mb-0">{{ $user->personnel->nom }}</p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Prénom</p>
                                                                    <p class="mb-0">{{ $user->personnel->prenom }}</p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Téléphone</p>
                                                                    <p class="mb-0">(+225) {{ $user->personnel->telephone }}</p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Email</p>
                                                                    <p class="mb-0">{{ $user->personnel->email }}</p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0 pt-0">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Groupe</p>
                                                                    <p class="mb-0">{{ $user->roles->first()->name ?? ""}}</p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Fonction</p>
                                                                    <p class="mb-0">{{ $user->latestFonction->fonctionUtilisateur->libelle_fonction ?? ""}}</p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0 pt-0">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Niveaux Accès</p>
                                                                    <p class="mb-0">{{ $user->niveauAcces->libelle ?? "" }}</p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Structure de rattachement</p>
                                                                    <p class="mb-0">{{ $user->personnel->prenom }}</p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0 pb-0">
                                                            <p class="mb-1 text-muted">Adresse</p>
                                                            <p class="mb-0">{{ $user->personnel->addresse }}</p>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">
                                    <div class="row">
                                        <form class="row" style="align-items: center;" id="update-user" action="{{ route("users.update_auth", ['userId' => $user->id]) }}" method="post" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}" name="ecran_id" required>
                                            <div class="col-lg-12">
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h5>Infos Personnelles</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-3">
                                                                <div class="form-group">
                                                                    <label for="fonction">Fonction :</label>
                                                                    <select name="fonction" id="fonction" class="form-select" required>
                                                                        <option value="">Selectionner une fonction</option>
                                                                        @foreach($fonctions as $fonction)
                                                                        <option value="{{ $fonction->code }}" {{ optional($user->latestFonction)->code_fonction == $fonction->code ? 'selected' : '' }}>
                                                                            {{ $fonction->libelle_fonction }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-3 ">
                                                                <div class="form-group">
                                                                    <label for="structure_ratache">Structure :</label>
                                                                    <label for="bai">B :</label>
                                                                    <input type="radio" value="bai" name="structure" id="bai" selected="true" onclick="showSelect('bailleur')" style="margin-right: 5px;">
                                                                    <label for="age">A :</label>
                                                                    <input type="radio" name="structure" value="age" id="age" onclick="showSelect('agence')" style="margin-right: 5px;">
                                                                    <label for="min">M :</label>
                                                                    <input type="radio" name="structure" value="min" id="min" onclick="showSelect('ministere')">

                                                                    <select name="bailleur" id="bailleur" class="form-select" style="display: none;">
                                                                        <option value="">Selectionner le bailleur</option>
                                                                        @foreach($bailleurs as $bailleur)
                                                                        <option value="{{ $bailleur->code_bailleur }}" {{ optional($user->personnel)->code_structure_bailleur == $bailleur->code_bailleur ? 'selected' : '' }}>
                                                                            {{ $bailleur->libelle_long }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>

                                                                    <select name="agence" id="agence" class="form-select" style="display: none;">
                                                                        <option value="">Selectionner l'agence</option>
                                                                        @foreach($agences as $agence)
                                                                        <option value="{{ $agence->code_agence_execution }}" {{ optional($user->personnel)->code_agence_execution == $agence->code_agence_execution ? 'selected' : '' }}>
                                                                            {{ $agence->nom_agence }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>

                                                                    <select name="ministere" id="ministere" class="form-select" style="display: none;">
                                                                        <option value="">Selectionner le ministère</option>
                                                                        @foreach($ministeres as $ministere)
                                                                        <option value="{{ $ministere->code }}" {{ optional($user->personnel)->code_structure_ministere == $ministere->code ? 'selected' : '' }}>
                                                                            {{ $ministere->libelle }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>

                                                                </div>
                                                            </div>

                                                            @role('Admin')
                                                            <div class="col-3">
                                                                <div class="form-group">
                                                                    <label for="group_user">Groupe utilisateur :</label>
                                                                    <select name="group_user" id="group_user" class="form-select" required>
                                                                        <option value="">Selectionner un groupe</option>
                                                                        @foreach($groupe_utilisateur as $groupe)
                                                                        <option value="{{  $groupe->name }}" {{  optional($user->roles->first())->id == $groupe->id ? 'selected' : '' }}>
                                                                            {{ $groupe->name }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            @endrole

                                                            <div class="col-3">
                                                                <div class="form-group">
                                                                    <label for="niveau_acces_id">Niveau d'accès :</label>
                                                                    <select name="niveau_acces_id" class="form-select">
                                                                        <option value="">Selectionner un niveau</option>
                                                                        @foreach($niveauxAcces as $niveauAcces)
                                                                        <option value="{{ $niveauAcces->id }}" {{ $user->niveau_acces_id == $niveauAcces->id ? 'selected' : '' }}>
                                                                            {{ $niveauAcces->libelle }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col">
                                                                <div class="form-group">
                                                                    <label for="sous_domaine">Sous-domaines séléctionnés:</label>
                                                                    <select id="sous_domaine" name="sous_domaine" multiple required>
                                                                        @foreach ($sous_domaines as $sd)
                                                                        <option value="{{ $sd->code }}">{{ $sd->libelle }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <label for="domaine">Domaine:</label>
                                                                    <select id="domaine" class="form-control" name="domaine" multiple>

                                                                        @foreach ($domaines as $domaine)
                                                                        <option value="{{ $domaine->code }}">{{ $domaine->libelle }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-4">
                                                                <div class="form-group">
                                                                    <label class="form-label">Nom utilisateur</label>
                                                                    <input type="text" class="form-control" name="username" value="{{ $user->login }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <label class="form-label">Nom</label>
                                                                    <input type="text" class="form-control" name="nom" value="{{ $user->personnel->nom }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <label class="form-label">Prénom</label>
                                                                    <input type="text" class="form-control" name="prenom" value="{{ $user->personnel->prenom }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" name="email" value="{{ $user->personnel->email }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <label class="form-label">Téléphone</label>
                                                                    <input type="text" class="form-control" name="tel" value="{{ $user->personnel->telephone }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <label class="form-label">Adresse</label>
                                                                    <input type="text" class="form-control" name="adresse" value="{{ $user->personnel->addresse }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-4">
                                                                <label for="photo" class="form-label">Photo</label>
                                                                <div class="form-group position-relative has-icon-left">
                                                                    <input type="file" accept=".jpeg, .jpg, .png" id="photo" class="form-control" name="photo" />
                                                                    <div class="form-control-icon">
                                                                        <i class="bi bi-image-fill"></i>
                                                                    </div>
                                                                </div>
                                                                @error('tel')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <label for="photo" class="form-label">Photo actuelle</label>
                                                                <div class="form-group">
                                                                    @if ($user->personnel->photo)
                                                                    <img style="width: 40px; height: 40px; border-radius: 50px;" src="{{ asset("users/".$user->personnel->photo) }}" alt="Photo">
                                                                    @else
                                                                    <img style="width: 40px; height: 40px; border-radius: 50px;" src="{{ asset("users/user.png") }}" alt="Photo">
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 text-end btn-page">
                                                <button class="btn btn-outline-secondary" type="reset">Annuler</button>
                                                <button class="btn btn-primary" type="submit">Mettre à jour</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="tab-pane" id="profile-4" role="tabpanel" aria-labelledby="profile-tab-4">
                                    <div class="card">
                                        <form action="{{ route('password.change') }}" method="post">
                                            <div class="card-header">
                                                <h5>Modifier mot de passe</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-sm-6">

                                                        @csrf
                                                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}" name="ecran_id" required>
                                                        <div class="form-group">
                                                            <label class="form-label">Ancien mot de passe</label>
                                                            <input type="password" class="form-control" name="old">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">Nouveaux mot de passe</label>
                                                            <input type="password" name="new" class="form-control">
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">Confirmer nouveau</label>
                                                            <input type="password" name="confirm_new" class="form-control">
                                                        </div>

                                                    </div>
                                                    <div class="col-sm-6">
                                                        <h5>Suggestions pour le mot de passe:</h5>
                                                        <ul class="list-group list-group-flush">
                                                            <li class="list-group-item"><i class="ti ti-minus me-2"></i> Au moins 8 caractères</li>
                                                            <li class="list-group-item"><i class="ti ti-minus me-2"></i> Au moins une minscule (a-z)
                                                            </li>
                                                            <li class="list-group-item"><i class="ti ti-minus me-2"></i> Au moins une majuscule (A-Z)</li>
                                                            <li class="list-group-item"><i class="ti ti-minus me-2"></i> Au moins un chiffre (0-9)</li>
                                                            <li class="list-group-item"><i class="ti ti-minus me-2"></i> Au moins un caractère spécial
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer text-end btn-page">
                                                <button class="btn btn-outline-secondary" type="reset">Annuler</button>
                                                <button class="btn btn-primary" type="submit">Modifier</button>
                                            </div>
                                        </form>
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





    $(document).ready(function() {
        var uid = '{{ $user->id }}';
        var userSD = @json($sous_dom);
        var userD = @json($dom);
        if ('{{ $user -> personnel -> bailleur ? "true" : "false"}}') {
            $("#bai").prop("checked", true);
            showSelect('bailleur');
        }

        if ('{{ $user -> personnel -> agence ? "true" : "false"}}') {
            $("#age").prop("checked", true);
            showSelect('agence');
        }

        if ('{{ $user -> personnel -> ministere ? "true" : "false"}}') {
            $("#min").prop("checked", true);
            showSelect('ministere');
        }
        $('#fonction').on('change', function() {
            getGroupeUserByFonctionId($(this));
        })

        var domaines = $('#domaine').filterMultiSelect({

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

        var sous_dom = $('#sous_domaine').filterMultiSelect({

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
        console.log(userD);
        // Pour parcourir les éléments
        userSD.forEach(function(item) {
            sous_dom.selectOption(item.sous_domaine);
        });
        userD.forEach(function(item) {
            domaines.selectOption(item.code_domaine)
        })

        $('#update-user').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            // Create FormData object from the form
            var formData = new FormData(this);

            // Add additional data to FormData if needed
            formData.append("sd", sous_dom.getSelectedOptionsAsJson());
            formData.append("domS", domaines.getSelectedOptionsAsJson());
            // Assume that you have a variable userId containing the user ID
            var userId = uid; // Change this to your dynamic user ID
            // Build the correct URL for the AJAX request
            var url = '/admin/users/details-user/' + userId;

            $.ajax({
                url: url
                , type: 'POST'
                , data: formData
                , contentType: false, // Don't set content type (let jQuery handle it)
                processData: false, // Don't process data (let jQuery handle it)
                success: function(response) {
                    showPopup(response.success);

                    // Rediriger l'utilisateur après une requête réussie
                    window.location.href = "/admin/users/details-user/" + userId;
                }
                , error: function(xhr, status, error) {
                    var err = JSON.parse(xhr.responseText);
                    console.log(err); // Affichez les détails de l'erreur côté serveur dans la console
                    showPopup('Une erreur est survenue !');
                }
            });
        });

    });


    function getGroupeUserByFonctionId(selectElement) {
        var selectedFonction = selectElement.val();

        // Effectuez une requête AJAX pour obtenir les sous-domaines
        $.ajax({
            type: "GET"
            , url: "/admin/get-groupes/" + selectedFonction
            , success: function(data) {
                console.log(data);
                var groupess = $("#group_user"); // Correction: Utilisation de l'ID directement

                groupess.empty(); // Effacez les options précédentes

                // Ajoutez les options des sous-domaines récupérés
                $.each(data.groupes, function(key, value) {
                    groupess.append(
                        $("<option>", {
                            value: key
                            , text: value
                        , })
                    );
                });

                groupess.trigger("change");
            }
        , });
    }



    document.getElementById('username').addEventListener('keyup', function() {
        var username = this.value;

        // Effectuer la requête AJAX
        $.ajax({
            url: '/check-username'
            , method: 'GET'
            , data: {
                username: username
            }
            , success: function(response) {
                if (response.exists) {
                    document.getElementById('username-error').innerText = 'Le nom d\'utilisateur est déjà pris.';
                    document.getElementById('username').classList.add('is-invalid');
                } else {
                    document.getElementById('username-error').innerText = '';
                    document.getElementById('username').classList.remove('is-invalid');
                }
            }
        });
    });

    document.getElementById('email').addEventListener('keyup', function() {
        var email = this.value;

        // Effectuer la requête AJAX
        $.ajax({
            url: '/check-email'
            , method: 'get'
            , data: {
                email: email
            }
            , success: function(response) {
                if (response.exists) {
                    document.getElementById('email-error').innerText = 'Cet eamil est déjà utilisé par un autre utilisateur.';
                    document.getElementById('email').classList.add('is-invalid');
                } else {
                    document.getElementById('email-error').innerText = '';
                    document.getElementById('email').classList.remove('is-invalid');
                }
            }
        });
    });

</script>

@endsection
