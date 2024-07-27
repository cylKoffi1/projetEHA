<!doctype html>
    <html class="no-js" lang="en">

    <head>
        <!-- meta data -->
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @include('layouts.lurl')
        <link rel="stylesheet" href="{{ asset('leaflet/leaflet.css')}}" />



  <style>

            .info {
                padding: 6px 8px;
                font: 14px/16px Arial, Helvetica, sans-serif;
                background: white;
                background: rgba(255,255,255,0.8);
                box-shadow: 0 0 15px rgba(0,0,0,0.2);
                border-radius: 5px;
            }

                .info h4 {
                    margin: 0 0 5px;
                    color: #777;
                }
                .district-label {
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        color: #000; /* Couleur du texte */
    }

            .legend {
                text-align: left;
                line-height: 18px;
                color: #555;
            }

                .legend i {
                    width: 18px;
                    height: 18px;
                    float: left;
                    margin-right: 8px;
                    opacity: 0.9;
                }
                .did {
                    text-align: center;
                    justify-content: center;
                    align-items: center;
                }
                #context-menu {
                display: flex;
                    background-color: white;
                    border: 1px solid #ccc;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    padding: 10px;
                    list-style: none;
                }

                #context-menu ul {
                    margin: 0;
                    padding: 0;
                }

                #context-menu li {
                    padding: 5px 10px;
                    cursor: pointer;
                }

                #context-menu li:hover {
                    background-color: #f0f0f0;
                }

    </style>
    </head>


    <body  >

    @include('layouts.menu')

            <div class="container" style="text-align: center;">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col">
                            <div class="did">
                                <div class="row">
                                    <div class="col-md-1 col-sm-17"><label for="">Dates :</label></div>

                                    <div class="col" >
                                        <h class="col-md-3 col-sm-18" style="    margin-left: -60px;"><input type="radio" id="radioButton1" name="radioButtons" value="prévisionnelles" /><label for=""> prévisionnelles</label></h>
                                    </div>
                                    <div class="col" >
                                        <h class="col-md-3 col-sm-19" style="margin-left:-160px;"><input type="radio" id="radioButton2" name="radioButtons" value="effectives" /><label for=""> effectives</label></h>
                                    </div>
                                    <div class="col" >
                                        <h class="col-md-3 col-sm-19" style="margin-left:400px;"><input type="radio" id="radioButton3" name="radioButtons" value="Tous" /><label for="">Afficher toutes les données</label></h>
                                    </div>
                                </div>

                                <div class="col-md-2 col-sm-12">
                                    <h style="color: yellow;">début</h>
                                    <div >
                                        <input type="date" class="form-control" name="start_date" id="start_date">
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <h style="color: yellow;"> fin</h>
                                    <div >
                                        <input type="date" class="form-control" name="end_date" id="end_date">
                                    </div>

                                </div>
                            </div>
                    </div>
                        <div class="col-md-2 col-sm-12">

                            <h style="color: yellow;">Bailleur</h><br>
                            <div>
                                <select class="form-contro" id="bailleur" style=" height: 30px; width: 150px;">
                                    <option value="">Select bailleur</option>
                                    @foreach ($bailleur as $bail)
                                        <option value="{{ $bail->code_bailleur}}">{{$bail->libelle_long}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <h style="color: yellow;">Statut</h><br>
                            <div>
                                <select class="form-contro" id="status" style=" height: 30px; width: 150px;">
                                        <option value="">Select Status</option>
                                    @foreach ($statut as $stat)
                                        <option value="{{ $stat->code}}">{{$stat->libelle}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-29 col-sm-2">
                            <div class="single-model-search text-center">

                                <button class="welcome-btnn model-search-btn" id="filterButton" >
                                    Filtrer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <!--service start -->
                <section id="service" class="service" >

                    <div class="container">
                    <div class="col-sm-2">
                        <div class="row">
                            <label for="finance" class="form-control-label">Finance</label>
                            <input type="checkbox" id="financeLayer" onchange="handleCheckboxChange('financeLayer', 'Finance')">
                        </div>
                        <div class="row">
                            <label for="nombreProjet" class="form-control-label">Nombre de projet</label>
                            <input type="checkbox" id="nombreLayer" onchange="handleCheckboxChange('nombreLayer', 'Nombre')">
                        </div>
                    </div>
                    <div class="col-sm-10">
                    <div class="service-content">

                            <div class="row">


                                    <div class="col" style="height: 100%;">

                                                <div id="map" style="width: 90%; height: 590px; padding-left: 107%; z-index:1; outline-style: none;"></div>


                                    </div>
                            </div>

                    </div>
                    </div>
                </section>

                <button id="openModalButton">Ouvrir Modal</button>
<style>
    /* Style de base pour le modal */
.modal {
  display: none; /* Le modal est caché par défaut */
  position: fixed;
  z-index: 1;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.4); /* Fond semi-transparent */
}

/* Contenu du modal */
.modal-content {
  background-color: #fefefe;
  margin: 15% auto;
  padding: 20px;
  border: 1px solid #888;
  width: 80%;
  max-width: 600px;
  border-radius: 8px;
  position: relative;
}

/* Bouton de fermeture (X) */
.close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}

</style>
<!-- The Modal -->
<div id="customModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <p id="alertMessage">Message du modal</p>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chroma-js/2.1.0/chroma.min.js"></script>
<script src="{{ asset('leaflet/leaflet.js')}}"></script>

<script type="text/javascript" src="{{ asset('leaflet/geojson/districts.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojson/regions.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojson/departements.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/District.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/Region.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/Cout.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/CoutRegion.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/CoutDepartment.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/District_temp.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/Region_temp.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/Cout_temp.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/CoutRegion_temp.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/Department.geojson.js')}}"></script>
<script src="{{ asset('leaflet/codeJS/scriptFina.js') }}"></script>
<script src="{{ asset('leaflet/codeJS/scriptJS.js') }}"></script>
<script>
    // Call initMapJS on page load
    document.addEventListener('DOMContentLoaded', function () {
    // Automatically select the "Afficher toutes les données" radio button and trigger the filter
    var allDataRadioButton = document.getElementById('radioButton3');
    var startDateInput = document.getElementById('start_date');
    var endDateInput = document.getElementById('end_date');
    var statusInput = document.getElementById('status');
    var bailleurInput = document.getElementById('bailleur');

if (allDataRadioButton) {
        allDataRadioButton.checked = true;


        // Videz les champs de formulaire
        startDateInput.value = '';
        endDateInput.value = '';
        statusInput.value = '';
        bailleurInput.value = '';

    }
});




function getContextMenuLink(geoCode, geoType, domaine) {
    console.log(geoType + ' code est ' + geoCode); // Log ajoutée ici
    var storedFormDataString = localStorage.getItem('formData');
    if (storedFormDataString) {
        var storedFormData = JSON.parse(storedFormDataString);
        console.log('Retrieved form data:', storedFormData);

        var startDate = storedFormData.startDate || '';
        var endDate = storedFormData.endDate || '';
        var status = storedFormData.status || '';
        var bailleur = storedFormData.bailleur || '';
        var type = storedFormData.dateType || '';

        var filteredDataURL = '{{ url("/filtered-data") }}' +
            '?domaine=' + encodeURIComponent(domaine) +
            '&geoCode=' + encodeURIComponent(geoCode) +
            '&geoType=' + encodeURIComponent(geoType) +
            '&start_date=' + encodeURIComponent(startDate) +
            '&end_date=' + encodeURIComponent(endDate) +
            '&status=' + encodeURIComponent(status) +
            '&bailleur=' + encodeURIComponent(bailleur) +
            '&type=' + encodeURIComponent(type);

        return filteredDataURL;
    } else {
        console.log('No stored form data found.');
        return '{{ url("/filtered-data")}}' +
            '?domaine=' + encodeURIComponent(domaine) +
            '&geoCode=' + encodeURIComponent(geoCode) +
            '&geoType=' + encodeURIComponent(geoType);
    }
}



var startDateInput = document.getElementById('start_date');
var endDateInput = document.getElementById('end_date');
var statusInput = document.getElementById('status');
var bailleurInput = document.getElementById('bailleur');

// Chargement des données sauvegardées depuis le localStorage
window.addEventListener('DOMContentLoaded', function() {
    // Vérifie s'il y a des données sauvegardées dans le localStorage
    var formDataJSON = localStorage.getItem('formData');
    if (formDataJSON) {
        var formData = JSON.parse(formDataJSON);

        // Remplir les champs de formulaire avec les données sauvegardées
        startDateInput.value = formData.startDate || '';
        endDateInput.value = formData.endDate || '';
        statusInput.value = formData.status || '';
        bailleurInput.value = formData.bailleur || '';
    }
});
var startDateInput = document.getElementById('start_date');
var endDateInput = document.getElementById('end_date');
var statusInput = document.getElementById('status');
var bailleurInput = document.getElementById('bailleur');

endDateInput.addEventListener('change', function() {
    if (endDateInput.value < startDateInput.value) {
        $('#alertMessage').text('La date de fin ne peut pas être antérieure à la date de début.');
        $('#alertModal').modal('show');
        endDateInput.value = startDateInput.value;
    }

});

document.getElementById('filterButton').addEventListener('click', function() {
    var startDate = startDateInput.value;
    var endDate = endDateInput.value;
    var status = statusInput.value;
    var bailleur = bailleurInput.value;
    var dateType = document.querySelector('input[name="radioButtons"]:checked');

    if (!dateType) {
        $('#alertMessage').text('Veuillez sélectionner une option de date (prévisionnelles ou effectives) ou le sans filtre.');
        $('#alertModal').modal('show');
        return;
    }

    dateType = dateType.value;

    var formData = {
        startDate: startDate,
        endDate: endDate,
        status: status,
        bailleur: bailleur,
        dateType: dateType
    };

    // Sauvegarde des données dans le localStorage
    localStorage.setItem('formData', JSON.stringify(formData));

    // Appel de la fonction pour filtrer et mettre à jour les résultats
    triggerFilter();

    // Rechargement de la page après la mise à jour des résultats
    clearCacheAndReload();
});

// Fonction pour nettoyer le cache et recharger la page
function clearCacheAndReload() {
    if ('caches' in window) {
        caches.keys().then(function(names) {
            for (let name of names) {
                caches.delete(name);
            }
        }).then(function() {
            window.location.reload();
        });
    } else {
        window.location.reload();
    }
}

// Fonction pour déclencher le filtrage des résultats
function triggerFilter() {
    var startDate = startDateInput.value;
    var endDate = endDateInput.value;
    var status = statusInput.value;
    var bailleur = bailleurInput.value;
    var dateType = document.querySelector('input[name="radioButtons"]:checked').value;

    var randomQueryString = `&_=${new Date().getTime()}`;

    fetch(`{{ route('filter.maps') }}?start_date=${startDate}&end_date=${endDate}&status=${status}&bailleur=${bailleur}&date_type=${dateType}${randomQueryString}`, {
        headers: {
            'Cache-Control': 'no-cache'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
    })
    .then(data => {
        if ('caches' in window) {
            caches.keys().then(function(names) {
                for (let name of names) {
                    caches.delete(name);
                }
            });
        }
    })
    .catch(error => console.error('Error:', error));
}








    // Call initMapJS on page load
    document.addEventListener('DOMContentLoaded', function () {
        initMapJS();
    });

    function handleCheckboxChange(checkboxId, layerType) {
        var checkbox = document.getElementById(checkboxId);

        // Uncheck all other checkboxes
        var checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(function (cb) {
            if (cb.id !== checkboxId) {
                cb.checked = false;
            }
        });

        // Call the function to change the layer based on the user's selection
        if (checkbox.checked) {
            changeMapLayerJS(layerType);
        } else {
            // Handle the case where the checkbox is unchecked if necessary
        }
    }
</script>
<script>
    // Récupération des éléments nécessaires
var modal = document.getElementById('customModal');
var modalMessage = document.getElementById('modalMessage');
var openModalButton = document.getElementById('openModalButton');
var closeModalSpan = document.getElementsByClassName('close')[0];

// Écouteur pour ouvrir le modal lorsque le bouton est cliqué
openModalButton.addEventListener('click', function() {
  modal.style.display = 'block'; // Affiche le modal
});

// Écouteur pour fermer le modal lorsque l'utilisateur clique sur (X)
closeModalSpan.addEventListener('click', function() {
  modal.style.display = 'none'; // Cache le modal
});

// Écouteur pour fermer le modal lorsque l'utilisateur clique en dehors du modal
window.addEventListener('click', function(event) {
  if (event.target === modal) {
    modal.style.display = 'none'; // Cache le modal
  }
});

// Fonction pour afficher un message dans le modal
function showModalMessage(message) {
  modalMessage.textContent = message;
  modal.style.display = 'block'; // Affiche le modal
}

</script>

    </body>
