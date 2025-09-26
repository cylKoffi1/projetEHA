@extends('layouts.app')

@section('content')

{{-- ========= Message de succès (sécurisé) ========= --}}
@if (session('success'))
<script>
  // S'exécute même si jQuery n'est pas encore dispo : on attend le DOM, puis jQuery/Bootstrap.
  (function() {
    var successMsg = @json(session('success'));
    function showAlertWhenReady(){
      if (window.jQuery && typeof $('#alertModal').modal === 'function') {
        $('#alertMessage').text(successMsg);
        $('#alertModal').modal('show');
      } else {
        setTimeout(showAlertWhenReady, 50);
      }
    }
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', showAlertWhenReady);
    } else {
      showAlertWhenReady();
    }
  })();
</script>
@endif

<style>
  /* ---------- TABLE ---------- */
  .tableClass {
    width: 100%;
    border-collapse: separate; /* laissez DataTables gérer les largeurs */
  }
  /* Empêche le débordement visuel quand la colonne est trop étroite */
  .tableClass th, .tableClass td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 110px;      /* largeur mini par colonne feuille */
  }
  /* 1ère colonne (libellé) un peu plus large et stable */
  .tableClass tbody td:first-child,
  .tableClass thead tr:nth-child(1) th:first-child,
  .tableClass thead tr:nth-child(2) th:first-child {
    min-width: 260px;
    max-width: 340px;
  }
/* Alignement à droite pour toutes les colonnes numériques (toutes sauf la 1ère) */
.tableClass th:not(:first-child),
.tableClass td:not(:first-child) {
  text-align: right;
  /* chiffres à chasse fixe = colonnes qui “tombent” bien */
  font-variant-numeric: tabular-nums;
}

/* Optionnel : le tiret de cellule vide reste discret et aligné à droite */
.tableClass td:not(:first-child):empty::after { content: '-'; opacity: .6; }

  /* Empêche la souris de tenter de trier la 1ère rangée (groupes) */
  .tableClass thead tr.group-row th {
    pointer-events: none;
  }

  tbody tr td a { text-decoration: none; }

  /* Ligne National épinglée (peut dépendre de vos wrappers et de DataTables) */
  .national-row {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #e8f4ff !important;
    font-weight: 600;
  }
  .national-row td, .national-row th {
    background: #e8f4ff !important;
    border-bottom: 2px solid #bcdfff;
  }

  /* Lignes de ratio par acteur */
  .ratio-actor-row td { background: #f9fbff; }
  .ratio-actor-label { font-weight: 600; color: #244; }

  /* ----------- FILTRES (look) ----------- */
  .filters-card { border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 1.5rem; overflow: hidden; }
  .filters-body { padding: 1.25rem; background-color: #fff; }
  .filter-section { margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid #eaeaea; }
  .filter-title { font-weight: 600; margin-bottom: 0.75rem; color: #495057; display: flex; align-items: center; }
  .filter-title i { margin-right: 0.5rem; font-size: 1.1rem; }
  .filter-options { display: flex; flex-wrap: wrap; gap: 0.75rem; }
  .filter-check { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 0.5rem 0.75rem; transition: all 0.2s ease; }
  .filter-check:hover { background-color: #e9ecef; border-color: #adb5bd; }
  .filter-check input[type="checkbox"], .filter-check input[type="radio"] { margin-right: 0.4rem; }
  .filter-check label { margin-bottom: 0; cursor: pointer; }
  .btn-reset { margin-top: 1rem; border-radius: 6px; }

  @media (max-width: 768px) {
    .filter-options { flex-direction: column; gap: 0.5rem; }
    .filter-check { width: 100%; }
  }

  /* Nettoyage sélecteurs : cibler précisément */
  #filtersCollapse { background: #f0f6ff; border-radius: 8px; }
  #filtersToggle { background: linear-gradient(135deg, #007bff, #0056b3) !important; color: #fff !important; border-radius: 0; }
  #filtersToggle h6, #filtersToggle i { color: #fff !important; }
  #filtersToggle:hover { background: linear-gradient(135deg, #0056b3, #004494) !important; }

  /* Chevron animé : basé sur l’état .collapsed que Bootstrap applique au bouton */
  #filtersToggle .chevron-toggle { transition: transform .2s ease; }
  #filtersToggle.collapsed .chevron-toggle { transform: rotate(180deg); }
</style>

<section id="multiple-column-form">
  <div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-12">
          {{-- Remplacement du <li> isolé par un conteneur simple --}}
          <div class="breadcrumb-item" style="text-align:right;padding:5px;">
            <span id="date-now" style="color:#34495E;"></span>
          </div>
        </div>
      </div>
      <div class="row align-items-center">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i> Tableau de bord</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="">Financier</a></li>
            </ol>
          </nav>
        </div>
      </div>
    </div>
  </div>

  <div class="row match-height">
    <div class="col-12">

      {{-- ======== CARD DE FILTRES ======== --}}
      @php
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
        $rolesAvailable = !empty($roles) ? $roles : array_keys($roleLabels);
      @endphp

      <div class="card shadow-sm border-0 mb-4">
        <button
          id="filtersToggle"
          type="button"
          class="card-header bg-white border-0 w-100 d-flex align-items-center justify-content-between collapsed"
          data-bs-toggle="collapse"
          data-bs-target="#filtersCollapse"
          aria-expanded="false"
          aria-controls="filtersCollapse"
          style="cursor:pointer"
        >
          <h6 class="mb-0 d-flex align-items-center fw-semibold text-secondary">
            <i class="bi bi-funnel me-2 text-muted"></i> Filtres
          </h6>
          <i class="bi bi-chevron-up chevron-toggle text-muted"></i>
        </button>

        <div class="collapse" id="filtersCollapse">
          <div class="card-body filters-body">
            <div class="row g-4">
              {{-- STATUTS --}}
              <div class="col-md-6 col-lg-4">
                <h6 class="small fw-bold text-muted mb-3">
                  <i class="bi bi-circle-fill text-primary me-1"></i> Statuts
                </h6>
                <div class="row row-cols-3 g-2">
                  @foreach($statusOrder as $k)
                    <div class="col">
                      <div class="form-check">
                        <input class="form-check-input status-filter" type="checkbox" id="st-{{ $k }}" value="{{ $k }}" checked>
                        <label class="form-check-label small" for="st-{{ $k }}">{{ $statusTitles[$k] }}</label>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>

              {{-- TYPE PROJET --}}
              <div class="col-md-6 col-lg-4">
                <h6 class="small fw-bold text-muted mb-3 text-center">
                  <i class="bi bi-grid-1x2 text-success me-1"></i> Type de projet
                </h6>
                <div class="row row-cols-3 g-2">
                  <div class="col">
                    <div class="form-check">
                      <input class="form-check-input type-filter" type="radio" name="typeProjet" id="tous" value="tous" checked>
                      <label class="form-check-label small" for="tous">Tous</label>
                    </div>
                  </div>
                  <div class="col">
                    <div class="form-check">
                      <input class="form-check-input type-filter" type="radio" name="typeProjet" id="public" value="public">
                      <label class="form-check-label small" for="public">Public</label>
                    </div>
                  </div>
                  <div class="col">
                    <div class="form-check">
                      <input class="form-check-input type-filter" type="radio" name="typeProjet" id="prive" value="prive">
                      <label class="form-check-label small" for="prive">Privé</label>
                    </div>
                  </div>
                </div>
              </div>

              {{-- ACTEURS (sans “National”) --}}
              <div class="col-md-12 col-lg-4">
                <h6 class="small fw-bold text-muted mb-2">
                  <i class="bi bi-people text-warning me-1"></i> Acteurs
                </h6>
                <div class="row row-cols-3 g-2">
                  @foreach($rolesAvailable as $code)
                    <div class="col">
                      <div class="form-check">
                        <input class="form-check-input actor-filter" type="checkbox" id="role-{{ $code }}" value="{{ $code }}" checked>
                        <label class="form-check-label small" for="role-{{ $code }}">{{ $roleLabels[$code] ?? $code }}</label>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>

            </div>

            <div class="text-center mt-4">
              <button id="btn-reset-filters" type="button" class="btn btn-light border rounded-pill px-4 btn-reset">
                <i class="bi bi-arrow-clockwise"></i> Réinitialiser les filtres
              </button>
            </div>
          </div>
        </div>
      </div>
      {{-- ================= FIN CARD DE FILTRES ================= --}}

      <div class="card">
        <div class="card-header text-center">
          <h5 class="card-title">Tableau de bord financier (montants)</h5>
        </div>

        <div class="card-content">
          <div class="card-body">

            @php
              $money = function($v){ return number_format((float)($v ?? 0), 0, ',', ' '); };
              $cellLink = function($val, $params = []) use ($ecran, $money) {
                  $v = (float)($val ?? 0);
                  $url = route('finance.data', array_merge(['ecran_id'=>$ecran->id], $params));
                  return $v > 0 ? '<a href="'.$url.'">'.$money($v).'</a>' : '-';
              };
            @endphp

            {{-- Wrapper responsive pour scroll horizontal propre --}}
            <div class="table-responsive">
              <table class="table table-striped table-bordered tableClass" id="table1">
                <thead>
                  <!-- Rangée 1: groupes -->
                  <tr class="group-row">
                    <th scope="col"></th>
                    @foreach($statusOrder as $k)
                      <th scope="colgroup" colspan="3" class="text-center">{{ $statusTitles[$k] }}</th>
                    @endforeach
                  </tr>
                  <!-- Rangée 2: feuilles -->
                  <tr class="leaf-row">
                    <th scope="col"></th>
                    @foreach($statusOrder as $k)
                      <th scope="col">Total</th><th scope="col">Public</th><th scope="col">Privé</th>
                    @endforeach
                  </tr>
                </thead>

                <tbody>
                  {{-- NATIONAL --}}
                  <tr class="national-row" data-role="national">
                    <td>
                      <a href="{{ route('finance.data', ['ecran_id' => $ecran->id, 'type' => 'national']) }}">National</a>
                    </td>
                    @foreach($statusOrder as $k)
                      {!! '<td>'.$cellLink($stats['National']["total_$k"]  ?? 0, ['type'=>'national','statut'=>$k,'segment'=>'total']).'</td>' !!}
                      {!! '<td>'.$cellLink($stats['National']["public_$k"] ?? 0, ['type'=>'national','statut'=>$k,'segment'=>'public']).'</td>' !!}
                      {!! '<td>'.$cellLink($stats['National']["prive_$k"]  ?? 0, ['type'=>'national','statut'=>$k,'segment'=>'prive']).'</td>' !!}
                    @endforeach
                  </tr>

                  {{-- MES RÔLES --}}
                  @if(!empty($roles))
                    @foreach($roles as $code)
                      <tr data-role="{{ $code }}">
                        <td>
                          <a href="{{ route('finance.data', ['ecran_id'=>$ecran->id,'type'=>'personnel','role'=>$code]) }}">
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

                  {{-- Gabarit invisible (repère de colonnes) --}}
                  <tr data-role="ratio-template" style="display:none;">
                    <td><strong>Ratio (%)</strong></td>
                    @foreach($statusOrder as $k)
                      <td>0%</td><td>0%</td><td>0%</td>
                    @endforeach
                  </tr>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<script>
  // Horloge
  setInterval(() => {
    const el = document.getElementById('date-now');
    if (el) el.textContent = new Date().toLocaleString();
  }, 1000);

  $(document).ready(async function () {
    const $table = $('.tableClass').first();
    if ($table.length === 0) return;

    // Donner un ID si absent
    let tableId = $table.attr('id');
    if (!tableId) {
      tableId = 'dt-' + Math.random().toString(36).slice(2, 8);
      $table.attr('id', tableId);
    }

    // Init DataTables (votre fonction perso)
    await initDataTable(
      '{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}',
      tableId,
      'Tableau de bord financier (montants)'
    );
    const dt = $table.DataTable();

    /* ------------------ UTILS ------------------ */
    const statusOrder = @json($statusOrder);
    const colMap = {};
    let start = 1; // col 0 = libellé
    statusOrder.forEach(k => { colMap[k] = { total:start, public:start+1, prive:start+2 }; start += 3; });

    function typeSelection(){ return $('input.type-filter:checked').val(); }
    const showPublicCol = () => (typeSelection()==='tous' || typeSelection()==='public');
    const showPriveCol  = () => (typeSelection()==='tous' || typeSelection()==='prive');

    // Parse robuste
    function parseNumberFromText(text) {
      if (!text) return 0;
      let s = String(text)
        .replace(/[\u00A0\u202F]/g, '')
        .replace(/['\u2018\u2019]/g, '')
        .replace(/[^\d,.\-]/g, '');
      if (s.includes(',') && !s.includes('.')) s = s.replace(/,/g, '.');
      if ((s.match(/[.,]/g) || []).length > 1) s = s.replace(/[^0-9\-]/g, '');
      const n = parseFloat(s);
      return isNaN(n) ? 0 : n;
    }
    function formatPercent(value, actorVal, natVal) {
      if (natVal > 0 && actorVal === 0) return '0%';
      if (actorVal === 0 && natVal === 0) return '-';
      if (!isFinite(value)) return '-';
      if (value === 0) return '0%';
      if (value > 0 && value < 1) return (Math.round(value * 10) / 10).toString().replace('.', ',') + '%';
      return Math.round(value) + '%';
    }
    function getCellNumberByDom(rowEl, colIdx){
      const td = rowEl.querySelector(`td:nth-child(${colIdx+1})`);
      if (!td) return 0;
      return parseNumberFromText(td.textContent || '');
    }
    function setCellTextByDom(rowEl, colIdx, text){
      const td = rowEl.querySelector(`td:nth-child(${colIdx+1})`);
      if (td) td.textContent = text;
    }

    /* === NOUVEAU : synchronise les cellules des lignes ratio avec la visibilité des colonnes DT === */
    function syncRatioColsToDtVisibility(){
      const colCount = dt.columns().count();
      for (let i = 0; i < colCount; i++){
        const vis = dt.column(i).visible();
        const nth = i + 1;
        // Ne touche qu’aux lignes de ratio (celles que DataTables ne gère pas)
        const $cells = $table.find(`tbody tr.ratio-actor-row td:nth-child(${nth})`);
        if (vis) $cells.show(); else $cells.hide();
      }
    }

    /* ------------------ FILTRAGE ACTEURS ------------------ */
    let enabledActors = new Set($('.actor-filter:checked').map((_,el)=>el.value.toLowerCase()).get());
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex){
      if (settings.nTable !== $table[0]) return true;
      const node = dt.row(dataIndex).node();
      const role = (node?.dataset?.role || '').toLowerCase();
      if (role === 'national') return true;
      if (role.startsWith('ratio-')) return enabledActors.has(role.slice(6));
      return enabledActors.has(role);
    });
    function applyActorFilter(){
      enabledActors = new Set($('.actor-filter:checked').map((_,el)=>el.value.toLowerCase()).get());
      dt.draw(false);
      hideEmptyActorRows(); 
    }

    /* ------------------ LIGNES RATIO ------------------ */
    function ensureActorRatioRows(){
      const $tbody = $table.find('tbody');
      const colCount = dt.columns().count();
      $tbody.find('tr').each(function(){
        const role = (this.dataset.role || '').toLowerCase();
        if (!role || role === 'national' || role.startsWith('ratio-') || role === 'ratio-template') return;
        const $actorRow = $(this);
        const $next = $actorRow.next('tr');
        const expectedRoleAttr = 'ratio-' + role;
        if (!$next.length || ($next.get(0).dataset.role || '').toLowerCase() !== expectedRoleAttr) {
          const actorLabel = ($actorRow.find('td:first a, td:first').first().text() || role).trim();
          const $ratioRow = $('<tr class="ratio-actor-row"></tr>').attr('data-role', expectedRoleAttr);
          $ratioRow.append($('<td class="ratio-actor-label"></td>').text('Ratio ' + actorLabel + ' / National'));
          for (let i=1; i<colCount; i++) $ratioRow.append('<td>-</td>');
          $actorRow.after($ratioRow);
        }
      });
    }

    /* ------------------ ENTÊTES GROUPÉS (met à jour tous les thead, y compris clones) ------------------ */
    function refreshGroupedHeader() {
      const $allTheads = $table.closest('.dataTables_wrapper').find('thead'); // couvre header original + clones
      $allTheads.each(function(){
        const $thead = $(this);
        const groupHeaderThs = $thead.find('tr.group-row th').toArray().slice(1); // saute la 1ère (libellé)
        statusOrder.forEach((k, idx) => {
          const cols = colMap[k];
          const visibleCount = [
            dt.column(cols.total).visible(),
            dt.column(cols.public).visible(),
            dt.column(cols.prive ).visible()
          ].filter(Boolean).length;
          const th = groupHeaderThs[idx];
          if (!th) return;
          if (visibleCount === 0) {
            th.style.display = 'none';
            th.setAttribute('colspan', '0');
          } else {
            th.style.display = '';
            th.setAttribute('colspan', String(visibleCount));
          }
        });
      });
    }

    /* ------------------ RATIOS ------------------ */
    function recomputeActorRatios() {
      const $tbody   = $table.find('tbody');
      const natRowEl = $tbody.find('tr.national-row')[0];
      if (!natRowEl) return;
      const segments = ['total','public','prive'];
      $tbody.find('tr.ratio-actor-row').each(function(){
        const ratioRow  = this;
        const actorRow  = $(ratioRow).prev('tr').get(0);
        if (!actorRow) return;
        Object.entries(colMap).forEach(([k, cols]) => {
          segments.forEach(seg => {
            const colIdx = cols[seg];
            if (!dt.column(colIdx).visible()) return;
            const natVal   = getCellNumberByDom(natRowEl, colIdx);
            const actorVal = getCellNumberByDom(actorRow, colIdx);
            const pctVal = (natVal > 0) ? (actorVal / natVal * 100) : NaN;
            setCellTextByDom(ratioRow, colIdx, formatPercent(pctVal, actorVal, natVal));
          });
        });
      });
    }
    // Renvoie true si une ligne (acteur) possède au moins une valeur > 0
    function actorRowHasAnyData(rowEl) {
      const colCount = dt.columns().count();
      // on ignore la 1ère colonne (libellé)
      for (let i = 1; i < colCount; i++) {
        if (!dt.column(i).visible()) continue; // on se cale sur les colonnes visibles
        const v = getCellNumberByDom(rowEl, i);
        if (v > 0) return true;
      }
      return false;
    }

    // Cache/affiche les lignes acteurs & leurs ratios si aucune donnée
    function hideEmptyActorRows() {
      const $tbody = $table.find('tbody');
      $tbody.find('tr').each(function(){
        const role = (this.dataset.role || '').toLowerCase();
        if (!role || role === 'national' || role.startsWith('ratio-') || role === 'ratio-template') return;

        const actorRow = this;
        const hasData  = actorRowHasAnyData(actorRow);

        // Ligne ratio correspondante (si elle existe)
        const $next = $(actorRow).next('tr');
        const isRatioNext = $next.length && (($next.get(0).dataset.role || '').toLowerCase() === 'ratio-' + role);

        if (hasData) {
          actorRow.style.display = '';
          if (isRatioNext) $next.get(0).style.display = '';  // on laisse la ligne ratio visible (calculée ailleurs)
        } else {
          actorRow.style.display = 'none';
          if (isRatioNext) $next.get(0).style.display = 'none';
        }
      });

      // Bonus : si une ligne ratio ne contient que des '-' (après calcul), on la cache
      $tbody.find('tr.ratio-actor-row').each(function(){
        if (this.style.display === 'none') return; // déjà cachée avec l'acteur
        const tds = Array.from(this.querySelectorAll('td')).slice(1); // sans libellé
        const onlyDashesOrEmpty = tds.every(td => (td.textContent || '').trim() === '-' || (td.textContent || '').trim() === '');
        if (onlyDashesOrEmpty) this.style.display = 'none';
      });
    }

    /* ------------------ FILTRES COLONNES ------------------ */
    function applyStatusFilter(){
      const checked = $('.status-filter:checked').map((_,el)=>el.value).get();
      statusOrder.forEach(k => {
        const cols = colMap[k];
        const visible = checked.includes(k);
        dt.column(cols.total).visible(visible, false);
        dt.column(cols.public).visible(visible && showPublicCol(), false);
        dt.column(cols.prive ).visible(visible && showPriveCol(),  false);
      });
      dt.columns.adjust();
      refreshGroupedHeader();
      syncRatioColsToDtVisibility();   // <<— clé : masque/affiche aussi les cellules des lignes ratio
      dt.draw(false);
      hideEmptyActorRows();
    }
    function applyTypeProjetFilter(){
      const checked = $('.status-filter:checked').map((_,el)=>el.value).get();
      statusOrder.forEach(k => {
        const cols = colMap[k];
        dt.column(cols.public).visible(checked.includes(k) && showPublicCol(), false);
        dt.column(cols.prive ).visible(checked.includes(k) && showPriveCol(),  false);
      });
      dt.columns.adjust();
      refreshGroupedHeader();
      syncRatioColsToDtVisibility();   // <<— idem
      dt.draw(false);
      hideEmptyActorRows(); 
    }

    /* ------------------ HOOKS ------------------ */
    // Quand DataTables change la visibilité (au cas où c'est déclenché ailleurs)
    $table.on('column-visibility.dt', function () {
      refreshGroupedHeader();
      syncRatioColsToDtVisibility();
    });

    dt.on('draw.dt', function(){
      const $body = $table.find('tbody');
      const $nat  = $body.find('tr.national-row');
      if ($nat.length) $nat.prependTo($body); // National toujours en haut
      ensureActorRatioRows();
      recomputeActorRatios();
      refreshGroupedHeader();
      syncRatioColsToDtVisibility();  
      hideEmptyActorRows();
    });

    /* ------------------ LISTENERS UI ------------------ */
    $(document).on('change', '.status-filter', applyStatusFilter);
    $(document).on('change', '.type-filter',   applyTypeProjetFilter);
    $(document).on('change', '.actor-filter',  applyActorFilter);

    $('#btn-reset-filters').on('click', function(){
      $('.status-filter').prop('checked', true);
      $('.type-filter[value="tous"]').prop('checked', true);
      $('.actor-filter').prop('checked', true);
      applyStatusFilter(); applyTypeProjetFilter(); applyActorFilter();
    });

    /* ------------------ INIT ------------------ */
    applyStatusFilter();
    applyTypeProjetFilter();
    applyActorFilter();
    hideEmptyActorRows();
    
    // Ajuste au resize
    $(window).on('resize', () => { dt.columns.adjust(); refreshGroupedHeader(); syncRatioColsToDtVisibility(); });
  });
</script>

@endsection
