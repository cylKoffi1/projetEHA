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
        .carac-item {
            display: flex;
            gap: 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .carac-label {
            min-width: 130px; /* Ajuste selon tes libellés */
            font-weight: 500;
            text-align: right;
        }

        .carac-separator {
            margin-right: 4px;
        }

        .carac-value {
            flex: 1;
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

                            @include('etudes_projets.steps.Information_Generales')
                            @include('etudes_projets.steps.Infrastructure')
                            @include('etudes_projets.steps.actionAMener')
                            @include('etudes_projets.steps.maitreOuvrages')


                            


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
                                        <label>Local *</label><br>
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
                                        <lookup-select name="bailleur" id="bailleur" placeholder="Sélectionner un bailleur">
                                            <option value="">Sélectionner le bailleur</option>
                                        </lookup-select>
                                    </div>
                                    <div class="col d-none" id="chargeDeContainer">
                                        <label for="chargeDe">En charge de :</label>
                                        <select name="chargeDe" id="chargeDe" class="form-control">
                                            @foreach ($SecteurActivites as $SecteurActivite)
                                                <option value="{{ $SecteurActivite->id }}">{{ $SecteurActivite->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label for="montant">Montant</label>
                                        <input type="text" id="montant" class="form-control" placeholder="Montant" oninput="formatNumber(this)">
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
                                                <th>En charge de</th>
                                                <th>Montant</th>
                                                <th>Devise</th>
                                                <th>Local</th>
                                                <th>Type de financement</th>
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
                                    const Financement = document.getElementById('typeFinancement');

                                    if (!selected || !selected.value) {
                                        alert("Veuillez sélectionner un bailleur.", 'warning');
                                        return;
                                    }
                                    if (!Financement.value) {
                                        alert("Veuillez sélectionner le type de financement.", 'warning');
                                        return;
                                    }
                                    
                                    const typeFinancement = Financement.value;
                                    const typeFinancementText = Financement.selectedOptions[0]?.textContent ?? ''
                                    const bailleurText = selected.text;
                                    const bailleurValue = selected.value;
                                    const montant = document.getElementById('montant').value;
                                    const devise = document.getElementById('deviseBailleur').value;
                                    const commentaire = document.getElementById('commentaire').value;
                                    
                                    const selectElement = document.getElementById('chargeDe');
                                    const enChargeDeValue = selectElement.value;
                                    const enChargeDeText = selectElement.selectedOptions[0]?.textContent ?? '';

                                    const localRadio = document.querySelector('input[name="BaillOui"]:checked');
                                    const localValue = localRadio ? localRadio.value : '';

                                    if (!montant ) {
                                        alert("Veuillez saisir le montant", "warning");
                                        return;
                                    }

                                    const newRow = `
                                        <tr>
                                            <td>
                                                ${bailleurText}
                                                <input type="hidden" name="financements[${financementIndex}][bailleur]" value="${bailleurValue}">
                                            </td>
                                            <td >
                                                ${enChargeDeText}
                                                <input type="hidden" name="financements[${financementIndex}][chargeDe]" value="${enChargeDeValue}">
                                            </td>
                                            <td oninput="formatNumber(this)" class="text-end">
                                                ${montant}
                                                <input type="hidden" name="financements[${financementIndex}][montant]" value="${montant}" >
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
                                                ${typeFinancementText}
                                                <input type="hidden" name="financements[${financementIndex}][typeFinancement]" value="${typeFinancement}">
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
                                    document.getElementById('chargeDe').value = '';

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
                                        const montant = parseFloat(row.querySelector('input[name$="[montant]"]').value.replace(/\s/g, '') || 0);
                                        const enChargeDe = row.querySelector('input[name$="[chargeDe]"]').value ?? null;
                                        const devise = row.querySelector('input[name$="[devise]"]').value;
                                        const local = row.querySelector('input[name$="[local]"]').value;
                                        const commentaire = row.querySelector('input[name$="[commentaire]"]').value;
                                        const typeFinancement = row.querySelector('input[name$="[typeFinancement]"]').value;
                                        financements.push({ bailleur,  montant, enChargeDe, devise, local, commentaire });
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
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
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
        const acteurSelect2 = document.getElementById('acteurSelect');
        const secteurActiviteContainer2 = document.getElementById('sectActivEnt').parentElement;
        
        if ( !acteurSelect2 || !secteurActiviteContainer2 ) {
            console.error("Les éléments HTML avec les identifiants 'acteurMoeSelect' ou 'sectActivEntMoe' n'ont pas été trouvés.");
            return;
        }
        
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
        
        if (acteurSelect2.value === '5689') {
            secteurActiviteContainer2.style.display = 'block';
        } else {
            secteurActiviteContainer2.style.display = 'none';
        }
    });
</script>
<script>
    let currentStep = 1;
    const totalSteps = 7;
    let uploadedFiles = [];
    let infrastructuresAction = [];

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
    }

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




</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>



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
   


</script>

<!------------------------Action à mener ------------------------------->

<!------------------------FIN Action à mener ------------------------------->
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="BaillOui"]');
    const bailleurLookup = document.getElementById('bailleur');
    const chargeDe = document.getElementById('chargeDeContainer');
    const paysCode = document.getElementById('paysSelect')?.value ?? '';


    // ✅ Fonction pour gérer l'affichage du champ "En charge de"
    function handleChargeDeDisplay() {
        const selected = bailleurLookup.getSelected?.();

        const codeActeur = selected?.value;
        const codePays = selected?.codePays;


        if (codeActeur === '5689') {
            console.log('✅ Condition remplie : affichage de "En charge de"');
            chargeDe.classList.remove('d-none');
        } else {
            console.log('🚫 Condition non remplie : on cache "En charge de"');
            chargeDe.classList.add('d-none');
        }
    }

    // 📌 Écouteur prêt du composant custom
    if (bailleurLookup) {
        // Quand le lookup est prêt, on branche l’écouteur "change"
        bailleurLookup.addEventListener('ready', () => {
            bailleurLookup.addEventListener('change', handleChargeDeDisplay);
        });

        // fallback si jamais ready est déjà passé
        if (bailleurLookup.getSelected) {
            bailleurLookup.addEventListener('change', handleChargeDeDisplay);
        }
    }

    // 📡 Chargement dynamique des bailleurs selon le bouton radio (local ou non)
    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            const local = this.value;

            fetch(`{{ url('/') }}/get-bailleurs?local=${local}`)
                .then(res => res.json())
                .then(data => {
                    const options = data.map(acteur => ({
                        value: acteur.code_acteur.toString(), // toujours en string
                        text: `${acteur.libelle_court || ''} ${acteur.libelle_long || ''}`.trim(),
                        codePays: acteur.code_pays // 🔥 obligatoire pour la condition
                    }));

                    bailleurLookup.setOptions?.(options);


                   
                    bailleurLookup.clear?.(); // réinitialise la sélection
                    chargeDe.classList.add('d-none');
                })
                .catch(err => {
                    console.error('[ERROR] Chargement des bailleurs échoué :', err);
                });
        });
    });
});
</script>
@endsection
