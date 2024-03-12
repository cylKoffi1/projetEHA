@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('assets/compiled/css/projet.css')}}">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<style>
    .inline {
        display: flex;
        align-items: flex-start;
        /* Aligner les éléments sur le côté gauche */
        margin-bottom: 10px;
        /* Espacement entre les champs */

    }

    .inline2 {
        display: flex;
        align-items: flex-start;
        /* Aligner les éléments sur le côté gauche */
        margin-top: 48px;
        /* Espacement entre les champs */

    }

    .gauche3,
    .droit3 {
        margin-left: 8px;
        width: 100%;
    }

    /* Style des étiquettes */
    .inline label {
        margin-bottom: 5px;
        /* Espacement entre les étiquettes */
    }

</style>
@section('content')

<section id="multiple-column-form">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-4 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
                </div>
                <div class="col-12 col-md-4 order-md-2">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;">
                        <span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span>
                    </li>
                </div>
                <div class="col-12 col-md-4 order-md-3 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Réalisation de projet</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Paramètre de réalisation</li>

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
                    <h4 class="card-title">Caractéristiques des infrastructures</h4>

                </div>
                <div class="card-content">
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{session('success')}}
                            </div>
                        @elseif (session('error'))
                            <div class="alert alert-danger">
                                {{session('error')}}
                            </div>
                        @endif
                        <ul class="nav nav-tabs" id="myTabs">
                            <li class="nav-item">
                                <a class="nav-link active" id="caracteristiques-tab" data-toggle="tab" href="#caracteristiques">Caractéristiques</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="dates-effectives-tab" data-toggle="tab" href="#dates-effectives">Dates Effectives</a>
                            </li>
                        </ul>
                        <div class="tab-content mt-2">
                            <div class="tab-pane fade show active" id="caracteristiques" style="background-color:transparent;">
                                <form class="form" id="personnelForm" method="POST" enctype="multipart/form-data" action="{{ route('enregistrer.Caracteristiques') }}">
                                    @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                                    <div  class="form-step">
                                        <div class="row">
                                            <div class="col">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <!-- Ajoutez ces champs cachés dans votre formulaire -->
                                                        <input type="hidden" id="code_projet" name="code_projet">
                                                        <input type="hidden" id="code_action_mener_projet" name="code_action_mener_projet">
                                                        <div class="col-4" style="width: 30%;">
                                                            <label for="code_projet">Code du projet</label>
                                                            <input type="text" class="form-control" id="code_projet_input" name="code_projet" readonly>
                                                        </div>
                                                        <div class="col-2" style="width: 18%;">
                                                            <label for="ordre">N° d'ordre :</label>
                                                            <input type="text" name="ordre" class="form-control" style="width: 90px;" id="ordre">
                                                        </div>
                                                        <div class="col-3" style="width: 25%;">
                                                                <label for="infrastructure" >Infrastructure</label>
                                                                <input type="text" name="infrastructure" id="infrastructure" class="form-control">
                                                                <input type="hidden" name="infrastructurecode" id="infrastructurecode" class="form-control">
                                                        </div>
                                                        <div class="col-3" style="width: 27%;">
                                                            <label for="FamilleInfrastructure" >Famille d'infrastructure</label>
                                                            <input type="text" class="form-control" name="FamilleInfrastructure" id="FamilleInfrastructure" readonly >
                                                            <input type="hidden" class="form-control" name="Famillecode" id="Famillecode" >
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">
                                                        <!--Les caractréristiQues-->
                                                            <!--  Ouvrage de captage -->
                                                    @if($codeFamilleInfrastructure == 1)
                                                    <div class="row">
                                                        <div class="col">
                                                            <label for="typeCaptage">Type de captage</label>
                                                            <select name="typeCaptage1" class="form-control">
                                                                <option value=""></option>
                                                                @foreach ($typeCaptages as $typeCaptage)
                                                                    <option value="{{ $typeCatage->code }}">{{ $typeCatage->libelle }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label for="debitCaptage">Débit / Capacité en (m3/)h</label>
                                                            <input type="number" name="debitCaptage1" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="profondeurCaptage">Profondeur (m)</label>
                                                            <input type="number" name="profondeurCaptage1" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="natureTravauxCaptage">Nature des travaux</label>
                                                                <select name="natureTravauxCaptage1" class="form-select">
                                                                    <option value=""></option>
                                                                    @foreach ($natureTravaux as $natureTravau)
                                                                    <option value="{{ $natureTravau->code }}">{{ $natureTravau->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                        </div>
                                                    </div>
                                                        <div class="col">
                                                            <hr>
                                                        </div>
                                                    @endif
                                                    <!--  Ouvrage de captage d'eau-->
                                                    @if($codeFamilleInfrastructure == 2)
                                                    <div class="row">
                                                        <div class="col">
                                                            <label for="typeCaptage">Type de captage</label>
                                                            <select name="typeCaptage" class="form-control">
                                                                <option value=""></option>
                                                                @foreach ($typeCaptages as $typeCaptage)
                                                                    <option value="{{ $typeCaptage->code }}">{{ $typeCaptage->libelle }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label for="debitCaptage">Débit/Capacité (m3/h)</label>
                                                            <input type="number" name="debitCaptage" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="profondeurCaptage">Profondeur (m)</label>
                                                            <input type="number" name="profondeurCaptage" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="natureTravauxCaptage">Nature des travaux</label>
                                                                <select name="natureTravauxCaptage2" class="form-select">
                                                                    <option value=""></option>
                                                                    @foreach ($natureTravaux as $natureTravau)
                                                                    <option value="{{ $natureTravau->code }}">{{ $natureTravau->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <!--  Unité de traitement -->
                                                    @if($codeFamilleInfrastructure == 3)
                                                    <div class="row">
                                                        <div class="col">
                                                            <label for="typeUnite">Type d'unité</label>
                                                            <select name="typeUnite" class="form-control">
                                                                <option value=""></option>
                                                                @foreach ($uniteTraitements as $uniteTraitement)
                                                                    <option value="{{ $uniteTraitement->code }}">{{ $uniteTraitement->libelle }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label for="debitUnite">Débit/Capacité (m3/h)</label>
                                                            <input type="number" name="debitUnite" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="natureTravauxCaptage">Nature des travaux</label>
                                                                <select name="natureTravauxCaptage3" class="form-select">
                                                                    <option value=""></option>
                                                                    @foreach ($natureTravaux as $natureTravau)
                                                                    <option value="{{ $natureTravau->code }}">{{ $natureTravau->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <!--  Réservoir -->
                                                    @if($codeFamilleInfrastructure == 4)
                                                    <div class="row">
                                                        <div class="col">
                                                            <label for="typeReservoir">Type de réservoir</label>
                                                            <select name="typeReservoir" class="form-control">
                                                                <option value=""></option>
                                                                @foreach ($typeCaptages as $typeCaptage)
                                                                    <option value="{{ $typeCaptage->code }}">{{ $typeCaptage->libelle }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label for="materiauReservoir">Matériau</label>
                                                            <select name="materiauReservoir" class="form-control">
                                                                <option value=""></option>
                                                                @foreach ($materielStockages as $materielStockage)
                                                                    <option value="{{ $materielStockage->code }}">{{ $materielStockage->libelle }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label for="capaciteReservoir">Capacité</label>
                                                            <input type="number" name="capaciteReservoir" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="natureTravauxCaptage">Nature des travaux</label>
                                                                <select name="natureTravauxCaptage4" class="form-select">
                                                                    <option value=""></option>
                                                                    @foreach ($natureTravaux as $natureTravau)
                                                                    <option value="{{ $natureTravau->code }}">{{ $natureTravau->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <!--  Réseau -->
                                                    @if($codeFamilleInfrastructure == 5)
                                                    <div class="row">
                                                        <div class="col">
                                                            <label for="typeTransportReseau">Type de transport</label>
                                                            <select name="typeTransportReseau" class="form-control">
                                                                <option value=""></option>
                                                                @foreach ($OuvrageTransports as $OuvrageTransport)
                                                                    <option value="{{ $OuvrageTransport->code }}">{{ $OuvrageTransport->libelle }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label for="materiauReseau">Matériau</label>
                                                            <select name="materiauReseau" class="form-control">
                                                                <option value=""></option>
                                                                @foreach ($materielStockages as $materielStockage)
                                                                    <option value="{{ $materielStockage->code }}">{{ $materielStockage->libelle }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label for="diametreReseau">Diamètre</label>
                                                            <input type="number" name="diametreReseau" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="lineaireReseau">Linéaire</label>
                                                            <input type="number" name="lineaireReseau" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="natureTravauxCaptage">Nature des travaux</label>
                                                                <select name="natureTravauxCaptage5" class="form-select">
                                                                    <option value=""></option>
                                                                    @foreach ($natureTravaux as $natureTravau)
                                                                    <option value="{{ $natureTravau->code }}">{{ $natureTravau->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <!-- D d'assainissement -->
                                                    @if($codeFamilleInfrastructure == 6)
                                                    <div class="row">
                                                        <div class="col">
                                                            <label for="typeOuvrageAssainissement">Type d'ouvrage</label>
                                                            <select name="typeOuvrageAssainissement" class="form-control">
                                                                <option value=""></option>
                                                                @foreach ($infrastructures as $infrastructure)
                                                                    @if ($infrastructure->code_domaine==02)
                                                                    <option value="{{ $infrastructure->code}}">{{ $infrastructure->libelle }}</option>
                                                                    @endif

                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label for="capaciteOuvrageAssainissement">Capacité/Volume</label>
                                                            <input type="number" name="capaciteOuvrageAssainissement" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="natureTravauxCaptage">Nature des travaux</label>
                                                                <select name="natureTravauxCaptage6" class="form-select">
                                                                    <option value=""></option>
                                                                    @foreach ($natureTravaux as $natureTravau)
                                                                    <option value="{{ $natureTravau->code }}">{{ $natureTravau->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <!--  Réseau de collecte et de transport -->
                                                    @if($codeFamilleInfrastructure == 7)
                                                    <div class="row">
                                                        <div class="col">
                                                            <label for="typeOuvrageReseau">Type d'ouvrage</label>
                                                            <select name="typeOuvrageReseau" class="form-control">
                                                                <option value=""></option>
                                                                @foreach ($infrastructures as $infrastructure)
                                                                    @if ($infrastructure->code_domaine==02)
                                                                    <option value="{{ $infrastructure->code}}">{{ $infrastructure->libelle }}</option>
                                                                    @endif

                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label for="typeReseauReseau">Type de réseau</label>
                                                            <select name="typeReseauReseau" class="form-control">
                                                                <option value=""></option>
                                                                @foreach ($typeReseaux as $typeReseau)
                                                                    <option value="{{ $typeReseau->code }}">{{ $typeReseau->libelle }}</option>
                                                                    @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label for="classeReseau">Classe</label>
                                                            <input type="text" name="classeReseau" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="lineaireReseauReseau">Linéaire</label>
                                                            <input type="number" name="lineaireReseauReseau" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="natureTravauxCaptage">Nature des travaux</label>
                                                                <select name="natureTravauxCaptage7" class="form-select">
                                                                    <option value=""></option>
                                                                    @foreach ($natureTravaux as $natureTravau)
                                                                    <option value="{{ $natureTravau->code }}">{{ $natureTravau->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <!--  Ouvrage -->
                                                    @if($codeFamilleInfrastructure == 9)
                                                    <div class="row">
                                                        <div class="col">
                                                            <label for="typeOuvrage">Type d'ouvrage</label>
                                                            <select name="typeOuvrage" class="form-control">
                                                                <option value=""></option>
                                                                @foreach ($infrastructures as $infrastructure)
                                                                    @if ($infrastructure->code_domaine==02)
                                                                    <option value="{{ $infrastructure->code}}">{{ $infrastructure->libelle }}</option>
                                                                    @endif

                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label for="nombreOuvrage">Nombre</label>
                                                            <input type="number" name="nombreOuvrage" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="natureTravauxCaptage">Nature des travaux</label>
                                                                <select name="natureTravauxCaptage8" class="form-select">
                                                                    <option value=""></option>
                                                                    @foreach ($natureTravaux as $natureTravau)
                                                                    <option value="{{ $natureTravau->code }}">{{ $natureTravau->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    <!--  Instrumentation -->
                                                    @if($codeFamilleInfrastructure == 10)
                                                    <div class="row">
                                                        <div class="col">
                                                            <label for="typeInstrument">Type d'instrument</label>
                                                            <select name="typeInstrument" class="form-control">
                                                                <option value=""></option>
                                                                @foreach ($typeInstruments as $typeInstrument)
                                                                    <option value="{{ $typeInstrument->code }}">{{ $typeInstrument->libelle }}</option>
                                                                    @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <label for="nombreInstrument">Nombre</label>
                                                            <input type="number" name="nombreInstrument" class="form-control">
                                                        </div>
                                                        <div class="col">
                                                            <label for="natureTravauxCaptage">Nature des travaux</label>
                                                                <select name="natureTravauxCaptage9" class="form-select">
                                                                    <option value=""></option>
                                                                    @foreach ($natureTravaux as $natureTravau)
                                                                    <option value="{{ $natureTravau->code }}">{{ $natureTravau->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                        </div>
                                                    </div>
                                                    @endif


                                                        <div class="col" style="text-align: center;">
                                                            <a href="#" id="niveauAvancementBtn" class="btn btn-secondary open-niveau-avancement-modal" style="color: white; margin-top:25px; font-size: 10;">Niveau d'avancement</a>

                                                        </div>


                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <div class="row">

                                                    </div>
                                                </div>

                                            </div>

                                        </div>
                                        <div class="mt-3 text-center" style="text-align: center;">
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="dates-effectives" style="background-color:transparent;">
                                <form class="form" id="personnelForm" method="POST" enctype="multipart/form-data" action="{{ route('enregistrer-dates-effectives') }}">
                                    @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                                    <div class="form-step">
                                        <div class="row">
                                            <div class="col">
                                                <div class="form-group">
                                                    <div class="row">
                                                        <div class="col-4" style="margin-top: 28px;">
                                                            <input type="hidden" id="code_projet2" name="code_projet2">
                                                            <label for="date_debut">Date effective de démarrage:</label>
                                                            @if(isset($dateEnregistree))
                                                                <input type="text" class="form-control" value="{{ $dateEnregistree }}" id="date_debut" name="date_debut">
                                                            @else
                                                                <input type="date" class="form-control" id="date_debut" name="date_debut">
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                            <label for="commentaire">Commentaire:</label>
                                                            <textarea name="commentaire" id="commentaire" cols="30" rows="3" class="form-control"></textarea>
                                                        </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3 text-center" style="text-align: center;">
                                            <button type="submit" class="btn btn-primary">Enregistrer</button>

                                        </div>

                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</section>
<div class="modal fade" id="doubleFormModal" tabindex="-1" role="dialog" aria-labelledby="doubleFormModalLabel" aria-hidden="true" style="background-color: #DBECF8;">
    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content" style="background-color: white;">
            <div class="modal-header">

                <h5 class="modal-title" id="doubleFormModalLabel">Niveau d'avancement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
                <div class="card-content" style="background-color: #EAF2F8;" >
                    <div class="modal-body" style="background-color: #EAF2F8;">

                        <div class="tab-content" id="myTabsContent" style="background-color: #EAF2F8;">
                            <!-- Premier formulaire - État d'avancement -->
                            <div class="tab-pane fade show active" id="etatAvancement" role="tabpanel" aria-labelledby="etatAvancement-tab" style="background-color: #EAF2F8;">
                                <form id="etatAvancementForm" method="POST" action="{{ route('enregistrer.niveauAvancement') }}" data-parsley-validate>
                                    @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col">
                                                <label for="code_projet">Code projet:</label>
                                                <input type="text" class="form-control" id="code_projet_Modal" name="code_projet_Modal" readonly>
                                            </div>
                                            <div class="col">
                                                <label for="code_projet">N° Ordre:</label>
                                                <input type="text" class="form-control" id="ordre_Modal" name="ordre_Modal" readonly>
                                            </div>
                                            <div class="col">
                                                <label for="date_realisation">Date de réalisation:</label>
                                                <input type="date" class="form-control" id="date_realisation_Modal" name="date_realisation_Modal">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col">
                                                <label for="quantite_reel">Quantité Prévue:</label>
                                                <input type="text" readonly class="form-control" name="quantite_provisionnel_Modal" id="quantite_provisionnel_Modal">
                                            </div>
                                            <div class="col">
                                                <label for="quantite_reel">Quantité réelle:</label>
                                                <input type="text" class="form-control" id="quantite_reel_Modal" name="quantite_reel_Modal">

                                            </div>
                                            <div class="col">
                                                <label for="pourcentage">Pourcentage:</label>
                                                <input type="text" class="form-control" id="pourcentage_Modal" name="pourcentage_Modal">
                                            </div>

                                            <div class="form-group">
                                                <label for="commentaire">Commentaire:</label>
                                                <textarea class="form-control" id="commentaire_Niveau_Modal" name="commentaire_Niveau_Modal" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Enregistrer État d'avancement</button>
                                </form>
                            </div>



                        </div>
                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
        </div>
    </div>
</div>
<script>
       // Utiliser la classe open-niveau-avancement-modal
       $(document).on("click", ".open-niveau-avancement-modal", function() {
            // Récupérer les valeurs du modal

            var numOrdre = $("#ordre_Modal").val();
            var codeProjet = $("#code_projet_input").val();
            console.log(numOrdre);
            console.log(codeProjet);

            // Effectuer la requête Ajax pour obtenir les données associées au code projet et à l'ordre
            $.ajax({
                url: '{{ route("get.donnees.formulaire")}}',
                type: 'GET',
                data: { code_projet_Modal: codeProjet, ordre_Modal: numOrdre },
                success: function(response) {
                    if (response.result && response.result.length > 0) {
                        var data = response.result[0];
                        if (data.Quantite !== undefined) {
                            $('#quantite_provisionnel_Modal').val(data.Quantite);
                            // Mettez à jour les autres champs au besoin
                        } else {
                            console.error('La propriété Quantite est indéfinie dans la réponse.');
                        }
                    } else {
                        console.error('Réponse vide ou inattendue.');
                    }
                },
                error: function(error) {
                    console.error('Erreur lors de la récupération des données pour le formulaire : ', error);
                }
            });

            // Afficher les modaux
            $('#doubleFormModal').modal('show');
            $('#niveauAvancementModal').modal('show');
        });
        document.getElementById('niveauAvancementBtn').addEventListener('click', function () {
            $('#doubleFormModal').modal('show');
        });
    $(document).ready(function() {
        $(".btn-navigate-form-step").click(function() {
            var stepNumber = $(this).attr("step_number");
            $(".form-step").addClass("d-none");
            $("#step-" + stepNumber).removeClass("d-none");
        });
    });
$(document).ready(function () {
    // Extraire les paramètres de l'URL
    var urlParams = new URLSearchParams(window.location.search);
    var codeProjet = urlParams.get('codeProjet');
    var codeActionMenerProjet = urlParams.get('codeActionMenerProjet');

    // Remplir les champs du formulaire avec les valeurs extraites
    $("#code_projet").val(codeProjet);
    $("#code_projet2").val(codeProjet);
    $("#code_action_mener_projet").val(codeActionMenerProjet);
});


$(document).ready(function () {
    // Extraire les paramètres de l'URL
    var urlParams = new URLSearchParams(window.location.search);
    var codeProjet = urlParams.get('codeProjet');
    var codeActionMenerProjet = urlParams.get('codeActionMenerProjet');

    // Remplir les champs du formulaire avec les valeurs extraites
    $("#code_projet").val(codeProjet);
    $("#code_action_mener_projet").val(codeActionMenerProjet);
    $("#code_projet_input").val(codeProjet);
    $("#code_projet_Modal").val(codeProjet);

    $.ajax({
        url: '/getNumeroOrdre', // Remplacez cela par la route réelle dans votre application
        type: 'GET',
        data: {
            codeProjet: codeProjet,
            codeActionMenerProjet: codeActionMenerProjet
        },
        success: function (data) {
            // Remplir le champ d'ordre avec la valeur obtenue
            $("#ordre").val(data.numeroOrdre);
            $("#ordre_Modal").val(data.numeroOrdre);
            $("#infrastructure").val(data.libelleInfrastructure);
            $("#infrastructurecode").val(data.codeInfrastructure);
            $("#Famillecode").val(data.codeFamilleInfrastructure);
            $("#FamilleInfrastructure").val(data.libelleFamilleInfrastructure);
        },
        error: function () {
            console.log('Erreur lors de la récupération du numéro d\'ordre.');
        }
    });
});

    $(document).ready(function () {
        $("#infrastructure").on('input', function () {
            var infrastructureCode = $("#infrastructurecode").val();
            var infrastructureInput = $(this).val();

            // Effectuer une requête AJAX pour récupérer la famille d'infrastructure
            $.ajax({
                url: '/getFamilleInfrastructure', // Remplacez cela par la route réelle dans votre application
                type: 'GET',
                data: {
                    infrastructureCode: infrastructureCode,
                    infrastructureInput: infrastructureInput
                },
                success: function (data) {
                    // Mettre à jour le champ de la famille d'infrastructure
                    $("#familleInfrastructure").val(data.familleInfrastructure);
                },
                error: function () {
                    console.log('Erreur lors de la récupération de la famille d\'infrastructure.');
                }
            });
        });
    });

    // Utilisez cette fonction pour charger les données du projet via Ajax
        /*function chargerDonneesProjet(codeProjet) {
            $.ajax({
                url: '/obtenir-donnees-projet',
                method: 'GET',
                data: { code_projet2: codeProjet },
                success: function (response) {
                    // Remplissez les champs du formulaire avec les données reçues
                    $('#date_debut').val(response.date_debut);
                    $('#date_fin').val(response.date_fin);
                    var formattedCout = number_format(response.coutEffective, 0, ' ', ' ');
                    $('#quantite').val(formattedCout);
                    $('#devise').val(response.devise);
                    $('#commentaire').val(response.commentaire);
                },
                error: function (error) {
                    console.log('Une erreur s\'est produite lors du chargement des données du projet.');
                    console.log(error);
                }
            });
        }*/

    /* Utilisez cette fonction pour déclencher le chargement des données lorsqu'un code de projet est disponible
    function chargerDonneesProjetSiCodeExiste() {
        var codeProjet = $('#code_projet2').val();
        if (codeProjet) {
            chargerDonneesProjet(codeProjet);
        }
    }*/

    // Appelez la fonction lors du chargement de la page ou lors d'un événement approprié
   /* $(document).ready(function () {
        chargerDonneesProjetSiCodeExiste();

        // Assurez-vous également de lier cette fonction à tout événement qui change le code du projet
        $('#code_projet2').on('change', function () {
            chargerDonneesProjetSiCodeExiste();
        });
    });*/

</script>
<!-- Ajoutez ce script à votre page HTML ou à votre fichier JavaScript externe -->
<script>



        ////////////////////FORMATAGE DE CHAMP NUMBER/////////////////
        function formatNumberInput(input) {
        // Supprimer tout sauf les chiffres et le séparateur décimal
        var sanitizedValue = input.value.replace(/[^0-9.]/g, '');

        // Séparer la partie entière et la partie décimale
        var parts = sanitizedValue.split(' ');
        var integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

        // Recréer la valeur avec le séparateur de milliers
        var formattedValue = integerPart;
        if (parts.length > 1) {
            formattedValue += ' ' + parts[1];
        }

        // Mettre à jour la valeur du champ
        input.value = formattedValue;
    }

    // Gérer l'événement de saisie pour le champ cout
    document.getElementById('quantite').addEventListener('input', function (event) {
        formatNumberInput(event.target);
    });
    // Fonction de formatage du nombre avec espaces comme séparateurs de milliers
    function number_format(number, decimals, decPoint, thousandsSep) {
        number = parseFloat(number);
        decimals = decimals || 0;
        var fixed = number.toFixed(decimals);
        var parts = fixed.split('.');
        var intPart = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1" + thousandsSep);
        var decPart = parts.length > 1 ? (decPoint + parts[1]) : '';
        return intPart + decPart;
    }

    ////////////////////////////////////////////////////
</script>

@endsection
