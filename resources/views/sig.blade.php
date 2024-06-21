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

                                <button class="welcome-btnn model-search-btn" id="filterButton" onclick="window.location.href='#'">
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


<script>
    // Call initMapJS on page load
    document.addEventListener('DOMContentLoaded', function () {

        // Automatically select the "Afficher toutes les données" radio button and trigger the filter
        var allDataRadioButton = document.getElementById('radioButton3');
        if (allDataRadioButton) {
            allDataRadioButton.checked = true; // Check the radio button
            triggerFilter(); // Call the function to apply the filter
        }
    });

    function triggerFilter() {
        var startDate = startDateInput.value;
        var endDate = endDateInput.value;
        var status = statusInput.value;
        var bailleur = bailleurInput.value;
        var dateType = document.querySelector('input[name="radioButtons"]:checked').value;

        var formData = {
            startDate: startDate,
            endDate: endDate,
            status: status,
            bailleur: bailleur,
            dateType: dateType
        };
        localStorage.setItem('formData', JSON.stringify(formData));

        // Append a random query string to the URL to bypass cache
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
            // Convert response to text and log it for debugging
            return response.text().then(text => {
                return JSON.parse(text);
            });
        })
        .then(data => {
            if ('caches' in window) {
            caches.keys().then(function(names) {
                for (let name of names) {
                    caches.delete(name);
                }
            });
        } else {
        }
        })
        .catch(error => console.error('Error:', error));
    }


    // Récupérez les éléments d'entrée
    var startDateInput = document.getElementById('start_date');
    var endDateInput = document.getElementById('end_date');
    var statusInput = document.getElementById('status');
    var bailleurInput = document.getElementById('bailleur');


    endDateInput.addEventListener('change', function() {
        // Assurez-vous que la date de fin ne peut pas être antérieure à la date de début
        if (endDateInput.value < startDateInput.value) {
            $('#alertMessage').text('La date de fin ne peut pas être antérieure à la date de début.');
            $('#alertModal').modal('show');
            endDateInput.value = startDateInput.value; // Réinitialisez la date de fin à la date de début
        }
    });
    // Écoutez les changements dans les champs de formulaire pour sauvegarder les données dans le stockage local
    document.getElementById('filterButton').addEventListener('click', function() {
        triggerFilter();
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
        localStorage.setItem('formData', JSON.stringify(formData));

        // Append a random query string to the URL to bypass cache
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
            // Convert response to text and log it for debugging
            return response.text().then(text => {
                return JSON.parse(text);
            });
        })
        .then(data => {
            // Vider le cache et recharger la page
            clearCacheAndReload();
        })
        .catch(error => console.error('Error:', error));
    });

    function clearCacheAndReload() {
        if ('caches' in window) {
            caches.keys().then(function(names) {
                for (let name of names) {
                    caches.delete(name);
                }
            }).then(function() {
                window.location.reload(true);
            });
        } else {
            window.location.reload(true);
        }
    }

        // Chargez les valeurs précédentes des champs de formulaire depuis le stockage local (s'il y en a)
        window.addEventListener('DOMContentLoaded', function() {
            // Vérifiez s'il y a des données sauvegardées dans le stockage local
            if (localStorage.getItem('formData')) {
                // Parsez les données sauvegardées depuis le stockage local
                var formData = JSON.parse(localStorage.getItem('formData'));

                // Si l'option "Sans filtre" est sélectionnée, ne remplissez pas les champs de formulaire avec les données sauvegardées
                var sansFiltreRadio = document.getElementById('radioButton3');
                if (sansFiltreRadio && sansFiltreRadio.checked) {
                    localStorage.removeItem('formData'); // Supprimez les données sauvegardées
                } else {
                    // Remplissez les champs de formulaire avec les données sauvegardées
                    startDateInput.value = formData.startDate || '';
                    endDateInput.value = formData.endDate || '';
                    statusInput.value = formData.status || '';
                    bailleurInput.value = formData.bailleur || '';

                    // Effacez les données après un certain délai (1 minute)
                    var delayInMilliseconds = 1 * 60 * 1000; // 1 minute en millisecondes
                    setTimeout(function() {
                        localStorage.removeItem('formData');
                    }, delayInMilliseconds);
                }
            }
        });







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

    </body>
