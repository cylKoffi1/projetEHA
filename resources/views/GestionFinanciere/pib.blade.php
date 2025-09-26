@extends('layouts.app')

@section('content')
@isset($ecran)
    @can("consulter_ecran_" . $ecran->id)
<div class="page-heading">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-12">
          <li class="breadcrumb-item" style="list-style:none; text-align:right; padding:5px;">
            <span id="date-now" style="color:#34495E;"></span>
          </li>
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
          <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion financière</h3>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
          <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="">PIB</a></li>
            </ol>
          </nav>
          <script>
            setInterval(function() {
              document.getElementById('date-now').textContent = new Date().toLocaleString();
            }, 1000);
          </script>
        </div>
      </div>
    </div>
</div>
<div class="container-fluid">

    {{-- FILTRES (pour le graphe par secteur) --}}
    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-3 align-items-end" id="filterForm" onsubmit="return false;">
                <div class="col-sm-2">
                    <label class="form-label">Année</label>
                    <input type="number" class="form-control" id="annee" value="{{ $annee }}" min="2000" max="{{ date('Y')+1 }}">
                </div>
                <div class="col-sm-2">
                    <button class="btn btn-primary" id="btnApply">Appliquer</button>
                </div>
                <div class="col-sm-8 text-end">
                    <small class="text-muted">
                        Pays: <strong>{{ $pays->nom_fr_fr }}</strong> — Groupe: <strong>{{ $groupeProjet->libelle }}</strong>
                    </small>
                </div>
            </form>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">PIB (année)</div>
                    <div class="h4 mb-0" id="kpiPib">—</div>
                    <small class="text-muted">Devise nationale</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted">Secteurs couverts</div>
                    <div class="h4 mb-0" id="kpiSecteurs">—</div>
                    <small class="text-muted">Nombre de secteurs</small>
                </div>
            </div>
        </div>
    </div>

    {{-- GRAPHIQUE PAR SECTEUR --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Montants par domaine & part du PIB</strong>
            <div class="d-flex gap-2">
                <button type="button" id="btnExportPng" class="btn btn-sm btn-outline-secondary">
                    Export PNG
                </button>
            </div>
        </div>
        <div class="card-body">
            <canvas id="chartPibSecteur" height="90"></canvas>
            <div class="mt-2" id="warnBox" style="display:none;">
                <div class="alert alert-warning py-2 px-3 mb-0" id="warnText"></div>
            </div>
        </div>
    </div>

    {{-- TABLEAU DÉTAILS --}}
<div class="card">
    <div class="card-header"><strong>Détails</strong></div>
    <div class="card-body table-responsive">
        <table class="table table-striped align-middle" id="tableDetails">
            <thead class="table-light">
                <tr>
                    <th>Secteur</th>
                    <th class="text-end">Montant</th>
                    <th class="text-end">Part du PIB</th>
                </tr>
            </thead>
            <tbody id="tbodyDetails"><!-- rempli en JS --></tbody>
        </table>
    </div>
</div>

    {{-- GRAPHIQUE EVOLUTION PIB --}}
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card shadow-sm" style="background-color: rgba(250, 250, 250, 0.9);">
                <div class="card-body p-3">
                    <h5 class="card-title">Représentation du PIB par année</h5>
                </div>
                <canvas id="pibChart" style="max-height: 220px;"></canvas>
            </div>
        </div>
    </div>

    {{-- FORMULAIRE & LISTE PIB --}}
    <section class="mt-4">
        <div class="card">
            <div class="card-header">
                <div style="display:flex; width:100%; justify-content:space-between; align-items:center;">
                    <h5 class="card-title">
                        Ajout d'un PIB
                        <a href="#" data-bs-toggle="collapse" data-bs-target="#formPIB" style="margin-left: 15px;">
                            <i class="bi bi-plus-circle me-1"></i>
                        </a>
                    </h5>

                    <div class="card-title text-end">
                        @if (session('success'))
                            <div class="alert alert-success mb-0">{{ session('success') }}</div>
                        @elseif (session('error'))
                            <div class="alert alert-danger mb-0">{{ session('error') }}</div>
                        @endif
                    </div>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger mt-2">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div id="formPIB" class="collapse show mt-3">
                    <form class="form" method="POST" action="{{ route('pib.store') }}">
                        @csrf
                        <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                        <div class="row">
                            <div class="col-md-4 col-12">
                                <label>Année :</label>
                                <input type="number" class="form-control" name="annee" required>
                            </div>
                            <div class="col-md-4 col-12">
                                <label>Montant :</label>
                                <input type="number" class="form-control" name="montant" required>
                            </div>
                            <div class="col-md-4 col-12">
                                <label>Devise :</label>
                                <select name="devise" class="form-select">
                                    @foreach ($devises as $dev)
                                        <option value="{{ $dev->code }}">{{ $dev->code_long }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col text-end">
                            @can("ajouter_ecran_" . $ecran->id)
                            <button type="submit" class="btn btn-primary mt-2">Enregistrer</button>
                            @endcan
                        </div>
                    </form>
                </div>

                <div style="text-align:center;" class="mt-3">
                    <h5 class="card-title mb-0">Liste des PIB par année</h5>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="tablePib">
                    <thead>
                        <tr>
                            <th>Année</th>
                            <th class="text-end">Montant</th>
                            <th style="width:120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pibs as $pib)
                            <tr>
                                <td class="col-2">{{ $pib->annee }}</td>
                                <td class="col-3 text-end">{{ number_format($pib->montant_pib, 0, ',', ' ') }}</td>
                                <td class="col-2">
                                    <div class="btn-group btn-group-sm">
                                        @can("modifier_ecran_" . $ecran->id)
                                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit-modal-{{ $pib->code }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        @endcan
                                        @can("supprimer_ecran_" . $ecran->id)
                                        <form method="POST" action="{{ route('pib.destroy', $pib->code) }}" onsubmit="return confirm('Supprimer ce PIB ?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                                            <button type="submit" class="btn btn-outline-danger">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>

                            {{-- MODAL EDIT --}}
                            <div class="modal fade" id="edit-modal-{{ $pib->code }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('pib.update', $pib->code) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Modifier PIB</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label>Année :</label>
                                                <input type="number" name="annee" value="{{ $pib->annee }}" class="form-control" required>
                                                <label class="mt-2">Montant :</label>
                                                <input type="number" name="montant" value="{{ $pib->montant_pib }}" class="form-control" required>
                                                <label class="mt-2">Devise :</label>
                                                <select name="devise" class="form-select">
                                                    @foreach ($devises as $dev)
                                                        <option value="{{ $dev->code }}" {{ $dev->code == $pib->devise ? 'selected' : '' }}>
                                                            {{ $dev->code_long }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="modal-footer">
                                                @can("modifier_ecran_" . $ecran->id)
                                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                                @endcan
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </section>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>

<script>
    // horloge
    setInterval(function(){ document.getElementById('date-now').textContent = new Date().toLocaleString(); }, 1000);

    // DataTable si helper dispo
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof initDataTable === 'function') {
            initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tablePib', 'Liste des PIB');
        }
    });

    // ====== GRAPHIQUE EVOLUTION PIB (séries) ======
    (function(){
        const ctx = document.getElementById('pibChart').getContext('2d');
        const pibSeries = @json($pibs->map(fn($p) => ['annee' => (int)$p->annee, 'montant' => (float)$p->montant_pib/1000000000])->values());
        const labels = pibSeries.map(p => p.annee);
        const data   = pibSeries.map(p => p.montant);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'PIB (en milliards)',
                    data,
                    borderWidth: 2,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: { y: { beginAtZero: true } }
            }
        });
    })();

    // ====== GRAPHIQUE PAR SECTEUR (bar + line %) ======

(function () {
    const $annee     = document.getElementById('annee');
    const $btnApply  = document.getElementById('btnApply');
    const $kpiPib    = document.getElementById('kpiPib');
    const $kpiSect   = document.getElementById('kpiSecteurs');
    const $warnBox   = document.getElementById('warnBox');
    const $warnText  = document.getElementById('warnText');
    const $tbody     = document.getElementById('tbodyDetails');

    const fmtNumber = (n) => (n ?? 0).toLocaleString('fr-FR', {maximumFractionDigits: 2});
    const fmtPct    = (p) => (p === null || p === undefined) ? 'n.d.' : (p.toLocaleString('fr-FR', {maximumFractionDigits: 2}) + ' %');

    let chartInstance = null; // <- instance locale

    function buildChart(labels, montants, parts) {
        const canvas = document.getElementById('chartPibSecteur');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        if (chartInstance) chartInstance.destroy();

        chartInstance = new Chart(ctx, {
            data: {
                labels,
                datasets: [
                    { type: 'bar',  label: 'Montant par secteur', data: montants, yAxisID: 'y' },
                    { type: 'line', label: 'Part du PIB (%)',     data: parts,    yAxisID: 'y1', tension: 0.3 }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y:  { beginAtZero: true, ticks: { callback: (v)=>v.toLocaleString('fr-FR') }, title: { display:true, text:'Montant' } },
                    y1: { beginAtZero: true, position: 'right', grid:{ drawOnChartArea:false }, ticks:{ callback:(v)=>v.toLocaleString('fr-FR')+' %' }, title:{ display:true, text:'% PIB' } }
                }
            }
        });

        // >>> EXPOSE L’INSTANCE POUR L’EXPORT
        window.chartPibSecteur = chartInstance;
    }

    function fillTable(rows) {
        if (!$tbody) return;
        $tbody.innerHTML = '';
        rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${r.secteur}</td>
                <td class="text-end">${fmtNumber(r.montant)}</td>
                <td class="text-end">${fmtPct(r.part)}</td>
            `;
            $tbody.appendChild(tr);
        });
    }

    async function loadData() {
        const annee = parseInt($annee?.value || new Date().getFullYear(), 10);
        const url   = `{{ route('gf.representations.pib.data') }}?annee=${annee}`;

        try {
            const r = await fetch(url);
            const res = await r.json();
            if (!res.ok) throw new Error('Chargement impossible');

            if ($kpiPib)  $kpiPib.textContent  = res.pib !== null ? fmtNumber(res.pib) : '—';
            if ($kpiSect) $kpiSect.textContent = (res.labels || []).length;

            buildChart(res.labels || [], res.montants || [], res.parts || []);
            fillTable(res.table || []);

            if (res.warn) {
                if ($warnText) $warnText.textContent = res.warn;
                if ($warnBox)  $warnBox.style.display = 'block';
            } else {
                if ($warnBox)  $warnBox.style.display = 'none';
            }

            // >>> NOTIFIE LE SCRIPT D’EXPORT QUE LES DONNÉES SONT À JOUR
            if (typeof window.__onPibDataLoaded === 'function') {
                window.__onPibDataLoaded(res);
            }

            return res;
        } catch (err) {
            console.error(err);
            if ($kpiPib)  $kpiPib.textContent  = '—';
            if ($kpiSect) $kpiSect.textContent = '—';
            buildChart([], [], []);
            fillTable([]);
            if ($warnText) $warnText.textContent = "Erreur de chargement des données.";
            if ($warnBox)  $warnBox.style.display = 'block';
            return null;
        }
    }

    // Bouton “Appliquer”
    $btnApply?.addEventListener('click', (e) => { e.preventDefault(); loadData(); });

    // >>> EXPOSE LE LOADER SI BESOIN AILLEURS
    window.loadPibSecteurData = loadData;

    // Première charge
    loadData();
})();

</script>
<script>
(function() {
    const btnPng = document.getElementById('btnExportPng');

    let lastPayload = {
        annee: null,
        labels: [],
        montants: [],
        parts: [],
        table: [],
        pib: null,
        pays: @json(isset($pays) ? ($pays->nom_fr_fr ?? '') : ''),
        groupe: @json(session('projet_selectionne') ?? '')
    };

    // Appelée par le loader après chaque fetch réussi
    window.__onPibDataLoaded = function(res){
        lastPayload.annee    = parseInt(document.getElementById('annee').value || new Date().getFullYear(), 10);
        lastPayload.labels   = res.labels || [];
        lastPayload.montants = res.montants || [];
        lastPayload.parts    = res.parts || [];
        lastPayload.table    = res.table || [];
        lastPayload.pib      = res.pib ?? null;
    };

    function getChartInstance() {
        // préférence à l’instance exposée
        return window.chartPibSecteur || Chart.getChart(document.getElementById('chartPibSecteur'));
    }

    btnPng?.addEventListener('click', function() {
        const ch = getChartInstance();
        if (!ch) { alert('Graphique non initialisé.'); return; }
        const dataUrl = ch.toBase64Image('image/png', 1.0);
        const link = document.createElement('a');
        link.href = dataUrl;
        link.download = `pib_secteurs_${lastPayload.annee || new Date().getFullYear()}.png`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

})();
</script>
    @endcan
@endisset
@endsection
