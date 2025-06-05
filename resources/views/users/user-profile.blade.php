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
                                                            @if (auth()->user()->acteur?->Photo )
                                                            <img class="rounded-circle img-fluid wid-70" style="width: 50px; height: 50px; border-radius: 50px;" src="{{ asset(auth()->user()->acteur?->Photo ) }}" alt="User image">
                                                            @else
                                                            <img class="rounded-circle img-fluid wid-70" style="width: 50px; height: 50px; border-radius: 50px;" src="{{ asset('users/user.png') }}" alt="Photo">
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
                                                            <p class="mb-0">{{ $user->acteur?->personnePhysique?->email }}</p>
                                                        </div>
                                                        <div class="d-inline-flex align-items-center justify-content-between w-100 mb-3">
                                                            <i class="bi bi-phone"></i>
                                                            <p class="mb-0">{{ $user->acteur?->personnePhysique?->telephone_mobile ?? '--' }} / {{ $user->acteur?->personnePhysique?->telephone_bureau ?? '--' }}</p>
                                                        </div>
                                                        <div class="d-inline-flex align-items-center justify-content-between w-100 mb-3">
                                                            <i class="bi bi-house"></i>
                                                            <p class="mb-0">{{ $user->acteur?->adresse }}</p>
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
                                                                    <p class="mb-0">{{ $user->acteur?->libelle_court }}</p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Prénom</p>
                                                                    <p class="mb-0">{{ $user->acteur?->libelle_long }}</p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Téléphone</p>
                                                                    <p class="mb-0"> {{ $user->acteur?->telephone }}</p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Email</p>
                                                                    <p class="mb-0">{{ $user->acteur?->email }}</p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0 pt-0">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Adresse</p>
                                                                    <p class="mb-0">{{ $user->acteur?->adresse }}</p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Fonction</p>
                                                                    <p class="mb-0">{{ $user->fonctionUtilisateur?->libelle_fonction ?? '--' }}</p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0 pt-0">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Groupe utilisateur</p>
                                                                    <p class="mb-0">
                                                                        @if($user)
                                                                                {{ $user->groupeUtilisateur?->libelle_groupe ?? 'Aucun groupe utilisateur'}}
                                                                        @endif</p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Type acteur</p>
                                                                    <p class="mb-0">
                                                                        @if ($user)
                                                                            @if($user->acteur?->type_acteur)
                                                                                {{ $user->acteur?->type?->libelle_type_acteur}}                                                                                
                                                                            @endif
                                                                        @else
                                                                            Aucune Type acteur
                                                                        @endif</p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li class="list-group-item px-0 pt-0">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Groupe projet</p>
                                                                    <p class="mb-0">
                                                                        {{ $groupeProjetSelectionne->libelle ?? 'Aucun groupe projet sélectionné' }}
                                                                    </p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p class="mb-1 text-muted">Domaines</p>
                                                                    <p class="mb-0">
                                                                        @if($domainesSelectionnes->isNotEmpty())
                                                                            {{ $domainesSelectionnes->pluck('libelle')->implode(', ') }}
                                                                        @else
                                                                            Aucun domaine
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">
                                    <div class="row">
                                        <form class="row" style="align-items: center;" id="update-user" action="{{ route('users.update_auth', ['userId' => $user->id]) }}" method="post" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" class="form-control" id="ecran_id" {{--value="{{ $ecran->id }}"--}} name="ecran_id" required>
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
                                                                        <option value="{{ $fonction->code }}" data-structure="{{ $fonction->code_structure }}" {{ optional($user->latestFonction)->code_fonction == $fonction->code ? 'selected' : '' }}>
                                                                            {{ $fonction->libelle_fonction }}
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
                                                                    <input type="text" class="form-control" name="nom" value="{{ $user->acteur->libelle_court }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <label class="form-label">Prénom</label>
                                                                    <input type="text" class="form-control" name="prenom" value="{{ $user->acteur->libelle_long }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                                                    <input type="text" class="form-control" name="email" value="{{ $user->acteur->email }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <label class="form-label">Téléphone</label>
                                                                    <input type="text" class="form-control" name="tel" value="{{ $user->acteur->telephone }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-4">
                                                                <div class="form-group">
                                                                    <label class="form-label">Adresse</label>
                                                                    <input type="text" class="form-control" name="adresse" value="{{ $user->acteur->adresse }}">
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
                                                                   
                                                                    @if (auth()->user()->acteur?->Photo )
                                                                    <img style="width: 40px; height: 40px; border-radius: 50px;" src="{{ asset(auth()->user()->acteur?->Photo ) }}" alt="Photo">
                                                                    @else
                                                                    <img style="width: 40px; height: 40px; border-radius: 50px;"  src="{{ asset('users/user.png') }}" alt="Photo">
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
                                        <form id="change-password-form" action="{{ route('password.change') }}" method="post">
                                            <div class="card-header">
                                                <h5>Modifier mot de passe</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-sm-6">

                                                        @csrf
                                                        <input type="hidden" class="form-control" id="ecran_id" {{--value="{{ $ecran->id }}"--}} name="ecran_id" required>
                                                        <div class="form-group position-relative">
                                                            <label class="form-label">Ancien mot de passe</label>
                                                            <input type="password" id="old" name="old" class="form-control">
                                                            <i class="bi bi-eye-fill toggle-password" data-target="old" style="position: absolute; right: 10px; top: 38px; cursor: pointer;"></i>
                                                        </div>

                                                        <div class="form-group position-relative">
                                                            <label class="form-label">Nouveau mot de passe</label>
                                                            <input type="password" id="new" name="new" class="form-control">
                                                            <i class="bi bi-eye-fill toggle-password" data-target="new" style="position: absolute; right: 10px; top: 38px; cursor: pointer;"></i>
                                                        </div>

                                                        <div class="form-group position-relative">
                                                            <label class="form-label">Confirmer nouveau</label>
                                                            <input type="password" id="confirm_new" name="confirm_new" class="form-control">
                                                            <i class="bi bi-eye-fill toggle-password" data-target="confirm_new" style="position: absolute; right: 10px; top: 38px; cursor: pointer;"></i>
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
    document.querySelectorAll('.toggle-password').forEach(eye => {
        eye.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);

            // Toggle l’icône
            this.classList.toggle('bi-eye-fill');
            this.classList.toggle('bi-eye-slash-fill');
        });
    });
    
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const updateForm = document.getElementById("update-user");

        updateForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const formData = new FormData(updateForm);

            fetch(updateForm.action, {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert(data.message);
                } else if (data.status === "error") {
                    alert(data.errors.join("\n"));
                }
            })
            .catch(error => {
                alert("Une erreur est survenue.");
                console.error("Erreur:", error);
            });
        });
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("change-password-form");

        form.addEventListener("submit", function (e) {
            e.preventDefault(); // Empêche le rechargement

            const formData = new FormData(form);

            fetch(form.action, {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert(data.message,"success"); // ✅ Succès
                    form.reset();
                } else if (data.status === "error") {
                    alert(data.errors.join("\n"),"error"); // ⚠️ Erreurs
                }
            })
            .catch(error => {
                alert("Une erreur est survenue.","error");
                console.error("Erreur:", error);
            });
        });
    });
</script>

<script>

const groupeProjetSelect = document.querySelector("lookup-multiselect[name='groupe_projet']"); // le vrai nom du champ groupe projet
const domaineSelect = document.querySelector("lookup-multiselect[name='domaine']");
const sousDomaineSelect = document.querySelector("lookup-multiselect[name='sous_domaine']");

// Met à jour les domaines quand un groupe projet est sélectionné
groupeProjetSelect.addEventListener("change", () => {
    const selected = groupeProjetSelect.value;
    if (!selected.length) return;

    fetch(`{{url("/")}}/domaines/${selected[0]}`)
        .then(res => res.json())
        .then(data => {
            const options = data.map(d => ({
                value: d.code,
                text: d.libelle
            }));
            domaineSelect.setOptions(options);
            sousDomaineSelect.setOptions([]); // reset sous-domaines
        });
});

// Met à jour les sous-domaines quand un domaine est sélectionné
domaineSelect.addEventListener("change", () => {
    const groupeProjet = groupeProjetSelect.value[0];
    const domaines = domaineSelect.value;

    if (!groupeProjet || !domaines.length) return;

    const promises = domaines.map(d =>
        fetch(`{{url("/")}}/sous-domaines/${d}/${groupeProjet}`).then(res => res.json())
    );

    Promise.all(promises).then(results => {
        const merged = results.flat();
        const unique = Array.from(
            new Map(merged.map(item => [item.code_sous_domaine, item])).values()
        );

        sousDomaineSelect.setOptions(
            unique.map(s => ({
                value: s.code_sous_domaine,
                text: s.lib_sous_domaine
            }))
        );
    });
});


    function filterOptions(structure) {
        var select = document.getElementById('fonction');
        var options = select.options;
        var selectedStructure = structure.toLowerCase();

        for (var i = 0; i < options.length; i++) {
            var option = options[i];
            var optionStructure = option.getAttribute('data-structure');

            if (optionStructure === selectedStructure || !selectedStructure) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        }
    }
</script>
<script>
    document.getElementById('username').addEventListener('keyup', function() {
        var username = this.value;

        // Effectuer la requête AJAX
        $.ajax({
            url: '{{url("/")}}/check-username'
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
            url: '{{url("/")}}/check-email'
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
