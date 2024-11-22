function initCountryMap(countryAlpha3Code) {
    var map = L.map('countryMap').setView([0, 0], 2);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var currentLayers = {}; // Pour stocker les couches GeoJSON par niveau
    var parentNames = {}; // Noms des entités parent
    var parentTypes = {}; // Types des entités parent

    // Contrôle d'information
    var info = L.control();

    info.onAdd = function (map) {
        this._div = L.DomUtil.create('div', 'info');
        return this._div;
    };

    info.update = function (types, names) {
        // Mise à jour avec TYPE_{level} uniquement
        this._div.innerHTML = `
            <table>
                <thead>
                    ${types.map((type, index) => `
                        <tr>
                            <th style="text-align: left;">${type || 'N/A'}:</th>
                            <td>${names[index] || 'N/A'}</td>
                        </tr>
                    `).join('')}
                </thead>
            </table>
            <table style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nbr</th>
                        <th>District</th>
                        <th>Région</th>
                        <th>Départ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style="text-align: right;">Alimentation en eau potable:</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                    </tr>
                    <tr>
                        <th style="text-align: right;">Assainissement et drainage:</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                    </tr>
                    <tr>
                        <th style="text-align: right;">Hygiène:</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                    </tr>
                    <tr>
                        <th style="text-align: right;">Ressource en eau:</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                    </tr>
                    <tr>
                        <th style="text-align: right;">EHA établissement de Santé:</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                    </tr>
                    <tr>
                        <th style="text-align: right;">EHA établissement d’Enseignement:</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                    </tr>
                    <tr>
                        <th style="text-align: right;">EHA autres Entités:</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                        <th>-</th>
                    </tr>
                </tbody>
            </table>
        `;
    };

    info.addTo(map);

    // Charger le niveau 1 par défaut
    loadGeoJsonLevel(1);

    function loadGeoJsonLevel(level, parentName = null) {
        var geojsonPath = `/geojson/gadm41_${countryAlpha3Code}_${level}.json.js`;

        var script = document.createElement('script');
        script.src = geojsonPath;
        script.onload = function () {
            var statesData = window[`statesDataLevel${level}`];
            if (statesData) {
                var filteredData = statesData;
                if (parentName !== null) {
                    var nameProperty = `NAME_${level - 1}`;
                    filteredData = statesData.features.filter(feature => feature.properties[nameProperty] === parentName);
                }

                // Gestion des couches existantes
                for (var i = level + 1; i <= Object.keys(currentLayers).length; i++) {
                    if (currentLayers[i]) {
                        map.removeLayer(currentLayers[i]);
                        delete currentLayers[i];
                    }
                }

                if (currentLayers[level]) {
                    map.removeLayer(currentLayers[level]);
                    delete currentLayers[level];
                }

                var geoJsonLayer = L.geoJSON(filteredData, {
                    style: function (feature) {
                        return {
                            weight: 2,
                            color: 'white',
                            fillColor: '#87CE01',
                            fillOpacity: 0.7
                        };
                    },
                    onEachFeature: function (feature, layer) {
                        feature.properties.level = level;

                        layer.on({
                            mouseover: function (e) {
                                highlightFeature(e);
                                var types = [];
                                var names = [];

                                // Dynamique TYPE_{level}
                                for (let i = 1; i <= 3; i++) {
                                    const typeKey = `TYPE_${i}`;
                                    const nameKey = `NAME_${i}`;
                                    if (feature.properties[typeKey]) {
                                        types.push(feature.properties[typeKey]);
                                        names.push(feature.properties[nameKey]);
                                    }
                                }

                                info.update(types, names);
                            },
                            mouseout: function (e) {
                                resetHighlight(e);
                            },
                            click: function (e) {
                                var nameProperty = `NAME_${level}`;
                                var parentInfo = parentNames[level - 1] ? ` (${level - 1}: ${parentNames[level - 1]})` : '';
                                var parentType = level > 1 ? feature.properties[`TYPE_${level - 1}`] : 'N/A';
                                console.log(`Niveau ${level} sélectionné: ${feature.properties[nameProperty]}${parentInfo} (Type: ${parentType})`);

                                parentNames[level] = feature.properties[nameProperty];
                                parentTypes[level] = parentType;

                                var types = [
                                    parentTypes[level - 1] || 'N/A',
                                    parentType
                                ];

                                info.update(types, [parentNames[level - 1], feature.properties[nameProperty]]);

                                if (level < 5) {
                                    var nextNameProperty = `NAME_${level}`;
                                    loadGeoJsonLevel(level + 1, feature.properties[nextNameProperty]);
                                }
                            }
                        });
                    }
                }).addTo(map);

                currentLayers[level] = geoJsonLayer;

                if (level === 1) {
                    map.fitBounds(geoJsonLayer.getBounds());
                }

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

        script.onerror = function () {
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
        var level = layer.feature.properties.level;
        if (currentLayers[level]) {
            currentLayers[level].resetStyle(layer);
        }
    }
}
