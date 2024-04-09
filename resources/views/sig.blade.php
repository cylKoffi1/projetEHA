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
                                </div>

                                <div class="col-md-2 col-sm-12">
                                    <h style="color: yellow;">début</h>
                                    <div >
                                        <input type="date" name="" id="">
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-12">
                                    <h style="color: yellow;"> fin</h>
                                    <div >
                                        <input type="date" name="" id="">
                                    </div>
                                </div>
                            </div>
                    </div>
                        <div class="col-md-2 col-sm-12">

                            <h style="color: yellow;">Bailleur</h><br>
                            <div>
                                <select class="form-contro" style=" height: 30px; width: 150px;">
                                    <option value="default">model</option>
                                    <option value="kia-rio">kia-rio</option>
                                    <option value="mitsubishi">mitsubishi</option>
                                    <option value="ford">ford</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-12">
                            <h style="color: yellow;">Statut</h><br>
                            <div>
                                <select class="form-contro" style=" height: 30px; width: 150px;">
                                    <option value="default">Cloturé</option>
                                    <option value="sedan">En cours</option>
                                    <option value="van">Annulé</option>
                                    <option value="roadster">Prévu</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-29 col-sm-2">
                            <div class="single-model-search text-center">
                                <button class="welcome-btnn model-search-btn" onclick="window.location.href='#'">
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
                                            {{-- <center> --}}

                                                <div id="map" style="width: 90%; height: 590px; padding-left: 107%; z-index:1; outline-style: none;"></div>

                                            {{-- </center> --}}
                                    </div>
                            </div>

                    </div>
                    </div>
                </section><!--/.service-->
                <!--service end-->
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
                    const radioButton1 = document.getElementById("radioButton1");
                    const radioButton2 = document.getElementById("radioButton2");

                    radioButton1.addEventListener("change", () => {
                    radioButton2.disabled = !radioButton1.checked;
                    });

                    radioButton2.addEventListener("change", () => {
                    radioButton1.disabled = !radioButton2.checked;
                    });
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


    </body>
