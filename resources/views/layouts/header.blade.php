<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BTP-Project</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap JS (avec Popper.js inclus) -->
    <script src="{{ asset('assets/compiled/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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
    .dropdown-menu {
        min-width: 200px;
        background-color: white;
        border-radius: 10px;
        padding: 10px;
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
        font-size: 14px;
    }
    .dropdown-header {
        font-size: 14px;
    }
    .dropdown-menu a:hover {
        background-color: #f1f1f1;
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
<style>
    /* Style pour le modal de notification */
    .modal-notification {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 50%;
        top: 20px;
        transform: translateX(-50%);
        background-color: rgba(0,0,0,0.8);
        color: white;
        padding: 15px 25px;
        border-radius: 5px;
        max-width: 80%;
        text-align: center;
        animation: fadeIn 0.3s;
    }

    .modal-notification.success {
        background-color: rgba(40, 167, 69, 0.9);
    }

    .modal-notification.error {
        background-color: rgba(220, 53, 69, 0.9);
    }

    .modal-contents {
        position: relative;
    }

    .close-notification {
        position: absolute;
        right: -10px;
        top: -10px;
        color: white;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        background: rgba(0,0,0,0.5);
        border-radius: 50%;
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @keyframes fadeIn {
        from {opacity: 0; top: 0;}
        to {opacity: 1; top: 20px;}
    }

    /* Style pour le modal de confirmation */
    .confirmationModals {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        background-color: rgba(0, 0, 0, 0.9);
        color: white;
        padding: 20px;
        border-radius: 5px;
        max-width: 400px;
        width: 90%;
        text-align: center;
    }

    .confirmationModals .modal-contentss {
        padding: 10px;
    }

    .confirmation-buttons {
        display: flex;
        justify-content: center;
        margin-top: 20px;
        gap: 10px;
    }

    #confirmAction {
        min-width: 100px;
    }

    #cancelAction {
        min-width: 100px;
    }
</style>

<script>
    // Fonction pour les notifications (avec timeout)
    function alert(message, type = 'success') {
        const modals = document.getElementById('notificationModal');
        const messageEl = document.getElementById('notificationMessage');
        
        modals.className = `modal-notification ${type}`;
        messageEl.textContent = message;
        modals.style.display = 'block';
        
        // Fermer automatiquement après 3 secondes
        setTimeout(() => {
            modals.style.display = 'none';
        }, 3000);
    }

      // Fonction de confirmation personnalisée
    // confirm.js

    window.confirm = async function(message) {
        return new Promise((resolve) => {
            const modal = document.getElementById('confirmationModals');
            const messageEl = document.getElementById('confirmationMessage');
            const confirmBtn = document.getElementById('confirmAction');
            const cancelBtn = document.getElementById('cancelAction');

            // Afficher le message
            messageEl.textContent = message;
            modal.style.display = 'block';

            // Nettoyer anciens événements
            confirmBtn.onclick = null;
            cancelBtn.onclick = null;

            // Si confirmé
            confirmBtn.onclick = function () {
                modal.style.display = 'none';
                resolve(true);
            };

            // Si annulé
            cancelBtn.onclick = function () {
                modal.style.display = 'none';
                resolve(false);
            };

            // Si clic à l'extérieur
            modal.onclick = function (e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    resolve(false);
                }
            };
        });
    };

    // Optionnel : Handler réutilisable pour tous les boutons de suppression
    window.confirms = async function(event, message) {
        event.preventDefault();
        const confirmed = await confirm(message);
        if (confirmed) {
            event.target.closest('form').submit();
        }
    };

</script>

<!-- Modal de notification -->
<div id="notificationModal" class="modal-notification">
    <div class="modal-contents">
        <span class="close-notification">&times;</span>
        <p id="notificationMessage"></p>
    </div>
</div>

<!-- Modal de Confirmation -->
<div id="confirmationModals" class="confirmationModals">
    <div class="modal-contentss">
        <p id="confirmationMessage" style="color:white"></p>
        <div class="confirmation-buttons">
            <button id="confirmAction" class="btn btn-danger">Confirmer</button>
            <button id="cancelAction" class="btn btn-secondary">Annuler</button>
        </div>
    </div>
</div>
<nav class="navbar navbar-expand-lg fixed-top navbar-light" style="z-index: 2000; width: 100%; height: 90px; background-color: #435ebe; padding: 10px 20px;">
    <div class="container-fluid d-flex align-items-center justify-content-between">
        <!-- Logo et nom du projet -->
        <div class="d-flex align-items-center">

            <button class="sidebar-toggle" onclick="toggleSidebar()" style="margin: 2px;">☰</button>

            <a class="navbar-brand d-flex align-items-center" href="#" style="color: white; flex-direction: column;">
                <img src="{{ asset(auth()->user()?->paysSelectionne()?->armoirie) }}" style="width: 40px; height: auto; " class="logo-img" alt="Logo" />
                <span class="project-title">BTP-PROJECT</span>
            </a>
        </div>

        <!-- Infos utilisateur -->
        <div class="user-info d-none d-md-block text-center">
            <span class="user-role">{{ auth()->user()?->acteur?->libelle_court }} {{ auth()->user()?->acteur?->libelle_long }}</span>
            @if(auth()->user()?->fonctionUtilisateur)
                <span class="user-function">{{ auth()->user()->fonctionUtilisateur->libelle_fonction ?? '' }}: {{ $personnelAffiche }}</span>
            @endif
        </div>

        <!-- Sélection du groupe projet -->
        @if(auth()->check())
        <div class="project-group text-center d-none d-lg-block" style="flex-grow: 4; text-align: center; ">
            <span class="group-name" style="font-size: 18px; font-weight: bold; text-transform: uppercase; color: white !important;">
                @if (session('projet_selectionne') && auth()->user())
                    {{ auth()->user()?->groupeProjetSelectionne()?->libelle }}
                @else
                    Aucun groupe projet sélectionné
                @endif
            </span>
        </div>
        @endif
        <!-- Menu Burger pour Mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu Principal -->
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0" style="align-items: center;">
                <li class="nav-item">
                    <a class="nav-link" style="color: white;" href="{{ url('/admin') }}">Accueil</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="display: flex; align-items: center; padding: 0px;">
                        <div class="user-profile d-flex flex-column align-items-center text-white" style="margin-right: 10px;">
                            <span class="user-name fw-bold" style="color: #F1C40F;">{{ auth()->user()->login }}</span>
                            <span class="user-group text-sm" style="color: #F1C40F;">{{ auth()->user()->groupeUtilisateur->libelle_groupe }}</span>
                        </div>
                        <img class="profile-img" src="{{ asset(auth()->user()->acteur?->Photo ?? 'users/user.png') }}" alt="Profile Picture" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end text-dark" aria-labelledby="navbarDropdown">
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" style="color: black;" href="{{ url('/admin/users/details-user/' . auth()->user()->id) }}"><i class="fas fa-user-circle"></i> Mon compte</a></li>
                        <li><a class="dropdown-item" style="color: black;" href="#" data-bs-toggle="modal" data-bs-target="#changeGroupModal"><i class="bi bi-box-arrow-left"></i> Changer de groupe projet</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    .logo-img {
        width: 40px;
        height: auto;
        margin-right: 10px;
    }
    .project-title {
        font-size: 18px;
        font-weight: bold;
    }
    .user-info span, .project-group span {
        color: #F1C40F;
        font-size: 14px;
        display: block;
    }
    .profile-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        margin-left: 10px;
    }
    .user-profile {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .navbar-toggler {
        border: none;
        color: white;
    }
    .show {
        background-color: #435EBE !important;
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        color: white;
    }
    @media (max-width: 768px) {
        .user-info, .project-group {
            display: none !important;
        }
        .navbar-nav {
            text-align: center;
        }
        .dropdown-menu {
            text-align: left;
        }
    }
</style>

@if(auth()->check())
<div class="modal fade" id="changeGroupModal" tabindex="-1" aria-labelledby="changeGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeGroupModalLabel">Changer de Groupe Projet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

<style>
    @media (max-width: 1024px) {

        .modal-lg {
            max-width: 80%;
        }
    }

    @media (max-width: 768px) {
        .modal-lg {
            max-width: 90%;
        }
        .modal-body {
            padding: 15px;
        }
        .form-control {
            font-size: 14px;
            padding: 8px;
        }
        button {
            font-size: 14px;
            padding: 8px 10px;
        }
    }

    @media (max-width: 425px) {
        .modal.show .modal-dialog {
            transform: matrix(1, 0, 0, 1, 86, 136) !important;
        }
    }

    @media (max-width: 480px) {
        .modal-lg {
            max-width: 95%;
        }
        .modal-content {
            border-radius: 10px;
        }
        .modal-body {
            padding: 10px;
        }
        .form-control {
            font-size: 12px;
            padding: 6px;
        }
        button {
            font-size: 12px;
            padding: 6px 8px;
        }
    }
</style>

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
