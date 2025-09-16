@extends('layouts.app')

@section('content')
<style>
  .info{background:rgba(255,255,255,.9);position:absolute;top:10px;right:10px;padding:10px;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,.15)}
  .info .title{font-weight:700;margin-bottom:6px}
  .legend{background:rgba(255,255,255,.9);padding:10px 12px;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,.15)}
  .leaflet-control-attribution{display:none}
  .drawer-overlay{position:fixed;inset:0;background:rgba(17,24,39,.35);backdrop-filter:blur(2px);opacity:0;visibility:hidden;transition:opacity .25s ease,visibility .25s ease;z-index:1049}
  .drawer{position:fixed;top:0;right:-52vw;width:52vw;height:100vh;background:#fff;box-shadow:-8px 0 24px rgba(0,0,0,.18);transition:right .30s ease;z-index:1050;display:flex;flex-direction:column;border-top-left-radius:12px;border-bottom-left-radius:12px}
  .drawer.open{right:0}
  .drawer-overlay.open{opacity:1;visibility:visible}
  .drawer-header{padding:14px 18px;border-bottom:1px solid #e5e7eb}
  .drawer-body{padding:14px 18px;overflow:auto}
  .divicon{display:flex;align-items:center;justify-content:center;font-weight:700;border-radius:999px;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.25);width:34px;height:34px}
  .filter-badge{display:inline-flex;align-items:center;gap:6px;border:1px solid #e5e7eb;border-radius:999px;padding:6px 10px;background:#fff}
</style>

<section class="mb-2">
  <div class="card">
    <div class="card-header">
      <h4 class="card-title mb-1">Visualisation des infrastructures</h4>
      <div class="d-flex flex-wrap align-items-end gap-3">
        <div>
          <label class="form-label mb-1">Dates</label>
          <div class="d-flex gap-3">
            <label><input type="radio" name="dateType" value="prévisionnelles"> Prévisionnelles</label>
            <label><input type="radio" name="dateType" value="effectives"> Effectives</label>
            <label><input type="radio" name="dateType" value="Tous" checked> Sans filtre</label>
          </div>
        </div>

        <div>
          <div class="d-flex gap-2">
            <div>
              <small>Début</small>
              <input type="date" class="form-control" id="start_date">
            </div>
            <div>
              <small>Fin</small>
              <input type="date" class="form-control" id="end_date">
            </div>
          </div>
        </div>

        <div>
          <small>Statut d’avancement</small>
          <select id="statusInfra" class="form-control">
            <option value="all">Toutes</option>
            <option value="done">Terminées</option>
            <option value="todo">Non terminées</option>
          </select>
        </div>

        <div>
          <button id="btnFilter" class="btn btn-primary">Filtrer & Mettre à jour</button>
        </div>

        <div class="ms-auto d-flex gap-2">
          <span class="filter-badge"><strong>Niveau sélectionné:</strong> <span id="lblLevel">—</span></span>
          <span class="filter-badge"><strong>Zone:</strong> <span id="lblZone">—</span></span>
        </div>
      </div>
    </div>

    <div class="card-body">
      <div id="countryMap" style="height: 630px; width: 100%;"></div>
    </div>
  </div>
</section>

<!-- Drawer -->
<div id="drawerOverlay" class="drawer-overlay"></div>
<div id="projectDrawer" class="drawer" role="dialog" aria-modal="true">
  <div class="drawer-header">
    <div class="d-flex align-items-center justify-content-between">
      <strong id="drawerTitle">Détails des projets</strong>
      <button class="btn btn-sm btn-light" onclick="window.closeProjectDrawer()">Fermer</button>
    </div>
    <div class="text-muted" id="drawerBreadcrumb">—</div>
    <div class="mt-2">
      <input type="text" id="drawerSearch" class="form-control" placeholder="Rechercher (code, libellé)…">
    </div>
  </div>
  <div class="drawer-body">
    <div class="mb-2 small text-muted" id="drawerMeta">—</div>
    <div class="table-responsive">
      <table class="table table-sm table-striped table-bordered mb-0">
        <thead>
          <tr>
            <th style="width:6%">#</th>
            <th style="width:22%">Code</th>
            <th>Libellé</th>
            <th style="width:18%">Coût</th>
          </tr>
        </thead>
        <tbody id="drawerTableBody">
          <tr><td colspan="4" class="text-center">Sélectionnez une cellule de la carte…</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
window.APP = {
  BASE_URL: "{{ url('/') }}",
  API_URL:  "{{ url('/api') }}",
  GEOJSON:  "{{ url('/geojson') }}",
  GROUP:    "{{ $codeGroupeProjet }}",
  ALPHA3:   "{{ $codeAlpha3 }}",
  ZOOM:     @json($codeZoom),
  NIVEAUX:  @json($niveau),
  DOMAINES: @json($domainesAssocie)
};

</script>
<script src="{{ asset('geojsonCode/map-infras.js') }}"></script>
@endsection
