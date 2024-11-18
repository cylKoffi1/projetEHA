function initCountryMap(countryAlpha3Code) {
    var map = L.map('countryMap').setView([0, 0], 2); // Initialisation avec une vue globale

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var currentLayers = {}; // Stocker les couches GeoJSON par niveau
    var parentNames = {}; // Stocker les noms des parents pour chaque niveau

    // Charger le niveau 1 par défaut
    loadGeoJsonLevel(1);

    function loadGeoJsonLevel(level, parentName = null) {
        var geojsonPath = `/geojson/gadm41_${countryAlpha3Code}_${level}.json.js`;

        // Créer un élément script pour charger le fichier .json.js
        var script = document.createElement('script');
        script.src = geojsonPath;
        script.onload = function() {
            var statesData = window[`statesDataLevel${level}`];
            if (statesData) {
                var filteredData = statesData;
                if (parentName !== null) {
                    // Filtrer les données pour ne charger que les sous-niveaux du parent sélectionné
                    var nameProperty = `NAME_${level - 1}`;
                    filteredData = statesData.features.filter(feature => feature.properties[nameProperty] === parentName);
                }

                // Supprimer tous les sous-niveaux existants si un niveau supérieur est sélectionné
                for (var i = level; i <= Object.keys(currentLayers).length; i++) {
                    if (currentLayers[i]) {
                        map.removeLayer(currentLayers[i]);
                        delete currentLayers[i];
                        console.log(`Sous-niveau ${i} supprimé`);
                    }
                }

                var geoJsonLayer = L.geoJSON(filteredData, {
                    style: function(feature) {
                        return {
                            weight: 2,
                            color: 'white',
                            fillColor: '#87CE01', // Couleur de remplissage par défaut
                            fillOpacity: 0.7
                        };
                    },
                    onEachFeature: function(feature, layer) {
                        // Assurez-vous que chaque feature a une propriété 'level'
                        feature.properties.level = level;

                        layer.on({
                            mouseover: function(e) {
                                highlightFeature(e);
                            },
                            mouseout: function(e) {
                                resetHighlight(e);
                            },
                            click: function(e) {
                                var nameProperty = `NAME_${level}`;
                                var parentInfo = parentNames[level - 1] ? ` (${level - 1}: ${parentNames[level - 1]})` : '';
                                console.log(`Niveau ${level} sélectionné: ${feature.properties[nameProperty]}${parentInfo}`);

                                // Mettre à jour le nom du parent pour le niveau suivant
                                parentNames[level] = feature.properties[nameProperty];

                                if (level < 5) { // Assurez-vous que le niveau maximum est respecté
                                    var nextNameProperty = `NAME_${level}`;
                                    loadGeoJsonLevel(level + 1, feature.properties[nextNameProperty]); // Charger le niveau suivant
                                }
                            }
                        });
                    }
                }).addTo(map);

                currentLayers[level] = geoJsonLayer; // Stocker la couche pour le niveau

                if (level === 1) {
                    map.fitBounds(geoJsonLayer.getBounds()); // Centrer la carte sur le niveau 1
                }

                // Afficher une notification Toastify pour chaque niveau chargé
                Toastify({
                    text: `Niveau ${level} chargé avec succès`,
                    duration: 3000,
                    gravity: "top",
                    position: 'right',
                    style: {
                        background: "linear-gradient(to right, #00b09b, #96c93d)",
                    }
                }).showToast();
            } else {
                console.error(`Erreur: Les données pour le niveau ${level} n'ont pas été chargées correctement.`);
                Toastify({
                    text: `Erreur lors du chargement du niveau ${level}`,
                    duration: 3000,
                    gravity: "top",
                    position: 'right',
                    style: {
                        background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                    }
                }).showToast();
            }
        };
        script.onerror = function() {
            console.error(`Erreur lors du chargement du fichier GeoJSON pour le niveau ${level}:`, geojsonPath);
            Toastify({
                text: `Erreur lors du chargement du fichier pour le niveau ${level}`,
                duration: 3000,
                gravity: "top",
                position: 'right',
                style: {
                    background: "linear-gradient(to right, #ff5f6d, #ffc371)",
                }
            }).showToast();
        };
        document.head.appendChild(script);
    }

    function highlightFeature(e) {
        var layer = e.target;
        layer.setStyle({
            weight: 3,
            color: '#666',
            dashArray: '',
            fillOpacity: 0.7
        });
        layer.bringToFront();
    }

    function resetHighlight(e) {
        var layer = e.target;
        var level = layer.feature.properties.level; // Assurez-vous que chaque feature a une propriété 'level'
        if (currentLayers[level]) {
            currentLayers[level].resetStyle(layer);
        }
    }
}
