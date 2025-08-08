// map.js - Code complet pour la carte interactive des projets
// Fonction de normalisation améliorée
const normalized = str => {
    if (!str) return '';
    return str.toString() // Au cas où c'est un nombre
              .toLowerCase()
              .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Supprime les accents
              .replace(/\s+/g, ' ')
              .replace(/^région\s+(d[eu']\s+)?/i, '')
              .replace(/^province\s+(d[eu']\s+)?/i, '')
              .replace(/^département\s+(d[eu']\s+)?/i, '')
              .trim();
};
// Constantes pour les noms de propriétés dans les GeoJSON
const PROPERTIES = {

    COD: { // Structure pour la RDC
        1: { name: 'NAME_1', type: 'TYPE_1' }, // Provinces
        2: { name: 'NAME_2', type: 'TYPE_2' }, // Territoires
        3: { name: 'NAME_3', type: 'TYPE_3' }  // Villes
    },
    MLI: { // Structure pour le Mali
        1: { name: 'NAME_1', type: 'Région' },
        2: { name: 'NAME_2', type: 'Cercle' },
        3: { name: 'NAME_3', type: 'Arrondissement' }
    },
    DEFAULT: { // Structure par défaut pour les autres pays
        1: { name: 'NAME_1', type: 'Niveau 1' },
        2: { name: 'NAME_2', type: 'Niveau 2' },
        3: { name: 'NAME_3', type: 'Niveau 3' }
    }
};

function initCountryMap(countryAlpha3Code, codeZoom, codeGroupeProjet, domainesAssocie, niveau) {
    // Initialisation de la carte
    var map = L.map('countryMap', {
        zoomControl: true,
        center: [4.54, -3.55],
        zoom: codeZoom.minZoom,
        maxZoom: codeZoom.maxZoom,
        minZoom: codeZoom.minZoom,
        dragging: true
    });

    window.currentMapMode = 'count';

    // Ajustement pour centrer la carte
    map.panBy([20, 0]);

    // Déterminer la structure de propriétés à utiliser
    const countryProps = PROPERTIES[countryAlpha3Code] || PROPERTIES.DEFAULT;
    const isRDC = countryAlpha3Code === "COD";
    const isMLI = countryAlpha3Code === "MLI";

    // Variables d'état
    var currentLayers = {}; // Couches GeoJSON par niveau
    var selectedLevels = {}; // Niveaux sélectionnés
    window.projectData = {}; // ✅ portée globale
    var maxLevels = 3; // Nombre maximal de niveaux à afficher

    // Initialisation pour le Mali
    if (isMLI) {
        selectedLevels = {
            1: { type: "Région", name: "Cliquez sur une zone" },
            2: { type: "Cercle", name: "Cliquez sur une zone" },
            3: { type: "Arrondissement", name: "Cliquez sur une zone" }
        };
    }

    // Échelle de couleurs avec chroma.js
    const colorScale = chroma.scale(['#ebebb9', '#c9c943', '#6495ed', '#af6eeb', '#32cd32', '#eaff00', '#ffba00', '#ff0000'])
        .domain([0, 350])
        .mode('lab');

    // Contrôle d'information
    var info = L.control({ position: 'topright' });

    info.onAdd = function(map) {
        this._div = L.DomUtil.create('div', 'info');
        this.update(domainesAssocie, niveau);
        return this._div;
    };

    info.update = function(domaines = [], niveau = []) {
        const levelRows = [];

        if (isRDC) {
            levelRows.push(
                `<tr><th style="text-align: right;">Province:</th><td>${selectedLevels["Province"] || '—'}</td></tr>`,
                `<tr><th style="text-align: right;">Territoire:</th><td>${selectedLevels["Territoire"] || '—'}</td></tr>`,
                `<tr><th style="text-align: right;">Ville:</th><td>${selectedLevels["Ville"] || '—'}</td></tr>`
            );
        } else if (isMLI) {
            for (let i = 1; i <= maxLevels; i++) {
                const level = selectedLevels[i] || { type: `Niveau ${i}`, name: '—' };
                levelRows.push(
                    `<tr><th style="text-align: right;">${level.type}:</th><td>${level.name}</td></tr>`
                );
            }
        } else {
            for (let i = 1; i <= Math.min(maxLevels, niveau.length); i++) {
                const levelName = (niveau[i - 1]?.libelle_decoupage) || `Niveau ${i}`;
                const levelValue = typeof selectedLevels[i] === 'object' ? selectedLevels[i].name : selectedLevels[i];
                levelRows.push(
                    `<tr><th style="text-align: right;">${levelName}:</th><td>${levelValue || '—'}</td></tr>`
                );
            }
        }

        // 🧠 Obtenir les données de chaque niveau sélectionné
        const localityDataByLevel = (() => {
            const data = {};
            for (let l = 1; l <= maxLevels; l++) {
                const entry = selectedLevels[l];
                const name = typeof entry === 'object' ? entry.name : entry;
                if (!name) continue;

                const key = normalized(name);
                if (window.projectData[key]) {
                    data[l] = window.projectData[key];
                }
            }
            return data;
        })();

        // 🧮 Total global = niveau le plus bas sélectionné
        const totalProjects = (() => {
            const last = Object.values(localityDataByLevel).pop();
            if (!last) return 0;

            if (window.currentMapMetric === 'cost') {
                const total = (last.public || 0) + (last.private || 0);
                if (window.currentMapFilter === 'private') return (last.cost * ((last.private || 0) / (total || 1))) / 1_000_000_000;
                else if (window.currentMapFilter === 'public') return (last.cost * ((last.public || 0) / (total || 1))) / 1_000_000_000;
                else return last.cost / 1_000_000_000;
            }

            if (window.currentMapFilter === 'private') return last.private || 0;
            if (window.currentMapFilter === 'public') return last.public || 0;
            return last.count || 0;
        })();
        const totalCostPublic = Object.values(localityDataByLevel).reduce((sum, d) => sum + (d?.public_cost || 0), 0);
        const totalCostPrivate = Object.values(localityDataByLevel).reduce((sum, d) => sum + (d?.private_cost || 0), 0);
        const totalCost = totalCostPublic + totalCostPrivate;

        const domainRows = domaines.map(domaine => {
            const domainCode = domaine.code.substring(0, 2);

            return `
                <tr>
                    <th style="border: 1px solid black; text-align: right;">${domaine.libelle}</th>
                    <td style="border: 1px solid black; text-align: center;">
                        ${
                            (() => {
                                let sum = 0;
                                for (const level of Object.values(localityDataByLevel)) {
                                    const stats = level?.byDomain?.[domainCode];
                                    if (!stats) continue;

                                    if (window.currentMapMetric === 'cost') {
                                        if (window.currentMapFilter === 'private') sum += stats.cost * (stats.private / (stats.public + stats.private || 1));
                                        else if (window.currentMapFilter === 'public') sum += stats.cost * (stats.public / (stats.public + stats.private || 1));
                                        else sum += stats.cost;
                                    } else {
                                        if (window.currentMapFilter === 'private') sum += stats.private || 0;
                                        else if (window.currentMapFilter === 'public') sum += stats.public || 0;
                                        else sum += stats.public + stats.private;
                                    }
                                }

                                return window.currentMapMetric === 'cost' ? (sum / 1_000_000_000).toFixed(2) + '' : sum;
                            })()
                        }
                    </td>
                    ${Array.from({ length: maxLevels }, (_, i) => {
                        const levelData = localityDataByLevel[i + 1];
                        const stats = levelData?.byDomain?.[domainCode] || {};

                        if (window.currentMapMetric === 'cost') {
                            const total = (stats.public || 0) + (stats.private || 0);
                            const pubCost = stats.cost && total ? stats.cost * (stats.public / total) : 0;
                            const privCost = stats.cost && total ? stats.cost * (stats.private / total) : 0;

                            return `
                                <td style="border: 1px solid black; text-align: center;">
                                    <span class="project-cell" data-code="${levelData?.code || ''}" data-level="${i+1}" data-filter="public" data-domain="${domainCode}">${window.currentMapFilter === 'private' ? '-' : (pubCost / 1_000_000_000).toFixed(2) + ''}</span>
                                </td>
                                <td style="border: 1px solid black; text-align: center;">
                                    <span class="project-cell" data-code="${levelData?.code || ''}" data-level="${i+1}" data-filter="private" data-domain="${domainCode}">${window.currentMapFilter === 'public' ? '-' : (privCost / 1_000_000_000).toFixed(2) + ''}</span>
                                </td>
                            `;
                        } else {
                            return `
                                <td style="border: 1px solid black; text-align: center;">
                                    <span class="project-cell" data-code="${levelData?.code || ''}" data-level="${i+1}" data-filter="public" data-domain="${domainCode}">${window.currentMapFilter === 'private' ? '-' : (stats.public ?? 0)}</span>
                                </td>
                                <td style="border: 1px solid black; text-align: center;">
                                    <span class="project-cell" data-code="${levelData?.code || ''}" data-level="${i+1}" data-filter="private" data-domain="${domainCode}">${window.currentMapFilter === 'public' ? '-' : (stats.private ?? 0)}</span>
                                </td>
                            `;
                        }
                    }).join('')}
                </tr>
            `;
        }).join('');

        this._div.innerHTML = `
            <div class="title">Informations sur la zone</div>
            <table class="level-info">
                ${levelRows.join('')}
            </table>
            <table class="project-info">
                <thead>
                    <tr>
                        <th colspan="${2 + maxLevels * 2}" style="text-align: center;">Répartition des projets</th>
                    </tr>
                    <tr>
                        <th rowspan="2" style="border: 1px solid black; text-align: center;">Domaines</th>
                        <th rowspan="2" style="border: 1px solid black; text-align: center;">Total</th>
                        <th colspan="${maxLevels * 2}" style="border: 1px solid black; text-align: center;">Répartition par niveau</th>
                    </tr>
                    <tr>
                        ${Array.from({ length: maxLevels }, (_, i) => `
                            <th colspan="2" style="border: 1px solid black; text-align: center;">
                                ${isRDC ? ['Province', 'Territoire', 'Ville'][i] || `Niveau ${i + 1}` :
                                  isMLI ? ['Région', 'Cercle', 'Arrondissement'][i] :
                                  (niveau[i]?.libelle_decoupage) || `Niveau ${i + 1}`}
                            </th>
                        `).join('')}
                    </tr>
                    <tr>
                        <th></th><th></th>
                        ${Array.from({ length: maxLevels }, () => `
                            <th style="border: 1px solid black; text-align: center;">Public</th>
                            <th style="border: 1px solid black; text-align: center;">Privé</th>
                        `).join('')}
                    </tr>
                </thead>
                <tbody>
                    ${domainRows}
                    <tr>
                        <th style="border: 1px solid black; text-align: right;">Total</th>
                        <td style="border: 1px solid black; text-align: center;">
                            ${
                                window.currentMapMetric === 'cost'
                                    ? totalProjects.toFixed(2) + ''
                                    : formatWithSpaces(totalProjects)
                            }
                        </td>


                        ${Array.from({ length: maxLevels }, (_, i) => {
                            const levelData = localityDataByLevel[i + 1];
                            if (window.currentMapMetric === 'cost') {
                                const total = (levelData?.public || 0) + (levelData?.private || 0);
                                const pubCosts = levelData?.cost && total ? levelData.cost * (levelData.public / total) : 0;
                                const privCosts = levelData?.cost && total ? levelData.cost * (levelData.private / total) : 0;
                                const pubCost = pubCosts / 1000000000;
                                const privCost = privCosts / 1000000000;
                                return `
                                    <td style="border: 1px solid black; text-align: center;">
                                        <span class="project-cell" data-code="${levelData?.code || ''}" data-level="${i+1}" data-filter="public">${window.currentMapFilter === 'private' ? '-' : pubCost.toFixed(2) + ' '}</span>
                                    </td>
                                    <td style="border: 1px solid black; text-align: center;">
                                        <span class="project-cell" data-code="${levelData?.code || ''}" data-level="${i+1}" data-filter="private">${window.currentMapFilter === 'public' ? '-' : privCost.toFixed(2) + ' '}</span>
                                    </td>
                                `;
                            } else {
                                return `
                                    <td style="border: 1px solid black; text-align: center;">
                                        <span class="project-cell" data-code="${levelData?.code || ''}" data-level="${i+1}" data-filter="public">${window.currentMapFilter === 'private' ? '-' : (levelData?.public ?? 0)}</span>
                                    </td>
                                    <td style="border: 1px solid black; text-align: center;">
                                        <span class="project-cell" data-code="${levelData?.code || ''}" data-level="${i+1}" data-filter="private">${window.currentMapFilter === 'public' ? '-' : (levelData?.private ?? 0)}</span>
                                    </td>
                                `;
                            }
                        }).join('')}

                </tbody>
            </table>
        `;

        // Binder les clics sur les cellules du tableau pour ouvrir le drawer
        const cells = this._div.querySelectorAll('.project-cell');
        cells.forEach(el => {
            el.style.cursor = 'pointer';
            el.addEventListener('click', () => {
                const code = el.dataset.code;
                const domain = el.dataset.domain || '';
                const filter = el.dataset.filter || 'cumul';
                const level = parseInt(el.dataset.level || '0', 10) || undefined;
                const valueText = (el.textContent || '').trim();
                if (!code || valueText === '-' || valueText === '—') return;
                if (typeof window.openProjectDrawer === 'function') {
                    window.openProjectDrawer({ code, domain, filter, level });
                }
            });
        });
    };


    info.addTo(map);

    function createDynamicLegend(map, groupeCode) {
        // La légende dépend uniquement du type de métrique (coût ou nombre)
        const metric = window.currentMapMetric || 'count';

        let typeFin;
        if (metric === 'cost') {
            typeFin = 2; // 2 = légende pour le financement
        } else {
            typeFin = 1; // 1 = légende pour le nombre de projets
        }

        fetch(`/api/legende/${groupeCode}?typeFin=${typeFin}`)
            .then(response => response.json())
            .then(data => {
                window.customLegend = data.seuils;

                // Supprimer l'ancienne légende si elle existe
                if (window.currentLegendControl) {
                    map.removeControl(window.currentLegendControl);
                }

                const legend = L.control({ position: 'bottomright' });

                legend.onAdd = function (map) {
                    const div = L.DomUtil.create('div', 'info legend');
                    const labels = [`<h4>LÉGENDE</h4><p>${data.label}</p>`];

                    data.seuils.forEach(({ borneInf, borneSup, couleur }) => {
                        labels.push(
                            `<i style="background:${couleur}; opacity: 0.7;"></i> ${borneInf}${borneSup ? `–${borneSup}` : '+'}`
                        );
                    });

                    div.innerHTML = labels.join('<br>');
                    return div;
                };

                legend.addTo(map);
                window.currentLegendControl = legend;
            })
            .catch(err => {
                console.error('Erreur chargement légende dynamique :', err);
            });
    }



    createDynamicLegend(map, codeGroupeProjet);
    // Légende
    /*var legend = L.control({ position: 'bottomright' });

    legend.onAdd = function(map) {
        var div = L.DomUtil.create('div', 'info legend');
        var grades = [0, 50, 100, 150, 200, 250, 300, 350];
        var labels = ['<h4>LEGENDE</h4><p>Nombre de projets</p>'];

        for (var i = 0; i < grades.length; i++) {
            const from = grades[i];
            const to = grades[i + 1];
            const color = colorScale(from + 1).hex();

            labels.push(
                `<i style="background:${color}; opacity: 0.7;"></i> ${from}${to ? `–${to}` : '+'}`
            );
        }

        div.innerHTML = labels.join('<br>');
        return div;
    };

    legend.addTo(map);*/

    // Chargement des données des projets
    loadProjectData(countryAlpha3Code, codeGroupeProjet)
        .then(data => {
            window.projectData = processProjectData(data);
            loadGeoJsonLevel(1); // Charger le premier niveau
        })
        .catch(error => console.error('Error loading project data:', error));

    // Fonction pour charger les données des projets depuis l'API
    function loadProjectData(countryCode, groupCode) {
        return fetch(`/api/projects?country=${countryCode}&group=${groupCode}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            });
    }

    function getParentCode(code) {
        if (code.length === 6) return code.substring(0, 4); // niveau 3 → 2
        if (code.length === 4) return code.substring(0, 2); // niveau 2 → 1
        return null;
    }

    function findParentKey(parentCode, data) {
        if (!parentCode) return null;

        return Object.keys(data).find(key => {
            return data[key].code === parentCode;
        });
    }

    // Traitement des données des projets
    function processProjectData(projects) {
        const data = {};

        projects.forEach(project => {
            const key = normalized(project.name);
            data[key] = {
                name: project.name,
                code: project.code,
                count: project.count,
                cost: project.cost,
                public: project.public,
                private: project.private,
                byDomain: project.byDomain
            };
        });

        window.projectData = data; // ✅ mise à jour directe
        return data;
    }

    window.processProjectData = processProjectData;

    // Obtenir les données de la localité actuellement sélectionnée
    function getCurrentLocalityData() {
        const dataByLevel = {};
        for (let l = 1; l <= maxLevels; l++) {
            const entry = selectedLevels[l];
            const name = typeof entry === 'object' ? entry.name : entry;
            if (!name) continue;

            const key = normalized(name);
            if (window.projectData[key]) {
                dataByLevel[l] = window.projectData[key];
            }
        }
        return dataByLevel;
    }

    // Chargement d'un niveau GeoJSON
    function loadGeoJsonLevel(level, parentName = null) {
        const scriptName = `statesDataLevel${level}`;
        const geojsonPath = `${window.location.origin}/geojson/gadm41_${countryAlpha3Code}_${level}.json.js`;

        return new Promise((resolve, reject) => {
            // Vérifier si le script est déjà chargé
            if (window[scriptName]) {
                resolve(window[scriptName]);
                return;
            }

            const script = document.createElement('script');
            script.src = geojsonPath;

            script.onload = () => {
                if (window[scriptName]) {
                    resolve(window[scriptName]);
                } else {
                    reject(new Error(`Variable ${scriptName} not found after script load`));
                }
            };

            script.onerror = () => {
                reject(new Error(`Failed to load script: ${geojsonPath}`));
            };

            document.head.appendChild(script);
        })
        .then(data => {
            const filteredData = parentName
                ? filterGeoJsonByParent(data, level - 1, parentName)
                : data;

            createGeoJsonLayer(filteredData, level);
        })
        .catch(error => {
            console.error(`Error loading GeoJSON level ${level}:`, error);
        });
    }

    // Filtrer le GeoJSON par parent
    function filterGeoJsonByParent(data, parentLevel, parentName) {
        const parentProp = countryProps[parentLevel]?.name || `NAME_${parentLevel}`;

        return {
            ...data,
            features: data.features.filter(feature =>
                feature.properties[parentProp] === parentName
            )
        };
    }

    // Supprimer les couches au-dessus d'un certain niveau
    function clearLayersAbove(level) {
        for (let l = level; l <= maxLevels; l++) {
            if (currentLayers[l]) {
                map.removeLayer(currentLayers[l]);
                delete currentLayers[l];
            }
        }
    }


    // Créer une couche GeoJSON
    function createGeoJsonLayer(data, level) {
        const layer = L.geoJSON(data, {
            style: feature => getFeatureStyle(feature, level),
            onEachFeature: (feature, layer) => onEachFeature(feature, layer, level)
        });

        layer.addTo(map);
        currentLayers[level] = layer;

        // Ajuster la vue pour le premier niveau
        if (level === 1) {
            map.fitBounds(layer.getBounds());
        }
    }

    // Style des features

    function getFeatureStyle(feature, level) {
        const regionName = feature.properties[countryProps[level]?.name];

        // Utilisation du double paramètre (métrique + filtre)
        const metric = window.currentMapMetric || 'count';   // 'count' ou 'cost'
        const filter = window.currentMapFilter || 'cumul';   // 'public', 'private', 'cumul'

        const value = findProjectStatByRegionName(regionName, metric, filter);

        let fillColor = '#c7bda3'; // Couleur par défaut si aucun match

        if (value > 0 && customLegend.length > 0) {
            const found = customLegend.find(({ borneInf, borneSup }) => {
                if (borneInf !== null && borneInf !== undefined &&
                    (borneSup === null || borneSup === undefined)) {
                    return value >= borneInf;
                } else if (borneInf !== null && borneSup !== null) {
                    return value >= borneInf && value <= borneSup;
                }
                return false;
            });

            if (found) {
                fillColor = found.couleur;
            } else {
                fillColor = '#ff0000'; // fallback rouge si aucun seuil trouvé
            }
        }

        return {
            weight: 1,
            opacity: 1,
            color: 'white',
            fillOpacity: 0.7,
            fillColor: fillColor
        };
    }





    // Modifiez findProjectCountByRegionName pour plus de robustesse
    function findProjectStatByRegionName(regionName, metric = 'count', filter = 'cumul') {
        if (!window.projectData || !regionName) return 0;

        const normName = normalized(regionName);
        const stats = window.projectData[normName];
        if (!stats) return 0;

        const source = filter === 'public' ? stats.public :
                      filter === 'private' ? stats.private :
                      (stats.public || 0) + (stats.private || 0); // cumul

        if (metric === 'count') return source || 0;
        if (metric === 'cost') return ((stats.cost || 0) * (source / stats.count || 1)) / 1_000_000_000;

        return 0;
    }

    function formatWithSpaces(number) {
        return Number(number).toLocaleString('fr-FR');
    }




    function reloadMapWithNewStyle() {
        if (!window.projectData || Object.keys(window.projectData).length === 0) {
            console.warn("❗ Aucun projet à afficher.");
            return;
        }
        for (let l = 1; l <= maxLevels; l++) {
            if (currentLayers[l]) {
                currentLayers[l].eachLayer(layer => {
                    const feature = layer.feature;
                    const newStyle = getFeatureStyle(feature, l);
                    layer.setStyle(newStyle);

                    const regionName = feature.properties[countryProps[l]?.name];
                    const statValue =  findProjectStatByRegionName(regionName, window.currentMapMetric, window.currentMapFilter);

                    const value = window.currentMapMode === 'cost'
                        ? `${formatWithSpaces(statValue * 1_000_000_000)} `
                        : statValue;

                    layer.bindTooltip(`
                        <b>${regionName}</b><br>
                        ${window.currentMapMode === 'count' ? 'Projets' : 'Montant'} : ${value}
                    `);
                });
            }
        }

        createDynamicLegend(map, window.codeGroupeProjet); // ✅ Ajout ici
        info.update(domainesAssocie, niveau);
    }

    window.reloadMapWithNewStyle = reloadMapWithNewStyle;
    console.log(window.window.projectData)

    // Gestion des événements sur chaque feature
    function onEachFeature(feature, layer, level) {
        feature.properties.level = level;
        const regionName = feature.properties[countryProps[level]?.name];
        const statValue = findProjectStatByRegionName(regionName, window.currentMapMetric, window.currentMapFilter);


        layer.on({
            click: e => {
                onFeatureClick(e, level);
                map.closePopup(); // facultatif
                map.fire('click'); // pour virer tout focus visuel
            },
            mouseover: highlightFeature,
            mouseout: resetHighlight
        });

        const value = window.currentMapMode === 'cost'
        ? `${formatWithSpaces(statValue * 1_000_000_000)}`
        : statValue;

        layer.bindTooltip(`
            <b>${regionName}</b><br>
            ${window.currentMapMode === 'count' ? 'Projets' : 'Montant'}: ${value}
        `);


    }

    // Gestion du clic sur une feature
    function onFeatureClick(e, level) {
        const layer = e.target;

        // Réinitialise le style (supprime le "focus" visuel Leaflet)
        if (currentLayers[level]) {
            currentLayers[level].resetStyle(layer);
        }
        const feature = e.target.feature;
        const nameProp = countryProps[level]?.name || `NAME_${level}`;
        const typeProp = countryProps[level]?.type || `TYPE_${level}`;

        const featureName = feature.properties[nameProp];
        const featureType = feature.properties[typeProp] || `Niveau ${level}`;

        // Supprimer tous les niveaux inférieurs du niveau actuel
        clearLayersAbove(level + 1);

        // Mise à jour des niveaux sélectionnés
        if (isRDC) {
            if (featureType === "Province") {
                selectedLevels = {
                    "Province": featureName,
                    "Territoire": null,
                    "Ville": null
                };
            } else if (featureType === "Territoire") {
                selectedLevels["Territoire"] = featureName;
                selectedLevels["Ville"] = null;
            } else if (featureType === "Ville") {
                selectedLevels["Ville"] = featureName;
            }
        } else if (isMLI) {
            selectedLevels[level] = { type: featureType, name: featureName };

            // Réinitialiser uniquement les niveaux inférieurs
            for (let l = level + 1; l <= maxLevels; l++) {
                selectedLevels[l] = {
                    type: ["Région", "Cercle", "Arrondissement"][l-1] || `Niveau ${l}`,
                    name: "Cliquez sur une zone"
                };
            }
        } else {
            selectedLevels[level] = featureName;

            // Supprimer les niveaux inférieurs
            for (let l = level + 1; l <= maxLevels; l++) {
                delete selectedLevels[l];
            }
        }

        // Mettre à jour le panneau d'infos
        info.update(domainesAssocie, niveau);

        // Charger uniquement le niveau suivant
        if (level < maxLevels) {
            loadGeoJsonLevel(level + 1, featureName);
        }
    }


    // Mise en surbrillance au survol
    function highlightFeature(e) {
        const layer = e.target;
        layer.setStyle({
            weight: 3,
            color: '#666',
            fillOpacity: 0.9
        });
        layer.bringToFront();
    }

    // Réinitialisation du style
    function resetHighlight(e) {
        const layer = e.target;
        const level = layer.feature.properties.level;

        if (currentLayers[level]) {
            currentLayers[level].resetStyle(layer);
        }
    }
}

// initAfricaMap.js - Carte interactive pour l'Afrique avec rendu similaire à initCountryMap
function initAfricaMap() {
    const map = L.map('countryMap', {
        center: [0, 20],
        zoom: 3,
        zoomControl: true
    });

    const colorScale = chroma.scale(['#c7bda3', '#c2e699', '#78c679', '#31a354', '#006837', '#004529', '#082c1f', '#02150b'])
        .domain([0, 500, 1000, 1500, 2000, 2500, 3000, 3500])
        .mode('lab');

    let africaData = {}; // Données projet par pays
    let selectedCountry = null;

    const info = L.control({ position: 'topright' });

    info.onAdd = function () {
        this._div = L.DomUtil.create('div', 'info');
        this.update();
        return this._div;
    };

    info.update = function () {
        if (!selectedCountry || !africaData[selectedCountry]) {
            this._div.innerHTML = `
                <div class="title">Informations sur la zone</div>
                <table class="level-info">
                    <tr><th style="text-align: right;">Pays:</th><td>—</td></tr>
                </table>
                <table class="project-info">
                    <thead>
                        <tr>
                            <th colspan="4" style="text-align: center;">Répartition des projets</th>
                        </tr>
                        <tr>
                            <th style="border: 1px solid black; text-align: center;">Groupe</th>
                            <th style="border: 1px solid black; text-align: center;">Total</th>
                            <th style="border: 1px solid black; text-align: center;">Public</th>
                            <th style="border: 1px solid black; text-align: center;">Privé</th>
                        </tr>
                    </thead>
                    <tbody><tr><td colspan="4" style="text-align: center;">Aucune donnée</td></tr></tbody>
                </table>
            `;
            return;
        }

        const data = africaData[selectedCountry];
        const groupRows = Object.entries(data.groupes).map(([code, stats]) => {
            return `
                <tr>
                    <th style="border: 1px solid black; text-align: right;">${code}</th>
                    <td style="border: 1px solid black; text-align: center;">${stats.count}</td>
                    <td style="border: 1px solid black; text-align: center;">${stats.public}</td>
                    <td style="border: 1px solid black; text-align: center;">${stats.private}</td>
                </tr>
            `;
        }).join('');

        this._div.innerHTML = `
            <div class="title">Informations sur la zone</div>
            <table class="level-info">
                <tr><th style="text-align: right;">Pays:</th><td>${data.pays}</td></tr>
            </table>
            <table class="project-info">
                <thead>
                    <tr>
                        <th colspan="4" style="text-align: center;">Répartition du pays</th>
                    </tr>
                    <tr>
                        <th style="border: 1px solid black; text-align: center;">Groupe</th>
                        <th style="border: 1px solid black; text-align: center;">Total</th>
                        <th style="border: 1px solid black; text-align: center;">Public</th>
                        <th style="border: 1px solid black; text-align: center;">Privé</th>
                    </tr>
                </thead>
                <tbody>
                    ${groupRows}
                    <tr>
                        <th style="border: 1px solid black; text-align: right;">Total</th>
                        <td style="border: 1px solid black; text-align: center;">${data.total}</td>
                        <td style="border: 1px solid black; text-align: center;">${data.public}</td>
                        <td style="border: 1px solid black; text-align: center;">${data.private}</td>
                    </tr>
                </tbody>
            </table>
        `;
    };

    info.addTo(map);

    const legend = L.control({ position: 'bottomright' });

    legend.onAdd = function (map) {
        const div = L.DomUtil.create('div', 'info legend');
        const grades = [0, 500, 1000, 1500, 2000, 2500, 3000, 3500];
        const labels = ['<h4>LEGENDE</h4><p>Nombre de projets</p>'];

        for (let i = 0; i < grades.length; i++) {
            const from = grades[i];
            const to = grades[i + 1];
            const color = colorScale(from + 1).hex();
            labels.push(`<i style="background:${color}; opacity: 0.7;"></i> ${from}${to ? `–${to}` : '+'}`);
        }

        div.innerHTML = labels.join('<br>');
        return div;
    };

    legend.addTo(map);

    function normalized(str) {
        if (!str) return '';
        return str.toString()
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    const script = document.createElement('script');
    script.src = `${window.location.origin}/geojson/gadm41_AFQ_1.json.js`;

    script.onload = () => {
        const geojson = window.statesDataLevel1;

        if (!geojson || !geojson.features || geojson.features.length === 0) {
            console.error("GeoJSON Afrique invalide ou vide.");
            return;
        }

        fetch('/api/projects/all')
            .then(res => res.json())
            .then(data => {
                africaData = processAfricaProjectData(data);
                renderAfricaTable(africaData);

                const africaLayer = L.geoJSON(geojson, {
                    style: feature => {
                        const key = normalized(feature.properties.NAME_0);
                        const count = africaData[key]?.total || 0;
                        return {
                            weight: 1,
                            color: '#333',
                            fillOpacity: 0.7,
                            fillColor: count > 0 ? colorScale(count).hex() : '#c7bda3'
                        };
                    },
                    onEachFeature: (feature, layer) => {
                        const name = feature.properties.NAME_0;
                        const displayName = feature.properties.name_long || name;
                        const key = normalized(name);

                        layer.on({
                            click: () => {
                                selectedCountry = key;
                                info.update();
                            },
                            mouseover: () => {
                                selectedCountry = key;
                                info.update();
                                layer.setStyle({ weight: 3, color: '#666', fillOpacity: 0.9 });
                                layer.bringToFront();
                            },
                            mouseout: () => {
                                selectedCountry = null;
                                info.update();
                                layer.setStyle({ weight: 1, color: '#333', fillOpacity: 0.7 });
                            }
                        });

                        layer.bindTooltip(`<b>${displayName}</b>`);
                    }
                }).addTo(map);

                const bounds = africaLayer.getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds);
                } else {
                    console.warn("Les coordonnées du GeoJSON Afrique sont invalides. Impossible de centrer la carte.");
                }
            });
    };

    script.onerror = () => {
        console.error("Erreur lors du chargement du fichier gadm41_AFQ_1.json.js");
    };

    document.head.appendChild(script);

    function processAfricaProjectData(projects) {
        const summary = {};

        projects.forEach(project => {
            const codeProjet = String(project.code_projet || '');
            if (codeProjet.length < 6) return;

            const groupCode = codeProjet.substring(3, 6);
            const isPublic = project.is_public;
            const countryName = project.country_name || codeProjet.substring(0, 3);
            const key = normalized(countryName);

            if (!summary[key]) {
                summary[key] = {
                    pays: countryName,
                    total: 0,
                    public: 0,
                    private: 0,
                    groupes: {}
                };
            }

            summary[key].total++;
            isPublic ? summary[key].public++ : summary[key].private++;

            if (!summary[key].groupes[groupCode]) {
                summary[key].groupes[groupCode] = { count: 0, public: 0, private: 0 };
            }

            summary[key].groupes[groupCode].count++;
            isPublic ? summary[key].groupes[groupCode].public++ : summary[key].groupes[groupCode].private++;
        });

        return summary;
    }

    function renderAfricaTable(data) {
        const container = document.getElementById('africaDataTable');
        if (!container) return;

        const allGroupCodes = new Set();
        Object.values(data).forEach(country => {
            Object.keys(country.groupes).forEach(g => allGroupCodes.add(g));
        });

        const sortedGroups = [...allGroupCodes].sort();

        let html = '<table class="table table-bordered table-striped table-sm">';
        html += '<thead><tr><th rowspan="2">Pays</th><th rowspan="2">Total</th><th rowspan="2">Public</th><th rowspan="2">Privé</th>';
        html += `<th colspan="${sortedGroups.length * 3}" style="text-align: center;">Répartition par groupe projet</th></tr>`;
        html += '<tr>';
        sortedGroups.forEach(group => {
            html += `<th>${group}</th><th>Pub</th><th>Priv</th>`;
        });
        html += '</tr></thead><tbody>';

        for (const key in data) {
            const row = data[key];
            html += `<tr><td>${row.pays}</td><td>${row.total}</td><td>${row.public}</td><td>${row.private}</td>`;
            sortedGroups.forEach(group => {
                const g = row.groupes[group] || { count: 0, public: 0, private: 0 };
                html += `<td>${g.count}</td><td>${g.public}</td><td>${g.private}</td>`;
            });
            html += '</tr>';
        }

        html += '</tbody></table>';
        container.innerHTML = html;
    }
}
