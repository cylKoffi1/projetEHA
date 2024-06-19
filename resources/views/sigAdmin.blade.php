@extends('layouts.app')

@section('content')
<style>

.info {
  position: absolute;
  top: 10px;
  right: 10px;
  background-color: white;
  padding: 10px;
  border-radius: 5px;
}
.map{
    padding-right: 200;
}
g{
    transform: translate3d(-120px, 20px, 0px);
}
.info .title {
  font-weight: bold;
}

.info .content {
  font-size: 12px;
}

.info .close-button {
  position: absolute;
  top: 5px;
  right: 5px;
  width: 10px;
  height: 10px;
  cursor: pointer;
}

.info .close-button:hover {
  background-color: #ccc;
}
    .district-label {
        font-size: 12px;
        font-weight: bold;
        text-align: center;
        color: #000;
        /* Couleur du texte */
    }

    .legend {
        position: absolute;
        top: 350px;
        right: 10px;
        width: 200px;
        float: left;
    }

    .legend i {
        width: 18px;
        height: 18px;
        float: left;
        margin-right: 8px;
        opacity: 0.9;
    }
    .leaflet-control-attribution {
        visibility: hidden;
    }
    .did {
        text-align: center;
        justify-content: center;
        align-items: center;
    }
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 6px;
        font-size: 80%;
        color: #dc3545;
        /* Couleur du texte pour les messages d'erreur */
    }
    #status-bailleur {
        margin-top: 33px;
        margin-left: -50px;
        position: relative;
    }


    .wide-column{
        min-width: 100px;
    }










/* marker & overlays interactivity */
.leaflet-marker-icon,
.leaflet-marker-shadow,
.leaflet-image-layer,
.leaflet-pane > svg path,
.leaflet-tile-container {
	pointer-events: none;
	}

.leaflet-marker-icon.leaflet-interactive,
.leaflet-image-layer.leaflet-interactive,
.leaflet-pane > svg path.leaflet-interactive,
svg.leaflet-image-layer.leaflet-interactive path {
	pointer-events: auto;
	}

/* visual tweaks */






</style>


@if (session('success'))
<script>
    alert("{{ session('success') }}");
</script>
@endif
<section id="multiple-column-form">
    <!-- Your existing HTML content -->
    <div class="row match-height">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Visualisation sur la carte</h4>
                    <hr>
                    <!-- Your existing filter form -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col">
                                    <div class="row">
                                        <div class="row">
                                            <div class="col-md-1 col-sm-17"><label for="">Dates:</label></div>
                                            <div class="col">
                                                <h class="col-md-3 col-sm-18"><input type="radio" id="radioButton1" name="radioButtons" value="prévisionnelles" /><label for=""> prévisionnelles</label></h>
                                            </div>
                                            <div class="col">
                                                <h class="col-md-3 col-sm-19"><input type="radio" id="radioButton2" name="radioButtons" value="effectives" /><label for=""> effectives</label></h>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="row">
                                                <div class="col">
                                                    <center>Début</center>
                                                    <input type="date" class="form-control" name="start_date" id="start_date">
                                                </div>
                                                <div class="col">
                                                    <center>Fin</center>
                                                    <input type="date" class="form-control" name="end_date" id="end_date">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col" id="status-bailleur">
                                    <div class="row">
                                        <div class="row">
                                            <div class="col">
                                            <div>
                                                    <center>Bailleur</center>
                                                    <select class="form-control" id="bailleur">
                                                        <option value="">Select bailleur</option>
                                                        @foreach ($bailleur as $bail)
                                                            <option value="{{ $bail->code_bailleur}}">{{$bail->libelle_long}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div>
                                                    <center>Statut</center>
                                                    <select class="form-control" id="status">
                                                        <option value="">Select Status</option>
                                                        @foreach ($statut as $stat)
                                                            <option value="{{ $stat->code}}">{{$stat->libelle}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-20 col-sm-2" style="top: -6px;">
                                    <div class="single-model-search text-center">
                                        <center>
                                            <div class="text-center" style="width:143px; padding: 13px; " >
                                                <h class="col-md-3 col-sm-9"><input type="radio" id="radioButton3" name="radioButtons" value="Tous" /><label >Sans filtre</label></h>
                                            </div>
                                        </center>
                                        <button class="btn btn-secondary" id="filterButton" onclick="window.location.href='#'">
                                            Filtrer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="finance" class="form-control-label">Finance</label>
                                <input type="checkbox" id="financeLayer" onchange="handleCheckboxChange('financeLayer', 'Finance')">
                            </div>
                            <div class="col">
                                <label for="nombreProjet" class="form-control-label">Nombre de projet</label>
                                <input type="checkbox" id="nombreLayer" onchange="handleCheckboxChange('nombreLayer', 'Nombre')">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="row" style="flex-wrap: nowrap">
                            <div class="col">
                                <div id="map" style="height: 590px; outline-style: none;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
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

        fetch(`{{ route('filter.map') }}?start_date=${startDate}&end_date=${endDate}&status=${status}&bailleur=${bailleur}&date_type=${dateType}${randomQueryString}`, {
            headers: {
                'Cache-Control': 'no-cache'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            updateMap(data);
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





    function updateMap(data) {
        // Implement the logic to update your map with the filtered data
        console.log(data);
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/chroma-js/2.1.0/chroma.min.js"></script>
<script src="{{ asset('leaflet/leaflet.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojson/districts.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojson/regions.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojson/departements.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/District.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/Region.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/Cout.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/CoutRegion.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/District_temp.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/Region_temp.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/Cout_temp.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/CoutRegion_temp.geojson.js')}}"></script>
<script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/Department.geojson.js')}}"></script>
<script src="{{ asset('leaflet/codeJS/scriptFina.js') }}"></script>
<script src="{{ asset('leaflet/codeJS/scriptJS.js') }}"></script>
@endsection
