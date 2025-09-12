@extends('layouts.app')

@section('content')
<div class="page-heading">
  <div class="page-title">
    <div class="row">
      <div class="col-12 col-md-6 order-md-1 order-last">
        <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i> Gestion financière</h3>
        <p class="text-subtitle text-muted">Tableau de bord & Représentations</p>
      </div>
      <div class="col-12 col-md-6 order-md-2 order-first">
        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Gestion Financière</a></li>
            <li class="breadcrumb-item active">Dashboards</li>
          </ol>
        </nav>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid">

  {{-- Filtres --}}
  <div class="card mb-3">
    <div class="card-body">
      <form class="row g-3 align-items-end" method="GET" action="{{ route('gf.representation') }}">
        <div class="col-auto">
          <label class="form-label">Année</label>
          <input type="number" min="2000" max="2100" class="form-control" name="annee" value="{{ $annee }}">
        </div>
        <div class="col-auto">
          <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Appliquer</button>
        </div>
        <div class="col text-end">
          <small id="date-now" class="text-muted"></small>
        </div>
      </form>
    </div>
  </div>

  {{-- KPIs --}}
  <div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
      <div class="text-muted">Budget total (projets)</div>
      <div class="h4 mb-0" id="kpiBudget">—</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
      <div class="text-muted">Décaissements ({{ $annee }})</div>
      <div class="h4 mb-0" id="kpiDec">—</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
      <div class="text-muted">Achats ({{ $annee }})</div>
      <div class="h4 mb-0" id="kpiAchats">—</div>
    </div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body">
      <div class="text-muted">Règlements ({{ $annee }})</div>
      <div class="h4 mb-0" id="kpiRegs">—</div>
    </div></div></div>
  </div>

  {{-- LIGNE 1 : PIB proxy & décaissements mensuels --}}
  <div class="row">
    <div class="col-lg-6 mb-4">
      <div class="card h-100">
        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>PIB (proxy) par domaine</h6></div>
        <div class="card-body">
          <canvas id="chartPibSecteur" height="160"></canvas>
          @if(collect($pibParSecteur['montants'] ?? [])->sum() == 0)
            <div class="text-muted small mt-2">Aucune donnée disponible pour l’année sélectionnée.</div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-4">
      <div class="card h-100">
        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Décaissements mensuels — {{ $annee }}</h6></div>
        <div class="card-body"><canvas id="chartDecaissements" height="160"></canvas></div>
      </div>
    </div>
  </div>

  {{-- LIGNE 2 : Dépenses par nature & Top bailleurs --}}
  <div class="row">
    <div class="col-lg-5 mb-4">
      <div class="card h-100">
        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Dépenses par nature — {{ $annee }}</h6></div>
        <div class="card-body"><canvas id="chartNature" height="210"></canvas></div>
      </div>
    </div>

    <div class="col-lg-7 mb-4">
      <div class="card h-100">
        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-trophy me-2"></i>Top 5 bailleurs (décaissements) — {{ $annee }}</h6></div>
        <div class="card-body"><canvas id="chartTopBailleurs" height="210"></canvas></div>
      </div>
    </div>
  </div>

  {{-- LIGNE 3 : Extras (du “plus”) --}}
  <div class="row">
    <div class="col-lg-4 mb-4">
      <div class="card h-100">
        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-currency-exchange me-2"></i>Décaissements par devise</h6></div>
        <div class="card-body"><canvas id="chartDevise" height="200"></canvas></div>
      </div>
    </div>

    <div class="col-lg-4 mb-4">
      <div class="card h-100">
        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-stopwatch me-2"></i>Délai moyen de règlement (jours)</h6></div>
        <div class="card-body"><canvas id="chartDelai" height="200"></canvas></div>
      </div>
    </div>

    <div class="col-lg-4 mb-4">
      <div class="card h-100">
        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-cart-check me-2"></i>Achats mensuels — {{ $annee }}</h6></div>
        <div class="card-body"><canvas id="chartAchats" height="200"></canvas></div>
      </div>
    </div>
  </div>

  {{-- LIGNE 4 : Décaissements par secteur --}}
  <div class="row">
    <div class="col-12 mb-4">
      <div class="card h-100">
        <div class="card-header bg-light"><h6 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Décaissements par secteur — {{ $annee }}</h6></div>
        <div class="card-body"><canvas id="chartSecteur" height="200"></canvas></div>
      </div>
    </div>
  </div>

</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  // horloge
  setInterval(()=>{ const el=document.getElementById('date-now'); if(el) el.textContent=new Date().toLocaleString(); }, 1000);

  // KPIs
  const fmt = (v)=> new Intl.NumberFormat('fr-FR',{maximumFractionDigits:0}).format(v ?? 0);
  document.getElementById('kpiBudget').textContent = fmt(@json($budgetTotal));
  document.getElementById('kpiDec').textContent    = fmt(@json($decaissementsTotal));
  document.getElementById('kpiAchats').textContent = fmt(@json($depensesParNature['data'][0] ?? 0));
  document.getElementById('kpiRegs').textContent   = fmt(@json($depensesParNature['data'][1] ?? 0));

  // Données
  const PIB_LABELS = @json($pibParSecteur['labels'] ?? []);
  const PIB_DATA   = @json($pibParSecteur['montants'] ?? []);
  const DECAISSEMENTS_MENSUELS = @json($decaissementsMensuels ?? []);
  const NATURE_LABELS = @json($depensesParNature['labels'] ?? []);
  const NATURE_DATA   = @json($depensesParNature['data'] ?? []);
  const TOP_LABELS = @json($topBailleurs['labels'] ?? []);
  const TOP_DATA   = @json($topBailleurs['data'] ?? []);
  const DEVISE_LABELS = @json($decaisseParDevise['labels'] ?? []);
  const DEVISE_DATA   = @json($decaisseParDevise['data'] ?? []);
  const DELAI_DATA    = @json($delaiMoyenParMois ?? []);
  const ACHATS_MOIS   = @json($achatsMensuels ?? []);
  const SECT_LABELS   = @json($decaisseParSecteur['labels'] ?? []);
  const SECT_DATA     = @json($decaisseParSecteur['data'] ?? []);

  const fmtCur = (v)=> new Intl.NumberFormat('fr-FR',{ maximumFractionDigits:0 }).format(v);

  // Graphs
  new Chart(document.getElementById('chartPibSecteur'), {
    type:'bar',
    data:{ labels: PIB_LABELS, datasets:[{ label:'Montant (proxy)', data: PIB_DATA }] },
    options:{ responsive:true, plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label:(c)=> fmtCur(c.raw) }}},
              scales:{ y:{ beginAtZero:true, ticks:{ callback:(v)=> fmtCur(v) }}} }
  });

  new Chart(document.getElementById('chartDecaissements'),{
    type:'line',
    data:{ labels:['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'],
           datasets:[{ label:'Décaissements', data:DECAISSEMENTS_MENSUELS, tension:.3, fill:false }]},
    options:{ responsive:true, plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label:(c)=> fmtCur(c.raw) }}},
              scales:{ y:{ beginAtZero:true, ticks:{ callback:(v)=> fmtCur(v) }}} }
  });

  new Chart(document.getElementById('chartNature'),{
    type:'doughnut',
    data:{ labels:NATURE_LABELS, datasets:[{ data:NATURE_DATA }] },
    options:{ responsive:true, plugins:{ legend:{ position:'bottom' }, tooltip:{ callbacks:{ label:(c)=> `${c.label}: ${fmtCur(c.raw)}` }}},
              cutout:'60%' }
  });

  new Chart(document.getElementById('chartTopBailleurs'),{
    type:'bar',
    data:{ labels:TOP_LABELS, datasets:[{ label:'Montant décaissé', data:TOP_DATA }] },
    options:{ indexAxis:'y', responsive:true, plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label:(c)=> fmtCur(c.raw) }}},
              scales:{ x:{ beginAtZero:true, ticks:{ callback:(v)=> fmtCur(v) }}} }
  });

  new Chart(document.getElementById('chartDevise'),{
    type:'doughnut',
    data:{ labels:DEVISE_LABELS, datasets:[{ data:DEVISE_DATA }] },
    options:{ responsive:true, plugins:{ legend:{ position:'bottom' }, tooltip:{ callbacks:{ label:(c)=> `${c.label}: ${fmtCur(c.raw)}` }}}, cutout:'60%' }
  });

  new Chart(document.getElementById('chartDelai'),{
    type:'line',
    data:{ labels:['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'],
           datasets:[{ label:'Jours', data:DELAI_DATA, tension:.3, spanGaps:true }]},
    options:{ responsive:true, plugins:{ legend:{ display:false } } }
  });

  new Chart(document.getElementById('chartAchats'),{
    type:'bar',
    data:{ labels:['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'],
           datasets:[{ label:'Achats', data:ACHATS_MOIS }]},
    options:{ responsive:true, plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label:(c)=> fmtCur(c.raw) }}},
              scales:{ y:{ beginAtZero:true, ticks:{ callback:(v)=> fmtCur(v) }}} }
  });

  new Chart(document.getElementById('chartSecteur'),{
    type:'bar',
    data:{ labels:SECT_LABELS, datasets:[{ label:'Montant', data:SECT_DATA }] },
    options:{ responsive:true, plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label:(c)=> fmtCur(c.raw) }}},
              scales:{ y:{ beginAtZero:true, ticks:{ callback:(v)=> fmtCur(v) }}} }
  });
})();
</script>
@endsection
