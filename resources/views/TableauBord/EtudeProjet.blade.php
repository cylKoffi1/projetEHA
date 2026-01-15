@extends('layouts.app')

@section('content')

@php
    use Illuminate\Support\Str;

    $roleLabels = [
        'chef_projet' => "Chef de projet",
        'moe'         => "Maître d'œuvre",
        'mo'          => "Maître d'ouvrage",
        'bailleur'    => "Bailleur",
    ];

    // Helper pour générer les liens sur TOUTES les cellules (tableau)
    $routeBase = ($typeVue ?? 'nombre') === 'finance' ? 'finance.data' : 'nombre.data';

    $cellLink = function ($val, array $params = []) use ($ecran, $routeBase, $typeVue) {
        $v = (float)($val ?? 0);
        $label = number_format($v, 0, ',', ' ');
        $url = route($routeBase, array_merge(['ecran_id' => $ecran->id], $params));
        // Toujours un lien, même si 0
        return '<a href="'.$url.'">'.$label.'</a>';
    };
@endphp

<style>
    /* ======================== TABLE (mode "table") =========================== */
    .tableClass {
        width: 100%;
        border-collapse: separate;
    }

    .tableClass th, .tableClass td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 110px;
    }

    /* 1ère colonne */
    .tableClass tbody td:first-child,
    .tableClass thead tr:nth-child(1) th:first-child,
    .tableClass thead tr:nth-child(2) th:first-child {
        min-width: 260px;
        max-width: 340px;
    }

    .tableClass th:not(:first-child),
    .tableClass td:not(:first-child) {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .tableClass td:not(:first-child):empty::after {
        content: '-';
        opacity: .6;
    }

    /* ENTETES */
    .tableClass thead tr.group-row th { pointer-events: none; }

    /* Ligne National */
    .national-row {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #e8f4ff !important;
        font-weight: 600;
    }
    .national-row td, .national-row th {
        background: #e8f4ff !important;
    }

    /* Ratio */
    .ratio-actor-row td { background: #f9fbff; }
    .ratio-actor-label { font-weight: 600; color: #244; }

    /* Filtres */
    #filtersCollapse { background:#f0f6ff; border-radius: 8px; }
    #filtersToggle { background: linear-gradient(135deg, #007bff, #0056b3) !important; }
    #filtersToggle h6, #filtersToggle i { color:#fff !important; }
    #filtersToggle:hover { background: linear-gradient(135deg, #0056b3, #004494) !important; }
    #filtersToggle .chevron-toggle { transition: transform .2s ease; }
    #filtersToggle.collapsed .chevron-toggle { transform: rotate(180deg); }

    /* SWITCH TYPE (Nombre / Finance / Graphique) */
    .switch-container {
        display: flex;
        justify-content: center;
        margin-bottom: 15px;
    }

    .switch-type {
        display: inline-flex;
        background: #f1f1f1;
        border-radius: 50px;
        padding: 6px;
        gap: 10px;
    }

    .switch-type a {
        padding: 6px 18px;
        border-radius: 30px;
        font-weight: 600;
        text-decoration: none;
        color: #4b4b4b;
    }

    .switch-type a.active {
        background: #0d6efd;
        color: white;
    }

    /* ======================== PREVISUALISATION GRAPHIQUE =========================== */

    /* Fix DataTables widths pour les tables dans les onglets */
    .dataTables_wrapper .dataTables_scrollHeadInner,
    .dataTables_wrapper .dataTables_scrollHeadInner table,
    .dataTables_wrapper .dataTables_scrollBody table {
    width: 100% !important;
    }

    table.dataTable {
    width: 100% !important;
    table-layout: auto;
    }

    /* Donne une couleur correcte aux boutons de pagination, etc. */
    .dt-button, .dataTables_info, .paginate_button, #table-projet_next, #table-projet_previous
    #table-projet_paginate span a  {
        color: black !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        color: black !important;
    }
</style>

<section id="multiple-column-form">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <div class="breadcrumb-item" style="text-align:right;padding:5px;">
                        <span id="date-now" style="color:#34495E;"></span>
                    </div>
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>
                        <i class="bi bi-arrow-return-left return" onclick="history.back()" style="cursor:pointer;"></i>
                        Tableau de bord
                    </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">Étude de projet</a></li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ ($mode ?? 'table') === 'graph' ? 'Représentation graphique' : 'Tableau de synthèse' }}
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        {{-- ===================== SWITCH TYPE (3 boutons) ======================== --}}
        <div class="switch-container">
            <div class="switch-type">

                {{-- Nombre de projets (mode tableau) --}}
                <a href="{{ route('stat.dashboard', [
                        'ecran_id' => $ecran->id,
                        'vue'      => 'nombre',
                        'mode'     => 'table',
                    ]) }}"
                class="{{ ($mode ?? 'table') === 'table' && ($typeVue ?? 'nombre') === 'nombre' ? 'active' : '' }}">
                    Nombre de projets
                </a>

                {{-- Finances (mode tableau) --}}
                <a href="{{ route('stat.dashboard', [
                        'ecran_id' => $ecran->id,
                        'vue'      => 'finance',
                        'mode'     => 'table',
                    ]) }}"
                class="{{ ($mode ?? 'table') === 'table' && ($typeVue ?? 'nombre') === 'finance' ? 'active' : '' }}">
                    Finances (montants)
                </a>

                {{-- Représentation graphique --}}
                <a href="{{ route('stat.dashboard', [
                        'ecran_id' => $ecran->id,
                        'vue'      => ($typeVue ?? 'nombre'),
                        'mode'     => 'graph',
                    ]) }}"
                class="{{ ($mode ?? 'table') === 'graph' ? 'active' : '' }}">
                    Représentation graphique
                </a>
            </div>
        </div>


        {{--  =====================  Filtres par dates  =====================  --}}
        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <h6 class="fw-bold mb-3">
                    <i class="bi bi-calendar-date me-1"></i> Filtre par période
                </h6>

                <form method="GET" action="{{ route('stat.dashboard') }}">
                    <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                    <input type="hidden" name="vue" value="{{ $typeVue }}">
                    <input type="hidden" name="mode" value="{{ $mode }}">

                    <div class="row g-3">

                        {{-- Début prévisionnel --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Début prévisionnel</label>
                            <input type="date" name="date_debut_prev"
                                class="form-control"
                                value="{{ request('date_debut_prev') }}">
                        </div>

                        {{-- Fin prévisionnelle --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Fin prévisionnelle</label>
                            <input type="date" name="date_fin_prev"
                                class="form-control"
                                value="{{ request('date_fin_prev') }}">
                        </div>

                        {{-- Début effectif --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Début effectif</label>
                            <input type="date" name="date_debut_effectif"
                                class="form-control"
                                value="{{ request('date_debut_effectif') }}">
                        </div>

                        {{-- Fin effective --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold small">Fin effective</label>
                            <input type="date" name="date_fin_effectif"
                                class="form-control"
                                value="{{ request('date_fin_effectif') }}">
                        </div>

                    </div>

                    <div class="mt-3 text-end">
                        <button class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-search"></i> Appliquer
                        </button>
                    </div>

                </form>

            </div>
        </div>


        {{-- ============ MODE TABLE ============ --}}
        @if(($mode ?? 'table') === 'table')

            <div class="row match-height">
                <div class="col-12">

                    {{-- ===================== CARD DES FILTRES ======================== --}}
                    <div class="card shadow-sm border-0 mb-4">

                        <button id="filtersToggle" type="button"
                            class="card-header w-100 d-flex align-items-center justify-content-between collapsed"
                            data-bs-toggle="collapse" data-bs-target="#filtersCollapse">

                            <h6 class="mb-0 d-flex align-items-center fw-semibold">
                                <i class="bi bi-funnel me-2"></i> Filtres
                            </h6>
                            <i class="bi bi-chevron-up chevron-toggle"></i>
                        </button>

                        <div class="collapse" id="filtersCollapse">
                            <div class="card-body">

                                <div class="row g-4">

                                    {{-- === STATUTS === --}}
                                    <div class="col-md-6 col-lg-4">
                                        <h6 class="small fw-bold text-muted mb-3">Statuts</h6>
                                        <div class="row row-cols-3 g-2">
                                            @foreach($statusOrder as $k)
                                                <div class="col">
                                                    <div class="form-check">
                                                        <input class="form-check-input status-filter"
                                                               type="checkbox" value="{{ $k }}"
                                                               id="st-{{ $k }}" checked>
                                                        <label class="form-check-label small"
                                                               for="st-{{ $k }}">
                                                            {{ $statusTitles[$k] }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- === TYPE PROJET === --}}
                                    <div class="col-md-6 col-lg-4">
                                        <h6 class="small fw-bold text-muted mb-3">Type de projet</h6>
                                        <div class="row row-cols-3 g-2">
                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input type-filter"
                                                        type="radio" name="typeProjet"
                                                        value="tous" id="tous" checked>
                                                    <label class="form-check-label small" for="tous">Tous</label>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input type-filter"
                                                        type="radio" name="typeProjet" value="public" id="public">
                                                    <label class="form-check-label small" for="public">Public</label>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="form-check">
                                                    <input class="form-check-input type-filter"
                                                        type="radio" name="typeProjet" value="prive" id="prive">
                                                    <label class="form-check-label small" for="prive">Privé</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- === ACTEURS === --}}
                                    <div class="col-md-12 col-lg-4">
                                        <h6 class="small fw-bold text-muted mb-3">Acteurs</h6>

                                        <div class="row row-cols-3 g-2">
                                            @foreach($roles as $code)
                                                <div class="col">
                                                    <div class="form-check">
                                                        <input class="form-check-input actor-filter"
                                                            type="checkbox" value="{{ $code }}" id="role-{{ $code }}" checked>
                                                        <label class="form-check-label small" for="role-{{ $code }}">
                                                            {{ $roleLabels[$code] ?? $code }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>

                                <div class="text-center mt-4">
                                    <button id="btn-reset-filters" class="btn btn-light border px-4 rounded-pill">
                                        <i class="bi bi-arrow-clockwise"></i> Réinitialiser
                                    </button>
                                </div>

                            </div>
                        </div>

                    </div>

                    {{-- ===================== TABLEAU ======================== --}}
                    <div class="card">
                        <div class="card-header text-center">
                            <h5 class="card-title">
                                {{ ($typeVue ?? 'nombre')==='finance' ? 'Tableau financier (montants)' : 'Nombre de projets' }}
                            </h5>
                        </div>

                        <div class="card-content">
                            <div class="card-body">

                                <div class="table-responsive">

                                    <table
                                        class="table table-striped table-bordered tableClass"
                                        id="table1"
                                        data-status-order='@json($statusOrder)'
                                    >
                                        <thead>

                                            <!-- GROUPES -->
                                            <tr class="group-row">
                                                <th></th>
                                                @foreach($statusOrder as $k)
                                                    <th colspan="3" class="text-center">
                                                        {{ $statusTitles[$k] }}
                                                    </th>
                                                @endforeach
                                            </tr>

                                            <!-- FEUILLES -->
                                            <tr class="leaf-row">
                                                <th></th>
                                                @foreach($statusOrder as $k)
                                                    <th>Total</th>
                                                    <th>Public</th>
                                                    <th>Privé</th>
                                                @endforeach
                                            </tr>

                                        </thead>

                                        <tbody>

                                            {{-- ============ NATIONAL ============ --}}
                                            <tr class="national-row" data-role="national">
                                                <td>
                                                    <a href="{{ route($routeBase,
                                                        ['ecran_id'=>$ecran->id,'type'=>'national']) }}">
                                                        National
                                                    </a>
                                                </td>

                                                @foreach($statusOrder as $k)
                                                    {{-- TOTAL --}}
                                                    <td>
                                                        {!! $cellLink(
                                                            $national["total_$k"] ?? 0,
                                                            ['type'=>'national','statut'=>$k,'segment'=>'total']
                                                        ) !!}
                                                    </td>

                                                    {{-- PUBLIC --}}
                                                    <td>
                                                        {!! $cellLink(
                                                            $national["public_$k"] ?? 0,
                                                            ['type'=>'national','statut'=>$k,'segment'=>'public']
                                                        ) !!}
                                                    </td>

                                                    {{-- PRIVE --}}
                                                    <td>
                                                        {!! $cellLink(
                                                            $national["prive_$k"] ?? 0,
                                                            ['type'=>'national','statut'=>$k,'segment'=>'prive']
                                                        ) !!}
                                                    </td>
                                                @endforeach
                                            </tr>

                                            {{-- ============ ROLES ============ --}}
                                            @foreach($roles as $role)
                                                <tr data-role="{{ $role }}">
                                                    <td>
                                                        <a href="{{ route($routeBase,
                                                            ['ecran_id'=>$ecran->id,'type'=>'personnel','role'=>$role]) }}">
                                                            {{ $roleLabels[$role] ?? $role }}
                                                        </a>
                                                    </td>

                                                    @foreach($statusOrder as $k)
                                                        <td>
                                                            {!! $cellLink(
                                                                $statsMoi[$role]["total_$k"] ?? 0,
                                                                ['type'=>'personnel','role'=>$role,'statut'=>$k,'segment'=>'total']
                                                            ) !!}
                                                        </td>
                                                        <td>
                                                            {!! $cellLink(
                                                                $statsMoi[$role]["public_$k"] ?? 0,
                                                                ['type'=>'personnel','role'=>$role,'statut'=>$k,'segment'=>'public']
                                                            ) !!}
                                                        </td>
                                                        <td>
                                                            {!! $cellLink(
                                                                $statsMoi[$role]["prive_$k"] ?? 0,
                                                                ['type'=>'personnel','role'=>$role,'statut'=>$k,'segment'=>'prive']
                                                            ) !!}
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach

                                        </tbody>

                                    </table>

                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>

        {{-- ============ MODE GRAPH ============ --}}
        @elseif(($mode ?? 'table') === 'graph')

            @php
                $grouped  = collect($rows ?? [])->groupBy('famille');
                $tabOrder = ['ETUDE','INFRASTRUCTURE','APPUI'];
                $tabs     = array_values(array_filter($tabOrder, fn($f) => $grouped->has($f)));
            @endphp

            {{-- ===== Graphiques globaux ===== --}}
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="mb-2 text-center">Répartition par famille</h6>
                            <canvas id="chartFamily" style="max-height:220px;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="mb-2 text-center">Créations par année</h6>
                            <canvas id="chartYear" style="max-height:220px;"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="mb-2 text-center">Répartition des acteurs (global)</h6>
                            <canvas id="chartActors" style="max-height:220px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Onglets par famille ===== --}}
            <div class="card shadow-sm mt-4">
                <div class="card-body">
                Demande de projet 
                    <ul class="nav nav-pills mb-3" id="famTabs" role="tablist">
                            
                            @forelse($tabs as $i => $fam)
                            @php
                                $countFam = $grouped[$fam]->count();
                                $actorsFam = $actorCountsByFamily[$fam] ?? [
                                    "Maîtres d’Ouvrage"=>0,"Maîtres d’Œuvre"=>0,"Bailleurs"=>0,"Chefs de Projet"=>0,"Bénéficiaires"=>0
                                ];
                            @endphp
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $i===0 ? 'active' : '' }}"
                                        id="tab-{{ $fam }}"
                                        data-bs-toggle="pill"
                                        data-bs-target="#panel-{{ $fam }}"
                                        type="button" role="tab" aria-controls="panel-{{ $fam }}"
                                        aria-selected="{{ $i===0 ? 'true' : 'false' }}">
                                    {{ $fam }}
                                    <span class="badge bg-secondary ms-2">{{ number_format($countFam, 0, ',', ' ') }}</span>
                                </button>
                            </li>
                        @empty
                            <li class="nav-item"><span class="text-muted">Aucune famille disponible</span></li>
                        @endforelse
                    </ul>

                    <div class="tab-content" id="famTabsContent">
                        @forelse($tabs as $i => $fam)
                            @php
                                $rowsFam   = $grouped[$fam];
                                $actorsFam = $actorCountsByFamily[$fam] ?? [
                                    "Maîtres d’Ouvrage"=>0,"Maîtres d’Œuvre"=>0,"Bailleurs"=>0,"Chefs de Projet"=>0,"Bénéficiaires"=>0
                                ];
                            @endphp
                            <div class="tab-pane fade {{ $i===0 ? 'show active' : '' }}" id="panel-{{ $fam }}" role="tabpanel" aria-labelledby="tab-{{ $fam }}">

                                {{-- mini stats acteurs pour la famille --}}
                                <div class="row g-2 mb-3">
                                    @foreach($actorsFam as $lib => $val)
                                        <div class="col-sm-6 col-lg-3">
                                            <div class="border rounded p-2 d-flex justify-content-between">
                                                <span class="small" style="color: black">{{ $lib }}</span>
                                                <span class="badge bg-light text-dark" style="color: black">
                                                    {{ number_format($val, 0, ',', ' ') }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle dt-demandes" id="table-{{ Str::slug($fam) }}">
                                        <thead>
                                            <tr>
                                                <th>Famille</th>
                                                <th>Code</th>
                                                <th>Intitulé</th>
                                                <th>Début prévu</th>
                                                <th>Fin prévue</th>
                                                <th class="text-end">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($rowsFam as $r)
                                                <tr>
                                                    <td><span class="badge bg-secondary">{{ $r->famille }}</span></td>
                                                    <td class="font-monospace">{{ $r->code }}</td>
                                                    <td>{{ $r->intitule }}</td>
                                                    <td>{{ $r->date_debut ? \Carbon\Carbon::parse($r->date_debut)->format('d/m/Y') : '—' }}</td>
                                                    <td>{{ $r->date_fin ? \Carbon\Carbon::parse($r->date_fin)->format('d/m/Y') : '—' }}</td>
                                                    <td class="text-end">
                                                        {{ $r->montant !== null ? number_format($r->montant, 0, ',', ' ') : '—' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted py-4">
                                                        Aucun projet au statut “Prévu”.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="text-muted">Aucune donnée à afficher.</div>
                        @endforelse
                    </div>
                </div>
            </div>

        @endif
    </div>
</section>

{{-- ==================== SCRIPTS COMMUNS ==================== --}}
<script>
    // Horloge en haut à droite
    setInterval(() => {
        const el = document.getElementById('date-now');
        if (el) {
            el.textContent = new Date().toLocaleString('fr-FR');
        }
    }, 1000);
</script>

{{-- ==================== JS MODE TABLE ==================== --}}
@if(($mode ?? 'table') === 'table')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const table = $("#table1");
    const statusOrder = JSON.parse(table.attr("data-status-order") || "[]");

    const dt = table.DataTable({
        paging: false,
        info: false,
        searching: false,
        ordering: false,
        scrollX: true,
        fixedHeader: true,
        orderCellsTop: true
    });

    dt.on('draw.dt', function () {
        const $body = $(dt.table().body());
        const $nat = $body.find('tr.national-row');
        if ($nat.length) {
            $nat.prependTo($body);
        }

        refreshGroupedHeader();
        fixHiddenColumnWidths();
        rebuildRatios();
        applyRowFiltering();
    });

    function parseNum(value) {
        if (!value) return 0;
        return Number(
            String(value)
                .replace(/\u00A0/g, '')
                .replace(/\s+/g, '')
                .replace(',', '.')
        ) || 0;
    }

    function actorRowHasData(row) {
        const cells = Array.from(row.querySelectorAll('td')).slice(1);
        return cells.some(td => parseNum(td.innerText) > 0);
    }

    function saveFilters() {
        const selectedStatuses = [...document.querySelectorAll(".status-filter:checked")].map(e => e.value);
        const selectedType = document.querySelector('input[name="typeProjet"]:checked')?.value || 'tous';
        const selectedActors = [...document.querySelectorAll(".actor-filter:checked")].map(e => e.value);
        localStorage.setItem("statFilters", JSON.stringify({
            statuses: selectedStatuses,
            type: selectedType,
            actors: selectedActors
        }));
    }

    function loadFilters() {
        const saved = JSON.parse(localStorage.getItem("statFilters") || "{}");
        if (saved.statuses) {
            document.querySelectorAll(".status-filter").forEach(cb => {
                cb.checked = saved.statuses.includes(cb.value);
            });
        }
        if (saved.type) {
            const rb = document.querySelector(`input[name="typeProjet"][value="${saved.type}"]`);
            if (rb) rb.checked = true;
        }
        if (saved.actors) {
            document.querySelectorAll(".actor-filter").forEach(cb => {
                cb.checked = saved.actors.includes(cb.value);
            });
        }
    }

    function getColIndex(statut, segment) {
        const idxStatut = statusOrder.indexOf(statut);
        if (idxStatut === -1) return null;
        const offset = {total:0, public:1, prive:2}[segment] ?? 0;
        return 1 + idxStatut * 3 + offset;
    }

    function refreshGroupedHeader() {
        const thead    = document.querySelector("#table1 thead");
        if (!thead) return;

        const groupRow = thead.querySelector("tr.group-row");
        if (!groupRow) return;

        const groupThs = groupRow.querySelectorAll("th");

        statusOrder.forEach((st, i) => {
            const th = groupThs[i + 1];
            if (!th) return;

            const idxTotal  = getColIndex(st, 'total');
            const idxPublic = getColIndex(st, 'public');
            const idxPrive  = getColIndex(st, 'prive');

            let visibleCount = 0;
            [idxTotal, idxPublic, idxPrive].forEach(idx => {
                if (idx !== null && dt.column(idx).visible()) visibleCount++;
            });

            if (visibleCount === 0) {
                th.style.display = 'none';
                th.colSpan = 0;
            } else {
                th.style.display = '';
                th.colSpan = visibleCount;
            }
        });
    }

    function applyColumnFiltering() {
        const visibleStatuses = [...document.querySelectorAll(".status-filter:checked")].map(e => e.value);
        const selectedType = document.querySelector('input[name="typeProjet"]:checked')?.value || 'tous';

        statusOrder.forEach(st => {
            const showStatus = visibleStatuses.includes(st);

            const colTotal = getColIndex(st, 'total');
            if (colTotal !== null) {
                dt.column(colTotal).visible(showStatus, false);
            }

            const colPublic = getColIndex(st, 'public');
            if (colPublic !== null) {
                const visible = showStatus && (selectedType === 'tous' || selectedType === 'public');
                dt.column(colPublic).visible(visible, false);
            }

            const colPrive = getColIndex(st, 'prive');
            if (colPrive !== null) {
                const visible = showStatus && (selectedType === 'tous' || selectedType === 'prive');
                dt.column(colPrive).visible(visible, false);
            }
        });

        dt.columns.adjust();
        refreshGroupedHeader();
    }

    function rebuildRatios() {
        document
            .querySelectorAll("#table1 tbody tr[data-role^='ratio-']")
            .forEach(e => e.remove());

        const nat = document.querySelector("#table1 tbody tr[data-role='national']");
        if (!nat) return;

        const tbody = document.querySelector("#table1 tbody");

        const actorRows = [...tbody.querySelectorAll("tr[data-role]")].filter(tr => {
            const role = tr.getAttribute('data-role');
            return role && role !== 'national' && !role.startsWith('ratio-');
        });

        actorRows.forEach(row => {
            const role = row.getAttribute('data-role');

            if (!actorRowHasData(row)) {
                row.style.display = 'none';
                return;
            }

            const ratioRow = document.createElement('tr');
            ratioRow.classList.add('ratio-actor-row');
            ratioRow.setAttribute('data-role', 'ratio-' + role);

            const labelCell = document.createElement('td');
            labelCell.classList.add('ratio-actor-label');
            labelCell.textContent =
                'Ratio ' + (row.children[0].innerText.trim() || role) + ' / National';
            ratioRow.appendChild(labelCell);

            statusOrder.forEach(st => {
                ['total','public','prive'].forEach(segment => {
                    const colIdx = getColIndex(st, segment);
                    const td = document.createElement('td');

                    if (colIdx === null || !dt.column(colIdx).visible()) {
                        return;
                    } else {
                        const valRole = parseNum(row.children[colIdx]?.innerText);
                        const valNat  = parseNum(nat.children[colIdx]?.innerText);

                        let txt = '-';
                        if (valNat > 0) {
                            const p = (valRole / valNat) * 100;
                            txt = p === 0 ? '0%' : p.toFixed(1).replace('.', ',') + '%';
                        }
                        td.textContent = txt;
                    }
                    ratioRow.appendChild(td);
                });
            });

            row.insertAdjacentElement('afterend', ratioRow);
        });
    }

    function applyRowFiltering() {
        const visibleActors = new Set(
            [...document.querySelectorAll(".actor-filter:checked")].map(e => e.value)
        );

        const tbody = document.querySelector("#table1 tbody");

        [...tbody.querySelectorAll("tr[data-role]")].forEach(tr => {
            const role = tr.getAttribute("data-role");
            if (!role) return;

            if (role === "national") {
                tr.style.display = "";
                return;
            }

            if (role.startsWith('ratio-')) return;

            const hasData = actorRowHasData(tr);
            const visible = visibleActors.has(role) && hasData;
            tr.style.display = visible ? "" : "none";

            const ratioRow = tbody.querySelector(`tr[data-role="ratio-${role}"]`);
            if (ratioRow) {
                ratioRow.style.display = visible ? "" : "none";
            }
        });
    }

    function fixHiddenColumnWidths() {
        $("#table1").find("th, td").each(function () {
            const col = dt.column($(this).index());
            if (col && !col.visible()) {
                this.style.width = "0px";
                this.style.minWidth = "0px";
                this.style.maxWidth = "0px";
                this.style.padding = "0";
                this.style.border = "none";
                this.style.overflow = "hidden";
            }
        });
        dt.columns.adjust();
    }

    function applyAllFilters() {
        applyColumnFiltering();
        saveFilters();
        dt.columns.adjust().draw(false);
    }

    document.querySelectorAll(".status-filter, .type-filter, .actor-filter")
        .forEach(el => el.addEventListener("change", applyAllFilters));

    const resetBtn = document.getElementById("btn-reset-filters");
    if (resetBtn) {
        resetBtn.addEventListener("click", function () {
            document.querySelectorAll(".status-filter").forEach(e => e.checked = true);
            const rbTous = document.querySelector('input[name="typeProjet"][value="tous"]');
            if (rbTous) rbTous.checked = true;
            document.querySelectorAll(".actor-filter").forEach(e => e.checked = true);
            applyAllFilters();
        });
    }

    loadFilters();
    applyAllFilters();

    const filtersToggle = document.getElementById("filtersToggle");
    if (filtersToggle) {
        filtersToggle.addEventListener("click", function () {
            this.classList.toggle("collapsed");
        });
    }

});
</script>
@endif

{{-- ==================== JS MODE GRAPH ==================== --}}
@if(($mode ?? 'table') === 'graph')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const byFamily    = @json($byFamily ?? []);
        const yearCounts  = @json($yearCounts ?? []);
        const actorsCount = @json($actorCounts ?? []);

        const mk = (id, type, labels, data, label) => {
            const el = document.getElementById(id);
            if (!el) return;
            new Chart(el.getContext('2d'), {
                type,
                data: { labels, datasets: [{ label, data }] },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } },
                    scales: (type === 'bar' || type === 'line')
                        ? { x: { type: 'category' }, y: { beginAtZero: true } }
                        : {}
                }
            });
        };

        mk('chartFamily', 'doughnut', Object.keys(byFamily), Object.values(byFamily), 'Projets');

        const yKeys = Object.keys(yearCounts).sort((a,b)=>parseInt(a)-parseInt(b));
        mk('chartYear', 'bar', yKeys, yKeys.map(k=>yearCounts[k]), 'Créations');

        const aLabels = Object.keys(actorsCount);
        const aData   = Object.values(actorsCount);
        mk('chartActors', 'doughnut', aLabels, aData, 'Acteurs');
    });
</script>

<script>
$(document).ready(function () {
    const userName = '{{ auth()->user()?->acteur?->libelle_court }} {{ auth()->user()?->acteur?->libelle_long }}';

    $('.dt-demandes').each(function () {
        const id = this.id;
        if (typeof initDataTable === 'function') {
            initDataTable(userName, id, 'Liste des demandes de projet');
        }
    });

    document.querySelectorAll('button[data-bs-toggle="pill"]').forEach((btn) => {
        btn.addEventListener('shown.bs.tab', () => {
            if ($.fn.dataTable) {
                $.fn.dataTable
                    .tables({ visible: true, api: true })
                    .columns.adjust();
            }
        });
    });
});
</script>
@endif

@endsection
