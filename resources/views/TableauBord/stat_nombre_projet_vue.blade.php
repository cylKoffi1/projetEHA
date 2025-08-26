@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
  $('#alertMessage').text("{{ session('success') }}");
  $('#alertModal').modal('show');
</script>
@endif

<style>
  .invalid-feedback{display:block;margin-top:6px;font-size:80%;color:#dc3545}
  th, td { white-space: nowrap; }
  tbody tr td a { text-decoration: none; }
  
  /* Styles améliorés pour les filtres */
  .filters-card {
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
    overflow: hidden;
  }
  
  .filters-header {
    background-color: #f8f9fa;
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
  }
  .chevron-toggle { transition: transform .2s ease; }
  .filters-header.collapsed .chevron-toggle { transform: rotate(180deg); }
  
  .filters-body { padding: 1.25rem; background-color: #fff; }
  .filter-section { margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #eaeaea; }
  .filter-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
  .filter-title { font-weight: 600; margin-bottom: 0.75rem; color: #495057; display: flex; align-items: center; }
  .filter-title i { margin-right: 0.5rem; font-size: 1.1rem; }
  .filter-options { display: flex; flex-wrap: wrap; gap: 0.75rem; }
  .filter-check { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 0.5rem 0.75rem; transition: all 0.2s ease; }
  .filter-check:hover { background-color: #e9ecef; border-color: #adb5bd; }
  .filter-check input[type="checkbox"],
  .filter-check input[type="radio"] { margin-right: 0.4rem; }
  .filter-check label { margin-bottom: 0; cursor: pointer; }
  .btn-reset { margin-top: 1rem; border-radius: 6px; }
  
  /* Responsive */
  @media (max-width: 768px) {
    .filter-options { flex-direction: column; gap: 0.5rem; }
    .filter-check { width: 100%; }
  }

  /* === Zone de filtres === */
.card#filtersCollapse,
.card.shadow-sm.border-0.mb-4 {
  background: #f0f6ff; /* bleu très léger pour la zone */
  border-radius: 8px;
}

/* === En-tête bouton toggle === */
#filtersToggle {
  background: linear-gradient(135deg, #007bff, #0056b3) !important;
  color: #fff !important;
  border-radius: 0;
}

#filtersToggle h6,
#filtersToggle i {
  color: #fff !important;
}

/* Hover sur le bouton */
#filtersToggle:hover {
  background: linear-gradient(135deg, #0056b3, #004494) !important;
}

</style>

<section id="multiple-column-form">
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-12">
          <li class="breadcrumb-item" style="list-style:none;text-align:right;padding:5px;">
            <span id="date-now" style="color:#34495E;"></span>
          </li>
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i> Tableau de bord</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="">Nombre de projet</a></li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div class="row match-height">
    <div class="col-12">
      <div class="card">
        <div class="card-header text-center">
          <h5 class="card-title">Tableau de bord en nombre de projets</h5>
        </div>

        <div class="card-content">
          <div class="card-body">

            @php
              // fournis par le contrôleur
              $roles        = $roles ?? [];
              $statusOrder  = $statusOrder ?? ['prevu','en_cours','cloture','termine','redemarre','suspendu','annule'];
              $statusTitles = $statusTitles ?? [
                'prevu'=>'Prévu','en_cours'=>'En cours','cloture'=>'Clôturé',
                'termine'=>'Terminé','redemarre'=>'Redémarré','suspendu'=>'Suspendu','annule'=>'Annulé'
              ];
              $roleLabels = [
                'chef_projet' => "Chef de projet",
                'moe'         => "Maître d'œuvre",
                'mo'          => "Maître d'ouvrage",
                'bailleur'    => "Bailleur",
              ];
              // acteurs visibles dans les filtres : ceux de l'utilisateur, sinon tous
              $rolesAvailable = !empty($roles) ? $roles : array_keys($roleLabels);

              // helper: fait les liens cliquables sur les nombres
              $cellLink = function($val, $params = []) use ($ecran) {
                  $v = (int)($val ?? 0);
                  $url = route('nombre.data', array_merge(['ecran_id'=>$ecran->id], $params));
                  return $v > 0 ? '<a href="'.$url.'">'.$v.'</a>' : '0';
              };
            @endphp
            {{-- ======== CARD DE FILTRES SIMPLE & MODERNE ======== --}}
            <div class="card shadow-sm border-0 mb-4">
            <button
                id="filtersToggle"
                type="button"
                class="card-header bg-white border-0 w-100 d-flex align-items-center justify-content-between"
                aria-expanded="true"
                aria-controls="filtersCollapse"
                style="cursor:pointer"
            >
                <h6 class="mb-0 d-flex align-items-center fw-semibold text-secondary">
                <i class="bi bi-funnel me-2 text-muted"></i> Filtres
                </h6>
                <i class="bi bi-chevron-up chevron-toggle text-muted"></i>
            </button>
            
            <div class="collapse show" id="filtersCollapse">
                <div class="card-body" style="background-color: #d9d9d9;">
                <div class="row g-4">
                    {{-- STATUTS --}}
                    <div class="col-md-6 col-lg-4">
                    <h6 class="small fw-bold text-muted mb-3"><i class="bi bi-circle-fill text-primary me-1"></i> Statuts</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($statusOrder as $k)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input status-filter" type="checkbox" id="st-{{ $k }}" value="{{ $k }}" checked>
                            <label class="form-check-label small" for="st-{{ $k }}">{{ $statusTitles[$k] }}</label>
                        </div>
                        @endforeach
                    </div>
                    </div>

                    {{-- TYPE PROJET --}}
                    <div class="col-md-6 col-lg-4">
                    <h6 class="small fw-bold text-muted mb-3"><i class="bi bi-grid-1x2 text-success me-1"></i> Type de projet</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="form-check form-check-inline">
                        <input class="form-check-input type-filter" type="radio" name="typeProjet" id="tous" value="tous" checked>
                        <label class="form-check-label small" for="tous">Tous</label>
                        </div>
                        <div class="form-check form-check-inline">
                        <input class="form-check-input type-filter" type="radio" name="typeProjet" id="public" value="public">
                        <label class="form-check-label small" for="public">Public</label>
                        </div>
                        <div class="form-check form-check-inline">
                        <input class="form-check-input type-filter" type="radio" name="typeProjet" id="prive" value="prive">
                        <label class="form-check-label small" for="prive">Privé</label>
                        </div>
                    </div>
                    </div>

                    {{-- ACTEURS --}}
                    <div class="col-md-12 col-lg-4">
                    <h6 class="small fw-bold text-muted mb-3"><i class="bi bi-people text-warning me-1"></i> Acteurs</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <div class="form-check form-check-inline">
                        <input class="form-check-input actor-filter" type="checkbox" id="role-national" value="national" checked>
                        <label class="form-check-label small" for="role-national">National</label>
                        </div>
                        @foreach($rolesAvailable as $code)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input actor-filter" type="checkbox" id="role-{{ $code }}" value="{{ $code }}" checked>
                            <label class="form-check-label small" for="role-{{ $code }}">{{ $roleLabels[$code] ?? $code }}</label>
                        </div>
                        @endforeach
                    </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button id="btn-reset-filters" type="button" class="btn btn-light border rounded-pill px-4">
                    <i class="bi bi-arrow-clockwise"></i> Réinitialiser
                    </button>
                </div>
                </div>
            </div>
            </div>

            {{-- ================================== --}}

            <table class="table table-striped table-bordered tableClass" id="table1" style="width:100%">
              <thead>
                <tr>
                  <th></th>
                  @foreach($statusOrder as $k)
                    <th colspan="3" class="text-center">{{ $statusTitles[$k] }}</th>
                  @endforeach
                </tr>
                <tr>
                  <th></th>
                  @foreach($statusOrder as $k)
                    <th>Total</th><th>Public</th><th>Privé</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>

                {{-- NATIONAL (toujours en haut) --}}
                <tr class="national-row" data-role="national">
                  <td>
                    <a href="{{ route('nombre.data', ['ecran_id' => $ecran->id, 'type' => 'national']) }}">National</a>
                  </td>
                  @foreach($statusOrder as $k)
                    {!! '<td>'.$cellLink($stats['National']["total_$k"]  ?? 0, ['type'=>'national','statut'=>$k,'segment'=>'total']).'</td>' !!}
                    {!! '<td>'.$cellLink($stats['National']["public_$k"] ?? 0, ['type'=>'national','statut'=>$k,'segment'=>'public']).'</td>' !!}
                    {!! '<td>'.$cellLink($stats['National']["prive_$k"]  ?? 0, ['type'=>'national','statut'=>$k,'segment'=>'prive']).'</td>' !!}
                  @endforeach
                </tr>

                {{-- LIGNES RÔLES --}}
                @if(!empty($roles))
                  @foreach($roles as $code)
                    <tr data-role="{{ $code }}">
                      <td>
                        <a href="{{ route('nombre.data', ['ecran_id'=>$ecran->id,'type'=>'personnel','role'=>$code]) }}">
                          {{ $roleLabels[$code] ?? $code }}
                        </a>
                      </td>
                      @foreach($statusOrder as $k)
                        {!! '<td>'.$cellLink($stats['Moi'][$code]["total_$k"]  ?? 0, ['type'=>'personnel','role'=>$code,'statut'=>$k,'segment'=>'total']).'</td>' !!}
                        {!! '<td>'.$cellLink($stats['Moi'][$code]["public_$k"] ?? 0, ['type'=>'personnel','role'=>$code,'statut'=>$k,'segment'=>'public']).'</td>' !!}
                        {!! '<td>'.$cellLink($stats['Moi'][$code]["prive_$k"]  ?? 0, ['type'=>'personnel','role'=>$code,'statut'=>$k,'segment'=>'prive']).'</td>' !!}
                      @endforeach
                    </tr>
                  @endforeach
                @endif

                {{-- RATIO (aucun lien) --}}
                <tr data-role="ratio">
                  <td><strong>Ratio (%)</strong></td>
                  @foreach($statusOrder as $k)
                    <td>{{ $stats['Ratio']["total_$k"]  ?? 0 }}%</td>
                    <td>{{ $stats['Ratio']["public_$k"] ?? 0 }}%</td>
                    <td>{{ $stats['Ratio']["prive_$k"]  ?? 0 }}%</td>
                  @endforeach
                </tr>

              </tbody>
            </table>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  // horloge
  setInterval(() => {
    const el = document.getElementById('date-now');
    if (el) el.textContent = new Date().toLocaleString();
  }, 1000);

  $(document).ready(async function () {
    // ===== 1) Récupérer la table par CLASSE
    const $table = $('.tableClass').first();
    if ($table.length === 0) return; // sécurité

    // lui donner un ID si absent (initDataTable attend un ID)
    let tableId = $table.attr('id');
    if (!tableId) {
      tableId = 'dt-' + Math.random().toString(36).slice(2, 8);
      $table.attr('id', tableId);
    }

    // ===== 2) Init DataTables via TA fonction (elle attend un ID)
    // si elle est async, on l'attend pour garantir que DT est prêt
    await initDataTable(
      '{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}',
      tableId,
      'Liste des nombres de projets'
    );

    // récupérer l'instance DT à partir de la table par CLASSE
    const dt = $table.DataTable();

    // ===== 3) Collapse robuste (inchangé)
    const collapseEl   = document.getElementById('filtersCollapse');
    const headerButton = document.getElementById('filtersToggle');
    const chevron      = document.querySelector('.chevron-toggle');

    const setExpanded = (expanded) => {
      headerButton?.setAttribute('aria-expanded', expanded ? 'true' : 'false');
      headerButton?.classList.toggle('collapsed', !expanded);
      if (chevron) chevron.style.transform = expanded ? 'rotate(0deg)' : 'rotate(180deg)';
    };
    setExpanded(true);

    let bsInstance = null;
    if (window.bootstrap && bootstrap.Collapse) {
      bsInstance = new bootstrap.Collapse(collapseEl, { toggle: false });
      collapseEl.addEventListener('shown.bs.collapse', () => setExpanded(true));
      collapseEl.addEventListener('hidden.bs.collapse', () => setExpanded(false));
    }
    headerButton?.addEventListener('click', (e) => {
      e.preventDefault();
      if (bsInstance) { bsInstance.toggle(); return; }
      const willShow = !collapseEl.classList.contains('show');
      collapseEl.classList.toggle('show', willShow);
      setExpanded(willShow);
    });

    // ===== 4) Filtres côté front (en utilisant LA CLASSE)
    (function(){
      const statusOrder = @json($statusOrder); // ["prevu","en_cours",...]
      // 1ère colonne = libellé ; ensuite groupes de 3 colonnes par statut
      const colMap = {};
      let start = 1;
      statusOrder.forEach(k => {
        colMap[k] = { total: start, public: start+1, prive: start+2 };
        start += 3;
      });

      function typeSelection(){ return $('input.type-filter:checked').val(); }
      const showPublicCol = () => (typeSelection()==='tous' || typeSelection()==='public');
      const showPriveCol  = () => (typeSelection()==='tous' || typeSelection()==='prive');

      function applyStatusFilter(){
        const checked = $('.status-filter:checked').map((_,el)=>el.value).get();
        statusOrder.forEach(k => {
          const visible = checked.includes(k);
          const cols = colMap[k];
          dt.column(cols.total).visible(visible, false);
          dt.column(cols.public).visible(visible && showPublicCol(), false);
          dt.column(cols.prive ).visible(visible && showPriveCol(),  false);
        });
        dt.columns.adjust().draw(false);
      }

      function applyTypeProjetFilter(){
        const checked = $('.status-filter:checked').map((_,el)=>el.value).get();
        statusOrder.forEach(k => {
          const cols = colMap[k];
          dt.column(cols.public).visible(checked.includes(k) && showPublicCol(), false);
          dt.column(cols.prive ).visible(checked.includes(k) && showPriveCol(),  false);
        });
        dt.columns.adjust().draw(false);
      }

      function applyActorFilter(){
        const enabled = $('.actor-filter:checked').map((_,el)=>el.value.toLowerCase()).get(); // ["national","mo",...]
        // important : cibler les lignes via LA CLASSE
        $table.find('tbody tr').each(function(){
          const role = (this.dataset.role || '').toLowerCase();
          if (role === 'ratio') { $(this).show(); return; }
          $(this).toggle(enabled.includes(role));
        });
        dt.draw(false);
      }

      // Listeners
      $(document).on('change', '.status-filter', applyStatusFilter);
      $(document).on('change', '.type-filter',   applyTypeProjetFilter);
      $(document).on('change', '.actor-filter',  applyActorFilter);

      $('#btn-reset-filters').on('click', function(){
        $('.status-filter').prop('checked', true);
        $('.type-filter[value="tous"]').prop('checked', true);
        $('.actor-filter').prop('checked', true);
        applyStatusFilter(); applyTypeProjetFilter(); applyActorFilter();
      });

      // Init
      applyStatusFilter();
      applyTypeProjetFilter();
      applyActorFilter();
    })();
  });
</script>

@endsection
