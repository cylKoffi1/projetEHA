@extends('layouts.app')

@section('content')
<style>
    #map {
        height: 700px;
        width: 100%;
    }
    
    .info-panel {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
        max-width: 300px;
    }
    
    .legend {
        position: absolute;
        bottom: 30px;
        right: 10px;
        z-index: 1000;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        margin: 5px 0;
    }
    
    .legend-color {
        width: 20px;
        height: 20px;
        margin-right: 10px;
        border-radius: 50%;
    }
    
    .infrastructure-popup img {
        max-width: 100%;
        max-height: 200px;
        margin-bottom: 10px;
    }
    
    .filter-controls {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 1000;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
</style>

<div id="map"></div>

<div class="info-panel">
    <h4>Filtres</h4>
    <div id="famille-filters">
        @foreach($familles as $famille)
            <div>
                <input type="checkbox" 
                       id="famille-{{ $famille->code_famille }}" 
                       value="{{ $famille->code_famille }}" 
                       checked
                       class="famille-filter">
                <label for="famille-{{ $famille->code_famille }}">
                    {{ $famille->libelleFamille }}
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="legend">
    <h4>Légende</h4>
    <div id="legend-items"></div>
</div>

<!-- Inclure Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<!-- Inclure Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<!-- Inclure Leaflet.markercluster -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser la carte
        const map = L.map('map').setView([10, -5], 6);
        
        // Ajouter la couche de tuiles OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Variables pour stocker les marqueurs et les clusters
        let markers = [];
        let markerClusters = {};
        let familleColors = {};
        
        // Charger les couleurs des familles
        fetch('/api/infrastructures/familles-colors')
            .then(response => response.json())
            .then(colors => {
                familleColors = colors;
                updateLegend();
                loadInfrastructures();
            });
        
        // Charger les infrastructures
        function loadInfrastructures() {
            fetch('/api/infrastructures/geojson')
                .then(response => response.json())
                .then(data => {
                    // Créer un groupe de marqueurs par famille
                    Object.keys(familleColors).forEach(familleCode => {
                        markerClusters[familleCode] = L.markerClusterGroup();
                    });
                    
                    // Créer les marqueurs pour chaque infrastructure
                    data.features.forEach(feature => {
                        const familleCode = feature.properties.famille_code;
                        const color = familleColors[familleCode]?.color || '#3388ff';
                        
                        // Créer un marqueur personnalisé
                        const marker = L.marker(
                            [feature.geometry.coordinates[1], feature.geometry.coordinates[0]], 
                            {
                                icon: L.divIcon({
                                    className: 'custom-marker',
                                    html: `<div style="background-color:${color}; width: 15px; height: 15px; border-radius: 50%; border: 2px solid white;"></div>`,
                                    iconSize: [20, 20]
                                })
                            }
                        );
                        
                        // Ajouter un popup avec les informations de l'infrastructure
                        let popupContent = `
                            <div class="infrastructure-popup">
                                <h4>${feature.properties.libelle}</h4>
                                <p><strong>Famille:</strong> ${feature.properties.famille}</p>
                                <p><strong>Localisation:</strong> ${feature.properties.localisation}</p>
                                <p><strong>Code:</strong> ${feature.properties.code}</p>
                        `;
                        
                        if (feature.properties.photo) {
                            popupContent += `<img src="${feature.properties.photo}" alt="Photo de l'infrastructure">`;
                        }
                        
                        popupContent += `</div>`;
                        
                        marker.bindPopup(popupContent);
                        
                        // Stocker le marqueur
                        markers.push({
                            marker: marker,
                            famille: familleCode
                        });
                        
                        // Ajouter le marqueur au cluster correspondant
                        if (markerClusters[familleCode]) {
                            markerClusters[familleCode].addLayer(marker);
                        }
                    });
                    
                    // Ajouter tous les clusters à la carte initialement
                    Object.values(markerClusters).forEach(cluster => {
                        map.addLayer(cluster);
                    });
                });
        }
        
        // Mettre à jour la légende
        function updateLegend() {
            const legendItems = document.getElementById('legend-items');
            legendItems.innerHTML = '';
            
            Object.entries(familleColors).forEach(([code, data]) => {
                const item = document.createElement('div');
                item.className = 'legend-item';
                item.innerHTML = `
                    <div class="legend-color" style="background-color:${data.color}"></div>
                    <span>${data.libelle}</span>
                `;
                legendItems.appendChild(item);
            });
        }
        
        // Gérer les filtres
        document.querySelectorAll('.famille-filter').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const familleCode = this.value;
                const isChecked = this.checked;
                
                if (isChecked) {
                    // Afficher les marqueurs de cette famille
                    if (markerClusters[familleCode]) {
                        map.addLayer(markerClusters[familleCode]);
                    }
                } else {
                    // Masquer les marqueurs de cette famille
                    if (markerClusters[familleCode]) {
                        map.removeLayer(markerClusters[familleCode]);
                    }
                }
            });
        });
        
        // Bouton pour tout sélectionner/désélectionner
        const selectAllBtn = document.createElement('button');
        selectAllBtn.textContent = 'Tout sélectionner';
        selectAllBtn.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.famille-filter');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
                cb.dispatchEvent(new Event('change'));
            });
            
            selectAllBtn.textContent = allChecked ? 'Tout sélectionner' : 'Tout désélectionner';
        });
        
        document.querySelector('.info-panel').prepend(selectAllBtn);
    });
</script>
@endsection