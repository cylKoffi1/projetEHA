{{--previsualisationgraphique--}}
@extends('layouts.app')
<style>
    /* Empêche les largeurs cassées avec scrollX dans des onglets cachés */
.dataTables_wrapper .dataTables_scrollHeadInner,
.dataTables_wrapper .dataTables_scrollHeadInner table,
.dataTables_wrapper .dataTables_scrollBody table {
  width: 100% !important;
}

table.dataTable {
  width: 100% !important;
  table-layout: auto;
}
.dt-button, .dataTables_info, .paginate_button, #table-projet_next, #table-projet_previous
#table-projet_paginate span a  {
    color: black !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current, .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    color: black !important;
}
</style>
@section('content')
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

            <div class="row align-items-center">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>
                        <i class="bi bi-arrow-return-left return" onclick="history.back()" style="cursor:pointer;"></i>
                        Etude de projets
                    </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">Naissance de projet</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Consultation des demandes</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

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
        @php
            $grouped  = collect($rows ?? [])->groupBy('famille');
            $tabOrder = ['PROJET','ETUDE','APPUI'];
            $tabs     = array_values(array_filter($tabOrder, fn($f) => $grouped->has($f)));
        @endphp

        <div class="card shadow-sm mt-4">
            <div class="card-body">
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
                                            <span class="badge bg-light text-dark" style="color: black">{{ number_format($val, 0, ',', ' ') }}</span>
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
                                                <td colspan="6" class="text-center text-muted py-4">Aucun projet au statut “Prévu”.</td>
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

    </div>
</section>

{{-- ====== Scripts ====== --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

    // horloge discrète
    setInterval(()=>{ const el = document.getElementById('date-now'); if(el) el.textContent = new Date().toLocaleString(); }, 1000);

    document.addEventListener('DOMContentLoaded', () => {
        // IMPORTANT : utilisez [] et pas {} pour éviter les erreurs Blade/PHP
        const byFamily    = @json($byFamily ?? []);
        const yearCounts  = @json($yearCounts ?? []);
        const actorsCount = @json($actorCounts ?? []); // global

        const mk = (id, type, labels, data, label) => {
            const el = document.getElementById(id);
            if (!el) return;
            new Chart(el.getContext('2d'), {
                type,
                data: { labels, datasets: [{ label, data }] },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'bottom' } },
                    scales: (type === 'bar' || type === 'line') ? { x: { type: 'category' }, y: { beginAtZero: true } } : {}
                }
            });
        };

        // Doughnut par famille (global)
        mk('chartFamily', 'doughnut', Object.keys(byFamily), Object.values(byFamily), 'Projets');

        // Bar par année (global, triée)
        const yKeys = Object.keys(yearCounts).sort((a,b)=>parseInt(a)-parseInt(b));
        mk('chartYear', 'bar', yKeys, yKeys.map(k=>yearCounts[k]), 'Créations');

        // Doughnut acteurs (global)
        const aLabels = Object.keys(actorsCount);
        const aData   = Object.values(actorsCount);
        mk('chartActors', 'doughnut', aLabels, aData, 'Acteurs');
    });
</script>
<script>
$(document).ready(function () {
    const userName = '{{ auth()->user()?->acteur?->libelle_court }} {{ auth()->user()?->acteur?->libelle_long }}';

    // 1) init pour chaque tableau présent (tous onglets confondus)
    $('.dt-demandes').each(function () {
        const id = this.id; // ex: "table-projet"
        initDataTable(userName, id, 'Liste des demandes de projet');
    });

    // 2) corriger les largeurs quand un onglet devient visible
    document.querySelectorAll('button[data-bs-toggle="pill"]').forEach((btn) => {
        btn.addEventListener('shown.bs.tab', () => {
            // Ajuste toutes les DataTables visibles (sans toucher à votre fonction)
            if ($.fn.dataTable) {
                $.fn.dataTable
                    .tables({ visible: true, api: true })
                    .columns.adjust(); // recalcul des largeurs
            }
        });
    });
});
</script>

@endsection
