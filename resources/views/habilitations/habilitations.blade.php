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
    }

    .table-container {
        margin-top: 20px;
    }

    .dropdown-toggle::after {
        display: none;
    }

    .dropdown-menu {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }

    .sub-row {
        background-color: #cae3ea;
    }

    .dropdown-item {
        color: #495057;
    }

    .dropdown-item:hover {
        background-color: #e9ecef;
    }
</style>

<section id="multiple-column-form">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;">
                        <span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span>
                    </li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i> Plateforme</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Gestion des habilitations</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Habilitations</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="row match-height">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Gestion des habilitations</h4>
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
                            <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}" name="ecran_id" required>
                            <div class="row">
                                <div class="mb-3 col-sm-4">
                                    <label for="role" class="form-label">Groupe utilisateur</label>
                                    <select class="form-select" id="role" required name="role">
                                        <option value="" selected>Sélectionnez un groupe utilisateur</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->code }}">{{ $role->libelle_groupe }}</option>
                                        @endforeach
                                    </select>
                                </div>
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
                                        <th><input type="checkbox" id="checkAllAjouter" onclick="toggleAll('ajouter')"> Ajouter</th>
                                        <th><input type="checkbox" id="checkAllModifier" onclick="toggleAll('modifier')"> Modifier</th>
                                        <th><input type="checkbox" id="checkAllSupprimer" onclick="toggleAll('supprimer')"> Supprimer</th>
                                        <th><input type="checkbox" id="checkAllConsulter" onclick="toggleAll('consulter')"> Consulter</th>
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
                                    {{--@can("ajouter_ecran_" . $ecran->id) --}}
                            <button type="submit" id="soumettre_personnel" class="btn btn-primary me-1 mb-1">
                                Enregistrer
                            </button>
                            {{-- @endcan --}}

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
    function toggleAll(type) {
        let checkboxes;
        switch(type) {
            case 'ajouter':
                checkboxes = document.querySelectorAll('input[name="ajouter_rubrique_ecran"], input[name="ajouter_sous_menu_ecran"]');
                break;
            case 'modifier':
                checkboxes = document.querySelectorAll('input[name="modifier_rubrique_ecran"], input[name="modifier_sous_menu_ecran"]');
                break;
            case 'supprimer':
                checkboxes = document.querySelectorAll('input[name="supprimer_rubrique_ecran"], input[name="supprimer_sous_menu_ecran"]');
                break;
            case 'consulter':
                checkboxes = document.querySelectorAll('input[name="consulter_rubrique"], input[name="consulter_sous_menu"], input[name="consulter_rubrique_ecran"], input[name="consulter_sous_menu_ecran"]');
                break;
        }

        let masterCheckbox = document.getElementById('checkAll' + type.charAt(0).toUpperCase() + type.slice(1));
        checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
    }
</script>

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


    // Écoutez l'événement de soumission du formulaire
    document.getElementById('personnelForm').addEventListener('submit', function(event) {
        // Empêcher le comportement par défaut du formulaire
        event.preventDefault();

        // Récupérer les données du formulaire
        var formData = new FormData(this);

        // Créer un tableau pour stocker les ID des éléments sélectionnés
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
            if (checkbox.name === 'consulter_rubrique' && checkbox.checked) {
                consulterRubrique.push(checkbox.value);
            } else if (checkbox.name === 'consulter_rubrique_ecran' && checkbox.checked) {
                consulterRubriqueEcran.push(checkbox.value);
            } else if (checkbox.name === 'consulter_sous_menu' && checkbox.checked) {
                consulterSousMenu.push(checkbox.value);
            } else if (checkbox.name === 'consulter_sous_menu_ecran' && checkbox.checked) {
                consulterSousMenuEcran.push(checkbox.value);
            } else if (checkbox.name === 'ajouter_rubrique_ecran' && checkbox.checked) {
                ajouterRubriqueEcran.push(checkbox.value);
            } else if (checkbox.name === 'modifier_rubrique_ecran' && checkbox.checked) {
                modifierRubriqueEcran.push(checkbox.value);
            } else if (checkbox.name === 'supprimer_rubrique_ecran' && checkbox.checked) {
                supprimerRubriqueEcran.push(checkbox.value);
            } else if (checkbox.name === 'ajouter_sous_menu_ecran' && checkbox.checked) {
                ajouterSousMenuEcran.push(checkbox.value);
            } else if (checkbox.name === 'modifier_sous_menu_ecran' && checkbox.checked) {
                modifierSousMenuEcran.push(checkbox.value);
            } else if (checkbox.name === 'supprimer_sous_menu_ecran' && checkbox.checked) {
                supprimerSousMenuEcran.push(checkbox.value);
            } else if (checkbox.name === 'ajouter_rubrique_ecran' && !checkbox.checked) {
                permissionsAsupprimer.push(checkbox.className);
            } else if (checkbox.name === 'modifier_rubrique_ecran' && !checkbox.checked) {
                permissionsAsupprimer.push(checkbox.className);
            } else if (checkbox.name === 'supprimer_rubrique_ecran' && !checkbox.checked) {
                permissionsAsupprimer.push(checkbox.className);
            } else if (checkbox.name === 'ajouter_sous_menu_ecran' && !checkbox.checked) {
                permissionsAsupprimer.push(checkbox.className);
            } else if (checkbox.name === 'modifier_sous_menu_ecran' && !checkbox.checked) {
                permissionsAsupprimer.push(checkbox.className);
            } else if (checkbox.name === 'supprimer_sous_menu_ecran' && !checkbox.checked) {
                permissionsAsupprimer.push(checkbox.className);
            }
        });
// Dans votre event listener de soumission du formulaire
document.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
    if (!checkbox.checked) {
        // Pour les cases décochées, ajoutez-les à permissionsAsupprimer
        if (checkbox.name.startsWith('ajouter_') || 
            checkbox.name.startsWith('modifier_') || 
            checkbox.name.startsWith('supprimer_')) {
            permissionsAsupprimer.push(checkbox.name.replace('_rubrique_ecran', '_ecran')
                                      .replace('_sous_menu_ecran', '_ecran') + '_' + checkbox.value);
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
                method: 'POST'
                , body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Traiter la réponse
                console.log(data);
                showPopup(data.message);
            })
            .catch(error => {

            });
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
        fetch('{{ url("/")}}/get-role-permissions/' + roleId)
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
