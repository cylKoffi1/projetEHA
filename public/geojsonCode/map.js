function initCountryMap(countryAlpha3Code,codeZoom, codeGroupeProjet, domainesAssocie, niveau) {
    var map = L.map('countryMap', {
        zoomControl: true,
        center: [-6.5, 7],
        maxZoom: codeZoom.maxZoom,
        minZoom: codeZoom.minZoom,
        dragging: true,
        prefix: null
    }).setView([4.54, -3.55], 4);

    // Ajustement pour pousser la carte vers la gauche
    map.panBy([20, 0]);

    var currentLayers = {}; // Pour stocker les couches GeoJSON par niveau
    var selectedLevels = {};
    var selectedLevel = {};
    var maxLevels = 3; // Par défaut, limiter à 3 niveaux

    // Vérifie si le pays est la RDC
    const isRDC = countryAlpha3Code === "COD";
    const isMLI = countryAlpha3Code === "MLI";


    // Échelle des couleurs pour les projets
    const colorScale = ['#ebebb9', '#c9c943', '#6495ed', '#af6eeb', '#32cd32', '#eaff00', '#ffba00', '#ff0000'];

    // Règle pour attribuer une couleur en fonction de projectCount
    function getColor(projectCount) {
        if (projectCount >= 350) return colorScale[7];
        else if (projectCount >= 300) return colorScale[6];
        else if (projectCount >= 250) return colorScale[5];
        else if (projectCount >= 200) return colorScale[4];
        else if (projectCount >= 150) return colorScale[3];
        else if (projectCount >= 100) return colorScale[2];
        else if (projectCount >= 50) return colorScale[1];
        else return colorScale[0];
    }
    // Contrôle d'information
    var info = L.control();

    info.onAdd = function (map) {
        this._div = L.DomUtil.create('div', 'info');
        return this._div;
    };

    info.update = function (domaines = [], niveau = []) {
        if (!Array.isArray(niveau)) {
            console.error("Le paramètre 'niveau' n'est pas un tableau valide.");
            return;
        }

        // Construction des informations fixes pour chaque niveau
        const rows = [];
        const maxVisibleTypes = 3;
        if (isRDC) {
            rows.push(`
                <tr>
                    <th style="text-align: right;">Province:</th>
                    <td>${selectedLevels["Province"] || 'Sélectionnez un niveau'}</td>
                </tr>
                <tr>
                    <th style="text-align: right;">Territoire:</th>
                    <td>${selectedLevels["Territoire"] || 'Sélectionnez un niveau'}</td>
                </tr>
                <tr>
                    <th style="text-align: right;">Ville:</th>
                    <td>${selectedLevels["Ville"] || 'Sélectionnez un niveau'}</td>
                </tr>
            `);
        } else if(isMLI){
            // Initialiser selectedLevels avec des données en dur
            selectedLevel[1] = { type: "Région", name: "Cliquez sur une zone" };
            selectedLevel[2] = { type: "Cercle", name: "Cliquez sur une zone" };
            selectedLevel[3] = { type: "Arrondissement", name: "Cliquez sur une zone" };
            // Générer les lignes pour les types et les noms
            for (let i = 1; i <= maxVisibleTypes; i++) {
                // Vérifier si selectedLevels[i] existe
                const levelData = selectedLevels[i] || selectedLevel[i];
                const type = levelData.type;
                const name = levelData.name;

                rows.push(`
                    <tr>
                        <th style="text-align: right;">${type}:</th>
                        <td>${name}</td>
                    </tr>
                `);
            }


        }else {
            const limitedNiveaux = niveau.slice(0, maxLevels);
            rows.push(...limitedNiveaux.map((n, index) => `
                <tr>
                    <th style="text-align: right;">${n.libelle_decoupage || `Niveau ${index + 1}`}:</th>
                    <td>${selectedLevels[index + 1] || 'Sélectionnez un niveau'}</td>
                </tr>
            `));
        }

        this._div.innerHTML = `
            <table>
                <thead>
                    ${rows.join('')}
                </thead>
            </table>
            <table style="border-collapse: collapse; width: 100%; font-size: 12px;">
                <thead>
                    <!-- Ligne pour le titre % -->
                    <tr>
                        <th colspan="2" style="border: none;"></th>
                        <th colspan="${isMLI ? 6 : isRDC ? 6 : maxLevels * 2}" style="border: 1px solid black; text-align: center;">%</th>
                    </tr>
                    <!-- Ligne pour les colonnes dynamiques -->
                    <tr>
                        <th></th>
                        <th style="border: none;"></th>
                        ${(isMLI
                            ? Object.values(selectedLevels).map(level => level.type || "Type manquant")
                            : isRDC
                            ? ["Province", "Territoire", "Ville"]
                            : niveau.slice(0, maxLevels).map(n => n.libelle_decoupage || '')
                        ).map(name => `
                            <th colspan="2" style="border: 1px solid black; text-align: center; font-size: 12px; width: 50px;">${name}</th>
                        `).join('')}
                    </tr>
                    <!-- Ligne pour les colonnes cumulées Public/Privé -->
                    <tr>
                        <th colspan="1" style="border: 1px solid black; text-align: center;">Domaines</th>
                        <th style="border: 1px solid black; text-align: center;">Total</th>
                        ${(isMLI
                            ? Object.values(selectedLevels).map(() => `
                                <th style="border: 1px solid black; text-align: center;">Public</th>
                                <th style="border: 1px solid black; text-align: center;">Privé</th>
                            `).join("")
                            : isRDC
                            ? ["Province", "Territoire", "Ville"]
                            : niveau.slice(0, maxLevels)
                        ).map(() => `
                            <th style="border: 1px solid black; text-align: center;">Public</th>
                            <th style="border: 1px solid black; text-align: center;">Privé</th>
                        `).join('')}
                    </tr>
                </thead>
                <tbody>
                    <!-- Boucle pour les lignes des domaines -->
                    ${domaines.map(domaine => `
                        <tr>
                            <th style="border: 1px solid black; text-align: right;">${domaine.libelle || ''}</th>
                            <td style="border: 1px solid black; text-align: center;"></td>
                            ${(isMLI
                            ? Object.values(selectedLevels).map(() => `
                                <td style="border: 1px solid black; text-align: center;"></td>
                                <td style="border: 1px solid black; text-align: center;"></td>
                            `).join("")
                            : isRDC ? ["Province", "Territoire", "Ville"] : niveau.slice(0, maxLevels)).map(() => `
                                <td style="border: 1px solid black; text-align: center;"></td>
                                <td style="border: 1px solid black; text-align: center;"></td>
                            `).join('')}
                        </tr>
                    `).join('')}
                </tbody>
            </table>



        `;
    };

    info.addTo(map);

    // Mettre à jour la bulle d'information avec des valeurs initiales
    info.update(domainesAssocie, niveau);

    // Légende pour la carte
     var legend = L.control({ position: 'bottomright' });

     legend.onAdd = function (map) {
        var div = L.DomUtil.create('div', 'info legend');
        var grades = [0, 50, 100, 150, 200, 250, 300, 350];
        var labels = [];

        // Ajouter le titre de la légende
        div.innerHTML = '<h4>LEGENDE</h4><p>Nombre de projet</p>';

        // Boucle pour générer les plages de couleurs avec transparence
        for (var i = 0; i < grades.length; i++) {
            labels.push(
                `<i style="background:${getColor(grades[i] + 1)}; opacity: 0.7;"></i> ${grades[i]}${grades[i + 1] ? `&ndash;${grades[i + 1]}` : '+'}`
            );
        }

        // Ajouter les labels sous le titre
        div.innerHTML += labels.join('<br>');
        return div;
    };


    legend.addTo(map);

    // Charger le niveau 1 par défaut
    function loadGeoJsonLevel(level, parentName = null) {
        var geojsonPath = `${window.location.origin}/geojson/gadm41_${countryAlpha3Code}_${level}.json.js`;

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
                        var projectCount = feature.properties.projectCount || 0; // Remplacez par le bon champ
                        return {
                            weight: 2,
                            color: 'white',
                            fillColor: getColor(projectCount),
                            fillOpacity: 0.7
                        };
                    },
                    onEachFeature: function (feature, layer) {
                        feature.properties.level = level;


                        // Gestion des événements (aucune surbrillance ou rectangle affiché)
                        layer.on({
                            click: function (e) {
                                var feature = e.target.feature;
                                handleClick(feature, level); // Appeler la fonction de gestion du clic
                                if (level < 5) {
                                    var nextNameProperty = `NAME_${level}`;
                                    loadGeoJsonLevel(level + 1, feature.properties[nextNameProperty]);
                                }
                            },
                            mouseover: function (e) {
                                // Pas de mise en surbrillance
                            },
                            mouseout: function (e) {
                                // Pas de réinitialisation du style
                            }
                        });
                    }

                }).addTo(map);

                currentLayers[level] = geoJsonLayer;

                if (level === 1) {
                    map.fitBounds(geoJsonLayer.getBounds());
                }
            }
        };

        script.onerror = function () {
            console.error(`Erreur lors du chargement du fichier GeoJSON pour le niveau ${level}:`, geojsonPath);
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

    // Lors du clic sur un niveau
    function handleClick(feature, level) {
        var nameProperty = `NAME_${level}`;
        var typeProperty = `TYPE_${level}`;
        var name = feature.properties[nameProperty];
        var type = feature.properties[typeProperty];

        // Spécifique à la RDC
        if (isRDC) {
            var typeProperty = `TYPE_${level}`;
            if (feature.properties[typeProperty] === "Province") {
                selectedLevels["Province"] = name;
            } else if (feature.properties[typeProperty] === "Territoire") {
                selectedLevels["Territoire"] = name;
            } else if (feature.properties[typeProperty] === "Ville") {
                selectedLevels["Ville"] = name;
            }
        } else  if (isMLI) {
            // Logique spécifique au Mali
            selectedLevels[level] = { type, name };

            // Réinitialiser les niveaux suivants pour le Mali
            if (level === 1) {
                selectedLevels[2] = { type: "Cercle", name: "Cliquez sur une zone" };
                selectedLevels[3] = { type: "Arrondissement", name: "Cliquez sur une zone" };
            } else if (level === 2) {
                selectedLevels[3] = { type: "Arrondissement", name: "Cliquez sur une zone" };
            }
        } else {
            // Réinitialiser les niveaux supérieurs
            for (let i = level + 1; i <= 3; i++) {
                delete selectedLevels[i];
            }
            selectedLevels[level] = name;
        }

        // Mettre à jour l'affichage
        info.update(domainesAssocie, niveau);
        // Charger les sous-niveaux
        if (level < 5) {
            loadGeoJsonLevel(level + 1, name);
        }
    }

    // Charger les fichiers GeoJSON
    loadGeoJsonLevel(1);
}
