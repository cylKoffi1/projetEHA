'use strict';

// =====================================================
// map.js — fully parameterized, no country-specific constants
// - Auto-detects available admin levels (1/2/3) from GeoJSON files
// - Infers property keys (NAME_x / TYPE_x) from the data at runtime
// - UI labels come from backend `niveaux` when available, else fall back to `Niveau i`
// - All URLs & behaviors can be overridden via `options`
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
    // suppression des accents
    .replace(/[\u0300-\u036f]/g, '')
    // espaces multiples → simple espace
    .replace(/\s+/g, ' ')
    // préfixes administratifs fréquents
    .replace(/^region\s+(d[eu']\s+)?/i, '')
    .replace(/^région\s+(d[eu']\s+)?/i, '')
    .replace(/^province\s+(d[eu']\s+)?/i, '')
    .replace(/^departement\s+(d[eu']\s+)?/i, '')
    .replace(/^département\s+(d[eu']\s+)?/i, '')
    .replace(/^district\s+(d[eu']\s+)?/i, '')   // 🔁 ajout pour aligner avec LocalitesPays
    .replace(/^district\s+/i, '')
    .trim();
};

function formatWithSpaces(number) {
  const n = Number(number);
  if (!isFinite(n)) return '0';
  return n.toLocaleString('fr-FR');
}

function apiUrl(path = '') {
  const base = (window.APP && window.APP.API_URL);
  return base.replace(/\/+$/, '') + '/' + path.replace(/^\/+/, '');
}

function geojsonBaseUrl() {
  const base = (window.APP && window.APP.GEOJSON);
  return base.replace(/\/+$/, '') + '/';
}

// -----------------------------
// URL helpers (overridable)
// -----------------------------
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

async function detectAvailableLevels(countryAlpha3Code, options) {
  const base = (options?.geojsonBaseUrl || geojsonBaseUrl()) + `gadm41_${countryAlpha3Code}_`;
  const ok1 = await urlExists(`${base}1.json.js`);
  const ok2 = ok1 ? await urlExists(`${base}2.json.js`) : false;
  const ok3 = ok2 ? await urlExists(`${base}3.json.js`) : false;
  if (ok1 && ok2 && ok3) return 3;
  if (ok1 && ok2) return 2;
  if (ok1) return 1;
  return 0;
}



// Minimum entre niveaux GeoJSON et niveaux disponibles en base
async function detectMaxLevels(countryAlpha3Code, niveauxBackend, options) {
  const geoLevels = await detectAvailableLevels(countryAlpha3Code, options);
  const dataLevels = Array.isArray(niveauxBackend)
    ? Math.max(1, Math.min(3, niveauxBackend.length))
    : null;
  return dataLevels ? Math.min(geoLevels, dataLevels) : geoLevels;
}

// -----------------------------
// Property key inference
// -----------------------------
function guessNameKey(props) {
  if (!props) return null;
  const keys = Object.keys(props);
  let cand = keys.find((k) => /^(name(_?\d+)?)$/i.test(k));
  if (cand) return cand;
  cand = keys.find((k) => /(adm\d+_?name)$/i.test(k));
  if (cand) return cand;
  cand = keys.find((k) => /name/i.test(k));
  return cand || null;
}

function guessTypeKey(props) {
  if (!props) return null;
  const keys = Object.keys(props);
  let cand = keys.find((k) => /^type(_?\d+)?$/i.test(k));
  if (cand) return cand;
  cand = keys.find((k) => /level|status/i.test(k));
  return cand || null; // may be null
}

function getNameKeyForLevel(level, sampleProps, options) {
  if (options?.nameKey) {
    const key =
      typeof options.nameKey === 'function'
        ? options.nameKey(level)
        : options.nameKey;
    if (key && sampleProps && key in sampleProps) return key;
  }
  const pattern = `NAME_${level}`;
  if (sampleProps && pattern in sampleProps) return pattern;
  return guessNameKey(sampleProps);
}

function getTypeKeyForLevel(level, sampleProps, options) {
  if (options?.typeKey) {
    const key =
      typeof options.typeKey === 'function'
        ? options.typeKey(level)
        : options.typeKey;
    if (key && sampleProps && key in sampleProps) return key;
  }
  const pattern = `TYPE_${level}`;
  if (sampleProps && pattern in sampleProps) return pattern;
  return guessTypeKey(sampleProps);
}

function labelForLevel(level, niveauxBackend, options, typeKey, featureProps) {
  if (Array.isArray(options?.levelLabels) && options.levelLabels[level - 1])
    return options.levelLabels[level - 1];
  const backendLabel = niveauxBackend?.[level - 1]?.libelle_decoupage;
  if (backendLabel) return backendLabel;
  if (typeKey && featureProps && featureProps[typeKey])
    return String(featureProps[typeKey]);
  return `Niveau ${level}`;
}

// -----------------------------
// MAIN
// -----------------------------
function initCountryMap(
  countryAlpha3Code,
  codeZoom,
  codeGroupeProjet,
  domainesAssocie,
  niveaux,
  options = {}
) {
  const map = L.map('countryMap', {
    zoomControl: true,
    center: options.center || [4.54, -3.55],
    zoom: codeZoom.minZoom,
    maxZoom: codeZoom.maxZoom,
    minZoom: codeZoom.minZoom,
    dragging: true,
  });
  map.panBy([20, 0]);
  
  // Exposer la carte dans window pour accès externe
  window.mapInstance = map;

  // State
  let currentLayers = {};
  let selectedLevels = {};
  let maxLevels = 3;
  let infrastructureMarkersLayer = L.layerGroup().addTo(map);
  let currentInfrastructureLevel = 0;

  window.currentMapMetric = window.currentMapMetric || 'count'; // 'count' | 'cost'
  window.currentMapFilter = window.currentMapFilter || 'cumul'; // 'cumul' | 'public' | 'private'
  window.customLegend = Array.isArray(window.customLegend) ? window.customLegend : [];
  window.currentLegendControl = window.currentLegendControl || null;
  window.projectData = window.projectData || {};
  window.codeGroupeProjet = codeGroupeProjet;

  const detectedKeys = { 1: {}, 2: {}, 3: {} };

  // -----------------------------
  // Info control
  // -----------------------------
  const info = L.control({ position: 'topright' });
  info.onAdd = function () {
    this._div = L.DomUtil.create('div', 'info');
    this.update(domainesAssocie, niveaux);
    return this._div;
  };

  info.update = function (domaines = [], niveauxBackend = []) {
    const levelRows = [];
    for (let i = 1; i <= maxLevels; i++) {
      const val =
        typeof selectedLevels[i] === 'object' ? selectedLevels[i]?.name : selectedLevels[i];
      const sampleProps = detectedKeys[i]?.sampleProps || null;
      const typeKey = detectedKeys[i]?.typeKey || null;
      const label = labelForLevel(i, niveauxBackend, options, typeKey, sampleProps);
      levelRows.push(
        `<tr><th style="text-align:right;">${label}:</th><td>${val || '—'}</td></tr>`
      );
    }

    const localityDataByLevel = (() => {
      const data = {};
      for (let l = 1; l <= maxLevels; l++) {
        const entry = selectedLevels[l];
        const name = typeof entry === 'object' ? entry?.name : entry;
        if (!name) continue;
        const key = normalized(name);
        if (window.projectData[key]) data[l] = window.projectData[key];
      }
      return data;
    })();

    const totalProjects = (() => {
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
      return last.count || 0;
    })();

    const domainRows = (domaines || [])
      .map((domaine) => {
        const domainCode = String(domaine.code || '').substring(0, 2);

        const totalCell = (() => {
            // Prendre uniquement le niveau le plus bas sélectionné
            const levels = Object.keys(localityDataByLevel).map(Number).sort((a,b) => a - b);
            const deepest = levels.length ? levels[levels.length - 1] : null;
            if (!deepest) return window.currentMapMetric === 'cost' ? '0.00' : 0;
          
            const stats = localityDataByLevel[deepest]?.byDomain?.[domainCode];
            if (!stats) return window.currentMapMetric === 'cost' ? '0.00' : 0;
          
            if (window.currentMapMetric === 'cost') {
              const tot = (stats.public || 0) + (stats.private || 0);
              let sum = 0;
              if (window.currentMapFilter === 'private') sum = tot > 0 ? (stats.cost || 0) * ((stats.private || 0) / tot) : 0;
              else if (window.currentMapFilter === 'public') sum = tot > 0 ? (stats.cost || 0) * ((stats.public || 0) / tot) : 0;
              else sum = (stats.cost || 0);
              return (sum / 1_000_000_000).toFixed(2);
            } else {
              if (window.currentMapFilter === 'private') return (stats.private || 0);
              if (window.currentMapFilter === 'public')  return (stats.public  || 0);
              return (stats.public || 0) + (stats.private || 0);
            }
          })();
          

        const perLevelCells = Array.from({ length: maxLevels }, (_, idx) => {
          const levelData = localityDataByLevel[idx + 1];
          const stats = levelData?.byDomain?.[domainCode] || {};
          if (window.currentMapMetric === 'cost') {
            const tot = (stats.public || 0) + (stats.private || 0);
            const pubCost = stats.cost && tot > 0 ? stats.cost * (stats.public / tot) : 0;
            const privCost = stats.cost && tot > 0 ? stats.cost * (stats.private / tot) : 0;
            return `
              <td style="border:1px solid #000; text-align:center;">
                <a href="#"  class="project-cell" data-code="${levelData?.code || ''}" data-level="${
              idx + 1
            }" data-filter="public" data-domain="${domainCode}">${
              window.currentMapFilter === 'private' ? '-' : (pubCost / 1_000_000_000).toFixed(2)
            }</a>
              </td>
              <td style="border:1px solid #000; text-align:center;">
                <a href="#"  class="project-cell" data-code="${levelData?.code || ''}" data-level="${
              idx + 1
            }" data-filter="private" data-domain="${domainCode}">${
              window.currentMapFilter === 'public' ? '-' : (privCost / 1_000_000_000).toFixed(2)
            }</a>
              </td>`;
          }
          return `
            <td style="border:1px solid #000; text-align:center;">
              <a href="#"  class="project-cell" data-code="${levelData?.code || ''}" data-level="${
            idx + 1
          }" data-filter="public" data-domain="${domainCode}">${
            window.currentMapFilter === 'private' ? '-' : stats.public ?? 0
          }</a>
            </td>
            <td style="border:1px solid #000; text-align:center;">
              <a href="#"  class="project-cell" data-code="${levelData?.code || ''}" data-level="${
            idx + 1
          }" data-filter="private" data-domain="${domainCode}">${
            window.currentMapFilter === 'public' ? '-' : stats.private ?? 0
          }</a>
            </td>`;
        }).join('');

        return `
          <tr>
            <th style="border:1px solid #000; text-align:right;">${domaine.libelle}</th>
            <td style="border:1px solid #000; text-align:center;">${totalCell}</td>
            ${perLevelCells}
          </tr>`;
      })
      .join('');

    const headerCols = Array.from({ length: maxLevels }, (_, i) => {
      const sampleProps = detectedKeys[i + 1]?.sampleProps || null;
      const typeKey = detectedKeys[i + 1]?.typeKey || null;
      const label = labelForLevel(i + 1, niveauxBackend, options, typeKey, sampleProps);
      return `<th colspan="2" style="border:1px solid #000; text-align:center;">${label}</th>`;
    }).join('');

    this._div.innerHTML = `
      <div class="title">Informations sur la zone</div>
      <table class="level-info">${levelRows.join('')}</table>
      <table class="project-info">
        <thead>
          <tr>
            <th colspan="${2 + maxLevels * 2}" style="text-align:center;">Répartition des projets</th>
          </tr>
          <tr>
            <th rowspan="2" style="border:1px solid #000; text-align:center;">Domaines</th>
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
          ${domainRows}
          ${(() => {
            const totalRowCells = Array.from({ length: maxLevels }, (_, idx) => {
              const levelData = localityDataByLevel[idx + 1] || {};
              if (window.currentMapMetric === 'cost') {
                const tot = (levelData.public || 0) + (levelData.private || 0);
                const pubCost = levelData.cost && tot > 0 ? levelData.cost * (levelData.public / tot) : 0;
                const privCost = levelData.cost && tot > 0 ? levelData.cost * (levelData.private / tot) : 0;
                return `
                  <td style="border:1px solid #000; text-align:center;"><a href="#"  class="project-cell" data-code="${
                    levelData.code || ''
                  }" data-level="${idx + 1}" data-filter="public">${
                  window.currentMapFilter === 'private' ? '-' : (pubCost / 1_000_000_000).toFixed(2)
                }</a></td>
                  <td style="border:1px solid #000; text-align:center;"><a href="#"  class="project-cell" data-code="${
                    levelData.code || ''
                  }" data-level="${idx + 1}" data-filter="private">${
                  window.currentMapFilter === 'public' ? '-' : (privCost / 1_000_000_000).toFixed(2)
                }</a></td>
                `;
              }
              return `
                <td style="border:1px solid #000; text-align:center;"><a href="#"  class="project-cell" data-code="${
                  levelData.code || ''
                }" data-level="${idx + 1}" data-filter="public">${
                window.currentMapFilter === 'private' ? '-' : (levelData.public ?? 0)
              }</a></td>
                <td style="border:1px solid #000; text-align:center;"><a href="#"  class="project-cell" data-code="${
                  levelData.code || ''
                }" data-level="${idx + 1}" data-filter="private">${
                window.currentMapFilter === 'public' ? '-' : (levelData.private ?? 0)
              }</a></td>
              `;
            }).join('');

            return `
              <tr>
                <th style="border:1px solid #000; text-align:right;">Total</th>
                <td style="border:1px solid #000; text-align:center;">${
                  window.currentMapMetric === 'cost'
                    ? Number(totalProjects).toFixed(2)
                    : formatWithSpaces(totalProjects)
                }</td>
                ${totalRowCells}
              </tr>`;
          })()}
        </tbody>
      </table>
    `;

    this._div.querySelectorAll('.project-cell').forEach((el) => {
      el.style.cursor = 'pointer';
      el.addEventListener('click', () => {
        const code = el.dataset.code;
        const domain = el.dataset.domain || '';
        const filter = el.dataset.filter || 'cumul';
        const level = parseInt(el.dataset.level || '0', 10) || undefined;
        const valueText = (el.textContent || '').trim();
        if (!code || valueText === '-' || valueText === '—') return;
        if (typeof window.openProjectDrawer === 'function') {
          window.openProjectDrawer({ code, domain, filter, level });
        }
      });
    });
  };
  info.addTo(map);

  // -----------------------------
  // Legend (from backend)
  // -----------------------------
  function createDynamicLegend(map, groupeCode) {
    const metric = window.currentMapMetric || 'count';
    const typeFin = metric === 'cost' ? 2 : 1;
  
    const legendUrl = options.getLegendUrl
      ? options.getLegendUrl(groupeCode, typeFin)
      : apiUrl(`legende/${encodeURIComponent(groupeCode)}?typeFin=${typeFin}`);
  
    fetch(encodeURI(legendUrl))
      .then((r) => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
      })
      .then((data) => {
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
      .catch((err) => console.error('Erreur chargement légende dynamique :', err));
  }
  

  createDynamicLegend(map, codeGroupeProjet);

  // -----------------------------
  // Project data
  // -----------------------------
  function loadProjectData(countryCode, groupCode) {
    const url = options.getProjectsUrl
      ? options.getProjectsUrl(countryCode, groupCode)
      : apiUrl(`projects?country=${encodeURIComponent(countryCode)}&group=${encodeURIComponent(groupCode)}`);
  
    return fetch(url, { headers: { 'Accept': 'application/json' } })
      .then((r) => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
      });
  }
  

  function processProjectData(projects) {
    const data = {};
    (projects || []).forEach((project) => {
      const key = normalized(project.name);
      data[key] = {
        name: project.name,
        code: project.code,
        count: Number(project.count) || 0,
        cost: Number(project.cost) || 0,
        public: Number(project.public) || 0,
        private: Number(project.private) || 0,
        byDomain: project.byDomain || {},
      };
    });
    // Debug: vérifier la structure des données agrégées pour la carte admin/carte
    try {
      console.log('[sigAdmin] processProjectData → projets:', {
        inputCount: (projects || []).length,
        keysSample: Object.keys(data).slice(0, 10),
        sample: Object.values(data).slice(0, 2),
      });
    } catch (e) {
      // pas bloquant pour la prod
    }
    window.projectData = data;
    return data;
  }
  window.processProjectData = processProjectData;

  // -----------------------------
  // GeoJSON layers
  // -----------------------------
  function filterGeoJsonByParent(data, parentLevel, parentName) {
    const nameKey = detectedKeys[parentLevel]?.nameKey;
    const target = normalized(parentName);
    return {
      ...data,
      features: (data.features || []).filter(
        (f) => normalized(f.properties?.[nameKey]) === target
      ),
    };
  }

  function getFeatureStyle(feature, level) {
    const nameKey = detectedKeys[level]?.nameKey;
    const regionName = feature.properties?.[nameKey];
    const value = valueForLegend(regionName, window.currentMapMetric, window.currentMapFilter);
    let fillColor = '#c7bda3';

    if (value > 0 && Array.isArray(window.customLegend) && window.customLegend.length > 0) {
      const found = window.customLegend.find(({ borneInf, borneSup }) => {
        if (
          borneInf !== null &&
          borneInf !== undefined &&
          (borneSup === null || borneSup === undefined)
        )
          return value >= borneInf;
        if (borneInf !== null && borneSup !== null && borneSup !== undefined)
          return value >= borneInf && value <= borneSup;
        return false;
      });
      fillColor = found ? found.couleur : '#ff0000';
    }

    // Si les couches transparentes sont activées, réduire l'opacité du remplissage
    // mais garder les bordures visibles (seulement si la variable est définie)
    const fillOpacity = (window.transparentLayersEnabled === true) ? 0.1 : 0.7;
    const borderOpacity = 1; // Toujours garder les bordures visibles
    const borderWeight = (window.transparentLayersEnabled === true) ? 2 : 1; // Bordures plus épaisses si transparent

    return { 
      weight: borderWeight, 
      opacity: borderOpacity, 
      color: 'white', 
      fillOpacity: fillOpacity, 
      fillColor 
    };
  }

  function onEachFeature(feature, layer, level) {
    feature.properties.level = level;
    const nameKey = detectedKeys[level]?.nameKey;
    const typeKey = detectedKeys[level]?.typeKey;
    const regionName = feature.properties?.[nameKey];

    const statValue = valueForDisplay(
      regionName,
      window.currentMapMetric,
      window.currentMapFilter
    );
    const valueTxt = window.currentMapMetric === 'cost' ? `${Number(statValue).toFixed(2)} M` : `${statValue}`;

    layer.on({
      click: (e) => onFeatureClick(e, level),
      mouseover: highlightFeature,
      mouseout: resetHighlight,
    });

    // curseur + tooltip
    layer.bindTooltip(
      `<b>${regionName}</b><br>${window.currentMapMetric === 'count' ? 'Projets' : 'Montant'}: ${valueTxt}`
    );
    layer.on('mouseover', () => {
      const el = layer.getElement?.();
      if (el) el.style.cursor = 'pointer';
    });
  }
  function valueForLegend(regionName, metric = 'count', filter = 'cumul') {
    if (!window.projectData || !regionName) return 0;
    const stats = window.projectData[normalized(regionName)];
    if (!stats) {
      // Debug léger : loguer de temps en temps les cas sans correspondance
      if (Math.random() < 0.02) {
        try {
          console.log('[sigAdmin] valueForLegend: aucune stats pour la région', {
            regionName,
            normalizedName: normalized(regionName),
            availableKeys: Object.keys(window.projectData || {}).slice(0, 10),
          });
        } catch (e) {}
      }
      return 0;
    }
  
    // source = nombre de projets (public/privé/cumul) -> pour ratio éventuel
    const totalCount = (stats.public || 0) + (stats.private || 0);
    const sourceCount =
      filter === 'public' ? (stats.public || 0) :
      filter === 'private' ? (stats.private || 0) :
      totalCount;
  
    if (metric === 'count') return sourceCount;
  
    // metric === 'cost' : on applique le même ratio que pour l'affichage,
    // mais on NE DIVISE PAS par 1e9 pour rester dans l'unité brute des seuils
    const ratio = totalCount ? (sourceCount / totalCount) : 0;
    return (stats.cost || 0) * ratio; // << unité brute (ex: FCFA / EUR selon tes seuils)
  }
  
  function valueForDisplay(regionName, metric = 'count', filter = 'cumul') {
    const raw = valueForLegend(regionName, metric, filter);
    // Pour l’affichage seulement : si coût, montrer en milliards avec 2 décimales
    if (metric === 'cost') return raw / 1_000_000_000;
    return raw;
  }
  
  function createGeoJsonLayer(data, level) {
    if (!data || !data.features || !data.features.length) return;

    // Infer keys for this level using the first feature
    const sampleProps = data.features?.[0]?.properties || {};
    const nameKey = getNameKeyForLevel(level, sampleProps, options) || guessNameKey(sampleProps);
    const typeKey = getTypeKeyForLevel(level, sampleProps, options);
    detectedKeys[level] = { nameKey, typeKey, sampleProps };

    const layer = L.geoJSON(data, {
      style: (feat) => getFeatureStyle(feat, level),
      onEachFeature: (feature, lyr) => onEachFeature(feature, lyr, level),
    });
    layer.addTo(map);
    currentLayers[level] = layer;
    if (level === 1) map.fitBounds(layer.getBounds());
  }

  function clearLayersAbove(level) {
    for (let l = level; l <= maxLevels; l++) {
      if (currentLayers[l]) {
        map.removeLayer(currentLayers[l]);
        delete currentLayers[l];
      }
    }
  }

  function loadGeoJsonLevel(level, parentName = null) {
    if (level > maxLevels) return Promise.resolve();
  
    const base = (options.geojsonBaseUrl || geojsonBaseUrl());
    const varName = `statesDataLevel${level}`;
    const url = `${base}gadm41_${countryAlpha3Code}_${level}.json.js`;
  
    return new Promise((resolve, reject) => {
      if (window[varName]) return resolve(window[varName]);
      const script = document.createElement('script');
      script.src = url;
      script.async = true;
      script.onload = () => window[varName] ? resolve(window[varName]) : reject(new Error(`Variable ${varName} non trouvée`));
      script.onerror = () => { if (level <= maxLevels) maxLevels = level - 1; resolve(null); };
      document.head.appendChild(script);
    })
    .then((data) => {
      if (!data) return;
      const filtered = parentName ? filterGeoJsonByParent(data, level - 1, parentName) : data;
      createGeoJsonLayer(filtered, level);
    })
    .catch((err) => console.error(`Error loading GeoJSON level ${level}:`, err));
  }
  

  // -----------------------------
  // Stats helpers
  // -----------------------------


  function reloadMapWithNewStyle() {
    if (!window.projectData || Object.keys(window.projectData).length === 0) {
      console.warn('❗ Aucun projet à afficher.');
      return;
    }
    for (let l = 1; l <= maxLevels; l++) {
      if (currentLayers[l]) {
        currentLayers[l].eachLayer((lyr) => {
          const feat = lyr.feature;
          const newStyle = getFeatureStyle(feat, l);
          lyr.setStyle(newStyle);

          const nameKey = detectedKeys[l]?.nameKey;
          const regionName = feat.properties?.[nameKey];
          const val = valueForDisplay(
            regionName,
            window.currentMapMetric,
            window.currentMapFilter
          );
          const valueTxt = window.currentMapMetric === 'cost' ? `${Number(val).toFixed(2)} G` : `${val}`;
          lyr.bindTooltip(
            `<b>${regionName}</b><br>${window.currentMapMetric === 'count' ? 'Projets' : 'Montant'} : ${valueTxt}`
          );
        });
      }
    }
    createDynamicLegend(map, window.codeGroupeProjet);
    info.update(domainesAssocie, niveaux);
  }
  window.reloadMapWithNewStyle = reloadMapWithNewStyle;

  function onFeatureClick(e, level) {
    const lyr = e.target;
    if (currentLayers[level]) currentLayers[level].resetStyle(lyr);

    const feature = lyr.feature;
    const nameKey = detectedKeys[level]?.nameKey;
    const typeKey = detectedKeys[level]?.typeKey;
    const featureName = feature.properties?.[nameKey];
    const featureType = typeKey ? feature.properties?.[typeKey] : `Niveau ${level}`;

    clearLayersAbove(level + 1);

    selectedLevels[level] = { type: featureType, name: featureName };
    for (let l = level + 1; l <= maxLevels; l++) delete selectedLevels[l];

    info.update(domainesAssocie, niveaux);
    
    // Si on est au niveau 3 et que la fonction handleLevel3InfrastructureClick existe (sigInfra uniquement)
    if (level === 3 && typeof window.handleLevel3InfrastructureClick === 'function') {
      // Récupérer les bounds de la couche pour ajuster la vue
      const bounds = lyr.getBounds ? lyr.getBounds() : null;
      
      // Essayer de trouver un code de localité dans les propriétés
      let localiteCode = feature.properties?.GID_3 || 
                        feature.properties?.GID_2 || 
                        feature.properties?.GID_1 ||
                        feature.properties?.code ||
                        featureName; // Utiliser le nom comme fallback
      
      console.log('[Map] Clic sur niveau 3, appel handleLevel3InfrastructureClick', {
        localiteCode,
        featureName,
        bounds: bounds ? bounds.toBBoxString() : null
      });
      
      window.handleLevel3InfrastructureClick(localiteCode, featureName, bounds);
    } else if (level < maxLevels) {
      loadGeoJsonLevel(level + 1, featureName);
    }
  }

  function highlightFeature(e) {
    const layer = e.target;
    const fillOpacity = (window.transparentLayersEnabled === true) ? 0.3 : 0.95;
    layer.setStyle({ weight: 4, color: '#222', fillOpacity: fillOpacity });
    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) layer.bringToFront();
  }

  function resetHighlight(e) {
    const layer = e.target;
    const lvl = layer.feature.properties.level;
    if (currentLayers[lvl]) {
      // Réappliquer le style avec la transparence actuelle
      const newStyle = getFeatureStyle(layer.feature, lvl);
      layer.setStyle(newStyle);
    }
  }

  // -----------------------------
  // Bootstrap: detect levels → load data → load level 1
  // -----------------------------
  detectMaxLevels(countryAlpha3Code, niveaux, options)
    .then((l) => {
      maxLevels = Math.max(1, l || 1);
    })
    .catch(() => {
      maxLevels = 1;
    })
    .finally(() => {
      const projectsPromise = loadProjectData(countryAlpha3Code, codeGroupeProjet).then(
        (data) => {
          window.projectData = processProjectData(data);
        }
      );
      projectsPromise
        .then(() => loadGeoJsonLevel(1))
        .catch((err) => console.error('Error loading project data:', err));
    });
}

// Export
window.initCountryMap = initCountryMap;



// initAfricaMap.js - Carte interactive pour l'Afrique avec rendu similaire à initCountryMap
function initAfricaMap() {
    const map = L.map('countryMap', {
        center: [0, 20],
        zoom: 3,
        zoomControl: true
    });

    const colorScale = chroma.scale(['#c7bda3', '#c2e699', '#78c679', '#31a354', '#006837', '#004529', '#082c1f', '#02150b'])
        .domain([0, 500, 1000, 1500, 2000, 2500, 3000, 3500])
        .mode('lab');

    let africaData = {}; // Données projet par pays
    let selectedCountry = null;

    const info = L.control({ position: 'topright' });

    info.onAdd = function () {
        this._div = L.DomUtil.create('div', 'info');
        this.update();
        return this._div;
    };

    info.update = function () {
        if (!selectedCountry || !africaData[selectedCountry]) {
            this._div.innerHTML = `
                <div class="title">Informations sur la zone</div>
                <table class="level-info">
                    <tr><th style="text-align: right;">Pays:</th><td>—</td></tr>
                </table>
                <table class="project-info">
                    <thead>
                        <tr>
                            <th colspan="4" style="text-align: center;">Répartition des projets</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid black; text-align: center;">Groupe</th>
                            <th style="border: 1px solid black; text-align: center;">Total</th>
                            <th style="border: 1px solid black; text-align: center;">Public</th>
                            <th style="border: 1px solid black; text-align: center;">Privé</th>
                        </tr>
                    </thead>
                    <tbody><tr><td colspan="4" style="text-align: center;">Aucune donnée</td></tr></tbody>
                </table>
            `;
            return;
        }

        const data = africaData[selectedCountry];
        const groupRows = Object.entries(data.groupes).map(([code, stats]) => {
            return `
                <tr>
                    <th style="border: 1px solid black; text-align: right;">${code}</th>
                    <td style="border: 1px solid black; text-align: center;">${stats.count}</td>
                    <td style="border: 1px solid black; text-align: center;">${stats.public}</td>
                    <td style="border: 1px solid black; text-align: center;">${stats.private}</td>
                </tr>
            `;
        }).join('');

        this._div.innerHTML = `
            <div class="title">Informations sur la zone</div>
            <table class="level-info">
                <tr><th style="text-align: right;">Pays:</th><td>${data.pays}</td></tr>
            </table>
            <table class="project-info">
                <thead>
                    <tr>
                        <th colspan="4" style="text-align: center;">Répartition du pays</th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid black; text-align: center;">Groupe</th>
                        <th style="border: 1px solid black; text-align: center;">Total</th>
                        <th style="border: 1px solid black; text-align: center;">Public</th>
                        <th style="border: 1px solid black; text-align: center;">Privé</th>
                    </tr>
                </thead>
                <tbody>
                    ${groupRows}
                    <tr>
                        <th style="border: 1px solid black; text-align: right;">Total</th>
                        <td style="border: 1px solid black; text-align: center;">${data.total}</td>
                        <td style="border: 1px solid black; text-align: center;">${data.public}</td>
                        <td style="border: 1px solid black; text-align: center;">${data.private}</td>
                    </tr>
                </tbody>
            </table>
        `;
    };

    info.addTo(map);

    const legend = L.control({ position: 'bottomright' });

    legend.onAdd = function (map) {
        const div = L.DomUtil.create('div', 'info legend');
        const grades = [0, 500, 1000, 1500, 2000, 2500, 3000, 3500];
        const labels = ['<h4>LEGENDE</h4><p>Nombre de projets</p>'];

        for (let i = 0; i < grades.length; i++) {
            const from = grades[i];
            const to = grades[i + 1];
            const color = colorScale(from + 1).hex();
            labels.push(`<i style="background:${color}; opacity: 0.7;"></i> ${from}${to ? `–${to}` : '+'}`);
        }

        div.innerHTML = labels.join('<br>');
        return div;
    };

    legend.addTo(map);

    function normalized(str) {
        if (!str) return '';
        return str.toString()
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    const script = document.createElement('script');
    script.src = geojsonBaseUrl() + 'gadm41_AFQ_1.json.js';

    script.onload = () => {
        const geojson = window.statesDataLevel1;

        if (!geojson || !geojson.features || geojson.features.length === 0) {
            console.error("GeoJSON Afrique invalide ou vide.");
            return;
        }

        fetch(apiUrl('projects/all'))
            .then(res => res.json())
            .then(data => {
                africaData = processAfricaProjectData(data);
                renderAfricaTable(africaData);

                const africaLayer = L.geoJSON(geojson, {
                    style: feature => {
                        const key = normalized(feature.properties.NAME_0);
                        const count = africaData[key]?.total || 0;
                        return {
                            weight: 1,
                            color: '#333',
                            fillOpacity: 0.7,
                            fillColor: count > 0 ? colorScale(count).hex() : '#c7bda3'
                        };
                    },
                    onEachFeature: (feature, layer) => {
                        const name = feature.properties.NAME_0;
                        const displayName = feature.properties.name_long || name;
                        const key = normalized(name);

                        layer.on({
                            click: () => {
                                selectedCountry = key;
                                info.update();
                            },
                            mouseover: () => {
                                selectedCountry = key;
                                info.update();
                                layer.setStyle({ weight: 3, color: '#666', fillOpacity: 0.9 });
                                layer.bringToFront();
                            },
                            mouseout: () => {
                                selectedCountry = null;
                                info.update();
                                layer.setStyle({ weight: 1, color: '#333', fillOpacity: 0.7 });
                            }
                        });

                        layer.bindTooltip(`<b>${displayName}</b>`);
                    }
                }).addTo(map);

                const bounds = africaLayer.getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds);
                } else {
                    console.warn("Les coordonnées du GeoJSON Afrique sont invalides. Impossible de centrer la carte.");
                }
            });
    };

    script.onerror = () => {
        console.error("Erreur lors du chargement du fichier gadm41_AFQ_1.json.js");
    };

    document.head.appendChild(script);

    function processAfricaProjectData(projects) {
        const summary = {};

        projects.forEach(project => {
            const codeProjet = String(project.code_projet || '');
            if (codeProjet.length < 6) return;

            const groupCode = codeProjet.substring(3, 6);
            const isPublic = project.is_public;
            const countryName = project.country_name || codeProjet.substring(0, 3);
            const key = normalized(countryName);

            if (!summary[key]) {
                summary[key] = {
                    pays: countryName,
                    total: 0,
                    public: 0,
                    private: 0,
                    groupes: {}
                };
            }

            summary[key].total++;
            isPublic ? summary[key].public++ : summary[key].private++;

            if (!summary[key].groupes[groupCode]) {
                summary[key].groupes[groupCode] = { count: 0, public: 0, private: 0 };
            }

            summary[key].groupes[groupCode].count++;
            isPublic ? summary[key].groupes[groupCode].public++ : summary[key].groupes[groupCode].private++;
        });

        return summary;
    }

    function renderAfricaTable(data) {
        const container = document.getElementById('africaDataTable');
        if (!container) return;

        const allGroupCodes = new Set();
        Object.values(data).forEach(country => {
            Object.keys(country.groupes).forEach(g => allGroupCodes.add(g));
        });

        const sortedGroups = [...allGroupCodes].sort();

        let html = '<table class="table table-bordered table-striped table-sm">';
        html += '<thead><tr><th rowspan="2">Pays</th><th rowspan="2">Total</th><th rowspan="2">Public</th><th rowspan="2">Privé</th>';
        html += `<th colspan="${sortedGroups.length * 3}" style="text-align: center;">Répartition par groupe projet</th></tr>`;
        html += '<tr>';
        sortedGroups.forEach(group => {
            html += `<th>${group}</th><th>Pub</th><th>Priv</th>`;
        });
        html += '</tr></thead><tbody>';

        for (const key in data) {
            const row = data[key];
            html += `<tr><td>${row.pays}</td><td>${row.total}</td><td>${row.public}</td><td>${row.private}</td>`;
            sortedGroups.forEach(group => {
                const g = row.groupes[group] || { count: 0, public: 0, private: 0 };
                html += `<td>${g.count}</td><td>${g.public}</td><td>${g.private}</td>`;
            });
            html += '</tr>';
        }

        html += '</tbody></table>';
        container.innerHTML = html;
    }
}
