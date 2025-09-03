'use strict';

// Helpers
const fmtFR = new Intl.NumberFormat('fr-FR');
const apiBase = () => (window.APP?.API_URL || '').replace(/\/+$/,'');
const geoBase = () => (window.APP?.GEOJSON || '').replace(/\/+$/,'') + '/';
const norm = (s)=> (s||'').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').trim();

async function urlExists(u){
  try{
    let r = await fetch(u,{method:'HEAD'}); if(r.ok) return true;
    r = await fetch(u,{cache:'no-cache'}); return r.ok;
  }catch{ return false; }
}
async function detectLevels(alpha3){
  const b = geoBase() + `gadm41_${alpha3}_`;
  const ok1 = await urlExists(b+'1.json.js');
  const ok2 = ok1 ? await urlExists(b+'2.json.js') : false;
  const ok3 = ok2 ? await urlExists(b+'3.json.js') : false;
  return ok3 ? 3 : ok2 ? 2 : ok1 ? 1 : 0;
}
function guessNameKey(props){
  if(!props) return null;
  const ks = Object.keys(props);
  let c = ks.find(k=>/^NAME_\d$/i.test(k)); if(c) return c;
  c = ks.find(k=>/^name$/i.test(k)); if(c) return c;
  c = ks.find(k=>/name/i.test(k)); return c||null;
}
function guessTypeKey(props){
  if(!props) return null;
  const ks = Object.keys(props);
  let c = ks.find(k=>/^TYPE_\d$/i.test(k)); if(c) return c;
  c = ks.find(k=>/type|level|status/i.test(k)); return c||null;
}

// State
let MAP, MAXL=3, CUR = { metric:'count', finance:'cumul', groupe:'', dom:'', sous:'' };
let LAYERS = {};
let KEYS = {1:{},2:{},3:{}};
let LEG = [];
let AGG = {}; // code -> {name, level, code, count, public, private, cost, byDomain:{}}
let NAMES_BY_CODE = {};

function valueForLegend(code){
  const v = AGG[code]; if(!v) return 0;
  if(CUR.metric==='count'){
    if(CUR.finance==='public') return v.public||0;
    if(CUR.finance==='private') return v.private||0;
    return v.count||0;
  }
  // cost: unité brute (FCFA/EUR) ; la légende est servie dans la même unité
  const tot = (v.public||0) + (v.private||0);
  const ratio = CUR.finance==='public' ? (tot? (v.public/tot):0)
              : CUR.finance==='private'? (tot? (v.private/tot):0)
              : 1;
  return (v.cost||0) * ratio;
}
function valueForTooltip(code){
  const raw = valueForLegend(code);
  if(CUR.metric==='cost') return (raw/1_000_000_000).toFixed(2)+' G';
  return fmtFR.format(raw);
}

function styleFor(feature, level){
  const nameKey = KEYS[level]?.nameKey;
  const name = feature.properties?.[nameKey];
  // Nous utilisons le CODE (clé stable) plutôt que le nom
  const code = feature.properties?.__code || name; // fallback si pas injecté
  const v = valueForLegend(code);
  let fill = '#e5e7eb';
  if(LEG && LEG.length){
    const f = LEG.find(s=>{
      const bi = (s.borneInf ?? null), bs = (s.borneSup ?? null);
      if(bs===null || bs===undefined) return v >= bi;
      return v >= bi && v <= bs;
    });
    fill = f ? f.couleur : '#f43f5e';
  }
  return {weight:1,opacity:1,color:'#fff',fillOpacity:.8,fillColor:fill};
}

function bindEvents(layer, level){
  layer.on({
    mouseover: e=>{
      const l=e.target; l.setStyle({weight:3,color:'#222',fillOpacity:.95}); l.bringToFront?.();
    },
    mouseout: e=>{
      const l=e.target; const lv = l.feature.properties.level;
      LAYERS[lv]?.resetStyle(l);
    },
    click: e=>{
      const f = e.target.feature;
      const code = f.properties.__code || f.properties[KEYS[level]?.nameKey];
      openDrawerFor(code, level);
      // drill-down
      if(level<MAXL) loadLevel(level+1, f.properties[KEYS[level]?.nameKey]);
    }
  });
}

function tooltipText(feature, level){
  const code = feature.properties.__code || feature.properties[KEYS[level]?.nameKey];
  const name = AGG[code]?.name || feature.properties[KEYS[level]?.nameKey];
  const metricLabel = CUR.metric==='count' ? 'Infras' : 'Montant';
  return `<b>${name}</b><br>${metricLabel}: ${valueForTooltip(code)}`;
}

function injectCodes(data, level){
  // Nous injectons un __code stable basé sur le code localité via mapping AGG
  // Si non trouvé, fallback: NAME_x
  const nameK = guessNameKey(data.features?.[0]?.properties||{});
  data.features.forEach(fe=>{
    const nm = fe.properties[nameK];
    // Trouver dans AGG l’entrée dont name normalisé == nm (fallback si clé exacte présente)
    // Mais nous avons l’agrégat indexé par CODE → pour améliorer le matching
    // on cherche par valeurs AGG[name]==nm → sinon on laisse le nom.
    let code = null;
    // heuristique: si une entrée AGG a le même nom
    for(const k in AGG){
      if((AGG[k]?.name||'').toLowerCase() === (nm||'').toLowerCase()){ code=k; break; }
    }
    fe.properties.__code = code || nm;
    fe.properties.level = level;
  });
}

async function loadLegend(){
  const url = `${apiBase()}/infras/legend?metric=${encodeURIComponent(CUR.metric)}`;
  const js = await fetch(url).then(r=>r.json());
  LEG = js.seuils||[];
  // Dessine contrôle
  if(window._legendCtl){ MAP.removeControl(window._legendCtl); }
  const ctl = L.control({position:'bottomright'});
  ctl.onAdd= function(){
    const div = L.DomUtil.create('div','info legend');
    const labels = [`<strong>${js.label||'Légende'}</strong>`];
    (js.seuils||[]).forEach(s=>{
      const txt = (s.borneSup==null || s.borneSup==undefined) ? `${s.borneInf}+` : `${s.borneInf}–${s.borneSup}`;
      labels.push(`<i style="display:inline-block;width:16px;height:16px;background:${s.couleur};margin-right:6px;border-radius:3px;"></i>${txt}`);
    });
    div.innerHTML = labels.join('<br>');
    return div;
  };
  ctl.addTo(MAP);
  window._legendCtl = ctl;
}

async function fetchFilters(){
  const js = await fetch(`${apiBase()}/infras/filters`).then(r=>r.json());
  const $g = document.getElementById('filtreGroupe');
  const $d = document.getElementById('filtreDomaine');
  const $s = document.getElementById('filtreSous');
  (js.groupes||[]).forEach(g=>{
    const o=document.createElement('option'); o.value=g.code; o.textContent=`${g.libelle} (${g.code})`; $g.appendChild(o);
  });
  (js.domaines||[]).forEach(d=>{
    const o=document.createElement('option'); o.value=d.code; o.textContent=`${d.libelle}`; o.dataset.groupe=d.groupe_projet_code; $d.appendChild(o);
  });
  (js.sous||[]).forEach(sd=>{
    const o=document.createElement('option'); o.value=sd.code_sous_domaine; o.textContent=sd.lib_sous_domaine; o.dataset.groupe=sd.code_groupe_projet; o.dataset.domaine=sd.code_domaine; $s.appendChild(o);
  });

  // Chaînage simple
  $g.addEventListener('change', ()=>{
    const g=$g.value;
    [...$d.options].forEach((opt,i)=>{ if(i===0){opt.hidden=false;return;} opt.hidden = !!g && opt.dataset.groupe!==g; });
    [...$s.options].forEach((opt,i)=>{ if(i===0){opt.hidden=false;return;} opt.hidden = !!g && opt.dataset.groupe!==g; });
    $d.value=''; $s.value='';
  });
  $d.addEventListener('change', ()=>{
    const d=$d.value;
    [...$s.options].forEach((opt,i)=>{ if(i===0){opt.hidden=false;return;} opt.hidden = !!d && opt.dataset.domaine!==d; });
    $s.value='';
  });
}

async function fetchAggregate(){
  const p = new URLSearchParams();
  if(CUR.groupe)  p.set('groupe', CUR.groupe);
  if(CUR.dom)     p.set('domaine', CUR.dom);
  if(CUR.sous)    p.set('sous', CUR.sous);
  if(CUR.finance) p.set('finance', CUR.finance);
  const sd = document.getElementById('start_date').value;
  const ed = document.getElementById('end_date').value;
  if(sd) p.set('start_date', sd);
  if(ed) p.set('end_date', ed);

  const js = await fetch(`${apiBase()}/infras/aggregate?`+p.toString()).then(r=>r.json());
  const arr = js.projets||[];
  // index par code
  AGG = {};
  NAMES_BY_CODE = {};
  arr.forEach(x=>{ AGG[x.code]=x; NAMES_BY_CODE[x.code]=x.name; });
}

function applyInfoPanel(){
  if(window._infoCtl){ MAP.removeControl(window._infoCtl); }
  const ctl = L.control({position:'topright'});
  ctl.onAdd = function(){
    this._div = L.DomUtil.create('div','info');
    this.update();
    return this._div;
  };
  ctl.update = function(){
    const rows = [];
    for(let l=1; l<=MAXL; l++){
      rows.push(`<tr><th style="text-align:right;padding-right:6px;">${window.APP?.NIVEAUX?.[l-1]?.libelle_decoupage || ('Niveau '+l)}:</th><td id="selL${l}">—</td></tr>`);
    }
    this._div.innerHTML = `
      <div><strong>Informations</strong></div>
      <table class="table table-sm mb-2">${rows.join('')}</table>
      <div class="small text-muted">Métrique: <b>${CUR.metric==='count'?'Nb d’infras':'Montant réparti'}</b> — Filtre: <b>${CUR.finance}</b></div>
    `;
  };
  ctl.addTo(MAP);
  window._infoCtl = ctl;
}

function updateSelectedPath(level, feature){
  for(let l=level+1; l<=MAXL; l++){
    const cell = document.getElementById('selL'+l); if(cell) cell.textContent='—';
  }
  const cell = document.getElementById('selL'+level);
  if(cell){
    const code = feature.properties.__code || feature.properties[KEYS[level]?.nameKey];
    cell.textContent = NAMES_BY_CODE[code] || feature.properties[KEYS[level]?.nameKey] || code;
  }
}

async function loadLevel(level, parentName=null){
  if(level>MAXL) return;
  const url = `${geoBase()}gadm41_${window.APP.ALPHA3}_${level}.json.js`;
  const varName = `statesDataLevel${level}`;
  await new Promise((res,rej)=>{
    if(window[varName]) return res();
    const s=document.createElement('script'); s.src=url; s.async=true;
    s.onload=()=>window[varName]?res():rej(new Error('GeoJSON var missing'));
    s.onerror=()=>res(); document.head.appendChild(s);
  });
  const data = window[varName];
  if(!data || !data.features || !data.features.length) return;

  // Déduction de clés
  if(!KEYS[level].nameKey){
    const sp = data.features[0]?.properties||{};
    KEYS[level].nameKey = guessNameKey(sp);
    KEYS[level].typeKey = guessTypeKey(sp);
  }

  // Filtrage par parent (par nom — identique à ta base)
  let filtered = data;
  if(parentName){
    const nkPrev = KEYS[level-1]?.nameKey;
    const target = norm(parentName);
    filtered = {...data, features: data.features.filter(f=> norm(f.properties[nkPrev]) === target )};
  }

  injectCodes(filtered, level);

  if(LAYERS[level]){ MAP.removeLayer(LAYERS[level]); delete LAYERS[level]; }
  const layer = L.geoJSON(filtered, {
    style: (f)=> styleFor(f, level),
    onEachFeature: (f, lyr)=>{
      bindEvents(lyr, level);
      lyr.bindTooltip( ()=> tooltipText(f, level) );
      lyr.on('click', ()=> updateSelectedPath(level, f) );
    }
  }).addTo(MAP);
  LAYERS[level] = layer;
  if(level===1) MAP.fitBounds(layer.getBounds());
}

async function reloadStyles(){
  await loadLegend();
  for(let l=1; l<=MAXL; l++){
    if(!LAYERS[l]) continue;
    LAYERS[l].eachLayer(ly=>{
      const f=ly.feature;
      ly.setStyle(styleFor(f, l));
      ly.setTooltipContent( ()=> tooltipText(f,l) );
    });
  }
  applyInfoPanel();
}

// Drawer
async function openDrawerFor(code, level){
  const p = new URLSearchParams();
  p.set('code', code);
  p.set('filter', CUR.finance);
  if(CUR.dom) p.set('domain', CUR.dom);
  const js = await fetch(`${apiBase()}/infras/details?`+p.toString()).then(r=>r.json());

  const title = document.getElementById('drawerTitle');
  const meta  = document.getElementById('drawerMeta');
  const tbodyP= document.getElementById('drawerProjectsBody');
  const tbodyI= document.getElementById('drawerInfrasBody');
  document.getElementById('drawerFilter').textContent = `Filtre: ${CUR.finance}`;
  document.getElementById('drawerDomain').textContent = `Domaine: ${CUR.dom||'Tous'}`;
  document.getElementById('drawerLevel').textContent  = `Niveau ${level}`;

  title.textContent = `Détails — ${NAMES_BY_CODE[code]||code}`;
  meta.textContent  = `${js.count||0} projet(s), ${ (js.infras||[]).length } infrastructure(s)`;

  // Infras
  if(!js.infras || !js.infras.length){
    tbodyI.innerHTML = `<tr><td colspan="4" class="text-center">Aucune infrastructure</td></tr>`;
  }else{
    tbodyI.innerHTML = js.infras.map((x,i)=>`
      <tr>
        <td>${i+1}</td>
        <td>${x.code}</td>
        <td>${(x.libelle||'').replace(/</g,'&lt;')}</td>
        <td>${x.lat??'—'}, ${x.lng??'—'}</td>
      </tr>
    `).join('');
  }

  // Projets
  if(!js.projects || !js.projects.length){
    tbodyP.innerHTML = `<tr><td colspan="5" class="text-center">Aucun projet</td></tr>`;
  }else{
    tbodyP.innerHTML = js.projects.map((p,i)=>`
      <tr>
        <td>${i+1}</td>
        <td><a class="text-decoration-none" target="_blank">${p.code_projet}</a></td>
        <td>${(p.libelle_projet||'').replace(/</g,'&lt;')}</td>
        <td class="text-end">${fmtFR.format(p.cout_projet||0)} ${p.code_devise||''}</td>
        <td>${p.is_public?'Public':'Privé'}</td>
      </tr>
    `).join('');
  }

  document.getElementById('drawerOverlay').classList.add('open');
  document.getElementById('infraDrawer').classList.add('open');
}
window.closeInfraDrawer = function(){
  document.getElementById('drawerOverlay').classList.remove('open');
  document.getElementById('infraDrawer').classList.remove('open');
};
document.getElementById('drawerOverlay').addEventListener('click', window.closeInfraDrawer);

// Bootstrap
document.addEventListener('DOMContentLoaded', async ()=>{
  // Carte
  MAP = L.map('countryMap', {
    zoomControl: true,
    center: [4.54, -3.55],
    zoom: window.APP.ZOOM?.minZoom || 6,
    minZoom: window.APP.ZOOM?.minZoom || 6,
    maxZoom: window.APP.ZOOM?.maxZoom || 10,
  });

  // Filtres (remplissage)
  await fetchFilters();

  // Métrique / finance / filtres — événements
  document.getElementById('filtreMetric').addEventListener('change', async (e)=>{
    CUR.metric = e.target.value;
    await reloadStyles();
  });
  document.getElementById('filtreFinance').addEventListener('change', async (e)=>{
    CUR.finance = e.target.value;
    await reloadStyles();
  });
  document.getElementById('btnApply').addEventListener('click', async ()=>{
    CUR.groupe = document.getElementById('filtreGroupe').value || '';
    CUR.dom    = (document.getElementById('filtreDomaine').value||'').substring(0,2);
    CUR.sous   = document.getElementById('filtreSous').value || '';
    await fetchAggregate();
    await reloadStyles();
  });

  // Première charge: tout le monde
  await fetchAggregate();
  MAXL = await detectLevels(window.APP.ALPHA3) || 1;
  applyInfoPanel();
  await loadLegend();
  await loadLevel(1);
});
