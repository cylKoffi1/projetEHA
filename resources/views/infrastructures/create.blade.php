@extends('layouts.app')

@section('content')
<style>
    #map {
        height: 300px;
        width: 100%;
    }

    .card-infrastructure {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: none;
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, #3a7bd5 0%, #00d2ff 100%);
        color: white;
        padding: 1.5rem;
        border-bottom: none;
    }
    
    .section-title {
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 1px solid #eee;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
        position: relative;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 60px;
        height: 3px;
        background: #3a7bd5;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
        border-radius: 6px;
        padding: 0.6rem 1rem;
        border: 1px solid #e0e0e0;
        transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #3a7bd5;
        box-shadow: 0 0 0 0.25rem rgba(58, 123, 213, 0.15);
    }
    
    .image-upload-container {
        margin-top: 1rem;
    }    
    
    .caracteristique-card {
        background-color: #f8fafc;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        border-left: 4px solid #3a7bd5;
    }
    #galleryPreview .btn-danger {
        background-color: rgba(220, 53, 69, 0.9);
        border: none;
        font-weight: bold;
        font-size: 1.2rem;
        line-height: 1;
        padding: 0.2rem 0.6rem;
    }

    .btn-primary {
        background-color: #3a7bd5;
        border-color: #3a7bd5;
        padding: 0.6rem 1.5rem;
        border-radius: 6px;
        font-weight: 500;
    }
    
    .btn-outline-secondary {
        border-radius: 6px;
        padding: 0.6rem 1.5rem;
        font-weight: 500;
    }
    
    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 500;
        border: none;
        padding: 0.75rem 1.5rem;
    }
    
    .nav-tabs .nav-link.active {
        color: #3a7bd5;
        background: transparent;
        border-bottom: 3px solid #3a7bd5;
    }
    
    .tab-content {
        padding: 1.5rem 0;
    }
    
    .map-container {
        height: 300px;
        border-radius: 8px;
        overflow: hidden;
        margin-top: 1rem;
        border: 1px solid #e0e0e0;
    }
    
    .coordinates-input {
        position: relative;
    }
    
    .coordinates-input .btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        padding: 0.25rem 0.5rem;
    }
    
    .characteristics-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .characteristics-table td, .characteristics-table th {
        vertical-align: middle;
    }
</style>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-12">
                <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Nouvelle infrastructure</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="">Infrastructures</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Nouvelle infrastructure</li>

                    </ol>
                </nav>
                <div class="row">
                    <script>
                        setInterval(function() {
                            document.getElementById('date-now').textContent = getCurrentDate();
                        }, 1000);

                        function getCurrentDate() {
                            // Implémentez la logique pour obtenir la date actuelle au format souhaité
                            var currentDate = new Date();
                            return currentDate.toLocaleString(); // Vous pouvez utiliser une autre méthode pour le formatage
                        }

                    </script>

                </div>
            </div>
        </div>
    </div>
</div>
<section id="multiple-column-form">
    <div class="row match-height">
        <div class="col-12">
            <div class="card card-infrastructure">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title text-white mb-0">Formulaire de création</h4>
                        <div class="col-2">
                            @foreach ($pays as $alpha3 => $nom_fr_fr)
                                <input type="text" value="{{ $nom_fr_fr }}" class="form-control" readonly>
                                <input type="hidden" value="{{ $alpha3 }}" id="paysSelect">
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="card-content">
                    <div class="card-body">
                        <form id="infrastructure-form" 
                            method="POST" 
                            action="{{ isset($infrastructure) ? route('infrastructures.update', $infrastructure->id) : route('infrastructures.store') }}" 
                            enctype="multipart/form-data">
                            
                            @csrf
                            @if(isset($infrastructure))
                                @method('PUT')
                            @endif

                            <!-- Onglets -->
                            <ul class="nav nav-tabs" id="infraTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general" type="button">Informations générales</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#localisation" type="button">Localisation</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#caracteristiques" type="button">Caractéristiques</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#media" type="button">Média</button>
                                </li>
                            </ul>

                            <!-- Contenu des onglets -->
                            <div class="tab-content mt-3" id="infraTabsContent">

                                <!-- Onglet Informations générales -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Domaine *</label>
                                            <select class="form-select" name="domaine" id="domaineSelect" required>
                                                <option value="">Sélectionner un domaine</option>
                                                @foreach ($domaines as $domaine)
                                                    <option value="{{ $domaine->code }}"
                                                        {{ (old('domaine') ?? $infrastructure->familleInfrastructure->code_domaine ?? '') == $domaine->code ? 'selected' : '' }}>
                                                        {{ $domaine->libelle }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Famille *</label>
                                            <select class="form-select" id="code_famille_infrastructure" name="code_famille_infrastructure" required>
                                                <option value="">Sélectionner une famille</option>
                                                @if(isset($infrastructure))
                                                    <option 
                                                        value="{{ $infrastructure->code_famille_infrastructure }}" 
                                                        data-idfamille="{{ $infrastructure->familleInfrastructure->code_famille }}" 
                                                        selected>
                                                        {{ $infrastructure->familleInfrastructure->libelleFamille ?? 'Famille inconnue' }}
                                                    </option>
                                                @endif

                                            </select>
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label">Nom de l'infrastructure *</label>
                                            <input type="text" class="form-control" id="libelle" name="libelle"
                                                value="{{ old('libelle', $infrastructure->libelle ?? '') }}" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Date de création</label>
                                            <input type="date" class="form-control" id="date_operation" name="date_operation"
                                                value="{{ old('date_operation', $infrastructure->date_operation ?? '') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Onglet Localisation -->
                                <div class="tab-pane fade" id="localisation" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Localité *</label>
                                            <lookup-select name="code_localite" id="niveau1Select" required>
                                                @if(isset($infrastructure))
                                                    <option value="{{ $infrastructure->code_localite }}" selected>
                                                        {{ $infrastructure->localisation->libelle ?? 'Localité inconnue' }}
                                                    </option>
                                                @endif
                                            </lookup-select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Niveau</label>
                                            <select id="niveau2Select" class="form-select" disabled>
                                                @if(isset($infrastructure))
                                                    <option selected>{{ old('niveau', $infrastructure->localisation->id_niveau ?? '') }}</option>
                                                @else
                                                    <option value="">Sélectionnez un niveau</option>
                                                @endif
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Découpage</label>
                                            <select id="niveau3Select" class="form-select" disabled>
                                                @if(isset($infrastructure))
                                                    <option selected>{{ old('code_decoupage', $infrastructure->localisation->decoupage->libelle_decoupage ?? '') }}</option>
                                                @else
                                                    <option value="">Sélectionnez un découpage</option>
                                                @endif
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Latitude</label>
                                            <input type="text" class="form-control" name="latitude" id="latitude"
                                                value="{{ old('latitude', $infrastructure->latitude ?? '') }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Longitude</label>
                                            <input type="text" class="form-control" name="longitude" id="longitude"
                                                value="{{ old('longitude', $infrastructure->longitude ?? '') }}">
                                        </div>

                                        <div class="col-12">
                                            <div class="map-container" id="map"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Onglet Caractéristiques -->
                                <div class="tab-pane fade" id="caracteristiques" role="tabpanel">
                                    <div id="caracteristiquesHeriteesSection">
                                        <h5 class="section-title">Caractéristiques héritées</h5>
                                        <div id="caracteristiquesHeriteesContainer" class="row">
                                            <div class="col-12 text-center py-4">
                                                <p class="text-muted">Sélectionnez d'abord une famille pour voir ses caractéristiques</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Onglet Média -->
                                <div class="tab-pane fade" id="media" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label">Galerie de photos</label>
                                        <input type="file" id="galleryInput" name="gallery[]" multiple accept="image/*" class="form-control">
                                        <div id="galleryPreview" class="row mt-3" style="gap: 10px;"></div>
                                    </div>
                                </div>

                            </div>

                            <!-- Champs cachés -->
                            <input type="hidden" id="niveau" name="niveau" value="{{ old('niveau', $infrastructure->localisation->niveau ?? '') }}">
                            <input type="hidden" id="code_decoupage" name="code_decoupage" value="{{ old('code_decoupage', $infrastructure->localisation->code_decoupage ?? '') }}">

                            <!-- Boutons -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('infrastructures.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i> {{ isset($infrastructure) ? 'Mettre à jour' : 'Créer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
{{--<script>
    const caracteristiquesSpecifiques = [];

    document.getElementById('typeCaracSpec').addEventListener('change', function () {
        const typeText = this.selectedOptions[0].text.toLowerCase();
        const valeurInput = document.getElementById('valeurCaracSpec');
        document.getElementById('valeursPossiblesBloc').style.display = (typeText === 'liste') ? 'block' : 'none';
        document.querySelectorAll('.uniteBloc').forEach(el => el.style.display = (typeText === 'nombre') ? 'block' : 'none');

        // Adapter type champ
        if (typeText === 'nombre') {
            valeurInput.type = 'number';
        } else if (typeText === 'boolean') {
            valeurInput.type = 'checkbox';
        } else {
            valeurInput.type = 'text';
        }
    });

    function ajouterCaracSpec() {
        const typeId = document.getElementById('typeCaracSpec').value;
        const typeLabel = document.getElementById('typeCaracSpec').selectedOptions[0].text;
        const libelle = document.getElementById('libelleCaracSpec').value.trim();
        const valeursPossibles = document.getElementById('valeursPossiblesCaracSpec').value.trim();
        const valeurInput = document.getElementById('valeurCaracSpec');
        const valeur = valeursExistantes[carac.id] ?? valeursExistantes[String(carac.id)] ?? null;
        const unite = document.getElementById('uniteCaracSpec').value.trim();
        const symbole = document.getElementById('symboleCaracSpec').value.trim();

        if (!libelle || !typeId) {
            alert("Champs obligatoires manquants.",'warning');
            return;
        }

        const carac = {
            libelle,
            type_id: typeId,
            type_label: typeLabel,
            valeur: valeur,
            valeurs_possibles: typeLabel.toLowerCase() === 'liste' ? valeursPossibles.split(',').map(v => v.trim()) : [],
            unite_libelle: typeLabel.toLowerCase() === 'nombre' ? unite : null,
            unite_symbole: typeLabel.toLowerCase() === 'nombre' ? symbole : null
        };

        caracteristiquesSpecifiques.push(carac);
        document.getElementById('caracteristiques_specifiques_json').value = JSON.stringify(caracteristiquesSpecifiques);
        afficherCaracSpec();
        resetCaracSpecForm();
    }

    function afficherCaracSpec() {
        const tbody = document.querySelector('#tableCaracSpec tbody');
        tbody.innerHTML = '';
        caracteristiquesSpecifiques.forEach((carac, index) => {
            tbody.innerHTML += `
                <tr>
                    <td>${carac.libelle}</td>
                    <td>${carac.type_label}</td>
                    <td>${carac.valeur}</td>
                    <td>${carac.unite_symbole || ''}</td>
                    <td><button class="btn btn-sm btn-danger" onclick="supprimerCaracSpec(${index})">Supprimer</button></td>
                </tr>`;
        });
    }

    function supprimerCaracSpec(index) {
        caracteristiquesSpecifiques.splice(index, 1);
        document.getElementById('caracteristiques_specifiques_json').value = JSON.stringify(caracteristiquesSpecifiques);
        afficherCaracSpec();
    }

    function resetCaracSpecForm() {
        document.getElementById('libelleCaracSpec').value = '';
        document.getElementById('valeursPossiblesCaracSpec').value = '';
        document.getElementById('valeurCaracSpec').value = '';
        document.getElementById('uniteCaracSpec').value = '';
        document.getElementById('symboleCaracSpec').value = '';
    }

</script>--}}
<script>
    const valeursExistantes = @json($valeursExistantes ?? []);
    console.log(valeursExistantes);
    function generateInputField(carac, valeur = null) {
        const nameAttr = `caracteristiques[${carac.id}]`;
        let inputHtml = '';

        switch (carac.type_label.toLowerCase()) {
            case 'liste':
                inputHtml = `<select name="${nameAttr}" class="form-select">`;
                inputHtml += `<option value="">Sélectionner</option>`;
                carac.valeurs_possibles.forEach(val => {
                    const selected = valeur == val ? 'selected' : '';
                    inputHtml += `<option value="${val}" ${selected}>${val}</option>`;
                });
                inputHtml += `</select>`;
                break;

            case 'nombre':
                inputHtml = `
                    <div class="input-group">
                        <input type="number" step="any" name="${nameAttr}" class="form-control" placeholder="Ex: 12.5" value="${valeur ?? ''}">
                        ${carac.unite_symbole ? `<span class="input-group-text">${carac.unite_symbole}</span>` : ''}
                    </div>`;
                break;

            case 'texte':
                inputHtml = `<input type="text" name="${nameAttr}" class="form-control" value="${valeur ?? ''}" placeholder="Entrer du texte">`;
                break;

            case 'boolean':
                const checked = valeur == 1 ? 'checked' : '';
                inputHtml = `
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="${nameAttr}" value="1" id="carac_${carac.id}" ${checked}>
                        <label class="form-check-label" for="carac_${carac.id}">Oui</label>
                    </div>`;
                break;

            default:
                inputHtml = `<input type="text" name="${nameAttr}" class="form-control" value="${valeur ?? ''}">`;
        }

        return inputHtml;
    }


</script>

<script>

// Gestion du changement de domaine pour charger les familles
document.getElementById('domaineSelect').addEventListener('change', function() {
    const codeDomaine = this.value;
    const familleSelect = document.getElementById('code_famille_infrastructure');
    
    familleSelect.innerHTML = '<option value="">Chargement...</option>';
    familleSelect.disabled = true;

    if (codeDomaine) {
        fetch(`{{ url("/")}}/familles-by-domaine/${codeDomaine}`)
            .then(res => res.json())
            .then(data => {
                familleSelect.innerHTML = '<option value="">Sélectionner une famille</option>';
                data.forEach(famille => {
                    const option = document.createElement('option');
                    option.value = famille.code_famille;
                    option.textContent = famille.libelleFamille;
                    option.dataset.idfamille = famille.idFamille;
                    familleSelect.appendChild(option);
                });
                familleSelect.disabled = false;
            })
            .catch(() => {
                familleSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                familleSelect.disabled = false;
            });
    } else {
        familleSelect.innerHTML = '<option value="">Sélectionner une famille</option>';
        familleSelect.disabled = false;
    }
});

// Gestion des caractéristiques héritées
document.getElementById('code_famille_infrastructure').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const idFamille = selectedOption.dataset.idfamille;
    const container = document.getElementById('caracteristiquesHeriteesContainer');

    if (!idFamille) {
        container.innerHTML = '<div class="col-12 text-center py-4"><p class="text-muted">Sélectionnez une famille pour voir ses caractéristiques</p></div>';
        return;
    }
    
    fetch(`{{ url('/') }}/famille/${idFamille}/caracteristiques`)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                container.innerHTML = '<div class="col-12 text-center py-4"><p class="text-muted">Cette famille n\'a pas de caractéristiques définies</p></div>';
                return;
            }

            let html = '';
            console.log("Liste des caractéristiques reçues :", data);
            console.log("Valeurs existantes : ", valeursExistantes);

            data.forEach(carac => {
                html += `
                    <div class="col-md-4">
                        <div class="caracteristique-card">
                            <label class="form-label">${carac.libelle}</label>
                            ${generateInputField(carac, valeursExistantes[carac.id] ?? null)}
                        </div>
                    </div>`;
            });


            container.innerHTML = html;
        })
        .catch(err => {
            console.error('Erreur:', err);
            container.innerHTML = '<div class="col-12 text-center py-4"><p class="text-danger">Erreur lors du chargement des caractéristiques</p></div>';
        });
});


// Gestion de la localisation
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
                document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
                updateMap(position.coords.latitude, position.coords.longitude);
            },
            error => {
                alert('Impossible d\'obtenir votre position : ' + error.message, 'error');
            }
        );
    } else {
        alert('La géolocalisation n\'est pas supportée par votre navigateur', 'error');
    }
}

// Initialisation de la carte (Leaflet.js serait nécessaire)
function initMap() {
    // Initialiser la carte ici
    console.log('Map would be initialized here');
}

function updateMap(lat, lng) {
    // Mettre à jour la carte avec les nouvelles coordonnées
    console.log('Map would be updated to:', lat, lng);
}

let map;
let marker;

function initMap() {
    const paysCode = document.getElementById('paysSelect').value;
    const nomPays = `{!! $nomPays !!}`; // Ce que tu as déjà
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);

    if (!isNaN(lat) && !isNaN(lng)) {
        map = L.map('map').setView([lat, lng], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18,
        }).addTo(map);
        marker = L.marker([lat, lng]).addTo(map);
        return;
    }

    if (!nomPays) {
        // Si aucun nom, on centre sur Afrique
        map = L.map('map').setView([0, 20], 3); // Vue Afrique entière
        return;
    }

    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(nomPays)}`)
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);
                const zoom = 6;

                map = L.map('map').setView([lat, lon], zoom);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 18,
                }).addTo(map);
            } else {
                // Si pays introuvable : fallback
                map = L.map('map').setView([0, 20], 3);
            }
        })
        .catch(err => {
            console.error("Erreur lors de la localisation du pays :", err);
            map = L.map('map').setView([0, 20], 3);
        });
}


function updateMap(lat, lng) {
    if (!map) return;

    const latNum = parseFloat(lat);
    const lngNum = parseFloat(lng);

    if (!isNaN(latNum) && !isNaN(lngNum)) {
        map.setView([latNum, lngNum], 12);

        // Supprimer l'ancien marqueur si présent
        if (marker) {
            map.removeLayer(marker);
        }

        marker = L.marker([latNum, lngNum]).addTo(map);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    initMap(); // Initialiser la carte

    const currentLocaliteId = '{{ $infrastructure->code_localite ?? '' }}';
    const currentLocaliteLabel = '{{ $infrastructure->localisation->libelle ?? '' }}';
    const selectEl = document.getElementById("niveau1Select");

    if (currentLocaliteId && currentLocaliteLabel && selectEl && typeof selectEl.setOptions === 'function') {
        fetch(`{{ url('/') }}/get-localites/${paysCode}`)
            .then(res => res.json())
            .then(data => {
                const options = data.map(localite => ({
                    value: localite.id,
                    text: localite.libelle,
                }));

                // Injecte manuellement la localité si absente
                if (!options.some(opt => opt.value == currentLocaliteId)) {
                    options.unshift({
                        value: currentLocaliteId,
                        text: currentLocaliteLabel
                    });
                }

                selectEl.setOptions(options);
                selectEl.setSelected(currentLocaliteId);
            });
    }



    


    const paysCode = document.getElementById('paysSelect').value;

    if (paysCode) {
        fetch(`{{ url('/') }}/get-localites/${paysCode}`)
            .then(res => res.json())
            .then(data => {
                const options = data.map(localite => ({
                    value: localite.id,
                    text: localite.libelle,
                    codeRattachement: localite.code_rattachement,
                    nom: localite.libelle
                }));

                const selectEl = document.getElementById("niveau1Select");
                selectEl.setOptions(options);

                selectEl.addEventListener('change', async function () {
                    const selectedValue = this.value;
                    const selectedOption = options.find(opt => opt.value == selectedValue);
                    const localiteName = selectedOption?.nom;
                    const pays = `{!! $nomPays !!}`;

                    if (!localiteName || !pays) return;

                    try {
                        const query = encodeURIComponent(`${localiteName}, ${pays}`);
                        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${query}`;

                        const response = await fetch(url, { headers: { 'Accept-Language': 'fr' } });
                        const results = await response.json();

                        if (results.length > 0) {
                            const first = results[0];
                            document.getElementById('latitude').value = parseFloat(first.lat).toFixed(6);
                            document.getElementById('longitude').value = parseFloat(first.lon).toFixed(6);
                            updateMap(first.lat, first.lon);
                        } else {
                            alert("Coordonnées introuvables pour cette localité.", 'error');
                        }
                    } catch (err) {
                        console.error("Erreur lors de la recherche des coordonnées :", err);
                        alert("Erreur lors de la récupération des coordonnées.", 'error');
                    }
                });

                selectEl.addEventListener("change", function () {
                    const localiteId = this.value;
                    if (localiteId) {
                        fetch(`{{ url('/') }}/get-decoupage-niveau/${localiteId}`)
                            .then(res => res.json())
                            .then(data => {
                                document.getElementById("niveau2Select").innerHTML = `<option value="${data.niveau}">${data.niveau}</option>`;
                                document.getElementById("niveau3Select").innerHTML = `<option value="${data.code_decoupage}">${data.libelle_decoupage}</option>`;

                                document.getElementById("niveau2Select").disabled = false;
                                document.getElementById("niveau3Select").disabled = false;

                                document.getElementById("niveau").value = data.niveau;
                                document.getElementById("code_decoupage").value = data.code_decoupage;
                            })
                            .catch(err => {
                                console.error('Erreur récupération découpage:', err);
                                document.getElementById("niveau2Select").innerHTML = `<option value="">Erreur</option>`;
                                document.getElementById("niveau3Select").innerHTML = `<option value="">Erreur</option>`;
                            });
                    }
                });
            });
    }
        // 🛠️ Si on est en édition, forcer le chargement des caractéristiques héritées
        const familleSelect = document.getElementById('code_famille_infrastructure');
        if (familleSelect && familleSelect.value) {
            console.log('code_famille',familleSelect);
                familleSelect.dispatchEvent(new Event('change'));
            
        }
});






// Forcer le redimensionnement de Leaflet à l'ouverture de l'onglet "Localisation"
document.querySelector('button[data-bs-target="#localisation"]').addEventListener('shown.bs.tab', function () {
    if (map) {
        map.invalidateSize();
    }
});

</script>
<script>
let selectedGalleryFiles = [];

function addToGallery(input) {
    const newFiles = Array.from(input.files);

    newFiles.forEach(file => {
        if (!file.type.startsWith('image/')) return;

        const id = `img_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;
        selectedGalleryFiles.push({ id, file });

        const reader = new FileReader();
        reader.onload = function (e) {
            const container = document.createElement('div');
            container.classList.add('col-md-2', 'mb-3');
            container.id = id;
            container.innerHTML = `
                <div class="position-relative">
                    <img src="${e.target.result}" class="img-thumbnail" style="width: 100%; height: 150px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeFromGallery('${id}')">
                        &times;
                    </button>
                </div>
                @if(isset($infrastructure) && $infrastructure->images)
                    <div class="row mb-3">
                        @foreach($infrastructure->images as $image)
                            <div class="col-md-2 mb-2 position-relative">
                                <img src="{{ asset($image->chemin_image) }}" class="img-thumbnail" style="height: 150px; object-fit: cover;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeFromGallery('${id}')">
                                    &times;
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

            `;
            document.getElementById('galleryPreview').appendChild(container);
        };
        reader.readAsDataURL(file);
    });

    // Réinitialiser le champ pour permettre de sélectionner à nouveau les mêmes fichiers
    input.value = '';
}

function removeFromGallery(id) {
    selectedGalleryFiles = selectedGalleryFiles.filter(item => item.id !== id);
    const imgDiv = document.getElementById(id);
    if (imgDiv) imgDiv.remove();
}

document.getElementById('infrastructure-form').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    selectedGalleryFiles.forEach((item, index) => {
        formData.append(`gallery[${index}]`, item.file);
    });

    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Enregistrement...';

    fetch(form.action, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(async response => {
        if (!response.ok) throw await response.json();
        return response.json();
    })
    .then(data => {
        alert(data.success || 'Enregistrement réussi');
        if (data.redirect) {
            window.location.href = data.redirect;
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Enregistrer l\'infrastructure';

        if (error.errors) {
            let messages = Object.values(error.errors).flat().join('\n');
            alert("Erreur(s) :\n" + messages, 'error');
        } else {
            alert("Erreur : " + (error.message || 'Une erreur inconnue est survenue'), 'error');
        }
    });
});
</script>

@endsection