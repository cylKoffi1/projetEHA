/* eslint-disable */
(function(){
  // ------- helpers
  const nfFR = new Intl.NumberFormat('fr-FR');
  const A = window.APP||{};
  const api = (p)=> (A.API_URL.replace(/\/+$/,'') + '/' + p.replace(/^\/+/,''));
  const gjBase = ()=> A.GEOJSON.replace(/\/+$/,'') + '/';
  const normalized = (s)=> (s||'').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/\s+/g,' ').trim();

  // ------- state
  let map;
  let currentLayers = {};
  let selected = {};            // {1:{name,code},2:...}
  let maxLevels = 3;
  let markersLayer = L.layerGroup(); // overlay for markers

  // ------- icon & color dictionaries
  // Groups → emoji (n1)
  const GROUP_ICONS = {
    'BAT':'🏢','ENE':'⚡','EHA':'🚰','TRP':'🚦','TIC':'📡','AXU':'🏞️','BTP':'🏗️'
  };
  // Domain (2 chars) → emoji (n2)
  const DOMAIN_ICONS = {
    '01':'🏠','02':'🏘️','03':'🏬','04':'🏛️','05':'🎪', // BAT example
    '10':'🛠️','20':'🧪','30':'🛰️'
  };
  // Subdomain (full) → color (n3)
  const SUB_COLORS = new Proxy({}, {
    get(target, code){
      if(!target[code]){
        // deterministic pastel color per code
        let hash=0; for(let i=0;i<code.length;i++){ hash=(hash*31 + code.charCodeAt(i))>>>0; }
        const h = hash % 360, s=70, l=55;
        target[code] = `hsl(${h} ${s}% ${l}%)`;
      }
      return target[code];
    }
  });

  const divIcon = (txt,bg)=> L.divIcon({
    className:'divicon',
    html:`<div class="divicon" style="background:${bg||'#4f46e5'}">${txt}</div>`,
    iconSize:[34,34],
    iconAnchor:[17,17]
  });

  // ------- UI bits
  const lblLevel = document.getElementById('lblLevel');
  const lblZone  = document.getElementById('lblZone');

  const drawer   = document.getElementById('projectDrawer');
  const overlay  = document.getElementById('drawerOverlay');
  const drawerTableBody = document.getElementById('drawerTableBody');
  const drawerMeta = document.getElementById('drawerMeta');
  const drawerSearch = document.getElementById('drawerSearch');
  const drawerBreadcrumb = document.getElementById('drawerBreadcrumb');

  window.closeProjectDrawer = () => { overlay.classList.remove('open'); drawer.classList.remove('open'); };
  overlay.addEventListener('click', window.closeProjectDrawer);

  // ------- Info control
  const info = L.control({position:'topright'});
  info.onAdd = function(){
    this._div = L.DomUtil.create('div','info');
    this.update();
    return this._div;
  };
  info.update = function(){
    const deepest = Object.keys(selected).sort((a,b)=>a-b).pop();
    const sel = deepest ? selected[deepest] : null;
    const levelLabel = deepest ? labelForLevel(+deepest) : '—';
    const zone = sel?.name || '—';
    this._div.innerHTML = `
      <div class="title">Informations</div>
      <div><strong>Niveau :</strong> ${levelLabel}</div>
      <div><strong>Zone :</strong> ${zone}</div>
      <hr class="my-2">
      <small>Les valeurs représentent le <strong>nombre d’infrastructures bénéficiaires</strong>.</small>
    `;
  };

  // ------- Legend control (icons by level)
  let legendCtl = null;
  function renderLegend(level){
    if (legendCtl) { map.removeControl(legendCtl); legendCtl = null; }
    legendCtl = L.control({position:'bottomright'});
    legendCtl.onAdd = function(){
      const div = L.DomUtil.create('div','legend');
      if (A.GROUP !== 'BTP') {
        if (level === 1) {
          div.innerHTML = `<div><strong>Légende</strong><br>Groupes</div>` +
            Object.entries(GROUP_ICONS).map(([k,emo])=>`<div>${emo} — ${k}</div>`).join('');
        } else if (level === 2) {
          div.innerHTML = `<div><strong>Légende</strong><br>Domaines</div>` +
            Object.entries(DOMAIN_ICONS).map(([k,emo])=>`<div>${emo} — Domaine ${k}</div>`).join('');
        } else {
          div.innerHTML = `<div><strong>Légende</strong><br>Sous-domaines</div><small>Couleurs différentes par code</small>`;
        }
      } else {
        // BTP
        if (level === 1) {
          div.innerHTML = `<div><strong>Légende</strong><br>Domaines</div>` +
            Object.entries(DOMAIN_ICONS).map(([k,emo])=>`<div>${emo} — Domaine ${k}</div>`).join('');
        } else {
          div.innerHTML = `<div><strong>Légende</strong><br>Sous-domaines</div><small>Couleurs différentes par code</small>`;
        }
      }
      return div;
    };
    legendCtl.addTo(map);
  }

  // ------- labels
  function labelForLevel(level){
    const item = Array.isArray(A.NIVEAUX) ? A.NIVEAUX.find(n => Number(n.num_niveau_decoupage) === level) : null;
    return item ? item.libelle_decoupage : `Niveau ${level}`;
  }

  // ------- geojson loading
  async function urlExists(src) {
    try {
      const r = await fetch(src, { method:'HEAD' });
      if (r && r.ok) return true;
      const g = await fetch(src, { method:'GET', cache:'no-cache' });
      return !!(g && g.ok);
    } catch { return false; }
  }
  async function detectLevels(alpha3){
    const base = gjBase() + `gadm41_${alpha3}_`;
    const ok1 = await urlExists(`${base}1.json.js`);
    const ok2 = ok1 && await urlExists(`${base}2.json.js`);
    const ok3 = ok2 && await urlExists(`${base}3.json.js`);
    return ok3?3:(ok2?2:(ok1?1:0));
  }
  function getNameKey(props, level){
    const cand = `NAME_${level}`; if (props && cand in props) return cand;
    const keys = Object.keys(props||{});
    const alt = keys.find(k=>/name/i.test(k));
    return alt || cand;
  }

  function loadGeo(level, parentName){
    const varName = `statesDataLevel${level}`;
    const url = gjBase() + `gadm41_${A.ALPHA3}_${level}.json.js`;
    return new Promise((res, rej)=>{
      if (window[varName]) return res(window[varName]);
      const s = document.createElement('script');
      s.src=url; s.async=true;
      s.onload=()=> window[varName]?res(window[varName]):rej(new Error('no var'));
      s.onerror=()=>res(null);
      document.head.appendChild(s);
    }).then(data=>{
      if (!data) return null;
      // filter by parent
      if (parentName) {
        const nameKeyPrev = getNameKey(data.features?.[0]?.properties || {}, level-1);
        const target = normalized(parentName);
        const filtered = { ...data, features:(data.features||[]).filter(f=> normalized(f.properties[nameKeyPrev])===target ) };
        return filtered;
      }
      return data;
    });
  }

  function styleFeature(level, feature){
    return { weight:1, color:'#fff', fillOpacity:.7, fillColor:'#dbeafe' };
  }

  function eachFeature(level, feature, layer){
    const nameKey = getNameKey(feature.properties, level);
    const name = feature.properties[nameKey];

    layer.on({
      click: ()=> onSelect(level, name),
      mouseover: (e)=>{ e.target.setStyle({weight:3,color:'#334155',fillOpacity:.9}); },
      mouseout: (e)=>{ currentLayers[level].resetStyle(e.target); }
    });
    layer.bindTooltip(`<b>${name}</b>`);
  }

  // ------- selection + markers
  async function onSelect(level, name){
    // purge deeper levels
    for(let l=level+1; l<=maxLevels; l++){
      if (currentLayers[l]) { map.removeLayer(currentLayers[l]); delete currentLayers[l]; }
    }
    selected[level] = { name, code: null };
    for(let l=level+1; l<=maxLevels; l++) delete selected[l];

    // load next geojson if exists
    if (level < maxLevels) {
      const gj = await loadGeo(level+1, name);
      if (gj && gj.features && gj.features.length){
        const lyr = L.geoJSON(gj, {
          style: f=>styleFeature(level+1,f),
          onEachFeature: (f,l)=>eachFeature(level+1,f,l)
        });
        lyr.addTo(map);
        currentLayers[level+1] = lyr;
        map.fitBounds(lyr.getBounds());
      }
    }
    lblLevel.textContent = labelForLevel(level);
    lblZone.textContent  = name;
    info.update();
    renderLegend(level);

    // compute prefix code from selected polygon name using our aggregate data
    let prefix = await resolvePrefixFromName(level, name);
    await drawMarkers(level, prefix);
  }

  async function resolvePrefixFromName(level, name){
    // call aggregate to get codes and names once; cache locally
    if (!window.__aggCache){
      const r = await fetch(api('projectsInfras?country='+encodeURIComponent(A.ALPHA3)+'&group='+encodeURIComponent(A.GROUP)));
      window.__aggCache = (await r.json()) || [];
    }
    const entry = window.__aggCache.find(x => (x.level === level && normalized(x.name) === normalized(name)));
    return entry ? entry.code : '';
  }

  async function drawMarkers(level, codePrefix){
    markersLayer.clearLayers();

    const status = document.getElementById('statusInfra').value || 'all';
    const qs = new URLSearchParams({ level, code: codePrefix||'', status }).toString();
    const r = await fetch(api('infrasInfras/markers?'+qs));
    const data = await r.json();

    (data.markers||[]).forEach(m=>{
      let icon;
      if (m.class === 'group'){
        const emo = GROUP_ICONS[m.code] || '🏗️';
        icon = divIcon(emo,'#eef2ff');
      } else if (m.class === 'domain'){
        const emo = DOMAIN_ICONS[m.code] || '📂';
        icon = divIcon(emo,'#ecfeff');
      } else {
        const col = SUB_COLORS[m.code] || '#fde68a';
        icon = divIcon('•', col);
      }
      const mk = L.marker([m.lat, m.lng], { icon });
      mk.bindTooltip(`<b>${m.label}</b><br>${nfFR.format(m.count)} infrastructure(s)`);
      markersLayer.addLayer(mk);
    });

    markersLayer.addTo(map);

    // si pas de niveau > 3, on garde ce zoom confortable
    if (!currentLayers[level+1] && markersLayer.getLayers().length){
      const g = L.featureGroup(markersLayer.getLayers());
      try { map.fitBounds(g.getBounds().pad(0.25)); } catch(_){}
    }
  }

  // ------- drawer search (just client-side on already loaded rows)
  drawerSearch.addEventListener('input', function(){
    const term = (this.value||'').toLowerCase().trim();
    const base = window.__drawerData || [];
    const filtered = !term ? base : base.filter(p =>
      (p.code_projet||'').toLowerCase().includes(term) ||
      (p.libelle_projet||'').toLowerCase().includes(term)
    );
    renderDrawerRows(filtered);
    drawerMeta.textContent = `${filtered.length} projet(s)`;
  });

  function renderDrawerRows(projects){
    if (!projects.length){
      drawerTableBody.innerHTML = `<tr><td colspan="4" class="text-center">Aucun projet</td></tr>`;
      return;
    }
    drawerTableBody.innerHTML = projects.map((p,idx)=>{
      const cout = nfFR.format(p.cout_projet||0) + ' ' + (p.code_devise||'');
      return `<tr>
        <td class="text-center">${idx+1}</td>
        <td>${p.code_projet}</td>
        <td>${(p.libelle_projet||'').replace(/</g,'&lt;')}</td>
        <td class="text-end">${cout}</td>
      </tr>`;
    }).join('');
  }

  // expose open drawer from markers table/legend if needed
  window.openProjectDrawer = async ({code, level, domain='', filter='cumul'}={})=>{
    drawerBreadcrumb.textContent = `${labelForLevel(level)} — ${code}`;
    drawerMeta.textContent = 'Chargement…';
    drawerTableBody.innerHTML = `<tr><td colspan="4" class="text-center">Chargement…</td></tr>`;

    const qs = new URLSearchParams({code, level, domain, filter}).toString();
    const r = await fetch(api('project-detailsInfras?'+qs));
    const data = await r.json();
    window.__drawerData = data.projects || [];
    renderDrawerRows(window.__drawerData);
    drawerMeta.textContent = `${data.count||window.__drawerData.length} projet(s)`;

    overlay.classList.add('open'); drawer.classList.add('open');
  };

  // ------- bootstrap
  async function boot(){
    map = L.map('countryMap', {
      zoomControl: true,
      center: [4.54, -3.55],
      zoom: A.ZOOM?.minZoom || 6,
      maxZoom: A.ZOOM?.maxZoom || 12,
      minZoom: A.ZOOM?.minZoom || 5
    });

    info.addTo(map);
    maxLevels = await detectLevels(A.ALPHA3) || 1;

    // level 1
    const gj = await loadGeo(1);
    if (gj && gj.features && gj.features.length){
      const lyr = L.geoJSON(gj, { style:f=>styleFeature(1,f), onEachFeature:(f,l)=>eachFeature(1,f,l) });
      lyr.addTo(map);
      currentLayers[1] = lyr;
      map.fitBounds(lyr.getBounds());
      renderLegend(1);
      // initial markers: whole country prefix empty -> server regroupe par classes
      await drawMarkers(1, '');
    }

    // bouton filtre
    document.getElementById('btnFilter').addEventListener('click', async ()=>{
      const deepest = Object.keys(selected).sort((a,b)=>a-b).pop();
      const level = deepest ? +deepest : 1;
      const name  = selected[level]?.name || null;
      const prefix = name ? await resolvePrefixFromName(level, name) : '';
      await drawMarkers(level, prefix);
    });
  }

  boot();
})();
