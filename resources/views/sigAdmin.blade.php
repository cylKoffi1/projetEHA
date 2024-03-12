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

<!-- resources/views/users/create.blade.php -->

@if (session('success'))
<script>
    alert("{{ session('success') }}");

</script>
@endif

<style>
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 6px;
        font-size: 80%;
        color: #dc3545;
        /* Couleur du texte pour les messages d'erreur */
    }

</style>
<section id="multiple-column-form">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion SIG</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Gestion SIG</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Réquêttes prédéfinies</li>

                        </ol>
                    </nav>
                    <div class="row">
                        <script>
                            setInterval(function() {
                                document.getElementById('date-now').textContent = getCurrentDate();
                            }, 1000);

                            function getCurrentDate() {
                                // Implémentez la logique pour obtenir la date actuelle au format souhaité
                                var currentDate = new Date();
                                return currentDate.toLocaleString(); // Vous pouvez utiliser une autre méthode pour le formatage
                            }

                        </script>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row match-height">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        Visualisation sur la carte

                    </h4>
                    <span id="create_new"></span>
                    @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <hr>
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
                <div class="card-content">
                    <div class="card-body">

                        <div class="row" style="flex-wrap: nowrap">


                            <div class="col" >
                                {{-- <center> --}}

                                <div id="map" style=" height: 590px;  outline-style: none;"></div>

                                {{-- </center> --}}
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

</section>


                <!-- Ajoutez cette balise script dans votre fichier HTML -->
                <script src="https://cdnjs.cloudflare.com/ajax/libs/chroma-js/2.1.0/chroma.min.js"></script>

                <script src="{{ asset('leaflet/leaflet.js')}}" ></script>
                <script type="text/javascript" src="{{ asset('leaflet/geojson/districts.geojson.js')}}"></script>
                <script type="text/javascript" src="{{ asset('leaflet/geojson/regions.geojson.js')}}"></script>
                <script type="text/javascript" src="{{ asset('leaflet/geojson/departements.geojson.js')}}"></script>

                <!-- contenu des données de la bd à afficher sur la carte-->
                <script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/District.geojson.js')}}"></script>
                <script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/Region.geojson.js')}}"></script>
                <script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/Cout.geojson.js')}}"></script>
                <script type="text/javascript" src="{{ asset('leaflet/geojsonTemp/CoutRegion.geojson.js')}}"></script>

                <script>
                    function filterMap(checkedCheckbox, uncheckedCheckbox) {
                        // Désactivez l'autre case à cocher
                        document.getElementById(uncheckedCheckbox).checked = false;

                        // Appelez la fonction pour changer la couche en fonction de la sélection de l'utilisateur
                        changeMapLayerJS();
                    }
                </script>
                <script>
                    // Appeler initMapJS au chargement de la page
                    document.addEventListener('DOMContentLoaded', function () {
                        initMapJS();
                    });

                    // Fonction pour changer la couche en fonction de la checkbox sélectionnée
                    function handleCheckboxChange(checkboxId, layerType) {
                        var checkbox = document.getElementById(checkboxId);

                        // Désactivez tous les autres checkboxes
                        var checkboxes = document.querySelectorAll('input[type="checkbox"]');
                        checkboxes.forEach(function (cb) {
                            if (cb.id !== checkboxId) {
                                cb.checked = false;
                            }
                        });

                        // Appelez la fonction pour changer la couche en fonction de la sélection de l'utilisateur
                        if (checkbox.checked) {
                            changeMapLayerJS(layerType);
                        } else {
                            // Traitez le cas où le checkbox est décoché si nécessaire
                        }
                    }
                </script>
                <!--Les codes js des deux cartes -->
                <script src="{{ asset('leaflet/codeJS/scriptFina.js') }}"></script>
                <script src="{{ asset('leaflet/codeJS/scriptJS.js') }}"></script>


@endsection
