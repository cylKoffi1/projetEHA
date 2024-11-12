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

    .table-container {
        margin-top: 20px;
    }

    .dropdown-toggle::after {
        display: none;
        /* Masquer la flèche du dropdown */
    }

    .dropdown-menu {
        background-color: #f8f9fa;
        /* Couleur de fond du menu déroulant */
        border: 1px solid #dee2e6;
        /* Bordure du menu déroulant */
        border-radius: 0.25rem;
        /* Coins arrondis du menu déroulant */
    }

    .sub-row {
        background-color: #cae3ea;
    }

    .dropdown-item {
        color: #495057;
        /* Couleur du texte des éléments du menu déroulant */
    }

    .dropdown-item:hover {
        background-color: #e9ecef;
        /* Couleur de survol des éléments du menu déroulant */
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Plateforme </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Gestion des habilitations</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Habilitations</li>

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
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        Gestion des habilitations
                        {{-- <a href="#" style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a> --}}
                    </h4>
                    <span id="create_new"></span>
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
                <div class="card-content">
                    <div class="card-body">
                        <form class="form" id="personnelForm" method="POST" enctype="multipart/form-data" action="{{ route('role-assignment.assign') }}">
                            @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                            <div class="row">
                                <div class="mb-3 col-sm-4">
                                    <label for="role" class="form-label">Groupe utilisateur</label>
                                    <select class="form-select" id="role" required name="role">
                                        <option value="" selected>Sélectionez un groupe utilisateur</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- <div class="mb-3 col-sm-4">
                                    <label for="role" class="form-label">Rubrique</label>
                                    <select class="form-select" id="rubriqueSelect" name="rubrique">
                                        <option value="">Sélectionnez une rubrique</option>
                                        @foreach($rubriques as $rubrique)
                                        <option value="{{ $rubrique->code }}">{{ $rubrique->libelle }}</option>
                                @endforeach
                                </select>
                            </div> --}}
                    </div>
                    <div class="row">
                        <div class="mb-3 col-sm-12">
                            <table id="sousMenuTable" class="table table-bordered table-responsive-lg" cellspacing="0" style="width: 100%; overflow-x: auto;">
                                <thead class="thead-light">
                                    <tr>
                                        <th colspan="3"></th>
                                        <th colspan="4" style="text-align: center;">Autaurisations</th>
                                    </tr>
                                    <tr>
                                        <th>code</th>
                                        <th>Page</th>
                                        <th>+/-</th>
                                        <th>Ajouter</th>
                                        <th>Modifier</th>
                                        <th>Supprimer</th>
                                        <th>Consulter</th>
                                    </tr>
                                </thead>
                                <tbody id="sousMenuTable_body">
                                    @foreach ($rubriques as $rubrique)
                                    <tr class="main-row">
                                        <td>{{ $rubrique->code }}</td>
                                        <td>{{ $rubrique->libelle }}</td>
                                        <td><button type="button" onclick="toggleSubMenu(this, 'rubrique_sub_row_{{ $rubrique->code }}')">+</button></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td><input type="checkbox" name="consulter_rubrique" value="{{ $rubrique->code }}" id=""></td>
                                    </tr>

                                    @foreach ($rubrique->ecrans as $ecran)
                                    <tr class="rubrique_sub_row_{{ $rubrique->code }} sub-row" style="display: none;">
                                        <td>{{ $ecran->id }}</td>
                                        <td>{{ $ecran->libelle }}</td>
                                        <td></td>
                                        <td><input type="checkbox" class="ajouter_ecran_{{ $ecran->id }}" name="ajouter_rubrique_ecran" value="{{ $ecran->id }}" id=""></td>
                                        <td><input type="checkbox" class="modifier_ecran_{{ $ecran->id }}" name="modifier_rubrique_ecran" value="{{ $ecran->id }}" id=""></td>
                                        <td><input type="checkbox" class="supprimer_ecran_{{ $ecran->id }}" name="supprimer_rubrique_ecran" value="{{ $ecran->id }}" id=""></td>
                                        <td><input type="checkbox" name="consulter_rubrique_ecran" value="{{ $ecran->id }}" id=""></td>
                                        <td></td>
                                    </tr>
                                    @endforeach
                                    @include('partials.row', ['sousMenus' => $rubrique->sousMenus, 'level'=> 1])

                                    @endforeach
                                </tbody>
                            </table>

                        </div>

                    </div>
                    <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                            {{-- <button type="reset" class="btn btn-light-secondary me-1 mb-1">
                                        Annuler
                                    </button> --}}
                        @can("ajouter_ecran_" . $ecran->id)
                            <button type="submit" id="soumettre_personnel" class="btn btn-primary me-1 mb-1">
                                Enregistrer
                            </button>
                        @endcan

                        </div>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>

</section>

<script>
    function toggleSubMenu(btn, className) {
        var subRows = document.getElementById('sousMenuTable_body').getElementsByClassName(className);
        for (var i = 0; i < subRows.length; i++) {
            var subRow = subRows[i];
            if (subRow.style.display === "none") {
                subRow.style.display = "table-row";
            } else {
                subRow.style.display = "none";
            }
        }

        btn.textContent = (btn.textContent === "-") ? "+" : "-";
    }

    document.getElementById('personnelForm').addEventListener('submit', function(event) {
    // Empêcher le comportement par défaut du formulaire
    event.preventDefault();

    // Désactiver le bouton de soumission
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;

    // Récupérer les données du formulaire
    var formData = new FormData(this);

    // Créer des tableaux pour stocker les ID des éléments sélectionnés
    var consulterRubrique = [];
    var consulterRubriqueEcran = [];
    var consulterSousMenu = [];
    var consulterSousMenuEcran = [];

    var ajouterSousMenuEcran = [];
    var modifierSousMenuEcran = [];
    var supprimerSousMenuEcran = [];

    var ajouterRubriqueEcran = [];
    var modifierRubriqueEcran = [];
    var supprimerRubriqueEcran = [];

    var permissionsAsupprimer = [];

    // Sélectionner tous les éléments checkbox et ajouter leurs valeurs aux tableaux appropriés
    document.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
        if (checkbox.checked) {
            switch (checkbox.name) {
                case 'consulter_rubrique':
                    consulterRubrique.push(checkbox.value);
                    break;
                case 'consulter_rubrique_ecran':
                    consulterRubriqueEcran.push(checkbox.value);
                    break;
                case 'consulter_sous_menu':
                    consulterSousMenu.push(checkbox.value);
                    break;
                case 'consulter_sous_menu_ecran':
                    consulterSousMenuEcran.push(checkbox.value);
                    break;
                case 'ajouter_rubrique_ecran':
                    ajouterRubriqueEcran.push(checkbox.value);
                    break;
                case 'modifier_rubrique_ecran':
                    modifierRubriqueEcran.push(checkbox.value);
                    break;
                case 'supprimer_rubrique_ecran':
                    supprimerRubriqueEcran.push(checkbox.value);
                    break;
                case 'ajouter_sous_menu_ecran':
                    ajouterSousMenuEcran.push(checkbox.value);
                    break;
                case 'modifier_sous_menu_ecran':
                    modifierSousMenuEcran.push(checkbox.value);
                    break;
                case 'supprimer_sous_menu_ecran':
                    supprimerSousMenuEcran.push(checkbox.value);
                    break;
            }
        } else {
            switch (checkbox.name) {
                case 'ajouter_rubrique_ecran':
                case 'modifier_rubrique_ecran':
                case 'supprimer_rubrique_ecran':
                case 'ajouter_sous_menu_ecran':
                case 'modifier_sous_menu_ecran':
                case 'supprimer_sous_menu_ecran':
                    permissionsAsupprimer.push(checkbox.className);
                    break;
            }
        }
    });

    // Ajouter les tableaux d'ID aux données du formulaire
    formData.append('consulterRubrique', JSON.stringify(consulterRubrique));
    formData.append('consulterRubriqueEcran', JSON.stringify(consulterRubriqueEcran));
    formData.append('consulterSousMenu', JSON.stringify(consulterSousMenu));
    formData.append('consulterSousMenuEcran', JSON.stringify(consulterSousMenuEcran));

    formData.append('ajouterRubriqueEcran', JSON.stringify(ajouterRubriqueEcran));
    formData.append('modifierRubriqueEcran', JSON.stringify(modifierRubriqueEcran));
    formData.append('supprimerRubriqueEcran', JSON.stringify(supprimerRubriqueEcran));

    formData.append('ajouterSousMenuEcran', JSON.stringify(ajouterSousMenuEcran));
    formData.append('modifierSousMenuEcran', JSON.stringify(modifierSousMenuEcran));
    formData.append('supprimerSousMenuEcran', JSON.stringify(supprimerSousMenuEcran));

    formData.append('permissionsAsupprimer', JSON.stringify(permissionsAsupprimer));

    // Envoyer les données via Ajax
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur du réseau');
        }
        return response.json();
    })
    .then(data => {
        console.log(data);
        showPopup(data.message);
    })
    .catch(error => {
        console.error('Erreur:', error);
        showPopup("Une erreur s'est produite !");
        // Réactiver le bouton de soumission en cas d'erreur
        submitButton.disabled = false;
    });

    // Fonction pour afficher une alerte
    function showPopup(message) {
        alert(message);
    }
});




    // Écouter l'événement de changement de sélection du rôle
    document.getElementById('role').addEventListener('change', function() {
        // Récupérer l'ID du rôle sélectionné
        var roleId = this.value;

        // Réinitialiser toutes les cases à cocher
        document.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
            checkbox.checked = false;
        });

        // Effectuer une requête AJAX pour obtenir les autorisations du rôle sélectionné
        fetch('/get-role-permissions/' + roleId)
            .then(response => response.json())
            .then(data => {
                console.log(data);
                data.rubriquesAcocher.forEach(rubrique => {
                    document.querySelector('input[name="consulter_rubrique"][value="' + rubrique.code + '"]').checked = true;
                });

                data.sous_menusAcocher.forEach(sous_menu => {
                    document.querySelector('input[name="consulter_sous_menu"][value="' + sous_menu.code + '"]').checked = true;
                });

                data.ecransAcocher.forEach(ecran => {
                    // Vérifiez si la permission est associée à une rubrique ou à un sous-menu
                    if (ecran.code_rubrique) {
                        document.querySelector('input[name="consulter_rubrique_ecran"][value="' + ecran.id + '"]').checked = true;
                    } else if (ecran.code_sous_menu) {
                        document.querySelector('input[name="consulter_sous_menu_ecran"][value="' + ecran.id + '"]').checked = true;
                    }
                });

                data.permissions.forEach(permission => {
                    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(function(checkbox) {
                        // Vérifier si la classe de la case à cocher contient le nom de la permission
                        if (checkbox.classList.contains(permission)) {
                            checkbox.checked = true; // Cocher la case à cocher si la classe correspond
                        }
                    });
                });

            })
            .catch(error => {
                console.error('Error:', error);
            });
    });

</script>


@endsection
