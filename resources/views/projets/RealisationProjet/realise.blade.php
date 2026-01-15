@extends('layouts.app')

<style>
    :root {
        --primary-color: #435ebe;
        --secondary-color: #2c3e50;
        --warning-color: #ffc107;
        --success-color: #28a745;
        --info-color: #17a2b8;
        --danger-color: #dc3545;
        --light-bg: #f8f9fa;
        --dark-bg: #343a40;
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(78, 70, 70, 0.1);
        border: none;
    }

    .card-header {
        background: linear-gradient(135deg, #a6b4f0, #a3b6c7);
        color: white;
        border-radius: 10px 10px 0 0 !important;
    }

    .form-control, .form-select {
        border-radius: 5px;
        border: 1px solid #ced4da;
        transition: all 0.3s;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(67, 94, 190, 0.25);
    }

    /* Styles pour les options du select */
    .avec-infrastructures {
        background-color: var(--warning-color);
        color: #000;
    }

    .plusieurs-actions {
        background-color: var(--primary-color);
        color: white;
    }

    .non-trouves {
        background-color: var(--danger-color);
        color: white;
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-secondary {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    .btn-warning {
        background-color: var(--warning-color);
        border-color: var(--warning-color);
    }

    .btn-success {
        background-color: var(--success-color);
        border-color: var(--success-color);
    }

    .btn-info {
        background-color: var(--info-color);
        border-color: var(--info-color);
    }

    .btn-danger {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
    }

    .badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: 500;
    }

    .modal-content {
        border-radius: 10px;
        border: none;
    }

    .modal-header {
        background:  linear-gradient(135deg, #a6b4f0, #a3b6c7);
        color: white;
        border-radius: 10px 10px 0 0;
    }

    .date-display {
        font-size: 1rem;
        color: var(--secondary-color);
        font-weight: 500;
    }

    .action-btn {
        transition: all 0.3s;
        padding: 5px 10px;
        border-radius: 5px;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .return-icon {
        cursor: pointer;
        transition: all 0.3s;
        color: var(--primary-color);
    }

    .return-icon:hover {
        transform: scale(1.1);
        color: var(--secondary-color);
    }

    .toggle-list-btn {
        color: var(--primary-color);
        cursor: pointer;
        transition: all 0.3s;
    }

    .toggle-list-btn:hover {
        color: var(--secondary-color);
        text-decoration: underline;
    }

    .drawer-left {
        position: fixed;
        top: 0;
        left: -420px;
        width: 400px;
        height: 100%;
        background-color: #fff;
        border-right: 1px solid #dee2e6;
        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.15);
        transition: left 0.3s ease;
        z-index: 1055;
        overflow-y: auto;
        padding: 20px;
    }
    .drawer-left.show {
        left: 0;
    }
    .drawer-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: bold;
        margin-bottom: 15px;
    }
    .drawer-body {
        padding-top: 10px;
    }


    /* Animation pour les changements de données */
    @keyframes highlight {
        from { background-color: #ffff99; }
        to { background-color: transparent; }
    }

    .highlight {
        animation: highlight 1.5s;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .form-control, .form-select {
            width: 100% !important;
        }

        .table-container {
            overflow-x: scroll;
        }
    }
</style>

@section('content')
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Réalisation de projet</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Démarrage de projet</li>

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
                    <h4 class="card-title text-black">
                        <i class="fas fa-project-diagram me-2"></i>
                        Réalisation: Nouveau projet
                    </h4>
                </div>

                <div class="card-content">
                    <div class="card-body">

                            <input type="hidden" id="ecran_id" value="{{ optional($ecran)->id }}" name="ecran_id">


                            <div class="row mb-4">
                                <div class="col-9">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Section des informations prévisionnelles du projet
                                    </div>
                                </div>
                                <div class="col-3">
                                    @can("ajouter_ecran_" . $ecran->id)
                                    <button class="btn btn-success float-end" data-bs-toggle="modal" data-bs-target="#modalDemarrerProjet">
                                        <i class="fas fa-play-circle me-1"></i> Démarrer un projet
                                    </button>
                                    @endcan
                                    <div class="modal fade" id="modalDemarrerProjet" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">

                                                <div class="modal-content">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title">
                                                            <i class="fas fa-play me-2"></i> Lancer officiellement le projet
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST" id="form-demarrer-projet">
                                                        @csrf
                                                        <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">

                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="code_projet_effectif" class="form-label">Projet à démarrer</label>
                                                                <input type="text" class="form-control" id="code_projet_effectif" name="code_projet" readonly required>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="date_debut_effective" class="form-label">Date effective de démarrage</label>
                                                                <input type="date" class="form-control" name="date_debut" id="date_debut_effective" >
                                                            </div>

                                                            <div class="mb-3">
                                                                <label for="commentaire_effectif" class="form-label">Commentaire (optionnel)</label>
                                                                <textarea name="commentaire" class="form-control" id="commentaire_effectif" rows="2" placeholder="Commentaire"></textarea>
                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            @can("ajouter_ecran_" . $ecran->id)
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fas fa-check me-1"></i> Enregistrer
                                                            </button>
                                                            @endcan
                                                            @can("consulter_ecran_" . $ecran->id)
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                <i class="fas fa-times me-1"></i> Annuler
                                                            </button>
                                                            @endcan
                                                        </div>
                                                    </form>
                                                </div>

                                        </div>
                                    </div>


                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Type de projet</label>
                                    <select id="real_type_projet" class="form-select">
                                        @can('projettype.select', 'INF')
                                            <option value="PROJET">Projet d'infrastructure</option>
                                        @endcan
                                        @can('projettype.select', 'APP')
                                            <option value="APPUI">Projet d'appui</option>
                                        @endcan
                                        @can('projettype.select', 'ETU')
                                            <option value="ETUDE">Projet d'étude</option>
                                        @endcan
                                    </select>
                                    <small class="text-muted">Choisissez un type pour filtrer la liste.</small>
                                </div>
                            </div>
                            <div class="row">
                                

                                <div class="col-md-3">
                                    <label for="code_projet" class="form-label">Code projet</label>
                                    <select name="code_projet" id="code_projet" class="form-select">
                                        <option value="">Sélectionner un projet</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="date_debut">Date début</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="far fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="date_debut" name="date_debut">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label for="date_fin">Date fin</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="far fa-calendar"></i></span>
                                        <input type="date" id="date_fin" class="form-control" name="date_fin">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label for="cout">Coût</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                        <input type="text" class="form-control text-end" id="cout" name="cout">
                                    </div>
                                </div>

                                <div class="col-md-1">
                                    <label for="devise">Devise</label>
                                    <input type="text" id="devise" class="form-control" name="devise" readonly>
                                </div>

                                <div class="col-md-2 mt-2">
                                    <label for="statut">Statut</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                        <input type="text" name="statut" id="statutInput" class="form-control" readonly>
                                        <input type="hidden" name="code_statut" id="codeStatutInput">
                                    </div>
                                </div>
                            </div>


                            <input type="hidden" id="codeProjetHidden">

                        <div class="row mt-4" id="actionsBlock">
                            <div class="col-12">
                                <div class="table-container mt-3">
                                <table id="actionTable" class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>N° ordre</th>
                                        <th>Action à mener</th>
                                        <th>Quantité</th>
                                        <th>Infrastructure</th>
                                        <th>Bénéficiaire</th>
                                        <th>Caractéristiques</th>
                                    </tr>
                                    </thead>
                                    <tbody id="beneficiaire-table-body"></tbody>
                                </table>
                                </div>
                            </div>
                        </div>
                        <!-- Tableau pour projets d'appui (simple, sans card) -->
                        <table id="tableAppuiBenefs" class="table table-bordered table-striped mt-3" style="display:none;">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Designation (bénéficiaire)</th>
                                    <th>Type</th>
                                    <th>Projet source</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyAppuiBenefs">
                                <tr><td colspan="4" class="text-center text-muted">Sélectionnez un projet d'appui...</td></tr>
                            </tbody>
                        </table>



                        <div class="row mt-3">
                            <div class="col-12">
                                @can("consulter_ecran_" . $ecran->id)
                                <a href="#" id="voir-liste-link" class="toggle-list-btn">
                                    <i class="fas fa-list me-2"></i>
                                    Voir la liste complète des projets
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bénéficiaires -->
    <div class="modal fade" id="beneficiaireModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="beneficiaireForm" method="POST" data-parsley-validate action="{{ route('enregistrer.beneficiaires') }}">
                    @csrf
                    <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                    <input type="hidden" name="CodeProjetBene" id="CodeProjetBene">
                    <input type="hidden" name="numOrdreBene" id="numOrdreBene">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-users me-2"></i>
                            Gestion des bénéficiaires
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="btn-group" role="group">
                                    <input type="radio" class="btn-check" name="beneficiaire_type" value="acteur" id="type_acteur" autocomplete="off" checked>
                                    <label class="btn btn-outline-primary" for="type_acteur" onclick="afficheSelect('select_acteur')">
                                        <i class="fas fa-user-tie me-2"></i>Acteur
                                    </label>

                                    <input type="radio" class="btn-check" name="beneficiaire_type" value="localite" id="type_localite" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="type_localite" onclick="afficheSelect('select_localite')">
                                        <i class="fas fa-map-marker-alt me-2"></i>Localité
                                    </label>

                                    <input type="radio" class="btn-check" name="beneficiaire_type" value="infrastructure" id="type_infra" autocomplete="off">
                                    <label class="btn btn-outline-primary" for="type_infra" onclick="afficheSelect('select_infra')">
                                        <i class="fas fa-building me-2"></i>Infrastructure
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <select name="beneficiaire_code" id="select_acteur" class="form-select" style="display: block;">
                                    <option value="">Sélectionner un acteur</option>
                                    @foreach ($acteurs as $acteur)
                                    <option value="{{ $acteur->code_acteur }}">{{ $acteur->libelle_long }}</option>
                                    @endforeach
                                </select>

                                <select name="beneficiaire_code" id="select_localite" class="form-select" style="display: none;">
                                    <option value="">Sélectionner une localité</option>
                                    @foreach ($localites as $loc)
                                    <option value="{{ $loc->code_rattachement }}">{{ $loc->libelle }}</option>
                                    @endforeach
                                </select>

                                <select name="beneficiaire_code" id="select_infra" class="form-select" style="display: none;">
                                    <option value="">Sélectionner une infrastructure</option>
                                    @foreach ($infras as $infra)
                                    <option value="{{ $infra->code }}">{{ $infra->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                @can("ajouter_ecran_" . $ecran->id)
                                <button type="button" class="btn btn-primary me-2" id="addBtn">
                                     Ajouter
                                </button>
                                @endcan
                                @can("supprimer_ecran_" . $ecran->id)
                                <button type="button" class="btn btn-danger" id="deleteBtn">
                                    Supprimer
                                </button>
                                @endcan
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="beneficiaireTable">
                                        <thead>
                                            <tr>
                                                <th width="5%">
                                                    <input type="checkbox" id="check-all">
                                                </th>
                                                <th width="20%">Code</th>
                                                <th width="50%">Libellé/Nom</th>
                                                <th width="25%">Type</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        @can("ajouter_ecran_" . $ecran->id)
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                        @endcan
                        @can("consulter_ecran_" . $ecran->id)
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Fermer
                        </button>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Liste des projets (cachée par défaut) -->
    <div class="row mt-4" id="liste-projets" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-list-ol me-2"></i>
                        Liste des nouveaux projets (Statut prévu)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                            <thead>
                                <tr>
                                    <th>Code projet</th>
                                    <th>Domaine</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th class="text-end">Coût</th>
                                    <th>Dévise</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projets as $projet)
                                <tr>
                                    <td>{{ $projet->code_projet }}</td>
                                    <td>{{ $projet->sousDomaine?->Domaine?->libelle }}</td>
                                    <td>{{ $projet->date_demarrage_prevue ? date('d/m/Y', strtotime($projet->date_demarrage_prevue)) : '-' }}</td>
                                    <td>{{ $projet->date_fin_prevue ? date('d/m/Y', strtotime($projet->date_fin_prevue)) : '-' }}</td>
                                    <td class="text-end">{{ $projet->cout_projet ? number_format($projet->cout_projet, 0, ',', ' ') : '-' }}</td>
                                    <td>{{ $projet->devise?->code_long ?? '-' }}</td>
                                    <td>
                                        @if($projet->statuts?->statut)
                                        <span class="badge bg-primary">{{ $projet->statuts?->statut?->libelle }}</span>
                                        @else
                                        <span class="badge bg-secondary">Non défini</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
  // Recharge la table "Voir la liste complète..." selon le type choisi
  async function reloadListeProjetsFull() {
    const type = document.getElementById('real_type_projet')?.value || 'PROJET';
    const tbody = document.querySelector('#table1 tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="7" class="text-center">Chargement...</td></tr>`;

    try {
      const url = @json(route('realise.listeProjets')) + '?type=' + encodeURIComponent(type);
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
      const data = await res.json();

      tbody.innerHTML = '';
      if (!data.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted">Aucun projet</td></tr>`;
        return;
      }

      data.forEach(row => {
        tbody.insertAdjacentHTML('beforeend', `
          <tr>
            <td>${row.code}</td>
            <td>${row.domaine ?? '-'}</td>
            <td>${row.date_debut ? new Date(row.date_debut).toLocaleDateString() : '-'}</td>
            <td>${row.date_fin   ? new Date(row.date_fin).toLocaleDateString()   : '-'}</td>
            <td class="text-end">${row.cout ? (Number(row.cout).toLocaleString('fr-FR')) : '-'}</td>
            <td>${row.devise ?? '-'}</td>
            <td><span class="badge bg-primary">${row.statut ?? '-'}</span></td>
          </tr>
        `);
      });
    } catch (e) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Erreur de chargement</td></tr>`;
    }
  }

  // Ouvre/ferme la liste -> au moment où on l’affiche, on recharge
  document.getElementById('voir-liste-link')?.addEventListener('click', (e) => {
    setTimeout(reloadListeProjetsFull, 50);
  });

  // Si on change de type, on recalcule la liste si le bloc est visible
  document.getElementById('real_type_projet')?.addEventListener('change', () => {
    const bloc = document.getElementById('liste-projets');
    if (bloc && bloc.style.display !== 'none') reloadListeProjetsFull();
  });
</script>

<script>
    $(document).ready(function() {

        // Initialisation DataTable
        $(document).ready(function() {
            initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des des projets prévus ')
        });


        // Initialisation des sélecteurs de bénéficiaires
        $("#type_acteur").prop("checked", true);
        afficheSelect('select_acteur');

        // Gestion de l'affichage de la liste des projets
        $('#voir-liste-link').click(function(e) {
            e.preventDefault();
            $('#liste-projets').slideToggle();
            $(this).find('i').toggleClass('fa-list fa-times');
        });

        // Formatage des nombres
        $('#cout').on('input', function(e) {
            formatNumberInput(e.target);
        });
    });
    function checkProjectDetails() {
        const codeProjet = $('#code_projet').val();
        if (!codeProjet) return;

        $.ajax({
            url: '{{ url("/fetchProjectDetails") }}',
            method: 'GET',
            data: {
                code_projet: codeProjet
            },
            success: function (response) {
                $('#date_debut').val(response.date_debut || '');
                $('#date_fin').val(response.date_fin || '');
                $('#cout').val(response.cout ? formatNumber(response.cout) : '');
                $('#statutInput').val(response.statutInput || '');
                $('#devise').val(response.devise || '');
                $('#codeProjetHidden').val(response.codeProjet);
                $('#code_projet_effectif').val(response.codeProjet);

                const type = response.type || detectTypeFromCode(response.codeProjet);
                toggleBlocksByType(type);

                if (type === 'PROJET') {
                    updateTableData(response.codeProjet, response.actions || []);
                }
                else if (type === 'APPUI') {
                    // Tableau simple : affiche les bénéficiaires appui
                    loadBeneficiairesAppui(response.codeProjet);
                    $('#beneficiaire-table-body').html('');
                }
                else {
                    // ETUDE
                    $('#beneficiaire-table-body').html('');
                    $('#tbodyAppuiBenefs').html('<tr><td colspan="4" class="text-center text-muted">Projet d\'étude</td></tr>');
                }
            }
        });
    }

    function updateTableData(codeProjet, data) {
        const tbody = $('#beneficiaire-table-body');
        tbody.empty();

        if (data.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center text-muted">Aucune action disponible pour ce projet</td></tr>');
            return;
        }

        data.forEach(item => {
            console.log('item', item);

            // Vérifier si l'infrastructure est définie
            let caracButton = '';
            if (item.infrastructure_idCode) {
                caracButton = `
                    <a href="{{ url('admin/infrastructures') }}/${item.infrastructure_idCode}"
                    class="btn btn-sm btn-primary action-btn">
                        <i class="fas fa-cog me-1"></i> Caractéristiques
                    </a>
                `;
            } else {
                caracButton = `
                    <button type="button" class="btn btn-sm btn-secondary action-btn no-carac">
                        <i class="fas fa-ban me-1"></i> Caractéristiques
                    </button>
                `;
            }

            const row = `
                <tr class="action" data-id="${item.code}">
                    <td class="num_ordre_cell">${item.Num_ordre}</td>
                    <td>${item.action_libelle || '-'}</td>
                    <td>${item.Quantite}</td>
                    <td>${item.infrastructure_libelle || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary beneficiaire-btn"
                                data-bs-toggle="modal" data-bs-target="#beneficiaireModal"
                                data-projet="${codeProjet}" data-ordre="${item.Num_ordre}">
                            <i class="fas fa-user-plus me-1"></i> Bénéficiaires
                        </button>
                    </td>
                    <td>
                        ${caracButton}
                    </td>
                </tr>
            `;

            tbody.append(row);
        });

        // Gestion du clic sur le bouton "aucune caractéristique"
        $(document).on('click', '.no-carac', function() {
            alert("Aucune caractéristique disponible pour cette infrastructure.", 'warning');
        });

    }

    // Gestion des bénéficiaires
    $(document).on('click', '.beneficiaire-btn', function() {
        const codeProjet = $(this).data('projet');
        const numOrdre = $(this).data('ordre');

        $('#CodeProjetBene').val(codeProjet);
        $('#numOrdreBene').val(numOrdre);

        loadBeneficiaires(codeProjet, numOrdre);
    });

    function loadBeneficiaires(codeProjet, numOrdre) {
        $.ajax({
            url: '{{ url("/recuperer-beneficiaires") }}',
            type: 'GET',
            data: {
                code_projet: codeProjet,
                NumOrdre: numOrdre
            },
            beforeSend: function() {
                $('#beneficiaireTable tbody').html('<tr><td colspan="4" class="text-center">Chargement...</td></tr>');
            },
            success: function(response) {
                const tbody = $('#beneficiaireTable tbody');
                tbody.empty();

                if (response.length === 0) {
                    tbody.append('<tr><td colspan="4" class="text-center text-muted">Aucun bénéficiaire enregistré</td></tr>');
                    return;
                }

                response.forEach(beneficiaire => {
                    const row = `
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>${beneficiaire.code}</td>
                            <td>${beneficiaire.libelle_nom_etablissement}</td>
                            <td>${beneficiaire.type}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Impossible de charger les bénéficiaires'
                });
            }
        });
    }

    // Gestion de l'ajout de bénéficiaires
    $('#addBtn').click(function() {
        let code, libelle, type;

        if ($('#type_acteur').is(':checked')) {
            code = $('#select_acteur').val();
            libelle = $('#select_acteur option:selected').text();
            type = 'acteur';
        } else if ($('#type_localite').is(':checked')) {
            code = $('#select_localite').val();
            libelle = $('#select_localite option:selected').text();
            type = 'localite';
        } else if ($('#type_infra').is(':checked')) {
            code = $('#select_infra').val();
            libelle = $('#select_infra option:selected').text();
            type = 'infrastructure';
        }

        if (!code) {
            Swal.fire({
                icon: 'warning',
                title: 'Attention',
                text: 'Veuillez sélectionner un élément à ajouter'
            });
            return;
        }

        // Vérifier si l'élément existe déjà
        const exists = $('#beneficiaireTable tbody tr').toArray().some(tr => {
            return $(tr).find('td:eq(1)').text() === code && $(tr).find('td:eq(3)').text() === type;
        });

        if (exists) {
            Swal.fire({
                icon: 'warning',
                title: 'Attention',
                text: 'Cet élément est déjà dans la liste'
            });
            return;
        }

        // Ajouter à la table
        $('#beneficiaireTable tbody').append(`
            <tr>
                <td><input type="checkbox"></td>
                <td>${code}</td>
                <td>${libelle}</td>
                <td>${type}</td>
            </tr>
        `);

        // Réinitialiser le select
        if (type === 'acteur') $('#select_acteur').val('');
        else if (type === 'localite') $('#select_localite').val('');
        else if (type === 'infrastructure') $('#select_infra').val('');
    });

    // Gestion de la suppression de bénéficiaires
    $('#deleteBtn').click(function() {
        const selected = $('#beneficiaireTable tbody input[type="checkbox"]:checked').closest('tr');

        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Attention',
                text: 'Veuillez sélectionner au moins un élément à supprimer'
            });
            return;
        }

        Swal.fire({
            title: 'Confirmer la suppression',
            text: `Êtes-vous sûr de vouloir supprimer ${selected.length} élément(s) ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer'
        }).then((result) => {
            if (result.isConfirmed) {
                selected.remove();

                // Si plus de lignes, afficher un message
                if ($('#beneficiaireTable tbody tr').length === 0) {
                    $('#beneficiaireTable tbody').html('<tr><td colspan="4" class="text-center text-muted">Aucun bénéficiaire</td></tr>');
                }
            }
        });
    });

    // Sélection/désélection de tous les bénéficiaires
    $('#check-all').change(function() {
        $('#beneficiaireTable tbody input[type="checkbox"]').prop('checked', this.checked);
    });

    // Formatage des nombres
    function formatNumber(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }

    function formatNumberInput(input) {
        let value = input.value.replace(/[^\d]/g, '');
        input.value = formatNumber(value);
    }

    function afficheSelect(selectId) {
        $('#select_acteur, #select_localite, #select_infra').hide();
        $('#' + selectId).show();
    }

    function goBack() {
        window.history.back();
    }
    // Lorsque le projet est sélectionné, mettre à jour le champ du modal
    $('#code_projet').on('change', function () {
        const selected = $(this).val();
        $('#code_projet_effectif').val(selected);
    });

</script>
<script>
        // Fonction générique pour la soumission AJAX
        function envoyerFormulaireAjax($form) {
            const ecran_id = $form.find('input[name="ecran_id"]').val();
            const formData = $form.serialize();

            // Déterminer l'URL
            let url = $form.attr('action');
            if (!url || url.trim() === '') {
                // Cas du form-demarrer-projet (pas d'action dans le form)
                url = "{{ url('/enregistrer-dates-effectives') }}" + "?ecran_id=" + encodeURIComponent(ecran_id);
            }

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                success: function (response) {
                    if (response.success) {
                        alert(response.message); // ✅ message succès natif

                        // Redirection uniquement pour le formulaire "démarrage projet"
                        if ($form.attr('id') === 'form-demarrer-projet') {
                            window.location.href = '{{ route("projet.realise") }}' + "?ecran_id=" + encodeURIComponent(ecran_id);
                        }

                    } else {
                        alert(response.message || 'Erreur inconnue.'); // ✅ message erreur natif
                    }
                },
                error: function (xhr) {
                    let message = 'Une erreur est survenue.';
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        message = errors.join('\n');
                    } else if (xhr.responseJSON?.message) {
                        message = xhr.responseJSON.message;
                    }
                    alert(message); // ✅ affichage natif
                }
            });
        }



        // Formulaire de bénéficiaires
        $('#beneficiaireForm').on('submit', function (e) {
            e.preventDefault();

            $('input[name^="beneficiaires["]').remove(); // Nettoie les anciens champs

            $('#beneficiaireTable tbody tr').each(function (index) {
                const $row = $(this);
                const code = $row.find('td:eq(1)').text().trim();
                const libelle = $row.find('td:eq(2)').text().trim();
                const type = $row.find('td:eq(3)').text().trim();

                $('<input>').attr({
                    type: 'hidden',
                    name: `beneficiaires[${index}][code]`,
                    value: code
                }).appendTo('#beneficiaireForm');

                $('<input>').attr({
                    type: 'hidden',
                    name: `beneficiaires[${index}][libelle]`,
                    value: libelle
                }).appendTo('#beneficiaireForm');

                $('<input>').attr({
                    type: 'hidden',
                    name: `beneficiaires[${index}][type]`,
                    value: type
                }).appendTo('#beneficiaireForm');
            });

            envoyerFormulaireAjax($(this));
        });


        // Formulaire de démarrage de projet
        $('#form-demarrer-projet').on('submit', function (e) {
            e.preventDefault();
            envoyerFormulaireAjax($(this));
        });


</script>
<script>
  // Reprend la même logique que dans l’autre écran
  function detectTypeFromCode(code) {
    if (!code) return 'PROJET';
    if (code.startsWith('ET_')) return 'ETUDE';
    if (code.startsWith('APPUI_')) return 'APPUI';
    return 'PROJET';
  }

    function toggleBlocksByType(type) {
        const actionsTable = document.getElementById('actionTable');
        const appuiTable   = document.getElementById('tableAppuiBenefs');

        if (type === 'ETUDE') {
            actionsTable.style.display = 'none';
            appuiTable.style.display = 'none';
        } 
        else if (type === 'APPUI') {
            actionsTable.style.display = 'none';
            appuiTable.style.display = '';
        } 
        else {
            // PROJET
            actionsTable.style.display = '';
            appuiTable.style.display = 'none';
        }
    }

    async function loadBeneficiairesAppui(codeAppui) {
        const tbody = document.getElementById('tbodyAppuiBenefs');

        tbody.innerHTML = `<tr><td colspan="4" class="text-center">Chargement...</td></tr>`;

        try {
            const url = "{{ route('appui.beneficiaires', ['code_appui' => 'CODE']) }}".replace("CODE", codeAppui);
            const res = await fetch(url);
            const data = await res.json();

            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">Aucun bénéficiaire</td></tr>`;
                return;
            }

            tbody.innerHTML = "";
            data.forEach(b => {
                tbody.innerHTML += `
                    <tr>
                        <td>${b.code}</td>
                        <td>${b.libelle ?? "-"}</td>
                        <td>${b.type}</td>
                        <td>${b.projet_source.code} - ${b.projet_source.libelle}</td>

                    </tr>`;
            });

        } catch (e) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Erreur de chargement</td></tr>`;
        }
    }

</script>

<script>
  // Détection par préfixe (même règle que ton autre écran)
  const PREFIX_TO_TYPE_REAL = [
    { prefix: 'ET_',    type: 'ETUDE'  },
    { prefix: 'APPUI_', type: 'APPUI'  },
  ];
  function detectTypeFromCodeReal(code) {
    if (!code) return 'PROJET';
    const hit = PREFIX_TO_TYPE_REAL.find(p => code.startsWith(p.prefix));
    return hit ? hit.type : 'PROJET';
  }

  const typeSelectReal   = document.getElementById('real_type_projet');
  const projetSelectReal = document.getElementById('code_projet');

  async function reloadProjetOptionsReal(type, preselect=null) {
    projetSelectReal.innerHTML = '<option value="">Chargement...</option>';
    try {
        const url = @json(route('realise.optionsProjets')) 
          + '?type=' + encodeURIComponent(type || 'PROJET')
          + '&statut=1';
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();

      projetSelectReal.innerHTML = '<option value="">Sélectionner un projet</option>';
      (data || []).forEach(row => {
        const opt = document.createElement('option');
        opt.value = row.code;
        opt.textContent = row.code + (row.label ? ' — ' + row.label : '');
        projetSelectReal.appendChild(opt);
      });

      if (preselect && (data || []).some(d => d.code === preselect)) {
        projetSelectReal.value = preselect;
        // déclenche chargement des détails (logique existante)
        checkProjectDetails();
      } else {
        // on nettoie la fiche si on change de type
        document.getElementById('date_debut').value = '';
        document.getElementById('date_fin').value   = '';
        document.getElementById('cout').value       = '';
        document.getElementById('devise').value     = '';
        document.getElementById('statutInput').value= '';
        $('#beneficiaire-table-body').empty();
      }
    } catch (e) {
      projetSelectReal.innerHTML = '<option value="">Erreur de chargement</option>';
    }
  }

  // Init au chargement
  document.addEventListener('DOMContentLoaded', () => {
    reloadProjetOptionsReal(typeSelectReal.value);
  });

  // Quand on change de type, on recharge la liste
  typeSelectReal.addEventListener('change', () => {
    reloadProjetOptionsReal(typeSelectReal.value);
  });

  // Quand on choisit un projet : auto-corrige le type si nécessaire, puis charge les détails
  projetSelectReal.addEventListener('change', function() {
    const code = this.value;
    if (!code) return;

    const autoType = detectTypeFromCodeReal(code);
    if (autoType !== typeSelectReal.value) {
      typeSelectReal.value = autoType;
      reloadProjetOptionsReal(autoType, code);
      return;
    }
    // Utilise ta logique existante
    checkProjectDetails();

    // Alimente aussi le champ du modal démarrage (tu l’avais déjà)
    $('#code_projet_effectif').val(code);
  });
</script>

@endsection
