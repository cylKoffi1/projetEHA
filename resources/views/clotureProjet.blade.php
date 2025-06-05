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
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
    
    /* Styles spécifiques à la clôture */
    .cloture-section {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-top: 20px;
        border-left: 4px solid var(--danger-color);
    }
    
    .cloture-btn {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
        transition: all 0.3s;
    }
    
    .cloture-btn:hover {
        background-color: #c82333;
        border-color: #bd2130;
        transform: translateY(-2px);
    }
</style>

@section('content')
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
                    <h3><i class="bi bi-arrow-return-left return-icon" onclick="goBack()"></i> Clôture de projet</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Réalisation de projet</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Clôture de projet</li>
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
                    <h4 class="card-title text-white">
                        <i class="fas fa-lock me-2"></i>
                        Clôture des projets
                    </h4>
                </div>
                
                <div class="card-content">
                    <div class="card-body">
                        @if (session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                        @elseif (session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                        </div>
                        @endif
                        
                        <form class="form" id="projectForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="ecran_id" value="{{ $ecran->id }}" name="ecran_id">
                            <input type="hidden" id="codeProjetHidden">
                            
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Section des informations effectives du projet
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="code_projet" class="form-label">Code projet</label>
                                    <select name="code_projet" id="code_projet" class="form-select" onchange="checkProjectDetails()">
                                        <option value="">Sélectionner un projet</option>
                                        @foreach ($statutProjetStatut as $statutProjetStatu)
                                        <option value="{{ $statutProjetStatu->code_projet }}">{{ $statutProjetStatu->code_projet }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="col-md-2">
                                    <label for="date_debut">Date début</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="far fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="date_debut" name="date_debut" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <label for="date_fin">Date fin</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="far fa-calendar"></i></span>
                                        <input type="date" id="date_fin" class="form-control" name="date_fin" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <label for="cout">Coût</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                        <input type="text" class="form-control text-end" id="cout" name="cout" readonly>
                                    </div>
                                </div>
                                
                                <div class="col-md-1">
                                    <label for="devise">Devise</label>
                                    <input type="text" id="devise" class="form-control" name="devise" readonly>
                                </div>
                                
                                <div class="col-md-2">
                                    <label for="statut">Statut</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                        <input type="text" name="statut" id="statutInput" class="form-control" readonly>
                                        <input type="hidden" name="code_statut" id="codeStatutInput">
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="actionTable" class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>N° ordre</th>
                                                <th>Action à mener</th>
                                                <th>Quantité</th>
                                                <th>Unité de mesure</th>
                                                <th>Infrastructure</th>
                                                <th>Bénéficiaire</th>
                                            </tr>
                                        </thead>
                                        <tbody id="beneficiaire-table-body">
                                            <!-- Données chargées dynamiquement -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="cloture-section mt-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="dateCloture">Date de clôture</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="far fa-calendar"></i></span>
                                        <input type="date" class="form-control" name="dateCloture" id="dateCloture">
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="descriptionCloture">Description</label>
                                    <textarea class="form-control" name="descriptionCloture" id="descriptionCloture" rows="2" placeholder="Raison de la clôture..."></textarea>
                                </div>
                                
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger cloture-btn w-100" onclick="cloturerProjet()">
                                        <i class="fas fa-lock me-2"></i> Clôturer le projet
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <a href="#" id="voir-liste-link" class="toggle-list-btn">
                                    <i class="fas fa-list me-2"></i>
                                    Voir la liste complète des projets clôturés
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Liste des projets clôturés (cachée par défaut) -->
    <div class="row mt-4" id="liste-projets" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title text-white">
                        <i class="fas fa-list-ol me-2"></i>
                        Liste des projets clôturés
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
                                {{--@foreach ($projets as $projet)
                                    @php
                                        $statutsProjet = $statuts->where('code_projet', $projet->code_projet);
                                        $statut = $statutsProjet->firstWhere('codeSStatu', '07');
                                    @endphp

                                    @if ($statut)
                                    <tr>
                                        <td>{{ $projet->code_projet }}</td>
                                        <td>{{ $projet->sousDomaine?->Domaine?->libelle }}</td>
                                        <td>{{ $projet->date_demarrage_prevue ? date('d/m/Y', strtotime($projet->date_demarrage_prevue)) : '-' }}</td>
                                        <td>{{ $projet->date_fin_prevue ? date('d/m/Y', strtotime($projet->date_fin_prevue)) : '-' }}</td>
                                        <td class="text-end">{{ $projet->cout_projet ? number_format($projet->cout_projet, 0, ',', ' ') : '-' }}</td>
                                        <td>{{ $projet->devise?->code_long ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-danger">{{ $statut->statut_libelle }}</span>
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach--}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmation -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Confirmation de clôture
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmationMessage">Êtes-vous sûr de vouloir clôturer ce projet ? Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Annuler
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmCloture">
                        <i class="fas fa-check me-1"></i> Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des projets clôturés');
        
        // Affichage de la date actuelle
        updateCurrentDate();
        setInterval(updateCurrentDate, 1000);
        
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
    
    function initDataTable(tableId) {
        $('#' + tableId).DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
            },
            responsive: true,
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            pageLength: 10
        });
    }
    
    function checkProjectDetails() {
        const codeProjet = $('#code_projet').val();
        if (!codeProjet) return;
        
        $.ajax({
            url: '{{ url("/fetchProjectDetails")}}',
            method: 'GET',
            data: { 
                _token: '{{ csrf_token() }}',
                code_projet: codeProjet 
            },
            beforeSend: function() {
                $('#code_projet').addClass('loading');
            },
            success: function(response) {
                $('#date_debut').val(response.date_debut);
                $('#date_fin').val(response.date_fin);
                $('#cout').val(formatNumberInput(response.cout));
                $('#statutInput').val(response.statutInput);
                $('#codeProjetHidden').val(response.codeProjet);
                $('#devise').val(response.devise);
                
                updateTableData(response.actions || []);
                
                $('.form-control').addClass('highlight');
                setTimeout(() => $('.form-control').removeClass('highlight'), 1500);
            },
            complete: function() {
                $('#code_projet').removeClass('loading');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: xhr.responseJSON?.message || 'Une erreur est survenue'
                });
            }
        });
    }
    
    function updateTableData(data) {
        const tbody = $('#beneficiaire-table-body');
        tbody.empty();
        
        if (data.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center text-muted">Aucune action disponible pour ce projet</td></tr>');
            return;
        }
        
        data.forEach(item => {
            const row = `
                <tr class="action" data-id="${item.code}">
                    <td class="num_ordre_cell">${item.Num_ordre}</td>
                    <td>${item.action_libelle}</td>
                    <td>${item.Quantite}</td>
                    <td>${item.Unite_mesure || '-'}</td>
                    <td>${item.infrastructure_libelle || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary beneficiaire-btn" 
                                data-bs-toggle="modal" data-bs-target="#beneficiaireModal"
                                data-projet="${item.CodeProjet}" data-ordre="${item.Num_ordre}">
                            <i class="fas fa-user-plus me-1"></i> Bénéficiaires
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
    
    function formatNumberInput(number) {
        if (!number) return '';
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }
    
    function updateCurrentDate() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        document.getElementById('date-now').textContent = now.toLocaleDateString('fr-FR', options);
    }
    
    function cloturerProjet() {
        const codeProjet = $('#code_projet').val();
        const dateCloture = $('#dateCloture').val();
        
        if (!codeProjet) {
            Swal.fire({
                icon: 'warning',
                title: 'Attention',
                text: 'Veuillez sélectionner un projet à clôturer'
            });
            return;
        }
        
        if (!dateCloture) {
            Swal.fire({
                icon: 'warning',
                title: 'Attention',
                text: 'Veuillez spécifier une date de clôture'
            });
            return;
        }
        
        // Afficher le modal de confirmation
        $('#confirmationModal').modal('show');
    }
    
    // Confirmation de clôture
    $('#confirmCloture').click(function() {
        const codeProjet = $('#code_projet').val();
        const dateCloture = $('#dateCloture').val();
        const description = $('#descriptionCloture').val();
        
        $.ajax({
            url: '{{ url("/cloturer-projet") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                code_projet: codeProjet,
                date_cloture: dateCloture,
                description: description,
                ecran_id: $('#ecran_id').val()
            },
            beforeSend: function() {
                $('#confirmCloture').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Clôture en cours...');
            },
            success: function(response) {
                if (response.success) {
                    $('#confirmationModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Succès',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erreur',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: xhr.responseJSON?.message || 'Une erreur est survenue lors de la clôture'
                });
            },
            complete: function() {
                $('#confirmCloture').prop('disabled', false).html('<i class="fas fa-check me-1"></i> Confirmer');
            }
        });
    });
    
    function goBack() {
        window.history.back();
    }
</script>
@endsection