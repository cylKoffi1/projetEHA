/* global L */
'use strict';

// =====================================================
// autresRequetes-map.js
// Carte admin/autresRequetes
// - Similaire à admin/carte, mais:
//   - Données = infrastructures bénéficiaires (via table jouir)
//   - Bulle = répartition par groupes projet (au lieu des domaines)
// =====================================================

// -----------------------------
// Helpers
// -----------------------------
const normalized = (str) => {
  if (!str) return '';
  return str
    .toString()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/\s+/g, ' ')
    .replace(/^region\s+(d[eu']\s+)?/i, '')
    .replace(/^région\s+(d[eu']\s+)?/i, '')
    .replace(/^province\s+(d[eu']\s+)?/i, '')
    .replace(/^departement\s+(d[eu']\s+)?/i, '')
    .replace(/^département\s+(d[eu']\s+)?/i, '')
    .replace(/^district\s+(d[eu']\s+)?/i, '')
    .replace(/^district\s+/i, '')
    .trim();
};

function apiUrl(path = '') {
  const base = (window.APP && window.APP.API_URL) || '';
  return base.replace(/\/+$/, '') + '/' + path.replace(/^\/+/, '');
}

function geojsonBaseUrl() {
  const base = (window.APP && window.APP.GEOJSON) || '';
  return base.replace(/\/+$/, '') + '/';
}

function formatWithSpaces(number) {
  const n = Number(number);
  if (!isFinite(n)) return '0';
  return n.toLocaleString('fr-FR');
}

function escapeHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// -----------------------------
// Geo helpers (point in polygon)
// -----------------------------
function pointInRing(lng, lat, ring) {
  // ring: [[lng,lat], ...] (GeoJSON)
  let inside = false;
  for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
    const xi = ring[i][0], yi = ring[i][1];
    const xj = ring[j][0], yj = ring[j][1];
    const intersect = ((yi > lat) !== (yj > lat)) && (lng < ((xj - xi) * (lat - yi)) / ((yj - yi) || 1e-12) + xi);
    if (intersect) inside = !inside;
  }
  return inside;
}

function pointInPolygon(lng, lat, polygonCoords) {
  // polygonCoords: [outerRing, hole1, hole2...]
  if (!Array.isArray(polygonCoords) || polygonCoords.length === 0) return false;
  const outer = polygonCoords[0];
  if (!pointInRing(lng, lat, outer)) return false;
  for (let k = 1; k < polygonCoords.length; k++) {
    if (pointInRing(lng, lat, polygonCoords[k])) return false; // in a hole
  }
  return true;
}

function pointInGeometry(lat, lng, geometry) {
  if (!geometry) return false;
  const type = geometry.type;
  const coords = geometry.coordinates;
  if (!type || !coords) return false;
  if (type === 'Polygon') {
    return pointInPolygon(lng, lat, coords);
  }
  if (type === 'MultiPolygon') {
    for (const poly of coords) {
      if (pointInPolygon(lng, lat, poly)) return true;
    }
    return false;
  }
  return false;
}

function guessNameKey(props) {
  if (!props) return null;
  const keys = Object.keys(props);
  const pattern = keys.find((k) => /^NAME_\d+$/i.test(k));
  if (pattern) return pattern;
  return keys.find((k) => /name/i.test(k)) || null;
}

async function urlExists(src) {
  try {
    let resp = await fetch(src, { method: 'HEAD' });
    if (resp && resp.ok) return true;
    resp = await fetch(src, { method: 'GET', cache: 'no-cache' });
    return !!(resp && resp.ok);
  } catch (_) {
    return false;
  }
}

async function detectAvailableLevels(countryAlpha3Code) {
  const base = geojsonBaseUrl() + `gadm41_${countryAlpha3Code}_`;
  const ok1 = await urlExists(`${base}1.json.js`);
  const ok2 = ok1 ? await urlExists(`${base}2.json.js`) : false;
  const ok3 = ok2 ? await urlExists(`${base}3.json.js`) : false;
  if (ok1 && ok2 && ok3) return 3;
  if (ok1 && ok2) return 2;
  if (ok1) return 1;
  return 0;
}

function labelForLevel(level, niveauxBackend) {
  const backendLabel = niveauxBackend?.[level - 1]?.libelle_decoupage;
  return backendLabel || `Niveau ${level}`;
}

// -----------------------------
// DATA
// -----------------------------
function processAggregateData(payload) {
  const projects = Array.isArray(payload?.projets) ? payload.projets : (Array.isArray(payload) ? payload : []);
  const data = {};
  (projects || []).forEach((entry) => {
    const key = normalized(entry.name);
    data[key] = {
      name: entry.name,
      code: entry.code,
      level: entry.level,
      count: Number(entry.count) || 0,
      cost: Number(entry.cost) || 0,
      public: Number(entry.public) || 0,
      private: Number(entry.private) || 0,
      byGroup: entry.byGroup || {},
      lat: entry.lat ?? null,
      lng: entry.lng ?? null,
    };
  });
  window.projectData = data;
  // Exposer aussi une version indexée par code localité (plus fiable que le libellé)
  try {
    const byCode = {};
    (projects || []).forEach((e) => { if (e && e.code) byCode[String(e.code)] = e; });
    window.projectDataByCode = byCode;
  } catch (_) {}
  return data;
}

function valueForLegend(regionName, metric = 'count', filter = 'cumul') {
  if (!window.projectData || !regionName) return 0;
  const stats = window.projectData[normalized(regionName)];
  if (!stats) return 0;

  const totalCount = (stats.public || 0) + (stats.private || 0);
  const sourceCount =
    filter === 'public' ? (stats.public || 0) :
    filter === 'private' ? (stats.private || 0) :
    totalCount;

  if (metric === 'count') return sourceCount;

  // metric === 'cost' : ratio basé sur public/private/count
  const ratio = totalCount ? (sourceCount / totalCount) : 0;
  return (stats.cost || 0) * ratio;
}

function valueForDisplay(regionName, metric = 'count', filter = 'cumul') {
  const raw = valueForLegend(regionName, metric, filter);
  if (metric === 'cost') return raw / 1_000_000_000; // affichage en G
  return raw;
}

function pickFillColor(value, legend = []) {
  if (!Array.isArray(legend) || legend.length === 0) return '#c7bda3';
  const found = legend.find(({ borneInf, borneSup }) => {
    if (borneInf !== null && borneInf !== undefined && (borneSup === null || borneSup === undefined)) {
      return value >= borneInf;
    }
    if (borneInf !== null && borneSup !== null && borneSup !== undefined) {
      return value >= borneInf && value <= borneSup;
    }
    return false;
  });
  return found ? found.couleur : '#ff0000';
}

// -----------------------------
// MAIN
// -----------------------------
function initAutresRequetesMap(countryAlpha3Code, codeZoom, groupesProjet, niveaux, options = {}) {
  // Ping (debug): permet de confirmer dans laravel.log que le JS tourne et que l’API est joignable
  // (le backend fait un Log::info dans legend())
  try {
    fetch(apiUrl(`autres-requetes/legend?metric=count&_=${Date.now()}`), { headers: { 'Accept': 'application/json' } })
      .catch(() => {});
  } catch (_) {}

  const map = L.map('countryMap', {
    zoomControl: true,
    center: options.center || [4.54, -3.55],
    zoom: codeZoom?.minZoom || 6,
    //maxZoom: codeZoom?.maxZoom || 12,
    minZoom: codeZoom?.minZoom || 6,
    dragging: true,
  });
  map.panBy([20, 0]);

  // state
  let currentLayers = {};
  let selectedLevels = {};
  let maxLevels = 3;
  let detectedNameKeys = {};
  let currentGroup = options.initialGroup || '';
  let currentFilters = {};
  let infraMarkersLayer = L.layerGroup().addTo(map);
  let groupMarkersLayer = L.layerGroup().addTo(map);

  const groupMetaByCode = (() => {
    const idx = {};
    (groupesProjet || []).forEach((g) => {
      const code = String(g.code || '').trim();
      if (!code) return;
      idx[code] = {
        code,
        libelle: g.libelle || code,
        icon: g.icon || '',
        icon_color: g.icon_color || '#111827',
      };
    });
    return idx;
  })();

  window.customLegend = Array.isArray(window.customLegend) ? window.customLegend : [];

  // -----------------------------
  // Info control (bulle)
  // -----------------------------
  const info = L.control({ position: 'topright' });
  info.onAdd = function () {
    this._div = L.DomUtil.create('div', 'info');
    this.update();
    return this._div;
  };

  info.update = function () {
    const levelRows = [];
    for (let i = 1; i <= maxLevels; i++) {
      const val = selectedLevels[i]?.name || '—';
      levelRows.push(
        `<tr><th style="text-align:right;">${labelForLevel(i, niveaux)}:</th><td>${val}</td></tr>`
      );
    }

    // Données sélectionnées par niveau (on mappe via le nom normalisé)
    const localityDataByLevel = (() => {
      const data = {};
      for (let l = 1; l <= maxLevels; l++) {
        const name = selectedLevels[l]?.name;
        if (!name) continue;
        const key = normalized(name);
        if (window.projectData?.[key]) data[l] = window.projectData[key];
      }
      return data;
    })();

    const totalValue = (() => {
      const last = Object.values(localityDataByLevel).pop();
      if (!last) return 0;
      if (window.currentMapMetric === 'cost') {
        const total = (last.public || 0) + (last.private || 0);
        if (window.currentMapFilter === 'private')
          return (total > 0 ? (last.cost || 0) * ((last.private || 0) / total) : 0) / 1_000_000_000;
        if (window.currentMapFilter === 'public')
          return (total > 0 ? (last.cost || 0) * ((last.public || 0) / total) : 0) / 1_000_000_000;
        return (last.cost || 0) / 1_000_000_000;
      }
      if (window.currentMapFilter === 'private') return last.private || 0;
      if (window.currentMapFilter === 'public') return last.public || 0;
      return (last.public || 0) + (last.private || 0);
    })();

    const groupRows = (groupesProjet || [])
      .map((g) => {
        const gcode = String(g.code || g.groupe_projet_id || '').trim();
        const labelText = (g.libelle || gcode || '').toString();
        if (!gcode) return '';

        const meta = groupMetaByCode[gcode] || { code: gcode, libelle: labelText, icon: '', icon_color: '#111827' };
        const label = `<span style="display:inline-flex;gap:6px;align-items:center;">${iconHtml(meta)}<span>${escapeHtml(labelText)}</span></span>`;

        // prendre uniquement le niveau le plus bas sélectionné
        const levels = Object.keys(localityDataByLevel).map(Number).sort((a, b) => a - b);
        const deepest = levels.length ? levels[levels.length - 1] : null;
        const stats = deepest ? (localityDataByLevel[deepest]?.byGroup?.[gcode] || null) : null;

        const totalCell = (() => {
          if (!stats) return window.currentMapMetric === 'cost' ? '0.00' : '0';
          if (window.currentMapMetric === 'cost') {
            const tot = (stats.public || 0) + (stats.private || 0);
            let sum = 0;
            if (window.currentMapFilter === 'private') sum = tot > 0 ? (stats.cost || 0) * ((stats.private || 0) / tot) : 0;
            else if (window.currentMapFilter === 'public') sum = tot > 0 ? (stats.cost || 0) * ((stats.public || 0) / tot) : 0;
            else sum = (stats.cost || 0);
            return (sum / 1_000_000_000).toFixed(2);
          }
          if (window.currentMapFilter === 'private') return String(stats.private || 0);
          if (window.currentMapFilter === 'public') return String(stats.public || 0);
          return String((stats.public || 0) + (stats.private || 0));
        })();

        const perLevelCells = Array.from({ length: maxLevels }, (_, idx) => {
          const levelData = localityDataByLevel[idx + 1];
          const s = levelData?.byGroup?.[gcode] || {};

          if (window.currentMapMetric === 'cost') {
            const tot = (s.public || 0) + (s.private || 0);
            const pubCost = s.cost && tot > 0 ? s.cost * (s.public / tot) : 0;
            const privCost = s.cost && tot > 0 ? s.cost * (s.private / tot) : 0;
            return `
              <td style="border:1px solid #000; text-align:center;">${window.currentMapFilter === 'private' ? '-' : (pubCost / 1_000_000_000).toFixed(2)}</td>
              <td style="border:1px solid #000; text-align:center;">${window.currentMapFilter === 'public' ? '-' : (privCost / 1_000_000_000).toFixed(2)}</td>`;
          }
          return `
            <td style="border:1px solid #000; text-align:center;">${window.currentMapFilter === 'private' ? '-' : (s.public ?? 0)}</td>
            <td style="border:1px solid #000; text-align:center;">${window.currentMapFilter === 'public' ? '-' : (s.private ?? 0)}</td>`;
        }).join('');

        return `
          <tr>
            <th style="border:1px solid #000; text-align:right;">${label}</th>
            <td style="border:1px solid #000; text-align:center;">${totalCell}</td>
            ${perLevelCells}
          </tr>`;
      })
      .filter(Boolean)
      .join('');

    const headerCols = Array.from({ length: maxLevels }, (_, i) => {
      const label = labelForLevel(i + 1, niveaux);
      return `<th colspan="2" style="border:1px solid #000; text-align:center;">${label}</th>`;
    }).join('');

    this._div.innerHTML = `
      <div class="title">Informations sur la zone</div>
      <table class="level-info">${levelRows.join('')}</table>
      <table class="project-info">
        <thead>
          <tr>
            <th colspan="${2 + maxLevels * 2}" style="text-align:center;">Répartition des infrastructures bénéficiaires</th>
          </tr>
          <tr>
            <th rowspan="2" style="border:1px solid #000; text-align:center;">Groupes projet</th>
            <th rowspan="2" style="border:1px solid #000; text-align:center;">Total</th>
            <th colspan="${maxLevels * 2}" style="border:1px solid #000; text-align:center;">Répartition par niveau</th>
          </tr>
          <tr>${headerCols}</tr>
          <tr>
            <th></th><th></th>
            ${Array.from({ length: maxLevels }, () => `
              <th style="border:1px solid #000; text-align:center;">Public</th>
              <th style="border:1px solid #000; text-align:center;">Privé</th>
            `).join('')}
          </tr>
        </thead>
        <tbody>
          ${groupRows || `<tr><td colspan="${2 + maxLevels * 2}" class="text-center">Aucune donnée</td></tr>`}
          <tr>
            <th style="border:1px solid #000; text-align:right;">Total</th>
            <td style="border:1px solid #000; text-align:center;">${
              window.currentMapMetric === 'cost'
                ? Number(totalValue).toFixed(2)
                : formatWithSpaces(totalValue)
            }</td>
            ${Array.from({ length: maxLevels }, (_, idx) => {
              const levelData = localityDataByLevel[idx + 1] || {};
              if (window.currentMapMetric === 'cost') {
                const tot = (levelData.public || 0) + (levelData.private || 0);
                const pubCost = levelData.cost && tot > 0 ? levelData.cost * (levelData.public / tot) : 0;
                const privCost = levelData.cost && tot > 0 ? levelData.cost * (levelData.private / tot) : 0;
                return `
                  <td style="border:1px solid #000; text-align:center;">${window.currentMapFilter === 'private' ? '-' : (pubCost / 1_000_000_000).toFixed(2)}</td>
                  <td style="border:1px solid #000; text-align:center;">${window.currentMapFilter === 'public' ? '-' : (privCost / 1_000_000_000).toFixed(2)}</td>
                `;
              }
              return `
                <td style="border:1px solid #000; text-align:center;">${window.currentMapFilter === 'private' ? '-' : (levelData.public ?? 0)}</td>
                <td style="border:1px solid #000; text-align:center;">${window.currentMapFilter === 'public' ? '-' : (levelData.private ?? 0)}</td>
              `;
            }).join('')}
          </tr>
        </tbody>
      </table>
    `;
  };
  info.addTo(map);

  // -----------------------------
  // Legend
  // -----------------------------
  function createLegend() {
    const metric = window.currentMapMetric || 'count';
    const url = apiUrl(`autres-requetes/legend?metric=${encodeURIComponent(metric)}`);
    fetch(url)
      .then((r) => r.json())
      .then((data) => {
        // Données BD (pour la carte) : légende
        window.__AUTRES_REQUETES_LEGEND__ = data;
        console.log('[BD->carte] LEGEND (autresRequetes):', data);
        window.customLegend = data.seuils || [];
        if (window.currentLegendControl) map.removeControl(window.currentLegendControl);
        const legend = L.control({ position: 'bottomright' });
        legend.onAdd = function () {
          const div = L.DomUtil.create('div', 'info legend');
          const labels = [`<h4>LÉGENDE</h4><p>${data.label || ''}</p>`];
          (data.seuils || []).forEach(({ borneInf, borneSup, couleur }) => {
            labels.push(
              `<i style="background:${couleur}; opacity:0.7;"></i> ${borneInf}${
                (borneSup || borneSup === 0) ? `–${borneSup}` : '+'
              }`
            );
          });
          div.innerHTML = labels.join('<br>');
          return div;
        };
        legend.addTo(map);
        window.currentLegendControl = legend;
      })
      .catch((err) => console.error('[autresRequetes] Erreur chargement légende:', err));
  }

  // -----------------------------
  // GeoJSON layers
  // -----------------------------
  function getFeatureStyle(feature, level) {
    const nameKey = detectedNameKeys[level];
    const regionName = feature.properties?.[nameKey];
    const value = valueForLegend(regionName, window.currentMapMetric, window.currentMapFilter);
    const fillColor = pickFillColor(value, window.customLegend);
    return { weight: 1, opacity: 1, color: 'white', fillOpacity: 0.7, fillColor };
  }

  function highlightFeature(e) {
    const layer = e.target;
    layer.setStyle({ weight: 4, color: '#222', fillOpacity: 0.95 });
    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) layer.bringToFront();
  }

  function resetHighlight(e) {
    const layer = e.target;
    const lvl = layer.feature.properties.__level;
    const base = currentLayers[lvl];
    if (base) base.resetStyle(layer);
  }

  function onFeatureClick(e, level) {
    const lyr = e.target;
    const nameKey = detectedNameKeys[level];
    const featureName = lyr.feature.properties?.[nameKey];

    // mémoriser la sélection
    selectedLevels[level] = { name: featureName };
    for (let l = level + 1; l <= maxLevels; l++) delete selectedLevels[l];
    info.update();

    // charger niveau suivant ou marqueurs
    if (level < maxLevels) {
      clearLayersAbove(level + 1);
      loadGeoJsonLevel(level + 1, featureName);
    } else {
      // niveau 3: charger les infrastructures bénéficiaires en points (optionnel)
      const codePrefix = (() => {
        // on tente d'obtenir un code via GID_*, sinon on n'affiche pas les points
        const gid = lyr.feature?.properties?.[`GID_${level}`];
        if (!gid) return null;
        const m = String(gid).match(/(\d{2,6})$/);
        return m ? m[1] : null;
      })();
      if (codePrefix) {
        loadMarkers(codePrefix);
      } else {
        infraMarkersLayer.clearLayers();
      }
    }

    // Reposer les marqueurs de groupes sur le niveau courant
    renderGroupMarkers();
  }

  function onEachFeature(feature, layer, level) {
    const props = feature.properties || {};
    feature.properties.__level = level;

    const nameKey = detectedNameKeys[level];
    const regionName = props?.[nameKey];
    const val = valueForDisplay(regionName, window.currentMapMetric, window.currentMapFilter);
    const valueTxt = window.currentMapMetric === 'cost' ? `${Number(val).toFixed(2)} G` : `${val}`;

    layer.on({
      click: (e) => onFeatureClick(e, level),
      mouseover: highlightFeature,
      mouseout: resetHighlight,
    });

    layer.bindTooltip(
      `<b>${regionName || '—'}</b><br>${window.currentMapMetric === 'count' ? 'Infrastructures' : 'Montant'}: ${valueTxt}`
    );
    layer.on('mouseover', () => {
      const el = layer.getElement?.();
      if (el) el.style.cursor = 'pointer';
    });
  }

  function clearLayersAbove(level) {
    for (let l = level; l <= maxLevels; l++) {
      if (currentLayers[l]) {
        map.removeLayer(currentLayers[l]);
        delete currentLayers[l];
      }
    }
  }

  function filterGeoJsonByParent(data, parentLevel, parentName) {
    const nameKey = detectedNameKeys[parentLevel];
    const target = normalized(parentName);
    return {
      ...data,
      features: (data.features || []).filter(
        (f) => normalized(f.properties?.[nameKey]) === target
      ),
    };
  }

  function createGeoJsonLayer(data, level, parentName) {
    if (!data || !data.features || !data.features.length) return;
    const sampleProps = data.features?.[0]?.properties || {};
    const nameKey = sampleProps[`NAME_${level}`] ? `NAME_${level}` : guessNameKey(sampleProps);
    detectedNameKeys[level] = nameKey || `NAME_${level}`;

    const filtered = parentName ? filterGeoJsonByParent(data, level - 1, parentName) : data;

    const layer = L.geoJSON(filtered, {
      style: (feat) => getFeatureStyle(feat, level),
      onEachFeature: (feature, lyr) => onEachFeature(feature, lyr, level),
    });
    layer.addTo(map);
    currentLayers[level] = layer;
    if (level === 1) map.fitBounds(layer.getBounds());
  }

  function loadGeoJsonLevel(level, parentName = null) {
    if (level > maxLevels) return Promise.resolve();
    const varName = `autresRequetesStatesDataLevel${level}_${countryAlpha3Code}`;
    const url = `${geojsonBaseUrl()}gadm41_${countryAlpha3Code}_${level}.json.js`;

    return new Promise((resolve, reject) => {
      if (window[varName]) return resolve(window[varName]);
      const script = document.createElement('script');
      script.src = url;
      script.async = true;
      script.onload = () => {
        // Les fichiers .json.js existants exposent généralement window.statesDataLevelX
        const fallbackName = `statesDataLevel${level}`;
        const data = window[varName] || window[fallbackName] || null;
        if (!data) return reject(new Error(`Variable GeoJSON introuvable (level ${level})`));
        resolve(data);
      };
      script.onerror = () => resolve(null);
      document.head.appendChild(script);
    }).then((data) => {
      if (!data) return;
      createGeoJsonLayer(data, level, parentName);
      renderGroupMarkers();
    }).catch((err) => console.error('[autresRequetes] GeoJSON load error:', err));
  }

  // -----------------------------
  // Markers (infrastructures bénéficiaires)
  // -----------------------------
  function loadMarkers(codePrefix) {
    const params = new URLSearchParams();
    if (codePrefix) params.set('code', codePrefix);
    if (currentGroup) params.set('groupe', currentGroup);
    const url = apiUrl(`autres-requetes/markers?${params.toString()}`);
    fetch(url)
      .then((r) => r.json())
      .then((data) => {
        // Données BD (pour la carte) : markers infras
        window.__AUTRES_REQUETES_MARKERS__ = data.markers || [];
        console.log('[BD->carte] MARKERS (autresRequetes):', window.__AUTRES_REQUETES_MARKERS__);
        infraMarkersLayer.clearLayers();
        (data.markers || []).forEach((m) => {
          const marker = L.circleMarker([m.lat, m.lng], {
            radius: 5,
            color: '#111827',
            weight: 1,
            fillColor: '#ef4444',
            fillOpacity: 0.9,
          });
          marker.bindPopup(
            `<b>${(m.label || '').toString().replace(/</g, '&lt;').replace(/>/g, '&gt;')}</b><br>Code: ${m.code || ''}<br>Groupe: ${m.groupe || ''}`
          );
          marker.addTo(infraMarkersLayer);
        });
      })
      .catch((err) => console.error('[autresRequetes] markers error:', err));
  }

  // -----------------------------
  // Group project markers (icônes + nombre) par zone/niveau
  // -----------------------------
  function getCurrentViewLevel() {
    const keys = Object.keys(currentLayers || {}).map((k) => parseInt(k, 10)).filter((n) => Number.isFinite(n));
    return keys.length ? Math.max(...keys) : 1;
  }

  function groupValue(stats) {
    if (!stats) return 0;
    const metric = window.currentMapMetric || 'count';
    const filter = window.currentMapFilter || 'cumul';

    if (metric === 'count') {
      if (filter === 'public') return Number(stats.public) || 0;
      if (filter === 'private') return Number(stats.private) || 0;
      return (Number(stats.public) || 0) + (Number(stats.private) || 0);
    }

    // metric === 'cost'
    const tot = (Number(stats.public) || 0) + (Number(stats.private) || 0);
    let sum = Number(stats.cost) || 0;
    if (filter === 'public') sum = tot > 0 ? sum * ((Number(stats.public) || 0) / tot) : 0;
    if (filter === 'private') sum = tot > 0 ? sum * ((Number(stats.private) || 0) / tot) : 0;
    // affichage en G
    return sum / 1_000_000_000;
  }

  function iconHtml(meta) {
    const icon = String(meta?.icon || '').trim();
    const color = String(meta?.icon_color || '#111827');

    // Emoji / caractère unique (ta table groupe_projet.icon)
    // Exemple: "🏢", "⚡", "💧", ...
    if (icon && !icon.includes('fa-') && !icon.includes('fas') && !icon.includes('far') && !icon.includes('fab') && !icon.includes('/') && icon.length <= 4) {
      return `<span style="font-size:16px;line-height:1;">${escapeHtml(icon)}</span>`;
    }

    // Si l'icône ressemble à une classe font-awesome (fa-xxx)
    if (icon.includes('fa-') || icon.includes('fas') || icon.includes('far') || icon.includes('fal') || icon.includes('fab')) {
      const classes = icon.includes('fa-') ? icon : `fa-${icon}`;
      // si pas de prefix, on met "fas" par défaut
      const finalClass = classes.includes('fa ') || classes.includes('fas ') || classes.includes('far ') || classes.includes('fab ')
        ? classes
        : `fas ${classes}`;
      return `<i class="${finalClass}" style="color:${color};"></i>`;
    }

    // Si c'est un chemin image
    if (/(\.png|\.svg|\.jpg|\.jpeg|\.webp)$/i.test(icon) || icon.startsWith('http') || icon.startsWith('/')) {
      return `<img src="${icon}" alt="" style="width:16px;height:16px;object-fit:contain;"/>`;
    }

    // fallback: code texte
    return `<span style="font-weight:800;font-size:10px;color:${color};">${(meta?.code || '').toString().slice(0,3)}</span>`;
  }

  function renderGroupMarkers() {
    groupMarkersLayer.clearLayers();

    const level = getCurrentViewLevel();
    const layer = currentLayers[level];
    if (!layer) return;

    const groupCodes = Object.keys(groupMetaByCode);
    if (!groupCodes.length) return;

    // Anti-chevauchement en PIXELS (beaucoup plus fiable que les degrés)
    const usedPoints = []; // points en pixels (layerPoint)
    const iconSizePx = 26;
    const minDistPx = Math.max(22, iconSizePx + 10); // distance mini entre centres

    function tooClosePt(pt) {
      for (const p of usedPoints) {
        const d = Math.sqrt(Math.pow(pt.x - p.x, 2) + Math.pow(pt.y - p.y, 2));
        if (d < minDistPx) return true;
      }
      return false;
    }

    function reserveNonOverlappingPt(basePt, angle, startRadiusPx, maxRadiusPx, isAllowedLatLng) {
      // spirale en pixels autour du point de base
      let r = startRadiusPx;
      let a = angle;
      for (let attempt = 0; attempt < 26; attempt++) {
        const pt = L.point(
          basePt.x + r * Math.cos(a),
          basePt.y + r * Math.sin(a)
        );
        if (!tooClosePt(pt)) {
          const ll = map.layerPointToLatLng(pt);
          if (isAllowedLatLng && !isAllowedLatLng(ll)) {
            // pas dans la zone -> continue
          } else {
            usedPoints.push(pt);
            return pt;
          }
        }
        r += 10;          // augmente le rayon
        a += Math.PI / 7; // tourne légèrement
        if (maxRadiusPx != null && r > maxRadiusPx) break;
      }
      const fallback = L.point(basePt.x + startRadiusPx * Math.cos(angle), basePt.y + startRadiusPx * Math.sin(angle));
      const ll = map.layerPointToLatLng(fallback);
      if (!isAllowedLatLng || isAllowedLatLng(ll)) {
        usedPoints.push(fallback);
        return fallback;
      }
      // ultime fallback: point de base
      usedPoints.push(basePt);
      return basePt;
    }

    let zonesRendered = 0;
    layer.eachLayer((lyr) => {
      const feat = lyr.feature;
      const nameKey = detectedNameKeys[level];
      const regionName = feat?.properties?.[nameKey];
      if (!regionName) return;

      // IMPORTANT: on se base sur le NAME_x affiché (cohérence)
      const statsZone = window.projectData?.[normalized(regionName)] || null;
      if (!statsZone) return;
      const geom = feat?.geometry || null;

      // baseLat/Lng: priorité aux coords retournées par l’API, sinon centre bounds du polygone
      let baseLat = Number(statsZone.lat);
      let baseLng = Number(statsZone.lng);
      if (!isFinite(baseLat) || !isFinite(baseLng)) {
        const b = lyr.getBounds?.();
        if (!b || !b.isValid?.()) return;
        const c = b.getCenter();
        baseLat = c.lat;
        baseLng = c.lng;
      }

      // Si le centroïde n’est pas dans la zone, on force le centre bounds
      if (geom && !pointInGeometry(baseLat, baseLng, geom)) {
        const b = lyr.getBounds?.();
        if (b && b.isValid?.()) {
          const c = b.getCenter();
          baseLat = c.lat;
          baseLng = c.lng;
        }
      }

      const basePt = map.latLngToLayerPoint([baseLat, baseLng]);

      // Rayon max en pixels basé sur la taille de la zone à ce zoom
      let maxRadiusPx = null;
      const b = lyr.getBounds?.();
      if (b && b.isValid?.()) {
        const nw = map.latLngToLayerPoint(b.getNorthWest());
        const se = map.latLngToLayerPoint(b.getSouthEast());
        const w = Math.abs(se.x - nw.x);
        const h = Math.abs(se.y - nw.y);
        maxRadiusPx = Math.max(10, Math.min(w, h) / 2 - 18);
      }

      const byGroup = statsZone.byGroup || {};
      const items = groupCodes
        .map((code) => ({ code, stats: byGroup[code] }))
        .map((it) => ({ ...it, value: groupValue(it.stats) }))
        .filter((it) => it.value && it.value > 0);
      if (!items.length) return;

      zonesRendered++;

      // À faible zoom, on limite pour éviter de saturer et faciliter la séparation;
      // à fort zoom, on peut montrer plus.
      const z = map.getZoom();
      const maxPerZone = z >= 10 ? 20 : z >= 8 ? 10 : 6;
      if (items.length > maxPerZone) items.length = maxPerZone;

      items.forEach((it, idx) => {
        const meta = groupMetaByCode[it.code] || { code: it.code, icon: '', icon_color: '#111827', libelle: it.code };
        const n = items.length;
        const angle = (idx / n) * Math.PI * 2;
        const pt = reserveNonOverlappingPt(
          basePt,
          angle,
          18,
          maxRadiusPx,
          (ll) => !geom || pointInGeometry(ll.lat, ll.lng, geom)
        );
        const ll = map.layerPointToLatLng(pt);
        const lat = ll.lat;
        const lng = ll.lng;

        const label = (meta.libelle || it.code || '').toString();
        const valueText = (window.currentMapMetric === 'cost')
          ? `${Number(it.value).toFixed(2)} G`
          : `${Math.round(Number(it.value))}`;

        const html = `
          <div style="
            position:relative;
            width:26px;height:26px;
            border-radius:999px;
            background:rgba(255,255,255,0.92);
            border:2px solid ${meta.icon_color || '#111827'};
            display:flex;align-items:center;justify-content:center;
            box-shadow:0 2px 8px rgba(0,0,0,.22);
          ">
            ${iconHtml(meta)}
            <div style="
              position:absolute;right:-6px;top:-6px;
              min-width:18px;height:18px;
              padding:0 4px;
              border-radius:999px;
              background:${meta.icon_color || '#111827'};
              color:#fff;
              font-size:11px;
              font-weight:800;
              display:flex;align-items:center;justify-content:center;
              border:2px solid #fff;
            ">${valueText}</div>
          </div>
        `;

        const marker = L.marker([lat, lng], {
          interactive: true,
          icon: L.divIcon({
            className: 'gp-group-marker',
            html,
            iconSize: [26, 26],
            iconAnchor: [13, 13],
          }),
        });

        marker.bindTooltip(`<b>${label}</b><br>${valueText}`, { direction: 'top', opacity: 0.95 });
        marker.addTo(groupMarkersLayer);
      });
    });

    // Debug léger
    // console.log('[autresRequetes] renderGroupMarkers', { level, zonesRendered });
  }

  map.on('zoomend', () => {
    renderGroupMarkers();
  });

  // -----------------------------
  // Reload
  // -----------------------------
  function reloadStylesAndInfo() {
    for (let l = 1; l <= maxLevels; l++) {
      if (currentLayers[l]) {
        currentLayers[l].eachLayer((lyr) => {
          const feat = lyr.feature;
          const newStyle = getFeatureStyle(feat, l);
          lyr.setStyle(newStyle);
        });
      }
    }
    createLegend();
    info.update();
    renderGroupMarkers();
  }

  window.reloadAutresRequetesMap = reloadStylesAndInfo;

  window.setAutresRequetesGroup = function (groupCode) {
    currentGroup = groupCode || '';
    infraMarkersLayer.clearLayers();
    groupMarkersLayer.clearLayers();
    loadAggregateData().then(() => reloadStylesAndInfo());
  };

  window.applyAutresRequetesFilters = function (filters) {
    currentFilters = { ...(filters || {}) };
    infraMarkersLayer.clearLayers();
    groupMarkersLayer.clearLayers();
    loadAggregateData().then(() => reloadStylesAndInfo());
  };

  function loadAggregateData() {
    const params = new URLSearchParams();
    if (currentGroup) params.set('groupe', currentGroup);
    const f = currentFilters || {};
    const dateType = f.date_type || '';
    const start = f.start_date || '';
    const end = f.end_date || '';
    if (dateType && dateType !== 'Tous') params.set('date_type', dateType);
    if (dateType !== 'Tous' && start) params.set('start_date', start);
    if (dateType !== 'Tous' && end) params.set('end_date', end);
    if (f.status) params.set('status', f.status);
    if (f.bailleur) params.set('bailleur', f.bailleur);
    const url = apiUrl(`autres-requetes/aggregate?${params.toString()}`);
    return fetch(url, { headers: { 'Accept': 'application/json' } })
      .then((r) => r.json())
      .then((payload) => {
        // Données BD (pour la carte) : agrégat
        window.__AUTRES_REQUETES_DB__ = payload?.projets || [];
        console.log('[BD->carte] AGGREGATE (autresRequetes) payload.projets:', window.__AUTRES_REQUETES_DB__);
        return processAggregateData(payload);
      });
  }

  // -----------------------------
  // Bootstrap
  // -----------------------------
  detectAvailableLevels(countryAlpha3Code)
    .then((l) => { maxLevels = Math.max(1, l || 1); })
    .catch(() => { maxLevels = 1; })
    .finally(() => {
      loadAggregateData()
        .then(() => {
          createLegend();
          return loadGeoJsonLevel(1);
        })
        .then(() => info.update())
        .catch((err) => console.error('[autresRequetes] bootstrap error:', err));
    });
}

// Export
window.initAutresRequetesMap = initAutresRequetesMap;

