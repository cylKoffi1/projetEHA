<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BTP-Project</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>

<style>
    .card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
        background-color: rgba(255, 255, 255, 0.8);
    }
    /* Profile Picture */
    .profile-pic {
        display: inline-block;
        vertical-align: middle;
        width: 50px;
        height: 50px;
        overflow: hidden;
        border-radius: 50%;
    }

    .profile-pic img {
        width: 100%;
        height: auto;
        object-fit: cover;
    }

    .profile-menu .dropdown-menu {
        right: 0;
        left: unset;
    }

    .profile-menu .fa-fw {
        margin-right: 10px;
    }

    .toggle-change::after {
        border-top: 0;
        border-bottom: 0.3em solid;
    }

    .navbar .navbar-nav {
        color: white;
    }

    .dropdown-menu a {
        color: #34495E;
    }

    .show {
        background-color: white;
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        color: white;
    }

    .toggle-change {
        color: #34495E
    }

    .show .nav-link {
        color: #34495E;
    }
    #changeGroupModal  {
        background: url("{{ asset('assets/BTP-Image/a-wind-turbine-with-environment-ecology-sign-hologram-sustainable-clean-energy-free-video.jpg') }}") no-repeat center center;
        background-size: cover;
        color: white; /* Pour améliorer la lisibilité du texte */
    }
    #changeGroupModal .modal-body,
    #changeGroupModal .modal-header,
    #changeGroupModal .modal-footer {
        background: rgba(0, 0, 0, 0.6); /* Ajoute une légère transparence pour le contenu */
    }
    #changeGroupModal .modal-body, #changeGroupModal .modal-header, #changeGroupModal .modal-footer {
    background: rgb(255 255 255 / 60%);
}
    .modal-content{
        width: 57% !important;
    }
    .modal.show .modal-dialog {
        transform: matrix(1, 0, 0, 1, 170, 111) !important;
    }
</style>

<nav class="navbar navbar-expand-lg fixed-top navbar-light" style="z-index: 2000; width: 100%; height: 90px; background-color: #435ebe;">
    <div class="container-fluid" style="align-items: center;">
        <div style="display: flex; flex-direction: column; align-items: center;">
            <a class="navbar-brand" href="#" style="color: white; display: flex; flex-direction: column; align-items: flex-start;">
                <img src="{{ asset( auth()->user()?->paysSelectionne()?->armoirie)}}" style="width: 40px; height: auto; margin-bottom: 5px;" alt="" />
                <span>BTP-PROJECT</span>
            </a>
        </div>
        <span style="color: #F1C40F; display: flex; flex-direction: column; align-items: center;">
            <span>{{ auth()->user()?->acteur?->libelle_court }} {{ auth()->user()?->acteur?->libelle_long }}</span>
            @if(auth()->user()?->fonctionUtilisateur)
                <span>{{ auth()->user()->fonctionUtilisateur->libelle_fonction ?? "" }}:  {{ $personnelAffiche }}</span>
            @endif
        </span>
        @if(auth()->check())
        <div style="flex-grow: 4; text-align: center; color: white;">
            <span style="font-size: 18px; text-transform: uppercase;">
                @if (session('projet_selectionne') && auth()->user())
                <span>{{ auth()->user()?->groupeProjetSelectionne()?->libelle }}</span>
            @else
                <span>Aucun groupe projet sélectionné</span>
            @endif
            </span>
        </div>
        @endif
        <header class="mb-3 navbar-toggler">
            <a href="{{ url('#')}}" class="burger-btn d-block d-xl-none">
                <span class="navbar-toggler-icon"></span>
            </a>
        </header>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0"></ul>
            @if(auth()->check())
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 profile-menu" style="align-items: center">
                <li class="nav-item">
                    <a class="nav-link" style="color: white;" href="{{ url('/admin')}}">Accueil</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="{{ url('#')}}" id="navbarDropdown" style="display: flex; align-items: center;" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div style="display: flex; flex-direction: column; align-items: center; margin-right: 7px;">
                            <span style="color: #F1C40F;">{{ auth()->user()->login }} </span>
                            <span style="font-size: 13px; color: #F1C40F;">{{ auth()->user()->groupeUtilisateur->libelle_groupe  }}</span>
                        </div>
                        @if (auth()->user()->acteur)
                            <div class="profile-pic">

                                <img src="{{ asset(auth()->user()->acteur->Photo) }}" alt="Profile Picture">
                            </div>
                        @else
                            <div class="profile-pic">
                                <img src="{{ asset("users/user.png") }}" alt="Profile Picture">
                            </div>
                        @endif
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="{{ url('/admin/users/details-user/' . auth()->user()->id . '?ecran_id=' . $ecran->id) }}"><i class="fas fa-sliders-h fa-fw"></i> Mon compte</a></li>
                        <li><a class="dropdown-item" href="{{ url('/admin/users/details-user/' . auth()->user()->id . '?ecran_id=' . $ecran->id) }}"><i class="fas fa-cog fa-fw"></i> Réglages</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="sidebar-item">
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#changeGroupModal">
                                <i class="bi bi-box-arrow-left"></i> Changer de groupe projet
                            </a>
                        </li>


                    </ul>
                </li>
            </ul>
            @else
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 profile-menu" style="align-items: center">

                <li class="nav-item">
                    <a class="nav-link" style="color: white;" href="{{ url('/')}}">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" style="color: white;" href="{{ url('/connexion')}}">Connexion</a>
                </li>
            </ul>
            @endif
        </div>
    </div>
    <?php
    ?>
</nav>
@if(auth()->check())
<div class="modal fade" id="changeGroupModal" tabindex="-1" aria-labelledby="changeGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeGroupModalLabel">Changer de Groupe Projet</h5>
            </div>
            <div class="modal-body">
                <form id="change-group-form">
                    @csrf

                    <!-- Étape 1 : Sélection du Groupe Projet (Affiché par défaut) -->
                    <div id="step-group">
                        <div class="form-group mt-3">
                            <label for="group-select">Sélectionnez un Groupe Projet :</label>
                            <select id="group-select" class="form-control" required>
                                <option value="">Veuillez sélectionner un groupe projet</option>
                            </select>
                        </div>
                        <button type="button" id="change-country" class="btn btn-secondary">Modifier le pays</button>
                        <button type="submit" id="next-group" class="btn btn-primary" style="float: right;">Changer</button>
                        <hr class="mt-4">
                    </div>

                    <!-- Étape 2 : Sélection du Pays (Caché au départ) -->
                    <div id="step-country" style="display: none;">
                        <div class="form-group">
                            <label for="country-select">Sélectionnez un Pays :</label>
                            <select id="country-select" class="form-control" required>
                                <option value="">Veuillez sélectionner un pays</option>
                                @foreach($payss as $pay)
                                    <option value="{{ $pay->alpha3 }}">{{ $pay->nom_fr_fr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" id="prev-country" class="btn btn-primary">Retour</button>
                        <button type="button" id="next-country" class="btn btn-primary" style="float: right;">Suivant</button>
                        <hr class="mt-4">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "3000"
    };

</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Récupérer le pays sélectionné depuis la session PHP
        var paysSelectionne = "{{ session('pays_selectionne') }}";

        // Pré-remplir le champ pays avec la session et charger les groupes projets
        if (paysSelectionne) {
            $('#country-select').val(paysSelectionne).trigger('change');
            chargerGroupesProjets(paysSelectionne);
        }

        // Quand on clique sur "Modifier le pays", afficher la sélection du pays
        $('#change-country').on('click', function () {
            $('#step-group').hide();
            $('#step-country').show();
        });

        // Quand on clique sur "Retour", revenir à la sélection du groupe projet
        $('#prev-country').on('click', function () {
            $('#step-country').hide();
            $('#step-group').show();
        });

        // Quand un pays est sélectionné, charger les groupes projets
        $('#country-select').on('change', function () {
            let selectedCountry = $(this).val();
            if (selectedCountry) {
                chargerGroupesProjets(selectedCountry);
            }
        });

        // Fonction pour charger les groupes projets d'un pays donné
        function chargerGroupesProjets(paysCode) {
            $.post("{{ route('login.getGroupsByCountry') }}",
                { pays_code: paysCode, _token: '{{ csrf_token() }}' },
                function (response) {
                    let options = '<option value="">Veuillez sélectionner un groupe projet</option>';
                    response.forEach(group => {
                        options += `<option value="${group.groupe_projet_id}">${group.groupe_projet.libelle}</option>`;
                    });
                    $('#group-select').html(options);

                    // Revenir à la sélection du groupe projet si on est sur l'étape du pays
                    if ($('#step-country').is(':visible')) {
                        $('#step-country').hide();
                        $('#step-group').show();
                    }
                }
            ).fail(function () {
                toastr.error('Erreur lors du chargement des groupes projets.');
            });
        }

        // Soumettre le formulaire pour changer de groupe projet
        $('#change-group-form').on('submit', function (e) {
            e.preventDefault();
            const selectedCountry = $('#country-select').val();
            const selectedGroup = $('#group-select').val();

            if (!selectedCountry || !selectedGroup) {
                toastr.error("Veuillez sélectionner un pays et un groupe projet.");
                return;
            }

            $.post("{{ route('login.changeGroup') }}",
                { pays_code: selectedCountry, projet_id: selectedGroup, _token: '{{ csrf_token() }}' },
                function (response) {
                    if (response.success) {
                        toastr.success("Changement effectué avec succès !");
                        window.location.reload();
                    } else {
                        toastr.error("Une erreur est survenue lors du changement.");
                    }
                }
            ).fail(function () {
                toastr.error('Erreur lors du changement de groupe projet.');
            });
        });

    });


</script>
@endif


<script>
    document.querySelectorAll('.dropdown-toggle').forEach(item => {
        item.addEventListener('click', event => {

            if (event.target.classList.contains('dropdown-toggle')) {
                event.target.classList.toggle('toggle-change');
            } else if (event.target.parentElement.classList.contains('dropdown-toggle')) {
                event.target.parentElement.classList.toggle('toggle-change');
            }
        })
    });

</script>
