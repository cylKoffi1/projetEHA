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
                                                <th colspan="4" style="text-align: center;">Autorisations</th>
                                            </tr>
                                            <tr>
                                                <th>Code</th>
                                                <th>Page</th>
                                                <th>+/-</th>
                                                <th>
                                                    <input type="checkbox" id="selectAllAjouter" title="Tout sélectionner - Ajouter" />
                                                    Ajouter
                                                </th>
                                                <th>
                                                    <input type="checkbox" id="selectAllModifier" title="Tout sélectionner - Modifier" />
                                                    Modifier
                                                </th>
                                                <th>
                                                    <input type="checkbox" id="selectAllSupprimer" title="Tout sélectionner - Supprimer" />
                                                    Supprimer
                                                </th>
                                                <th>
                                                    <input type="checkbox" id="selectAllConsulter" title="Tout sélectionner - Consulter" />
                                                    Consulter
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="sousMenuTable_body">
                                            @foreach ($rubriques as $rubrique)
                                            <tr class="main-row">
                                                <td>{{ $rubrique->code }}</td>
                                                <td>{{ $rubrique->libelle }}</td>
                                                <td>
                                                    <button type="button" onclick="toggleSubMenu(this, 'rubrique_sub_row_{{ $rubrique->code }}')">+</button>
                                                </td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td><input type="checkbox" name="consulterRubrique[]" value="{{ $rubrique->code }}" class="checkbox-consulter rubrique_{{ $rubrique->code }}" /></td>
                                            </tr>
                                            @foreach ($rubrique->ecrans as $ecran)
                                            <tr class="rubrique_sub_row_{{ $rubrique->code }} sub-row" style="display: none;">
                                                <td>{{ $ecran->id }}</td>
                                                <td>{{ $ecran->libelle }}</td>
                                                <td></td>
                                                <td><input type="checkbox" name="ajouterRubriqueEcran[]" value="{{ $ecran->id }}" class="checkbox-ajouter rubrique_{{ $rubrique->code }}" /></td>
                                                <td><input type="checkbox" name="modifierRubriqueEcran[]" value="{{ $ecran->id }}" class="checkbox-modifier rubrique_{{ $rubrique->code }}" /></td>
                                                <td><input type="checkbox" name="supprimerRubriqueEcran[]" value="{{ $ecran->id }}" class="checkbox-supprimer rubrique_{{ $rubrique->code }}" /></td>
                                                <td><input type="checkbox" name="consulterRubriqueEcran[]" value="{{ $ecran->id }}" class="checkbox-consulter rubrique_{{ $rubrique->code }}" /></td>
                                            </tr>
                                            @endforeach

                                            @foreach ($rubrique->sousMenus as $sousMenu)
                                            <tr class="rubrique_sub_row_{{ $rubrique->code }} sub-row" style="display: none;">
                                                <td></td>
                                                <td><i class="bi bi-arrow-right-circle-fill"></i> {{ $sousMenu->libelle }}</td>
                                                <td><button type="button" onclick="toggleSubMenu(this, 'sub_row_sub_row_{{ $sousMenu->code }}')">+</button></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td><input type="checkbox" name="consulterSousMenu[]" value="{{ $sousMenu->code }}" class="checkbox-consulter" /></td>
                                            </tr>

                                            @foreach ($sousMenu->ecrans as $ecran)
                                            <tr class="sub_row_sub_row_{{ $sousMenu->code }} sub-row" style="display: none;">
                                                <td>{{ $ecran->code }}</td>
                                                <td>{{ $ecran->libelle }}</td>
                                                <td></td>
                                                <td><input type="checkbox" name="ajouterSousMenuEcran[]" value="{{ $ecran->id }}" class="checkbox-ajouter" /></td>
                                                <td><input type="checkbox" name="modifierSousMenuEcran[]" value="{{ $ecran->id }}" class="checkbox-modifier"/></td>
                                                <td><input type="checkbox" name="supprimerSousMenuEcran[]" value="{{ $ecran->id }}" class="checkbox-supprimer"/></td>
                                                <td><input type="checkbox" name="consulterSousMenuEcran[]" value="{{ $ecran->id }}" class="checkbox-consulter"/></td>
                                            </tr>
                                            @endforeach
                                            @endforeach

                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 d-flex justify-content-end">
                                    <button type="submit" id="soumettre_personnel" class="btn btn-primary me-1 mb-1">Enregistrer</button>
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
    document.addEventListener('DOMContentLoaded', function () {
        // Fonction pour sélectionner/désélectionner toutes les cases à cocher d'une colonne
        function toggleColumnSelection(columnClass, isChecked) {
            const checkboxes = document.querySelectorAll(`.${columnClass}`);
            checkboxes.forEach(cb => cb.checked = isChecked);
        }

        // Sélectionner/Désélectionner toutes les cases pour "Ajouter"
        document.getElementById('selectAllAjouter').addEventListener('change', function () {
            toggleColumnSelection('checkbox-ajouter', this.checked);
        });

        // Sélectionner/Désélectionner toutes les cases pour "Modifier"
        document.getElementById('selectAllModifier').addEventListener('change', function () {
            toggleColumnSelection('checkbox-modifier', this.checked);
        });

        // Sélectionner/Désélectionner toutes les cases pour "Supprimer"
        document.getElementById('selectAllSupprimer').addEventListener('change', function () {
            toggleColumnSelection('checkbox-supprimer', this.checked);
        });

        // Sélectionner/Désélectionner toutes les cases pour "Consulter"
        document.getElementById('selectAllConsulter').addEventListener('change', function () {
            toggleColumnSelection('checkbox-consulter', this.checked);
        });
    });

    // Fonction pour afficher et masquer les sous-menus
    function toggleSubMenu(button, className) {
        const subRows = document.querySelectorAll(`.${className}`);
        const isVisible = Array.from(subRows).some(row => row.style.display === 'table-row');

        subRows.forEach(row => {
            row.style.display = isVisible ? 'none' : 'table-row';
        });

        button.textContent = isVisible ? '+' : '-';
    }
    function applyCheckboxSelection(className, values) {
        // Vérifiez si les valeurs sont définies
        if (!values) {
            console.warn(`Aucune valeur fournie pour ${className}`);
            return;
        }

        // Convertir les valeurs en tableau si elles ne le sont pas déjà
        const valuesArray = Array.isArray(values) ? values : Object.values(values);

        // Appliquer la sélection des checkboxes
        valuesArray.forEach(id => {
            const checkbox = document.querySelector(`.${className}[value="${id}"]`);
            if (checkbox) checkbox.checked = true;
        });
    }

    // Gestion du formulaire : soumission via AJAX
    document.getElementById('personnelForm').addEventListener('submit', function (event) {
        event.preventDefault();

        // Désactiver le bouton pour éviter les doubles soumissions
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        // Récupérer les données du formulaire
        const formData = new FormData(this);

        // Gestion des permissions via les checkboxes
        const consulterRubrique = [];
        const consulterSousMenu = [];
        const consulterRubriqueEcran = [];
        const consulterSousMenuEcran = [];
        const ajouterRubriqueEcran = [];
        const modifierRubriqueEcran = [];
        const supprimerRubriqueEcran = [];
        const ajouterSousMenuEcran = [];
        const modifierSousMenuEcran = [];
        const supprimerSousMenuEcran = [];
        const permissionsAsupprimer = [];

        // Collecte des données des checkboxes
        document.querySelectorAll('input[type="checkbox"]').forEach(function (checkbox) {
            if (checkbox.checked) {
                switch (checkbox.name) {
                    case 'consulterRubrique[]':
                        consulterRubrique.push(checkbox.value);
                        break;
                    case 'consulterSousMenu[]':
                        consulterSousMenu.push(checkbox.value);
                        break;
                    case 'consulterRubriqueEcran[]':
                        consulterRubriqueEcran.push(checkbox.value);
                        break;
                    case 'consulterSousMenuEcran[]':
                        consulterSousMenuEcran.push(checkbox.value);
                        break;
                    case 'ajouterRubriqueEcran[]':
                        ajouterRubriqueEcran.push(checkbox.value);
                        break;
                    case 'modifierRubriqueEcran[]':
                        modifierRubriqueEcran.push(checkbox.value);
                        break;
                    case 'supprimerRubriqueEcran[]':
                        supprimerRubriqueEcran.push(checkbox.value);
                        break;
                    case 'ajouterSousMenuEcran[]':
                        ajouterSousMenuEcran.push(checkbox.value);
                        break;
                    case 'modifierSousMenuEcran[]':
                        modifierSousMenuEcran.push(checkbox.value);
                        break;
                    case 'supprimerSousMenuEcran[]':
                        supprimerSousMenuEcran.push(checkbox.value);
                        break;
                }
            } else {
                switch (checkbox.name) {
                    case 'ajouterRubriqueEcran[]':
                    case 'modifierRubriqueEcran[]':
                    case 'supprimerRubriqueEcran[]':
                    case 'ajouterSousMenuEcran[]':
                    case 'modifierSousMenuEcran[]':
                    case 'supprimerSousMenuEcran[]':
                        permissionsAsupprimer.push(checkbox.className);
                        break;
                }
            }
        });

        // Ajouter les données collectées au FormData
        formData.append('consulterRubrique', JSON.stringify(consulterRubrique));
        formData.append('consulterSousMenu', JSON.stringify(consulterSousMenu));
        formData.append('consulterRubriqueEcran', JSON.stringify(consulterRubriqueEcran));
        formData.append('consulterSousMenuEcran', JSON.stringify(consulterSousMenuEcran));
        formData.append('ajouterRubriqueEcran', JSON.stringify(ajouterRubriqueEcran));
        formData.append('modifierRubriqueEcran', JSON.stringify(modifierRubriqueEcran));
        formData.append('supprimerRubriqueEcran', JSON.stringify(supprimerRubriqueEcran));
        formData.append('ajouterSousMenuEcran', JSON.stringify(ajouterSousMenuEcran));
        formData.append('modifierSousMenuEcran', JSON.stringify(modifierSousMenuEcran));
        formData.append('supprimerSousMenuEcran', JSON.stringify(supprimerSousMenuEcran));
        formData.append('permissionsAsupprimer', JSON.stringify(permissionsAsupprimer));

        // Envoyer les données via une requête Fetch
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Erreur réseau');
            return response.json();
        })
        .then(data => {
            alert(data.message || 'Enregistré avec succès !');
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue.');
        })
        .finally(() => {
            submitButton.disabled = false;
        });
    });

    // Gestion des changements du rôle
    document.getElementById('role').addEventListener('change', function () {
    const roleId = this.value;


    // Réinitialiser toutes les cases à cocher
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => checkbox.checked = false);

        // Charger les permissions existantes via AJAX
        fetch(`/get-role-permissions/${roleId}`)
            .then(response => response.json())
            .then(data => {
                console.log(data);

                // Appliquer les permissions au formulaire
                applyCheckboxSelection('checkbox-ajouter', data.ajouterRubriqueEcran);
                applyCheckboxSelection('checkbox-modifier', data.modifierRubriqueEcran);
                applyCheckboxSelection('checkbox-supprimer', data.supprimerRubriqueEcran);
                applyCheckboxSelection('checkbox-consulter', data.consulterSousMenuEcran);


                applyCheckboxSelection('checkbox-ajouter', data.ajouterSousMenuEcran);
                applyCheckboxSelection('checkbox-modifier', data.modifierSousMenuEcran);
                applyCheckboxSelection('checkbox-supprimer', data.supprimerSousMenuEcran);

                data.rubriques.forEach(rubrique => {
                    const checkbox = document.querySelector(`input[name="consulterRubrique[]"][value="${rubrique.code}"]`);
                    if (checkbox) checkbox.checked = true;
                });

                data.sousMenus.forEach(sousMenu => {
                    const checkbox = document.querySelector(`input[name="consulterSousMenu[]"][value="${sousMenu.code}"]`);
                    if (checkbox) checkbox.checked = true;
                });


                data.ecrans.forEach(ecran => {
                    const checkbox = document.querySelector(`input[name="consulterRubriqueEcran[]"][value="${ecran.id}"]`);
                    if (checkbox) checkbox.checked = true;
                });
            })
            .catch(error => console.error('Erreur:', error));
    });


    // Sélectionner toutes les cases d'une catégorie
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.select-all').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                const rowClass = this.getAttribute('data-row');
                const checkboxes = document.querySelectorAll(`.${rowClass}`);
                checkboxes.forEach(cb => cb.checked = checkbox.checked);
            });
        });
    });

    // Affichage de la date actuelle en continu
    setInterval(function () {
        document.getElementById('date-now').textContent = getCurrentDate();
    }, 1000);

    function getCurrentDate() {
        const currentDate = new Date();
        return currentDate.toLocaleString();
    }
</script>

@endsection
