{{-- resources/views/approbations/dashboard.blade.php --}}
@extends('layouts.app')

<style>
  :root {
    --primary:#3b82f6; --primary-light:#dbeafe;
    --success:#10b981; --success-light:#d1fae5;
    --danger:#ef4444; --danger-light:#fee2e2;
    --warning:#f59e0b; --warning-light:#fef3c7;
    --secondary:#6b7280; --secondary-light:#f3f4f6;
    --dark:#111827; --light:#f8fafc; --border:#e5e7eb;
    --shadow:0 1px 3px rgba(0,0,0,.1),0 1px 2px rgba(0,0,0,.06);
    --shadow-lg:0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -2px rgba(0,0,0,.05);
  }
  .dashboard-container{ background:var(--light); min-height:100vh; }
  .dashboard-header{ background:#fff; border-bottom:1px solid var(--border); padding:1.5rem 0; margin-bottom:2rem; }
  .header-content{ display:flex; align-items:center; gap:2rem; justify-content:space-between; }
  .header-title h1{ font-size:1.75rem; font-weight:700; color:var(--dark); margin:0; }
  .header-title p{ color:var(--secondary); margin:.10rem 0 0; }

  .stats-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; margin-bottom:1.25rem; }
  .stat-card{ background:#fff; border-radius:12px; padding:1rem 1.25rem; box-shadow:var(--shadow); border-left:4px solid var(--primary); }
  .stat-card.pending { border-left-color:var(--warning); }
  .stat-card.approved{ border-left-color:var(--success); }
  .stat-card.rejected{ border-left-color:var(--danger); }
  .stat-value{ font-size:1.75rem; font-weight:800; }
  .stat-label{ color:var(--secondary); font-weight:500; }

  .main-card{ background:#fff; border-radius:12px; box-shadow:var(--shadow); overflow:hidden; margin-bottom:2rem; }
  .card-header{ padding:1rem 1.25rem; border-bottom:1px solid var(--border); background:var(--light); }
  .card-toolbar{ display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; }
  .bulk-actions{ display:flex; gap:.5rem; align-items:center; }
  .btn-modern{ border-radius:8px; font-weight:600; padding:.5rem .75rem; border:1px solid var(--border); background:#fff; display:inline-flex; gap:.5rem; align-items:center; transition:.15s; }
  .btn-modern:disabled{ opacity:.55; cursor:not-allowed; }
  .btn-modern:not(:disabled):hover{ transform:translateY(-1px); }
  .btn-success{ background:var(--success); border-color:var(--success); color:#fff; }
  .btn-danger{ background:var(--danger); border-color:var(--danger); color:#fff; }
  .btn-primary{ background:var(--primary); border-color:var(--primary); color:#fff; }
  .btn-outline{ background:#fff; color:var(--dark); }

  /* Navigation par onglets */
  .nav-tabs-modern{ border-bottom:1px solid var(--border); padding:0 1.25rem; background:var(--light); }
  .nav-tabs-modern .nav-link{ border:none; padding:1rem 1.5rem; font-weight:600; color:var(--secondary); background:transparent; position:relative; transition:all 0.2s ease; }
  .nav-tabs-modern .nav-link:hover{ color:var(--primary); background:rgba(59,130,246,0.05); }
  .nav-tabs-modern .nav-link.active{ color:var(--primary); background:#fff; border-bottom:3px solid var(--primary); }
  .nav-tabs-modern .nav-link .badge{ font-size:0.7rem; margin-left:0.5rem; }

  .tab-content{ background:#fff; }
  .tab-pane{ display:none; }
  .tab-pane.active{ display:block; }

  .search-box{ position:relative; margin-left:auto; }
  .search-box input{ padding-left:2.25rem; min-width:260px; }
  .search-icon{ position:absolute; left:.6rem; top:50%; transform:translateY(-50%); color:var(--secondary); }

  .table-modern{ width:100%; border-collapse:collapse; }
  .table-modern thead{ background:var(--light); }
  .table-modern th{ padding:.875rem; font-size:.8rem; font-weight:700; border-bottom:2px solid var(--border); text-transform:uppercase; letter-spacing:.02em; }
  .table-modern td{ padding:1rem; border-bottom:1px solid var(--border); vertical-align:middle; }
  .table-modern tbody tr:hover{ background:#fafbfc; }
  .table-modern tbody tr.table-light{ background:var(--secondary-light); }

  .checkbox-modern{ width:18px; height:18px; border-radius:4px; border:2px solid var(--border); background:#fff; position:relative; cursor:pointer; }
  .checkbox-modern:checked{ background:var(--primary); border-color:var(--primary); }
  .checkbox-modern:checked::after{ content:'✓'; position:absolute; color:#fff; font-size:12px; font-weight:800; top:50%; left:50%; transform:translate(-50%,-54%); }
  
  .help-step ul li {
    color: black !important;
  }

  /* Styles pour tooltips rapprochés */
  /* Raccourcit LA distance entre l’élément et le tooltip (Bootstrap 5.3+) */
  .custom-tooltip {
    --bs-tooltip-margin: 2px; /* <- clé pour éviter les tooltips trop éloignés */
  }

  /* Style */
  .custom-tooltip .tooltip-inner {
    background: #1f2937;
    color: #fff;
    border-radius: 6px;
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 500;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
    max-width: 300px;
  }

  .custom-tooltip.bs-tooltip-top .tooltip-arrow::before   { border-top-color:    #1f2937; }
  .custom-tooltip.bs-tooltip-bottom .tooltip-arrow::before{ border-bottom-color: #1f2937; }
  .custom-tooltip.bs-tooltip-start .tooltip-arrow::before { border-left-color:   #1f2937; }
  .custom-tooltip.bs-tooltip-end .tooltip-arrow::before   { border-right-color:  #1f2937; }

  /* Avatars (pile) */
  .avatar-stack{ display:flex; align-items:center; margin:0 -4px; }
  .avatar{
    width:32px; height:32px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:.75rem; font-weight:700; color:#fff;
    border:2px solid #fff; box-shadow:0 2px 4px rgba(0,0,0,.1);
    margin-left:-8px; transition:.15s;
  }
  .avatar:first-child{ margin-left:0; }
  .avatar:hover{ transform:scale(1.1); z-index:1; }
  .avatar-success{ background:var(--success); }
  .avatar-warning{ background:var(--warning); }
  .avatar-danger{ background:var(--danger); }
  .avatar-secondary{ background:var(--secondary); }


  .quick-actions .btn-modern + .tooltip {
      margin-top: -5px !important;
  }

  .bulk-actions .btn-modern + .tooltip {
      margin-top: -5px !important;
  }

  .badge-modern{ padding:.35rem .65rem; border-radius:999px; font-size:.75rem; font-weight:700; display:inline-flex; gap:.35rem; align-items:center; }
  .badge-warning{ background:var(--warning-light); color:#92400e; }
  .badge-secondary{ background:var(--secondary-light); color:var(--secondary); }
  .badge-light{ background:#f8fafc; color:var(--secondary); border:1px solid var(--border); }
  .badge-success{ background:var(--success-light); color:#065f46; }
  .badge-danger{ background:var(--danger-light); color:#7f1d1d; }

  .quick-actions{ display:flex; gap:.4rem; flex-wrap:wrap; }
  .quick-actions .btn-modern{ padding:.375rem .55rem; }

  .modal-modern .modal-content{ border-radius:12px; border:none; box-shadow:var(--shadow-lg); }
  .modal-modern .modal-header{ border-bottom:1px solid var(--border); padding:1rem 1.25rem; }
  .modal-modern .modal-body{ padding:1rem 1.25rem; }
  .modal-modern .modal-footer{ border-top:1px solid var(--border); padding:1rem 1.25rem; }

  .offcanvas-modern{ width:860px!important; max-width:100vw; }
  .offcanvas-modern .offcanvas-header{ border-bottom:1px solid var(--border); padding:1rem 1.25rem; }
  .offcanvas-modern .offcanvas-body{ padding:0; }
  #oc-frame{ border:0; width:100%; height:100%; min-height:70vh; }

  .empty-state{ text-align:center; padding:3rem 1rem; color:var(--secondary); }
  .empty-state-icon{ font-size:3rem; margin-bottom:1rem; opacity:.55; }
  .dataTables_info, .paginate_button, .dt-button {color: black !important;}

  /* Toast Notifications */
  .toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 400px;
  }
  .toast {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    animation: slideInRight 0.3s ease-out;
    border-left: 4px solid var(--primary);
  }
  .toast.success { border-left-color: var(--success); }
  .toast.error { border-left-color: var(--danger); }
  .toast.info { border-left-color: var(--primary); }
  .toast.warning { border-left-color: var(--warning); }
  .toast-icon {
    font-size: 1.25rem;
    flex-shrink: 0;
  }
  .toast.success .toast-icon { color: var(--success); }
  .toast.error .toast-icon { color: var(--danger); }
  .toast.info .toast-icon { color: var(--primary); }
  .toast.warning .toast-icon { color: var(--warning); }
  .toast-content {
    flex: 1;
    font-weight: 500;
    color: var(--dark);
  }
  .toast-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: var(--secondary);
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.2s;
  }
  .toast-close:hover { color: var(--dark); }
  @keyframes slideInRight {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  @keyframes slideOutRight {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
  .toast.hiding {
    animation: slideOutRight 0.3s ease-in forwards;
  }

  /* Amélioration des transitions */
  .table-modern tbody tr {
    transition: background-color 0.2s ease, transform 0.1s ease;
  }
  .table-modern tbody tr:hover {
    transform: translateX(2px);
  }
  .btn-modern {
    transition: all 0.2s ease;
  }
  .btn-modern:not(:disabled):active {
    transform: translateY(0);
  }

  /* Bannière d'aide */
  .help-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-lg);
    animation: slideDown 0.4s ease-out;
  }
  .help-banner-content {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: #fff;
  }
  .help-banner-icon {
    font-size: 2rem;
    flex-shrink: 0;
  }
  .help-banner-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }
  .help-banner-text strong {
    font-size: 1.1rem;
  }
  .help-banner-text span {
    font-size: 0.9rem;
    opacity: 0.95;
  }
  .help-banner-actions {
    display: flex;
    gap: 0.75rem;
    align-items: center;
  }
  .btn-help-primary {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .btn-help-primary:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-1px);
  }
  .btn-help-close {
    background: transparent;
    border: none;
    color: #fff;
    font-size: 1.25rem;
    cursor: pointer;
    padding: 0.25rem;
    opacity: 0.8;
    transition: opacity 0.2s;
  }
  .btn-help-close:hover {
    opacity: 1;
  }
  .help-icon {
    font-size: 0.75rem;
    margin-left: 0.5rem;
    opacity: 0.6;
    cursor: help;
  }
  @keyframes slideDown {
    from {
      transform: translateY(-20px);
      opacity: 0;
    }
    to {
      transform: translateY(0);
      opacity: 1;
    }
  }

  /* Modal d'aide */
  .help-modal .modal-content {
    border-radius: 12px;
  }
  .help-modal .modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    border-radius: 12px 12px 0 0;
  }
  .help-step {
    display: none;
    padding: 1.5rem;
  }
  .help-step.active {
    display: block;
    animation: fadeIn 0.3s ease-in;
  }
  .help-step h5 {
    color: var(--primary);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .help-step ul {
    list-style: none;
    padding-left: 0;
  }
  .help-step li {
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    background: var(--light);
    border-left: 3px solid var(--primary);
    border-radius: 4px;
  }
  .help-step li strong {
    color: var(--primary);
  }
  .help-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border);
    background: var(--light);
  }
  .help-step-indicator {
    font-weight: 600;
    color: var(--secondary);
  }
  .help-tips {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @media (max-width: 1024px){
    .header-content{ flex-direction:column; align-items:flex-start; gap:1rem; }
    .search-box{ margin-left:0; width:100%; }
    .search-box input{ min-width:auto; width:100%; }
    .nav-tabs-modern .nav-link{ padding:0.75rem 1rem; font-size:0.9rem; }
    .toast-container {
      right: 10px;
      left: 10px;
      max-width: none;
    }
  }
</style>

@section('content')
<div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Etudes projets </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Approbation</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Approuver</li>
                        </ol>
                        <div class="row">
                            <script>
                                setInterval(function() {
                                    document.getElementById('date-now').textContent = getCurrentDate();
                                }, 1000);

                                function getCurrentDate() {
                                    var currentDate = new Date();
                                    return currentDate.toLocaleString();
                                }
                            </script>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
 

    {{-- Stats --}}
    <div class="stats-grid">
      <div class="stat-card pending">
        <div class="stat-value">{{ $pendingCount ?? 0 }}</div>
        <div class="stat-label">
          <i class="fas fa-clock text-warning me-1"></i> En attente
          <i class="fas fa-info-circle help-icon" 
             data-bs-toggle="tooltip" 
             title="Nombre de demandes en attente de votre validation"></i>
        </div>
      </div>
      <div class="stat-card approved">
        <div class="stat-value">{{ $approvedCount ?? 0 }}</div>
        <div class="stat-label">
          <i class="fas fa-check-circle text-success me-1"></i> Approuvées
          <i class="fas fa-info-circle help-icon" 
             data-bs-toggle="tooltip" 
             title="Nombre de demandes que vous avez approuvées"></i>
        </div>
      </div>
      <div class="stat-card rejected">
        <div class="stat-value">{{ $rejectedCount ?? 0 }}</div>
        <div class="stat-label">
          <i class="fas fa-times-circle text-danger me-1"></i> Rejetées
          <i class="fas fa-info-circle help-icon" 
             data-bs-toggle="tooltip" 
             title="Nombre de demandes que vous avez rejetées"></i>
        </div>
      </div>
    </div>

    {{-- Carte principale avec onglets --}}
    <div class="main-card">
      {{-- En-tête avec onglets --}}
      <div class="card-header">
        <div class="card-toolbar" style="text-align: right;">
          <button type="button" class="btn-modern btn-outline" onclick="openHelpGuide()" 
                  data-bs-toggle="tooltip" title="Guide d'utilisation du workflow">
            <i class="fas fa-question-circle"></i> Aide
          </button>
          <div class="bulk-actions">
            <button type="button" id="btn-approve-selected" class="btn-modern btn-success" disabled 
                    aria-label="Approuver la sélection"
                    data-bs-toggle="tooltip" 
                    title="Approuver toutes les demandes sélectionnées">
              <i class="fas fa-check"></i> Approuver
            </button>
            <button type="button" id="btn-reject-selected" class="btn-modern btn-danger" disabled 
                    aria-label="Rejeter la sélection"
                    data-bs-toggle="tooltip" 
                    title="Rejeter toutes les demandes sélectionnées">
              <i class="fas fa-times"></i> Rejeter
            </button>
            <button type="button" id="btn-delegate-selected" class="btn-modern btn-primary" disabled 
                    aria-label="Déléguer la sélection"
                    data-bs-toggle="tooltip" 
                    title="Déléguer les demandes sélectionnées à un autre acteur">
              <i class="fas fa-user-friends"></i> Déléguer
            </button>
          </div>
        </div>

        {{-- Navigation par onglets --}}
        <ul class="nav nav-tabs nav-tabs-modern" id="approvalTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'current' ? 'active' : '' }}" 
                    id="current-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#current-pane" 
                    type="button" 
                    role="tab" 
                    aria-controls="current-pane" 
                    aria-selected="{{ $activeTab === 'current' ? 'true' : 'false' }}">
              <i class="fas fa-inbox me-2"></i>
              Approbations en cours
              @if($pendingCount > 0)
                <span class="badge bg-warning text-dark">{{ $pendingCount }}</span>
              @endif
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $activeTab === 'history' ? 'active' : '' }}" 
                    id="history-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#history-pane" 
                    type="button" 
                    role="tab" 
                    aria-controls="history-pane" 
                    aria-selected="{{ $activeTab === 'history' ? 'true' : 'false' }}">
              <i class="fas fa-history me-2"></i>
              Historique des approbations
            </button>
          </li>
        </ul>
      </div>

      {{-- Contenu des onglets --}}
      <div class="tab-content" id="approvalTabsContent">
        {{-- Onglet 1: Approbations en cours --}}
        <div class="tab-pane fade {{ $activeTab === 'current' ? 'show active' : '' }}" 
             id="current-pane" 
             role="tabpanel" 
             aria-labelledby="current-tab">
          
          @include('approbations.partials.approvals-table', [
            'rows' => $currentRows,
            'tableId' => 'tb-current-approvals',
            'emptyMessage' => 'Aucune approbation en attente pour le moment.',
            'showActions' => true
          ])

        </div>

        {{-- Onglet 2: Historique --}}
        <div class="tab-pane fade {{ $activeTab === 'history' ? 'show active' : '' }}" 
             id="history-pane" 
             role="tabpanel" 
             aria-labelledby="history-tab">
          
          @include('approbations.partials.approvals-table', [
            'rows' => $historyRows,
            'tableId' => 'tb-history-approvals',
            'emptyMessage' => 'Aucun historique d\'approbation pour le moment.',
            'showActions' => false
          ])

        </div>
      </div>

      {{-- Pied de page commun --}}
      @if(($currentRows->count() > 0 || $historyRows->count() > 0))
        <div class="card-footer text-muted small p-3" style="background: var(--light);">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
           
            <div>
              {{-- Pagination dynamique selon l'onglet actif --}}
              <div id="current-pagination" class="{{ $activeTab === 'current' ? '' : 'd-none' }}">
                {{ $currentRows->links('pagination::bootstrap-5') }}
              </div>
              <div id="history-pagination" class="{{ $activeTab === 'history' ? '' : 'd-none' }}">
                {{ $historyRows->links('pagination::bootstrap-5') }}
              </div>
            </div>
          </div>
        </div>
      @endif
    </div>
  </div>


{{-- Modal Délégation --}}
<div class="modal fade modal-modern" id="dlgDelegate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title"><i class="fas fa-user-friends text-primary me-2"></i> Déléguer l'approbation</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
    </div>
    <div class="modal-body">
      <label class="form-label fw-semibold">Acteur destinataire</label>
      <select name="delegate-to" id="delegate-to" class="form-select">
        <option value="">— Choisir un acteur —</option>
        @foreach ($Users as $user)
          @php($a = $user->acteur)
          <option value="{{ $a?->code_acteur }}">{{ $a?->libelle_court }} - {{ $a?->libelle_long }}</option>
        @endforeach
      </select>
      <div class="small text-muted mt-2">
        <i class="fas fa-info-circle"></i> Le destinataire pourra traiter ces approbations.
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn-modern btn-outline" data-bs-dismiss="modal"><i class="fas fa-times"></i> Annuler</button>
      <button type="button" class="btn-modern btn-primary" id="btn-do-delegate"><i class="fas fa-paper-plane"></i> Confirmer</button>
    </div>
  </div></div>
</div>

{{-- Offcanvas Guide d'utilisation (right panel) --}}
<div class="offcanvas offcanvas-end offcanvas-modern" tabindex="-1" id="oc-help" aria-labelledby="oc-help-label" style="width: 600px !important;">
  <div class="offcanvas-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
    <h5 class="offcanvas-title" id="oc-help-label">
      <i class="fas fa-graduation-cap me-2"></i> Guide d'utilisation du workflow de validation
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
  </div>
  <div class="offcanvas-body" style="padding: 0;">
    <div style="padding: 1.5rem;">
      {{-- Étape 1 --}}
      <div id="help-step-1" class="help-step active">
        <h5><i class="fas fa-info-circle"></i> Comprendre le système d'approbation</h5>
        <p>Le workflow de validation permet de gérer les demandes d'approbation de manière structurée et sécurisée.</p>
        <ul>
          <li><strong>📊 Tableau des approbations :</strong> Affiche toutes les demandes nécessitant votre validation</li>
          <li><strong>📈 Statistiques :</strong> Consultez rapidement le nombre de demandes en attente, approuvées ou rejetées</li>
          <li><strong>🔄 Onglets :</strong> Naviguez entre les approbations en cours et l'historique</li>
        </ul>
      </div>

      {{-- Étape 2 --}}
      <div id="help-step-2" class="help-step">
        <h5><i class="fas fa-check-circle"></i> Comment approuver une demande</h5>
        <ul>
          <li><strong>Approbation individuelle :</strong> Cliquez sur le bouton <span class="badge-modern badge-success"><i class="fas fa-check"></i></span> dans la colonne "Actions"</li>
          <li><strong>Approbation multiple :</strong> 
            <ol>
              <li>Cochez les cases des demandes à approuver</li>
              <li>Cliquez sur le bouton "Approuver" en haut de la page</li>
            </ol>
          </li>
          <li><strong>Double-clic :</strong> Double-cliquez sur une ligne pour consulter les détails avant d'approuver</li>
        </ul>
      </div>

      {{-- Étape 3 --}}
      <div id="help-step-3" class="help-step">
        <h5><i class="fas fa-times-circle"></i> Comment rejeter une demande</h5>
        <ul>
          <li><strong>Rejet individuel :</strong> Cliquez sur le bouton <span class="badge-modern badge-danger"><i class="fas fa-times"></i></span></li>
          <li><strong>Rejet multiple :</strong> Sélectionnez plusieurs demandes et cliquez sur "Rejeter"</li>
          <li><strong>⚠️ Important :</strong> Le rejet arrête immédiatement le processus d'approbation pour cette demande</li>
        </ul>
      </div>

      {{-- Étape 4 --}}
      <div id="help-step-4" class="help-step">
        <h5><i class="fas fa-user-friends"></i> Comment déléguer une approbation</h5>
        <ul>
          <li><strong>Délégation individuelle :</strong> Cliquez sur le bouton <span class="badge-modern badge-primary"><i class="fas fa-user-friends"></i></span></li>
          <li><strong>Délégation multiple :</strong> Sélectionnez les demandes et cliquez sur "Déléguer"</li>
          <li><strong>Choisir un acteur :</strong> Dans la fenêtre qui s'ouvre, sélectionnez l'acteur à qui vous souhaitez déléguer</li>
          <li><strong>💡 Astuce :</strong> La délégation est utile lorsque vous êtes indisponible ou que la demande relève d'un autre domaine</li>
        </ul>
      </div>

      {{-- Étape 5 --}}
      <div id="help-step-5" class="help-step">
        <h5><i class="fas fa-eye"></i> Consulter les détails d'une demande</h5>
        <ul>
          <li><strong>Bouton consulter :</strong> Cliquez sur <span class="badge-modern badge-outline"><i class="fas fa-eye"></i></span> pour voir tous les détails</li>
          <li><strong>Double-clic :</strong> Double-cliquez directement sur une ligne du tableau</li>
          <li><strong>Panel latéral :</strong> Les détails s'affichent dans un panneau à droite de l'écran</li>
        </ul>
      </div>

      {{-- Étape 6 --}}
      <div id="help-step-6" class="help-step">
        <h5><i class="fas fa-users"></i> Comprendre les statuts et les approbateurs</h5>
        <ul>
          <li><strong>🟡 En attente :</strong> La demande attend votre action</li>
          <li><strong>🟢 En cours :</strong> La demande est en cours de traitement</li>
          <li><strong>✅ Approuvé :</strong> Vous avez approuvé cette étape</li>
          <li><strong>❌ Rejeté :</strong> La demande a été rejetée</li>
          <li><strong>👥 Avatars :</strong> Les cercles colorés représentent les différents approbateurs et leur statut</li>
        </ul>
      </div>
    </div>
    <div class="help-navigation">
      <button type="button" class="btn-modern btn-outline" id="prev-help-step" onclick="changeHelpStep(-1)" disabled>
        <i class="fas fa-arrow-left"></i> Précédent
      </button>
      <div class="help-step-indicator">
        <span id="help-step-number">1</span> / 6
      </div>
      <button type="button" class="btn-modern btn-primary" id="next-help-step" onclick="changeHelpStep(1)">
        Suivant <i class="fas fa-arrow-right"></i>
      </button>
    </div>
  </div>
</div>

{{-- Offcanvas (right panel) --}}
<div class="offcanvas offcanvas-end offcanvas-modern" tabindex="-1" id="oc-view" aria-labelledby="oc-view-label">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="oc-view-label"><i class="fas fa-file-alt me-2"></i> Consultation</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
  </div>
  <div class="offcanvas-body p-0">
    <iframe id="oc-frame" src="about:blank"></iframe>
  </div>
</div>

<script>
(function(){
  // ========= Config/refs =========
  const CSRF        = '{{ csrf_token() }}';
  const ACT_URL_TPL = @json(route('approbations.act', ['stepInstance' => '__ID__']));
  const actUrl      = (id) => ACT_URL_TPL.replace('__ID__', String(id));

  // ========= Toast Notifications =========
  function showToast(message, type = 'info', duration = 4000) {
    const container = getOrCreateToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
      success: 'fa-check-circle',
      error: 'fa-exclamation-circle',
      warning: 'fa-exclamation-triangle',
      info: 'fa-info-circle'
    };
    
    toast.innerHTML = `
      <i class="fas ${icons[type] || icons.info} toast-icon"></i>
      <div class="toast-content">${message}</div>
      <button class="toast-close" aria-label="Fermer">&times;</button>
    `;
    
    container.appendChild(toast);
    
    const closeBtn = toast.querySelector('.toast-close');
    const closeToast = () => {
      toast.classList.add('hiding');
      setTimeout(() => toast.remove(), 300);
    };
    
    closeBtn.addEventListener('click', closeToast);
    setTimeout(closeToast, duration);
  }
  
  function getOrCreateToastContainer() {
    let container = document.getElementById('toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toast-container';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    return container;
  }
  
  window.showToast = showToast;

  // Offcanvas
  const $oc    = document.getElementById('oc-view');
  const $frame = document.getElementById('oc-frame');
  let ocInstance = null;

  // ========= Gestion du guide d'aide =========
  let currentHelpStep = 1;
  const totalHelpSteps = 6;

  function updateHelpStepUI() {
    // Masquer toutes les étapes
    document.querySelectorAll('.help-step').forEach(step => {
      step.classList.remove('active');
    });

    // Afficher l'étape actuelle
    const currentStepEl = document.getElementById(`help-step-${currentHelpStep}`);
    if (currentStepEl) {
      currentStepEl.classList.add('active');
    }

    // Mettre à jour l'indicateur
    const stepNumberEl = document.getElementById('help-step-number');
    if (stepNumberEl) {
      stepNumberEl.textContent = currentHelpStep;
    }

    // Gérer les boutons
    const prevBtn = document.getElementById('prev-help-step');
    const nextBtn = document.getElementById('next-help-step');
    
    if (prevBtn) {
      prevBtn.disabled = currentHelpStep === 1;
    }
    
    if (nextBtn) {
      if (currentHelpStep === totalHelpSteps) {
        nextBtn.innerHTML = '<i class="fas fa-check"></i> Terminer';
        nextBtn.onclick = () => {
          const offcanvasEl = document.getElementById('oc-help');
          if (offcanvasEl) {
            const offcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if (offcanvas) offcanvas.hide();
          }
          dismissHelpBanner();
        };
      } else {
        nextBtn.innerHTML = 'Suivant <i class="fas fa-arrow-right"></i>';
        nextBtn.onclick = () => changeHelpStep(1);
      }
    }
  }

  function changeHelpStep(direction) {
    currentHelpStep += direction;
    if (currentHelpStep < 1) currentHelpStep = 1;
    if (currentHelpStep > totalHelpSteps) currentHelpStep = totalHelpSteps;
    updateHelpStepUI();
  }

  function openHelpGuide() {
    currentHelpStep = 1;
    updateHelpStepUI();
    const offcanvasEl = document.getElementById('oc-help');
    if (offcanvasEl) {
      const offcanvas = new bootstrap.Offcanvas(offcanvasEl);
      offcanvas.show();
    }
  }

  function dismissHelpBanner() {
    const banner = document.getElementById('help-banner');
    if (banner) {
      banner.style.animation = 'slideOutRight 0.3s ease-in forwards';
      setTimeout(() => {
        banner.style.display = 'none';
        localStorage.setItem('workflow_help_dismissed', 'true');
      }, 300);
    }
  }

  // Exposer les fonctions globalement pour les attributs onclick
  window.openHelpGuide = openHelpGuide;
  window.dismissHelpBanner = dismissHelpBanner;
  window.changeHelpStep = changeHelpStep;

  function checkShowHelpBanner() {
    const dismissed = localStorage.getItem('workflow_help_dismissed');
    if (!dismissed) {
      const banner = document.getElementById('help-banner');
      if (banner) {
        setTimeout(() => {
          banner.style.display = 'block';
        }, 1000);
      }
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    if (window.bootstrap?.Offcanvas) {
      ocInstance = new bootstrap.Offcanvas($oc);
      $oc.addEventListener('hidden.bs.offcanvas', () => { try { $frame.src = 'about:blank'; } catch(_){} });
    }

    // DataTable sur l'onglet actif uniquement
    initDataTable('{{ auth()->user()->acteur->libelle_court }} {{ auth()->user()->acteur->libelle_long }}', 'tb-current-approvals', 'Liste des approbations en cours' );
    initDataTable('{{ auth()->user()->acteur->libelle_court }} {{ auth()->user()->acteur->libelle_long }}', 'tb-history-approvals', 'Historique des approbations');

    // Tooltips initial
    initTooltips();

    // Réinitialisations liées aux overlays
    const resetTT = () => setTimeout(initTooltips, 200);
    document.getElementById('oc-view')?.addEventListener('shown.bs.offcanvas', resetTT);
    document.getElementById('oc-help')?.addEventListener('shown.bs.offcanvas', resetTT);
    document.getElementById('dlgDelegate')?.addEventListener('shown.bs.modal', resetTT);

    // Vérifier si on doit afficher la bannière d'aide
    checkShowHelpBanner();
  });

  // ========= Tooltips (unique) =========
  window.initTooltips = function initTooltips() {
    if (!window.bootstrap?.Tooltip) return;

    // Détruire puis recréer proprement
    document.querySelectorAll('[data-bs-toggle="tooltip"], [title]').forEach(el => {
      const title = el.getAttribute('title');
      if (!title) return;

      // Détruire l’instance existante si besoin
      bootstrap.Tooltip.getInstance(el)?.dispose();

      // Base config – rapproché et robuste en table/overflow
      const cfg = {
        container: document.body,        // évite les soucis de conteneurs transform/overflow
        trigger: 'hover focus',
        placement: 'auto',               // choisit automatiquement le meilleur côté
        boundary: 'viewport',            // empêche les décalages à cause d’overflow
        fallbackPlacements: ['top', 'bottom', 'right', 'left'],
        delay: { show: 80, hide: 80 },
        customClass: 'custom-tooltip',
        // Rapprocher le tooltip (distance Popper). 2px est très proche ; ajustez si besoin.
        offset: [0, 2],
      };

      // Exception pour les avatars : encore plus proche (voire “léger chevauchement” en négatif)
      if (el.classList.contains('avatar')) cfg.offset = [0, 0];

      new bootstrap.Tooltip(el, cfg);
    });
  };


  // MutationObserver pour regénérer les tooltips après modifs DOM
  const observer = new MutationObserver((mut) => {
    if (mut.some(m => m.type === 'childList' && m.addedNodes.length)) setTimeout(initTooltips, 150);
  });
  observer.observe(document.body, { childList: true, subtree: true });

  // ========= Gestion changement d’onglet =========
  document.getElementById('approvalTabs')?.addEventListener('shown.bs.tab', (event) => {
    const target = event.target.getAttribute('data-bs-target');
    const tab = target === '#current-pane' ? 'current' : 'history';

    // mettre à jour l’URL
    const url = new URL(window.location);
    url.searchParams.set('tab', tab);
    window.history.replaceState({}, '', url);

    // tips + pagination
    document.getElementById('tab-tip-current')?.classList.toggle('d-none', tab !== 'current');
    document.getElementById('tab-tip-history')?.classList.toggle('d-none', tab !== 'history');
    document.getElementById('current-pagination')?.classList.toggle('d-none', tab !== 'current');
    document.getElementById('history-pagination')?.classList.toggle('d-none', tab !== 'history');

    // reset selections
    resetSelections(tab);
    setTimeout(initTooltips, 100);
  });

  function resetSelections(tab){
    const tableId = tab === 'current' ? 'tb-current-approvals' : 'tb-history-approvals';
    document.querySelectorAll('#'+tableId+' .chk-row').forEach(cb => cb.checked = false);
    const chkAll = document.getElementById('chk-all-'+tableId);
    if (chkAll) chkAll.checked = false;
    setBulkButtonsState(); // désactive les boutons masse
  }

  // ========= Helpers DOM par table =========
  function activeTableId(){
    return document.querySelector('.tab-pane.show.active table')?.id || 'tb-current-approvals';
  }
  function $activeTbody(){
    const id = activeTableId();
    return document.querySelector('#'+id+' tbody');
  }
  function $activeChkAll(){
    return document.getElementById('chk-all-'+activeTableId());
  }

  // ========= Boutons de masse (uniquement onglet "current") =========
  const $btnOpenSel = document.getElementById('btn-open-selected');
  const $btnApprove = document.getElementById('btn-approve-selected');
  const $btnReject  = document.getElementById('btn-reject-selected');
  const $btnDeleg   = document.getElementById('btn-delegate-selected');

  function selectedRows(){
    const $tb = $activeTbody(); if (!$tb) return [];
    return [...$tb.querySelectorAll('tr')].filter(tr => tr.querySelector('.chk-row')?.checked);
  }
  function rowData(tr){
    try { return JSON.parse(tr.dataset.row || '{}'); } catch { return {}; }
  }
  function setBulkButtonsState(){
    // désactive tout si on n’est pas sur l’onglet "current"
    const onCurrent = activeTableId() === 'tb-current-approvals';
    const count = onCurrent ? selectedRows().length : 0;
    if ($btnApprove) $btnApprove.disabled = !onCurrent || !count;
    if ($btnReject ) $btnReject .disabled = !onCurrent || !count;
    if ($btnDeleg  ) $btnDeleg  .disabled = !onCurrent || !count;
    if ($btnOpenSel) $btnOpenSel.disabled = !onCurrent || count !== 1;
  }

  // Select all (par table)
  document.addEventListener('change', (e) => {
    // select all
    const chkAll = $activeChkAll();
    if (chkAll && e.target === chkAll){
      const $tb = $activeTbody();
      [...$tb.rows].forEach(tr => tr.querySelector('.chk-row')?.classList && (tr.querySelector('.chk-row').checked = chkAll.checked));
      setBulkButtonsState();
    }
    // changements sur lignes
    if (e.target.classList?.contains('chk-row')) setBulkButtonsState();
  });

  // ========= Offcanvas open =========
  function openPanel(url, title='Consultation'){
    if (!url) return;
    document.getElementById('oc-view-label').textContent = title;
    $frame.src = url;
    ocInstance?.show();
  }

  // ========= UI helpers =========
  function setRowBusy(tr, busy=true){
    tr.querySelectorAll('.btn-approve,.btn-reject,.btn-delegate,.btn-view').forEach(b => { b.disabled = busy; });
    tr.classList.toggle('opacity-75', !!busy);
  }
  function updateStatusBadge(tr, text, type='light'){
    const statusCell = tr.querySelector('td:nth-child(6)');
    const icons = { success:'fa-check-circle', light:'fa-info-circle', warning:'fa-clock' };
    const icon = icons[type] || 'fa-info-circle';
    if (statusCell){
      statusCell.innerHTML = `<span class="badge-modern badge-${type}"><i class="fas ${icon}"></i> ${text}</span>`;
    }
  }
  async function fetchJSON(url, {method='GET', body=null, headers={}}={}){
    const res = await fetch(url,{
      method,
      headers: {
        'Accept':'application/json',
        'Content-Type':'application/json',
        'X-Requested-With':'XMLHttpRequest',
        'X-CSRF-TOKEN': CSRF,
        ...headers
      },
      body: body ? JSON.stringify(body) : null
    });
    const ct = res.headers.get('content-type') || '';
    const data = ct.includes('application/json') ? await res.json() : { message: await res.text() };
    if (!res.ok || data.success === false) {
      throw new Error(data.message || 'Une erreur est survenue');
    }
    return data;
  }
  async function doAct(stepId, actionCode, meta=null){
    if (!stepId || String(stepId).toLowerCase()==='null') throw new Error('Étape invalide');
    return fetchJSON(actUrl(stepId), { method:'POST', body:{ action_code: actionCode, meta }});
  }

  // ========= Handlers par ligne (sur table active) =========
  document.addEventListener('dblclick', (e)=>{
    const tr = e.target.closest('tr[data-row]'); if (!tr) return;
    const d = rowData(tr);
    openPanel(tr.dataset.url, `${d.module||''} • ${d.type||''} #${d.target_id||''}`);
  });

  document.addEventListener('click', async (e) => {
    // restreindre au tableau actif
    const $tb = $activeTbody(); if (!$tb) return;
    const tr = e.target.closest('tr'); if (!tr || !tr.parentElement || tr.parentElement !== $tb) return;

    const d = rowData(tr);
    const isApprove = e.target.closest('.btn-approve');
    const isReject  = e.target.closest('.btn-reject');
    const isDeleg   = e.target.closest('.btn-delegate');
    const isView    = e.target.closest('.btn-view');

    if (isView) {
      openPanel(tr.dataset.url, `${d.module||''} • ${d.type||''} #${d.target_id||''}`);
      return;
    }
    if (!isApprove && !isReject && !isDeleg) return;

    try{
      setRowBusy(tr, true);
      if (isApprove){
        updateStatusBadge(tr, 'Validation en cours…', 'warning');
        await doAct(d.step_id, 'APPROUVER');
        updateStatusBadge(tr, 'Approuvé', 'success');
        showToast('Demande approuvée avec succès', 'success');
        // Rafraîchir la ligne après un court délai
        setTimeout(() => {
          tr.style.transition = 'opacity 0.3s';
          tr.style.opacity = '0.5';
        }, 500);
      } else if (isReject){
        updateStatusBadge(tr, 'Rejet en cours…', 'warning');
        await doAct(d.step_id, 'REJETER');
        updateStatusBadge(tr, 'Rejeté', 'light');
        showToast('Demande rejetée', 'info');
        setTimeout(() => {
          tr.style.transition = 'opacity 0.3s';
          tr.style.opacity = '0.5';
        }, 500);
      } else if (isDeleg){
        openDelegate([d.step_id]);
      }
    } catch(err){
      showToast(err.message || 'Une erreur est survenue', 'error');
    } finally {
      setRowBusy(tr, false);
      setBulkButtonsState();
    }
  });

  // ========= Actions de masse (onglet current uniquement) =========
  document.getElementById('btn-open-selected')?.addEventListener('click', () => {
    if (activeTableId() !== 'tb-current-approvals') return;
    const sel = selectedRows(); if (sel.length !== 1) return;
    const tr = sel[0], d = rowData(tr);
    openPanel(tr.dataset.url, `${d.module||''} • ${d.type||''} #${d.target_id||''}`);
  });
  document.getElementById('btn-approve-selected')?.addEventListener('click', () => bulkDo('APPROUVER'));
  document.getElementById('btn-reject-selected') ?.addEventListener('click', () => bulkDo('REJETER'));

  async function bulkDo(action){
    if (activeTableId() !== 'tb-current-approvals') return;
    const rows = selectedRows();
    if (!rows.length) return;
    rows.forEach(tr => { setRowBusy(tr, true); updateStatusBadge(tr, (action==='APPROUVER'?'Validation':'Rejet')+' en cours…', 'warning'); });

    const ops = rows.map(tr => doAct(rowData(tr).step_id, action));
    const results = await Promise.allSettled(ops);
    let ok = 0, ko = 0;
    results.forEach((r, i) => {
      const tr = rows[i];
      if (r.status === 'fulfilled'){
        ok++;
        updateStatusBadge(tr, action==='APPROUVER' ? 'Approuvé' : 'Rejeté', action==='APPROUVER' ? 'success' : 'light');
      } else {
        ko++;
        updateStatusBadge(tr, 'Erreur', 'light');
        showToast(r.reason?.message || 'Erreur sur une ligne', 'error');
      }
      setRowBusy(tr, false);
    });
    if (ok) showToast(`${ok} ligne(s) traitée(s) avec succès`, 'success');
    if (ko) showToast(`${ko} échec(s) lors du traitement`, 'error');
    setBulkButtonsState();
  }

  // ========= Délégation =========
  let toDelegate = [];
  function openDelegate(stepIds){
    toDelegate = stepIds || [];
    document.getElementById('delegate-to').value = '';
    new bootstrap.Modal(document.getElementById('dlgDelegate')).show();
  }
  document.getElementById('btn-delegate-selected')?.addEventListener('click', () => {
    if (activeTableId() !== 'tb-current-approvals') return;
    const ids = selectedRows().map(tr => rowData(tr).step_id);
    if (!ids.length) {
      showToast('Sélectionnez au moins une ligne', 'warning');
      return;
    }
    openDelegate(ids);
  });
  document.getElementById('btn-do-delegate')?.addEventListener('click', async () => {
    const code = (document.getElementById('delegate-to').value || '').trim();
    if (!code) {
      showToast('Choisissez un acteur destinataire', 'warning');
      return;
    }
    try {
      const ops = toDelegate.map(id => doAct(id, 'DELEGUER', { delegate_to: code }));
      const res = await Promise.allSettled(ops);
      const ok = res.filter(r => r.status==='fulfilled').length;
      const ko = res.length - ok;
      if (ok) showToast(`Délégation envoyée : ${ok} succès${ko ? `, ${ko} échec(s)` : ''}`, ok === res.length ? 'success' : 'warning');
      if (ko) showToast(`${ko} délégation(s) ont échoué`, 'error');
      bootstrap.Modal.getInstance(document.getElementById('dlgDelegate')).hide();
    } catch(err){
      showToast(err.message || 'Erreur lors de la délégation', 'error');
    }
  });
})();
</script>

@endSection