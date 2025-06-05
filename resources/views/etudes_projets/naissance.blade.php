@extends('layouts.app')
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .form-group label {
            font-weight: bold;
        }
        .upload-box {
            border: 2px dashed #007bff;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            background: #f8f9fa;
        }
        #fixedPositionResults {
            position: absolute;
            width: 100%;
            max-height: 181px; /* Définit une hauteur maximale pour éviter un trop grand affichage */
            overflow-y: auto; /* Permet le défilement si trop d'éléments */
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            z-index: 2050 !important; /* Plus grand que la plupart des modals */
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        #fixedPositionResults li {
            padding: 10px;
            cursor: pointer;
        }

        #fixedPositionResults li:hover {
            background: #f1f1f1;
        }
        /* Ajuster la taille du modal */
        .modal-dialog {
            max-width: 80%;
            width: auto;
            min-width: 600px;
        }

        /* Permettre le défilement si le contenu est long */
        .modal-content {
            overflow-y: auto;
            max-height: 90vh;
        }

        /* Style de la table des bénéficiaires */
        .table-container {
            max-height: 300px;
            overflow-y: auto;
        }

        #beneficiaireTable {
            width: 100%;
            border-collapse: collapse;
        }

        #beneficiaireTable th, #beneficiaireTable td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        #beneficiaireTable th {
            background-color: #f2f2f2;
        }

        .upload-box:hover {
            background: #e2e6ea;
        }
        .uploaded-files {
            margin-top: 10px;
        }
        .step {
            display: none;
        }
        #documentModal .modal-body ul li {
           color: black;
        }
        .step.active {
            display: block;
        }
        .progress {
            height: 5px;
        }
            .upload-dropzone {
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            background-color: #f8f9fa;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .upload-dropzone:hover {
            background-color: #e9f5ff;
            border-color: #0056b3;
        }
        
        .upload-dropzone i {
            font-size: 48px;
            color: #007bff;
            margin-bottom: 15px;
        }
        
        .uploaded-files-list {
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        
        .list-header {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: bold;
        }
        
        .files-container {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .file-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .file-item:last-child {
            border-bottom: none;
        }
        
        .file-icon {
            margin-right: 10px;
            color: #6c757d;
        }
        
        .file-info {
            flex-grow: 1;
        }
        
        .file-name {
            font-weight: 500;
        }
        
        .file-size {
            font-size: 0.8em;
            color: #6c757d;
        }
        
        .file-remove {
            color: #dc3545;
            cursor: pointer;
        }
        
        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
    </style>
@section('content')

@if (session('success'))
<script>
    $('#alertMessage').text("{{ session('success') }}");
    $('#alertModal').modal('show');
</script>
@endif
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Etudes projets</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Naissance / modelisation</li>
                        </ol>
                        <div class="row">
                            <script>
                                setInterval(function() {
                                    document.getElementById('date-now').textContent = getCurrentDate();
                                }, 1000);

                                function getCurrentDate() {
                                    var currentDate = new Date();
                                    return currentDate.toLocaleString();
                                }
                            </script>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Naissance / Modélisation de Projet</h5>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>0
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>
            <div class="card-content">
                <div class="col-12">
                    <div class="container mt-5">
                        <h2 class="text-center mb-4 text-primary">📌 Demande de Projet - BTP-PROJECT</h2>

                        <!-- Barre de progression -->
                        <div class="progress mb-4">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 20%;" id="progressBar"></div>
                        </div>

                        <form class="col-12" id="projectForm">
                            <!-- Étape : Informations sur le Maître d’Œuvre -->
                            <div class="step " id="step-4">
                                <h5 class="text-secondary">👷 Informations / Maître d’ouvrage</h5>

                                <div class="row">
                                    <label>Type de Maître d’ouvrage *</label>
                                    <div class="col">
                                        <div class="form-check">
                                            <input type="checkbox" id="moePublic" class="form-check-input" name="type_ouvrage" value="Public" onchange="toggleTypeMoe()">
                                            <label class="form-check-label" for="moePublic">Public</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="moePrive" class="form-check-input" name="type_ouvrage" value="Privé" onchange="toggleTypeMoe()">
                                            <label class="form-check-label" for="moePrive">Privé</label>
                                        </div>

                                    </div>
                                    <!-- Options spécifiques pour le type privé -->
                                    <div class="col mt-3 d-none" id="optionsMoePrive">
                                        <label>Type de Privé *</label>
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="priveMoeType" id="moeEntreprise" value="Entreprise" onchange="toggleMoeFields()">
                                                <label class="form-check-label" for="moeEntreprise">Entreprise</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="priveMoeType" id="moeIndividu" value="Individu" onchange="toggleMoeFields()">
                                                <label class="form-check-label" for="moeIndividu">Individu</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col position-relative">
                                        <label>Nom acteur *</label>
                                        <select class="form-control required" name="acteurMoeSelect" id="acteurMoeSelect">
                                            <option value="">Sélectionnez un acteur</option>
                                        </select>
                                        <small class="text-muted">Sélectionnez l’entité qui assure le rôle de Maître d’œuvre.</small>
                                    </div>
                                    <div class="col">
                                        <label>De :</label>
                                        <select name="sectActivEntMoe" id="sectActivEntMoe" class="form-control" >
                                            <option value="">Sélectionnez...</option>
                                            @foreach ($SecteurActivites as $SecteurActivite)
                                                <option value="{{ $SecteurActivite->code }}">{{ $SecteurActivite->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                                <!--
                                <div class="row">

                                    --><!-- MOE Entreprise Fields -->
                                    <!--     <div class="row mt-3 d-none" id="moeEntrepriseFields">
                                            <hr>
                                            <h6>Détails pour l’Entreprise</h6>
                                            <div class="col-12">
                                                <ul class="nav nav-tabs" id="moeentrepriseTabs" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active" id="moeentreprise-general-tab" data-bs-toggle="tab" data-bs-target="#moeentreprise-general" type="button" role="tab" aria-controls="moeentreprise-general" aria-selected="true">Informations Générales</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="moeentreprise-legal-tab" data-bs-toggle="tab" data-bs-target="#moeentreprise-legal" type="button" role="tab" aria-controls="moeentreprise-legal" aria-selected="false">Informations Juridiques</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="moeentreprise-contact-tab" data-bs-toggle="tab" data-bs-target="#moeentreprise-contact" type="button" role="tab" aria-controls="moeentreprise-contact" aria-selected="false">Informations de Contact</button>
                                                    </li>
                                                </ul>
                                                <div class="tab-content mt-3" id="moeentrepriseTabsContent">-->
                                                    <!-- Tab 1: Informations Générales -->
                                                   <!--  <div class="tab-pane fade show active" id="moeentreprise-general" role="tabpanel" aria-labelledby="moeentreprise-general-tab">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label>Nom complet (Raison sociale) * :</label>
                                                                <input type="text" name="codeEntMoe" class="form-control" placeholder="Nom de l'entreprise">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Nom abrégé :</label>
                                                                <input type="text" name="nomEntMoe" class="form-control" placeholder="Nom de l'entreprise">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Date de création :</label>
                                                                <input type="text" name="dateCreationEntMoe" class="form-control" placeholder="Adresse complète">
                                                            </div>
                                                            <div class="col-md-4 ">
                                                                <label>Forme Juridique :</label>
                                                                <select name="FormeJuriEntMoe" id="FormeJuridique" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($formeJuridiques as $formeJuridique)
                                                                        <option value="{{ $formeJuridique->id }}">{{ $formeJuridique->forme }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    -->
                                                    <!-- Tab 2: Informations Juridiques -->
                                                    <!--     <div class="tab-pane fade" id="moeentreprise-legal" role="tabpanel" aria-labelledby="moeentreprise-legal-tab">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label>Numéro d’Immatriculation :</label>
                                                                <input type="text" name="NumImmEntMoe" class="form-control" placeholder="Numéro RCCM">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Numéro d’Identification Fiscale (NIF) :</label>
                                                                <input type="text" name="NIFEntMoe" class="form-control" placeholder="Numéro fiscal">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Registre du commerce (RCCM) :</label>
                                                                <input type="text" class="form-control" name="RCCMEntMoe" placeholder="Numéro fiscal">
                                                            </div>
                                                            <div class="col-md-6 mt-2">
                                                                <label>Capital Social :</label>
                                                                <input type="number" name="CapitalEntMoe" class="form-control" placeholder="Capital social de l’entreprise">
                                                            </div>
                                                            <div class="col-md-6 mt-2">
                                                                <label>Numéro d'agrément :</label>
                                                                <input type="text"  name="NumAgreEntMoe" id="Numéroagrement" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    --> <!-- Tab 3: Informations de Contact -->
                                                     <!--<div class="tab-pane fade" id="moeentreprise-contact" role="tabpanel" aria-labelledby="moeentreprise-contact-tab">
                                                        <div class="row">
                                                            <div class="col-4">
                                                                <label>Code postale</label>
                                                                <input type="text" name="CodePostEntMoe" class="form-control"  placeholder="Code postale">
                                                            </div>
                                                            <div class="col-4">
                                                                <label>Adresse postale</label>
                                                                <input type="text"  class="form-control" name="AddPostEntMoe" placeholder="Code postale">
                                                            </div>
                                                            <div class="col-4">
                                                                <label>Adresse Siège</label>
                                                                <input type="text" class="form-control" name="AddSieEntMoe" placeholder="Code postale">
                                                            </div>
                                                            <hr>
                                                            <div class="col-md-3">
                                                                <label>Représentant Légal :</label>
                                                                <lookup-select name="RepLeEntMoe" id="RepLeEntMoe">
                                                                    @foreach ($acteurRepres as $acteurRepre)
                                                                        <option value="{{ $acteurRepre->code_acteur }}">{{ $acteurRepre->libelle_court }} {{ $acteurRepre->libelle_long }}</option>
                                                                    @endforeach
                                                                </lookup-select>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Email:</label>
                                                                <input type="email" class="form-control" name="EmailRepLeEntMoe" placeholder="Email du représentant légal">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 1:</label>
                                                                <input type="text" class="form-control" name="Tel1RepLeEntMoe" placeholder="Téléphone 1 du représentant légal">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 2:</label>
                                                                <input type="text" class="form-control" name="Tel2RepLeEntMoe" placeholder="Téléphone 2 du représentant légal">
                                                            </div>
                                                            <hr>
                                                            
                                                            <hr>
                                                        </div>
                                                        <div class="row align-items-end">-->
                                                            <!-- Lookup-Multiselect -->
                                                           <!--  <div class="col-md-3">
                                                                <label>Personne de Contact</label>
                                                                <lookup-multiselect name="nomPCMoe" id="nomPCMoe">
                                                                    @foreach ($acteurRepres as $acteurRepre)
                                                                        <option value="{{ $acteurRepre->code_acteur }}">{{ $acteurRepre->libelle_court }} {{ $acteurRepre->libelle_long }}</option>
                                                                    @endforeach
                                                                </lookup-multiselect>
                                                            </div>-->

                                                            <!-- Conteneur pour afficher dynamiquement les champs -->
                                                             <!--<div class="col-md-9 d-flex flex-wrap" id="contactContainerMoe"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>-->

                                        <!-- MOE Individu Fields -->
                                         <!--<div class="row mt-3 d-none" id="moeIndividuFields">
                                            <hr>
                                            <h6>Détails pour l’Individu</h6>
                                            <div class="col-12">
                                                <ul class="nav nav-tabs" id="moeindividuTabs" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active" id="moeindividu-general-tab" data-bs-toggle="tab" data-bs-target="#moeindividu-general" type="button" role="tab" aria-controls="moeindividu-general" aria-selected="true">Informations Personnelles</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="moeindividu-contact-tab" data-bs-toggle="tab" data-bs-target="#moeindividu-contact" type="button" role="tab" aria-controls="moeindividu-contact" aria-selected="false">Informations de Contact</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="moeindividu-admin-tab" data-bs-toggle="tab" data-bs-target="#moeindividu-admin" type="button" role="tab" aria-controls="moeindividu-admin" aria-selected="false">Informations Administratives</button>
                                                    </li>
                                                </ul>
                                                <div class="tab-content mt-3" id="moeindividuTabsContent">-->
                                                    <!-- Tab 1: Informations Personnelles -->
                                                     <!--<div class="tab-pane fade show active" id="moeindividu-general" role="tabpanel" aria-labelledby="moeindividu-general-tab">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label>Nom *:</label>
                                                                <input type="text" name="NomIndMoe" class="form-control" placeholder="Nom">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Prénom *:</label>
                                                                <input type="text" name="PrenomIndMoe" class="form-control" placeholder="Prénom">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Date de Naissance :</label>
                                                                <input type="date" name="DateNaissanceIndMoe" class="form-control">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Genre</label>
                                                                <select name="genreIndMoe" id="genre" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($genres as $genre)
                                                                    <option value="{{ $genre->code_genre }}">{{ $genre->libelle_genre }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4 ">
                                                                <label>Situation Matrimoniale :</label>
                                                                <select class="form-control" name="SitMatIndMoe">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($SituationMatrimoniales as $SituationMatrimoniale)
                                                                        <option value="{{ $SituationMatrimoniale->id }}">{{ $SituationMatrimoniale->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Pays d'origine :</label>
                                                                <select name="nationnaliteIndMoe" id="nationnalite" class="form-control">
                                                                    <option value="">Sélectionner le pays </option>
                                                                    @foreach ($tousPays  as $pay)
                                                                        <option value="{{ $pay->id }}">{{ $pay->nom_fr_fr }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>-->

                                                    <!-- Tab 2: Informations de Contact -->
                                                     <!--<div class="tab-pane fade" id="moeindividu-contact" role="tabpanel" aria-labelledby="moeindividu-contact-tab">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label>Email *:</label>
                                                                <input type="email" name="emailIndMoe" class="form-control" placeholder="Email">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label for="codePostal">Code postal</label>
                                                                <input type="text" name="CodePostalIndMoe" id="CodePostal" class="form-control">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Adresse postale:</label>
                                                                <input type="text" name="AddPostIndMoe" class="form-control" placeholder="Adresse">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Adresse *</label>
                                                                <input type="text" name="AddSiegeIndMoe" class="form-control" placeholder="Adresse">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Téléphone Bureau *</label>
                                                                <input type="text" name="TelBureauIndMoe" class="form-control" placeholder="Téléphone">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Téléphone mobile *</label>
                                                                <input type="text" name="TelMobileIndMoe" class="form-control" placeholder="Téléphone">
                                                            </div>
                                                        </div>
                                                    </div>-->

                                                    <!-- Tab 3: Informations Administratives -->
                                                     <!--<div class="tab-pane fade" id="moeindividu-admin" role="tabpanel" aria-labelledby="moeindividu-admin-tab">
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <label>Pièce d’Identité :</label>
                                                                <select class="form-control" name="PieceIdIndMoe">
                                                                    @foreach($Pieceidentite as $Pieceidentit)
                                                                    <option value="{{ $Pieceidentit->idPieceIdent }}">{{ $Pieceidentit->libelle_long }}</option>
								                                    @endforeach

                                                                </select>

                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Numéro Pièce:</label>
                                                                <input type="text" name="NumPeceIndMoe" class="form-control" placeholder="Numéro de CNI">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Date de etablissement:</label>
                                                                <input type="date" name="DateEtablissementIndMoe" class="form-control" placeholder="Numéro de CNI">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Date de expiration:</label>
                                                                <input type="date" name="DateExpIndMoe" class="form-control" placeholder="Numéro de CNI">
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label>Numéro Fiscal :</label>
                                                                <input type="text" name="NumFiscIndMoe" class="form-control" placeholder="Numéro fiscal">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Secteur d'activité :</label>
                                                                <select name="sectActIndMoe" id="SecteurActiviteEntreprise" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($formeJuridiques as $formeJuridique)
                                                                        <option value="{{ $formeJuridique->id }}">{{ $formeJuridique->forme }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>    
                                -->          


                                <hr>
                                <div class="row mt-3">
                                    <div class="col-10">
                                        <label>Description / Observations</label>
                                        <textarea class="form-control" id="descriptionMoe" rows="3" placeholder="Ajoutez des précisions sur le Maître d’œuvre"></textarea>
                                    </div>
                                    <div class="col-2  mt-4">
                                        <button type="button" class="btn btn-secondary" id="addMoeBtn" style="heght: 34px">
                                            <i class="fas fa-plus"></i> Ajouter 
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <table class="table table-bordered" id="moeTable">
                                        <thead>
                                            <tr>
                                                <th>Nom / Libellé court</th>
                                                <th>Prénom / Libellé long</th>
                                                <th>Secteur</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Ligne ajoutée dynamiquement -->
                                        </tbody>
                                    </table>
                                </div>
                                <br>
                                <div class="row">

                                    <div class="col">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary " onclick="saveStep4(nextStep)">Suivant <i class="fas fa-arrow-right"></i> </button>
                                    </div>
                                </div>

                            </div>
                            <!--Sauvegarde temporaire -->
                            <script>
                                function saveStep4(callback = null) {
                                    const codeProjet = localStorage.getItem('code_projet_temp');
                                    if (!codeProjet) return alert("Projet non trouvé.");

                                    const codeActeur = $('input[name="code_acteur_moe"]').val();
                                    if (!codeActeur) return alert("Veuillez ajouter un maître d’ouvrage.");

                                    const typeOuvrage = $('input[name="type_ouvrage"]:checked').val();
                                    const priveMoeType = $('input[name="priveMoeType"]:checked').val();
                                    const secteur = $('#sectActivEntMoe').val();
                                    const description = $('#descriptionMoe').val();

                                    $.ajax({
                                        url: '{{ route("projets.temp.save.step4") }}',
                                        method: 'POST',
                                        data: {
                                            _token: '{{ csrf_token() }}',
                                            code_projet: codeProjet,
                                            code_acteur_moe: codeActeur,
                                            type_ouvrage: typeOuvrage,
                                            priveMoeType: priveMoeType,
                                            sectActivEntMoe: secteur,
                                            descriptionMoe: description
                                        },
                                        success: function(response) {
                                            //alert(response.message || "Étape 4 sauvegardée.");
                                            nextStep();
                                            //if (typeof callback === "function") callback();
                                        },
                                        error: function (xhr) {
                                            let message = "Une erreur est survenue.";

                                            try {
                                                const response = JSON.parse(xhr.responseText);
                                                if (response.message) {
                                                    message = response.message;
                                                }
                                            } catch (e) {
                                                console.error("Erreur parsing JSON :", e);
                                                console.warn("Réponse brute :", xhr.responseText);
                                            }

                                            alert(message);
                                            console.error("Détail complet :", xhr.responseText);
                                        }
                                    });
                                }
                            </script>

                          <!-- Étape  : Informations sur le Maître d’Ouvrage -->
                            <div class="step" id="step-5">
                                <h5 class="text-secondary">🏗️ Informations / Maître d'œuvre</h5>

                                <!-- ✅ Sélection du Type -->
                                <div class="row">
                                    <label>Type de Maître d'œuvre  *</label>
                                    <div class="col">
                                        <div class="form-check">
                                            <input type="checkbox" id="public" class="form-check-input" name="type_mo" value="Public" onchange="toggleType()">
                                            <label class="form-check-label" for="public">Public</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="prive" class="form-check-input" name="type_mo" value="Privé" onchange="toggleType()">
                                            <label class="form-check-label" for="prive">Privé</label>
                                        </div>
                                        <small class="text-muted">Le maître d'œuvre peut être public (État), privé (Entreprise).</small>
                                    </div>
                                    <!-- ✅ Options spécifiques pour le type privé -->
                                    <div class="col mt-3 d-none" id="optionsPrive">
                                        <label>Type de Privé *</label>
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="priveType" id="entreprise" value="Entreprise" onchange="togglePriveFields()">
                                                <label class="form-check-label" for="entreprise">Entreprise</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="priveType" id="individu" value="Individu" onchange="togglePriveFields()">
                                                <label class="form-check-label" for="individu">Individu</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <!-- ✅ Sélection de l’Acteur -->
                                        <label>Nom Acteur *</label>
                                        <select class="form-control required" name="acteurSelect" id="acteurSelect">
                                            <option value="">Sélectionnez un acteur</option>

                                        </select>
                                        <small class="text-muted">Sélectionnez l’entité qui assure le rôle de Maître d'œuvre.</small>
                                    </div>
                                    <div class="col">
                                        <label>De :</label>
                                        <select name="sectActivEnt" id="sectActivEnt" class="form-control" >
                                            <option value="">Sélectionnez...</option>
                                            @foreach ($SecteurActivites as $SecteurActivite)
                                                <option value="{{ $SecteurActivite->code }}">{{ $SecteurActivite->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{--<div class="row">

                                    <!-- MOE Entreprise Fields -->
                                        <div class="row mt-3 d-none" id="entrepriseFields">
                                            <hr>
                                            <h6>Détails pour l’Entreprise</h6>
                                            <div class="col-12">
                                                <ul class="nav nav-tabs" id="entrepriseTabs" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active" id="entreprise-general-tab" data-bs-toggle="tab" data-bs-target="#entreprise-general" type="button" role="tab" aria-controls="entreprise-general" aria-selected="true">Informations Générales</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="entreprise-legal-tab" data-bs-toggle="tab" data-bs-target="#entreprise-legal" type="button" role="tab" aria-controls="entreprise-legal" aria-selected="false">Informations Juridiques</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="entreprise-contact-tab" data-bs-toggle="tab" data-bs-target="#entreprise-contact" type="button" role="tab" aria-controls="entreprise-contact" aria-selected="false">Informations de Contact</button>
                                                    </li>
                                                </ul>
                                                <div class="tab-content mt-3" id="entrepriseTabsContent">
                                                    <!-- Tab 1: Informations Générales -->
                                                    <div class="tab-pane fade show active" id="entreprise-general" role="tabpanel" aria-labelledby="entreprise-general-tab">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label>Raison social * </label>
                                                                <input type="text" name="raisonSocialeEnt" class="form-control" placeholder="Nom de l'entreprise">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Date de création * </label>
                                                                <input type="text" name="DateCreatEnt" class="form-control" placeholder="Adresse complète">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Secteur d'activité * </label>
                                                                <select name="SectActEnt" id="SecteurActiviteEntreprise" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($SecteurActivites as $SecteurActivite)
                                                                        <option value="{{ $SecteurActivite->code }}">{{ $SecteurActivite->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 ">
                                                                <label>Forme Juridique *</label>
                                                                <select name="FormeJurEnt" id="FormeJuridique" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($formeJuridiques as $formeJuridique)
                                                                        <option value="{{ $formeJuridique->id }}">{{ $formeJuridique->forme }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 2: Informations Juridiques -->
                                                    <div class="tab-pane fade" id="entreprise-legal" role="tabpanel" aria-labelledby="entreprise-legal-tab">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label>Numéro d’Immatriculation *:</label>
                                                                <input type="text" name="NumImmEnt" class="form-control" placeholder="Numéro RCCM">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Numéro d’Identification Fiscale (NIF) :</label>
                                                                <input type="text" name="NumIdentEnt" class="form-control" placeholder="Numéro fiscal">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Registre du commerce (RCCM) :</label>
                                                                <input type="text" name="RCCMEnt" class="form-control" placeholder="Numéro fiscal">
                                                            </div>
                                                            <div class="col-md-6 mt-2">
                                                                <label>Capital Social :</label>
                                                                <input type="number" name="CapitalEnt" class="form-control" placeholder="Capital social de l’entreprise">
                                                            </div>
                                                            <div class="col-md-6 mt-2">
                                                                <label>Numéro d'agrément :</label>
                                                                <input type="text" name="NumAgreEnt" id="Numéroagrement" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 3: Informations de Contact -->
                                                    <div class="tab-pane fade" id="entreprise-contact" role="tabpanel" aria-labelledby="entreprise-contact-tab">
                                                        <div class="row">
                                                            <div class="col-4">
                                                                <label>Code postale</label>
                                                                <input type="text" class="form-control" name="CodePostEnt" placeholder="Code postale">
                                                            </div>
                                                            <div class="col-4">
                                                                <label>Adresse postale</label>
                                                                <input type="text" class="form-control" name="addPostEnt" placeholder="Code postale">
                                                            </div>
                                                            <div class="col-4">
                                                                <label>Adresse Siège</label>
                                                                <input type="text" class="form-control" name="AddSiegEnt" placeholder="Code postale">
                                                            </div>
                                                            <hr>
                                                            <div class="col-md-3">
                                                                <label>Représentant Légal *</label>
                                                                <input type="text" class="form-control" name="RepLegEnt" placeholder="Nom du représentant légal">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Email *</label>
                                                                <input type="email" class="form-control" name="emailRepLegEnt" placeholder="Email du représentant légal">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 1 *</label>
                                                                <input type="text" class="form-control" name="Tel1RepLegEnt" placeholder="Téléphone 1 du représentant légal">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 2 *</label>
                                                                <input type="text" class="form-control" name="Tel2RepLegEnt" placeholder="Téléphone 2 du représentant légal">
                                                            </div>
                                                            <hr>
                                                            <div class="col-md-3">
                                                                <label>Personne de Contact </label>
                                                                <input type="text" class="form-control" name="PersContEnt" placeholder="Nom de la personne de contact">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Email</label>
                                                                <input type="email" class="form-control" name="emailPersContEnt" placeholder="Email du personne de Contact">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 1</label>
                                                                <input type="text" class="form-control" name="Tel1PersContEnt" placeholder="Téléphone 1 de la ersonne de Contact">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 2</label>
                                                                <input type="text" class="form-control" name="Tel2PersContEnt" placeholder="Téléphone 2 de la Personne de Contact">
                                                            </div>
                                                            <hr>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- MOE Individu Fields -->
                                        <div class="row mt-3 d-none" id="individuFields">
                                            <hr>
                                            <h6>Détails pour l’Individu</h6>
                                            <div class="col-12">
                                                <ul class="nav nav-tabs" id="individuTabs" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active" id="individu-general-tab" data-bs-toggle="tab" data-bs-target="#individu-general" type="button" role="tab" aria-controls="individu-general" aria-selected="true">Informations Personnelles</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="individu-admin-tab" data-bs-toggle="tab" data-bs-target="#individu-admin" type="button" role="tab" aria-controls="individu-admin" aria-selected="false">Informations Professionnelles</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="individu-contact-tab" data-bs-toggle="tab" data-bs-target="#individu-contact" type="button" role="tab" aria-controls="individu-contact" aria-selected="false">Informations de Contact</button>
                                                    </li>
                                                </ul>
                                                <div class="tab-content mt-3" id="individuTabsContent">
                                                    <!-- Tab 1: Informations Personnelles -->
                                                    <div class="tab-pane fade show active" id="individu-general" role="tabpanel" aria-labelledby="individu-general-tab">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label>Nom *</label>
                                                                <input type="text" name="nomInd" class="form-control" placeholder="Nom">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Prénom *</label>
                                                                <input type="text" name="PrenomInd" class="form-control" placeholder="Prénom">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Date de Naissance </label>
                                                                <input type="date" name="DateNaissInd" class="form-control">
                                                            </div>

                                                            <div class="col-md-4">
                                                                <label>Genre</label>
                                                                <select name="genreInd" id="genre" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($genres as $genre)
                                                                    <option value="{{ $genre->code_genre }}">{{ $genre->libelle_genre }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4 ">
                                                                <label>Situation Matrimoniale :</label>
                                                                <select class="form-control" name="SitMatrInd">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($SituationMatrimoniales as $SituationMatrimoniale)
                                                                        <option value="{{ $SituationMatrimoniale->id }}">{{ $SituationMatrimoniale->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4">
                                                            <label>Pays d'origine :</label>
                                                                <select name="nationnaliteInd" id="nationnalite" class="form-control">
                                                                    <option value="">Sélectionner le pays </option>
                                                                    @foreach ($tousPays  as $pay)
                                                                        <option value="{{ $pay->id }}">{{ $pay->nom_fr_fr }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 2: Informations de Contact -->
                                                    <div class="tab-pane fade" id="individu-contact" role="tabpanel" aria-labelledby="individu-contact-tab">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label>Email *</label>
                                                                <input type="email" name="emailInd" class="form-control" placeholder="Email">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label for="codePostal">Code postal</label>
                                                                <input type="text" name="CodePostalInd" id="CodePostal" class="form-control">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Adresse postale</label>
                                                                <input type="text" name="AddPostlInd" class="form-control" placeholder="Adresse">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Adresse *</label>
                                                                <input type="text" name="AddInd" class="form-control" placeholder="Adresse">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Téléphone Bureau *</label>
                                                                <input type="text" name="TelBureauInd" class="form-control" placeholder="Téléphone">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Téléphone mobile *</label>
                                                                <input type="text" name="TelMobileInd" class="form-control" placeholder="Téléphone">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 3: Informations Administratives -->
                                                    <div class="tab-pane fade" id="individu-admin" role="tabpanel" aria-labelledby="individu-admin-tab">
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <label>Pièce d’Identité :</label>
                                                                <select class="form-control" name="PieceIdentInd">
                                                                    @foreach($Pieceidentite as $Pieceidentit)
                                                                    <option value="{{ $Pieceidentit->idPieceIdent }}">{{ $Pieceidentit->libelle_long }}</option>
								                                    @endforeach

                                                                </select>

                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Numéro Pièce:</label>
                                                                <input type="text" name="NumPieceInd" class="form-control" placeholder="Numéro de CNI">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Date de etablissement:</label>
                                                                <input type="date" name="DateEtablInd" class="form-control" placeholder="Numéro de CNI">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Date de expiration:</label>
                                                                <input type="date" name="DateExpiraInd" class="form-control" placeholder="Numéro de CNI">
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label>Numéro Fiscal </label>
                                                                <input type="text" name="NumFiscInd" class="form-control" placeholder="Numéro fiscal">
                                                            </div>

                                                            <div class="col-md-6">
                                                                <label>Secteur d'activité *</label>
                                                                <select name="SectActInd" id="SecteurActiviteEntreprise" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($formeJuridiques as $formeJuridique)
                                                                        <option value="{{ $formeJuridique->id }}">{{ $formeJuridique->forme }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>--}}
                                <!-- ✅ Zone de description complémentaire -->
                                <div class="row">
                                    <div class="col-10">                                            
                                        <label>Description / Observations</label>
                                        <textarea class="form-control" id="descriptionInd" rows="3" placeholder="Ajoutez des précisions sur le Maître d’Ouvrage (ex: Budget, contraintes, accords...)"></textarea>
                                    </div>
                                    <div class="col-2 mt-4">
                                        <button type="button" class="btn btn-secondary" id="addMoeuvreBtn" style="heght: 34px">
                                            <i class="fas fa-plus"></i> Ajouter
                                        </button>

                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <table class="table table-bordered" id="moeuvreTable">
                                        <thead>
                                            <tr>
                                                <th>Nom / Libellé court</th>
                                                <th>Prénom / Libellé long</th>
                                                <th>Secteur</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Rempli dynamiquement -->
                                        </tbody>
                                    </table>
                                </div>

                                <br>
                                <div class="row">

                                    <div class="col">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary " onclick="saveStep5(nextStep)">Suivant <i class="fas fa-arrow-right"></i> </button>
                                    </div>
                                </div>

                            </div>
                            <!--Sauvegarde temporaire -->
                            <script>
                                function saveStep5(callback = null) {
                                    const codeProjet = localStorage.getItem("code_projet_temp");
                                    if (!codeProjet) {
                                        alert("Aucun projet temporaire trouvé.");
                                        return;
                                    }

                                    const acteurs = [];

                                    $("#moeuvreTable tbody tr").each(function () {
                                        const codeActeur = $(this).find('input[name="code_acteur_moeuvre[]"]').val();
                                        const secteurId = $(this).find('input[name="secteur_id[]"]').val();

                                        acteurs.push({
                                            code_acteur: codeActeur,
                                            secteur_id: secteurId
                                        });
                                    });

                                    if (acteurs.length === 0) {
                                        alert("Veuillez ajouter au moins un maître d’œuvre.");
                                        return;
                                    }

                                    $.ajax({
                                        url: '{{ route("projets.temp.save.step5") }}',
                                        method: 'POST',
                                        data: {
                                            _token: '{{ csrf_token() }}',
                                            code_projet: codeProjet,
                                            acteurs: acteurs
                                        },
                                        success: function (res) {
                                            //alert(res.message);
                                            nextStep();
                                            //if (typeof callback === "function") callback();
                                        },
                                        error: function (xhr) {
                                            let message = "Une erreur est survenue.";

                                            try {
                                                const response = JSON.parse(xhr.responseText);
                                                if (response.message) {
                                                    message = response.message;
                                                }
                                            } catch (e) {
                                                console.error("Erreur parsing JSON :", e);
                                                console.warn("Réponse brute :", xhr.responseText);
                                            }

                                            alert(message);
                                            console.error("Détail complet :", xhr.responseText);
                                        }
                                    });
                                }

                            </script>

                            <!-- 🔵 Étape : Financement -->
                            <div class="step" id="step-6">
                                <h5 class="text-secondary">💰 Ressources Financières</h5>

                                <div class="col-2 mb-3">
                                    <label for="typeFinancement">Type de financement</label>
                                    <select id="typeFinancement" name="type_financement" class="form-control">
                                        <option value="">Slectionner le type</option>
                                        @foreach ($typeFinancements as $typeFinancement)
                                            <option value="{{ $typeFinancement->code_type_financement }}">{{ $typeFinancement->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-1">
                                        <label>Local</label><br>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" id="BailOui" name="BaillOui" value="1" class="form-check-input">
                                            <label for="BailOui" class="form-check-label">Oui</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" id="BailNon" name="BaillOui" value="0" class="form-check-input">
                                            <label for="BailNon" class="form-check-label">Non</label>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <label for="bailleur">Bailleur</label>
                                        <lookup-select name="bailleur" id="bailleur">
                                            <option value="">Sélectionner le bailleur</option>
                                            @foreach ($bailleurActeurs as $bailleurActeur)
                                                <option value="{{ $bailleurActeur->code_acteur }}">{{ $bailleurActeur->libelle_court }} {{ $bailleurActeur->libelle_long }}</option>
                                            @endforeach
                                        </lookup-select>
                                    </div>

                                    <div class="col-md-2">
                                        <label for="montant">Montant</label>
                                        <input type="number" id="montant" class="form-control" placeholder="Montant">
                                    </div>

                                    <div class="col-md-1">
                                        <label for="deviseBailleur">Devise</label>
                                        <input type="text" id="deviseBailleur" class="form-control" value="{{ $Devises[0]->code_devise ?? 'XOF' }}" readonly>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="commentaire">Commentaire</label>
                                        <input type="text" id="commentaire" class="form-control" placeholder="Commentaire">
                                    </div>

                                    <div class="col text-end">
                                        <button type="button" class="btn btn-secondary" id="addFinancementBtn">Ajouter</button>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <table class="table table-bordered" id="tableFinancements">
                                        <thead>
                                            <tr>
                                                <th>Bailleur</th>
                                                <th>Montant</th>
                                                <th>Devise</th>
                                                <th>Local</th>
                                                <th>Commentaire</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary" onclick="saveStep6(nextStep)">Suivant <i class="fas fa-arrow-right"></i> </button>
                                    </div>
                                </div>
                            </div>

                            <script>
                                let financementIndex = 0;

                                document.getElementById('addFinancementBtn').addEventListener('click', function () {
                                    const bailleurLookup = document.getElementById('bailleur');
                                    const selected = bailleurLookup?.getSelected?.();

                                    if (!selected || !selected.value) {
                                        alert("Veuillez sélectionner un bailleur.");
                                        return;
                                    }

                                    const bailleurText = selected.text;
                                    const bailleurValue = selected.value;
                                    const montant = document.getElementById('montant').value;
                                    const devise = document.getElementById('deviseBailleur').value;
                                    const commentaire = document.getElementById('commentaire').value;

                                    const localRadio = document.querySelector('input[name="BaillOui"]:checked');
                                    const localValue = localRadio ? localRadio.value : '';

                                    if (!montant || !devise || localValue === '') {
                                        alert("Veuillez remplir tous les champs obligatoires.");
                                        return;
                                    }

                                    const newRow = `
                                        <tr>
                                            <td>
                                                ${bailleurText}
                                                <input type="hidden" name="financements[${financementIndex}][bailleur]" value="${bailleurValue}">
                                            </td>
                                            <td>
                                                ${montant}
                                                <input type="hidden" name="financements[${financementIndex}][montant]" value="${montant}">
                                            </td>
                                            <td>
                                                ${devise}
                                                <input type="hidden" name="financements[${financementIndex}][devise]" value="${devise}">
                                            </td>
                                            <td>
                                                ${localValue == 1 ? 'Oui' : 'Non'}
                                                <input type="hidden" name="financements[${financementIndex}][local]" value="${localValue}">
                                            </td>
                                            <td>
                                                ${commentaire}
                                                <input type="hidden" name="financements[${financementIndex}][commentaire]" value="${commentaire}">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-danger btn-sm removeRow"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    `;

                                    document.querySelector("#tableFinancements tbody").insertAdjacentHTML("beforeend", newRow);
                                    financementIndex++;

                                    // Reset
                                    document.getElementById('montant').value = '';
                                    document.getElementById('commentaire').value = '';
                                    document.getElementById('BailOui').checked = false;
                                    document.getElementById('BailNon').checked = false;

                                    if (bailleurLookup && bailleurLookup.shadowRoot) {
                                        bailleurLookup.value = null;
                                        bailleurLookup.shadowRoot.querySelector("input").value = '';
                                    }
                                });

                                // Suppression de ligne
                                document.getElementById('tableFinancements').addEventListener('click', function (e) {
                                    if (e.target.closest('.removeRow')) {
                                        e.target.closest('tr').remove();
                                    }
                                });

                                // Sauvegarde
                                function saveStep6(callback = null) {
                                    const codeProjet = localStorage.getItem("code_projet_temp");
                                    if (!codeProjet) return alert("Projet non trouvé.");

                                    const typeFinancement = document.getElementById("typeFinancement").value;
                                    localStorage.setItem("type_financement", typeFinancement);

                                    const financements = [];

                                    document.querySelectorAll("#tableFinancements tbody tr").forEach(row => {
                                        const bailleur = row.querySelector('input[name$="[bailleur]"]').value;
                                        const montant = row.querySelector('input[name$="[montant]"]').value;
                                        const devise = row.querySelector('input[name$="[devise]"]').value;
                                        const local = row.querySelector('input[name$="[local]"]').value;
                                        const commentaire = row.querySelector('input[name$="[commentaire]"]').value;

                                        financements.push({ bailleur, montant, devise, local, commentaire });
                                    });

                                    if (financements.length === 0) {
                                        alert("Aucun financement ajouté.");
                                        return;
                                    }

                                    $.ajax({
                                        url: '{{ route("projets.temp.save.step6") }}',
                                        method: 'POST',
                                        data: {
                                            _token: '{{ csrf_token() }}',
                                            code_projet: codeProjet,
                                            type_financement: typeFinancement,
                                            financements: financements
                                        },
                                        success: function (res) {
                                            nextStep();
                                            if (typeof callback === 'function') callback();
                                        },
                                        error: function (xhr) {
                                            let message = "Une erreur est survenue.";

                                            try {
                                                const response = JSON.parse(xhr.responseText);
                                                if (response.message) {
                                                    message = response.message;
                                                }
                                            } catch (e) {
                                                console.error("Erreur parsing JSON :", e);
                                                console.warn("Réponse brute :", xhr.responseText);
                                            }

                                            alert(message);
                                            console.error("Détail complet :", xhr.responseText);
                                        }
                                    });
                                }
                            </script>

        

                            <!-- 🟢 Étape  : Informations Générales -->
                            <div class="step active" id="step-1">
                                <h5 class="text-secondary">📋 Informations Générales</h5>
                                <div class="row">
                                    <div class="col-4">
                                        <label>Nature des travaux *</label>

                                        <select name="natureTraveaux" id="natureTraveaux" class="form-control">
                                            <option>Sélectionner une nature</option>
                                            @foreach ($NaturesTravaux as $NaturesTravau)
                                                <option value="{{ $NaturesTravau->code }}">{{ $NaturesTravau->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4">
                                    <label>Groupe de Projet *</label>
                                    <select class="form-control" name="groupe_projet" disabled>
                                        <option value="">Sélectionner un groupe</option>
                                        @foreach ($GroupeProjets as $groupe)
                                            <option value="{{ $groupe->code }}"
                                                {{ $groupeSelectionne == $groupe->code ? 'selected' : '' }}>
                                                {{ $groupe->libelle }}
                                            </option>
                                        @endforeach
                                    </select>

                                    </div>

                                    <div class="col-4">
                                        <label>Nom du Projet *</label>
                                        <input type="text" class="form-control" placeholder="Nom du projet" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <label for="Domaine">Domaine *</label>
                                        <select name="domaine" id="domaineSelect" class="form-control">
                                            <option value="">Sélectionner domaine</option>
                                            @foreach ($Domaines as $domaine)
                                                <option value="{{ $domaine->code }}">{{ $domaine->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col">
                                        <label for="SousDomaine">Sous-Domaine *</label>
                                        <select name="SousDomaine" id="sousDomaineSelect" class="form-control">
                                            <option value="">Sélectionner sous domaine</option>
                                        </select>
                                    </div>


				                    <div class="col">
                                        <label for="SousDomaine">Date Début prévisionnelle *</label>
                                        <input type="date" class="form-control">
                                    </div>
				                    <div class="col">
                                        <label for="SousDomaine">Date Fin prévisionnelle *</label>
                                        <input type="date" class="form-control">
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>Coût du projet</label>
                                        <input type="text" name="coutProjet" id="coutProjet" class="form-control text-end" oninput="formatNumber(this)">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Devise du coût</label>
                                        <input type="text" name="code_devise" id="deviseCout" class="form-control" value="{{ $deviseCouts->code_long }}" readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <label for="">Commentaire</label>
                                        <textarea class="form-control" name="commentaireProjet" id="commentaireProjet"></textarea>
                                    </div>
                                </div>
                                <br>


                                <div class="row">
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary" onclick="saveStep1(nextStep)">Suivant <i class="fas fa-arrow-right"></i> </button>
                                    </div>
                                </div>
                            </div>
                            <!--Sauvegarde temporaire -->
                            <script>
                                function saveStep1(callback = null) {
                                    const data = {
                                        _token: '{{ csrf_token() }}',
                                        libelle_projet: $('input[placeholder="Nom du projet"]').val(),
                                        code_sous_domaine: $('#sousDomaineSelect').val(),
                                        date_demarrage_prevue: $('input[type="date"]').eq(0).val(),
                                        date_fin_prevue: $('input[type="date"]').eq(1).val(),
                                        cout_projet: $('#coutProjet').val().replace(/\s/g, ''), // nettoie les espaces
                                        code_devise: $('#deviseCout').val(),
                                        commentaire: $('#commentaireProjet').val(),
                                        code_nature: $('#natureTraveaux').val(),
                                        code_pays: '{{ session('pays_selectionne') }}', // ajustable selon ton contexte
                                    };

                                    $.ajax({
                                        url: '{{ route("projets.temp.save.step1") }}',
                                        method: 'POST',
                                        data: data,
                                        success: function(response) {
                                            if (response.success) {
                                                console.log("Step 1 sauvegardé temporairement.");
                                                localStorage.setItem("code_projet_temp", response.code_projet);
                                                //if (typeof callback === 'function') callback();
                                                nextStep();
                                            }
                                        },
                                        error: function (xhr) {
                                            let message = "Une erreur est survenue.";

                                            try {
                                                const response = JSON.parse(xhr.responseText);
                                                if (response.message) {
                                                    message = response.message;
                                                }
                                            } catch (e) {
                                                console.error("Erreur parsing JSON :", e);
                                                console.warn("Réponse brute :", xhr.responseText);
                                            }

                                            alert(message);
                                            console.error("Détail complet :", xhr.responseText);
                                        }

                                    });
                                }
                            </script>


                            <!-- 🟠 Étape  : Localisation -->
                            <div class="step" id="step-2">
                                <ul class="nav nav-tabs" id="localisationTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="localisation-tab" data-bs-toggle="tab" data-bs-target="#localisation" type="button" role="tab">🌍 Localisation</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="infrastructures-tab" data-bs-toggle="tab" data-bs-target="#infrastructures" type="button" role="tab">🏗️ Infrastructures</button>
                                    </li>
                                </ul>

                                <div class="tab-content mt-3" id="tabContent">
                                    <!-- Localisation Tab -->
                                    <div class="tab-pane fade show active" id="localisation" role="tabpanel">
                                        <h5 class="text-secondary">🌍 Localisation</h5>
                                        <div class="row">
                                            <div class="col-4">
                                                <label>Pays *</label>
                                                @foreach ($Pays as $alpha3 => $nom_fr_fr)
                                                    <input type="text" value="{{ $nom_fr_fr }}" id="paysSelect1" class="form-control" readonly>
                                                    <input type="hidden" value="{{ $alpha3 }}" id="paysSelect" class="form-control" readonly>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label id="niveau1Label">Localité *</label>
                                                <lookup-select id="niveau1Select">
                                                    <option value="">Sélectionnez un niveau</option>
                                                </lookup-select>
                                            </div>
                                            <div class="col-md-3">
                                                <label id="niveau2Label">Niveau </label>
                                                <select class="form-control" id="niveau2Select" disabled>
                                                    <option value="">Sélectionnez un niveau</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label id="niveau3Label">Découpage</label>
                                                <select class="form-control" id="niveau3Select" disabled>
                                                    <option value="">Sélectionnez un niveau</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 text-end">
                                                <button type="button" class="btn btn-secondary" id="addLocaliteBtn">Ajouter</button>
                                            </div>
                                        </div> <br>
                                        <div class="row">
                                            <div class="col">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th hidden></th>
                                                            <th>Localité</th>
                                                            <th>Niveau</th>
                                                            <th hidden></th>
                                                            <th>Découpage</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tableLocalites">
                                                        <!-- Dynamically added rows -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Infrastructure Form -->
                                    <div class="tab-pane fade" id="infrastructures" role="tabpanel">
                                        <h5 class="text-secondary">🏗️ Infrastructures</h5>

                                        <!-- Formulaire principal -->
                                        <div id="infrastructureForm">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Famille d'Infrastructure *</label>
                                                    <select class="form-control" id="FamilleInfrastruc">
                                                        <option value="">Sélectionnez </option>
                                                        <!-- dynamiquement rempli -->
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Nom de l'infrastructure *</label>
                                                    <input type="text" class="form-control" id="infrastructureName">
                                                </div>
                                            </div>

                                            <!-- Ajout de caractéristiques -->
                                            <div class="row mt-3">
                                                <div class="col-md-3">
                                                    <label>Type de caractéristique</label>
                                                    <select class="form-control" id="tyCaract">
                                                        <option value="">Sélectionner le type </option>
                                                        @foreach ($TypeCaracteristiques as $TypeCaracteristique)
                                                            <option value="{{ $TypeCaracteristique->idTypeCaracteristique }}">{{ $TypeCaracteristique->libelleTypeCaracteristique }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Caractéristique</label>
                                                    <select class="form-control" id="caract">
                                                        <option value="">Sélectionner la caractéristique</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Unité</label>
                                                    <select class="form-control" id="unitCaract" >
                                                        <option value="">Sélectionner l'unité</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Valeur</label>
                                                    <input type="text" class="form-control" id="caractValue">
                                                </div>
                                            </div>
                                            <div class="text-end mt-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="addCaractToInfra">+ Ajouter caractéristique</button>
                                            </div>

                                            <!-- Liste temporaire des caractéristiques pour l'infrastructure en cours -->
                                            <div class="mt-3">
                                                <ul id="caractList" class="list-group"></ul>
                                            </div>

                                            <div class="text-end mt-3">
                                                <button type="button" class="btn btn-secondary" id="addInfrastructureBtn">Ajouter l'infrastructure</button>
                                            </div>
                                        </div>

                                        <hr>
                                        <!-- Liste finale des infrastructures -->
                                        <div class="row">
                                            <div class="col">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Nom</th>
                                                            <th>Famille</th>
                                                            <th>Caractéristiques</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tableInfrastructures">
                                                        <!-- Dynamically added rows -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <div class="row mt-3">
                                    <div class="col">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary" onclick="saveStep2(nextStep)">Suivant <i class="fas fa-arrow-right"></i> </button>
                                    </div>
                                </div>
                        </div>
                        <!--Sauvegarde temporaire -->
                        <script>
                            function saveStep2(callback = null) {
                                const codeProjet = localStorage.getItem("code_projet_temp");
                                if (!codeProjet) return alert("Aucun projet n’a encore été créé.");

                                // 🔁 Lecture des lignes de localisation
                                const localites = [];
                                $("#tableLocalites tr").each(function () {
                                    const cols = $(this).find("td");
                                    if (cols.length > 0) {
                                        localites.push({
                                            code_rattachement: cols.eq(0).text(),
                                            libelle: cols.eq(2).text(),
                                            niveau: cols.eq(3).text(),
                                            decoupage: cols.eq(4).text()
                                        });
                                    }
                                });

                                if (localites.length > 0) {
                                    localStorage.setItem("code_localisation", localites[0].id); // pour finalisation
                                }

                                // 🔁 Lecture des infrastructures + caractéristiques
                                const infrastructures = [];
                                $("#tableInfrastructures tr").each(function () {
                                    const tds = $(this).find("td");
                                    const name = $(tds[0]).find('input[type="hidden"]').val();
                                    const famille = $(tds[1]).find('input[type="hidden"]').val();

                                    const caracts = [];
                                    $(tds[2]).find('input[type="hidden"]').each(function () {
                                        const parts = $(this).val().split('|');
                                        caracts.push({
                                            id: parts[0],
                                            unite_id: parts[1],
                                            valeur: parts[2]
                                        });
                                    });

                                    infrastructures.push({
                                        libelle: name,
                                        famille_code: famille,
                                        localisation_id: null,
                                        caracteristiques: caracts
                                    });
                                });

                                // 📨 Envoi AJAX
                                $.ajax({
                                    url: '{{ route("projets.temp.save.step2") }}',
                                    method: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        code_projet: codeProjet,
                                        localites: localites,
                                        infrastructures: infrastructures
                                    },
                                    success: function (response) {
                                        if (typeof callback === "function") callback();
                                        else nextStep();
                                    },
                                    error: function (xhr) {
                                        let message = "Une erreur est survenue.";

                                        try {
                                            const response = JSON.parse(xhr.responseText);
                                            if (response.message) {
                                                message = response.message;
                                            }
                                        } catch (e) {
                                            console.error("Erreur parsing JSON :", e);
                                            console.warn("Réponse brute :", xhr.responseText);
                                        }

                                        alert(message);
                                        console.error("Détail complet :", xhr.responseText);
                                    }
                                });
                            }
                        </script>
                        <div class="step" id="step-3">
                        <h5 class="text-secondary">🌍 Infrastructures</h5>
                                <div class="row">
                                    <br>
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
                                                <div class="col-2" style="width: 22%;">
                                                    <p for="infrastructure">Infrastructure:</p>
                                                    <lookup-select name="infrastructure" id="insfrastructureSelect">
                                                        <option value="">Sélectionner l'infrastructure</option>
                                                        @foreach ($infrastructures as $infrastructure)
                                                            <option value="{{ $infrastructure->id }}">{{ $infrastructure->libelle }}</option>
                                                        @endforeach
                                                    </lookup-select>
                                                </div>
                                                <div class="col-2" style="margin-top: 7px; width: 17%;">
                                                    <a href="#"  id="toggleBeneficiaire">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"></path>
                                                        </svg>
                                                        Bénéficiaire
                                                    </a>




                                                        <button type="button" style="margin-top: 7px; float: right;" class="btn btn-secondary" id="addAction">
                                                            <i class="fas fa-plus"></i>
                                                            Action
                                                        </button>
                                                </div>
                                        </fieldset>
                                        <div class="row mt-3 d-none" id="infrastructureField">
                                            <div class="row">
                                                <div class="row">
                                                    <label for="structure_ratache">Bénéficiaire :</label>
                                                    <input type="hidden" name="CodeProjetBene" id="CodeProjetBene">
                                                    <input type="hidden" name="numOrdreBene" id="numOrdreBene">

                                                    <div class="col">
                                                        <label for="age">Localité :</label>
                                                        <input type="radio" name="beneficiaire_type[]" value="localite" id="age" checked="true" onclick="afficheSelect('localite')" style="margin-right: 15px;">
                                                    </div>
                                                    <div class="col">
                                                        <label for="sousp">Acteur :</label>
                                                        <input type="radio" name="beneficiaire_type[]" value="acteur" id="sousp" onclick="afficheSelect('acteur')" style="margin-right: 15px;">
                                                    </div>
                                                    <div class="col" >
                                                        <label for="min">infrastructure :</label>
                                                        <input type="radio" name="beneficiaire_type[]" value="infrastructure" id="dep" onclick="afficheSelect('infrastructure')" style="margin-right: 15px;">

                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col">
                                                        <lookup-select name="beneficiaire_code[]" id="localite" style="display: none;">
                                                            <option value="">Sélectionner la localité</option>
                                                            
                                                        </lookup-select>
                                                        <lookup-select name="beneficiaire_code[]" id="acteur" style="display: none;">
                                                            <option value="">Sélectionner l'acteur</option>
                                                            @foreach ($acteurs as $acteur)
                                                                <option value="{{ $acteur?->code_acteur }}">{{ $acteur?->libelle_court }} {{ $acteur?->libelle_long }} </option>
                                                            @endforeach
                                                        </lookup-select>
                                                        <lookup-select name="beneficiaire_code[]" id="infrastructure" style="display: none;">
                                                            <option value="">Sélectionner l'infrastructure</option>
                                                            @foreach ($infrastructures as $infrastructure)
                                                                <option value="{{ $infrastructure->id }}">{{ $infrastructure->libelle }}</option>
                                                            @endforeach
                                                        </lookup-select>
                                                    </div>

                                                    <div class="col">
                                                        <button type="button" class="btn btn-secondary" id="addBtnBene">
                                                            <i class="fas fa-plus"></i>
                                                            Ajouter
                                                        </button>
                                                    </div>
                                                    <div class="col">
                                                        <button type="button" class="btn btn-danger" style="width: 121px" id="deleteBtn">
                                                            <i class="fas fa-trash"></i>
                                                            Supprimer
                                                        </button>
                                                    </div>
                                                </div>
                                                <br><br>
                                            </div>
                                            <br>
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
                                        <hr>
                                    <div>

                                    <div class="table table-bordered" >
                                        <table id="tableActionMener" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th>N° d'ordre</th>
                                                    <th>Action</th>
                                                    <th>Quantité</th>
                                                    <th>Infrastructure</th>                                                    
                                                    <th hidden>InfrastructureCode</th>
                                                    <th>libelle Bénéficiaires</th>
                                                    <th hidden>Code bénéficiaire</th>
                                                    <th>type bénéficiaire</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="beneficiaire-table-body">
                                                <!-- Le corps du tableau sera géré dynamiquement via JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                                </div>


                                <div class="row mt-3">
                                    <div class="col">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()"><i class="fas fa-arrow-left"></i> Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary" onclick="saveStep3(nextStep)">Suivant <i class="fas fa-arrow-right"></i> </button>
                                    </div>
                                </div>
                            </div>
                            <!--Sauvegarde temporaire -->
                            <script>
                                function saveStep3(callback = null) {
                                    const codeProjet = localStorage.getItem("code_projet_temp");
                                    if (!codeProjet) return alert("Aucun projet temporaire trouvé.");

                                    const actions = [];
                                    $("#tableActionMener tbody tr").each(function () {
                                        const ordre = $(this).find("td:eq(0)").text();
                                        const actionText = $(this).find("td:eq(1)").text();
                                        const quantite = $(this).find("td:eq(2)").text();
                                        const infraLabel = $(this).find("td:eq(3)").text();
                                        const infraCode = $(this).find("td:eq(4)").text();

                                        const codeB = $(this).find("td:eq(6)").text();
                                        const typeB = $(this).find("td:eq(7)").text();
                                        const codePays = $(this).find("td:eq(8)").text();
                                        const codeRattachement = $(this).find("td:eq(9)").text();

                                        actions.push({
                                            ordre: ordre,
                                            action_code: getActionCodeByLabel(actionText),
                                            quantite: quantite,
                                            infrastructure_code: infraCode,
                                            beneficiaires: [{
                                                code: codeB,
                                                type: typeB,
                                                codePays: codePays,
                                                codeRattachement: codeRattachement
                                            }]
                                        });
                                    });

                                    $.ajax({
                                        url: '{{ route("projets.temp.save.step3") }}',
                                        method: 'POST',
                                        data: {
                                            _token: '{{ csrf_token() }}',
                                            code_projet: codeProjet,
                                            actions: actions
                                        },
                                        success: function (response) {
                                            //alert(response.message || "Étape 3 sauvegardée.");
                                            nextStep();
                                            //if (typeof callback === "function") callback();
                                        },
                                        error: function (xhr) {
                                            let message = "Une erreur est survenue.";

                                            try {
                                                const response = JSON.parse(xhr.responseText);
                                                if (response.message) {
                                                    message = response.message;
                                                }
                                            } catch (e) {
                                                console.error("Erreur parsing JSON :", e);
                                                console.warn("Réponse brute :", xhr.responseText);
                                            }

                                            alert(message);
                                            console.error("Détail complet :", xhr.responseText);
                                        }
                                    });
                                }

                                // Cette fonction est à adapter si tu veux mapper l'intitulé de l'action à son code
                                function getActionCodeByLabel(label) {
                                    const select = document.getElementById('action');
                                    for (let option of select.options) {
                                        if (option.text === label) return option.value;
                                    }
                                    return null;
                                }
                            </script>

                            <!-- 📜 Modal pour la liste des documents -->
                            <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true" style="background: transparent !important;">
                                <div class="modal-dialog">
                                    <div class="modal-content" style="width: 100% !important; background: white;">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="documentModalLabel">📜 Documents à fournir</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul>
                                                <li>📄 Cahier des Charges</li>
                                                <li>📊 Études Préliminaires (Faisabilité, Impact Environnemental, Géotechnique)</li>
                                                <li>📜 Plans et Maquettes du Projet</li>
                                                <li>💰 Budget Prévisionnel</li>
                                                <li>📝 Permis de Construire (si applicable)</li>
                                                <li>🏢 Justificatif de propriété du terrain</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 🟡 Étape  : Documents -->
                            <div class="step" id="step-7">
                            <div class="document-upload-section">
                                <h5 class="text-secondary">📎 Documents et Pièces Justificatives</h5>
                                
                                <div class="upload-container">
                                    <!-- Zone de dépôt -->
                                    <div class="upload-dropzone" id="dropZone">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Glissez-déposez vos fichiers ici</p>
                                        <p class="small">ou</p>
                                        <button type="button" class="btn btn-outline-primary" id="browseFilesBtn">
                                            Parcourir vos fichiers
                                        </button>
                                        <p class="file-limits">
                                            Formats acceptés: .pdf, .dwg, .jpg, .docx, .xlsx<br>
                                            Taille max: 100MB par fichier
                                        </p>
                                        <input type="file" id="fileUpload" multiple style="display: none;" 
                                            accept=".pdf,.dwg,.dxf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip,.rar">
                                    </div>
                                    
                                    <!-- Barre de progression -->
                                    <div class="upload-progress mt-3" id="uploadProgressContainer" style="display: none;">
                                        <div class="progress-info">
                                            <span id="uploadStatus">Préparation de l'envoi...</span>
                                            <span id="uploadPercent">0%</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                                id="uploadProgressBar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Fichiers sélectionnés -->
                                    <div class="uploaded-files-list mt-3" id="uploadedFilesList">
                                        <div class="list-header">
                                            <span>Fichiers à uploader (<span id="fileCount">0</span>)</span>
                                            <span id="totalSize">0 MB</span>
                                        </div>
                                        <div class="files-container" id="filesContainer">
                                            <!-- Les fichiers apparaîtront ici -->
                                        </div>
                                    </div>
                                </div>
                                <br><br>
                                <div class="row upload-actions">
                                    <div class="col">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep()">
                                      <i class="fas fa-arrow-left"></i> Précédent
                                    </button>
                                    </div>
                                    <div class="col text-end">
                                    <button type="button" class="btn btn-success" id="submitDocumentsBtn" disabled>
                                        <i class="fas fa-check"></i> Valider les documents
                                    </button>
                                    </div>
                                </div>
                            </div>
                            </div>
                            <!--Sauvegarde temporaire -->
                            <script>
                                /*document.getElementById('projectForm').addEventListener('submit', function(event) {
                                    event.preventDefault();
                                    
                                    const codeProjet = localStorage.getItem("code_projet_temp");
                                    if (!codeProjet) {
                                        alert("Aucun projet trouvé. Veuillez compléter les étapes <i class="fas fa-arrow-left"></i> Précédentes.");
                                        return;
                                    }

                                    const formData = new FormData();
                                    formData.append('code_projet', codeProjet);
                                    formData.append('_token', '{{ csrf_token() }}');

                                    // Ajouter chaque fichier individuellement
                                    const fileInput = document.getElementById('fileUpload');
                                    for (let i = 0; i < fileInput.files.length; i++) {
                                        formData.append('fichiers[]', fileInput.files[i]);
                                    }

                                    fetch('{{ route("projets.temp.save.step7") }}', {
                                        method: 'POST',
                                        body: formData,
                                        headers: {
                                            'Accept': 'application/json',
                                        },
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            finaliserCodeProjet();
                                            alert("Projet enregistré avec succès !");
                                            localStorage.removeItem("code_projet_temp");
                                            window.location.href = '{{ route("project.create") }}';
                                        } else {
                                            alert("Erreur : " + (data.message || "Erreur inconnue"));
                                        }
                                    })
                                    .catch(error => {
                                        console.error("Erreur d'envoi des fichiers :", error);
                                        alert("Erreur lors de la soumission. Voir la console pour plus de détails.");
                                    });
                                });*/

                            </script>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    /*function finaliserCodeProjet() {
        const codeTemp = localStorage.getItem("code_projet_temp");
        const codeLocalisation = localStorage.getItem("code_localisation");
        const typeFinancement = localStorage.getItem("type_financement");// à stocker depuis step6

        $.ajax({
            url: '{{ route("projets.finaliser") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                code_projet_temp: codeTemp,
                code_localisation: codeLocalisation,
                type_financement: typeFinancement
            },
            success: function (response) {
                alert("Code projet final : " + response.code_projet_final);
                localStorage.setItem("code_projet_temp", response.code_projet_final); // mise à jour
            },
            error: function () {
                alert("Erreur lors de la finalisation du code projet.");
            }
        });
    }*/

    function annulerProjetTemporaire() {
        const codeProjet = localStorage.getItem('code_projet_temp');

        if (!codeProjet || !confirm("Confirmer l'annulation du projet ?")) return;

        $.ajax({
            url: '{{ route("projets.abort") }}',
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}',
                code_projet: codeProjet
            },
            success: function (res) {
                alert(res.message);
                localStorage.removeItem("code_projet_temp");
                window.location.reload(); // ou redirection
            },
            error: function () {
                alert("Erreur lors de l'annulation du projet.");
            }
        });
    }
</script>
<script>
    //Séparateur de milliers
function formatNumber(input) {
    let value = input.value.replace(/\s/g, '').replace(/[^\d]/g, '');
    if (value === '') {
        input.value = '';
        return;
    }
    input.value = Number(value).toLocaleString('fr-FR'); // espace comme séparateur de milliers
}
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const acteurSelect = document.getElementById('acteurMoeSelect');
        const secteurActiviteContainer = document.getElementById('sectActivEntMoe').parentElement;
        const acteurSelect2 = document.getElementById('acteurSelect');
        const secteurActiviteContainer2 = document.getElementById('sectActivEnt').parentElement;
        
        if (!acteurSelect || !secteurActiviteContainer|| !acteurSelect2 || !secteurActiviteContainer2 ) {
            console.error("Les éléments HTML avec les identifiants 'acteurMoeSelect' ou 'sectActivEntMoe' n'ont pas été trouvés.");
            return;
        }
        acteurSelect.addEventListener('change', function () {
            const selectedValue = acteurSelect.value;
            console.log("Valeur sélectionnée :", selectedValue);


            if (selectedValue === '5689') {
                // Afficher le secteur d'activité
                secteurActiviteContainer.style.display = 'block';
            } else {
                // Masquer le secteur d'activité
                secteurActiviteContainer.style.display = 'none';
            }
        });
        acteurSelect2.addEventListener('change', function () {
            const selectedValue2 = acteurSelect2.value;
            console.log("Valeur sélectionnée :", selectedValue2);


            if (selectedValue2 === '5689') {
                // Afficher le secteur d'activité
                secteurActiviteContainer2.style.display = 'block';
            } else {
                // Masquer le secteur d'activité
                secteurActiviteContainer2.style.display = 'none';
            }
        });

        // Initialiser l'affichage en fonction de la sélection actuelle
        if (acteurSelect.value === '5689') {
            secteurActiviteContainer.style.display = 'block';
        } else {
            secteurActiviteContainer.style.display = 'none';
        }
        if (acteurSelect2.value === '5689') {
            secteurActiviteContainer2.style.display = 'block';
        } else {
            secteurActiviteContainer2.style.display = 'none';
        }
    });
    /*document.addEventListener("DOMContentLoaded", function () {
        const lookupRL = document.getElementById("RepLeEntMoe"); // Sélecteur du lookup-select
        const emailRL = document.querySelector("input[name='EmailRepLeEntMoe']");
        const telephone1RL = document.querySelector("input[name='Tel1RepLeEntMoe']");
        const telephone2RL = document.querySelector("input[name='Tel2RepLeEntMoe']");

        const acteurs = @json($acteurRepres); // Récupération des acteurs depuis Laravel Blade

        function updateRepresentantLegal() {
            let selectedValue = lookupRL.value; // Récupérer l'ID sélectionné

            // Trouver les données du représentant légal
            let acteur = acteurs.find(a => a.code_acteur == selectedValue);

            if (acteur) {
                emailRL.value = acteur.email || ""; // Mettre à jour l'email
                telephone1RL.value = acteur.telephone_mobile || ""; // Mettre à jour Téléphone 1
                telephone2RL.value = acteur.telephone_bureau || ""; // Mettre à jour Téléphone 2
            } else {
                emailRL.value = ""; // Vider si aucun représentant légal trouvé
                telephone1RL.value = "";
                telephone2RL.value = "";
            }
        }

        // Écouter les changements sur le `lookup-select`
        //lookupRL.addEventListener("change", updateRepresentantLegal);

        // Optionnel : Remplir les champs au chargement si une valeur est déjà sélectionnée
        setTimeout(updateRepresentantLegal, 500);

        // Ajouter les champs cachés dynamiques pour conserver les modifications lors du submit
        const form = document.querySelector("form");
        form.addEventListener("submit", function () {
            // Ajouter des champs cachés pour les valeurs modifiées
            let hiddenEmail = document.createElement("input");
            hiddenEmail.type = "hidden";
            hiddenEmail.name = "emailRL_modified";
            hiddenEmail.value = emailRL.value;
            form.appendChild(hiddenEmail);

            let hiddenTel1 = document.createElement("input");
            hiddenTel1.type = "hidden";
            hiddenTel1.name = "telephone1RL_modified";
            hiddenTel1.value = telephone1RL.value;
            form.appendChild(hiddenTel1);

            let hiddenTel2 = document.createElement("input");
            hiddenTel2.type = "hidden";
            hiddenTel2.name = "telephone2RL_modified";
            hiddenTel2.value = telephone2RL.value;
            form.appendChild(hiddenTel2);
        });
    });
    document.addEventListener("DOMContentLoaded", function () {
        const lookup = document.getElementById("nomPCMoe"); // Sélection du lookup-multiselect
        const contactContainer = document.getElementById("contactContainerMoe");
        const acteurs = @json($acteurRepres); // Récupération des contacts depuis Laravel

        function updateContacts() {
            contactContainer.innerHTML = ""; // Vider le contenu

            let selectedValues = lookup.value; // Récupère les valeurs sélectionnées

            if (selectedValues.length === 0) {
                return; // Si aucune sélection, ne rien afficher
            }

            selectedValues.forEach(code => {
                let acteur = acteurs.find(a => a.code_acteur == code);
            // console.log('acteur :',acteur);
                if (acteur) {
                    let row = document.createElement("div");
                    row.classList.add("d-flex", "align-items-center", "me-3");

                    row.innerHTML = `
                        <div class="me-3">
                            <label>Nom</label>
                            <input type="text" class="form-control" value="${acteur.libelle_court} ${acteur.libelle_long}" readonly>
                        </div>
                        <div class="me-3">
                            <label>Email</label>
                            <input type="email" class="form-control" name="emailPCMoe" value="${acteur.email || ''}">
                        </div>
                        <div class="me-3">
                            <label>Téléphone 1</label>
                            <input type="text" class="form-control" name="Tel1PcMoe" value="${acteur.telephone_mobile || ''}">
                        </div>
                        <div class="me-3">
                            <label>Téléphone 2</label>
                            <input type="text" class="form-control" name="Tel2PCMoe" value="${acteur.telephone_bureau || ''}">
                        </div>
                    `;

                    contactContainer.appendChild(row);
                }
            });
        }

        // Écouter le changement de sélection sur `lookup-multiselect`
        lookup.addEventListener("change", updateContacts);

        // Optionnel : Afficher les données au chargement si des valeurs sont déjà sélectionnées
        setTimeout(updateContacts, 500);
    });*/
</script>
<script>
    let currentStep = 1;
    const totalSteps = 7;
    let uploadedFiles = [];

    function showStep(step) {
        document.querySelectorAll('.step').forEach((element, index) => {
            element.classList.remove('active');
        });
        document.getElementById('step-' + step).classList.add('active');
        updateProgressBar(step);
    }

    function nextStep() {
        if (currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    }

    function updateProgressBar(step) {
        const progressBar = document.getElementById('progressBar');
        const progressPercentage = (step / totalSteps) * 100;
        progressBar.style.width = progressPercentage + "%";
    }/*
// Configuration de l'upload
const MAX_FILE_SIZE = 100 * 1024 * 1024; // 100MB
const MAX_TOTAL_SIZE = 500 * 1024 * 1024; // 500MB max total
const ALLOWED_TYPES = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'image/jpeg',
    'image/png',
    'application/zip',
    'application/x-rar-compressed',
    'application/x-dwg', // AutoCAD
    'application/x-dxf'  // AutoCAD DXF
];

document.getElementById('fileUpload').addEventListener('change', function(e) {
    const files = e.target.files;
    let totalSize = 0;
    
    for (let i = 0; i < files.length; i++) {
        // Vérification taille fichier
        if (files[i].size > MAX_FILE_SIZE) {
            alert(`Le fichier ${files[i].name} dépasse la taille maximale de 100MB`);
            e.target.value = '';
            return;
        }
        
        // Vérification type de fichier
        if (!ALLOWED_TYPES.includes(files[i].type)) {
            alert(`Type de fichier non supporté: ${files[i].name}`);
            e.target.value = '';
            return;
        }
        
        totalSize += files[i].size;
    }
    
    // Vérification taille totale
    if (totalSize > MAX_TOTAL_SIZE) {
        alert(`La taille totale des fichiers (${(totalSize/1024/1024).toFixed(2)}MB) dépasse la limite de 500MB`);
        e.target.value = '';
    }
});

// Upload progressif avec feedback
async function uploadFiles(files) {
    const progressBar = document.getElementById('uploadProgress');
    const statusDiv = document.getElementById('uploadStatus');
    const codeProjet = localStorage.getItem('code_projet_temp');
    
    let uploadedCount = 0;
    const chunkSize = 10 * 1024 * 1024; // 10MB par chunk
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        statusDiv.textContent = `Traitement de ${file.name}...`;
        
        // Découpage en chunks pour les très gros fichiers
        if (file.size > 50 * 1024 * 1024) {
            await uploadInChunks(file, codeProjet, chunkSize, progressBar);
        } else {
            await uploadFile(file, codeProjet);
        }
        
        uploadedCount++;
        progressBar.value = (uploadedCount / files.length) * 100;
    }
    
    return uploadedCount;
}
async function uploadFile(file, codeProjet) {
    const formData = new FormData();
    formData.append('fichiers[]', file);
    formData.append('code_projet', codeProjet);
    formData.append('_token', '{{ csrf_token() }}');
    
    const response = await fetch('{{ route("projets.temp.save.step7") }}', {
        method: 'POST',
        body: formData
    });
    
    if (!response.ok) {
        throw new Error(`Échec upload ${file.name}`);
    }
    
    return response.json();
}*/
    /*function displayUploadedFiles() {
        let fileList = document.getElementById('uploadedFiles');
        fileList.innerHTML = "";

        uploadedFiles.forEach((file, index) => {
            let fileItem = document.createElement('div');
            fileItem.classList.add('file-item');
            fileItem.innerHTML = `

                <span><i class="fas fa-file"></i> ${file.name}</span>
                <i class="fas fa-trash " onclick="removeFile(${index})"></i>
            `;
            fileList.appendChild(fileItem);
        });
    }*/

    /*function removeFile(index) {
        uploadedFiles.splice(index, 1);
        displayUploadedFiles();
    }*/
    

    ////////////////ACTEURS
    document.addEventListener("DOMContentLoaded", function () {
        let acteurInput = document.getElementById("acteurMoeInput");
        let acteurList = document.getElementById("acteurMoeList");
        //let entrepriseFields = document.getElementById("moeEntrepriseFields");
        let individuFields = document.getElementById("moeIndividuFields");
        if (!acteurInput) return; // Sécurité si l'élément n'est pas présent
        acteurInput.addEventListener("keyup", function () {
            let searchValue = acteurInput.value.trim();

            if (searchValue.length > 1) {
                fetch(`{{ url('/')}}/api/acteurs?search=${searchValue}`)
                    .then(response => response.json())
                    .then(data => {
                        acteurList.innerHTML = "";
                        data.forEach(item => {
                            let li = document.createElement("li");
                            li.classList.add("list-group-item", "list-group-item-action");
                            li.textContent = item.libelle_long;
                            li.dataset.id = item.code_acteur;
                            li.dataset.type = item.type_acteur; // Stocker le type d'acteur

                            li.onclick = () => {
                                acteurInput.value = item.libelle_long;
                                acteurList.innerHTML = "";

                                // Remplissage automatique des champs selon le type d'acteur
                                remplirChampsActeur(item);

                                // Désactivation des autres champs si acteur existant sélectionné
                                if (item.type_acteur === "Entreprise") {
                                    entrepriseFields.classList.remove("d-none");
                                    individuFields.classList.add("d-none");
                                } else if (item.type_acteur === "Individu") {
                                    entrepriseFields.classList.add("d-none");
                                    individuFields.classList.remove("d-none");
                                }
                            };

                            acteurList.appendChild(li);
                        });

                        // Option pour ajouter un nouvel acteur
                        let addNewOption = document.createElement("li");
                        addNewOption.classList.add("list-group-item", "text-primary");
                        addNewOption.innerHTML = `<i class="fas fa-plus-circle"></i> Ajouter "${searchValue}"`;
                        addNewOption.onclick = () => {
                            acteurInput.value = searchValue;
                            acteurList.innerHTML = "";
                            entrepriseFields.classList.add("d-none");
                            individuFields.classList.add("d-none");
                            activerChampsActeur(); // Activer tous les champs pour une saisie manuelle
                        };
                        acteurList.appendChild(addNewOption);
                    })
                    .catch(error => console.error("Erreur lors de la recherche des acteurs :", error));
            } else {
                acteurList.innerHTML = "";
            }
        });

        function remplirChampsActeur(acteur) {
            document.getElementById("nomEntreprise").value = acteur.nom_entreprise || "";
            document.getElementById("adresseEntreprise").value = acteur.adresse || "";
            document.getElementById("emailEntreprise").value = acteur.email || "";
            document.getElementById("telephoneEntreprise").value = acteur.telephone || "";
            document.getElementById("numImmatriculation").value = acteur.num_immatriculation || "";
            document.getElementById("numFiscal").value = acteur.num_fiscal || "";

            document.getElementById("nomIndividu").value = acteur.nom || "";
            document.getElementById("prenomIndividu").value = acteur.prenom || "";
            document.getElementById("emailIndividu").value = acteur.email || "";
            document.getElementById("telephoneIndividu").value = acteur.telephone || "";
            document.getElementById("cniIndividu").value = acteur.num_cni || "";

            // Désactiver les champs si acteur existant
            desactiverChampsActeur();
        }

        function desactiverChampsActeur() {
            document.querySelectorAll("#moeEntrepriseFields input, #moeIndividuFields input").forEach(input => {
                input.disabled = true;
            });
        }

        function activerChampsActeur() {
            document.querySelectorAll("#moeEntrepriseFields input, #moeIndividuFields input").forEach(input => {
                input.disabled = false;
                input.value = ""; // Réinitialiser les champs
            });
        }
    });




    ///////////////////////////LOCALLISATION
    
    let selectedLocalite = {
        id: null,
        code_rattachement: null,
        libelle: null,
        niveau: null,
        code_decoupage: null,
        libelle_decoupage: null
    };

    $(document).ready(function () {
        // Récupérer le code du pays
        const paysCode = $("#paysSelect").val();

        if (paysCode) {
            // Charger les localités du pays sélectionné
            $.ajax({
                url: '{{ url("/") }}/get-localites/' + paysCode,
                type: "GET",
                success: function (data) {
                    $("#niveau1Select").empty().append('<option value="">Sélectionnez une localité</option>');
                    $.each(data, function (index, localite) {
                        $("#niveau1Select").append(
                        `<option value="${localite.id}" data-code="${localite.code_rattachement}">
                            ${localite.libelle}
                         </option>`
                    );
                    });
                }
            });
        }

        // Lorsqu'on sélectionne une localité
        $("#niveau1Select").change(function () {
            const lookup = document.getElementById("niveau1Select");
            const localiteId = lookup.value;
            const selected = lookup.getSelected(); // méthode qu’on a ajoutée dans ton composant
            const localiteText = selected ? selected.text : "";
            const codeRattachement = selected.code || null;;

            // Stocker le libellé sélectionné
            selectedLocalite.libelle = localiteText;
            selectedLocalite.id = localiteId;
            selectedLocalite.code_rattachement = codeRattachement;

            if (localiteId) {
                // Charger le niveau et découpage associés
                $.ajax({
                    url: '{{ url("/") }}/get-decoupage-niveau/' + localiteId,
                    type: "GET",
                    success: function (data) {
                        $("#niveau2Select").empty()
                            .append('<option value="' + data.niveau + '">' + data.niveau + '</option>')
                            .prop("disabled", false);

                        $("#niveau3Select").empty()
                            .append('<option value="' + data.code_decoupage + '">' + data.libelle_decoupage + '</option>')
                            .prop("disabled", false);

                        selectedLocalite.niveau = data.niveau;
                        selectedLocalite.code_decoupage = data.code_decoupage;
                        selectedLocalite.libelle_decoupage = data.libelle_decoupage;
                    }
                });
            }
        });

        // Lorsqu'on clique sur "Ajouter"
        $("#addLocaliteBtn").click(function () {
            if (!selectedLocalite.id || !selectedLocalite.niveau || !selectedLocalite.code_decoupage) {
                alert("Veuillez sélectionner une localité avec son niveau et découpage.");
                return;
            }

            // Ajouter une ligne dans le tableau avec libellés + identifiants
            const newRow = `
                <tr data-id="${selectedLocalite.id}" data-code_rattachement="${selectedLocalite.code_rattachement}">
                    <td hidden>${selectedLocalite.code_rattachement}</td>
                    <td hidden>${selectedLocalite.id}</td>
                    <td>${selectedLocalite.libelle} </td>
                    <td>${selectedLocalite.niveau}</td>
                    <td hidden>${selectedLocalite.code_decoupage}</td>
                    <td>${selectedLocalite.libelle_decoupage} </td>
                    <td><button type="button" class="btn btn-danger btn-sm deleteRowBtn">Supprimer</button></td>
                </tr>
            `;

            $("#tableLocalites").append(newRow);

            // Réinitialiser les sélections
            $("#niveau1Select").val("");
            $("#niveau2Select").empty().append('<option value="">Sélectionnez un niveau</option>').prop("disabled", true);
            $("#niveau3Select").empty().append('<option value="">Sélectionnez un niveau</option>').prop("disabled", true);
            selectedLocalite = {
                id: null,
                libelle: null,
                code_rattachement: null,
                niveau: null,
                code_decoupage: null,
                libelle_decoupage: null
            };
        });

        // Supprimer une ligne du tableau
        $(document).on("click", ".deleteRowBtn", function () {
            $(this).closest("tr").remove();
        });
    });
    ///////////////////////////INFRASTRUCTURES
 
    document.getElementById('domaineSelect').addEventListener('change', function() {
        let codeSousDomaine = this.value;

        fetch('{{ url("/") }}/get-familles/' + codeSousDomaine)
            .then(response => response.json())
            .then(data => {
                let select = document.getElementById('FamilleInfrastruc');
                select.innerHTML = '<option value="">Sélectionnez</option>';

                data.forEach(function(famille) {
                    let option = document.createElement('option');
                    option.value = famille.idFamille;
                    option.text = famille.libelleFamille;
                    select.appendChild(option);
                });
            });
    });

    document.getElementById('tyCaract').addEventListener('change', function() {
        let idType = this.value;

        // Si rien n’est sélectionné, vide simplement l'autre select
        if (!idType) {
            document.getElementById('tyCaract').innerHTML = '<option value=""></option>';
            return;
        }

        fetch('{{ url("/") }}/get-caracteristiques/' + idType)
            .then(response => response.json())
            .then(data => {
                let caractSelect = document.getElementById('caract');
                caractSelect.innerHTML = '<option value=""></option>';

                data.forEach(function(caract) {
                    let option = document.createElement('option');
                    option.value = caract.idCaracteristique;
                    option.text = caract.libelleCaracteristique;
                    caractSelect.appendChild(option);
                });
            });
    });

    document.getElementById('caract').addEventListener('change', function () {
        let idCaracteristique = this.value;

        fetch('{{ url("/") }}/get-unites/' + idCaracteristique)
            .then(response => response.json())
            .then(data => {
                console.log('Unites reçues:', data);

                let selectUnite = document.getElementById('unitCaract');
                selectUnite.innerHTML = '<option value="">Unité mésure</option>';

                data.forEach(function (unite) {
                    let option = document.createElement('option');
                    option.value = unite.idUnite;
                    option.text = unite.libelleUnite + (unite.symbole ? ' (' + unite.symbole + ')' : '');
                    selectUnite.appendChild(option);
                });
            });
    });

    let currentCaracts = [];

    // Ajouter une caractéristique à l'infrastructure en cours
    $('#addCaractToInfra').on('click', function () {
        const type = $('#tyCaract').val();
        const caract = $('#caract').val();
        const unite = $('#unitCaract').val();
        const valeur = $('#caractValue').val();

        if (!caract || !unite || !valeur) {
            alert('Veuillez remplir tous les champs de caractéristique.');
            return;
        }

        const label = `${$('#caract option:selected').text()} : ${valeur} ${$('#unitCaract option:selected').text()}`;

        currentCaracts.push({
            id: caract,
            unite_id: unite,
            valeur: valeur,
            label: label
        });

        const item = `<li class="list-group-item">${label}</li>`;
        $('#caractList').append(item);

        // Reset
        $('#caractValue').val('');
    });

    // Ajouter une infrastructure au tableau final
    $('#addInfrastructureBtn').on('click', function () {
        const name = $('#infrastructureName').val();
        const famille = $('#FamilleInfrastruc').val();
        const familleText = $('#FamilleInfrastruc option:selected').text();

        if (!name || !famille || currentCaracts.length === 0) {
            alert("Veuillez remplir tous les champs de l'infrastructure.");
            return;
        }

        const row = `<tr>
            <td>${name}<input type="hidden" name="infra_names[]" value="${name}"></td>
            <td>${familleText}<input type="hidden" name="infra_familles[]" value="${famille}"></td>
            <td>
                ${currentCaracts.map(c => `<div>${c.label}<input type="hidden" name="caracts[${name}][]" value="${c.id}|${c.unite_id}|${c.valeur}"></div>`).join('')}
            </td>
            <td><button type="button" class="btn btn-danger btn-sm removeInfra">Supprimer</button></td>
        </tr>`;

        $('#tableInfrastructures').append(row);

        // Reset form
        $('#infrastructureName').val('');
        $('#FamilleInfrastruc').val('');
        $('#caractList').empty();
        currentCaracts = [];
    });

    // Supprimer une infrastructure
    $(document).on('click', '.removeInfra', function () {
        $(this).closest('tr').remove();
    });
  </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    ////////////////GENERALITE PROJET//////////////////////
    document.addEventListener("DOMContentLoaded", function () {
        let domaineSelect = document.getElementById("domaineSelect");
        let sousDomaineSelect = document.getElementById("sousDomaineSelect");

        domaineSelect.addEventListener("change", function () {
            let domaineCode = this.value;

            // Réinitialiser la liste des sous-domaines
            sousDomaineSelect.innerHTML = '<option value="">Sélectionner sous domaine</option>';

            if (domaineCode) {
                fetch(`{{ url('/') }}/get-sous-domaines/${domaineCode}`)
                .then(response => response.json())
                    .then(data => {
                        data.forEach(sousDomaine => {
                            let option = document.createElement("option");
                            option.value = sousDomaine.code_sous_domaine;
                            option.textContent = sousDomaine.lib_sous_domaine;
                            sousDomaineSelect.appendChild(option);
                        });
                        sousDomaineSelect.disabled = false;
                    })
                    .catch(error => console.error("Erreur lors du chargement des sous-domaines :", error));
            } else {
                sousDomaineSelect.disabled = true;
            }
        });
    });


    ///////////////INFORMATION / MAITRE OUVRAGE
    document.addEventListener("DOMContentLoaded", function () {
        // ✅ Vérification que seule UNE option (Public, Privé, Mixte) est sélectionnée
        const typeMOs = document.querySelectorAll('input[name="type_mo"]');
        typeMOs.forEach((checkbox) => {
            checkbox.addEventListener("change", function () {
                if (this.checked) {
                    typeMOs.forEach((cb) => {
                        if (cb !== this) cb.checked = false;
                    });
                }
            });
        });

        // ✅ Vérification avant de passer à l'étape Suivant <i class="fas fa-arrow-right"></i> e
        function validateStep3() {
            let typeSelected = false;
            typeMOs.forEach((checkbox) => {
                if (checkbox.checked) typeSelected = true;
            });

            if (!typeSelected) {
                alert("Veuillez sélectionner un type de Maître d’Ouvrage.");
                return false;
            }

            let acteur = document.getElementById("acteurSelect").value;
            let enCharge = document.getElementById("enChargeSelect").value;

            if (!acteur) {
                alert("Veuillez sélectionner un acteur responsable.");
                return false;
            }

            if (!enCharge) {
                alert("Veuillez définir la responsabilité du Maître d’Ouvrage.");
                return false;
            }

            return true;
        }
    });

    // ➕ Ajouter un maître d’œuvre
    $("#addMoeuvreBtn").on("click", function () {
        const selected = $("#acteurSelect option:selected");

        if (!selected.val()) {
            alert("Veuillez sélectionner un acteur.");
            return;
        }

        const codeActeur = selected.val();
        const libelleCourt = selected.data("libelle-court") || selected.text().split(" ")[0];
        const libelleLong = selected.data("libelle-long") || selected.text().split(" ").slice(1).join(" ");
        const secteur = $("#sectActivEnt option:selected").text();
        const secteurCode = $("#sectActivEnt").val();
        const tableBody = $("#moeuvreTable tbody");

        // Vérifie si l'acteur est déjà dans la liste
        if (tableBody.find(`input[value="${codeActeur}"]`).length > 0) {
            alert("Ce maître d’œuvre est déjà ajouté.");
            return;
        }

        const isMinistere = libelleCourt?.toLowerCase().includes("minist");

        const row = `
            <tr>
                <td>${libelleCourt}</td>
                <td>${libelleLong}</td>
                <td>${isMinistere ? secteur : "-"}</td>
                <td hidden>${isMinistere ? secteurCode : ""}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-moeuvre">
                        <i class="fas fa-trash"></i>
                    </button>
                    <input type="hidden" name="code_acteur_moeuvre[]" value="${codeActeur}">
                    <input type="hidden" name="secteur_id[]" value="${isMinistere ? secteurCode : ''}">
                </td>
            </tr>
        `;

        tableBody.append(row);
    });

    // 🗑️ Supprimer un maître d’œuvre
    $(document).on("click", ".remove-moeuvre", function () {
        $(this).closest("tr").remove();
    });


</script>
<script>
    function toggleType() {
        const publicRadio = document.getElementById('public'); // Checkbox "Public"
        const priveRadio = document.getElementById('prive');   // Checkbox "Privé"
        const optionsPrive = document.getElementById('optionsPrive'); // Section pour "Entreprise" ou "Individu"
        //const entrepriseFields = document.getElementById('entrepriseFields'); // Champs pour "Entreprise"
        const individuFields = document.getElementById('individuFields'); // Champs pour "Individu"
        const acteurSelect = document.getElementById('acteurSelect');

        // Si "Public" est sélectionné
        if (publicRadio.checked) {
            optionsPrive.classList.add('d-none'); // Cacher les options pour "Privé"
            /*entrepriseFields.classList.add('d-none'); // Cacher les champs "Entreprise"
            individuFields.classList.add('d-none'); */// Cacher les champs "Individu"
            fetchActeurs('Public');
        }
        // Si "Privé" est sélectionné
        else if (priveRadio.checked) {
            optionsPrive.classList.remove('d-none'); // Afficher les options pour "Entreprise" ou "Individu"
            acteurSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
            // Vérifier si une sous-option ("Entreprise" ou "Individu") est déjà sélectionnée
            const entrepriseRadio = document.getElementById('entreprise');
            const individuRadio = document.getElementById('individu');

            /*if (entrepriseRadio.checked) {
                // Si "Entreprise" est sélectionné, afficher ses champs et cacher ceux d'"Individu"
                entrepriseFields.classList.remove('d-none');
                individuFields.classList.add('d-none');
            } else if (individuRadio.checked) {
                // Si "Individu" est sélectionné, afficher ses champs et cacher ceux d'"Entreprise"
                individuFields.classList.remove('d-none');
                entrepriseFields.classList.add('d-none');
            } else {
                // Si aucune sous-option n'est encore sélectionnée, cacher les deux sections
                entrepriseFields.classList.add('d-none');
                individuFields.classList.add('d-none');
            }*/
        }else{
            optionsPrive.classList.add('d-none');
            acteurSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
        }
    }

    // Fonction pour basculer entre "Entreprise" et "Individu" lorsque "Privé" est sélectionné
    function togglePriveFields() {
        const entrepriseRadio = document.getElementById('entreprise'); // Radio "Entreprise"
        const individuRadio = document.getElementById('individu');     // Radio "Individu"
        //const entrepriseFields = document.getElementById('entrepriseFields'); // Champs "Entreprise"
        const individuFields = document.getElementById('individuFields'); // Champs "Individu"
        const acteurSelect = document.getElementById('acteurSelect');

        // Si "Entreprise" est sélectionné
        if (entrepriseRadio.checked) {
            fetchActeurs('Privé', 'Entreprise');
            /*entrepriseFields.classList.remove('d-none'); // Afficher les champs "Entreprise"
            individuFields.classList.add('d-none');*/ // Cacher les champs "Individu"
        }
        // Si "Individu" est sélectionné
        else if (individuRadio.checked) {
            fetchActeurs('Privé', 'Individu');
            /*individuFields.classList.remove('d-none'); // Afficher les champs "Individu"
            entrepriseFields.classList.add('d-none');*/ // Cacher les champs "Entreprise"
        }
    }
    // Fonction pour récupérer les acteurs via API
    function fetchActeurs(type_mo, priveType = null) {
        const acteurSelect = document.getElementById('acteurSelect'); // Select des acteurs
        let url = `{{ url("/") }}/get-acteurs?type_mo=${type_mo}`; // Construire l'URL API

        // Ajouter le sous-type (priveType) si présent
        if (priveType) {
            url += `&priveType=${priveType}`;
        }

        // Appeler l'API pour récupérer les acteurs
        fetch(url)
            .then(response => response.json())
            .then(data => {
                // Réinitialiser les options du select
                acteurSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';

                // Ajouter les options reçues
                data.forEach(acteur => {
                    const option = document.createElement('option');
                    option.value = acteur.code_acteur;
                    option.textContent = acteur.libelle_long;
                    acteurSelect.appendChild(option);
                });
            })
            .catch(error => console.error("Erreur lors du chargement des acteurs :", error));
    }
    // Ajout des écouteurs d'événements sur les éléments pour assurer le bon fonctionnement
    document.addEventListener("DOMContentLoaded", function () {
        // Écouter les changements sur les checkboxes "Public" et "Privé"
        document.getElementById('public').addEventListener('change', toggleType);
        document.getElementById('prive').addEventListener('change', toggleType);

        // Écouter les changements sur les radios "Entreprise" et "Individu"
        document.getElementById('entreprise').addEventListener('change', togglePriveFields);
        document.getElementById('individu').addEventListener('change', togglePriveFields);
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const typeSelectionInputs = document.querySelectorAll(".type_mo");

        const acteurSelect = document.getElementById("acteurSelect");

        typeSelectionInputs.forEach(input => {
            input.addEventListener("change", function() {
                const selectionType = this.value;

                fetch(`{{ url("/") }}/get-acteurs?type_selection=${selectionType}`)
                    .then(response => response.json())
                    .then(data => {
                        // Réinitialiser les options
                        acteurSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';

                        // Ajouter les nouvelles options
                        data.forEach(acteur => {
                            const option = document.createElement("option");
                            option.value = acteur.code_acteur;
                            option.textContent = acteur.libelle_long;
                            acteurSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error("Erreur lors du chargement des acteurs:", error));
            });
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
        const typeSelectionInputs = document.querySelectorAll(".type_ouvrage");

        const acteurMoeSelect = document.getElementById("acteurMoeSelect");

        typeSelectionInputs.forEach(input => {
            input.addEventListener("change", function() {
                const selectionType = this.value;

                fetch(`{{ url("/") }}/get-acteurs?type_selection=${selectionType}`)
                    .then(response => response.json())
                    .then(data => {
                        // Réinitialiser les options
                        acteurMoeSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';

                        // Ajouter les nouvelles options
                        data.forEach(acteur => {
                            const option = document.createElement("option");
                            option.value = acteur.code_acteur;
                            option.textContent = acteur.libelle_long;
                            acteurMoeSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error("Erreur lors du chargement des acteurs:", error));
            });
        });
    });
    ////////////////MAITRE D'OUVRAGE
    document.addEventListener("DOMContentLoaded", function () {
        // Empêcher la sélection de plusieurs options pour type_ouvrage
        const type_ouvrages = document.querySelectorAll('input[name="type_ouvrage"]');
        type_ouvrages.forEach((checkbox) => {
            checkbox.addEventListener("change", function () {
                if (this.checked) {
                    type_ouvrages.forEach((cb) => {
                        if (cb !== this) cb.checked = false;
                    });
                }
            });
        });

        // Gestion du Maître d’Ouvrage
        function toggleTypeMoe() {
            const publicRadio = document.getElementById('moePublic');
            const priveRadio = document.getElementById('moePrive');
            const optionsMoePrive = document.getElementById('optionsMoePrive');
            //const moeEntrepriseFields = document.getElementById('moeEntrepriseFields');
            const individuFields = document.getElementById('moeIndividuFields');
            const acteurMoeSelect = document.getElementById('acteurMoeSelect');

            if (publicRadio.checked) {
                optionsMoePrive.classList.add('d-none');
                /*moeEntrepriseFields.classList.add('d-none');
                individuFields.classList.add('d-none');*/
                fetchMoeActeurs('Public');
            } else if (priveRadio.checked) {
                optionsMoePrive.classList.remove('d-none');
                acteurMoeSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';

                const entrepriseRadio = document.getElementById('moeEntreprise');
                const individuRadio = document.getElementById('moeIndividu');

                /*if (entrepriseRadio.checked) {
                    moeEntrepriseFields.classList.remove('d-none');
                    individuFields.classList.add('d-none');
                } else if (individuRadio.checked) {
                    individuFields.classList.remove('d-none');
                    moeEntrepriseFields.classList.add('d-none');
                } else {
                    moeEntrepriseFields.classList.add('d-none');
                    individuFields.classList.add('d-none');
                }*/
            } else {
                optionsMoePrive.classList.add('d-none');
                acteurMoeSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
            }
        }

        function toggleMoeFields() {
            const entrepriseRadio = document.getElementById('moeEntreprise');
            const individuRadio = document.getElementById('moeIndividu');
            //const moeEntrepriseFields = document.getElementById('moeEntrepriseFields');
            const individuFields = document.getElementById('moeIndividuFields');
            const typeOuvrage = document.querySelector('input[name="type_ouvrage"]:checked')?.value;

            if (entrepriseRadio.checked) {
                fetchMoeActeurs(typeOuvrage, 'Entreprise');
                /*moeEntrepriseFields.classList.remove('d-none');
                individuFields.classList.add('d-none');*/
            } else if (individuRadio.checked) {
                fetchMoeActeurs(typeOuvrage, 'Individu');
                /*individuFields.classList.remove('d-none');
                moeEntrepriseFields.classList.add('d-none');*/
            }
        }

        function fetchMoeActeurs(typeOuvrage, priveType = null) {
            const acteurMoeSelect = document.getElementById('acteurMoeSelect');
            let url = `{{ url("/") }}/get-acteurs?type_ouvrage=${encodeURIComponent(typeOuvrage)}`;

            if (priveType) {
                url += `&priveMoeType=${encodeURIComponent(priveType)}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    acteurMoeSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
                    data.forEach(acteur => {
                        const option = document.createElement('option');
                        option.value = acteur.code_acteur;
                        option.textContent = acteur.libelle_long;
                        acteurMoeSelect.appendChild(option);
                    });
                })
                .catch(error => console.error("Erreur lors du chargement des acteurs :", error));
        }

        document.getElementById('moePublic').addEventListener('change', toggleTypeMoe);
        document.getElementById('moePrive').addEventListener('change', toggleTypeMoe);
        document.getElementById('moeEntreprise').addEventListener('change', toggleMoeFields);
        document.getElementById('moeIndividu').addEventListener('change', toggleMoeFields);
    });

    $("#addMoeBtn").on("click", function () {
        const selected = $("#acteurMoeSelect option:selected");

        if (!selected.val()) {
            alert("Veuillez sélectionner un acteur.");
            return;
        }

        const codeActeur = selected.val();
        const libelleCourt = selected.data("libelle-court") || selected.text().split(" ")[0];
        const libelleLong = selected.data("libelle-long") || selected.text().split(" ").slice(1).join(" ");

        const secteur = $("#sectActivEntMoe option:selected").text();
        const secteurCode = $("#sectActivEntMoe").val();

        const tableBody = $("#moeTable tbody");

        // S'assurer qu’il n’y a qu’un seul maître d’ouvrage
        tableBody.empty();
        const isMinistere = libelleCourt?.toLowerCase().includes("minist");
        const row = `
            <tr>
                <td>${libelleCourt}</td>
                <td>${libelleLong}</td>
                <td>${isMinistere ? secteur : "-"}</td>
                 
                <td hidden>${isMinistere ? secteurCode : ""}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-moe">
                        <i class="fas fa-trash"></i>
                    </button>
                    <input type="hidden" name="code_acteur_moe" value="${codeActeur}">
                </td>
            </tr>
        `;
        tableBody.append(row);
    });

    // Suppression de la ligne
    $(document).on("click", ".remove-moe", function () {
        $(this).closest("tr").remove();
    });


////////////////INFRASTRUCTURES
$(document).ready(function () {
    const paysCode = $("#paysSelect").val();

    if (paysCode) {
        $.ajax({
            url: '{{ url("/") }}/get-localites/' + paysCode,
            type: "GET",
            success: function (data) {
                const $localite = $("#localite");
                $localite.empty().append('<option value="">Sélectionnez une localité</option>');
                $.each(data, function (index, localite) {
                    $localite.append(
                        `<option 
                            value="${localite.id}" 
                            data-code-pays="${localite.id_pays}" 
                            data-code-rattachement="${localite.code_rattachement}">
                            ${localite.libelle}
                        </option>`
                    );
                });

                // Force la mise à jour du lookup-select après ajout dynamique
                const lookup = document.getElementById("localite");
                if (lookup) lookup.loadOptionsFromDOM?.();
            }
        });
    }

    // Pré-sélection "localité"
    $("#age").prop("checked", true);
    afficheSelect('localite');
});

// Toggle formulaire de bénéficiaire
document.addEventListener("DOMContentLoaded", function () {
    const beneficiaireBtn = document.getElementById("toggleBeneficiaire");
    const infrastructureField = document.getElementById("infrastructureField");

    if (beneficiaireBtn && infrastructureField) {
        beneficiaireBtn.addEventListener("click", function (event) {
            event.preventDefault();
            infrastructureField.classList.toggle("d-none");
        });
    }
});

function afficheSelect(selectId) {
    $('#localite, #infrastructure, #acteur').hide();
    $('#' + selectId).show();
}

let actionCounter = 1;

const typeToSelectId = {
    localite: "localite",
    acteur: "acteur",
    infrastructure: "infrastructure"
};

// 🔹 Ajouter un bénéficiaire
$("#addBtnBene").on("click", function () {
    const selectedType = $("input[name='beneficiaire_type[]']:checked").val();
    const selectId = typeToSelectId[selectedType];
    const selectedLookup = document.getElementById(selectId);
    const selectedOption = selectedLookup?.getSelected();

    if (selectedOption && selectedOption.value) {
        const code = selectedOption.value;
        const libelle = selectedOption.text;
        const type = selectId;

        const row = `
            <tr>
                <td><input type="checkbox"></td>
                <td>${code}</td>
                <td>${libelle}</td>
                <td>${type}</td>
            </tr>
        `;
        $("#beneficiaireTable tbody").append(row);
    } else {
        alert("Veuillez sélectionner un bénéficiaire.");
    }
});

// 🔹 Supprimer bénéficiaires sélectionnés
$("#deleteBtn").on("click", function () {
    $("#beneficiaireTable tbody input[type='checkbox']:checked").closest("tr").remove();
});

// 🔹 Ajouter une action avec ses bénéficiaires
$("#addAction").on("click", function () {
    const action = $("#action option:selected");
    const quantite = $("#quantite").val();
    const infrastructureLookup = document.getElementById("insfrastructureSelect");
    const infrastructureOption = infrastructureLookup?.getSelected();

    if (!action.val() || !quantite || !infrastructureOption || !infrastructureOption.value) {
        alert("Veuillez remplir tous les champs pour l'action.");
        return;
    }

    const infrastructureLibelle = infrastructureOption.text;
    const infrastructureCode = infrastructureOption.value;

    const $beneficiaires = $("#beneficiaireTable tbody tr");

    if ($beneficiaires.length === 0) {
        alert("Veuillez ajouter au moins un bénéficiaire.");
        return;
    }

    $beneficiaires.each(function () {
        const codeB = $(this).find("td:eq(1)").text();
        const libelleB = $(this).find("td:eq(2)").text();
        const typeB = $(this).find("td:eq(3)").text();

        let extraData = {};

        if (typeB === "localite") {
            const localiteSelect = document.getElementById("localite");
            const selected = localiteSelect?.getSelected();
            extraData.codePays = selected?.codePays || "";
            extraData.codeRattachement = selected?.codeRattachement || "";
        } else {
            extraData.codePays = "";
            extraData.codeRattachement = "";
        }

        const newRow = `
            <tr>
                <td>${actionCounter}</td>
                <td>${action.text()}</td>
                <td>${quantite}</td>
                <td>${infrastructureLibelle}</td>
                <td hidden>${infrastructureCode}</td>
                <td>${libelleB}</td>
                <td hidden>${codeB}</td>
                <td>${typeB}</td>
                <td hidden>${extraData.codePays}</td>
                <td hidden>${extraData.codeRattachement}</td>
                <td>
                    <button class="btn btn-danger btn-sm delete-row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $("#beneficiaire-table-body").append(newRow);
    });


    actionCounter++;
    $("#nordre").val(actionCounter);

    // Nettoyage
    $("#quantite").val('');
    $("#action").val('');
    infrastructureLookup.value = null;
    infrastructureLookup.shadowRoot.querySelector("input").value = '';
    $("#beneficiaireTable tbody").empty();
    $("#infrastructureField").addClass("d-none");
});

// 🔹 Suppression ligne dans tableau final
$(document).on("click", ".delete-row", function () {
    $(this).closest("tr").remove();
});

</script>
<script>
    // Configuration
    const MAX_FILE_SIZE = 100 * 1024 * 1024; // 100MB
    const MAX_TOTAL_SIZE = 500 * 1024 * 1024; // 500MB
    const ALLOWED_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png',
        'application/zip',
        'application/x-rar-compressed',
        'application/x-dwg',
        'application/x-dxf'
    ];

    // Variables globales
    let filesToUpload = [];

    // Événements
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileUpload');
        const browseBtn = document.getElementById('browseFilesBtn');
        
        // Gestion du clic sur le bouton "Parcourir"
        browseBtn.addEventListener('click', function() {
            fileInput.click();
        });
        
        // Gestion de la sélection de fichiers
        fileInput.addEventListener('change', handleFileSelect);
        
        // Gestion du drag and drop
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect({ target: fileInput });
            }
        });
        
        // Gestion du bouton de soumission
        document.getElementById('submitDocumentsBtn').addEventListener('click', uploadFiles);
    });

    function handleFileSelect(event) {
        const files = Array.from(event.target.files);
        let totalSize = 0;
        
        // Vérification des fichiers
        for (const file of files) {
            // Vérification du type
            if (!ALLOWED_TYPES.includes(file.type)) {
                alert(`Le type de fichier "${file.name}" n'est pas autorisé.`);
                return;
            }
            
            // Vérification de la taille
            if (file.size > MAX_FILE_SIZE) {
                alert(`Le fichier "${file.name}" dépasse la taille maximale de 100MB.`);
                return;
            }
            
            totalSize += file.size;
        }
        
        // Vérification de la taille totale
        if (totalSize > MAX_TOTAL_SIZE) {
            alert(`La taille totale des fichiers (${formatFileSize(totalSize)}) dépasse la limite de 500MB.`);
            return;
        }
        
        // Ajout des fichiers à la liste
        filesToUpload = filesToUpload.concat(files);
        updateFileList();
    }

    function updateFileList() {
        const container = document.getElementById('filesContainer');
        const fileCount = document.getElementById('fileCount');
        const totalSize = document.getElementById('totalSize');
        const submitBtn = document.getElementById('submitDocumentsBtn');
        
        // Calcul de la taille totale
        let totalSizeBytes = 0;
        
        // Vide le conteneur
        container.innerHTML = '';
        
        // Ajoute chaque fichier
        filesToUpload.forEach((file, index) => {
            totalSizeBytes += file.size;
            
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <div class="file-icon">
                    <i class="fas ${getFileIcon(file.type)}"></i>
                </div>
                <div class="file-info">
                    <div class="file-name">${file.name}</div>
                    <div class="file-size">${formatFileSize(file.size)}</div>
                </div>
                <div class="file-remove" onclick="removeFile(${index})">
                    <i class="fas fa-times"></i>
                </div>
            `;
            
            container.appendChild(fileItem);
        });
        
        // Met à jour les informations globales
        fileCount.textContent = filesToUpload.length;
        totalSize.textContent = formatFileSize(totalSizeBytes);
        
        // Active/désactive le bouton de soumission
        submitBtn.disabled = filesToUpload.length === 0;
    }

    function removeFile(index) {
        filesToUpload.splice(index, 1);
        updateFileList();
    }

    function getFileIcon(fileType) {
        const icons = {
            'application/pdf': 'fa-file-pdf',
            'image/': 'fa-file-image',
            'application/msword': 'fa-file-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'fa-file-word',
            'application/vnd.ms-excel': 'fa-file-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'fa-file-excel',
            'application/zip': 'fa-file-archive',
            'application/x-rar-compressed': 'fa-file-archive',
            'application/x-dwg': 'fa-file-alt',
            'application/x-dxf': 'fa-file-alt'
        };
        
        for (const [key, icon] of Object.entries(icons)) {
            if (fileType.includes(key.replace('*', ''))) {
                return icon;
            }
        }
        
        return 'fa-file';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    async function uploadFiles() {
    if (filesToUpload.length === 0) {
        showErrorAlert('Aucun fichier à uploader.');
        return;
    }

    const progressStatus = document.getElementById('uploadStatus');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressPercent = document.getElementById('uploadPercent');
    const progressContainer = document.getElementById('uploadProgressContainer');
    const submitBtn = document.getElementById('submitDocumentsBtn');

    // Init UI
    progressContainer.style.display = 'block';
    progressStatus.textContent = "Préparation de l'envoi...";
    progressBar.style.width = '0%';
    progressPercent.textContent = '0%';
    progressBar.classList.remove('bg-danger', 'bg-success');
    submitBtn.disabled = true;

    try {
        const codeProjet = localStorage.getItem('code_projet_temp');
        if (!codeProjet) throw new Error("Aucun projet sélectionné. Veuillez revenir à l'étape 1.");

        const formData = new FormData();
        formData.append('code_projet', codeProjet);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        filesToUpload.forEach(file => formData.append('fichiers[]', file));

        const response = await fetch('{{ route("projets.temp.save.step7") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json' // Important pour bien recevoir du JSON même en cas d’erreur Laravel
            }
        });

        const data = await response.json();

        if (response.ok && data.success) {
            progressBar.classList.add('bg-success');
            progressBar.style.width = '100%';
            progressStatus.textContent = 'Upload terminé avec succès!';
            progressPercent.textContent = '100%';

            setTimeout(() => {
                finaliserCodeProjet();
                window.location.href = '{{ route("project.create") }}';
            }, 1500);
        } else {
            throw new Error(data.message || 'Erreur serveur');
        }

    } catch (error) {
        progressBar.classList.add('bg-danger');
        progressStatus.textContent = 'Erreur: ' + error.message;
        showErrorAlert(
            "Échec de l'upload : " + error.message +
            "\n\nVérifie :\n- Taille et type des fichiers\n- Ta connexion Internet\n- Que le projet est bien sélectionné"
        );
        submitBtn.disabled = false;
    }
}



    function handleUploadError(error, progressStatus, progressBar, submitBtn) {
        console.error("Erreur lors de l'upload:", error);
        
        // Mise à jour de l'UI
        progressStatus.textContent = 'Erreur: ' + error.message;
        progressBar.classList.remove('progress-bar-animated');
        progressBar.classList.add('bg-danger');
        submitBtn.disabled = false;
        
        // Affichage de l'erreur à l'utilisateur
        showErrorAlert(
            "Échec de l'upload: " + error.message + 
            "\n\nVeuillez vérifier :" +
            "\n- La taille des fichiers (max 100MB par fichier, 500MB total)" +
            "\n- Le type des fichiers (PDF, Word, Excel, images, etc.)" +
            "\n- Votre connexion internet"
        );
    }

    function showErrorAlert(message) {
        // Utilisation de SweetAlert si disponible, sinon alert() natif
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Erreur',
                text: message,
                icon: 'error',
                confirmButtonText: 'OK',
                customClass: {
                    container: 'swal2-container-error'
                }
            });
        } else {
            alert(message);
        }
    }

    function finaliserCodeProjet() {
        const codeTemp = localStorage.getItem('code_projet_temp');
        const codeLocalisation = localStorage.getItem('code_localisation');
        const typeFinancement = localStorage.getItem('type_financement');

        if (!codeTemp || !codeLocalisation || !typeFinancement) {
            alert("Des informations manquent pour finaliser le projet.");
            return;
        }

        fetch('{{ route("projets.finaliser") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json', // ✅ pour forcer JSON même en cas d'erreur Laravel
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                code_projet_temp: codeTemp,
                code_localisation: codeLocalisation,
                type_financement: typeFinancement
            })
        })
        .then(async (response) => {
            const text = await response.text();

            try {
                const data = JSON.parse(text);

                if (response.ok) {
                    // ✅ Réponse OK
                    if (data.success) {
                        localStorage.removeItem('code_projet_temp');
                        localStorage.removeItem('type_financement');
                        localStorage.removeItem('code_localisation');
                        alert(data.message || "Projet finalisé avec succès !");
                        console.log("Code projet final :", data.code_projet_final);
                    } else {
                        alert(data.message || "Finalisation échouée.");
                    }
                } else {
                    // ❌ Laravel a répondu avec une erreur 422, 500, etc.
                    console.error("Erreur Laravel :", data);
                    alert(data.message || "Erreur serveur lors de la finalisation.");
                }
            } catch (e) {
                // 💥 Laravel a peut-être renvoyé du HTML (vue Blade)
                console.error("Réponse non JSON :", text);
                alert("Erreur inattendue. Le serveur a retourné une réponse non valide.");
            }
        })
        .catch(error => {
            console.error('Erreur réseau ou serveur lors de la finalisation :', error);
            alert("Une erreur est survenue lors de la finalisation du projet.");
        });
    }

</script>
@endsection
