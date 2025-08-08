@extends('layouts.app')

@section('content')
<style>

    .info {
        background:rgba(255, 255, 255, 0.57);
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        border-radius: 5px;
    }
    .map{
        padding-right: 200;
    }
    g{
        transform: translate3d(-120px, 20px, 0px);
    }
    .info .title {
    font-weight: bold;
    }

    .info .content {
    font-size: 12px;
    }
    .leaflet-control-zoom{
        display: none;
    }
    .info .close-button {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 10px;
    height: 10px;
    cursor: pointer;
    }

    .info .close-button:hover {
    background-color: #ccc;
    }
    .district-label {
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        color: #000;
        /* Couleur du texte */
    }

    .info.legend {
        background: rgba(255, 255, 255, 0.57); /* Blanc transparent */
        padding: 10px 15px;
        font: 14px Arial, sans-serif;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        border-radius: 5px;
        line-height: 18px;
        color: #333;
    }

    .info.legend h4 {
        margin: 0 0 5px;
        font-size: 16px;
        font-weight: bold;
        color: #000;
    }

    .info.legend p {
        margin: 0 0 10px;
        font-size: 14px;
        color: #555;
    }

    .info.legend i {
        width: 18px;
        height: 18px;
        float: left;
        margin-right: 8px;
        opacity: 0.7; /* Applique la transparence */
        border-radius: 3px; /* Adoucit les bords */
    }




    .leaflet-control-attribution {
        visibility: hidden;
    }
    .did {
        text-align: center;
        justify-content: center;
        align-items: center;
    }
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 6px;
        font-size: 80%;
        color: #dc3545;
        /* Couleur du texte pour les messages d'erreur */
    }
    #status-bailleur {
        margin-top: 33px;
        margin-left: -50px;
        position: relative;
    }

    th:empty {
            border: none; /* Supprime les bordures des cellules vides */
        }
    .wide-column{
        min-width: 100px;
    }

    .leaflet-interactive:focus {
        outline: none;
    }









/* marker & overlays interactivity */
.leaflet-marker-icon,
.leaflet-marker-shadow,
.leaflet-image-layer,
.leaflet-pane > svg path,
.leaflet-tile-container {
	pointer-events: none;
	}

.leaflet-marker-icon.leaflet-interactive,
.leaflet-image-layer.leaflet-interactive,
.leaflet-pane > svg path.leaflet-interactive,
svg.leaflet-image-layer.leaflet-interactive path {
	pointer-events: auto;
	}

/* visual tweaks */



.leaflet-right .leaflet-control {
    margin-right: -10px !important;
}
.leaflet-top .leaflet-control {
    margin-top: -10px !important;
}

/* Drawer */
.drawer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(17, 24, 39, 0.35);
    backdrop-filter: blur(2px);
    opacity: 0;
    visibility: hidden;
    transition: opacity .25s ease, visibility .25s ease;
    z-index: 1049;
}
.drawer {
    position: fixed;
    top: 0;
    right: -50vw; /* caché hors écran */
    width: 50vw; /* moitié de l'écran */
    height: 100vh;
    background: #ffffff;
    box-shadow: -8px 0 24px rgba(0,0,0,.18);
    transition: right .3s ease;
    z-index: 1050;
    display: flex;
    flex-direction: column;
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}
.drawer.open { right: 0; }
.drawer-overlay.open { opacity: 1; visibility: visible; }
.drawer-header {
    padding: 14px 18px;
    border-bottom: 1px solid #e5e7eb;
    display:flex; flex-direction:column; gap:8px;
    background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
}
.drawer-header .row-top{display:flex; align-items:center; justify-content:space-between;}
.drawer-title{font-weight:700; font-size:16px; color:#0f172a;}
.badge { display:inline-block; padding:4px 10px; font-size:12px; border-radius:14px; background:#eef2ff; color:#3730a3; margin-right:6px; border:1px solid #e0e7ff; }
.badge.gray{ background:#f3f4f6; color:#374151; border-color:#e5e7eb; }
.badge.green{ background:#ecfdf5; color:#065f46; border-color:#d1fae5; }
.breadcrumb { font-weight:600; color:#111827; letter-spacing:.2px; }
.breadcrumb small{ color:#6b7280; font-weight:500; }
.drawer-body { padding: 14px 18px; overflow: auto; }
.drawer-close { border: none; background: #ffffff; font-size: 22px; cursor:pointer; width:32px; height:32px; border-radius:8px; line-height: 1; }
.drawer-close:hover{ background:#f3f4f6; }

/* Table styling */
.table thead th{ position: sticky; top: 0; background:#f8fafc; z-index: 1; }
.table tbody tr:hover{ background:#fafafa; }
.table td, .table th{ vertical-align: middle; }
.table a.project-link{ color:#1d4ed8; text-decoration:none; font-weight:600; }
.table a.project-link:hover{ text-decoration:underline; }
</style>
<!-- Inclure le CSS de Toastify -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

<!-- Inclure le JavaScript de Toastify -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

@if (session('success'))
<script>
    alert("{{ session('success') }}");
</script>
@endif
<section id="multiple-column-form">
    <!-- Your existing HTML content -->
    <div class="row match-height">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Visualisation sur la carte</h4>
                    <hr>
                    <!-- Your existing filter form -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col">
                                    <div class="row">
                                        <div class="row">
                                            <div class="col-md-1 col-sm-17"><label for="">Dates:</label></div>
                                            <div class="col">
                                                <h class="col-md-3 col-sm-18"><input type="radio" id="radioButton1" name="radioButtons" value="prévisionnelles" /><label for=""> prévisionnelles</label></h>
                                            </div>
                                            <div class="col">
                                                <h class="col-md-3 col-sm-19"><input type="radio" id="radioButton2" name="radioButtons" value="effectives" /><label for=""> effectives</label></h>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="row">
                                                <div class="col">
                                                    <center>Début</center>
                                                    <input type="date" class="form-control" name="start_date" id="start_date">
                                                </div>
                                                <div class="col">
                                                    <center>Fin</center>
                                                    <input type="date" class="form-control" name="end_date" id="end_date">
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
                                <div class="col-md-20 col-sm-2" style="top: -6px;">
                                    <div class="single-model-search text-center">
                                        <center>
                                            <div class="text-center" style="width:143px; padding: 13px; " >
                                                <h class="col-md-3 col-sm-9"><input type="radio" id="radioButton3" name="radioButtons" value="Tous" /><label >Sans filtre</label></h>
                                            </div>
                                        </center>
                                        <button class="btn btn-secondary" id="filterButton" onclick="window.location.href='#'">
                                            Filtrer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="finance" class="form-control-label">Finance</label>
                                <input type="checkbox" id="financeLayer" onchange="handleCheckboxChange(this.id, 'Finance')">
                            </div>
                            <div class="col">
                                <label for="nombreProjet" class="form-control-label">Nombre de projet</label>
                                <input type="checkbox" id="nombreLayer" onchange="handleCheckboxChange(this.id, 'Nombre')">
                            </div>
                            <div class="col-5 border border-bg-gray-100">
                                <div class="row">
                                    <div class="col">
                                        <label for="nombreProjet" class="form-control-label">Cumul</label>
                                        <input type="checkbox" id="cumulLayer" onchange="handleCheckboxChange(this.id, 'Cumul')">
                                    </div>
                                    <div class="col">
                                        <label for="nombreProjet" class="form-control-label">Privé</label>
                                        <input type="checkbox" id="priveLayer" onchange="handleCheckboxChange(this.id, 'Privé')">
                                    </div>
                                    <div class="col">
                                        <label for="nombreProjet" class="form-control-label">Public</label>
                                        <input type="checkbox" id="publicLayer" onchange="handleCheckboxChange(this.id, 'Public')">
                                    </div>
                                </div>

                            </div>

                            </div>
                    </div>
                </div>

                <div class="card-content">
                    <div class="card-body">
                        <div class="row" style="flex-wrap: nowrap">
                            <div class="col">
                                <div id="countryMap" style="height: 590px; outline-style: none;"></div>
                                <div id="africaMap" style="height: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Drawer Overlay and Panel -->
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
    </div>
    <div class="drawer-body">
        <div class="mb-2 small text-muted" id="drawerMeta"></div>
        <div class="table-responsive">
            <table class="table table-sm table-striped table-bordered mb-0">
                <thead>
                    <tr>
                        <th style="width:18%">Code projet</th>
                        <th>Libellé</th>
                        <th style="width:12%">Type</th>
                        <th style="width:15%">Coût</th>
                        <th style="width:15%">Domaine</th>
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
    window.currentMapFilter = 'cumul'; // cumul, public ou private
    window.currentMapMetric = 'count'; // count ou cost

    // Elements du drawer
    const drawer = document.getElementById('projectDrawer');
    const overlay = document.getElementById('drawerOverlay');
    const drawerTitle = document.getElementById('drawerTitle');
    const drawerMeta = document.getElementById('drawerMeta');
    const drawerTableBody = document.getElementById('drawerTableBody');
    const drawerBreadcrumb = document.getElementById('drawerBreadcrumb');
    const drawerLevel = document.getElementById('drawerLevel');
    const drawerFilter = document.getElementById('drawerFilter');
    const drawerDomain = document.getElementById('drawerDomain');

    function libelleDomaine(code2){
        if(!window.domainesAssocieArr) return 'Tous';
        const found = window.domainesAssocieArr.find(d=> String(d.code).startsWith(String(code2||'')));
        return found ? (found.libelle || code2) : 'Tous';
    }

    function libelleNiveau(level){
        if(!window.niveauArr || !Array.isArray(window.niveauArr)) return `Niveau ${level||''}`;
        const item = window.niveauArr[(level||1)-1];
        return item?.libelle_decoupage || `Niveau ${level}`;
    }

    function breadcrumbFromCode(code){
        // code localisation 2/4/6 chars
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
        // Construit un dictionnaire code->libelle à partir des données déjà chargées côté carte (window.projectData)
        if(window.localiteNamesByCode) return;
        const map = {};
        try{
            // On peut dériver depuis window.projectData qui contient name+code
            if(window.projectData){
                Object.values(window.projectData).forEach(v=>{ if(v?.code && v?.name){ map[String(v.code)] = String(v.name);} });
            }
        }catch(e){}
        window.localiteNamesByCode = map;
    }

    window.openProjectDrawer = async function(params) {
        const { code, domain = '', filter = 'cumul', level } = params || {};
        await ensureLocaliteNames();

        const breadcrumb = breadcrumbFromCode(code);
        const domainLabel = domain ? libelleDomaine(domain) : 'Tous';
        const levelLabel = libelleNiveau(level);

        drawerTitle.textContent = 'Détails des projets';
        drawerBreadcrumb.innerHTML = `${breadcrumb ? breadcrumb : '—'} <small>(${code})</small>`;
        drawerLevel.textContent = levelLabel;
        drawerFilter.textContent = `Filtre: ${filter}`;
        drawerDomain.textContent = `Domaine: ${domainLabel}`;
        drawerMeta.textContent = 'Chargement…';
        drawerTableBody.innerHTML = '<tr><td colspan="5" class="text-center">Chargement…</td></tr>';

        const qs = new URLSearchParams({ code, filter, domain, _: Date.now() }).toString();
        fetch(`/api/project-details?${qs}`)
            .then(r => r.json())
            .then(data => {
                const rows = (data.projects || []).map(p => {
                    const url = `${window.location.origin}/projets/${encodeURIComponent(p.code_projet)}`;
                    return `
                    <tr>
                        <td><a href="${url}" target="_blank" rel="noopener" class="project-link">${p.code_projet}</a></td>
                        <td>${p.libelle_projet ? p.libelle_projet.replace(/</g,'&lt;').replace(/>/g,'&gt;') : ''}</td>
                        <td>${p.is_public ? 'Public' : 'Privé'}</td>
                        <td>${(p.cout_projet || 0).toLocaleString('fr-FR')}</td>
                        <td>${p.code_sous_domaine || ''}</td>
                    </tr>`;
                }).join('');

                drawerMeta.textContent = `${data.count || 0} projet(s)`;
                drawerTableBody.innerHTML = rows || '<tr><td colspan="5" class="text-center">Aucun projet</td></tr>';
            })
            .catch(err => {
                console.error('Erreur chargement détails projets', err);
                drawerMeta.textContent = 'Erreur de chargement';
                drawerTableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Erreur lors du chargement</td></tr>';
            });

        overlay.classList.add('open');
        drawer.classList.add('open');
    }

    window.closeProjectDrawer = function() {
        overlay.classList.remove('open');
        drawer.classList.remove('open');
    }
    overlay.addEventListener('click', window.closeProjectDrawer);

    // Récuprez les él7U%éments d'entrée
    var startDateInput = document.getElementById('start_date');
    var endDateInput = document.getElementById('end_date');
    var statusInput = document.getElementById('status');
    var bailleurInput = document.getElementById('bailleur');



    endDateInput.addEventListener('change', function() {
        // Assurez-vous que la date de fin ne peut pas être antérieure à la date de début
        if (endDateInput.value < startDateInput.value) {
            alert('La date de fin ne peut pas être antérieure à la date de début.');

            endDateInput.value = startDateInput.value; // Réinitialisez la date de fin à la date de début
        }
    });
    // Écoutez les changements dans les champs de formulaire pour sauvegarder les données dans le stockage local

    document.getElementById('filterButton').addEventListener('click', function () {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;
        const status = statusInput.value;
        const bailleur = bailleurInput.value;
        const dateTypeRadio = document.querySelector('input[name="radioButtons"]:checked');

        if (!dateTypeRadio) {
            alert('Veuillez sélectionner une option de date.');
            return;
        }

        const dateType = dateTypeRadio.value;

        if (dateType === 'Tous') {
            // Si "Sans filtre", recharge tous les projets
            fetch(`'{{ url("/")}}/api/projects?country={{ session('pays_selectionne') }}&group={{ session('projet_selectionne') }}`)
                .then(res => res.json())
                .then(projects => {
                    updateMap({ projets: projects });
                });
            return;
        }
        const queryParams = new URLSearchParams({
            start_date: startDate,
            end_date: endDate,
            status: status,
            bailleur: bailleur,
            date_type: dateType,
            _: new Date().getTime()
        }).toString();

        fetch(`'{{ url("/")}}/api/filtrer-projets?${queryParams}`)
            .then(res => res.json())
            .then(data => {
                updateMap(data.projets); // <-- Tes projets filtrés à afficher
                updateBailleurOptions(data.bailleurs);
                updateStatutOptions(data.statuts);
            })
            .catch(err => console.error('Erreur dans le filtre:', err));
    });

    function updateBailleurOptions(bailleurs) {
        const select = document.getElementById('bailleur');
        select.innerHTML = '<option value="">Tous les bailleurs</option>';
        bailleurs.forEach(b => {
            select.innerHTML += `<option value="${b.code_acteur}">${b.nom}</option>`;
        });
    }

    function updateStatutOptions(statuts) {
        const select = document.getElementById('status');
        select.innerHTML = '<option value="">Tous les statuts</option>';
        statuts.forEach(s => {
            select.innerHTML += `<option value="${s.id}">${s.libelle}</option>`;
        });
    }




    function clearCacheAndReload() {
        if ('caches' in window) {
            caches.keys().then(function(names) {
                for (let name of names) {
                    caches.delete(name);
                }
            }).then(function() {
                window.location.reload(true);
            });
        } else {
            window.location.reload(true);
        }
    }
        // Chargez les valeurs précédentes des champs de formulaire depuis le stockage local (s'il y en a)
        window.addEventListener('DOMContentLoaded', function() {
            // Vérifiez s'il y a des données sauvegardées dans le stockage local
            if (localStorage.getItem('formData')) {
                // Parsez les données sauvegardées depuis le stockage local
                var formData = JSON.parse(localStorage.getItem('formData'));

                // Si l'option "Sans filtre" est sélectionnée, ne remplissez pas les champs de formulaire avec les données sauvegardées
                var sansFiltreRadio = document.getElementById('radioButton3');
                if (sansFiltreRadio && sansFiltreRadio.checked) {
                    localStorage.removeItem('formData'); // Supprimez les données sauvegardées
                } else {
                    // Remplissez les champs de formulaire avec les données sauvegardées
                    startDateInput.value = formData.startDate || '';
                    endDateInput.value = formData.endDate || '';
                    statusInput.value = formData.status || '';
                    bailleurInput.value = formData.bailleur || '';

                    // Effacez les données après un certain délai (1 minute)
                    var delayInMilliseconds = 1 * 60 * 1000; // 1 minute en millisecondes
                    setTimeout(function() {
                        localStorage.removeItem('formData');
                    }, delayInMilliseconds);
                }
            }
        });


    function updateMap(data) {
        const filteredProjects = data.projets;

        if (!filteredProjects || filteredProjects.length === 0) {
            alert("Aucun projet trouvé pour ce filtre.");
            return;
        }

        // Remplacer les données projets globales utilisées dans map.js
        window.projectData = processProjectData(filteredProjects);

        // Recharge les styles sur la carte avec les nouveaux projets
        if (typeof window.reloadMapWithNewStyle === 'function') {
            window.reloadMapWithNewStyle();
        }

        // Optionnel : mettre à jour l'info box (droite)
        if (typeof info !== 'undefined') {
            info.update(window.domainesAssocie, window.niveau);
        }

        console.log('✅ Carte mise à jour avec les projets filtrés');
    }



     function changeMapLayerJS(layerType) {
            // Détermine le type d'affichage
        if (layerType === 'Finance') {
            window.currentMapMetric = 'cost';
        } else if (layerType === 'Nombre') {
            window.currentMapMetric = 'count';
        }

        // Rien ici pour filtre (cumul/public/privé), il sera mis à jour ailleurs

        window.reloadMapWithNewStyle?.();
    }



    function handleCheckboxChange(checkboxId, layerType) {
        const filterCheckboxes = {
            'cumulLayer': 'cumul',
            'priveLayer': 'private',
            'publicLayer': 'public'
        };

        const metricCheckboxes = {
            'financeLayer': 'Finance',
            'nombreLayer': 'Nombre'
        };

        // Gestion des cases à cocher de filtre (privé/public/cumul)
        if (filterCheckboxes[checkboxId]) {
            // Désactiver les autres cases de filtre
            Object.keys(filterCheckboxes).forEach(id => {
                if (id !== checkboxId) document.getElementById(id).checked = false;
            });

            // Activer/désactiver le filtre
            const checkbox = document.getElementById(checkboxId);
            if (checkbox.checked) {
                window.currentMapFilter = filterCheckboxes[checkboxId];
            } else {
                window.currentMapFilter = 'cumul'; // Retour au cumul par défaut
                document.getElementById('cumulLayer').checked = true;
            }

            window.reloadMapWithNewStyle?.();
            return;
        }

        // Gestion des cases à cocher de métrique (finance/nombre)
        if (metricCheckboxes[checkboxId]) {
            // Désactiver les autres cases de métrique
            Object.keys(metricCheckboxes).forEach(id => {
                if (id !== checkboxId) document.getElementById(id).checked = false;
            });

            // Activer/désactiver la métrique
            const checkbox = document.getElementById(checkboxId);
            if (checkbox.checked) {
                changeMapLayerJS(metricCheckboxes[checkboxId]);
            } else {
                // Si on désactive les deux, revenir au mode nombre par défaut
                window.currentMapMetric = 'count';
                document.getElementById('nombreLayer').checked = true;
                window.reloadMapWithNewStyle?.();
            }
            return;
        }
    }



</script>
<!-- Inclure le CSS de Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

<!-- Inclure le JavaScript de Leaflet -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<!-- Inclure votre fichier JavaScript -->
<script src="{{ asset('geojsonCode/map.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var countryAlpha3Code = '{{ $codeAlpha3 }}';
        var codeGroupeProjet = '{{ $codeGroupeProjet }}';
        window.codeGroupeProjet = '{{ $codeGroupeProjet }}';

        var domainesAssocie = @json($domainesAssocie);
        var niveau = @json($niveau);
        var codeZoom = @json($codeZoom);
        // Exposition globale pour le drawer
        window.domainesAssocieArr = domainesAssocie;
        window.niveauArr = niveau;

        if (countryAlpha3Code === "AFQ") {
            initAfricaMap(codeZoom); // Pour l’Afrique entière
        } else {
            initCountryMap(countryAlpha3Code, codeZoom, codeGroupeProjet, domainesAssocie, niveau); // Pays individuels
        }
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chroma-js/2.1.0/chroma.min.js"></script>
<script src="{{ asset('leaflet/leaflet.js')}}"></script>
@endsection
