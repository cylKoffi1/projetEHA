@extends('layouts.app')

@section('content')
<style>
  .info{background:rgba(255,255,255,.57);position:absolute;top:10px;right:10px;padding:10px;box-shadow:0 0 15px rgba(0,0,0,.2);border-radius:5px}
  .info .title{font-weight:700}
  .leaflet-control-zoom{display:none}
  .leaflet-control-attribution{visibility:hidden}

  /* leaflet interactivity */
  .leaflet-marker-icon,.leaflet-marker-shadow,.leaflet-image-layer,.leaflet-pane>svg path,.leaflet-tile-container{pointer-events:none}
  .leaflet-marker-icon.leaflet-interactive,.leaflet-image-layer.leaflet-interactive,.leaflet-pane>svg path.leaflet-interactive,svg.leaflet-image-layer.leaflet-interactive path{pointer-events:auto}

  .info.legend{background:rgba(255,255,255,.57);padding:10px 15px;font:14px Arial,sans-serif;box-shadow:0 0 15px rgba(0,0,0,.2);border-radius:5px;line-height:18px;color:#333}
  .info.legend h4{margin:0 0 5px;font-size:16px;font-weight:700;color:#000}
  .info.legend p{margin:0 0 10px;font-size:14px;color:#555}
  .info.legend i{width:18px;height:18px;float:left;margin-right:8px;opacity:.7;border-radius:3px}

  .leaflet-interactive:focus{outline:none}
</style>

<section id="multiple-column-form">
  <div class="row match-height">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <h4 class="card-title">Infrastructures bénéficiaires — Visualisation sur la carte</h4>
          <hr>

          <div class="row g-2 align-items-end">
            <div class="col-12">
              <div class="row g-2 align-items-end">
                <div class="col-md-4">
                  <label class="form-control-label">Dates</label>
                  <div class="d-flex gap-3 flex-wrap">
                    <label class="mb-0"><input type="radio" name="dateType" value="prévisionnelles"> Prévisionnelles</label>
                    <label class="mb-0"><input type="radio" name="dateType" value="effectives"> Effectives</label>
                    <label class="mb-0"><input type="radio" name="dateType" value="Tous" checked> Sans filtre</label>
                  </div>
                </div>
                <div class="col-md-2">
                  <label class="form-control-label">Début</label>
                  <input type="date" class="form-control" id="start_date">
                </div>
                <div class="col-md-2">
                  <label class="form-control-label">Fin</label>
                  <input type="date" class="form-control" id="end_date">
                </div>
                <div class="col-md-2">
                  <label class="form-control-label">Bailleur</label>
                  <select class="form-control" id="bailleur">
                    <option value="">Tous</option>
                    @foreach ($Bailleurs as $Bailleur)
                      <option value="{{ $Bailleur->code_acteur }}">{{ $Bailleur->libelle_court }} {{ $Bailleur->libelle_long }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-2">
                  <label class="form-control-label">Statut</label>
                  <select class="form-control" id="status">
                    <option value="">Tous</option>
                    @foreach ($TypesStatuts as $statut)
                      <option value="{{ $statut->id }}">{{ $statut->libelle }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="row mt-2">
                <div class="col text-end">
                  <button class="btn btn-secondary" id="filterButton" type="button">Filtrer</button>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-control-label">Métrique</label>
              <div class="d-flex gap-3">
                <label class="mb-0"><input type="radio" name="metric" value="count" checked> Nombre</label>
                <label class="mb-0"><input type="radio" name="metric" value="cost"> Montant</label>
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-control-label">Filtre financement</label>
              <div class="d-flex gap-3">
                <label class="mb-0"><input type="radio" name="finFilter" value="cumul" checked> Cumul</label>
                <label class="mb-0"><input type="radio" name="finFilter" value="public"> Public</label>
                <label class="mb-0"><input type="radio" name="finFilter" value="private"> Privé</label>
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-control-label">Groupe projet (optionnel)</label>
              <select class="form-control" id="groupeSelect">
                <option value="">Tous</option>
                @foreach(($groupesProjet ?? []) as $gp)
                  <option value="{{ $gp->code }}">{{ $gp->libelle ?? $gp->code }}</option>
                @endforeach
              </select>
              <small class="text-muted">Si votre groupe session n’est pas BTP, ce filtre est ignoré (sécurité).</small>
            </div>
          </div>
        </div>

        <div class="card-content">
          <div class="card-body">
            <div class="row" style="flex-wrap:nowrap">
              <div class="col">
                <div
                  id="countryMap"
                  style="height:590px;outline-style:none;"
                  data-alpha3="{{ $codeAlpha3 }}"
                  data-session-group="{{ $codeGroupeProjet }}"
                  data-zoom='@json($codeZoom)'
                  data-niveaux='@json($niveau)'
                  data-groupes='@json($groupesProjet ?? [])'
                ></div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
  window.APP = {
    BASE_URL:  "{{ url('/') }}",
    API_URL:   "{{ url('/api') }}",
    GEOJSON:   "{{ url('/geojson') }}"
  };
</script>

<script src="{{ asset('geojsonCode/autresRequetes-map.js') }}"></script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    window.currentMapMetric = 'count'; // 'count' | 'cost'
    window.currentMapFilter = 'cumul'; // 'cumul' | 'public' | 'private'

    const mapEl = document.getElementById('countryMap');
    const countryAlpha3Code = mapEl?.dataset?.alpha3 || '';
    const codeGroupeProjet = mapEl?.dataset?.sessionGroup || '';
    const safeJsonParse = (text, fallback) => {
      try { return JSON.parse(text); } catch(e) { return fallback; }
    };
    const codeZoom = safeJsonParse(mapEl?.dataset?.zoom || '{}', {});
    const niveaux = safeJsonParse(mapEl?.dataset?.niveaux || '[]', []);
    const groupesProjet = safeJsonParse(mapEl?.dataset?.groupes || '[]', []);

    // Pré-sélection du groupe : si pas BTP, on met le groupe de session (le back forcera de toute façon)
    const groupeSelect = document.getElementById('groupeSelect');
    if (groupeSelect && codeGroupeProjet && String(codeGroupeProjet).toUpperCase() !== 'BTP') {
      groupeSelect.value = String(codeGroupeProjet);
    }

    // Init carte
    window.initAutresRequetesMap(countryAlpha3Code, codeZoom, groupesProjet, niveaux, {
      initialGroup: groupeSelect ? groupeSelect.value : ''
    });

    // Listeners UI
    document.querySelectorAll('input[name="metric"]').forEach((el) => {
      el.addEventListener('change', () => {
        window.currentMapMetric = el.value;
        window.reloadAutresRequetesMap?.();
      });
    });
    document.querySelectorAll('input[name="finFilter"]').forEach((el) => {
      el.addEventListener('change', () => {
        window.currentMapFilter = el.value;
        window.reloadAutresRequetesMap?.();
      });
    });
    if (groupeSelect) {
      groupeSelect.addEventListener('change', () => {
        window.setAutresRequetesGroup?.(groupeSelect.value);
      });
    }

    // Filtres (dates / bailleur / statut)
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const statusInput = document.getElementById('status');
    const bailleurInput = document.getElementById('bailleur');

    endDateInput?.addEventListener('change', function(){
      if (endDateInput.value && startDateInput.value && endDateInput.value < startDateInput.value) {
        alert('La date de fin ne peut pas être antérieure à la date de début.');
        endDateInput.value = startDateInput.value;
      }
    });

    document.getElementById('filterButton')?.addEventListener('click', function(){
      const start_date = startDateInput?.value || '';
      const end_date = endDateInput?.value || '';
      const status = statusInput?.value || '';
      const bailleur = bailleurInput?.value || '';
      const dateType = document.querySelector('input[name="dateType"]:checked')?.value || 'Tous';

      const payload = {
        start_date,
        end_date,
        status,
        bailleur,
        date_type: dateType,
      };

      window.applyAutresRequetesFilters?.(payload);
    });
  });
</script>

@endsection
