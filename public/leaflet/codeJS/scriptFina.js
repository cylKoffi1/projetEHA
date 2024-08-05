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

    function combineGeoJsonData(mainData, additionalData, keyProperty) {
        var additionalDataDict = {};
        additionalData.features.forEach(function(feature) {
            additionalDataDict[feature.properties[keyProperty]] = feature.properties;
        });

        mainData.features.forEach(function(feature) {
            var additionalProperties = additionalDataDict[feature.properties[keyProperty]];
            if (additionalProperties) {
                feature.properties = { ...feature.properties, ...additionalProperties };
            }
        });
    }

    combineGeoJsonData(statesDataDistricts, statesDataDistrictsBD, 'NAME_1');
    combineGeoJsonData(statesDataRegions, statesDataRegionsBD, 'NAME_2');
    combineGeoJsonData(statesDataDepartements, statesDataDepartmentsBD, 'NAME_3');


    // Ajout d'une couche GeoJSON pour les régions
    var statesDataRegionsGeoJs = L.geoJson(statesDataRegions, {
        style: styleRegion,
        onEachFeature: function (feature, layer) {
            layer.on({
                mouseover: highlightRegion,
                mouseout: resetRegionHighlight,
                click: zoomToRegion,
                contextmenu: function (e) {
                var codeRegion = feature.properties.Code_NAME_2; // Assurez-vous que cette propriété est correcte
                if (typeof codeRegion === 'undefined') {
                    console.error('Code_NAME_2 is undefined for feature:', feature);
                } else {
                    console.log(feature)
                    L.popup()
                        .setLatLng(e.latlng)
                        .setContent(createContextMenu(contextMenuItems, codeRegion, 'region')) // Utilisation de Code_NAME_1
                        .openOn(mapFina);
                }
            }
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
                click: zoomToDepartement,
                contextmenu: function (e) {
                    var codeDepartment = feature.properties.Code_NAME_3; // Assurez-vous que cette propriété est correcte
                    if (typeof codeDepartment === 'undefined') {
                        console.error('Code_NAME_3 is undefined for feature:', feature);
                    } else {
                        console.log(feature)
                        L.popup()
                            .setLatLng(e.latlng)
                            .setContent(createContextMenu(contextMenuItems, codeDepartment, 'departement')) // Utilisation de Code_NAME_1
                            .openOn(mapFina);
                    }
                }
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
                fillColor: getColorBystatesDataDistricts(feature.properties.NAME_1)
            });
        },
        style: styleDist,
        onEachFeature: function (feature, layer) {
                    layer.on({
                        mouseover: highlightFeature,
                        mouseout: resetHighlight,
                        click: zoomToFeature,
                        contextmenu: function (e) {
                            var codeDistrict = feature.properties.Code_NAME_1;
                           if (typeof codeDistrict === 'undefined') {
                               console.error('Code_NAME_1 is undefined for feature:', feature);
                           } else {
                               L.popup()
                                   .setLatLng(e.latlng)
                                   .setContent(createContextMenu(contextMenuItems, codeDistrict, 'district')) // Utilisation de Code_NAME_1
                                   .openOn(mapFina);
                           }
                       }
                    });
                }
    }).addTo(mapFina);

    function afficherRegionData(e) {
        var layer = e.target;
        var props = layer.feature.properties;
        updateTableWithRegionData(props);
    }
    
    function createContextMenu(items, geoCode, geoType, props) {
        var container = L.DomUtil.create('div', 'context-menu');
        var table = L.DomUtil.create('table', '', container);

        // Trouver les informations basées sur geoCode
        var districtInfo = statesDataDistrictsBD.features.find(function (feature) {
            return feature.properties.Code_NAME_1 === geoCode;
        });
        var regionInfo = statesDataRegionsBD.features.find(function (feature) {
            return feature.properties.Code_NAME_2 === geoCode;
        });
        var departmentInfo = statesDataDepartmentsBD.features.find(function (feature) {
            return feature.properties.Code_NAME_3 === geoCode;
        });

        // Déterminer le type
        var isDistrict = !!districtInfo && geoType === 'district';
        var isRegion = !!regionInfo && geoType === 'region';
        var isDepartment = !!departmentInfo && geoType === 'departement';

        console.log(isDistrict, isRegion, isDepartment);

        // Boucle à travers les éléments du menu contextuel
        items.forEach(function(item) {
            var row = L.DomUtil.create('tr', '', table);

            // Création de la cellule de texte
            var textCell = L.DomUtil.create('td', '', row);
            textCell.innerHTML = item.text;

            // Création de la cellule de valeur avec lien
            var valueCell = L.DomUtil.create('td', '', row);
            var valueLink = L.DomUtil.create('a', '', valueCell);
            valueLink.href = '#';
            valueLink.innerHTML = '-'; // Valeur par défaut si aucune donnée n'est trouvée

            // Déterminer la valeur à afficher
            if (isDepartment) {
                valueLink.innerHTML = `${departmentInfo ? departmentInfo.properties[item.codeDomaines] || '-' : '-'}`;
            } else if (isRegion) {
                valueLink.innerHTML = `${regionInfo ? regionInfo.properties[item.codeDomaines] || '-' : '-'}`;
            } else if (isDistrict) {
                valueLink.innerHTML = `${districtInfo ? districtInfo.properties[item.codeDomaines] || '-' : '-'}`;
            }

            // Ajouter l'événement onclick au lien
            valueLink.onclick = function(e) {
                e.preventDefault();
                if (item.callback) {
                    item.callback(geoCode, geoType);
                }
            };
        });

        return container;
    }

    // Définition des éléments du menu contextuel
    var contextMenuItems = [
        {text: 'Alimentation en Eau Potable <br>', codeDomaines: 'AEP', codeDomaine:'01', callback: function(geoCode, geoType) {window.location.href = getContextMenuLink(geoCode, geoType, this.codeDomaine);}},
        {text: 'Assainissement et Drainage <br>', codeDomaines: 'AD', codeDomaine:'02', callback: function(geoCode, geoType) {window.location.href = getContextMenuLink(geoCode, geoType, this.codeDomaine);}},
        {text: 'Hygiène <br>', codeDomaines: 'HY', codeDomaine:'03', callback: function(geoCode, geoType) {window.location.href = getContextMenuLink(geoCode, geoType, this.codeDomaine);}},
        {text: 'Ressources en Eau <br>', codeDomaines: 'REE', codeDomaine:'04', callback: function(geoCode, geoType) {window.location.href = getContextMenuLink(geoCode, geoType, this.codeDomaine);}},
        {text: 'EHA dans les Établissements de Santé <br>', codeDomaines: 'EHAES', codeDomaine:'05', callback: function(geoCode, geoType) {window.location.href = getContextMenuLink(geoCode, geoType, this.codeDomaine);}},
        {text: 'EHA dans les Établissements d\'Enseignement <br>', codeDomaines: 'EHAEE', codeDomaine:'06', callback: function(geoCode, geoType) {window.location.href = getContextMenuLink(geoCode, geoType, this.codeDomaine);}},
        {text: 'EHA dans les autres Entités', codeDomaines: 'EHAEEn', codeDomaine:'07', callback: function(geoCode, geoType) {window.location.href = getContextMenuLink(geoCode, geoType, this.codeDomaine);}}
    ];
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
            MontantTotal: district.properties.MontantTotal || 0,
            CoutProjet: district.properties.CoutProjet || 0,
            AEP_T: district.properties.AEP_T || 0,
            AD_T: district.properties.AD_T || 0,
            HY_T: district.properties.HY_T || 0,
            EHAES_T: district.properties.EHAES_T || 0,
            EHAEE_T: district.properties.EHAEE_T || 0,
            EHAEEn_T: district.properties.EHAEEn_T || 0,
            REE_T: district.properties.REE_T || 0,
            RCPE_T: district.properties.RCPE_T || 0,
            MontantTotal_T: district.properties.MontantTotal_T || 0
        } : {
            AEP: 0,
            AD: 0,
            HY: 0,
            EHAES: 0,
            EHAEE: 0,
            EHAEEn: 0,
            REE: 0,
            RCPE: 0,
            MontantTotal: 0,
            CoutProjet: 0,
            AEP_T: 0,
            AD_T: 0,
            HY_T: 0,
            EHAES_T: 0,
            EHAEE_T: 0,
            EHAEEn_T: 0,
            REE_T: 0,
            RCPE_T: 0,
            MontantTotal_T: 0
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
            MontantTotal: region.properties.MontantTotal || 0,
        } : {
            AEP: 0,
            AD: 0,
            HY: 0,
            EHAES: 0,
            EHAEE: 0,
            EHAEEn: 0,
            REE: 0,
            RCPE: 0,
            MontantTotal: 0
        };
    }
    function getDepartmentInfo(departmentName) {
        var department = statesDataDepartmentsCoutBD.features.find(function (feature) {
            return feature.properties.NAME_3=== departmentName;
        });

        return department ? {
            AEP: department.properties.AEP || 0,
            AD: department.properties.AD || 0,
            HY: department.properties.HY || 0,
            EHAES: department.properties.EHAEE || 0,
            EHAEE: department.properties.EHAEE || 0,
            EHAEEn: department.properties.EHAEEn || 0,
            REE: department.properties.REE || 0,
            RCPE: department.properties.RCPE || 0,
            PROJET_NUM: department.properties.PROJET_NUM || 0,
            // Ajout des valeurs totales
            AEP_T: department.properties.AEP_T || 0,
            AD_T: department.properties.AD_T || 0,
            HY_T: department.properties.HY_T || 0,
            EHAES_T: department.properties.EHAES_T || 0,
            EHAEE_T: department.properties.EHAEE_T || 0,
            EHAEEn_T: department.properties.EHAEEn_T || 0,
            REE_T: department.properties.REE_T || 0,
            RCPE_T: department.properties.RCPE_T || 0,
            PROJET_NUM_T: department.properties.PROJET_NUM_T || 0
        } : {
            AEP: 0,
            AD: 0,
            HY: 0,
            EHAES: 0,
            EHAEE: 0,
            EHAEEn: 0,
            REE: 0,
            RCPE: 0,
            PROJET_NUM: 0,
            // Valeurs totales par défaut
            AEP_T: 0,
            AD_T: 0,
            HY_T: 0,
            EHAES_T: 0,
            EHAEE_T: 0,
            EHAEEn_T: 0,
            REE_T: 0,
            RCPE_T: 0,
            PROJET_NUM_T: 0
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
        var departmentInfo = getDepartmentInfo(props ? props.NAME_3:'');


        var formatNumberWithSpaces =function (value) {
            return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        }
        function displayValue(value) {
            // Vérifie si la valeur est égale à 0 ou à 0.00
            if (value === 0 || value === 0.00) {
                return '-';
            } else {
                return value;
            }
        }
        var calculatePercentageR = function (value, total ) {
            return (total !== 0) ? ((value / total) * 100).toFixed(2) + '' : '-';
        };
        // Condition pour déterminer si c'est une région ou un district
        var isRegion = props && props.NAME_2;
        var isDepaterment = props && props.NAME_3;

        this._div.innerHTML = `

        <table>
            <thead>
                <tr>
                    <th style="text-align: left;"></th>
                    <td></td>
                </tr>
                <tr>
                    <th style="text-align: left;">District: </th>
                    <td>${props ? props.NAME_1 : '---'}</td>
                </tr>
                <tr>
                    <th style="text-align: left;">Region :</th>
                    <td>${props ? props.NAME_2 : '---'}</td>
                </tr>
                <tr>
                    <th style="text-align: left;">Département :</th>
                    <td>${props ? props.NAME_3 : '---'}</td>
                </tr>
            </thead>
        </table>
    <table style="border-collapse: collapse; width: 100%;">
        <thead>
        <tr>
                <th ></th>
                <th ></th>
                <th colspan="3" style="border: 1px solid black; text-align: center;">%</th>

            </tr>
            <tr>
                <th class="col" style=""></th>
                <th  class="wide-column" style="border: 1px solid black; text-align: center; font-size:12px; width: 105px;"">Coût (million)</th>
                <th class="col" style="border: 1px solid black; text-align: center; font-size:12px;  width:50px;"">District</th>
                <th class="col" style="border: 1px solid black; text-align: center;  font-size:12px; width:50px;"">Région</th>
                <th class="col" style="border: 1px solid black; text-align: center; font-size:12px;  width:50px;"">Départ</th>
            </tr>

        </thead>
        <tbody>
            ${
                isDepaterment
                ? `
                    <tr>
                        <th class="row22" style="text-align: right;">Alimentation en eau potable:</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${formatNumberWithSpaces((departmentInfo.AEP/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.AEP, districtInfo.AEP_T) )}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.AEP, districtInfo.AEP))}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.AEP, regionInfo.AEP))}</th>
                    </tr>

                `
                : isRegion
                ? `
                    <tr>
                        <th class="row22" style="text-align: right;">Alimentation en eau potable:</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((regionInfo.AEP/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.AEP, districtInfo.AEP_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.AEP, districtInfo.AEP)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
                : `
                    <tr>
                        <th class="row22" style="text-align: right;">Alimentation en eau potable:</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((districtInfo.AEP/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.AEP, districtInfo.AEP_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
            }
            ${
                isDepaterment
                ? `
                    <tr>
                        <th class="row22" style="text-align: right;">Assainissement et drainage :</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${formatNumberWithSpaces((departmentInfo.AD/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.AD, districtInfo.AEP_T) )}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.AD, districtInfo.AEP))}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.AD, districtInfo.AEP))}</th>
                    </tr>

                `
                : isRegion
                ?  `
                    <tr>
                        <th class="row22" style="text-align: right;">Assainissement et drainage :</th>
                        <th  class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((regionInfo.AD/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.AD, districtInfo.AD_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.AD, districtInfo.AD)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
                : `
                    <tr>
                        <th class="row22" style="text-align: right;">Assainissement et drainage :</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((districtInfo.AD/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.AD, districtInfo.AD_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
            }
            ${
                isDepaterment
                ? `
                    <tr>
                        <th class="row22" style="text-align: right;">Hygiène :</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${formatNumberWithSpaces((departmentInfo.HY/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.HY, districtInfo.AEP_T) )}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.HY, districtInfo.AEP))}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.HY, districtInfo.AEP))}</th>
                    </tr>

                `
                : isRegion
                ?  `
                    <tr>
                        <th class="row22" style="text-align: right;">Hygiène :</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((regionInfo.HY/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.HY, districtInfo.HY_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.HY, districtInfo.HY)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
                : `
                    <tr>
                        <th class="row22" style="text-align: right;">Hygiène :</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((districtInfo.HY/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.HY, districtInfo.HY_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
            }
            ${
                isDepaterment
                ? `
                    <tr>
                        <th class="row22" style="text-align: right;">Ressource en eau :</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${formatNumberWithSpaces((departmentInfo.REE/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.REE, districtInfo.AEP_T) )}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.REE, districtInfo.AEP))}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.REE, districtInfo.AEP))}</th>
                    </tr>

                `
                : isRegion
                ?  `
                    <tr>
                        <th class="row22" style="text-align: right;">Ressource en eau :</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((regionInfo.REE/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.REE, districtInfo.REE_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.REE, districtInfo.REE)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
                : `
                    <tr>
                        <th class="row22" style="text-align: right;">Ressource en eau :</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((districtInfo.REE/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.REE, districtInfo.REE_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
            }

            ${
                isDepaterment
                ? `
                    <tr>
                        <th class="row22" style="text-align: right;">EHA Etb de Santé :</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${formatNumberWithSpaces((departmentInfo.EHAES/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAES, districtInfo.AEP_T) )}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAES, districtInfo.AEP))}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAES, districtInfo.AEP))}</th>
                    </tr>

                `
                : isRegion
                ?  `
                    <tr>
                        <th class="row22" style="text-align: right;">EHA Etb de Santé :</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((regionInfo.EHAES/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.EHAES, districtInfo.EHAES_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.EHAES, districtInfo.EHAES)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
                : `
                    <tr>
                        <th class="row22" style="text-align: right;">EHA Etb de Santé :</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((districtInfo.EHAES/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.EHAES, districtInfo.EHAES_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
            }

            ${
                isDepaterment
                ? `
                    <tr>
                        <th class="row22" style="text-align: right;">EHA Etb d’Enseignement :</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${formatNumberWithSpaces((departmentInfo.EHAEE/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAEE, districtInfo.AEP_T) )}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAEE, districtInfo.AEP))}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAEE, districtInfo.AEP))}</th>
                    </tr>

                `
                : isRegion
                ?  `
                    <tr>
                        <th class="row22" style="text-align: right;">EHA Etb d’Enseignement :</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((regionInfo.EHAEE/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.EHAEE, districtInfo.EHAEE_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.EHAEE, districtInfo.EHAEE)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
                : `
                    <tr>
                        <th class="row22" style="text-align: right;">EHA Etb d’Enseignement :</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((districtInfo.EHAEE/1000000).toFixed(3)  || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.EHAEE, districtInfo.EHAEE_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
            }

            ${
                isDepaterment
                ? `
                    <tr>
                        <th class="row22" style="text-align: right;">EHA autres Entités :</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${formatNumberWithSpaces((departmentInfo.EHAEEn/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAEEn, districtInfo.AEP_T) )}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAEEn, districtInfo.AEP))}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${displayValue(calculatePercentageR(departmentInfo.EHAEEn, districtInfo.AEP))}</th>
                    </tr>

                `
                : isRegion
                ?  `
                    <tr>
                        <th class="row22" style="text-align: right;">EHA autres Entités :</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((regionInfo.EHAEEn/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.EHAEEn, districtInfo.EHAEEn_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(regionInfo.EHAEEn, districtInfo.EHAEEn)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                    </tr>
                `
                : `
                    <tr>
                        <th class="row22" style="text-align: right;">EHA autres Entités :</th>
                        <th class="wide-column" style="border: 1px solid black; text-align: right; width: 105px;">${formatNumberWithSpaces((districtInfo.EHAEEn/1000000).toFixed(3) || 0)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">${calculatePercentageR(districtInfo.EHAEEn, districtInfo.EHAEEn_T)}</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
                        <th class="col" style="border: 1px solid black; text-align: center;">-</th>
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

// Fonction pour déterminer la couleur en fonction du nombre de projets
function getColorByProjectCount(projectCount) {
    // Utilisez une échelle de couleurs en fonction du nombre de projets
    var colorScale = chroma.scale(['#ebebb9', '#c9c943', '#6495ed', '#af6eeb', '#32cd32', '#eaff00', '#ffba00', '#ff0000']).mode('lch').colors(8); // Utilise Chroma.js pour créer une échelle de couleurs
    var colorIndex;

    if (projectCount >= 40000000000) {
        colorIndex = 7; // Montant très élevé (rouge)
    } else if (projectCount >= 20000000000) {
        colorIndex = 6; // Montant élevé (orange)
    } else if (projectCount >= 10000000000) {
        colorIndex = 5; // Montant important (jaune)
    } else if (projectCount >= 5000000000) {
        colorIndex = 4; // Montant moyen à élevé (vert)
    } else if (projectCount >= 2000000000) {
        colorIndex = 3; // Montant moyen (violet)
    } else if (projectCount >= 1000000000) {
        colorIndex = 2; // Montant bas à moyen (bleu)
    } else if (projectCount >= 10000000) {
        colorIndex = 1; // Montant bas (beige foncé)
    } else {
        colorIndex = 0; // Aucun montant (beige)
    }


    return colorScale[colorIndex];
}

// Fonction pour styliser les districts sur la carte
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

// Fonction pour obtenir le nombre de projets dans un district donné
function getProjectCount(districtName) {
    var district = montantBD.features.find(function (feature) {
        return feature.properties.NAME_1 === districtName;
    });

    var projectCount = district ? district.properties.CoutProjet : 0;
    return projectCount;
}
// Fonction pour ajouter une légende à la carte
var colorScale = chroma.scale(['#ebebb9', '#c9c943', '#6495ed', '#af6eeb', '#32cd32', '#eaff00', '#ffba00', '#ff0000']).mode('lch').colors(8);

function addLegend() {
    var legend = L.control({ position: 'bottomright' });

    legend.onAdd = function (map) {
        var div = L.DomUtil.create('div', 'legend');
        var labels = [];

        // Ajouter le titre de la légende
        div.innerHTML += '<h4>LEGENDE</h4>';
        div.innerHTML += '<p>Coût de projet en (million)</p>';

        // Ajouter les couleurs et les étiquettes
        var projectRanges = ['0', '5', '10', '20', '25', '30', '35', '40'];

        for (var i = 0; i < colorScale.length; i++) {
            div.innerHTML +=
                '<i style="background:' + colorScale[i] + '"></i> ' +
                projectRanges[i] + (projectRanges[i + 1] ? '&ndash;' + projectRanges[i + 1] + '<br>' : '+');
        }

        return div;
    };

    legend.addTo(mapFina);
}



// Appeler la fonction pour ajouter la légende à la carte
addLegend();

}


// Ajoutez une variable globale pour stocker le layer actuel
var currentLayer = 'Finance';

// Fonction pour changer la couche en fonction de la sélection de l'utilisateur
function changeMapLayerJS(layerType) {
    // Mettez à jour la variable globale currentLayer
    currentLayer = layerType;

    // Supprimez toutes les couches existantes sauf la carte
    mapFina.eachLayer(function (layerType) {
        if (layerType !== mapFina) {
            mapFina.removeLayer(layerType);
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
