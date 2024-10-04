@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('assets/compiled/css/projet.css')}}">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="{{ asset('assets/compiled/js/select2.min.js')}}"></script>
<link rel="stylesheet" href="{{ asset('assets/compiled/css/select2.min.css')}}">


@section('content')
<style>
    .form-step .haut input {
        width: calc(95% - 1rem);
        padding: 0.5rem;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        display: inline-block;
    }
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


    #myModal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
    select,
    input[type=text],
    input[type=number],
    textarea {
        display: block;
        width: 100%;
        padding: .375rem .75rem;
        font-size: -11rem;
        font-weight: 400;
        line-height: 1.5;
        color: #607080;
        -webkit-appearance: none;
        appearance: none;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #dce7f1;
        border-radius: .25rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out
    }

    input[type=date] {
        display: block;
        width: 100%;
        padding: .375rem .75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #607080;
        -webkit-appearance: none;
        appearance: none;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #dce7f1;
        border-radius: .25rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out
    }

        table {

            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
        }

        .action-buttons button {
            margin: 5px;
        }

</style>
<div>
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Définition de projet</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Nouveau projet</li>

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
    <div id="multiple-column-form">
        <!-- Form Steps / Progress Bar -->
        <ul class="form-stepper form-stepper-horizontal text-center mx-auto pl-0">
            <!-- Step 1 -->
            <li class="form-stepper-active text-center form-stepper-list" step="1">
                <a class="mx-2">
                    <span class="form-stepper-circle">
                        <span>1</span>
                    </span>
                    <div class="label" ><h6 style="font-size: 13px;">CODIFICATION <br> DELAI ET COUT</h6></div>
                </a>
            </li>
            <!-- Step 2 -->
            <li class="form-stepper-unfinished text-center form-stepper-list" step="2">
                <a class="mx-2">
                    <span class="form-stepper-circle text-muted">
                        <span>2</span>
                    </span>
                    <div class="label text-muted"><h6 style="font-size: 13px;">BUT</h6></div>
                </a>
            </li>
            <!-- Step 3 -->


            <li class="form-stepper-unfinished text-center form-stepper-list" step="3">
                <a class="mx-2">
                    <span class="form-stepper-circle text-muted">
                        <span>3</span>
                    </span>
                    <div class="label text-muted"><h6 style="font-size: 13px;">MAITRE D'OUVRAGE </h6></div>
                </a>
            </li>
            <!-- Step 4 -->

            <li class="form-stepper-unfinished text-center form-stepper-list" step="4">
                <a class="mx-2">
                    <span class="form-stepper-circle text-muted">
                        <span>4</span>
                    </span>
                    <div class="label text-muted"><h6 style="font-size: 13px;">FINANCEMENTS</h6></div>
                </a>
            </li>
             <!-- Step 5 -->
            <li class="form-stepper-unfinished text-center form-stepper-list" step="5">
                <a class="mx-2">
                    <span class="form-stepper-circle text-muted">
                        <span>5</span>
                    </span>
                    <div class="label text-muted"><h6 style="font-size: 13px;">AGENCES</h6></div>
                </a>
            </li>
            <!-- Step 6 -->
            <li class="form-stepper-unfinished text-center form-stepper-list" step="6">
                <a class="mx-2">
                    <span class="form-stepper-circle text-muted">
                        <span>6</span>
                    </span>
                    <div class="label text-muted"><h6 style="font-size: 13px;">CHEF DE PROJET</h6></div>
                </a>
            </li>
        </ul>

        <!-- Step Wise Form Content -->
        <div class="row match-height">
            <div class="col-12">
                <div class="card">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{session('success')}}
                        </div>
                    @elseif (session('error'))
                        <div class="alert alert-danger">
                            {{session('error')}}
                        </div>
                    @endif

                    <form id="userAccountSetupForm" name="userAccountSetupForm" action="{{ route('enregistrer.formulaire') }}" enctype="multipart/form-data" method="POST">
                        <!-- Step 1 Content -->
                         @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required> <!-- {{ csrf_field() }} -->
                        <section id="step-1" class="form-step">
                            <div class="date-section">

                                <h2 class="font-normal">Codification Projet</h2>
                            </div>


                            <!-- Step 1 input fields -->
                            <div class="haut">
                                <div class="row">
                                    <div class="col-3" style="width: 30%;">
                                        <label for="code_projet">Code du projet :</label>
                                        <input type="text" class="form-control" id="code_projet"  name="code_projet" readonly placeholder="Afficher le code du projet">

                                    </div>


                                    <div class="col">
                                        <label for="stat_projet" style="text-align: right; justify-content: right;">Statut du projet</label>
                                        <input type="text" name="statut" class="form-control" id="statutInput" style="width:90px; text-align: right; float: right; justify-content: right;" readonly>
                                        <input type="hidden" name="code_statut" class="form-control" id="codeStatutInput">
                                    </div>
                                </div><br>

                                <div class="row">
                                    <div class="col">
                                        <label for="district">District:</label>
                                        <select id="district" class="form-select" name="district" >
                                            <option value="">Sélectionner un district</option>
                                            @foreach ($districts as $district)
                                                <option value="{{ $district->code }}">{{ $district->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label for="region">Région:</label>
                                        <select id="region" class="form-select" name="region">
                                        <option value="">Sélectionner une region</option>
                                        @foreach ($regions as $region)
                                            <option value="{{ $region->code }}">{{ $region->libelle }}</option>
                                        @endforeach
                                        </select>
                                    </div>

                                </div>
                                    <div class="row">
                                        <div class="col">
                                                <label for="domaine">Domaine:</label>
                                                <select id="domaine" class="form-select" name="domaine">
                                                    <option value="">Sélectionner un domaine</option>
                                                    @foreach ($domaine_Info as $domaine)
                                                    <option value="{{ $domaine->code }}">{{ $domaine->libelle }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        <div class="col">
                                            <label for="sous_domaine">Sous-domaine:</label>
                                            <select id="sous_domaine" name="sous_domaine" class="form-select">
                                                <option value="">Sélectionner un sous-domaine</option>
                                                @foreach ($sous_domaine_Info as $sous_dom)
                                                <option value="{{$sous_dom->code}}">{{$sous_dom->libelle}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                            </div>

                            <div class="mt-3">
                                <div class="gauche">

                                    <br>
                                    <center>Date prévisionnelle</center>
                                    <div class="row">
                                        <div class="col">
                                            <label for="date_debut">Début:</label>
                                            <input type="date" class="form-control" id="date_debut" name="date_debut">
                                        </div>
                                        <div class="col">
                                            <label for="date_fin">Fin :</label>
                                            <input type="date" id="date_fin" class="form-control" name="date_fin" width="10">
                                        </div>
                                    </div>
                                </div>

                                <div class="droit">

                                    <div class="inline2" style="top: 50px;">
                                        <div class="row">
                                            <div class="col">
                                                <label for="cout">Coût :</label>
                                                <input type="text" class="form-control" id="cout-display" name="cout" min="0" style="text-align: right; justify-content: right;" oninput="formatCurrency('cout-display')" />
                                            </div>
                                            <div class="col">
                                                <label for="date_fin">Devise :</label>
                                                <input type="text" id="date_fin" value="XOF" readonly placeholder="XOF" class="form-control" width="10">
                                                <input type="hidden" value="57" id="deviseProject" name="deviseProject">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="mt-3">
                                <button class="button btn-navigate-form-step" type="button" step_number="2" style="float: right; margin-left: auto;">Suivant</button>
                            </div>
                        </section>

                        <section id="step-2" class="form-step d-none">
                            <h2 class="font-normal">But du projet</h2>

                            <!-- Step 2 input fields -->
                            <div style="width: 100%;">
                                <fieldset class="border p-2 mt-5">
                                    <legend class="w-auto">Actions</legend>
                                    <div class="row">
                                        <div class="col-1" style="width: 10%;">
                                            <p for="action">N ordre:</p>
                                            <input type="number" name="nordre" id="nordre" value="1" readonly class="form-control">
                                        </div>
                                        <div class="col-2" style="width: 25%;">
                                            <p for="action">Action à mener:</p>
                                            <select id="action" class="form-select" name="actionMener">
                                                <option value="">Sélectionner </option>
                                                @foreach ($actionMener as $action)
                                                <option value="{{ $action->code }}">{{ $action->libelle }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-2" style="width: 11%;">
                                            <p for="quantite">Quantité:</p>
                                            <input type="number" class="form-control"  min="0" id="quantite" name="quantite" style="width: 88%; text-align: right; justify-content: right;" >
                                        </div>
                                        <div class="col-2" style="width: 15%;">
                                            <p for="action">Unité mésure:</p>
                                            <select id="action_unite_mesure" class="form-select" name="uniteMesure">
                                                <option value="">Unité mésure</option>
                                                @foreach ($unite_mesure as $uM)
                                                <option value="{{$uM->id}}">{{$uM->libelle_court}}</option>
                                                @endforeach
                                                @foreach ($uniteVol as $uV)
                                                <option value="{{$uV->code}}">{{$uV->libelle_court}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-2" style="width: 22%;">
                                            <p for="infrastructure">Infrastructure:</p>
                                            <select name="infrastructure" class="form-select" id="insfrastructureSelect">
                                                <option value="">Sélectionner l'infrastructure</option>
                                                @foreach ($infrastruc as $infras)
                                                <option value="{{ $infras->code}}">{{$infras->libelle}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-2" style="margin-top: 7px; width: 17%;">
                                            <a href="#" data-toggle="modal" data-target="#largeModal">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"></path>
                                                </svg>
                                                Bénéficiaire
                                            </a>
                                            <div class="modal fade" id="largeModal" style="background-color: #DBECF8;" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
                                                <div class="modal-dialog modal-lg" style="background-color: #fff;">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title" id="myModalLabel">Bénéficiaires</h4>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="row">
                                                                    <label for="structure_ratache">Bénéficiaire :</label>
                                                                    <input type="hidden" name="CodeProjetBene" id="CodeProjetBene">
                                                                    <input type="hidden" name="numOrdreBene" id="numOrdreBene">

                                                                    <div class="col-2" style="width: 16%;">
                                                                        <label for="age">Localité :</label>
                                                                        <input type="radio" name="beneficiaire_type[]" value="localite" id="age" checked="true" onclick="afficheSelect('localite')" style="margin-right: 15px;">
                                                                    </div>
                                                                    <div class="col-2" style="width: 24%;">
                                                                        <label for="sousp">Sous-préfecture :</label>
                                                                        <input type="radio" name="beneficiaire_type[]" value="sous_prefecture1" id="sousp" onclick="afficheSelect('sous_prefecture1')" style="margin-right: 15px;">
                                                                    </div>
                                                                    <div class="col-2" style="width: 21%;">
                                                                        <label for="min">Département :</label>
                                                                        <input type="radio" name="beneficiaire_type[]" value="departement" id="dep" onclick="afficheSelect('departement2')" style="margin-right: 15px;">

                                                                    </div>
                                                                    <div class="col-2" style="width: 15%;">
                                                                        <label for="min">Region :</label>
                                                                        <input type="radio" name="beneficiaire_type[]" value="region" id="reg" onclick="afficheSelect('region2')" style="margin-right: 15px;">

                                                                    </div>
                                                                    <div class="col-2" style="width: 14%;">
                                                                        <label for="dis">District :</label>
                                                                        <input type="radio" name="beneficiaire_type[]" value="district" id="dis" onclick="afficheSelect('district1')">
                                                                    </div>
                                                                    <div class="col-2" style="width: 22%;">
                                                                        <label for="min">Etablissement :</label>
                                                                        <input type="radio" name="beneficiaire_type[]" value="etablissement" id="min" onclick="afficheSelect('etablissement')" style="margin-right: 15px;">

                                                                    </div>
                                                                </div>
                                                                <div class="col">
                                                                    <select name="beneficiaire_code[]" id="localite" class="form-select" style="display: none;">
                                                                        <option value="">Sélectionner la localité</option>
                                                                        @foreach ($localite as $loca)
                                                                        <option value="{{$loca->code}}">{{$loca->libelle}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <select name="beneficiaire_code[]" id="sous_prefecture1" class="form-select" style="display: none;">
                                                                        <option value="">Sélectionner la sous préfecture</option>
                                                                        @foreach ($sous_prefecture as $sous)
                                                                        <option value="{{$sous->code}}">{{$sous->libelle}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <select name="beneficiaire_code[]" id="departement2" class="form-select" style="display: none;">
                                                                        <option value="">Sélectionner le departement</option>
                                                                        @foreach ($departements as $depart)
                                                                        <option value="{{$depart->code}}">{{$depart->libelle}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <select name="beneficiaire_code[]" id="region2" class="form-select" style="display: none;">
                                                                        <option value="">Sélectionner le region</option>
                                                                        @foreach ($regions as $regio)
                                                                        <option value="{{$regio->code}}">{{$regio->libelle}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <select name="beneficiaire_code[]" id="district1" class="form-select" style="display: none;">
                                                                        <option value="">Sélectionner le district</option>
                                                                        @foreach ($districts as $dis)
                                                                        <option value="{{$dis->code}}">{{$dis->libelle}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <select name="beneficiaire_code[]" id="etablissement" class="form-select" style="display: none;">
                                                                        <option value="">Sélectionner l'établissement</option>
                                                                        @foreach ($etablissements as $etab)
                                                                        <option value="{{$etab->code}}">{{$etab->nom_etablissement}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="col">
                                                                    <button type="button" class="btn btn-secondary" id="addBtn">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"></path>
                                                                        </svg>
                                                                        Ajouter
                                                                    </button>
                                                                </div>
                                                                <div class="col">
                                                                    <button type="button" class="btn btn-danger" style="width: 121px" id="deleteBtn">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                                                            <path d="M1.5 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2V3h2a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1h2V2zM1 5v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V5H1zm3 0v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V5H4z"></path>
                                                                        </svg>
                                                                        Supprimer
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <div class="row" style="align-items: center;">
                                                                <div class="col">
                                                                    <div class="table-container">
                                                                        <table id="beneficiaireTable">
                                                                            <thead>
                                                                                <tr>
                                                                                    <th class="etablissement"><input type="checkbox"></th>
                                                                                    <th class="etablissement">Code</th>
                                                                                    <th class="etablissement">Libellé</th>
                                                                                    <th class="etablissement">Type</th>

                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>

                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                                <button type="button" style="margin-top: 7px; float: right;" class="btn btn-secondary" id="addAction">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"></path>
                                                    </svg>
                                                    Action
                                                </button>
                                        </div>
                                </fieldset>
                                <hr>
                                <div>
                                    <div class="table-container">
                                        <table id="tableActionMener">
                                            <thead>
                                                <tr>
                                                    <th>N° d'ordre</th>
                                                    <th>Action</th>
                                                    <th>Quantité</th>
                                                    <th>Unité de mésure</th>
                                                    <th>Infrastructure</th>
                                                    <th>libelle Bénéficiaires</th>
                                                    <th>Code bénéficiaire</th>
                                                    <th>type bénéficiaire</th>
                                                    <th hidden>ActionCode</th>
                                                    <th hidden>mesureCode</th>
                                                    <th hidden>InfrastructureCode</th>
                                                </tr>
                                            </thead>
                                            <tbody id="beneficiaire-table-body">
                                                <!-- Le corps du tableau sera géré dynamiquement via JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button class="button btn-navigate-form-step" type="button" step_number="1">Retour</button>
                                <button class="button btn-navigate-form-step" type="button" step_number="3">Suivant</button>

                            </div>
                        </section>

                        <section id="step-3" class="form-step d-none">

                            <h2 class="font-normal">Maître d'ouvrage</h2>
                            <!-- Step 5 input fields -->
                            <div class="mt-3">
                                <div class="row" style="--bs-gutter-x: 8.5rem;">

                                    <div class="col">
                                        <label for="minist">Ministère :</label>
                                        <input type="radio" name="structure" value="minist" id="minist" onclick="Affiches('ministeres')">

                                        <label for="collect">Collectivité territoriale :</label>
                                        <input type="radio" name="structure" value="collect" id="collect" onclick="Affiches('collectivite')" style="margin-right: 5px;">

                                        <select name="ministere_code" id="agenceMaitre" class="form-select" style="display: none; width: 138%;">
                                            <option value="">Selectionner la collectivité territoriale</option>
                                            @foreach ($collectivite as $collectivites)
                                            <option value="{{$collectivites->code_bailleur}}">{{$collectivites->libelle_long}}</option>
                                            @endforeach
                                        </select>
                                        <select name="ministere_code" id="ministereMaitre" class="form-select" style="display: none;" name="code_ministere">
                                            <option value="">Selectionner le ministère</option>
                                            @foreach ($ministere as $min)
                                            <option value="{{$min->code}}">{{$min->libelle}}</option>
                                            @endforeach
                                        </select>



                                    </div>

                                    <div class="col">
                                        <button type="button" style="margin-top: 30; float: right;" class="btn btn-secondary" id="addMinis" onclick="">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"></path>
                                            </svg>
                                            Ajouter
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-container">
                                <table id="tableMinis">
                                    <thead>
                                        <tr>

                                            <th>Code</th>
                                            <th>Libellé</th>
                                        </tr>
                                    </thead>
                                    <tbody id="beneficiaire-table-body">

                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">

                                <button class="button btn-navigate-form-step" type="button" step_number="2">Retour</button>
                                <button class="button btn-navigate-form-step" type="button" step_number="4">Suivant</button>

                            </div>
                        </section>

                        <section id="step-4" class="form-step d-none">
                            <h2 class="font-normal">Ressources financières</h2>
                            <div class="row">
                                <div class="col-3" style="float: right;">
                                <label for="public" >Type financement</label>
                                <select name="bailleur_financement" id="type_financement" class="form-select ">
                                    @foreach ($financements as $financement)
                                        <option value="{{$financement->code}}">{{$financement->libelle}}</option>
                                    @endforeach
                                </select>
                                </div>
                            </div>

                            <!-- Step 3 input fields -->
                            <div class="mt-3">
                                <div class="row">
                                    <div class="col">
                                        <label for="bailleur" style="text-align: left;">Bailleur:</label>
                                        <select id="bailleur" class="form-select" name="bailleur_code">
                                            <option value="">Sélectionner le bailleur</option>
                                            @foreach ($bailleurs as $ba)
                                            <option value="{{ $ba->code_bailleur }}">{{ $ba->libelle_long }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label for="Montant" style="text-align: left;">Montant:</label>
                                        <input type="text" class="form-control" min="0" name="montant_bailleur" id="montant" style="text-align: right; justify-content: right;" oninput="formatCurrency('montant')" />
                                    </div>
                                    <div class="col">
                                        <label for="Dévise" style="text-align: left;">Dévise:</label>
                                        <select name="bailleur_devise" class="form-select" id="devise" >
                                            <option value="">Sélectionner la dévise</option>
                                            @foreach ($devises as $de)
                                            <option value="{{ $de->code }}">{{ $de->code_long }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                        <div class="col">
                                            <label for="partie">Partie</label>
                                                <div>
                                                    <input type="checkbox" name="bailleur_partie" id="oui" onclick="onCheckboxClick('oui')" >
                                                    <label for="oui">Oui</label>

                                                    <input type="checkbox" name="bailleur_partie" id="non" onclick="onCheckboxClick('non')">
                                                    <label for="non">Non</label>
                                                </div>
                                                <div id="myModal" style="display: none;" >
                                                    <center><h5>Action à mener du bailleur</h5></center>
                                                    <hr>
                                                    <div class="table-container">
                                                        <table id="TableBailleurAction">
                                                            <thead>
                                                                <tr>
                                                                    <th>N° d'ordre</th>
                                                                    <th>Action</th>
                                                                    <th>Quantité</th>
                                                                    <th>Unité de mesure</th>
                                                                    <th>Infrastructure</th>
                                                                    <th><input id="checkboxBailleurAction" type="checkbox"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="bailleurAction-table-body">
                                                                <!-- Table body content goes here -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <button type="button" class="btn btn-primary" data-dismiss="modal" style="margin-top: 25px; float: left;" onclick="fermerModal()">Fermer</button>
                                                    <a type="button" class="btn btn-primary" id="enrBailleurAction" style="background-color: #607080; color: #fff; margin-top: 25px; float: right;">Enregistrer</a>
                                                </div>
                                        </div>
                                        <div class="col">
                                            <label for="commentaire" style="text-align: left;">Commentaire:</label>
                                            <textarea name="bailleur_commentaire" id="commentBailleur" cols="3" class="form-control" rows="8" style="height: 50px; width: 136%;"></textarea>
                                        </div>
                                        <div class="col">
                                            <button type="button" id="addbaill" style="margin-top: 30; float: right;" class="btn btn-secondary" onclick="addBailleurField()">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"></path>
                                                </svg>
                                                Ajouter
                                            </button>
                                        </div>
                                        <div>
                                            <div class="table-container">
                                                <table id="tableBailleur">
                                                    <thead>
                                                        <tr>
                                                            <th>Bailleur</th>
                                                            <th>Montant</th>
                                                            <th>Devise</th>
                                                            <th>Commentaire</th>
                                                            <th>type financement</th>
                                                            <th>Partie</th>
                                                            <th>nordre</th>
                                                            <th><input type="checkbox" name="actionBailleurCheck" id="actionBailleurCheck"></th>
                                                            <th hidden>bailleur code</th>
                                                            <th hidden>devise code</th>
                                                            <th hidden>financement code</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="bailleur-table-body">
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button class="button btn-navigate-form-step" type="button" step_number="3">Retour</button>
                                <button class="button btn-navigate-form-step" type="button" step_number="5">Suivant</button>
                            </div>
                        </section>

                        <section id="step-5" class="form-step d-none">


                            <h2 class="font-normal">Régie financière & Maître d'œuvre</h2>

                            <!-- Step 4 input fields -->
                            <div class="mt-3">

                                <div class="row" style="--bs-gutter-x: 8.5rem;">
                                    <div class="col">
                                        <label for="inputState">Agence</label>
                                        <select id="inputState" name="inputState" class="form-select" style="width: 149%;">
                                            <option value="">Selectionner l'agence</option>
                                            @foreach ($agence as $ag)
                                            <option value="{{ $ag->code_agence_execution }}"
                                            data-nom="{{ $ag->nom_agence }}"
                                            data-telephone="{{ $ag->telephone }}"
                                            data-email="{{ $ag->email }}"
                                            data-adresse="{{ $ag->addresse }}">
                                            {{ $ag->nom_agence }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col" style="margin-top: 40;">
                                        <input type="radio" id="customRadioInline1" checked name="customRadioInline1" class="custom-control-input" value="regie" onclick="updateNiveau(1)">
                                        <label class="custom-control-label" for="customRadioInline1" style="color: black;">Régie financière</label>
                                    </div>
                                    <div class="col" style="margin-top: 40;">
                                        <input type="radio" id="customRadioInline2" name="customRadioInline1" class="custom-control-input" value="maitreOeuvre" onclick="updateNiveau(2)">
                                        <label class="custom-control-label" for="customRadioInline2" style="color: black;">Maître d'œuvre</label>
                                    </div>
                                    <input type="hidden" name="niveau" id="niveau" value="1"> <!-- Champ caché pour le niveau -->
                                    <div class="col">
                                            <button type="button" style="margin-top: 30; float: right;" class="btn btn-secondary" onclick="addRegieMaitre()">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"></path>
                                                </svg>
                                                Ajouter
                                            </button>
                                        </div>
                                </div>
                            </div>
                            <div class="table-container">
                                <table id="regieMaitreTable">
                                    <thead>
                                        <tr>

                                            <th>Nom </th>
                                            <th>Type agence</th>
                                            <th>Télephone</th>
                                            <th>Email</th>
                                            <th>Adresse</th>
                                            <th hidden>niveau</th>
                                            <th hidden>code</th>
                                        </tr>
                                    </thead>
                                    <tbody id="beneficiaire-table-body">
                                        <!-- Le corps du tableau sera géré dynamiquement via JavaScript -->
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3">

                            <button class="button btn-navigate-form-step" type="button" step_number="4">Retour</button>
                                <button class="button btn-navigate-form-step" type="button" step_number="6">Suivant</button>
                            </div>
                        </section>

                        <section id="step-6" class="form-step d-none">

                            <h2 class="font-normal">Chef de projet</h2>

                            <!-- Step 6 input fields -->
                            <div class="mt-3">


                                <div class="row" style="--bs-gutter-x: 8.5rem;">
                                    <div class="col">
                                        <label for="agen">Agence :</label>
                                        <input type="radio" name="structure" value="agen" id="agen" onclick="Affiche('agence')" style="margin-right: 5px;">
                                        <label for="minis">Ministère :</label>
                                        <input type="radio" name="structure" value="mini" id="minis" onclick="Affiche('ministere')">

                                        <select name="ministère" id="ministereA" class="form-select" style="display: none;">
                                            <option value="">Selectionner le ministère</option>
                                            @foreach($ministere as $mini)
                                            <option value="{{ $mini->code }}" data-fonction="{{ $mini->libelle }}">
                                                {{ $mini->libelle }}
                                            </option>
                                            @endforeach
                                        </select>

                                        <select name="chefProjet_code" id="agenceA" class="form-select" style="display: none;">
                                            <option value="">Selectionner l'agence</option>
                                            @foreach($agence as $agence)
                                            <option value="{{ $agence->code_agence_execution }}" data-fonction="{{ $agence->nom_agence }}">
                                                {{ $agence->nom_agence }}
                                            </option>
                                            @endforeach
                                        </select>

                                    </div>
                                    <div class="col">
                                        <label for="inputProjet">Chef de projet</label>
                                        <select id="inputProjet" name="code_personnel" class="form-select" style="width: 149%;">
                                        <option value="">Sélectionner le chef</option>
                                        @foreach ($personnel as $person)

                                        // Récupérez la fonction de l'utilisateur actuel
                                            @if ($person->latestFonction)
                                                @php
                                                    $fonctionUtilisateur = $person->latestFonction->fonctionUtilisateur->libelle_fonction;
                                                @endphp
                                            @endif

                                            <option value="{{ $person->code_personnel }}"
                                                    data-nom="{{ $person->nom }}"
                                                    data-prenom="{{ $person->prenom }}"
                                                    data-structure-accueil="{{ $person->code_structure_agence }}"
                                                    data-adresse="{{ $person->addresse }}"
                                                    data-telephone="{{ $person->telephone }}"
                                                    data-email="{{ $person->email }}"
                                                    data-fonction="{{ $fonctionUtilisateur }}">
                                                {{ $person->nom }} {{ $person->prenom }}
                                            </option>

                                        @endforeach
                                    </select>

                                    </div>



                                    <div class="col">
                                            <button type="button" style="margin-top: 30; float: right;" class="btn btn-secondary" onclick="addChefProjet()">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"></path>
                                                </svg>
                                                Ajouter
                                            </button>
                                        </div>
                                </div>
                                </div>
                                <div class="table-container">
                                <table id="chefProjetTable">
                                    <thead>
                                        <tr>

                                            <th>Nom</th>
                                            <th>Prénom</th>
                                            <th>Adresse</th>
                                            <th>Téléphone</th>
                                            <th>Email</th>
                                            <th>Fonction</th>
                                            <th>Structure accueil</th>
                                            <th hidden>Code personnel</th>
                                        </tr>
                                    </thead>
                                    <tbody id="beneficiaire-table-body">

                                    </tbody>
                                </table>
                                </div>


                            <div class="mt-3">
                                <button class="button btn-navigate-form-step" type="button" step_number="5">Retour</button>
                                <button class="button submit-btn" id="enregistrerEHA" type="submit">Enregistrer</button>
                            </div>
                        </section>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal pour la fiche récapitulative -->
<div class="modal fade" id="recapModal" tabindex="-1" role="dialog" aria-labelledby="recapModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recapModalLabel">Fiche Récapitulative</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="recapContent">
                <!-- Le contenu de la fiche sera généré dynamiquement -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Retour</button>
                <button type="button" class="btn btn-primary" onclick="window.print();">Imprimer</button>
                <button type="button" class="btn btn-success" id="saveData">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/compiled/js/projet.js')}}"></script>
<!--code js pour les dates systèmes-->
<script>

    function formatCurrency(inputId) {
        // Récupérer la valeur actuelle du champ
        var inputValue = document.getElementById(inputId).value;

        // Supprimer les espaces existants (pour permettre la saisie continue)
        inputValue = inputValue.replace(/\s/g, '');

        // Formater le nombre avec des séparateurs de milliers
        var formattedValue = inputValue.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

        // Mettre à jour la valeur du champ avec le format
        document.getElementById(inputId).value = formattedValue;
    }


    ///////////////////////////DEFINITION DE PROJET ///////////////////
        // Script JavaScript pour la mise à jour dynamique des régions en fonction des districts


        // Script JavaScript pour la mise à jour dynamique des sous-domaines en fonction du domaine



        $(document).ready(function() {
            function updateProjectCode() {
                // Récupérez les valeurs de chaque sélecteur
                var districtCode = $("#district option:selected").val();
                var regionCode = $("#region option:selected").val();
                var domaineCode = $("#domaine option:selected").val();
                var sousDomaineCode = $("#sous_domaine option:selected").val();

                // Récupérez l'année de la date de début
                var dateDebut = new Date($("#date_debut").val());
                var anneeDebutCode = dateDebut.getFullYear();

                // Construisez le code projet
                var projetCode = `PEHA_D${districtCode}R${regionCode}_${sousDomaineCode}_${anneeDebutCode}_`;

                // Effectuez une requête AJAX pour vérifier si un code similaire existe dans la base de données
                $.ajax({
                    url: '/verifier_code_projet', // Remplacez par l'URL correcte de votre backend pour vérifier le code
                    method: 'GET',
                    data: {
                        code: projetCode
                    },
                    success: function(response) {
                        if (response.existe) {
                            // Si un code similaire existe, incrémentez le rang
                            var nouveauRang = (parseInt(response.dernierRang) + 1).toString().padStart(2, '0');
                            projetCode += nouveauRang;
                        } else {
                            // Si aucun code similaire n'existe, définissez le rang à 01
                            projetCode += '01';
                        }

                        // Mettez à jour le champ de texte avec le nouveau code
                        $("#code_projet").val(projetCode);
                    },
                    error: function(error) {
                        console.error('Erreur lors de la vérification du code projet :', error);
                    }
                });
            }

            // Écoutez les changements sur tous les sélecteurs et champs nécessaires
            $("#district, #region, #domaine, #sous_domaine, #date_debut").change(updateProjectCode);

            // Initialisez le code projet dès que la page est prête
            updateProjectCode();
        });

        //STATUT DU PROJET /

        $(document).ready(function () {
            var statusId = '01';

            $.ajax({
                url: '/get-project-status/' + statusId,
                method: 'GET',
                success: function (response) {
                    // Mettez à jour la valeur du champ de texte avec le libellé du statut
                    $('#statutInput').val(response.label);

                    // Mettez à jour la valeur du champ caché avec le code du statut
                    $('#codeStatutInput').val(response.code);
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });
    /////////////////////////// FIN DEFINITION DE PROJET ///////////////////

    ///////////////////////////BUT DU PROJET ///////////////////

        //afficher les bénéficiaires (localité, etablissement et districts)
        function afficheSelect(selectId) {
            // Hide all selects
            $('#localite,#sous_prefecture1 ,  #district1,  #etablissement, #departement2, #region2').hide();

            // Show the selected select
            $('#' + selectId).show();
        }
        $(document).ready(function() {
            $("#age").prop("checked", true);
            afficheSelect('localite');
        });
        //Bouton ajouter des bénéficiaires
        $(document).ready(function() {
            // Initialize your modal and table elements
            var modal = $('#largeModal');
            var table = $('#beneficiaireTable tbody');
            var data = [];


            $('#addBtn').on('click', function() {
                var code;
                var libelle;
                var type; // Ajout d'une variable pour le type de bénéficiaire

                if ($('#age').prop('checked')) {
                    code = $('#localite').val();
                    libelle = $('#localite option:selected').text();
                    type = 'localite'; // Définition du type de bénéficiaire
                } else if ($('#sousp').prop('checked')) {
                    code = $('#sous_prefecture1').val();
                    libelle = $('#sous_prefecture1 option:selected').text();
                    type = 'sous_prefecture';
                } else if ($('#min').prop('checked')) {
                    code = $('#etablissement').val();
                    libelle = $('#etablissement option:selected').text();
                    type = 'etablissement'; // Définition du type de bénéficiaire
                } else if ($('#reg').prop('checked')) {
                    code = $('#region2').val();
                    libelle = $('#region2 option:selected').text();
                    type = 'region'; // Définition du type de bénéficiaire
                } else if ($('#dis').prop('checked')) {
                    code = $('#district1').val();
                    libelle = $('#district1 option:selected').text();
                    type = 'district'; // Définition du type de bénéficiaire
                } else if ($('#dep').prop('checked')) {
                    code = $('#departement2').val();
                    libelle = $('#departement2 option:selected').text();
                    type = 'departement'; // Définition du type de bénéficiaire
                }

                if (code && libelle && type) {
                    // Vérifier si l'élément existe déjà dans le tableau
                    var exists = false;
                    $('#beneficiaireTable tbody tr').each(function() {
                        var existingCode = $(this).find('td:eq(1)').text();
                        var existingType = $(this).find('td:eq(3)').text();
                        if (existingCode === code && existingType === type) {
                            exists = true;
                            return false; // Sortir de la boucle each
                        }
                    });

                    if (!exists) {
                        // Ajouter un nouvel objet à la variable data avec les valeurs actuelles
                        var newRow = {
                            code: code,
                            libelle: libelle,
                            type: type
                        };
                        data.push(newRow);

                        // Ajouter une nouvelle ligne au tableau avec le type de bénéficiaire
                        table.append('<tr><td><input type="checkbox"></td><td>' + code + '</td><td>' + libelle + '</td><td>' + type + '</td></tr>');
                    } else {
                        $('#alertMessage').text('Cet élément existe déjà dans le tableau.');
                        $('#alertModal').modal('show');
                    }
                }

                // Fermer la modal
                modal.modal('hide');
            });




            // Gestionnaire du clic sur la checkbox d'en-tête pour sélectionner toutes les checkboxes
            $('#beneficiaireTable thead input[type="checkbox"]').on('click', function() {
                var isChecked = $(this).prop('checked');
                $('#beneficiaireTable tbody input[type="checkbox"]').prop('checked', isChecked);
            });

            // Gestionnaire du clic sur le bouton "Supprimer"
            $('#deleteBtn').on('click', function() {
                // Trouvez et supprimez les lignes sélectionnées
                $('#beneficiaireTable tbody input[type="checkbox"]:checked').closest('tr').remove();
            });

        });

        $(document).ready(function() {
            $('#addAction').on('click', function() {
                // Vérifier si au moins un bénéficiaire est sélectionné
                if ($('#beneficiaireTable tbody tr').length === 0) {
                    $('#alertMessage').text("Veuillez sélectionner au moins un bénéficiaire avant de cliquer sur le bouton Action.");
                        $('#alertModal').modal('show');

                    return;
                }

                // Récupérer les valeurs des champs
                var nordre = parseInt($('#nordre').val());
                var actionMener = $('#action').val();
                var quantite = $('#quantite').val();
                var uniteMesure = $('#action_unite_mesure').val();
                var infrastructure = $('#insfrastructureSelect').val();

                // Vérifier si toutes les données sont sélectionnées ou saisies
                if (nordre && actionMener && quantite && uniteMesure && infrastructure) {
                    // Récupérer les données du tableau #beneficiaireTable
                    var beneficiaireData = [];
                    $('#beneficiaireTable tbody tr').each(function() {
                        var rowData = {
                            code: $(this).find('td:eq(1)').text(),
                            libelle: $(this).find('td:eq(2)').text(),
                            type: $(this).find('td:eq(3)').text()
                        };
                        beneficiaireData.push(rowData);
                    });

                    // Vérifier si les données existent déjà dans le tableau #tableActionMener
                    var existeDeja = false;
                    $('#tableActionMener tbody tr').each(function() {
                        var existingAction = $(this).find('td:eq(1)').text();
                        var existingQuantite = $(this).find('td:eq(2)').text();
                        var existingUniteMesure = $(this).find('td:eq(3)').text();
                        var existingInfrastructure = $(this).find('td:eq(4)').text();

                        if (
                            existingAction === actionMener &&
                            existingQuantite === quantite &&
                            existingUniteMesure === uniteMesure &&
                            existingInfrastructure === infrastructure
                        ) {
                            existeDeja = true;
                            return false; // Sortir de la boucle each
                        }
                    });

                    if (!existeDeja) {
                        // Ajouter les données récupérées au tableau #tableActionMener
                        var tableActionMener = $('#tableActionMener tbody');
                        beneficiaireData.forEach(function(row) {
                            tableActionMener.append(
                                '<tr><td>' + nordre + '</td><td>' + getActionLibelle(actionMener) + '</td><td>' + quantite + '</td><td>' + getUniteMesureLibelle(uniteMesure) + '</td><td>' + getInfrastructure(infrastructure) + '</td><td>' + row.libelle + '</td><td>' + row.code + '</td><td>' + row.type + '</td><td hidden>' + actionMener + '</td><td hidden>' + uniteMesure + '</td><td hidden>' + infrastructure + '</td> </tr>');
                        });

                        // Incrémenter le champ #nordre
                        $('#nordre').val(nordre + 1);

                        // Fonction pour obtenir le libellé de l'action à partir de son code
                        function getActionLibelle(actionCode) {
                            var actionLibelle = '';
                            $('#action option').each(function() {
                                if ($(this).val() === actionCode) {
                                    actionLibelle = $(this).text();
                                    return false; // Sortir de la boucle each
                                }
                            });
                            return actionLibelle;
                        }
                        function getInfrastructure(infrastructureCode) {
                            var infrastructureLibelle = '';
                            $('#insfrastructureSelect option').each(function() {
                                if ($(this).val() === infrastructureCode) {
                                    infrastructureLibelle = $(this).text();
                                    return false; // Sortir de la boucle each
                                }
                            });
                            return infrastructureLibelle;
                        }

                        // Fonction pour obtenir le libellé de l'unité de mesure à partir de son code
                        function getUniteMesureLibelle(uniteMesureCode) {
                            var uniteMesureLibelle = '';
                            $('#action_unite_mesure option').each(function() {
                                if ($(this).val() === uniteMesureCode) {
                                    uniteMesureLibelle = $(this).text();
                                    return false; // Sortir de la boucle each
                                }
                            });
                            return uniteMesureLibelle;
                        }

                        // Réinitialiser les champs à null après l'ajout
                        $('#action').val('');
                        $('#quantite').val('');
                        $('#action_unite_mesure').val('');
                        $('#insfrastructureSelect').val('');

                        // Vider le tableau des bénéficiaires
                        $('#beneficiaireTable tbody').empty();
                    } else {
                        $('#alertMessage').text("Ces données existent déjà dans le tableau.");
                        $('#alertModal').modal('show');
                    }
                } else {
                    $('#alertMessage').text("Veuillez sélectionner et saisir toutes les données avant d\'ajouter.");
                        $('#alertModal').modal('show');
                }

            });
        });
    /////////////////////////// FIN BUT DU PROJET ///////////////////

    ////////////////////////////MAITE D'OUVRAGE//////////////////////
        $(document).ready(function() {
            // Affiche le sélecteur de ministère par défaut
            AfficheMinistereParDefaut();
        });

        function AfficheMinistereParDefaut() {
            $('#ministereMaitre').show();
            $('#agenceMaitre').hide();
        }

        function Affiches(type) {
            if (type === 'ministeres') {
                $('#ministereMaitre').show();
                $('#agenceMaitre').hide();
            } else if (type === 'collectivite') {
                $('#agenceMaitre').show();
                $('#ministereMaitre').hide();
            }
        }
        $(document).ready(function() {
            // Bouton ajouter des ministères
            $('#addMinis').on('click', function() {
                // Récupérer les valeurs sélectionnées
                var selectedStructure = $('input[name="structure"]:checked').val();

                // Vérifier si une structure est sélectionnée
                if (selectedStructure) {
                    var selectedCode = '';
                    var selectedLibelle = '';

                    if (selectedStructure === 'collect') {
                        // Si la collectivité territoriale est sélectionnée
                        selectedCode = $('#agenceMaitre').val();
                        selectedLibelle = $('#agenceMaitre option:selected').text();
                    } else if (selectedStructure === 'minist') {
                        // Si le ministère est sélectionné
                        selectedCode = $('#ministereMaitre').val();
                        selectedLibelle = $('#ministereMaitre option:selected').text();
                    }

                    // Vérifier si les données existent déjà dans le tableau #tableMinis
                    var existeDeja = false;
                    $('#tableMinis tbody tr').each(function() {
                        var existingCode = $(this).find('td:eq(0)').text();
                        var existingLibelle = $(this).find('td:eq(1)').text();

                        if (
                            existingCode === selectedCode &&
                            existingLibelle === selectedLibelle
                        ) {
                            existeDeja = true;
                            return false; // Sortir de la boucle each
                        }
                    });

                    if (!existeDeja) {
                        // Ajouter les données au tableau #tableMinis
                        var tableMinis = $('#tableMinis tbody');
                        tableMinis.append('<tr><td>' + selectedCode + '</td><td>' + selectedLibelle + '</td></tr>');
                    } else {
                        $('#alertMessage').text("Cette donnée existe déjà dans le tableau.");
                        $('#alertModal').modal('show');
                    }
                } else {
                    // Afficher un message si aucune structure n'est sélectionnée
                    $('#alertMessage').text("Veuillez sélectionner un ministère ou une collectivité territoriale.");
                    $('#alertModal').modal('show');
                }
            });
        });
    ////////////////////////////FIN MAITE D'OUVRAGE//////////////////////

    /////////////////////////RESSOURCE FINANCIERE////////////////
        // Liste temporaire pour stocker les données des actions sélectionnées
        var actionsSelectionneesTemp = [];

        // Fonction pour copier les données de l'action sélectionnée temporairement
        function copierActionSelectionnee() {
            // Récupérer l'index de la ligne sélectionnée
            var selectedIndex = $('#TableBailleurAction tbody tr').index($(this));

            // Vérifier si l'index est valide
            if (selectedIndex >= 0 && selectedIndex < actionsSelectionneesTemp.length) {
                // Retirer les données de l'action sélectionnée de la liste temporaire
                actionsSelectionneesTemp.splice(selectedIndex, 1);
            }
        }

        // Attacher la fonction à l'événement de clic sur les lignes de la table #TableBailleurAction
        $('#TableBailleurAction tbody').on('click', 'tr', copierActionSelectionnee);

        // Gestion de la sélection des checkboxes dans le tableau des actions à mener du bailleur
        $(document).on('click', '#TableBailleurAction thead input[type="checkbox"]', function () {
            // Récupérer la liste des checkboxes du corps du tableau
            var bailleurActionCheckboxes = $('#TableBailleurAction tbody input[type="checkbox"]');
            // Activer ou désactiver toutes les checkboxes en fonction de la checkbox de l'en-tête
            bailleurActionCheckboxes.prop('checked', this.checked);
            // Vérifier si toutes les checkboxes sont cochées
            var allChecked = bailleurActionCheckboxes.length === bailleurActionCheckboxes.filter(':checked').length;
            // Mettre à jour la checkbox de l'en-tête en conséquence
            $('#TableBailleurAction thead input[type="checkbox"]').prop('checked', allChecked);
        });

        // Gestion de la sélection de toutes les checkboxes dans le tableau des actions à mener du bailleur
        $(document).on('click', '#checkboxBailleurAction', function () {
            var bailleurActionCheckboxes = $('#TableBailleurAction tbody input[type="checkbox"]');
            bailleurActionCheckboxes.prop('checked', this.checked);
        });

        // Fonction pour ajouter les données du tableau #tableActionMener à la table TableBailleurAction
        function ajouterActionsBailleurAuTableau() {
            // Sélectionner le corps de la table #tableActionMener
            var tableBody = $('#tableActionMener tbody');

            // Récupérer les lignes du tableau #tableActionMener
            var rows = tableBody.find('tr');

            // Sélectionner le corps de la table #TableBailleurAction
            var newTableBody = $('#TableBailleurAction tbody');

            // Vider le contenu actuel de la table #TableBailleurAction
            newTableBody.empty();

            // Vider la liste temporaire des actions sélectionnées
            actionsSelectionneesTemp = [];

            // Ajouter chaque ligne du tableau #tableActionMener au tableau #TableBailleurAction
            rows.each(function () {
                // Récupérer les cellules de la ligne actuelle
                var cells = $(this).find('td');

                // Vérifier si le numéro d'ordre n'est pas déjà présent dans la liste temporaire
                var nordreExisteTemp = actionsSelectionneesTemp.some(function (action) {
                    return action.nordre === cells.eq(0).text();
                });

                // Si le numéro d'ordre n'existe pas dans la liste temporaire, l'ajouter
                if (!nordreExisteTemp) {
                    // Créer une nouvelle ligne avec les mêmes données
                    var newRow = $('<tr>').append(
                        $('<td>').text(cells.eq(0).text()), // Action
                        $('<td>').text(cells.eq(1).text()), // Quantité
                        $('<td>').text(cells.eq(2).text()), // Unité de mesure
                        $('<td>').text(cells.eq(3).text()), // Infrastructure
                        $('<td>').text(cells.eq(4).text()), // Infrastructure
                        $('<td>').append('<input type="checkbox">')
                    );

                    // Ajouter la nouvelle ligne au corps de la table #TableBailleurAction
                    newTableBody.append(newRow);

                    // Ajouter les données de l'action à la liste temporaire
                    actionsSelectionneesTemp.push({
                        nordre: cells.eq(0).text(),
                        action: cells.eq(1).text(),
                        quantite: cells.eq(2).text(),
                        uniteMesure: cells.eq(3).text(),
                        infrastructure: cells.eq(4).text(),
                        // Ajoutez d'autres données des bénéficiaires ici si nécessaire
                    });
                }
            });

            // Afficher le modal
            $('#myModal').css('display', 'block');
        }

        // Fonction appelée lorsqu'un checkbox est cliqué (dans votre balise <input>)
        function onCheckboxClick(id) {
            // Récupérer la checkbox cliquée
            var checkbox = document.getElementById(id);

            // Récupérer la checkbox opposée
            var checkboxOppose;
            if (id === 'oui') {
                checkboxOppose = document.getElementById('non');
            } else {
                checkboxOppose = document.getElementById('oui');
            }

            // Désactiver la checkbox opposée
            checkboxOppose.checked = false;

            // Exécuter la fonction d'origine
            onCheckboxClick.original(id);
        }

        // Fonction d'origine
        onCheckboxClick.original = function(id) {
            var ouiCheckbox = document.getElementById('oui');

            if (id === 'oui' && ouiCheckbox.checked) {
                // Si la case "Oui" est cochée, ajoutez les actions à la table
                ajouterActionsBailleurAuTableau();
            } else {
                // Si la case "Oui" est décochée, masquez le modal
                $('#myModal').css('display', 'none');
            }
        }

        // Fonction pour fermer le modal
        function fermerModal() {
            $('#myModal').css('display', 'none');
        }

        // Fonction appelée lors du clic sur le bouton "Enregistrer"
        $('#enrBailleurAction').on('click', function () {
            // Récupération des données temporairement stockées dans actionsSelectionneesTemp
            var actionsSelectionneesTemp = [];
            $('#TableBailleurAction tbody input[type="checkbox"]:checked').each(function () {
                actionsSelectionneesTemp.push({
                    nordre: $(this).closest('tr').find('td:eq(0)').text(),
                    action: $(this).closest('tr').find('td:eq(1)').text(),
                    quantite: $(this).closest('tr').find('td:eq(2)').text(),
                    uniteMesure: $(this).closest('tr').find('td:eq(3)').text(),
                    infrastructure: $(this).closest('tr').find('td:eq(4)').text()
                    // Ajoutez d'autres propriétés si nécessaire
                });
            });

            // Calcul du nombre total de lignes
            var nombreTotalDeLignes = $('#TableBailleurAction tbody tr').length;

            // Vos opérations d'enregistrement ici
            if (actionsSelectionneesTemp.length === 0) {

                $('#alertMessage').text("Vous devez sélectionner au moins une ligne.");
                $('#alertModal').modal('show');
            } else if (actionsSelectionneesTemp.length > nombreTotalDeLignes-1) {

                $('#alertMessage').text("Vous ne pouvez pas tout sélectionner. Veuillez choisir quelques-unes.");
                $('#alertModal').modal('show');
            } else {
                // Afficher les données dans la console
                $('#alertMessage').text("Les données ont été enregistrées temporairement.");
                $('#alertModal').modal('show');
            }
        });

        // Fonction pour ajouter une ligne au tableau #tableBailleur
        function ajouterLigneTableBailleur(bailleurLibelleLong, montant, deviseCodeLong, commentaire, nordre, partieChecked) {
            // Sélectionner le corps de la table #tableBailleur
            var tableBodyc = $('#tableBailleur tbody');

            // Vérifier si le N° d'ordre existe déjà dans le tableau
            var nordreExiste = tableBodyc.find('td:contains(' + nordre + ')').length > 0;

            // Si le N° d'ordre n'existe pas, ajouter une nouvelle ligne
            if (!nordreExiste) {
                    // Récupérer les valeurs des champs de saisie, des cases à cocher et des menus déroulants
                    var bailleurSelect = $('#bailleur');
                    var bailleurOption = bailleurSelect.find('option:selected');
                    var bailleurLibelleLong = bailleurOption.text();
                    var financementSelect = $('#type_financement');
                    var financementOption = financementSelect.find('option:selected');
                    var financementLibelleLong = financementOption.text();
                    var deviseSelect = $('#devise');
                    var deviseOption = deviseSelect.find('option:selected');
                    var deviseCodeLong = deviseOption.text();
                    var bailleurCode = $('#bailleur').val();
                    var financementCode = $('#type_financement').val();
                    var deviseCode = $('#devise').val();

                    // Créer une nouvelle ligne avec les données fournies
                    var newRow = $('<tr>').append(
                        $('<td>').text(bailleurLibelleLong),
                        $('<td>').text(montant),
                        $('<td>').text(deviseCodeLong),
                        $('<td>').text(commentaire),
                        $('<td>').text(financementLibelleLong),
                        $('<td>').text(partieChecked),
                        $('<td>').text(nordre),
                        $('<td>').html('<input type="checkbox" name="actionBailleurCheck" class="actionBailleurCheck">'),
                        $('<td hidden>').text(bailleurCode),
                        $('<td hidden>').text(deviseCode),
                        $('<td hidden>').text(financementCode)
                        // Ajoutez d'autres colonnes si nécessaire
                    );

                    // Ajouter la nouvelle ligne au corps de la table #tableBailleur
                    tableBodyc.append(newRow);
                } else {
                    // Si le N° d'ordre existe déjà, afficher un message ou prendre une autre action
                    console.log('Le N° d\'ordre ' + nordre + ' existe déjà dans le tableau.');
                }

        }

        // Fonction pour ajouter un bailleur
        function addBailleurField() {
            // Récupérer les valeurs des champs de saisie, des cases à cocher et des menus déroulants
            var bailleur = $('#bailleur').val();
            var montant = $('#montant').val();
            var devise = $('#devise').val();
            var commentaire = $('#commentBailleur').val();

            // Récupérer les lignes sélectionnées dans le tableau des actions du bailleur
            var selectedRows = $('#TableBailleurAction tbody input[type="checkbox"]:checked').closest('tr');

            // Ajouter une ligne au tableau tableBailleur pour chaque ligne sélectionnée
            selectedRows.each(function () {
                var nordre = $(this).find('td:eq(0)').text();
                var partieChecked = $('#oui').is(':checked') ? 'Oui' : ($('#non').is(':checked') ? 'Non' : '');

                // Ajouter une ligne au tableau tableBailleur avec les valeurs récupérées
                ajouterLigneTableBailleur(bailleur, montant, devise, commentaire, nordre, partieChecked);
            });

            // Réinitialiser les champs après l'ajout
            $('#bailleur, #montant, #devise, #commentBailleur, #public, #oui, #non').val('');
        }

        // Associer la fonction d'ajout de bailleur au bouton correspondant (ajustez l'ID du bouton en conséquence)
        $('#addBailleur').on('click', function () {
            addBailleurField();
        });
    /////////////////////////FIN RESSOURCE FINANCIERE////////////////

    //////////////////////REGIE FINANCIERE ET MAITRE DOEUVRE/////////////////////////////////////
        function addRegieMaitre() {
            // Récupérer les données de l'option sélectionnée
            var codeAgence = $('#inputState').val();
            var selectedAgence = $("#inputState option:selected");
            var nomAgence = selectedAgence.data("nom");
            var telephone = selectedAgence.data("telephone");
            var email = selectedAgence.data("email");
            var adresse = selectedAgence.data("adresse");
            var typeAgence = $("input[name='customRadioInline1']:checked").val(); // Récupérer la valeur du radio bouton
            var codeAgenceNiveau ;
            // Remplacez les valeurs "regie" et "maitreOeuvre" par les libellés correspondants
            if (typeAgence === 'regie') {
                codeAgenceNiveau=1;
                typeAgence = 'Régie financière';
            } else if (typeAgence === 'maitreOeuvre') {
                codeAgenceNiveau=2;
                typeAgence = 'Maître d\'œuvre';
            }

            // Vérifier si les données existent déjà dans le tableau #regieMaitreTable
            var existeDeja = false;
            $('#regieMaitreTable tbody tr').each(function() {
                var existingNom = $(this).find('td:eq(0)').text();
                var existingTypeAgence = $(this).find('td:eq(1)').text();
                var existingTelephone = $(this).find('td:eq(2)').text();
                var existingEmail = $(this).find('td:eq(3)').text();
                var existingAdresse = $(this).find('td:eq(4)').text();
                var existincodeAgenceNiveau = $(this).find('td:eq(5)').text();
                var existincodeAgence = $(this).find('td:eq(6)').text();

                if (
                    existingNom === nomAgence &&
                    existingTypeAgence === typeAgence &&
                    existingTelephone === telephone &&
                    existingEmail === email &&
                    existingAdresse === adresse &&
                    existincodeAgenceNiveau === codeAgenceNiveau &&
                    existincodeAgence === codeAgence
                ) {
                    existeDeja = true;
                    return false; // Sortir de la boucle each
                }
            });

            if (!existeDeja) {
                // Ajouter les données au tableau #regieMaitreTable
                var regieMaitreTable = $('#regieMaitreTable tbody');
                var newRow = `<tr>
                    <td>${nomAgence}</td>
                    <td>${typeAgence}</td>
                    <td>${telephone}</td>
                    <td>${email}</td>
                    <td>${adresse}</td>
                    <td hidden>${codeAgenceNiveau}</td>
                    <td hidden>${codeAgence}</td>
                </tr>`;
                regieMaitreTable.append(newRow);
            } else {

                $('#alertMessage').text("Ces données existent déjà dans le tableau.");
                $('#alertModal').modal('show');
            }
        }
    /////////////////////FIN REGIE FINANCIERE ET MAITRE DOEUVRE/////////////////////////////////////

    //////////////////////CHEF DE PROJET//////////////////////
        function Affiche(type) {
            if (type === 'ministere') {
                $('#ministereA').show();
                $('#agenceA').hide();
            } else if (type === 'agence') {
                $('#agenceA').show();
                $('#ministereA').hide();
            }
        }
        // Déplacez la déclaration de la fonction addChefProjet à l'extérieur de $(document).ready()
        function addChefProjet() {
            // Vérifier si une ligne a déjà été ajoutée
            if ($("#chefProjetTable tbody tr").length > 0) {
                $('#alertMessage').text("Vous ne pouvez sélectionner qu'un seul chef de projet.");
                $('#alertModal').modal('show');
                return; // Arrêter la fonction si une ligne existe déjà
            }

            // Récupérez les données de la personne sélectionnée
            var selectedPerson = $("#inputProjet option:selected");
            var nom = selectedPerson.data("nom");
            var prenom = selectedPerson.data("prenom");
            var structureAccueil = selectedPerson.data("structure-accueil");
            var adresse = selectedPerson.data("adresse");
            var telephone = selectedPerson.data("telephone");
            var email = selectedPerson.data("email");
            var fonction = selectedPerson.data("fonction");
            var codePersonnel = $('#inputProjet').val();

            // Récupérez les données du ministère ou de la collectivité sélectionné
            var selectedMinistere = $("#ministereA option:selected");
            var selectedAgence = $("#agenceA option:selected");

            // Vérifiez si l'option ministère est sélectionnée
            var ministere = selectedMinistere.length > 0 ? selectedMinistere.data("fonction") : "";

            // Vérifiez si l'option agence est sélectionnée
            var agence = selectedAgence.length > 0 ? selectedAgence.data("fonction") : "";

            // Utilisez une condition pour déterminer quelle fonction affichee
            var fonctionAffichee =   ministere || agence;

            // Créez une nouvelle ligne avec les données récupérées
            var newRow = `<tr>
                <td>${nom}</td>
                <td>${prenom}</td>
                <td>${adresse}</td>
                <td>${telephone}</td>
                <td>${email}</td>
                <td>${fonction}</td>
                <td>${fonctionAffichee}</td>
                <td hidden>${codePersonnel}</td>
                <td><a type="button" class="btn btn-danger delete-row">Supprimer</a></td>
            </tr>`;

            // Ajoutez la nouvelle ligne au tableau
            $("#chefProjetTable tbody").append(newRow);
        }
        // Ajoutez ce script à votre fichier JavaScript
        $(document).ready(function() {
            // Appeler la fonction Affiche au chargement de la page pour gérer l'état initial
            Affiche($('input[name="structure"]:checked').val());
            // Ajoutez ce script à votre fichier JavaScript
            $(document).on('click', '.delete-row', function() {
                // Obtenez la ligne parente du bouton sur lequel l'utilisateur a cliqué
                var row = $(this).closest('tr');

                // Supprimez la ligne
                row.remove();
            });
        });
    //////////////////////FIN CHEF DE PROJET//////////////////////

    //////////////////////ENREFISTREMENT DANS LA BASE DE DONNEES////////////////
        /*$('#enregistrerEHA').on('click', function() {
            // Afficher toutes les données dans la console
            console.log('Code Projet:', $('#code_projet').val());
            console.log('Statut Projet:', $('#codeStatutInput').val());
            console.log('District Projet:', $('#district').val());
            console.log('Region Projet:', $('#region').val());
            console.log('Domaine Projet:', $('#domaine').val());
            console.log('Sous-Domaine Projet:', $('#sous_domaine').val());
            console.log('Date Début Projet:', $('#date_debut').val());
            console.log('Date Fin Projet:', $('#date_fin').val());
            console.log('Cout Projet:', $('#cout-display').val());
            console.log('Devise Projet:', $('#deviseProject').val());

            console.log('Actions à Mener:////');
            $('#tableActionMener tbody tr').each(function() {
                console.log('Numéro d\'Ordre:', $(this).find('td:eq(0)').text());
                console.log('Action à Mener:', $(this).find('td:eq(8)').text());
                console.log('Quantité:', $(this).find('td:eq(2)').text());
                console.log('Unité de Mesure:', $(this).find('td:eq(9)').text());
                console.log('Infrastructure:', $(this).find('td:eq(10)').text());
                console.log('Bénéficiaire Code:', $(this).find('td:eq(6)').text());
                console.log('Bénéficiaire Type:', $(this).find('td:eq(7)').text());
            });

            console.log('Ministères://///');
            $('#tableMinis tbody tr').each(function() {
                console.log('Code Ministère:', $(this).find('td:eq(0)').text());
            });

            console.log('Bailleurs://////');
            $('#tableBailleur tbody tr').each(function() {
                console.log('Code Bailleurs:', $(this).find('td:eq(8)').text());
                console.log('Montant:', $(this).find('td:eq(1)').text());
                console.log('Devise:', $(this).find('td:eq(9)').text());
                console.log('Commentaire:', $(this).find('td:eq(3)').text());
                console.log('Financement Type:', $(this).find('td:eq(10)').text());
                console.log('Partie:', $(this).find('td:eq(5)').text());
                console.log('Numéro d\'Ordre:', $(this).find('td:eq(6)').text());
            });

            console.log('Agences://///');
            $('#regieMaitreTable tbody tr').each(function() {
                console.log('Code Agence:', $(this).find('td:eq(6)').text());
                console.log('Niveau:', $(this).find('td:eq(5)').text());
            });

            console.log('Chefs de Projet:///////');
            $('#chefProjetTable tbody tr').each(function() {
                console.log('Code Chef de Projet:', $(this).find('td:eq(7)').text());
            });


        });*/

        $('#enregistrerEHA').on('click', function() {
            // Envoi de la requête AJAX
            $.ajax({
                url: '{{ route("enregistrer.formulaire") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    code_projet:$('#code_projet').val(),
                    code_statut:$('#codeStatutInput').val(),
                    district:$('#district').val(),
                    region:$('#region').val(),
                    domaine:$('#domaine').val(),
                    sous_domaine:$('#sous_domaine').val(),
                    date_debut:$('#date_debut').val(),
                    date_fin:$('#date_fin').val(),
                    cout: $('#cout-display').val().replace(/\s/g, ''),
                    deviseProject:$('#deviseProject').val(),

                    nordre: $('#tableActionMener tbody tr').map(function() {
                        return $(this).find('td:eq(0)').text();
                    }).get(),
                    actionMener: $('#tableActionMener tbody tr').map(function() {
                        return $(this).find('td:eq(8)').text();
                    }).get(),
                    quantite: $('#tableActionMener tbody tr').map(function() {
                        return $(this).find('td:eq(2)').text();
                    }).get(),
                    uniteMesure: $('#tableActionMener tbody tr').map(function() {
                        return $(this).find('td:eq(9)').text();
                    }).get(),
                    infrastructure: $('#tableActionMener tbody tr').map(function() {
                        return $(this).find('td:eq(10)').text();
                    }).get(),
                    beneficiaire_code: $('#tableActionMener tbody tr').map(function() {
                        return $(this).find('td:eq(6)').text();
                    }).get(),
                    beneficiaire_type: $('#tableActionMener tbody tr').map(function() {
                        return $(this).find('td:eq(7)').text();
                    }).get(),


                    ministere_code: $('#tableMinis tbody tr').map(function() {
                        return $(this).find('td:eq(0)').text();
                    }).get(),


                    bailleur_code: $('#tableBailleur tbody tr').map(function() {
                        return $(this).find('td:eq(8)').text();
                    }).get(),
                    montant_bailleur: $('#tableBailleur tbody tr').map(function() {
                        return $(this).find('td:eq(1)').text().replace(/\s/g, '');
                    }).get(),
                    bailleur_devise: $('#tableBailleur tbody tr').map(function() {
                        return $(this).find('td:eq(9)').text();
                    }).get(),
                    bailleur_commentaire: $('#tableBailleur tbody tr').map(function() {
                        return $(this).find('td:eq(3)').text();
                    }).get(),
                    bailleur_financement: $('#tableBailleur tbody tr').map(function() {
                        return $(this).find('td:eq(10)').text();
                    }).get(),
                    bailleur_partie: $('#tableBailleur tbody tr').map(function() {
                        var partieValue = $(this).find('td:eq(5)').text().trim();
                        return partieValue === 'Oui' ? 1 : 0;
                    }).get(),

                    bailleur_nordre: $('#tableBailleur tbody tr').map(function() {
                        return $(this).find('td:eq(6)').text();
                    }).get(),

                    inputState: $('#regieMaitreTable tbody tr').map(function() {
                        return $(this).find('td:eq(6)').text();
                    }).get(),
                    niveau: $('#regieMaitreTable tbody tr').map(function() {
                        return $(this).find('td:eq(5)').text();
                    }).get(),

                    chefProjet_code: $('#chefProjetTable tbody tr').map(function() {
                        return $(this).find('td:eq(7)').text();
                    }).get(),
                },
                success: function(response) {
                    if (response.success) {
                        $('#alertMessage').text(response.message);
                        $('#alertModal').modal('show');
                    } else if (response.error) {
                        $('#alertMessage').text(response.message);
                        $('#alertModal').modal('show');
                        return false;
                    }
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        // Afficher les erreurs de validation
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = "Erreur de validation:\n";
                        for (var key in errors) {
                            errorMessage += "- " + errors[key][0] + "\n";
                        }
                        $('#alertMessage').text(errorMessage);
                        $('#alertModal').modal('show');
                    } else {
                        console.error('Erreur lors de l\'enregistrement : ' + error);
                    }
                }

            });
        });


</script>
@endsection
