// Initialisation de la carte Leaflet
var map;

function initMapFina() {
    if(map){
        map.remove();
    }
    mapFina = L.map('map', {
        zoomControl: false,
        center: [-6.5, 7],
        maxZoom: 6.95,
        minZoom: 6.95,
        dragging: false
    }).setView([7.54, -5.55], 7);
    mapFina.panBy([120, 0]);


    // Ajout d'une couche GeoJSON pour les régions
    var statesDataRegionsGeoJs = L.geoJson(statesDataRegions, {
        style: styleRegion,
        onEachFeature: function (feature, layer) {
            layer.on({
                mouseover: highlightRegion,
                mouseout: resetRegionHighlight,
                click: zoomToRegion
            });
        }
    }).addTo(mapFina);


    // Ajout d'une couche GeoJSON pour les départements en fonction des régions
    var statesDataDepartementsGeoJs = L.geoJson(statesDataDepartements, {
        style: styleDepartement,
        onEachFeature: function (feature, layer) {
            layer.on({
                mouseover: highlightDepartement,
                mouseout: resetDepartementHighlight,
                click: zoomToDepartement
            });
        }
    }).addTo(mapFina);
    // Zoom sur un département lorsqu'il est cliqué
    function zoomToDepartement(e) {

    }


    // Actions au survol de la souris
    var statesDataDistrictsGeoJs = L.geoJson(statesDataDistricts, {
        pointToLayer: function (feature, latlng) {
            var radius = setSizeIcon(feature.properties.nb_prod);
            return L.circleMarker(latlng, {
                radius: radius,
                color: '#fff',
                fillOpacity: 1,
                fillColor: getColorBystatesDataDistricts(feature.properties.MontantTotal)
            });
        },
        style: styleDist,
        onEachFeature: evenement,
        labels: true  // Assurez-vous que les labels sont activés
    }).addTo(mapFina);


    // Fonction pour mettre en surbrillance une entité au survol
    function highlightFeature(e) {
        var layer = e.target;

        layer.setStyle({
            weight: 3,
            color: '#666',
            dashArray: '',
            fillOpacity: 0.7
        });

        layer.bringToFront();
        info.update(layer.feature.properties);

        // Assombrir les régions associées au district survolé
        var districtCode = e.target.feature.properties.NAME_1;
        darkenAssociatedRegions(districtCode);
    }

    // Fonction pour mettre en surbrillance un département au survol
    function highlightDepartement(e) {
        var layer = e.target;
        layer.setStyle({
            weight: 3,
            color: '#666',
            dashArray: '',
            fillOpacity: 0.7
        });
        if (!L.Browser.ie && !L.Browser.opera) {
            layer.bringToFront();
        }
        info.update(layer.feature.properties);
    }

    function darkenAssociatedRegions(districtCode) {
        statesDataRegionsGeoJs.eachLayer(function (layer) {
            if (layer.feature.properties.NAME_1 === districtCode) {
                // Assombrir seulement les régions associées au district
                layer.setStyle({
                    weight: 3,
                    color: '#666',
                    dashArray: '',
                    fillOpacity: 0.5 // Ajustez l'opacité selon vos préférences
                });
            }
        });
    }
    function resetAssociatedRegions() {
        statesDataRegionsGeoJs.eachLayer(function (layer) {
            // Réinitialiser la couleur des régions associées
            layer.setStyle({
                weight: 2,
                color: 'white',
                fillOpacity: 0.7
            });
        });
    }


    // Fonction pour réinitialiser la surbrillance après le survol
    function resetHighlight(e) {
        statesDataDistrictsGeoJs.resetStyle();
        info.update();
        resetAssociatedRegions();
    }


    // Gestionnaire d'événements au survol de la souris
    function evenement(feature, layer) {
        layer.on({
            mouseover: function(e) {
                highlightFeature(e);
                addRegionHoverEvents(); // Ajout des événements de survol pour les régions
            },
            mouseout: function(e) {
                resetHighlight(e);
            },
            click: zoomToFeature
        });
    }


    // Zoom sur un district lorsqu'il est cliqué
    function zoomToFeature(e) {
        // Réinitialisez la couche des départements
        resetDepartementsLayer();
        // Mettre à jour la couche des régions en fonction du district cliqué
        var selectedDistrictCode = e.target.feature.properties.NAME_1;
        updateRegionsLayer(selectedDistrictCode);
    }

    function updateDepartementsLayer(selectedRegionCode) {
        // Réinitialisez la couche des départements
        resetDepartementsLayer();

        // Filtrer les départements en fonction de la région sélectionnée
        var filteredDepartments = statesDataDepartements.features.filter(function (department) {
            return department.properties.NAME_2 === selectedRegionCode;
        });

        // Mettre à jour la couche des départements
        statesDataDepartementsGeoJs.addData({
            type: 'FeatureCollection',
            features: filteredDepartments
        });
    }


    // Ajoutez une fonction pour réinitialiser la couche des départements
    function resetDepartementsLayer() {
        // Réinitialisez la couche des départements à sa configuration initiale
        statesDataDepartementsGeoJs.clearLayers();
    }
    // Fonction pour mettre à jour la couche des régions en fonction du district cliqué
    function updateRegionsLayer(selectedDistrictCode) {
        // Filtrer les régions en fonction du district sélectionné
        var filteredRegions = statesDataRegions.features.filter(function (region) {
            return region.properties.NAME_1 === selectedDistrictCode;
        });

        // Mettre à jour la couche des régions
        statesDataRegionsGeoJs.clearLayers();

        // Ajouter seulement les régions du district sélectionné
        statesDataRegionsGeoJs.addData({
            type: 'FeatureCollection',
            features: filteredRegions
        });

        // Ajout des événements de survol pour les régions après la mise à jour
        addRegionHoverEvents();
    }

    // Ajout de titre et d'information sur la région survolée par la souris
    info = L.control();

    info.onAdd = function (mapFina) {
        this._div = L.DomUtil.create('div', 'info');
        this.update();
        return this._div;
    };

    // Mise à jour de la fonction info.update
    function getDistrictInfo(districtName) {
        var district = montantBD.features.find(function (feature) {
            return feature.properties.NAME_1 === districtName;
        });

        return district ? {
            AEP: district.properties.AEP || 0,
            AD: district.properties.AD || 0,
            HY: district.properties.HY || 0,
            EHAES: district.properties.EHAEE || 0,
            EHAEE: district.properties.EHAEE || 0,
            EHAEEn: district.properties.EHAEEn || 0,
            REE: district.properties.REE || 0,
            RCPE: district.properties.RCPE || 0,
            MONTANT: district.properties.MontantTotal || 0
        } : {
            AEP: 0,
            AD: 0,
            HY: 0,
            EHAES: 0,
            EHAEE: 0,
            EHAEEn: 0,
            REE: 0,
            RCPE: 0,
            MONTANT: 0
        };
    }
    function getRegionInfo(regionCode) {
        var region = montantRegion.features.find(function (feature) {
            return feature.properties.NAME_2 === regionCode;
        });

        return region ? {
            AEP: region.properties.AEP || 0,
            AD: region.properties.AD || 0,
            HY: region.properties.HY || 0,
            EHAES: region.properties.EHAES || 0,
            EHAEE: region.properties.EHAEE || 0,
            EHAEEn: region.properties.EHAEEn || 0,
            REE: region.properties.REE || 0,
            RCPE: region.properties.RCPE || 0,
        } : {
            AEP: 0,
            AD: 0,
            HY: 0,
            EHAES: 0,
            EHAEE: 0,
            EHAEEn: 0,
            REE: 0,
            RCPE: 0,
        };
    }

    // Fonction pour mettre à jour les données de la région
    function updateRegionInfo(regionCode) {
        // Utilisez la couche des régions pour obtenir les informations de la région
        var region = statesDataRegions.features.find(function (feature) {
            return feature.properties.NAME_2 === regionCode;
        });

        // Mettez à jour les informations de la région dans le panneau d'information
        info.update(region.properties);
    }

    info.update = function (props) {
        var districtInfo = getDistrictInfo(props ? props.NAME_1 : '');
        var regionInfo = getRegionInfo(props ? props.NAME_2:'');

        var calculatePercentage = function (value) {
            return (districtInfo.MONTANT !== 0) ? ((value / districtInfo.MONTANT) * 100).toFixed(2) + '%' : '0%';
        };
        var formatNumberWithSpaces =function (value) {
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        }
        var calculatePercentageR = function (value, total = districtInfo.MONTANT) {
            return (total !== 0) ? ((value / total) * 100).toFixed(2) + '%' : '0%';
        };

        // Condition pour déterminer si c'est une région ou un district
        var isRegion = props && props.NAME_2;

        this._div.innerHTML = `
        <table>
            <thead>
                <tr>
                    <th style="text-align: right;"></th>
                    <td></td>
                </tr>
                <tr>
                    <th style="text-align: right;">District: </th>
                    <td>${props ? props.NAME_1 : '---'}</td>
                </tr>
                <tr>
                    <th style="text-align: right;">Region :</th>
                    <td>${props ? props.NAME_2 : '---'}</td>
                </tr>
                <tr>
                    <th style="text-align: right;">Département :</th>
                    <td>${props ? props.NAME_3 : '---'}</td>
                </tr>
            </thead>
        </table>
        <table>



            <thead>
                <tr>
                    <th class="col"></th>
                    <th class="col">Cout Projet </th>
                    <th class="col">'  '</th>
                    <th class="col" style="text-align: right;"> % </th>
                </tr>
            </thead>
            <tbody>

                ${
                    isRegion
                    ? `
                        <tr>
                            <th class="row22" style="text-align: right;">Alimentation en eau potable:</th>
                            <td>${formatNumberWithSpaces(regionInfo.AEP)}</td>
                            <td></td>
                            <td>${calculatePercentageR(regionInfo.AEP)}</td>
                        </tr>
                    `
                    : `
                        <tr>
                            <th class="row22" style="text-align: right;">Alimentation en eau potable:</th>
                            <td>${formatNumberWithSpaces(districtInfo.AEP)}</td>
                            <td></td>
                            <td>${calculatePercentage(districtInfo.AEP)}</td>
                        </tr>
                    `
                }
                ${
                    isRegion
                    ? `
                        <tr>
                            <th class="row22" style="text-align: right;">Assainissement et drainage :</th>
                            <td>${formatNumberWithSpaces(regionInfo.AD)}</td>
                            <td></td>
                            <td>${calculatePercentageR(regionInfo.AD)}</td>
                        </tr>
                    `
                    : `
                        <tr>
                            <th class="row22" style="text-align: right;">Assainissement et drainage :</th>
                            <td>${formatNumberWithSpaces(districtInfo.AD)}</td>
                            <td></td>
                            <td>${calculatePercentage(districtInfo.AD)}</td>
                        </tr>
                    `
                }
                ${
                    isRegion
                    ? `
                        <tr>
                            <th class="row22" style="text-align: right;">Hygiène :</th>
                            <td>${formatNumberWithSpaces(regionInfo.HY)}</td>
                            <td></td>
                            <td>${calculatePercentageR(regionInfo.HY)}</td>
                        </tr>
                    `
                    : `
                        <tr>
                            <th class="row22" style="text-align: right;">Hygiène :</th>
                            <td>${formatNumberWithSpaces(districtInfo.HY)}</td>
                            <td></td>
                            <td>${calculatePercentage(districtInfo.HY)}</td>
                        </tr>
                    `
                }
                ${
                    isRegion
                    ? `
                        <tr>
                            <th class="row22" style="text-align: right;">Ressource en eau :</th>
                            <td>${formatNumberWithSpaces(regionInfo.REE)}</td>
                            <td></td>
                            <td>${calculatePercentageR(regionInfo.REE)}</td>
                        </tr>
                    `
                    : `
                        <tr>
                            <th class="row22" style="text-align: right;">Ressource en eau :</th>
                            <td>${formatNumberWithSpaces(districtInfo.REE)}</td>
                            <td></td>
                            <td>${calculatePercentage(districtInfo.REE)}</td>
                        </tr>
                    `
                }
                ${
                    isRegion
                    ? `
                        <tr>
                            <th class="row22" style="text-align: right;">EHA dans les Etablissements de Santé :</th>
                            <td>${formatNumberWithSpaces(regionInfo.EHAES)}</td>
                            <td></td>
                            <td>${calculatePercentageR(regionInfo.EHAES)}</td>
                        </tr>
                    `
                    : `
                        <tr>
                            <th class="row22" style="text-align: right;">EHA dans les Etablissements de Santé :</th>
                            <td>${formatNumberWithSpaces(districtInfo.EHAES)}</td>
                            <td></td>
                            <td>${calculatePercentage(districtInfo.EHAES)}</td>
                        </tr>
                    `
                }
                ${
                    isRegion
                    ? `
                        <tr>
                            <th class="row22" style="text-align: right;">EHA dans les Etablissements d’Enseignement :</th>
                            <td>${formatNumberWithSpaces(regionInfo.EHAEE)}</td>
                            <td></td>
                            <td>${calculatePercentageR(regionInfo.EHAEE)}</td>
                        </tr>
                    `
                    : `
                        <tr>
                            <th class="row22" style="text-align: right;">EHA dans les Etablissements d’Enseignement :</th>
                            <td>${formatNumberWithSpaces(districtInfo.EHAEE)}</td>
                            <td></td>
                            <td>${calculatePercentage(districtInfo.EHAEE)}</td>
                        </tr>
                    `
                }
                ${
                    isRegion
                    ? `
                        <tr>
                            <th class="row22" style="text-align: right;">EHA dans les autres Entités :</th>
                            <td>${formatNumberWithSpaces(regionInfo.EHAEEn)}</td>
                            <td></td>
                            <td>${calculatePercentageR(regionInfo.EHAEEn)}</td>
                        </tr>
                    `
                    : `
                        <tr>
                            <th class="row22" style="text-align: right;">EHA dans les autres Entités :</th>
                            <td>${formatNumberWithSpaces(districtInfo.EHAEEn)}</td>
                            <td></td>
                            <td>${calculatePercentage(districtInfo.EHAEEn)}</td>
                        </tr>
                    `
                }
            </tbody>
        </table>
    `;

    // Ajoutez du style CSS pour aligner les ":" à droite
    this._div.querySelector('th').style.textAlign = 'left';
    this._div.querySelector('td').style.textAlign = 'left';

    };


    info.addTo(mapFina);




    // Fonction de style pour les régions
    function styleRegion(feature) {
        return { // Couleur d'orange pour les régions
            weight: 2,
            opacity: 1,
            color: 'white',

            fillColor:'#87CEEB',
            fillOpacity: 0.7 // Réduisez l'opacité pour atténuer la couleur
        };
    }

        // Fonction de style pour les départements
        function styleDepartement(feature) {
            return {
                weight: 2,
                opacity: 1,
                color: 'white',
                fillColor: '#87CE01', // Couleur de remplissage pour les départements
                fillOpacity: 0.7
            };
        }

    // Fonction pour mettre en surbrillance une région au survol
    function highlightRegion(e) {
        var layer = e.target;
        layer.setStyle({
            weight: 3,
            color: '#666',
            dashArray: '',
            fillOpacity: 0.7
        });
        if (!L.Browser.ie && !L.Browser.opera) {
            layer.bringToFront();
        }
        info.update(layer.feature.properties);
    }

    // Fonction pour réinitialiser la surbrillance de la région après le survol
    function resetRegionHighlight(e) {
        statesDataRegionsGeoJs.resetStyle();
        info.update();
    }

    // Fonction pour réinitialiser la surbrillance du département
    function resetDepartementHighlight(e) {
        statesDataDepartementsGeoJs.resetStyle();
        info.update();
    }
    // Gestionnaire d'événements au survol de la souris pour les régions
    function addRegionHoverEvents() {
        statesDataRegionsGeoJs.eachLayer(function (layer) {
            layer.on({
                mouseover: function (e) {
                    highlightRegion(e);
                    updateRegionInfo(e.target.feature.properties.NAME_2); // Mise à jour des informations de la région au survol
                },
                mouseout: function (e) {
                    resetRegionHighlight(e);
                },
                click: zoomToRegion
            });
        });
    }

    // Zoom sur une région lorsqu'elle est cliquée
    function zoomToRegion(e) {
        // Mettre à jour la couche des départements en fonction de la région cliquée
        var selectedRegionCode = e.target.feature.properties.NAME_2;
        updateDepartementsLayer(selectedRegionCode);
    }


    // Fonction pour obtenir la couleur en fonction du nombre de projets dans le district





    // Fonction de style pour les districts en utilisant le dégradé de couleurs
    function styleDist(feature) {
        var isHighlighted = feature.properties.highlighted;

        if (isHighlighted) {
            // Couleur fixe pour les districts en surbrillance
            return {
                fillColor: '#ff9900',
                weight: 3,
                opacity: 1,
                color: '#fff',
                fillOpacity: 0.7
            };
        } else {
            // Utilisez la couleur basée sur le nombre de projets
            var projectCount = getProjectCount(feature.properties.NAME_1);
            return {
                fillColor: getColorByProjectCount(projectCount),
                weight: 2,
                opacity: 1,
                color: 'white',
                fillOpacity: 0.7
            };
        }
    }


    // Fonction pour réinitialiser la surbrillance du district
    function resetDistrictHighlight() {
        statesDataDistrictsGeoJs.eachLayer(function (layer) {
            layer.feature.properties.highlighted = false;
            statesDataDistrictsGeoJs.resetStyle(layer);
        });
    }


    // à supprimer
    function getProjectCount(districtName) {
        var district = montantBD.features.find(function (feature) {
            return feature.properties.NAME_1 === districtName;
        });

        var projectCount = district ? district.properties.MontantTotal : 0;
        return projectCount;
    }






    // Ajout de la légende à la carte
    function getColorByProjectCount(projectCount) {
        // Utilisez une échelle de couleurs en fonction du nombre de projets
        var colorScale = chroma.scale(['#ebebb9', '#c9c943', '#6495ed', '#af6eeb', '#32cd32', '#eaff00', '#ffba00', '#ff0000']).mode('lch').colors(8); // Utilise Chroma.js pour créer une échelle de couleurs
        var colorIndex;

        if (projectCount >= 40000000000) {
            colorIndex = 7; // 300 et plus rouge
        } else if (projectCount >= 35000000000) {
            colorIndex = 6; // 155 à 299 orange
        } else if (projectCount >= 30000000000) {
            colorIndex = 5; // 100 à 154 jaune
        } else if (projectCount >= 25000000000) {
            colorIndex = 4; // 60 à 99 vert
        } else if (projectCount >= 15000000000) {
            colorIndex = 3; // 40 à 59 violet
        } else if (projectCount >= 10000000000) {
            colorIndex = 2; // 20 à 39 bleu
        } else if (projectCount >= 5000000000) {
            colorIndex = 1; // 1 à 19 beige foncé
        } else {
            colorIndex = 0; // 0 beige
        }

        return colorScale[colorIndex];
    }

    function addLegend() {
        var legend = L.control({ position: 'bottomright' });

        legend.onAdd = function (mapFina) {
            var div = L.DomUtil.create('div', 'legend');
            var labels = [];

            // Ajouter le titre de la légende
            div.innerHTML += '<h4>LEGENDE</h4>';
            div.innerHTML += '<p>Coût de projet en million</p>';


            // Ajouter les couleurs et les étiquettes
            var colorScale = chroma.scale(['#ebebb9', '#c9c943', '#6495ed', '#af6eeb', '#32cd32', '#eaff00', '#ffba00', '#ff0000']).mode('lch').colors(8); // Utilise Chroma.js pour créer une échelle de couleurs
            var projectRanges = [' 0 ', ' 5 ', ' 15 ', ' 20 ', ' 25 ', ' 30 ', '35 ', '40 '];

            for (var i = 0; i < colorScale.length; i++) {
                div.innerHTML +=
                    '<i style="background:' + colorScale[i] + '"></i> ' +
                    projectRanges[i] + (projectRanges[i + 1] ? '&ndash;' + projectRanges[i + 1] + '<br>' : '+');
            }

            return div;
        };

        legend.addTo(mapFina);
    }

    addLegend();

}


// Ajoutez une variable globale pour stocker le layer actuel
var currentLayer = 'Nombre';

// Fonction pour changer la couche en fonction de la sélection de l'utilisateur
function changeMapLayerJS(layerType) {
    // Mettez à jour la variable globale currentLayer
    currentLayer = layerType;

    // Supprimez toutes les couches existantes sauf la carte
    map.eachLayer(function (layerType) {
        if (layerType !== map) {
            map.removeLayer(layerType);
        }
    });

    // Ajouter la nouvelle couche GeoJSON
    switch (layerType) {
        case 'Finance':
            initMapFina();
            break;
        case 'Nombre':
            initMapJS();
            break;
        // Ajouter d'autres cas au besoin

        default:
            // Ajouter une couche par défaut si nécessaire
            break;
    }
}
