function initCountryMap(countryAlpha3Code) {
    var map = L.map('countryMap').setView([0, 0], 2); // Initialisation avec une vue globale

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Charger le niveau 1 par défaut
    loadGeoJsonLevel(1);

    function loadGeoJsonLevel(level, parentId = null) {
        var geojsonPath = `/geojson/gadm41_${countryAlpha3Code}_${level}.json.js`;

        // Créer un élément script pour charger le fichier .json.js
        var script = document.createElement('script');
        script.src = geojsonPath;
        script.onload = function() {
            var statesData = window[`statesDataLevel${level}`];
            if (statesData) {
                var filteredData = statesData;
                if (parentId !== null) {
                    // Filtrer les données pour ne charger que les sous-niveaux du parent sélectionné
                    filteredData = statesData.features.filter(feature => feature.properties.parentId === parentId);
                }

                var geoJsonLayer = L.geoJSON(filteredData, {
                    onEachFeature: function(feature, layer) {
                        layer.on('click', function() {
                            // Supprimer les couches existantes avant de charger les nouvelles
                            map.eachLayer(function (layer) {
                                if (layer instanceof L.GeoJSON) {
                                    map.removeLayer(layer);
                                }
                            });

                            if (level < 5) { // Assurez-vous que le niveau maximum est respecté
                                loadGeoJsonLevel(level + 1, feature.properties.id); // Charger le niveau suivant
                            }
                        });
                    }
                }).addTo(map);

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
}
