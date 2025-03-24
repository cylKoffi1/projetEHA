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
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary " onclick="nextStep()">Suivant</button>
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
                                            alert(response.message || "Étape 4 sauvegardée.");
                                            if (typeof callback === "function") callback();
                                        },
                                        error: function(xhr) {
                                            alert("Erreur lors de la sauvegarde !");
                                            console.error(xhr.responseText);
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
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary " onclick="nextStep()">Suivant</button>
                                    </div>
                                </div>

                            </div>
                            <!--Sauvegarde temporaire -->
                            <script>
                                function saveStep5(callback = null) {
                                    const codeProjet = localStorage.getItem("code_projet_temp");
                                    const row = $("#moeuvreTable tbody tr:first");

                                    if (!codeProjet || row.length === 0) {
                                        alert("Veuillez ajouter un maître d’œuvre.");
                                        return;
                                    }

                                    const codeActeur = row.find('input[name="code_acteur_moeuvre"]').val();
                                    const secteurCode = row.find("td:eq(3)").text() || null;

                                    $.ajax({
                                        url: '{{ route("projets.temp.save.step5") }}',
                                        method: 'POST',
                                        data: {
                                            _token: '{{ csrf_token() }}',
                                            code_projet: codeProjet,
                                            code_acteur: codeActeur,
                                            secteur_id: secteurCode
                                        },
                                        success: function (response) {
                                            alert(response.message || "Étape 5 sauvegardée.");
                                            if (typeof callback === "function") callback();
                                        },
                                        error: function (xhr) {
                                            alert("Erreur lors de l’enregistrement du maître d’œuvre.");
                                            console.error(xhr.responseText);
                                        }
                                    });
                                }
                            </script>

                            <!-- 🔵 Étape : Financement -->
                            <div class="step" id="step-6">
                                <h5 class="text-secondary">💰 Ressources Financières</h5>

                                <div class="col-2 mb-3">
                                    <label for="typeFinancement">Type de financement</label>
                                    <select id="typeFinancement" class="form-control">
                                        <option value="public">Public</option>
                                        <option value="privé">Privé</option>
                                        <option value="mixte">Mixte</option>
                                    </select>
                                </div>

                                <!-- Formulaire pour ajouter des détails financiers -->
                                <div class="row">
                                    <div class="col-1">
                                        <label>Local</label>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" id="BailOui" name="BaillOui" value="BaillOui" class="form-check-input">
                                            <label for="BailOui" class="form-check-label">Oui</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" id="BailNon" name="BaillOui" value="BaillNon" class="form-check-input">
                                            <label for="BailNon" class="form-check-label">Non</label>
                                        </div>
                                    </div>

                                    <div class="col">
                                        <label for="bailleur">Bailleur</label>
                                        <input type="text" id="bailleur" class="form-control" placeholder="Rechercher un bailleur...">
                                        <ul class="list-group position-absolute w-100 d-none" id="bailleurList" style="z-index: 1000;"></ul>
                                    </div>
                                    <!-- Bouton pour Ajouter un Nouveau Bailleur -->
                                    <div id="ajouterBailleurContainer" class="mt-2 d-none">
                                        <li class="list-group-item text-primary list-group-item-action" id="ajouterBailleurBtn">
                                            <i class="fas fa-plus-circle"></i> Ajouter "<span id="nouveauBailleurNom"></span>"
                                        </li>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="montant">Montant</label>
                                        <input type="number" id="montant" name="MontantBailleur" class="form-control" placeholder="Montant">
                                    </div>
                                    <div class="col-md-1">
                                        <label for="deviseBailleur">Devise</label>
                                        <input type="text" name="deviseBailleur" id="deviseBailleur" class="form-control" placeholder="Devise" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="commentaire">Commentaire</label>
                                        <input type="text" id="commentaire" name="CommentBailleur" class="form-control" placeholder="Commentaire">
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-secondary" id="addFinancementBtn">Ajouter</button>
                                    </div>
                                </div>

                                <!-- Tableau des ressources financières -->
                                <div class="mt-4">
                                    <table class="table table-bordered">
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
                                        <tbody id="tableFinancements">
                                            <!-- Les lignes seront ajoutées ici dynamiquement -->
                                        </tbody>
                                    </table>
                                </div>
                                <!-- MODAL D'AJOUT DE BAILLEUR -->
                                <div class="modal fade" id="modalAjoutBailleur" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Ajouter un Nouveau Bailleur</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Sélection du type de bailleur -->
                                                <div class="mb-3">
                                                    <label>Type de Bailleur *</label>
                                                    <select id="typeBailleur" class="form-control">
                                                        <option value="">Sélectionnez...</option>
                                                        <option value="morale">Personne Morale (Entreprise, Organisation)</option>
                                                        <option value="physique">Personne Physique (Individu)</option>
                                                    </select>
                                                </div>

                                                <!-- FORMULAIRE PERSONNE MORALE -->
                                                <div id="bailleurMoraleFields" class="d-none">
                                                    <h6 class="text-primary">Informations de l'Entreprise / Organisation</h6>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label>Raison Sociale *</label>
                                                            <input type="text" id="raisonSocialeBail" class="form-control" placeholder="Nom de l'entreprise">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Date de Création *</label>
                                                            <input type="date" id="dateCreationBail" class="form-control">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Secteur d'Activité *</label>
                                                            <select id="secteurActiviteBail" class="form-control">
                                                                <option value="">Sélectionnez...</option>
                                                                <option value="Finance">Finance</option>
                                                                <option value="Infrastructure">Infrastructure</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Forme Juridique *</label>
                                                            <select id="formeJuridiqueBail" class="form-control">
                                                                <option value="">Sélectionnez...</option>
                                                                <option value="SARL">SARL</option>
                                                                <option value="SA">SA</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Numéro d’Immatriculation *</label>
                                                            <input type="text" id="numImmatBail" class="form-control" placeholder="RCCM">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Numéro d’Identification Fiscale (NIF) *</label>
                                                            <input type="text" id="nifBail" class="form-control" placeholder="NIF">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Capital Social</label>
                                                            <input type="number" id="capitalBail" class="form-control" placeholder="Montant">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Adresse Siège</label>
                                                            <input type="text" id="adresseBail" class="form-control" placeholder="Adresse du siège">
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- FORMULAIRE PERSONNE PHYSIQUE -->
                                                <div id="bailleurPhysiqueFields" class="d-none">
                                                    <h6 class="text-primary">Informations du Bailleur (Individu)</h6>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label>Nom *</label>
                                                            <input type="text" id="nomBail" class="form-control" placeholder="Nom">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Prénom *</label>
                                                            <input type="text" id="prenomBail" class="form-control" placeholder="Prénom">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Date de Naissance *</label>
                                                            <input type="date" id="dateNaissanceBail" class="form-control">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Nationalité *</label>
                                                            <select id="nationaliteBail" class="form-control">
                                                                <option value="">Sélectionnez...</option>
                                                                <option value="CIV">Ivoirienne</option>
                                                                <option value="FRA">Française</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Email *</label>
                                                            <input type="email" id="emailBail" class="form-control" placeholder="Email">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Téléphone *</label>
                                                            <input type="text" id="telephoneBail" class="form-control" placeholder="Téléphone">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="button" class="btn btn-primary" id="btnEnregistrerBailleur">Enregistrer</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
                                    </div>
                                </div>
                            </div>


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
                                    <div class="col-md-6">
                                        <label>Coût du projet</label>
                                        <input type="text" name="coutProjet" id="coutProjet" class="form-control text-end" oninput="formatNumber(this)">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Devise du coût</label>
                                        <select name="deviseCout" id="deviseCout" class="form-control">
                                            @foreach ($deviseCouts as $deviseCout)
                                                <option value="{{ $deviseCout?->code }}">{{ $deviseCout?->code_long }}: {{ $deviseCout?->libelle }}</option>
                                            @endforeach
                                        </select>
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
                                        <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
                                    </div>
                                </div>
                            </div>
                            <!--Sauvegarde temporaire -->
                            <script>
                                function saveStep1() {
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
                                        code_pays: '{{ $codePays ?? auth()->user()->code_pays }}', // ajustable selon ton contexte
                                    };

                                    $.ajax({
                                        url: '{{ route("projets.temp.save.step1") }}',
                                        method: 'POST',
                                        data: data,
                                        success: function(response) {
                                            if (response.success) {
                                                console.log("Step 1 sauvegardé temporairement.");
                                                localStorage.setItem("code_projet_temp", response.code_projet);
                                                if (typeof callback === 'function') callback();
                                            }
                                        },
                                        error: function(xhr) {
                                            console.warn("Erreur, mais on ne bloque pas l'utilisateur.");
                                            if (typeof callback === 'function') callback(); // continue quand même
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

                                    <!-- Infrastructures Tab -->
                                    <div class="tab-pane fade" id="infrastructures" role="tabpanel">
                                        <h5 class="text-secondary">🏗️ Infrastructures</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Famille d'Infrastructure *</label>
                                                <select class="form-control" id="infrastructureType" >
                                                    <option value="">Sélectionnez </option>
                                                   
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label>Infrastructure *</label>
                                                <input type="text" class="form-control" name="typeCaracteristique" id="typeCaracteristique">
                                                    
                                            </div>
                                            
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label>Type de caractéristique</label>
                                                <select class="form-control" name="tyCaract" id="tyCaract">
                                                    <option value="">Sélectionner le type </option>
                                                    @foreach ($TypeCaracteristiques as $TypeCaracteristique)
                                                        <option value="{{ $TypeCaracteristique->idTypeCaracteristique }}">{{ $TypeCaracteristique->libelleTypeCaracteristique }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Caractéristique</label>
                                                <select class="form-control" name="caract" id="caract">
                                                    <option value="">Sélectionner la caractéristique</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Unité</label>
                                                <select class="form-control" name="unitCaract" id="unitCaract">
                                                    <option value="">Sélectionner l'unité</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label>Valeur</label>
                                                <input type="text" class="form-control" name="caractValues[]">
                                            </div>
                                        </div>
                                        <br>
                                        <div class="text-end">
                                            <button type="button" class="btn btn-secondary" id="addInfrastructureBtn">Ajouter</button>
                                        </div>
                                        <br>
                                        <div class="row">
                                            <div class="col">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Type</th>
                                                            <th>Nom</th>
                                                            <th>Localisation</th>
                                                            <th>Statut</th>
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
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
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
                                                id: cols.eq(0).text(),
                                                libelle: cols.eq(1).text(),
                                                niveau: cols.eq(2).text(),
                                                decoupage: cols.eq(3).text()
                                            });
                                        }
                                    });

                                    // 🔁 Lecture des infrastructures + caractéristiques
                                    const infrastructures = [];
                                    $("#tableInfrastructures tr").each(function () {
                                        const type = $(this).find('input[name="typesCaracteristiques[]"]').val();
                                        const caract = $(this).find('input[name="caracteristiques[]"]').val();
                                        const unite = $(this).find('input[name="unites[]"]').val();
                                        const valeur = $(this).find('input[name="valeursCaracteristiques[]"]').val();

                                        infrastructures.push({
                                            localisation_id: null, // à remplir si tu lies à une localité précise
                                            statut: 'prévu',
                                            caracteristiques: [
                                                {
                                                    id: caract,
                                                    unite_id: unite,
                                                    valeur: valeur,
                                                }
                                            ]
                                        });
                                    });

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
                                            alert(response.message || "Étape 2 sauvegardée.");
                                            if (typeof callback === "function") callback();
                                        },
                                        error: function (xhr) {
                                            alert("Erreur lors de la sauvegarde !");
                                            console.error(xhr.responseText);
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
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
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
                                            alert(response.message || "Étape 3 sauvegardée.");
                                            if (typeof callback === "function") callback();
                                        },
                                        error: function (xhr) {
                                            alert("Erreur lors de la sauvegarde !");
                                            console.error(xhr.responseText);
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
                            <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true" style="background: transparent;">
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
                                <h5 class="text-secondary">📎 Documents et Pièces Justificatives</h5>
                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#documentModal">
                                    📜 Liste des documents à fournir
                                </button>
                                <div class="upload-box" onclick="document.getElementById('fileUpload').click();">
                                    <p><i class="fas fa-upload"></i> Cliquez ici pour importer vos fichiers</p>
                                    <input type="file" id="fileUpload" class="d-none" multiple>
                                </div>
                                <div class="uploaded-files mt-2" id="uploadedFiles"></div>
                                <div class="row">
                                    <div class="col">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="submit" class="btn btn-success">Soumettre</button>
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
    }

    document.getElementById('fileUpload').addEventListener('change', function(event) {
        let files = event.target.files;
        let fileList = document.getElementById('uploadedFiles');

        for (let i = 0; i < files.length; i++) {
            let file = files[i];

            // Vérification si le fichier existe déjà
            if (uploadedFiles.some(f => f.name === file.name)) {
                continue;
            }

            uploadedFiles.push(file);
            displayUploadedFiles();
        }
    });

    function displayUploadedFiles() {
        let fileList = document.getElementById('uploadedFiles');
        fileList.innerHTML = "";

        uploadedFiles.forEach((file, index) => {
            let fileItem = document.createElement('div');
            fileItem.classList.add('file-item');
            fileItem.innerHTML = `
                <span><i class="fas fa-file"></i> ${file.name}</span>
                <i class="fas fa-trash" onclick="removeFile(${index})"></i>
            `;
            fileList.appendChild(fileItem);
        });
    }

    function removeFile(index) {
        uploadedFiles.splice(index, 1);
        displayUploadedFiles();
    }
    document.getElementById('projectForm').addEventListener('submit', function(event) {
        event.preventDefault();

        if (uploadedFiles.length === 0) {
            alert("Veuillez ajouter au moins un fichier avant de soumettre.");
            return;
        }

        alert("Formulaire soumis avec succès !");
        console.log("Fichiers soumis:", uploadedFiles);
    });


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
                fetch(`/api/acteurs?search=${searchValue}`)
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



   //////////////////////////FINANCEMENT
   document.addEventListener('DOMContentLoaded', function () {
    const typeBailleur = document.getElementById('typeBailleur');
    const bailleurMoraleFields = document.getElementById('bailleurMoraleFields');
    const bailleurPhysiqueFields = document.getElementById('bailleurPhysiqueFields');

    // Affichage dynamique du formulaire selon le type sélectionné
    typeBailleur.addEventListener('change', function () {
        bailleurMoraleFields.classList.toggle('d-none', typeBailleur.value !== 'morale');
        bailleurPhysiqueFields.classList.toggle('d-none', typeBailleur.value !== 'physique');
    });

    const bailleurInput = document.getElementById('bailleur');
    const bailleurList = document.getElementById('bailleurList');
    const ajouterBailleurContainer = document.getElementById('ajouterBailleurContainer');
    const ajouterBailleurBtn = document.getElementById('ajouterBailleurBtn');
    const nouveauBailleurNom = document.getElementById('nouveauBailleurNom');

    bailleurInput.addEventListener('input', function () {
        const query = bailleurInput.value.trim();
        if (query.length < 2) {
            bailleurList.innerHTML = '';
            ajouterBailleurContainer.classList.add('d-none');
            return;
        }

        fetch(`/api/bailleurs?search=${query}`)
            .then(response => response.json())
            .then(data => {
                bailleurList.innerHTML = '';
                if (data.length === 0) {
                    nouveauBailleurNom.textContent = query;
                    ajouterBailleurContainer.classList.remove('d-none');
                    return;
                }

                ajouterBailleurContainer.classList.add('d-none');
                data.forEach(bailleur => {
                    const li = document.createElement('li');
                    li.classList.add('list-group-item', 'list-group-item-action');
                    li.textContent = `${bailleur.libelle_long} (${bailleur.type_acteur})`;
                    li.dataset.id = bailleur.code_acteur;

                    li.addEventListener('click', function () {
                        bailleurInput.value = bailleur.libelle_long;
                        bailleurInput.dataset.id = bailleur.code_acteur;
                        bailleurList.innerHTML = '';
                    });

                    bailleurList.appendChild(li);
                });
            });
    });

    ajouterBailleurBtn.addEventListener('click', function () {
        const modal = new bootstrap.Modal(document.getElementById('modalAjoutBailleur'));
        modal.show();
    });

    document.getElementById('btnEnregistrerBailleur').addEventListener('click', function () {
        alert('Bailleur enregistré avec succès !');
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalAjoutBailleur'));
        modal.hide();
    });
});



    document.addEventListener('DOMContentLoaded', function () {
        const tableBody = document.getElementById('tableFinancements');
        const addButton = document.getElementById('addFinancementBtn');
        let partieSelection = null; // Pour suivre si "Oui" ou "Non" a été sélectionné.

        // Fonction pour verrouiller les boutons radio
        function verrouillerBoutons() {
            document.getElementById('BailOui').disabled = (partieSelection === 'non');
            document.getElementById('BailNon').disabled = (partieSelection === 'oui');
        }

        // Fonction pour réinitialiser les champs après ajout
        function resetFields() {
            document.getElementById('bailleur').value = '';
            document.getElementById('montant').value = '';
            document.getElementById('deviseBailleur').value = '';
            document.getElementById('commentaire').value = '';
            document.querySelectorAll('input[name="BaillOui"]').forEach((radio) => (radio.checked = false));
        }

        // Fonction pour supprimer une ligne et réinitialiser les boutons si nécessaire
        tableBody.addEventListener('click', function (event) {
            if (event.target.classList.contains('btn-danger')) {
                const row = event.target.closest('tr');
                row.remove();

                // Vérifier si toutes les lignes ont été supprimées pour réactiver les radios
                if (tableBody.querySelectorAll('tr').length === 0) {
                    partieSelection = null;
                    document.getElementById('BailOui').disabled = false;
                    document.getElementById('BailNon').disabled = false;
                }
            }
        });

        // Fonction pour ajouter un financement
        addButton.addEventListener('click', function () {
            // Récupérer les valeurs des champs
            const bailleur = document.getElementById('bailleur').value.trim();
            const montant = document.getElementById('montant').value.trim();
            const devise = document.getElementById('deviseBailleur').value.trim();
            const partie = document.querySelector('input[name="BaillOui"]:checked')?.value || '';
            const commentaire = document.getElementById('commentaire').value.trim();

            // Vérifications des champs obligatoires
            if (!bailleur || !montant || !devise) {
                alert('Veuillez remplir tous les champs obligatoires : Bailleur, Montant et Devise.');
                return;
            }

            if (!partie) {
                alert('Veuillez sélectionner si la ressource est locale ou non.');
                return;
            }

            // Vérifier si un seul financement "Non" peut être ajouté
            if (partie === 'BaillNon' && tableBody.querySelectorAll('tr td:nth-child(4)').textContent.includes('Non')) {
                alert('Vous ne pouvez ajouter qu\'un seul financement marqué comme "Non".');
                return;
            }

            // Mettre à jour la sélection "Partie"
            if (partieSelection === null) {
                partieSelection = partie;
                verrouillerBoutons();
            } else if (partieSelection !== partie) {
                alert(`Vous avez déjà sélectionné "${partieSelection}". Vous ne pouvez pas ajouter un financement avec "${partie}".`);
                return;
            }

            // Ajouter une nouvelle ligne au tableau
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${bailleur}</td>
                <td>${montant}</td>
                <td>${devise}</td>
                <td>${partie === 'BaillOui' ? 'Oui' : 'Non'}</td>
                <td>${commentaire}</td>
                <td><button class="btn btn-danger btn-sm">Supprimer</button></td>
            `;
            tableBody.appendChild(row);

            // Réinitialiser les champs après ajout
            resetFields();
        });
    });



    ///////////////////////////LOCALLISATION
    
    let selectedLocalite = {
        id: null,
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
                url: "/get-localites/" + paysCode,
                type: "GET",
                success: function (data) {
                    $("#niveau1Select").empty().append('<option value="">Sélectionnez une localité</option>');
                    $.each(data, function (index, localite) {
                        $("#niveau1Select").append('<option value="' + localite.id + '">' + localite.libelle + '</option>');
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


            // Stocker le libellé sélectionné
            selectedLocalite.libelle = localiteText;
            selectedLocalite.id = localiteId;

            if (localiteId) {
                // Charger le niveau et découpage associés
                $.ajax({
                    url: "/get-decoupage-niveau/" + localiteId,
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
                <tr data-id="${selectedLocalite.id}">
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
 
    document.getElementById('sousDomaineSelect').addEventListener('change', function() {
        let codeSousDomaine = this.value;

        fetch('/get-familles/' + codeSousDomaine)
            .then(response => response.json())
            .then(data => {
                let select = document.getElementById('infrastructureType');
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
            document.getElementById('caract').innerHTML = '<option value=""></option>';
            return;
        }

        fetch('/get-caracteristiques/' + idType)
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

        fetch('/get-unites/' + idCaracteristique)
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

    document.addEventListener('DOMContentLoaded', function () {
        const addBtn = document.getElementById('addInfrastructureBtn');
        const tableBody = document.getElementById('tableInfrastructures');

        addBtn.addEventListener('click', function () {
            // Récupère les champs
            const typeCaract = document.getElementById('tyCaract');
            const caract = document.getElementById('caract');
            const unit = document.getElementById('unitCaract');
            const valeur = document.querySelector('input[name="caractValues[]"]');

            // Vérifie que tout est rempli
            if (!typeCaract.value || !caract.value || !unit.value || !valeur.value) {
                alert('Veuillez remplir tous les champs pour ajouter une caractéristique.');
                return;
            }

            // Crée une ligne
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="hidden" name="typesCaracteristiques[]" value="${typeCaract.value}">
                    ${typeCaract.options[typeCaract.selectedIndex].text}
                </td>
                <td>
                    <input type="hidden" name="caracteristiques[]" value="${caract.value}">
                    ${caract.options[caract.selectedIndex].text}
                </td>
                <td>
                    <input type="hidden" name="unites[]" value="${unit.value}">
                    ${unit.options[unit.selectedIndex].text}
                </td>
                <td>
                    <input type="hidden" name="valeursCaracteristiques[]" value="${valeur.value}">
                    ${valeur.value}
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm removeRow">Supprimer</button>
                </td>
            `;

            tableBody.appendChild(row);

            // Réinitialise les champs
            caract.innerHTML = '<option value="">Sélectionner la caractéristique</option>';
            unit.innerHTML = '<option value="">Sélectionner l\'unité</option>';
            typeCaract.selectedIndex = 0;
            valeur.value = '';
        });

        // Délégué pour bouton supprimer
        tableBody.addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('removeRow')) {
                e.target.closest('tr').remove();
            }
        });
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
                fetch(`/get-sous-domaines/${domaineCode}`)
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

        // ✅ Vérification avant de passer à l'étape suivante
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

        // ✅ Bouton Suivant avec validation
        function nextStep() {
            if (validateStep3()) {
                currentStep++;
                showStep(currentStep);
            }
        }
    });

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

    // On remplace l’existant pour ne garder qu’un seul maître d’œuvre
    tableBody.empty();
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
                <input type="hidden" name="code_acteur_moeuvre" value="${codeActeur}">
            </td>
        </tr>
    `;
    tableBody.append(row);
});

// Suppression
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
        let url = `/get-acteurs?type_mo=${type_mo}`; // Construire l'URL API

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

                fetch(`/get-acteurs?type_selection=${selectionType}`)
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

                fetch(`/get-acteurs?type_selection=${selectionType}`)
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
            let url = `/get-acteurs?type_ouvrage=${encodeURIComponent(typeOuvrage)}`;

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
            url: "/get-localites/" + paysCode,
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

@endsection
