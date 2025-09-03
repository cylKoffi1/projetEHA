@extends('layouts.app')

@section('content')
<style>
  .info{background:rgba(255,255,255,.9);position:absolute;top:10px;right:10px;padding:12px 14px;box-shadow:0 8px 24px rgba(0,0,0,.12);border-radius:10px;max-width:560px}
  .info.legend{left:10px;right:auto}
  .drawer-overlay{position:fixed;inset:0;background:rgba(15,23,42,.35);backdrop-filter:blur(2px);opacity:0;visibility:hidden;transition:.25s;z-index:1049}
  .drawer{position:fixed;top:0;right:-52vw;width:52vw;height:100vh;background:#fff;box-shadow:-16px 0 48px rgba(0,0,0,.18);transition:right .3s ease;z-index:1050;display:flex;flex-direction:column;border-top-left-radius:14px;border-bottom-left-radius:14px}
  .drawer.open{right:0}.drawer-overlay.open{opacity:1;visibility:visible}
  .drawer-header{padding:14px 18px;border-bottom:1px solid #e5e7eb;background:linear-gradient(180deg,#f8fafc 0%,#fff 100%)}
  .drawer-title{font-weight:700}
  .badge{display:inline-block;padding:4px 10px;font-size:12px;border-radius:14px;background:#eef2ff;color:#3730a3;border:1px solid #e0e7ff;margin-right:6px}
  .badge.gray{background:#f3f4f6;color:#374151;border-color:#e5e7eb}
  .table thead th{position:sticky;top:0;background:#f8fafc;z-index:1}
  .leaflet-control-attribution{display:none}
</style>

<section class="container-fluid">
  <div class="card">
    <div class="card-header">
      <h4 class="card-title">Carte — Infrastructures bénéficiaires de projets</h4>
      <div class="row g-3 mt-2">
        <div class="col-md-2">
          <label class="form-label">Groupe projet</label>
          <select id="filtreGroupe" class="form-select">
            <option value="">Tous</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Domaine</label>
          <select id="filtreDomaine" class="form-select">
            <option value="">Tous</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Sous-domaine</label>
          <select id="filtreSous" class="form-select">
            <option value="">Tous</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Financement</label>
          <select id="filtreFinance" class="form-select">
            <option value="cumul">Cumul</option>
            <option value="public">Public</option>
            <option value="private">Privé</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Métrique</label>
          <select id="filtreMetric" class="form-select">
            <option value="count">Nb d’infras</option>
            <option value="cost">Montant (réparti)</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label">Début</label>
          <input type="date" id="start_date" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">Fin</label>
          <input type="date" id="end_date" class="form-control">
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button id="btnApply" class="btn btn-primary w-100">Appliquer</button>
        </div>
      </div>
    </div>

    <div class="card-body">
      <div id="countryMap" style="height: 640px;"></div>
    </div>
  </div>
</section>

<!-- Drawer -->
<div id="drawerOverlay" class="drawer-overlay"></div>
<div id="infraDrawer" class="drawer" role="dialog" aria-modal="true" aria-label="Détails">
  <div class="drawer-header">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <div id="drawerTitle" class="drawer-title">Détails — Infrastructures & Projets</div>
        <div class="mt-1">
          <span class="badge gray" id="drawerLevel">Niveau —</span>
          <span class="badge" id="drawerFilter">Filtre: cumul</span>
          <span class="badge" id="drawerDomain">Domaine: Tous</span>
        </div>
      </div>
      <button type="button" class="btn btn-light" onclick="window.closeInfraDrawer()">×</button>
    </div>
    <input class="form-control mt-2" id="drawerSearch" placeholder="Rechercher un projet ou une infra...">
  </div>
  <div class="p-3" style="overflow:auto">
    <div id="drawerMeta" class="text-muted small mb-2"></div>

    <div class="row g-2">
      <div class="col-12">
        <h6 class="mb-2">Infrastructures</h6>
        <div class="table-responsive">
          <table class="table table-sm table-bordered">
            <thead><tr><th>#</th><th>Code</th><th>Libellé</th><th>Coord.</th></tr></thead>
            <tbody id="drawerInfrasBody"><tr><td colspan="4" class="text-center">Sélectionnez une zone…</td></tr></tbody>
          </table>
        </div>
      </div>

      <div class="col-12 mt-3">
        <h6 class="mb-2">Projets</h6>
        <div class="table-responsive">
          <table class="table table-sm table-striped table-bordered">
            <thead><tr><th>#</th><th>Code</th><th>Libellé</th><th>Coût</th><th>Fin.</th></tr></thead>
            <tbody id="drawerProjectsBody"><tr><td colspan="5" class="text-center">—</td></tr></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
  window.APP = {
    BASE_URL:  "{{ url('/') }}",
    API_URL:   "{{ url('/api') }}",
    GEOJSON:   "{{ url('/geojson') }}",
    ALPHA3:    "{{ $codeAlpha3 }}",
    NIVEAUX:   @json($niveau),
    ZOOM:      @json($codeZoom)
  };
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chroma-js/2.1.0/chroma.min.js"></script>
<script src="{{ asset('geojsonCode/map-infras.js') }}"></script>
@endsection
