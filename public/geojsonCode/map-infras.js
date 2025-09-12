'use strict';

/* =========================================================
   map.js — Carte SIG (Leaflet) paramétrique, centrée sur
   le NOMBRE D’INFRASTRUCTURES BÉNÉFICIAIRES.

   Points clés :
   - Marqueurs/icônes selon le niveau :
       * MODE A (groupe ≠ BTP) : N1=Groupe, N2=Domaines, N3=Sous-domaines
       * MODE B (groupe = BTP) : N1=Domaines, N2=Sous-domaines, N3=Sous-domaines (fin)
   - Tooltips des marqueurs : "X infrastructure(s)"
   - Légende des catégories (icônes/couleurs) selon le niveau courant
   - Filtre "infrastructures terminées / non terminées / toutes"
   - Légende de seuils (bottom-right) : récupérée du backend (getByGroupe)
   - Reste compatible avec le code existant (info panel, reloadMapWithNewStyle, etc.)
   ========================================================= */

/* -----------------------------
 * Helpers généraux
 * ---------------------------*/
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
    .trim();
};

function formatWithSpaces(number) {
  const n = Number(number);
  if (!isFinite(n)) return '0';
  return n.toLocaleString('fr-FR');
}

function apiUrl(path = '') {
  const base = (window.APP && window.APP.API_URL) || '';
  return base.replace(/\/+$/, '') + '/' + path.replace(/^\/+/, '');
}

function geojsonBaseUrl() {
  const base = (window.APP && window.APP.GEOJSON) || '';
  return base.replace(/\/+$/, '') + '/';
}

/* -----------------------------
 * Détection dynamique de niveaux GeoJSON disponibles
 * ---------------------------*/
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

async function detectMaxLevels(countryAlpha3Code, niveauxBackend, options) {
  const geoLevels = await detectAvailableLevels(countryAlpha3Code, options);
  const dataLevels = Array.isArray(niveauxBackend)
    ? Math.max(1, Math.min(3, niveauxBackend.length))
    : null;
  return dataLevels ? Math.min(geoLevels, dataLevels) : geoLevels;
}

/* -----------------------------
 * Inférence des clés de propriétés (NAME_X / TYPE_X)
 * ---------------------------*/
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
  return cand || null;
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

/* -----------------------------
 * ICONES & COULEURS (catégories)
 * ---------------------------*/
const ICONS = {
  groups: {
    ENE: { label: 'Énergie',        glyph: '⚡' },
    EHA: { label: 'Eau/Assain.',    glyph: '💧' },
    BAT: { label: 'Bâtiments',      glyph: '🏗️' },
    TRP: { label: 'Transports',     glyph: '🚦' },
    TIC: { label: 'TIC',            glyph: '📡' },
    AXU: { label: 'Axes urbains',   glyph: '🛣️' },
    BTP: { label: 'BTP',            glyph: '🏗️' }, // mode spécial
  },
  domainColors: {
    '01':'#2563eb','02':'#16a34a','03':'#9333ea','04':'#ea580c','05':'#0ea5e9',
    '06':'#f59e0b','07':'#ef4444','08':'#14b8a6','09':'#64748b'
  },
  subFallback: '#111827'
};

function makeDivIcon({bg='#374151', glyph='●'} = {}, count = 0) {
  const badge = count
    ? `<span style="position:absolute;top:-6px;right:-6px;background:#111827;color:#fff;border-radius:10px;padding:0 5px;font-size:10px;line-height:16px;">${count}</span>`
    : '';
  const html = `
    <div style="position:relative;display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:${bg};box-shadow:0 2px 6px rgba(0,0,0,.25);font-size:16px;">
      <span style="transform:translateY(-1px)">${glyph}</span>
      ${badge}
    </div>`;
  return L.divIcon({ className: 'gp-pin', html, iconSize: [28,28], iconAnchor:[14,14], popupAnchor:[0,-14]});
}

/* -----------------------------
 * Centroid & offsets pour éviter la superposition
 * ---------------------------*/
function featureCentroid(lyr) {
  const b = lyr.getBounds();
  return b.getCenter();
}

function spreadOffsets(n){
  const arr = [];
  const r = 18;
  for (let i=0;i<n;i++){
    const ang = (i / Math.max(1,n)) * Math.PI*2;
    arr.push([Math.sin(ang)*r, Math.cos(ang)*r]);
  }
  return arr;
}

/* =========================================================
 *  FONCTION PRINCIPALE — Carte Pays
 * =======================================================*/
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

  // State
  let currentLayers = {};
  let selectedLevels = {};
  let maxLevels = 3;

  window.currentMapMetric = 'count';      // toujours "count" (nb d'infras)
  window.currentMapFilter = window.currentMapFilter || 'cumul'; // cumul|public|private
  window.customLegend = Array.isArray(window.customLegend) ? window.customLegend : [];
  window.currentLegendControl = window.currentLegendControl || null;
  window.projectData = window.projectData || {};
  window.codeGroupeProjet = codeGroupeProjet;

  const detectedKeys = { 1: {}, 2: {}, 3: {} };

  // Marqueurs par niveau
  let markerLayers = {
    1: L.layerGroup().addTo(map),
    2: L.layerGroup().addTo(map),
    3: L.layerGroup().addTo(map)
  };
  function clearMarkerLayers(fromLevel=1){
    for(let l=fromLevel;l<=3;l++){ markerLayers[l].clearLayers(); }
  }
  const isBTP = () => String(window.codeGroupeProjet||'').toUpperCase() === 'BTP';

  /* -----------------------------
   * INFO PANEL (top-right)
   * ---------------------------*/
  const info = L.control({ position: 'topright' });
  info.onAdd = function () {
    this._div = L.DomUtil.create('div', 'info');
    this.update(domainesAssocie, niveaux);
    return this._div;
  };

  function libelleDomaineFrom2(code2) {
    const arr = window.domainesAssocieArr || domainesAssocie || [];
    const found = arr.find(d => String(d.code).startsWith(String(code2)));
    return found ? (found.libelle || code2) : code2;
  }

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

    // Data agrégée par niveau sélectionné
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

    // Valeur totale (nb d'infras) selon filtre public/privé/cumul
    const totalInfras = (() => {
      const last = Object.values(localityDataByLevel).pop();
      if (!last) return 0;
      if (window.currentMapFilter === 'private') return last.private || 0;
      if (window.currentMapFilter === 'public')  return last.public  || 0;
      return last.count || 0;
    })();

    // Lignes par domaine
    const domainRows = (domaines || [])
      .map((domaine) => {
        const d2 = String(domaine.code || '').substring(0, 2);
        // niveau le plus profond sélectionné
        const levels = Object.keys(localityDataByLevel).map(Number).sort((a,b) => a - b);
        const deepest = levels.length ? levels[levels.length - 1] : null;
        const stats = deepest ? (localityDataByLevel[deepest]?.byDomain?.[d2] || {}) : {};
        let totalCell = 0;
        if (window.currentMapFilter === 'private') totalCell = stats.private || 0;
        else if (window.currentMapFilter === 'public') totalCell = stats.public || 0;
        else totalCell = (stats.public||0)+(stats.private||0);

        const perLevelCells = Array.from({ length: maxLevels }, (_, idx) => {
          const lv = idx + 1;
          const levelData = localityDataByLevel[lv];
          const st = levelData?.byDomain?.[d2] || {};
          const pv = window.currentMapFilter === 'private' ? st.private||0
                   : window.currentMapFilter === 'public'  ? st.public||0
                   : (st.public||0)+(st.private||0);
          const code = levelData?.code || '';
          return `<td style="border:1px solid #000; text-align:center;">
                    <a href="#" class="project-cell" data-code="${code}" data-level="${lv}" data-filter="${window.currentMapFilter}" data-domain="${d2}">
                      ${pv}
                    </a>
                  </td>`;
        }).join('');

        return `
          <tr>
            <th style="border:1px solid #000; text-align:right;">${domaine.libelle}</th>
            <td style="border:1px solid #000; text-align:center;">${formatWithSpaces(totalCell)}</td>
            ${perLevelCells}
          </tr>`;
      })
      .join('');

    const headerCols = Array.from({ length: maxLevels }, (_, i) => {
      const sampleProps = detectedKeys[i + 1]?.sampleProps || null;
      const typeKey = detectedKeys[i + 1]?.typeKey || null;
      const label = labelForLevel(i + 1, niveauxBackend, options, typeKey, sampleProps);
      return `<th style="border:1px solid #000; text-align:center;">${label}</th>`;
    }).join('');

    this._div.innerHTML = `
      <div class="title">Informations sur la zone</div>
      <table class="level-info">${levelRows.join('')}</table>
      <table class="project-info">
        <thead>
          <tr>
            <th colspan="${2 + maxLevels}" style="text-align:center;">Répartition des <u>infrastructures bénéficiaires</u></th>
          </tr>
          <tr>
            <th rowspan="2" style="border:1px solid #000; text-align:center;">Domaines</th>
            <th rowspan="2" style="border:1px solid #000; text-align:center;">Total</th>
            <th colspan="${maxLevels}" style="border:1px solid #000; text-align:center;">Par niveau</th>
          </tr>
          <tr>${headerCols}</tr>
        </thead>
        <tbody>
          ${domainRows}
          ${(() => {
            const totalRowCells = Array.from({ length: maxLevels }, (_, idx) => {
              const levelData = localityDataByLevel[idx + 1] || {};
              const pv = window.currentMapFilter === 'private' ? (levelData.private || 0)
                       : window.currentMapFilter === 'public'  ? (levelData.public  || 0)
                       : (levelData.public||0)+(levelData.private||0);
              const code = levelData.code || '';
              return `<td style="border:1px solid #000; text-align:center;">
                        <a href="#" class="project-cell" data-code="${code}" data-level="${idx + 1}" data-filter="${window.currentMapFilter}">
                          ${formatWithSpaces(pv)}
                        </a>
                      </td>`;
            }).join('');

            return `
              <tr>
                <th style="border:1px solid #000; text-align:right;">Total</th>
                <td style="border:1px solid #000; text-align:center;">${formatWithSpaces(totalInfras)}</td>
                ${totalRowCells}
              </tr>`;
          })()}
        </tbody>
      </table>
    `;

    // clic sur cellules → drawer
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

  /* -----------------------------
   * Légende dynamique (seuils)
   * ---------------------------*/
  function createDynamicLegend(map, groupeCode) {
    // On utilise toujours "count" (nb d'infras) → typeFin=1 côté backend
    const typeFin = 1;
    const legendUrl = options.getLegendUrl
      ? options.getLegendUrl(groupeCode, typeFin)
      : apiUrl(`legende/${encodeURIComponent(groupeCode)}?typeFin=${typeFin}`);

    fetch(encodeURI(legendUrl))
      .then((r) => {
        if (!r.ok) throw new Error(`HTTP ${r.status}`);
        return r.json();
      })
      .then((data) => {
        // data.label doit refléter "Nombre d’infrastructures bénéficiaires"
        window.customLegend = data.seuils || [];
        if (window.currentLegendControl) map.removeControl(window.currentLegendControl);
        const legend = L.control({ position: 'bottomright' });
        legend.onAdd = function () {
          const div = L.DomUtil.create('div', 'info legend');
          const labels = [`<h4>LÉGENDE</h4><p>${data.label || 'Nombre d’infrastructures bénéficiaires'}</p>`];
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

  /* -----------------------------
   * Légende catégories (icônes/couleurs)
   * ---------------------------*/
  let categoryLegendControl = null;

  function updateCategoryLegend(){
    if (categoryLegendControl){ map.removeControl(categoryLegendControl); categoryLegendControl = null; }

    const htmlRows = [];
    htmlRows.push('<div style="font-weight:700;margin-bottom:6px">Catégories</div>');

    if (isBTP()){
      // Niveaux pilotés par domaines / sous-domaines
      (window.domainesAssocieArr||domainesAssocie||[]).forEach(d=>{
        const d2 = String(d.code||'').substring(0,2);
        const c = ICONS.domainColors[d2] || '#334155';
        htmlRows.push(`<div style="display:flex;align-items:center;gap:8px;margin-bottom:2px">
          <span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:${c}"></span>
          <span>${d.libelle} (${d2})</span>
        </div>`);
      });
    } else {
      // Groupe courant + palette des domaines
      const grp = String(window.codeGroupeProjet||'').toUpperCase();
      const g = ICONS.groups[grp];
      if (g) htmlRows.push(`<div style="margin-bottom:6px">Groupe : ${g.glyph} ${g.label}</div>`);
      (window.domainesAssocieArr||domainesAssocie||[]).forEach(d=>{
        const d2 = String(d.code||'').substring(0,2);
        const c = ICONS.domainColors[d2] || '#334155';
        htmlRows.push(`<div style="display:flex;align-items:center;gap:8px;margin-bottom:2px">
          <span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:${c}"></span>
          <span>${d.libelle} (${d2})</span>
        </div>`);
      });
    }

    categoryLegendControl = L.control({position:'bottomleft'});
    categoryLegendControl.onAdd = function(){
      const div = L.DomUtil.create('div','info legend');
      div.innerHTML = htmlRows.join('');
      return div;
    };
    categoryLegendControl.addTo(map);
  }
  updateCategoryLegend();

  /* -----------------------------
   * Chargement/traitement des données
   * ---------------------------*/
  function loadProjectData(countryCode, groupCode) {
    // On privilégie l’endpoint "aggregate" (compte d'infras + byDomain + bySub)
    const infraStatus = (document.getElementById('infra_status')?.value || 'all');
    const params = new URLSearchParams({
      groupe: groupCode,
      finance: window.currentMapFilter || 'cumul',
      infra_status: infraStatus
    });
    const urlAgg = apiUrl(`aggregate?${params.toString()}`);

    return fetch(urlAgg, { headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(d => (d && d.projets) ? d.projets : [])
      .catch(() => {
        // Fallback, si jamais aggregate n'existe pas
        const url = apiUrl(`projects?country=${encodeURIComponent(countryCode)}&group=${encodeURIComponent(groupCode)}`);
        return fetch(url, { headers: { 'Accept': 'application/json' } }).then(r=>r.json());
      });
  }

  function processProjectData(projects) {
    // agrégat par zone : on attend {name, code, count, public, private, cost, byDomain, bySub}
    const data = {};
    (projects || []).forEach((p) => {
      const key = normalized(p.name);
      data[key] = {
        name: p.name,
        code: p.code,
        level: p.level,
        count: Number(p.count) || 0,
        cost: Number(p.cost) || 0,
        public: Number(p.public) || 0,
        private: Number(p.private) || 0,
        byDomain: p.byDomain || {},
        bySub: p.bySub || {},
      };
    });
    window.projectData = data;
    return data;
  }
  window.processProjectData = processProjectData;

  /* -----------------------------
   * Couleurs de polygones (dépend du seuil & nb d’infras)
   * ---------------------------*/
  function valueForLegend(regionName, filter = 'cumul') {
    if (!window.projectData || !regionName) return 0;
    const stats = window.projectData[normalized(regionName)];
    if (!stats) return 0;
    if (filter === 'private') return stats.private || 0;
    if (filter === 'public')  return stats.public  || 0;
    return stats.count || 0;
  }

  function valueForDisplay(regionName, filter = 'cumul') {
    return valueForLegend(regionName, filter);
  }

  function getFeatureStyle(feature, level) {
    const nameKey = detectedKeys[level]?.nameKey;
    const regionName = feature.properties?.[nameKey];
    const value = valueForLegend(regionName, window.currentMapFilter);
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

    return { weight: 1, opacity: 1, color: 'white', fillOpacity: 0.7, fillColor };
  }

  /* -----------------------------
   * Marqueurs par niveau
   * ---------------------------*/
  function buildMarkersForLevel(level){
    if(!currentLayers[level]) return;
    markerLayers[level].clearLayers();

    currentLayers[level].eachLayer((lyr)=>{
      const props = lyr.feature?.properties || {};
      const nameKey = detectedKeys[level]?.nameKey;
      const zoneName = props[nameKey];
      const stats = window.projectData[normalized(zoneName)];
      if(!stats) return;

      const center = featureCentroid(lyr);
      const categories = [];

      if (isBTP()){
        // MODE B : N1=domaines, N2=sous-domaines, N3=sous-domaines (plus fin)
        if (level === 1){
          const byDom = stats.byDomain || {};
          Object.entries(byDom).forEach(([d2, v])=>{
            categories.push({
              key: d2,
              kind: 'domain',
              label: libelleDomaineFrom2(d2),
              count: (v.public||0)+(v.private||0)
            });
          });
        } else {
          const bySub = stats.bySub || {};
          Object.entries(bySub).forEach(([s4, v])=>{
            const d2 = s4.substring(0,2);
            categories.push({
              key: s4,
              kind: 'sub',
              label: s4,
              color: ICONS.domainColors[d2] || ICONS.subFallback,
              count: (v.public||0)+(v.private||0)
            });
          });
        }
      } else {
        // MODE A : N1=groupe, N2=domaines, N3=sous-domaines
        if (level === 1){
          const grp = String(window.codeGroupeProjet||'').toUpperCase();
          categories.push({
            key: grp,
            kind: 'group',
            label: ICONS.groups[grp]?.label || grp,
            glyph: ICONS.groups[grp]?.glyph || '●',
            count: (stats.public||0)+(stats.private||0)
          });
        } else if (level === 2){
          const byDom = stats.byDomain || {};
          Object.entries(byDom).forEach(([d2, v])=>{
            categories.push({
              key: d2,
              kind: 'domain',
              label: libelleDomaineFrom2(d2),
              count: (v.public||0)+(v.private||0)
            });
          });
        } else if (level === 3){
          const bySub = stats.bySub || {};
          Object.entries(bySub).forEach(([s4, v])=>{
            const d2 = s4.substring(0,2);
            categories.push({
              key: s4,
              kind: 'sub',
              label: s4,
              color: ICONS.domainColors[d2] || ICONS.subFallback,
              count: (v.public||0)+(v.private||0)
            });
          });
        }
      }

      // 1 marqueur par catégorie avec badge = count
      const offs = spreadOffsets(categories.length);
      categories.forEach((cat, i)=>{
        let icon;
        if (cat.kind === 'group'){
          icon = makeDivIcon({bg:'#1f2937', glyph:cat.glyph||'●'}, cat.count);
        } else if (cat.kind === 'domain'){
          const bg = ICONS.domainColors[cat.key] || '#334155';
          icon = makeDivIcon({bg, glyph:'◆'}, cat.count);
        } else {
          const bg = cat.color || ICONS.subFallback;
          icon = makeDivIcon({bg, glyph:'⬤'}, cat.count);
        }

        // Décalage simple sans plugin (transform via layerPoint)
        const basePt = map.latLngToLayerPoint(center);
        const p2 = L.point(basePt.x + (offs[i]?.[0]||0), basePt.y + (offs[i]?.[1]||0));
        const shifted = map.layerPointToLatLng(p2);

        const m = L.marker(shifted, { icon });
        m.bindTooltip(`<b>${cat.label}</b><br>${formatWithSpaces(cat.count)} infrastructure(s)`);
        markerLayers[level].addLayer(m);
      });
    });
  }

  /* -----------------------------
   * GeoJSON (chargement par niveau)
   * ---------------------------*/
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

  function onEachFeature(feature, layer, level) {
    feature.properties.level = level;
    const nameKey = detectedKeys[level]?.nameKey;
    const regionName = feature.properties?.[nameKey];

    const val = valueForDisplay(regionName, window.currentMapFilter);
    layer.on({
      click: (e) => onFeatureClick(e, level),
      mouseover: highlightFeature,
      mouseout: resetHighlight,
    });
    layer.bindTooltip(`<b>${regionName}</b><br>Infrastructures : ${formatWithSpaces(val)}`);

    layer.on('mouseover', () => {
      const el = layer.getElement?.();
      if (el) el.style.cursor = 'pointer';
    });
  }

  function createGeoJsonLayer(data, level) {
    if (!data || !data.features || !data.features.length) return;

    // Inférer les clés pour ce niveau
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

    // Construire les marqueurs de ce niveau
    buildMarkersForLevel(level);
  }

  function clearLayersAbove(level) {
    for (let l = level; l <= maxLevels; l++) {
      if (currentLayers[l]) {
        map.removeLayer(currentLayers[l]);
        delete currentLayers[l];
      }
    }
    clearMarkerLayers(level);
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

  /* -----------------------------
   * Interactions / redraw
   * ---------------------------*/
  function reloadMapWithNewStyle() {
    if (!window.projectData || Object.keys(window.projectData).length === 0) {
      console.warn('❗ Aucun agrégat infrastructure à afficher.');
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
          const val = valueForDisplay(regionName, window.currentMapFilter);
          lyr.bindTooltip(`<b>${regionName}</b><br>Infrastructures : ${formatWithSpaces(val)}`);
        });
      }
    }
    // légendes + marqueurs
    createDynamicLegend(map, window.codeGroupeProjet);
    updateCategoryLegend();
    for (let l=1; l<=maxLevels; l++){
      if (currentLayers[l]) buildMarkersForLevel(l);
    }
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
    if (level < maxLevels) {
      loadGeoJsonLevel(level + 1, featureName);
    } else {
      // Dernier niveau : zoom confortable pour bien voir les marqueurs
      const b = currentLayers[level].getBounds?.();
      if (b && b.isValid()) map.fitBounds(b, { maxZoom: Math.max(map.getZoom(), 8) });
    }
  }

  function highlightFeature(e) {
    const layer = e.target;
    layer.setStyle({ weight: 4, color: '#222', fillOpacity: 0.95 });
    if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) layer.bringToFront();
  }

  function resetHighlight(e) {
    const layer = e.target;
    const lvl = layer.feature.properties.level;
    if (currentLayers[lvl]) currentLayers[lvl].resetStyle(layer);
  }

  /* -----------------------------
   * Bootstrap : niveaux → données → niveau 1
   * ---------------------------*/
  detectMaxLevels(countryAlpha3Code, niveaux, options)
    .then((l) => { maxLevels = Math.max(1, l || 1); })
    .catch(() => { maxLevels = 1; })
    .finally(() => {
      loadProjectData(countryAlpha3Code, codeGroupeProjet)
        .then((data) => { window.projectData = processProjectData(data); })
        .then(() => loadGeoJsonLevel(1))
        .catch((err) => console.error('Error loading project data:', err));
    });

  /* -----------------------------
   * Écoute le select "infra_status" pour recharger l’agrégat
   * ---------------------------*/
  const infraSel = document.getElementById('infra_status');
  if (infraSel) {
    infraSel.addEventListener('change', () => {
      // recharger l’agrégat puis restyler
      loadProjectData(countryAlpha3Code, codeGroupeProjet)
        .then((data) => { window.projectData = processProjectData(data); })
        .then(() => reloadMapWithNewStyle())
        .catch(console.error);
    });
  }
}

// Export global
window.initCountryMap = initCountryMap;

/* =========================================================
 *  Carte Afrique (AFQ) — inchangée, légère adaptation wording
 * =======================================================*/
function initAfricaMap() {
  const map = L.map('countryMap', {
    center: [0, 20],
    zoom: 3,
    zoomControl: true
  });

  const colorScale = chroma.scale(['#c7bda3', '#c2e699', '#78c679', '#31a354', '#006837', '#004529', '#082c1f', '#02150b'])
      .domain([0, 500, 1000, 1500, 2000, 2500, 3000, 3500])
      .mode('lab');

  let africaData = {}; // Données (ici : nombre d’items, peut rester "projets" si tel est le modèle)
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
              <th colspan="4" style="text-align: center;">Répartition</th>
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
          <td style="border: 1px solid black; text-align: center;">${formatWithSpaces(stats.count)}</td>
          <td style="border: 1px solid black; text-align: center;">${formatWithSpaces(stats.public)}</td>
          <td style="border: 1px solid black; text-align: center;">${formatWithSpaces(stats.private)}</td>
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
            <td style="border: 1px solid black; text-align: center;">${formatWithSpaces(data.total)}</td>
            <td style="border: 1px solid black; text-align: center;">${formatWithSpaces(data.public)}</td>
            <td style="border: 1px solid black; text-align: center;">${formatWithSpaces(data.private)}</td>
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
    const labels = ['<h4>LÉGENDE</h4><p>Volume agrégé</p>'];

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
              click: () => { selectedCountry = key; info.update(); },
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
        if (bounds.isValid()) map.fitBounds(bounds);
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
        summary[key] = { pays: countryName, total: 0, public: 0, private: 0, groupes: {} };
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
}
window.initAfricaMap = initAfricaMap;
