@extends('layouts.app')

@section('content')
<style>
    .info{background:rgba(255,255,255,.57);position:absolute;top:10px;right:10px;padding:10px;box-shadow:0 0 15px rgba(0,0,0,.2);border-radius:5px}
    .map{padding-right:200}
    .mon-svg g{ transform: translate3d(-120px, 20px, 0px); }
    .info .title{font-weight:700}
    .info .content{font-size:12px}
    .leaflet-control-zoom{display:none}
    .info .close-button{position:absolute;top:5px;right:5px;width:10px;height:10px;cursor:pointer}
    .info .close-button:hover{background:#ccc}
    .district-label{font-size:12px;font-weight:700;text-align:center;color:#000}
    .info.legend{background:rgba(255,255,255,.57);padding:10px 15px;font:14px Arial,sans-serif;box-shadow:0 0 15px rgba(0,0,0,.2);border-radius:5px;line-height:18px;color:#333}
    .info.legend h4{margin:0 0 5px;font-size:16px;font-weight:700;color:#000}
    .info.legend p{margin:0 0 10px;font-size:14px;color:#555}
    .info.legend i{width:18px;height:18px;float:left;margin-right:8px;opacity:.7;border-radius:3px}
    .leaflet-control-attribution{visibility:hidden}
    .did{text-align:center;justify-content:center;align-items:center}
    .invalid-feedback{display:block;width:100%;margin-top:6px;font-size:80%;color:#dc3545}
    #status-bailleur{margin-top:33px;margin-left:-50px;position:relative}
    th:empty{border:none}
    .wide-column{min-width:100px}
    .leaflet-interactive:focus{outline:none}

    /* leaflet interactivity */
    .leaflet-marker-icon,.leaflet-marker-shadow,.leaflet-image-layer,.leaflet-pane>svg path,.leaflet-tile-container{pointer-events:none}
    .leaflet-marker-icon.leaflet-interactive,.leaflet-image-layer.leaflet-interactive,.leaflet-pane>svg path.leaflet-interactive,svg.leaflet-image-layer.leaflet-interactive path{pointer-events:auto}

    .leaflet-right .leaflet-control{margin-right:-10px!important}
    .leaflet-top .leaflet-control{margin-top:-10px!important}

    /* Drawer */
    .drawer-overlay{position:fixed;inset:0;background:rgba(17,24,39,.35);backdrop-filter:blur(2px);opacity:0;visibility:hidden;transition:opacity .25s ease,visibility .25s ease;z-index:1049}
    .drawer{position:fixed;top:0;right:-50vw;width:50vw;height:100vh;background:#fff;box-shadow:-8px 0 24px rgba(0,0,0,.18);transition:right .3s ease;z-index:1050;display:flex;flex-direction:column;border-top-left-radius:12px;border-bottom-left-radius:12px}
    .drawer.open{right:0}
    .drawer-overlay.open{opacity:1;visibility:visible}
    .drawer-header{padding:14px 18px;border-bottom:1px solid #e5e7eb;display:flex;flex-direction:column;gap:8px;background:linear-gradient(180deg,#f8fafc 0%,#fff 100%)}
    .drawer-header .row-top{display:flex;align-items:center;justify-content:space-between}
    .drawer-title{font-weight:700;font-size:16px;color:#0f172a}
    .badge{display:inline-block;padding:4px 10px;font-size:12px;border-radius:14px;background:#eef2ff;color:#3730a3;margin-right:6px;border:1px solid #e0e7ff}
    .badge.gray{background:#f3f4f6;color:#374151;border-color:#e5e7eb}
    .badge.green{background:#ecfdf5;color:#065f46;border-color:#d1fae5}
    .breadcrumb{font-weight:600;color:#111827;letter-spacing:.2px}
    .breadcrumb small{color:#6b7280;font-weight:500}
    .drawer-body{padding:14px 18px;overflow:auto}
    .drawer-close{border:none;background:#fff;font-size:22px;cursor:pointer;width:32px;height:32px;border-radius:8px;line-height:1}
    .drawer-close:hover{background:#f3f4f6}
    .table thead th{position:sticky;top:0;background:#f8fafc;z-index:1}
    .table tbody tr:hover{background:#fafafa}
    .table td,.table th{vertical-align:middle}
    .table a.project-link{color:#1d4ed8;text-decoration:none;font-weight:600}
    .table a.project-link:hover{text-decoration:underline}
    .table.table-sm tr td{padding: 8px;}
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

@if (session('success'))
<script> alert(@json(session('success'))); </script>
@endif

<section id="multiple-column-form">
    <div class="row match-height">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Visualisation sur la carte</h4>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col">
                                    <div class="row">
                                        <div class="row">
                                            <div class="col-md-1 col-sm-17"><label>Dates:</label></div>
                                            <div class="col">
                                                <label><input type="radio" id="radioButton1" name="radioButtons" value="prévisionnelles"> prévisionnelles</label>
                                            </div>
                                            <div class="col">
                                                <label><input type="radio" id="radioButton2" name="radioButtons" value="effectives"> effectives</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="row">
                                                <div class="col">
                                                    <center>Début</center>
                                                    <input type="date" class="form-control" id="start_date">
                                                </div>
                                                <div class="col">
                                                    <center>Fin</center>
                                                    <input type="date" class="form-control" id="end_date">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col" id="status-bailleur">
                                    <div class="row">
                                        <div class="row">
                                            <div class="col">
                                                <div>
                                                    <center>Bailleur</center>
                                                    <select class="form-control" id="bailleur" name="bailleur">
                                                        <option value="">Select bailleur</option>
                                                        @foreach ($Bailleurs as $Bailleur)
                                                            <option value="{{ $Bailleur->code_acteur }}">{{ $Bailleur->libelle_court }} {{ $Bailleur->libelle_long }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div>
                                                    <center>Statut</center>
                                                    <select class="form-control" id="status">
                                                        <option value="">Select Status</option>
                                                        @foreach ($TypesStatuts as $statut)
                                                            <option value="{{ $statut->id }}">{{ $statut->libelle }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-20 col-sm-2" style="top:-6px;">
                                    <div class="single-model-search text-center">
                                        <center>
                                            <div class="text-center" style="width:143px;padding:13px;">
                                                <label><input type="radio" id="radioButton3" name="radioButtons" value="Tous"> Sans filtre</label>
                                            </div>
                                        </center>
                                        <button class="btn btn-secondary" id="filterButton" onclick="window.location.href='#'">Filtrer</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col">
                                <label class="form-control-label">Finance</label>
                                <input type="checkbox" id="financeLayer" onchange="handleCheckboxChange(this.id,'Finance')">
                            </div>
                            <div class="col">
                                <label class="form-control-label">Nombre de projet</label>
                                <input type="checkbox" id="nombreLayer" onchange="handleCheckboxChange(this.id,'Nombre')">
                            </div>
                            <div class="col-5 border border-bg-gray-100">
                                <div class="row">
                                    <div class="col">
                                        <label class="form-control-label">Cumul</label>
                                        <input type="checkbox" id="cumulLayer" onchange="handleCheckboxChange(this.id,'Cumul')">
                                    </div>
                                    <div class="col">
                                        <label class="form-control-label">Privé</label>
                                        <input type="checkbox" id="priveLayer" onchange="handleCheckboxChange(this.id,'Privé')">
                                    </div>
                                    <div class="col">
                                        <label class="form-control-label">Public</label>
                                        <input type="checkbox" id="publicLayer" onchange="handleCheckboxChange(this.id,'Public')">
                                    </div>
                                </div>
                            </div>
                        </div> <!-- row -->
                    </div> <!-- card-header -->
                </div>

                <div class="card-content">
                    <div class="card-body">
                        <div class="row" style="flex-wrap:nowrap">
                            <div class="col">
                                <div id="countryMap" style="height:590px;outline-style:none;"></div>
                                <div id="africaMap" style="height:auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- card -->
        </div>
    </div>
</section>

<!-- Drawer -->
<div id="drawerOverlay" class="drawer-overlay"></div>
<div id="projectDrawer" class="drawer" role="dialog" aria-modal="true">
    <div class="drawer-header">
        <div class="row-top">
            <span id="drawerTitle" class="drawer-title">Détails des projets</span>
            <button class="drawer-close" type="button" onclick="window.closeProjectDrawer()" aria-label="Fermer">×</button>
        </div>
        <div class="breadcrumb" id="drawerBreadcrumb">—</div>
        <div>
            <span class="badge gray" id="drawerLevel">Niveau —</span>
            <span class="badge" id="drawerFilter">Filtre: cumul</span>
            <span class="badge green" id="drawerDomain">Domaine: Tous</span>
        </div>

        <!-- 🔎 Barre de recherche -->
        <div class="mt-2">
            <input type="text" id="drawerSearch" class="form-control" placeholder="Rechercher (code projet, libellé)...">
        </div>
    </div>

    <div class="drawer-body">
        <div class="mb-2 small text-muted" id="drawerMeta"></div>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width:5%">#</th>
                        <th style="width:18%">Code projet</th>
                        <th>Libellé</th>
                        <th style="width:15%">Coût</th>
                    </tr>
                </thead>

                <tbody id="drawerTableBody">
                    <tr><td colspan="5" class="text-center">Sélectionnez une cellule du tableau pour voir les détails…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Coche cumul et nombre par défaut
    document.getElementById('cumulLayer').checked = true;
    document.getElementById('nombreLayer').checked = true;
    window.currentMapFilter = 'cumul'; // cumul | public | private
    window.currentMapMetric = 'count'; // count | cost

    // Drawer elements
    const drawer = document.getElementById('projectDrawer');
    const overlay = document.getElementById('drawerOverlay');
    const drawerTitle = document.getElementById('drawerTitle');
    const drawerMeta = document.getElementById('drawerMeta');
    const drawerTableBody = document.getElementById('drawerTableBody');
    const drawerBreadcrumb = document.getElementById('drawerBreadcrumb');
    const drawerLevel = document.getElementById('drawerLevel');
    const drawerFilter = document.getElementById('drawerFilter');
    const drawerDomain = document.getElementById('drawerDomain');
    const drawerSearch = document.getElementById('drawerSearch');

    // Formatter montants FR
    const nfFR = new Intl.NumberFormat('fr-FR');

    function libelleDomaine(code2){
        if(!window.domainesAssocieArr) return 'Tous';
        const found = window.domainesAssocieArr.find(d => String(d.code).startsWith(String(code2||'')));
        return found ? (found.libelle || code2) : 'Tous';
    }
    function libelleNiveau(level){
        if(!window.niveauArr || !Array.isArray(window.niveauArr)) return `Niveau ${level||''}`;
        const item = window.niveauArr[(level||1)-1];
        return item?.libelle_decoupage || `Niveau ${level}`;
    }
    function breadcrumbFromCode(code){
        if(!code) return '—';
        const parts = [];
        const mapNames = window.localiteNamesByCode || {};
        const L1 = code.substring(0,2);
        const L2 = code.length >=4 ? code.substring(0,4) : null;
        const L3 = code.length >=6 ? code.substring(0,6) : null;
        if(L1) parts.push(mapNames[L1]||L1);
        if(L2) parts.push(mapNames[L2]||L2);
        if(L3) parts.push(mapNames[L3]||L3);
        return parts.join(' › ');
    }
    async function ensureLocaliteNames(){
        if(window.localiteNamesByCode) return;
        const map = {};
        try{
            if(window.projectData){
                Object.values(window.projectData).forEach(v=>{
                    if(v?.code && v?.name){ map[String(v.code)] = String(v.name); }
                });
            }
        }catch(e){}
        window.localiteNamesByCode = map;
    }
    function getLocaliteNameByCode(code) {
        if (!code) return '';
        if (!window.localiteNamesByCode) return '';
        return window.localiteNamesByCode[String(code)] || '';
    }

    // ⬇️ helper: rend le tableau du drawer
    function renderDrawerRows(projects){
        if(!projects || projects.length === 0){
            drawerTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Aucun projet</td></tr>';
            return;
        }
        const rows = projects.map((p, idx)=>{
            const url   = `${window.location.origin}/projets/${encodeURIComponent(p.code_projet)}`;
            const cout  = nfFR.format(p.cout_projet || 0);
            const dev   = p.code_devise || '';
        
            return `
            <tr>
                <td class="text-center">${idx + 1}</td>
                <td><a href="${url}" target="_blank" rel="noopener" class="project-link">${p.code_projet}</a></td>
                <td>${(p.libelle_projet || '').replace(/</g,'&lt;').replace(/>/g,'&gt;')}</td>
                <td class="text-end" style="width: 21%">${cout} ${dev}</td>
            </tr>`;
        }).join('');

        drawerTableBody.innerHTML = rows;
    }


    // 🔍 recherche live dans le drawer
    drawerSearch.addEventListener('input', function(){
        const term = this.value.trim().toLowerCase();
        const base = window.drawerProjects || [];
        if(!term){ renderDrawerRows(base); return; }
        const filtered = base.filter(p =>
            (p.code_projet && p.code_projet.toLowerCase().includes(term)) ||
            (p.libelle_projet && p.libelle_projet.toLowerCase().includes(term))
        );
        renderDrawerRows(filtered);
        drawerMeta.textContent = `${filtered.length} projet(s) (filtré)`;
    });

    // Ouverture du drawer (appelée depuis la carte)
    window.openProjectDrawer = async function(params){
        const { code, domain = '', filter = 'cumul', level } = params || {};
        await ensureLocaliteNames();

        const breadcrumb = breadcrumbFromCode(code);
        const domainLabel = domain ? libelleDomaine(domain) : 'Tous';
        const levelLabel = libelleNiveau(level);
        let filters = 'private';

        if (filter === 'private'){
            filters = 'privé'; 
        }else if (filter === 'public'){
            filters = 'public';
        }

        drawerTitle.textContent = 'Détails des projets';
        drawerBreadcrumb.innerHTML = `${breadcrumb ? breadcrumb : '—'} <small>(${code})</small>`;
        const zoneName = (window.localiteNamesByCode && window.localiteNamesByCode[code]) || '';
        drawerLevel.textContent = `${levelLabel} : ${zoneName}`;

        drawerLevel.textContent = `${levelLabel} : ${zoneName}`;
        drawerFilter.textContent = `Filtre: ${filters}`;
        drawerDomain.textContent = `Domaine: ${domainLabel}`;
        drawerMeta.textContent = 'Chargement…';
        drawerTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Chargement…</td></tr>';
        drawerSearch.value = ''; // reset recherche

        const qs = new URLSearchParams({ code, filter, domain, _: Date.now() }).toString();
        fetch(`/api/project-details?${qs}`)
            .then(r => r.json())
            .then(data => {
                window.drawerProjects = data.projects || [];
                renderDrawerRows(window.drawerProjects);
                drawerMeta.textContent = `${data.count || window.drawerProjects.length} projet(s)`;
            })
            .catch(err => {
                console.error('Erreur chargement détails projets', err);
                drawerMeta.textContent = 'Erreur de chargement';
                drawerTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erreur lors du chargement</td></tr>';
            });

        overlay.classList.add('open');
        drawer.classList.add('open');
    };
    window.closeProjectDrawer = function(){
        overlay.classList.remove('open');
        drawer.classList.remove('open');
    };
    overlay.addEventListener('click', window.closeProjectDrawer);

    // Inputs filtre
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const statusInput = document.getElementById('status');
    const bailleurInput = document.getElementById('bailleur');

    endDateInput.addEventListener('change', function(){
        if (endDateInput.value && startDateInput.value && endDateInput.value < startDateInput.value) {
            alert('La date de fin ne peut pas être antérieure à la date de début.');
            endDateInput.value = startDateInput.value;
        }
    });

    // Bouton Filtrer (si utilisé)
    document.getElementById('filterButton').addEventListener('click', function () {
        const startDate = startDateInput.value || '';
        const endDate   = endDateInput.value   || '';
        const status    = statusInput.value    || '';
        const bailleur  = bailleurInput.value  || '';

        const dateTypeRadio = document.querySelector('input[name="radioButtons"]:checked');
        if (!dateTypeRadio) { alert('Veuillez sélectionner une option de date.'); return; }
        const dateType = dateTypeRadio.value; // 'prévisionnelles' | 'effectives' | 'Tous'

        if (dateType === 'Tous') {
            fetch(`{{ url('/') }}/api/projects?country={{ session('pays_selectionne') }}&group={{ session('projet_selectionne') }}`)
                .then(res => res.json())
                .then(data => updateMap({ projets: data }))
                .catch(console.error);
            return;
        }

        const query = new URLSearchParams({
            start_date: startDate,
            end_date: endDate,
            status,
            bailleur,
            date_type: dateType,
            _: Date.now()
        }).toString();

        fetch(`{{ url('/') }}/api/filtrer-projets?${query}`)
            .then(res => res.json())
            .then(data => {
                updateMap(data); // data.projets = agrégat
                // (Optionnel) désactivation d’options indisponibles
                disableUnavailableOptions('bailleur', data.bailleurs);
                disableUnavailableOptions('status',   data.statuts);
            })
            .catch(err => console.error('Erreur filtre:', err));
    });

    function disableUnavailableOptions(selectId, allowedValues){
        const sel = document.getElementById(selectId);
        const allowed = new Set((allowedValues || []).map(v => String(v.code_acteur ?? v.id ?? '')));
        [...sel.options].forEach((opt, i) => {
            if (i === 0) { opt.disabled = false; return; } // "Tous ..."
            opt.disabled = allowed.size > 0 && !allowed.has(opt.value);
        });
    }

    // Mise à jour de la carte avec l’agrégat
    function updateMap(data){
        const filteredProjects = data.projets;
        if (!filteredProjects || filteredProjects.length === 0) {
            alert("Aucun projet trouvé pour ce filtre.");
            return;
        }
        window.projectData = processProjectData(filteredProjects);
        if (typeof window.reloadMapWithNewStyle === 'function') {
            window.reloadMapWithNewStyle();
        }
        if (typeof info !== 'undefined') {
            info.update(window.domainesAssocie, window.niveau);
        }
        console.log('✅ Carte mise à jour avec les projets filtrés');
    }

    // couches carte
    function changeMapLayerJS(layerType){
        if (layerType === 'Finance') window.currentMapMetric = 'cost';
        else if (layerType === 'Nombre') window.currentMapMetric = 'count';
        window.reloadMapWithNewStyle?.();
    }
    function handleCheckboxChange(checkboxId){
        const filterCheckboxes = {'cumulLayer':'cumul','priveLayer':'private','publicLayer':'public'};
        const metricCheckboxes = {'financeLayer':'Finance','nombreLayer':'Nombre'};

        if (filterCheckboxes[checkboxId]) {
            Object.keys(filterCheckboxes).forEach(id => { if (id !== checkboxId) document.getElementById(id).checked = false; });
            const checkbox = document.getElementById(checkboxId);
            window.currentMapFilter = checkbox.checked ? filterCheckboxes[checkboxId] : 'cumul';
            if (!checkbox.checked) document.getElementById('cumulLayer').checked = true;
            window.reloadMapWithNewStyle?.();
            return;
        }
        if (metricCheckboxes[checkboxId]) {
            Object.keys(metricCheckboxes).forEach(id => { if (id !== checkboxId) document.getElementById(id).checked = false; });
            const checkbox = document.getElementById(checkboxId);
            if (checkbox.checked) changeMapLayerJS(metricCheckboxes[checkboxId]);
            else {
                window.currentMapMetric = 'count';
                document.getElementById('nombreLayer').checked = true;
                window.reloadMapWithNewStyle?.();
            }
        }
    }
</script>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
  window.APP = {
    BASE_URL:  "{{ url('/') }}",
    API_URL:   "{{ url('/api') }}",
    GEOJSON:   "{{ url('/geojson') }}"
  };
</script>

<script src="{{ asset('geojsonCode/map.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var countryAlpha3Code = '{{ $codeAlpha3 }}';
        var codeGroupeProjet = '{{ $codeGroupeProjet }}';
        window.codeGroupeProjet = '{{ $codeGroupeProjet }}';

        var domainesAssocie = @json($domainesAssocie);
        var niveau = @json($niveau);
        var codeZoom = @json($codeZoom);

        window.domainesAssocieArr = domainesAssocie;
        window.niveauArr = niveau;

        if (countryAlpha3Code === "AFQ") {
            initAfricaMap(codeZoom);
        } else {
            initCountryMap(countryAlpha3Code, codeZoom, codeGroupeProjet, domainesAssocie, niveau);
        }
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chroma-js/2.1.0/chroma.min.js"></script>
<script src="{{ asset('leaflet/leaflet.js')}}"></script>
@endsection
