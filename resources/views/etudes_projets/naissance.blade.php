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
                            <div class="step active" id="step-1">
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
                                        <label>Acteur Responsable *</label>
                                        <select class="form-control required" id="acteurMoeSelect">
                                            <option value="">Sélectionnez un acteur</option>
                                        </select>
                                        <small class="text-muted">Sélectionnez l’entité qui assure le rôle de Maître d’œuvre.</small>
                                    </div>

                                </div>

                                <div class="row">

                                    <!-- MOE Entreprise Fields -->
                                        <div class="row mt-3 d-none" id="moeEntrepriseFields">
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
                                                <div class="tab-content mt-3" id="moeentrepriseTabsContent">
                                                    <!-- Tab 1: Informations Générales -->
                                                    <div class="tab-pane fade show active" id="moeentreprise-general" role="tabpanel" aria-labelledby="moeentreprise-general-tab">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label>Code de l'Entreprise :</label>
                                                                <input type="text" class="form-control" placeholder="Nom de l'entreprise">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Nom de l'Entreprise :</label>
                                                                <input type="text" class="form-control" placeholder="Nom de l'entreprise">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Date de création :</label>
                                                                <input type="text" class="form-control" placeholder="Adresse complète">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Secteur d'activité :</label>
                                                                <select name="SecteurActiviteEntreprise" id="SecteurActiviteEntreprise" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($SecteurActivites as $SecteurActivite)
                                                                        <option value="{{ $SecteurActivite->code }}">{{ $SecteurActivite->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4 ">
                                                                <label>Forme Juridique :</label>
                                                                <select name="FormeJuridique" id="FormeJuridique" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($formeJuridiques as $formeJuridique)
                                                                        <option value="{{ $formeJuridique->id }}">{{ $formeJuridique->forme }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 2: Informations Juridiques -->
                                                    <div class="tab-pane fade" id="moeentreprise-legal" role="tabpanel" aria-labelledby="moeentreprise-legal-tab">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label>Numéro d’Immatriculation :</label>
                                                                <input type="text" class="form-control" placeholder="Numéro RCCM">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Numéro d’Identification Fiscale (NIF) :</label>
                                                                <input type="text" class="form-control" placeholder="Numéro fiscal">
                                                            </div>
                                                            <div class="col-md-6 mt-2">
                                                                <label>Capital Social :</label>
                                                                <input type="number" class="form-control" placeholder="Capital social de l’entreprise">
                                                            </div>
                                                            <div class="col-md-6 mt-2">
                                                                <label>Numéro d'agrément :</label>
                                                                <input type="text" name="Numéroagrement" id="Numéroagrement" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 3: Informations de Contact -->
                                                    <div class="tab-pane fade" id="moeentreprise-contact" role="tabpanel" aria-labelledby="moeentreprise-contact-tab">
                                                        <div class="row">
                                                            <div class="col-4">
                                                                <label>Code postale</label>
                                                                <input type="text" class="form-control" name="CodePostaleEntreprise" placeholder="Code postale">
                                                            </div>
                                                            <div class="col-4">
                                                                <label>Adresse postale</label>
                                                                <input type="text" class="form-control" name="AdressePostaleEntreprise" placeholder="Code postale">
                                                            </div>
                                                            <div class="col-4">
                                                                <label>Adresse Siège</label>
                                                                <input type="text" class="form-control" name="AdresseSiègeEntreprise" placeholder="Code postale">
                                                            </div>
                                                            <hr>
                                                            <div class="col-md-3">
                                                                <label>Représentant Légal :</label>
                                                                <input type="text" class="form-control"  placeholder="Nom du représentant légal">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Email:</label>
                                                                <input type="email" class="form-control" placeholder="Email du représentant légal">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 1:</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone 1 du représentant légal">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 2:</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone 2 du représentant légal">
                                                            </div>
                                                            <hr>
                                                            <div class="col-md-3">
                                                                <label>Personne de Contact :</label>
                                                                <input type="text" class="form-control" placeholder="Nom de la personne de contact">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Email:</label>
                                                                <input type="email" class="form-control" placeholder="Email du personne de Contact">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 1:</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone 1 de la ersonne de Contact">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 2:</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone 2 de la Personne de Contact">
                                                            </div>
                                                            <hr>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- MOE Individu Fields -->
                                        <div class="row mt-3 d-none" id="moeIndividuFields">
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
                                                <div class="tab-content mt-3" id="moeindividuTabsContent">
                                                    <!-- Tab 1: Informations Personnelles -->
                                                    <div class="tab-pane fade show active" id="moeindividu-general" role="tabpanel" aria-labelledby="moeindividu-general-tab">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label>Nom :</label>
                                                                <input type="text" class="form-control" placeholder="Nom">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Prénom :</label>
                                                                <input type="text" class="form-control" placeholder="Prénom">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Date de Naissance :</label>
                                                                <input type="date" class="form-control">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Nationalité :</label>
                                                                <input type="text" class="form-control" placeholder="Nationalité">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Secteur d'activité :</label>
                                                                <select name="SecteurActiviteEntreprise" id="SecteurActiviteEntreprise" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($formeJuridiques as $formeJuridique)
                                                                        <option value="{{ $formeJuridique->id }}">{{ $formeJuridique->forme }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 2: Informations de Contact -->
                                                    <div class="tab-pane fade" id="moeindividu-contact" role="tabpanel" aria-labelledby="moeindividu-contact-tab">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <label>Email :</label>
                                                                <input type="email" class="form-control" placeholder="Email">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label for="codePostal">Code postal</label>
                                                                <input type="text" name="CodePostal" id="CodePostal" class="form-control">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Adresse :</label>
                                                                <input type="text" class="form-control" placeholder="Adresse">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Adresse siège :</label>
                                                                <input type="text" class="form-control" placeholder="Adresse">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Téléphone Bureau:</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Téléphone mobile:</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 3: Informations Administratives -->
                                                    <div class="tab-pane fade" id="moeindividu-admin" role="tabpanel" aria-labelledby="moeindividu-admin-tab">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label>Numéro de Carte d’Identité :</label>
                                                                <input type="text" class="form-control" placeholder="Numéro de CNI">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Date de vailidité :</label>
                                                                <input type="date" class="form-control" placeholder="Numéro de CNI">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Numéro Fiscal :</label>
                                                                <input type="text" class="form-control" placeholder="Numéro fiscal">
                                                            </div>
                                                            <div class="col-md-4 ">
                                                                <label>Situation Matrimoniale :</label>
                                                                <select class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($SituationMatrimoniales as $SituationMatrimoniale)
                                                                        <option value="{{ $SituationMatrimoniale->id }}">{{ $SituationMatrimoniale->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Genre</label>
                                                                <select name="genre" id="genre" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($genres as $genre)
                                                                    <option value="{{ $genre->code_genre }}">{{ $genre->libelle_genre }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>                                <!-- Champs pour Entreprise -->


                                <hr>
                                <div class="row mt-3">
                                    <label>Description / Observations</label>
                                    <textarea class="form-control" id="descriptionMoe" rows="3" placeholder="Ajoutez des précisions sur le Maître d’œuvre"></textarea>
                                </div><br>
                                <div class="row">

                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
                                    </div>
                                </div>

                            </div>
                          <!-- Étape  : Informations sur le Maître d’Ouvrage -->
                            <div class="step" id="step-2">
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
                                        <label>Acteur Responsable *</label>
                                        <select class="form-control required" id="acteurSelect">
                                            <option value="">Sélectionnez un acteur</option>

                                        </select>
                                        <small class="text-muted">Sélectionnez l’entité qui assure le rôle de Maître d'œuvre.</small>
                                    </div>
                                    <div class="col">
                                        <!-- ✅ Sélection "En Charge de" -->
                                        <label>En Charge de *</label>
                                        <select class="form-control required" id="enChargeSelect">
                                            <option value="">Sélectionnez la responsabilité</option>
                                            @foreach ($SecteurActivites as $SecteurActivite)
                                            <option value="{{$SecteurActivite->code}}">{{$SecteurActivite->libelle}}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Définissez la responsabilité principale du Maître d'œuvre.</small>
                                    </div>
                                </div>

                                <div class="row">

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
                                                                <input type="text" class="form-control" placeholder="Nom de l'entreprise">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Date de création * </label>
                                                                <input type="text" class="form-control" placeholder="Adresse complète">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Secteur d'activité * </label>
                                                                <select name="SecteurActiviteEntreprise" id="SecteurActiviteEntreprise" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($SecteurActivites as $SecteurActivite)
                                                                        <option value="{{ $SecteurActivite->code }}">{{ $SecteurActivite->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6 ">
                                                                <label>Forme Juridique *</label>
                                                                <select name="FormeJuridique" id="FormeJuridique" class="form-control">
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
                                                                <input type="text" class="form-control" placeholder="Numéro RCCM">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Numéro d’Identification Fiscale (NIF) :</label>
                                                                <input type="text" class="form-control" placeholder="Numéro fiscal">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Registre du commerce (RCCM) :</label>
                                                                <input type="text" class="form-control" placeholder="Numéro fiscal">
                                                            </div>
                                                            <div class="col-md-6 mt-2">
                                                                <label>Capital Social :</label>
                                                                <input type="number" class="form-control" placeholder="Capital social de l’entreprise">
                                                            </div>
                                                            <div class="col-md-6 mt-2">
                                                                <label>Numéro d'agrément :</label>
                                                                <input type="text" name="Numéroagrement" id="Numéroagrement" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 3: Informations de Contact -->
                                                    <div class="tab-pane fade" id="entreprise-contact" role="tabpanel" aria-labelledby="entreprise-contact-tab">
                                                        <div class="row">
                                                            <div class="col-4">
                                                                <label>Code postale</label>
                                                                <input type="text" class="form-control" name="CodePostaleEntreprise" placeholder="Code postale">
                                                            </div>
                                                            <div class="col-4">
                                                                <label>Adresse postale</label>
                                                                <input type="text" class="form-control" name="AdressePostaleEntreprise" placeholder="Code postale">
                                                            </div>
                                                            <div class="col-4">
                                                                <label>Adresse Siège</label>
                                                                <input type="text" class="form-control" name="AdresseSiègeEntreprise" placeholder="Code postale">
                                                            </div>
                                                            <hr>
                                                            <div class="col-md-3">
                                                                <label>Représentant Légal *</label>
                                                                <input type="text" class="form-control"  placeholder="Nom du représentant légal">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Email *</label>
                                                                <input type="email" class="form-control" placeholder="Email du représentant légal">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 1 *</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone 1 du représentant légal">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 2 *</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone 2 du représentant légal">
                                                            </div>
                                                            <hr>
                                                            <div class="col-md-3">
                                                                <label>Personne de Contact </label>
                                                                <input type="text" class="form-control" placeholder="Nom de la personne de contact">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Email</label>
                                                                <input type="email" class="form-control" placeholder="Email du personne de Contact">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 1</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone 1 de la ersonne de Contact">
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label>Téléphone 2</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone 2 de la Personne de Contact">
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
                                                        <button class="nav-link" id="individu-contact-tab" data-bs-toggle="tab" data-bs-target="#individu-contact" type="button" role="tab" aria-controls="individu-contact" aria-selected="false">Informations de Contact</button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="individu-admin-tab" data-bs-toggle="tab" data-bs-target="#individu-admin" type="button" role="tab" aria-controls="individu-admin" aria-selected="false">Informations Administratives</button>
                                                    </li>
                                                </ul>
                                                <div class="tab-content mt-3" id="individuTabsContent">
                                                    <!-- Tab 1: Informations Personnelles -->
                                                    <div class="tab-pane fade show active" id="individu-general" role="tabpanel" aria-labelledby="individu-general-tab">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label>Nom *</label>
                                                                <input type="text" class="form-control" placeholder="Nom">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Prénom *</label>
                                                                <input type="text" class="form-control" placeholder="Prénom">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Date de Naissance </label>
                                                                <input type="date" class="form-control">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Nationalité *</label>
                                                                <input type="text" class="form-control" placeholder="Nationalité">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Secteur d'activité *</label>
                                                                <select name="SecteurActiviteEntreprise" id="SecteurActiviteEntreprise" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($formeJuridiques as $formeJuridique)
                                                                        <option value="{{ $formeJuridique->id }}">{{ $formeJuridique->forme }}</option>
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
                                                                <input type="email" class="form-control" placeholder="Email">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label for="codePostal">Code postal</label>
                                                                <input type="text" name="CodePostal" id="CodePostal" class="form-control">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Adresse postale</label>
                                                                <input type="text" class="form-control" placeholder="Adresse">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Adresse siège *</label>
                                                                <input type="text" class="form-control" placeholder="Adresse">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Téléphone Bureau *</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Téléphone mobile *</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 3: Informations Administratives -->
                                                    <div class="tab-pane fade" id="individu-admin" role="tabpanel" aria-labelledby="individu-admin-tab">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <label>Numéro de Carte d’Identité </label>
                                                                <input type="text" class="form-control" placeholder="Numéro de CNI">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Date de vailidité </label>
                                                                <input type="date" class="form-control" placeholder="Numéro de CNI">
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Numéro Fiscal </label>
                                                                <input type="text" class="form-control" placeholder="Numéro fiscal">
                                                            </div>
                                                            <div class="col-md-4 ">
                                                                <label>Situation Matrimoniale :</label>
                                                                <select class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($SituationMatrimoniales as $SituationMatrimoniale)
                                                                        <option value="{{ $SituationMatrimoniale->id }}">{{ $SituationMatrimoniale->libelle }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label>Genre</label>
                                                                <select name="genre" id="genre" class="form-control">
                                                                    <option value="">Sélectionnez...</option>
                                                                    @foreach ($genres as $genre)
                                                                    <option value="{{ $genre->code_genre }}">{{ $genre->libelle_genre }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                                <!-- ✅ Zone de description complémentaire -->
                                <div class="row">
                                    <label>Description / Observations</label>
                                    <textarea class="form-control" id="descriptionMO" rows="3" placeholder="Ajoutez des précisions sur le Maître d’Ouvrage (ex: Budget, contraintes, accords...)"></textarea>
                                </div><br>
                                <div class="row">

                                <div class="col">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary " onclick="nextStep()">Suivant</button>
                                    </div>
                                </div>

                            </div>

                            <!-- Étape : Informations sur le Chef de Projet -->
                            <div class="step" id="step-3">
                                <h5 class="text-secondary">👨‍💼 Informations / Chef de Projet</h5>

                                <!-- Recherche et sélection du Chef de Projet -->
                                <div class="col-4 position-relative">
                                    <label>Chef de Projet *</label>
                                    <input type="text" id="chefProjetInput" name="chefProjet" class="form-control" placeholder="Rechercher un chef de projet..." onkeyup="searchChefProjet()">
                                    <ul class="list-group position-absolute w-100 d-none" id="chefProjetList" style="z-index: 1000;"></ul>
                                    <small class="text-muted">Sélectionnez un chef de projet existant ou ajoutez un nouveau.</small>
                                </div>

                                <!-- Formulaire pour renseigner un nouveau chef de projet -->
                                <div class="row mt-3 d-none" id="chefProjetFields">
                                    <hr>
                                    <h6>Détails du Chef de Projet</h6>

                                    <div class="col-12">
                                        <ul class="nav nav-tabs" id="chefProjetTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active" id="chef-general-tab" data-bs-toggle="tab" data-bs-target="#chef-general" type="button" role="tab" aria-controls="chef-general" aria-selected="true">Informations Personnelles</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="chef-contact-tab" data-bs-toggle="tab" data-bs-target="#chef-contact" type="button" role="tab" aria-controls="chef-contact" aria-selected="false">Informations de Contact</button>
                                            </li>
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link" id="chef-admin-tab" data-bs-toggle="tab" data-bs-target="#chef-admin" type="button" role="tab" aria-controls="chef-admin" aria-selected="false">Informations Administratives</button>
                                            </li>
                                        </ul>

                                        <div class="tab-content mt-3" id="chefProjetTabsContent">
                                            <!-- Tab 1: Informations Personnelles -->
                                            <div class="tab-pane fade show active" id="chef-general" role="tabpanel" aria-labelledby="chef-general-tab">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label>Nom *</label>
                                                        <input type="text" class="form-control" id="chefNom" placeholder="Nom">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Prénom *</label>
                                                        <input type="text" class="form-control" id="chefPrenom" placeholder="Prénom">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Date de Naissance</label>
                                                        <input type="date" class="form-control" id="chefDateNaissance">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Nationalité *</label>
                                                        <input type="text" class="form-control" id="chefNationalite" placeholder="Nationalité">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Secteur d'activité *</label>
                                                        <select name="chefSecteurActivite" id="chefSecteurActivite" class="form-control">
                                                            <option value="">Sélectionnez...</option>
                                                            @foreach ($formeJuridiques as $formeJuridique)
                                                                <option value="{{ $formeJuridique->id }}">{{ $formeJuridique->forme }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tab 2: Informations de Contact -->
                                            <div class="tab-pane fade" id="chef-contact" role="tabpanel" aria-labelledby="chef-contact-tab">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label>Email *</label>
                                                        <input type="email" class="form-control" id="chefEmail" placeholder="Email">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label for="codePostal">Code postal</label>
                                                        <input type="text" name="chefCodePostal" id="chefCodePostal" class="form-control">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Adresse postale</label>
                                                        <input type="text" class="form-control" placeholder="Adresse">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Adresse siège *</label>
                                                        <input type="text" class="form-control" placeholder="Adresse">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Téléphone Bureau *</label>
                                                        <input type="text" class="form-control" placeholder="Téléphone">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Téléphone mobile *</label>
                                                        <input type="text" class="form-control" id="chefTelephoneMobille" placeholder="Téléphone">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tab 3: Informations Administratives -->
                                            <div class="tab-pane fade" id="chef-admin" role="tabpanel" aria-labelledby="chef-admin-tab">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label>Numéro de Carte d’Identité </label>
                                                        <input type="text" class="form-control" placeholder="Numéro de CNI">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label>Date de vailidité </label>
                                                        <input type="date" class="form-control" placeholder="Numéro de CNI">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Numéro Fiscal </label>
                                                        <input type="text" class="form-control" placeholder="Numéro fiscal">
                                                    </div>
                                                    <div class="col-md-4 ">
                                                        <label>Situation Matrimoniale :</label>
                                                        <select class="form-control">
                                                            <option value="">Sélectionnez...</option>
                                                            @foreach ($SituationMatrimoniales as $SituationMatrimoniale)
                                                                <option value="{{ $SituationMatrimoniale->id }}">{{ $SituationMatrimoniale->libelle }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label>Genre</label>
                                                        <select name="genre" id="genre" class="form-control">
                                                            <option value="">Sélectionnez...</option>
                                                            @foreach ($genres as $genre)
                                                            <option value="{{ $genre->code_genre }}">{{ $genre->libelle_genre }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Boutons de navigation -->
                                <div class="row mt-3">
                                    <div class="col">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
                                    </div>
                                </div>
                            </div>

                            <!-- 🔵 Étape : Financement -->
                            <div class="step" id="step-4">
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
                                    <div class="col-md-3">
                                        <label for="bailleur">Bailleur</label>
                                        <input type="text" id="bailleur" class="form-control" placeholder="Rechercher un bailleur...">
                                        <ul class="list-group" id="bailleurList"></ul>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="montant">Montant</label>
                                        <input type="number" id="montant" class="form-control" placeholder="Montant">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="devise">Devise</label>
                                        <select id="devise" class="form-control">
                                            <option value="FCFA">FCFA</option>
                                            <option value="USD">USD</option>
                                            <option value="EUR">EUR</option>
                                        </select>
                                    </div>
                                    <div class="col-md-1">
                                        <label>Partie</label><br>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" id="partieOui" name="partie" value="oui" class="form-check-input">
                                            <label for="partieOui" class="form-check-label">Oui</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input type="radio" id="partieNon" name="partie" value="non" class="form-check-input">
                                            <label for="partieNon" class="form-check-label">Non</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="commentaire">Commentaire</label>
                                        <input type="text" id="commentaire" class="form-control" placeholder="Commentaire">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
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
                                                <th>Partie</th>
                                                <th>Commentaire</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableFinancements">
                                            <!-- Les lignes seront ajoutées ici dynamiquement -->
                                        </tbody>
                                    </table>
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
                            <div class="step" id="step-5">
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
                                        <select class="form-control">
                                            <option>Sélectionner un groupe</option>
                                            @foreach ($GroupeProjets as $groupe)
                                            <option value="{{ $groupe->code }}">{{ $groupe->libelle }}</option>
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
                                        <select name="domaine" id="domaine" class="form-control">
                                            <option value="">Sélectionner domaine</option>
                                            @foreach ($Domaines as $domaine)
                                                <option value="{{ $domaine->code }}">{{ $domaine->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label for="SousDomaine">Sous-Domaine *</label>
                                        <select name="SousDomaine" id="SousDomaine" class="form-control">
                                            <option value="">Sélectionner sous domaine</option>
                                            @foreach ($SousDomaines as $SousDomaine)
                                               <option value="{{ $SousDomaine->code }}">{{ $SousDomaine->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div><br>
                                

                                <div class="row">
                                    <div class="col">
                                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                    </div>
                                    <div class="col text-end">
                                        <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
                                    </div>
                                </div>
                            </div>

                            <!-- 🟠 Étape  : Localisation -->
                            <div class="step" id="step-6">
                                <h5 class="text-secondary">🌍 Localisation</h5>
                                <div class="row">
                                    <br>
                                    <div class="col">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>Pays *</label>
                                                <select class="form-control" id="paysSelect">
                                                    <option value="">Sélectionnez un pays</option>
                                                    @foreach ($Pays as $alpha3 => $nom_fr_fr)
                                                        <option value="{{ $alpha3 }}">{{ $nom_fr_fr }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <label id="niveau1Label">Niveau 1 *</label>
                                                <select class="form-control" id="niveau1Select" disabled>
                                                    <option value="">Sélectionnez un niveau</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-12">
                                                <label id="niveau2Label">Niveau 2 *</label>
                                                <select class="form-control" id="niveau2Select" disabled>
                                                    <option value="">Sélectionnez un niveau</option>
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <label id="niveau3Label">Niveau 3 *</label>
                                                <select class="form-control" id="niveau3Select" disabled>
                                                    <option value="">Sélectionnez un niveau</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6 d-none" id="fixedPositionContainer">
                                                <label>Position Fixe :</label>
                                                <input type="text" id="fixedPosition" class="form-control" placeholder="Entrez une adresse précise...">
                                                <ul class="list-group position-absolute w-100 d-none" id="fixedPositionResults" style="z-index: 1000;"></ul>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <label>Latitude</label>
                                                <input type="text" id="latitude" class="form-control" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label>Longitude</label>
                                                <input type="text" id="longitude" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                            <div class="col-md-12">
                                                <label>📍 Sélectionner l'Emplacement sur la Carte</label>
                                                <div id="countryMap" style="height: 400px; border: 1px solid #ddd;"></div>
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

                            <!-- 🔵 Étape : Bénéficiaire -->
                            <div class="step" id="step-7">
                                <h5 class="text-secondary">🧍 Bénéficiaires</h5>
                                <div class="row">
                                    <div class="col-md-1">
                                        <label for="nOrdre">N°</label>
                                        <input type="number" id="nOrdre" class="form-control" value="1" readonly>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="action">Action à mener</label>
                                        <select id="action" class="form-control">
                                            <option value="">Sélectionner</option>
                                            <option value="Action 1">Action 1</option>
                                            <option value="Action 2">Action 2</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="quantite">Quantité</label>
                                        <input type="number" id="quantite" class="form-control" placeholder="Quantité">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="infrastructure">Infrastructure</label>
                                        <select id="infrastructure" class="form-control">
                                            <option value="">Sélectionner</option>
                                            <option value="Route">Route</option>
                                            <option value="Pont">Pont</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-secondary me-2" id="openBeneficiaireModalBtn" data-bs-toggle="modal" data-bs-target="#beneficiaireModal">
                                            Bénéficiaire
                                        </button>
                                    </div>
                                </div>

                                <!-- Tableau des Bénéficiaires -->
                                <div class="mt-4">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>N° d’ordre</th>
                                                <th>Action</th>
                                                <th>Quantité</th>
                                                <th>Infrastructure</th>
                                                <th>Libellé Bénéficiaires</th>
                                                <th>Code Bénéficiaire</th>
                                                <th>Type Bénéficiaire</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="beneficiaireMainTable">
                                            <!-- Les lignes seront ajoutées ici -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Modal pour gérer les bénéficiaires -->
                                <div class="modal fade" id="beneficiaireModal" tabindex="-1" aria-labelledby="beneficiaireModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="beneficiaireModalLabel">🧍 Ajouter des Bénéficiaires</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Types de bénéficiaires -->
                                                <div class="row mb-3">
                                                    <label>Bénéficiaire :</label>
                                                    <div class="col-md-12">
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="localite" name="beneficiaireType" value="Localité" class="form-check-input">
                                                            <label class="form-check-label" for="localite">Localité</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="sousPrefecture" name="beneficiaireType" value="Sous-préfecture" class="form-check-input">
                                                            <label class="form-check-label" for="sousPrefecture">Sous-préfecture</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="departement" name="beneficiaireType" value="Département" class="form-check-input">
                                                            <label class="form-check-label" for="departement">Département</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input type="radio" id="region" name="beneficiaireType" value="Région" class="form-check-input">
                                                            <label class="form-check-label" for="region">Région</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Liste déroulante pour sélectionner les bénéficiaires -->
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <label for="beneficiaireSelect">Sélectionner Bénéficiaire</label>
                                                        <select id="beneficiaireSelect" class="form-control">
                                                            <option value="B001">Bénéficiaire 1</option>
                                                            <option value="B002">Bénéficiaire 2</option>
                                                            <option value="B003">Bénéficiaire 3</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 d-flex align-items-end">
                                                        <button type="button" class="btn btn-primary" id="addBeneficiaireBtn">Ajouter</button>
                                                    </div>
                                                </div>

                                                <!-- Tableau des bénéficiaires sélectionnés -->
                                                <div class="mt-3">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Code</th>
                                                                <th>Libellé</th>
                                                                <th>Type</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="beneficiaireTableBody">
                                                            <!-- Lignes ajoutées dynamiquement -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
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
                            <div class="step" id="step-8">
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
    let currentStep = 1;
    const totalSteps = 8;
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
    let entrepriseFields = document.getElementById("moeEntrepriseFields");
    let individuFields = document.getElementById("moeIndividuFields");

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
        const tableBody = document.getElementById('tableFinancements');
        const addButton = document.getElementById('addFinancementBtn');
        let partieSelection = null; // Pour suivre si "Oui" ou "Non" a été sélectionné.

        // Fonction pour verrouiller les boutons radio
        function verrouillerBoutons() {
            if (partieSelection === 'oui') {
                document.getElementById('partieNon').disabled = true;
            } else if (partieSelection === 'non') {
                document.getElementById('partieOui').disabled = true;
            }
        }

        // Fonction pour réinitialiser les champs
        function resetFields() {
            document.getElementById('bailleur').value = '';
            document.getElementById('montant').value = '';
            document.getElementById('devise').value = '';
            document.querySelectorAll('input[name="partie"]').forEach((radio) => (radio.checked = false));
            document.getElementById('commentaire').value = '';
        }

        // Fonction pour supprimer une ligne
        tableBody.addEventListener('click', function (event) {
            if (event.target.classList.contains('btn-danger')) {
                const row = event.target.closest('tr');
                row.remove();

                // Vérifier si le tableau est vide et réinitialiser les boutons radio
                const rows = tableBody.querySelectorAll('tr');
                if (rows.length === 0) {
                    partieSelection = null;
                    document.getElementById('partieOui').disabled = false;
                    document.getElementById('partieNon').disabled = false;
                }
            }
        });

        // Fonction pour ajouter un financement
        addButton.addEventListener('click', function () {
            // Récupérer les valeurs des champs
            const bailleur = document.getElementById('bailleur').value;
            const montant = document.getElementById('montant').value;
            const devise = document.getElementById('devise').value;
            const partie = document.querySelector('input[name="partie"]:checked')?.value || '';
            const commentaire = document.getElementById('commentaire').value;

            // Vérifications des champs obligatoires
            if (!bailleur || !montant || !devise) {
                alert('Veuillez remplir tous les champs obligatoires : Bailleur, Montant et Devise.');
                return;
            }

            if (!partie) {
                alert('Veuillez sélectionner si la ressource est partielle ou complète.');
                return;
            }

            // Logique spécifique pour "Partie"
            if (partieSelection === null) {
                // Première sélection
                partieSelection = partie;
                verrouillerBoutons();
            } else if (partieSelection !== partie) {
                alert(`Vous avez déjà sélectionné "${partieSelection}". Vous ne pouvez pas ajouter un financement avec "${partie}".`);
                return;
            }

            if (partie === 'non' && tableBody.querySelectorAll('tr').length > 0) {
                alert('Vous ne pouvez ajouter qu\'un seul financement marqué comme "Non".');
                return;
            }

            // Ajouter une nouvelle ligne au tableau
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${bailleur}</td>
                <td>${montant}</td>
                <td>${devise}</td>
                <td>${partie === 'oui' ? 'Oui' : 'Non'}</td>
                <td>${commentaire}</td>
                <td><button class="btn btn-danger btn-sm">Supprimer</button></td>
            `;
            tableBody.appendChild(row);

            // Réinitialiser les champs
            resetFields();
        });
    });


    ///////////////////////////LOCALLISATION
document.addEventListener("DOMContentLoaded", function () {
    let map = L.map('countryMap').setView([7.539989, -5.54708], 6); // Position initiale : Côte d'Ivoire

    // Ajouter une couche OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let marker = L.marker([7.539989, -5.54708], { draggable: true }).addTo(map);

    // Mettre à jour les coordonnées GPS lors du déplacement du marqueur
    marker.on('dragend', function () {
        let position = marker.getLatLng();
        document.getElementById("latitude").value = position.lat.toFixed(6);
        document.getElementById("longitude").value = position.lng.toFixed(6);
    });

    // Sélection dynamique des niveaux
    document.getElementById("paysSelect").addEventListener("change", function () {
        let alpha3 = this.value;
        resetSelect(niveau1Select, "Niveau 1 *");
        resetSelect(niveau2Select, "Niveau 2 *");
        resetSelect(niveau3Select, "Niveau 3 *");

        if (!alpha3) return;

        fetch(`/pays/${alpha3}/niveaux`)
            .then(response => response.json())
            .then(data => {
                data.forEach(niveau => {
                    if (niveau.num_niveau_decoupage === 1) {
                        niveau1Label.textContent = niveau.libelle_decoupage + " *";
                        niveau1Select.disabled = false;
                        loadLocalites(alpha3, 1, null, niveau1Select);
                    } else if (niveau.num_niveau_decoupage === 2) {
                        niveau2Label.textContent = niveau.libelle_decoupage + " *";
                        niveau2Select.disabled = true;
                    } else if (niveau.num_niveau_decoupage === 3) {
                        niveau3Label.textContent = niveau.libelle_decoupage + " *";
                        niveau3Select.disabled = true;
                    }
                });

                // Zoomer sur le pays sélectionné
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.options[this.selectedIndex].text)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            let location = data[0];
                            map.setView([location.lat, location.lon], 6);
                            marker.setLatLng([location.lat, location.lon]);

                            document.getElementById("latitude").value = location.lat;
                            document.getElementById("longitude").value = location.lon;
                        }
                    });
            })
            .catch(error => console.error('Erreur chargement niveaux:', error));
    });

    // Sélection des sous-niveaux
    document.getElementById("niveau1Select").addEventListener("change", function () {
        let codeRattachement = this.value;
        loadLocalites(document.getElementById("paysSelect").value, 2, codeRattachement, niveau2Select);
    });

    document.getElementById("niveau2Select").addEventListener("change", function () {
        let codeRattachement = this.value;
        loadLocalites(document.getElementById("paysSelect").value, 3, codeRattachement, niveau3Select);

        // Afficher la zone de recherche de lieu fixe à partir du niveau 2
        document.getElementById("fixedPositionContainer").classList.remove("d-none");

        // Zoomer sur la carte en fonction du niveau 2 sélectionné
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.options[this.selectedIndex].text)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    let location = data[0];
                    map.setView([location.lat, location.lon], 10);
                    marker.setLatLng([location.lat, location.lon]);

                    document.getElementById("latitude").value = location.lat;
                    document.getElementById("longitude").value = location.lon;
                }
            });
    });

    document.getElementById("niveau3Select").addEventListener("change", function () {
        // Zoomer sur la carte en fonction du niveau 3 sélectionné
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(this.options[this.selectedIndex].text)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    let location = data[0];
                    map.setView([location.lat, location.lon], 12);
                    marker.setLatLng([location.lat, location.lon]);

                    document.getElementById("latitude").value = location.lat;
                    document.getElementById("longitude").value = location.lon;
                }
            });
    });

    // Charger les localités en fonction du niveau
    function loadLocalites(alpha3, niveau, codeRattachement, selectElement) {
        let url = `/pays/${alpha3}/niveau/${niveau}/localites${codeRattachement ? `?code_rattachement=${codeRattachement}` : ""}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                resetSelect(selectElement, `Niveau ${niveau} *`);
                data.forEach(localite => {
                    let option = document.createElement('option');
                    option.value = localite.code_rattachement;
                    option.textContent = localite.libelle;
                    selectElement.appendChild(option);
                });
                selectElement.disabled = false;
            })
            .catch(error => console.error('Erreur chargement localités:', error));
    }

    // Réinitialiser un select
    function resetSelect(selectElement, defaultText) {
        selectElement.innerHTML = `<option value="">${defaultText}</option>`;
        selectElement.disabled = true;
    }

    // Recherche et mise à jour des coordonnées en fonction du lieu entré manuellement
    document.getElementById("fixedPosition").addEventListener("keyup", function () {
        let input = this.value.trim();
        if (input.length < 3) return;

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(input)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    let location = data[0];
                    map.setView([location.lat, location.lon], 14);
                    marker.setLatLng([location.lat, location.lon]);

                    document.getElementById("latitude").value = location.lat;
                    document.getElementById("longitude").value = location.lon;
                }
            });
    });

    // Mise à jour des coordonnées GPS en cliquant sur la carte
    map.on('click', function (e) {
        let lat = e.latlng.lat.toFixed(6);
        let lon = e.latlng.lng.toFixed(6);

        document.getElementById("latitude").value = lat;
        document.getElementById("longitude").value = lon;

        marker.setLatLng([lat, lon]);
    });
});







    ////////////////////////////BENEFICAIRE
    document.addEventListener("DOMContentLoaded", function () {
        const beneficiaireTableBody = document.getElementById("beneficiaireTableBody");
        const beneficiaireMainTable = document.getElementById("beneficiaireMainTable");
        const addBeneficiaireBtn = document.getElementById("addBeneficiaireBtn");

        let selectedBeneficiaires = []; // Tableau des bénéficiaires sélectionnés

        // Ajouter un bénéficiaire depuis le modal
        addBeneficiaireBtn.addEventListener("click", function () {
            const beneficiaireType = document.querySelector('input[name="beneficiaireType"]:checked');
            const beneficiaireSelect = document.getElementById("beneficiaireSelect");

            if (!beneficiaireType || !beneficiaireSelect.value) {
                alert("Veuillez sélectionner un type et un bénéficiaire.");
                return;
            }

            // Ajouter le bénéficiaire dans le tableau modal
            const beneficiaire = {
                code: beneficiaireSelect.value,
                libelle: beneficiaireSelect.options[beneficiaireSelect.selectedIndex].text,
                type: beneficiaireType.value
            };

            selectedBeneficiaires.push(beneficiaire);

            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${beneficiaire.code}</td>
                <td>${beneficiaire.libelle}</td>
                <td>${beneficiaire.type}</td>
                <td><button class="btn btn-danger btn-sm removeBeneficiaire">Supprimer</button></td>
            `;
            beneficiaireTableBody.appendChild(row);
        });

        // Supprimer un bénéficiaire dans le modal
        beneficiaireTableBody.addEventListener("click", function (e) {
            if (e.target.classList.contains("removeBeneficiaire")) {
                const row = e.target.closest("tr");
                const code = row.children[0].textContent;

                // Retirer du tableau des bénéficiaires sélectionnés
                selectedBeneficiaires = selectedBeneficiaires.filter(b => b.code !== code);

                // Supprimer la ligne du tableau
                row.remove();
            }
        });

        // Ajouter les bénéficiaires dans le tableau principal
        document.getElementById("openBeneficiaireModalBtn").addEventListener("click", function () {
            if (selectedBeneficiaires.length === 0) {
                alert("Veuillez ajouter au moins un bénéficiaire.");
                return;
            }

            const nOrdre = document.getElementById("nOrdre").value;
            const action = document.getElementById("action").value;
            const quantite = document.getElementById("quantite").value;
            const infrastructure = document.getElementById("infrastructure").value;

            if (!action || !quantite || !infrastructure) {
                alert("Veuillez remplir tous les champs.");
                return;
            }

            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${nOrdre}</td>
                <td>${action}</td>
                <td>${quantite}</td>
                <td>${infrastructure}</td>
                <td>${selectedBeneficiaires.map(b => b.libelle).join(", ")}</td>
                <td>${selectedBeneficiaires.map(b => b.code).join(", ")}</td>
                <td>${selectedBeneficiaires.map(b => b.type).join(", ")}</td>
                <td><button class="btn btn-danger btn-sm removeAction">Supprimer</button></td>
            `;

            beneficiaireMainTable.appendChild(row);

            // Réinitialiser les bénéficiaires
            selectedBeneficiaires = [];
            beneficiaireTableBody.innerHTML = "";
        });

        // Supprimer une action dans le tableau principal
        beneficiaireMainTable.addEventListener("click", function (e) {
            if (e.target.classList.contains("removeAction")) {
                e.target.closest("tr").remove();
            }
        });
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

</script>
<script>
function toggleType() {
    const publicRadio = document.getElementById('public'); // Checkbox "Public"
    const priveRadio = document.getElementById('prive');   // Checkbox "Privé"
    const optionsPrive = document.getElementById('optionsPrive'); // Section pour "Entreprise" ou "Individu"
    const entrepriseFields = document.getElementById('entrepriseFields'); // Champs pour "Entreprise"
    const individuFields = document.getElementById('individuFields'); // Champs pour "Individu"
    const acteurSelect = document.getElementById('acteurSelect');

    // Si "Public" est sélectionné
    if (publicRadio.checked) {
        optionsPrive.classList.add('d-none'); // Cacher les options pour "Privé"
        entrepriseFields.classList.add('d-none'); // Cacher les champs "Entreprise"
        individuFields.classList.add('d-none'); // Cacher les champs "Individu"
        fetchActeurs('Public');
    }
    // Si "Privé" est sélectionné
    else if (priveRadio.checked) {
        optionsPrive.classList.remove('d-none'); // Afficher les options pour "Entreprise" ou "Individu"
        acteurSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
        // Vérifier si une sous-option ("Entreprise" ou "Individu") est déjà sélectionnée
        const entrepriseRadio = document.getElementById('entreprise');
        const individuRadio = document.getElementById('individu');

        if (entrepriseRadio.checked) {
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
        }
    }else{
        optionsPrive.classList.add('d-none');
        acteurSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
    }
}

// Fonction pour basculer entre "Entreprise" et "Individu" lorsque "Privé" est sélectionné
function togglePriveFields() {
    const entrepriseRadio = document.getElementById('entreprise'); // Radio "Entreprise"
    const individuRadio = document.getElementById('individu');     // Radio "Individu"
    const entrepriseFields = document.getElementById('entrepriseFields'); // Champs "Entreprise"
    const individuFields = document.getElementById('individuFields'); // Champs "Individu"
    const acteurSelect = document.getElementById('acteurSelect');

    // Si "Entreprise" est sélectionné
    if (entrepriseRadio.checked) {
        fetchActeurs('Privé', 'Entreprise');
        entrepriseFields.classList.remove('d-none'); // Afficher les champs "Entreprise"
        individuFields.classList.add('d-none'); // Cacher les champs "Individu"
    }
    // Si "Individu" est sélectionné
    else if (individuRadio.checked) {
        fetchActeurs('Privé', 'Individu');
        individuFields.classList.remove('d-none'); // Afficher les champs "Individu"
        entrepriseFields.classList.add('d-none'); // Cacher les champs "Entreprise"
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


    ////////////////MAITRE D'OEUVRE
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
            const moeEntrepriseFields = document.getElementById('moeEntrepriseFields');
            const individuFields = document.getElementById('moeIndividuFields');
            const acteurMoeSelect = document.getElementById('acteurMoeSelect');

            if (publicRadio.checked) {
                optionsMoePrive.classList.add('d-none');
                moeEntrepriseFields.classList.add('d-none');
                individuFields.classList.add('d-none');
                fetchMoeActeurs('Public');
            } else if (priveRadio.checked) {
                optionsMoePrive.classList.remove('d-none');
                acteurMoeSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';

                const entrepriseRadio = document.getElementById('moeEntreprise');
                const individuRadio = document.getElementById('moeIndividu');

                if (entrepriseRadio.checked) {
                    moeEntrepriseFields.classList.remove('d-none');
                    individuFields.classList.add('d-none');
                } else if (individuRadio.checked) {
                    individuFields.classList.remove('d-none');
                    moeEntrepriseFields.classList.add('d-none');
                } else {
                    moeEntrepriseFields.classList.add('d-none');
                    individuFields.classList.add('d-none');
                }
            } else {
                optionsMoePrive.classList.add('d-none');
                acteurMoeSelect.innerHTML = '<option value="">Sélectionnez un acteur</option>';
            }
        }

        function toggleMoeFields() {
            const entrepriseRadio = document.getElementById('moeEntreprise');
            const individuRadio = document.getElementById('moeIndividu');
            const moeEntrepriseFields = document.getElementById('moeEntrepriseFields');
            const individuFields = document.getElementById('moeIndividuFields');
            const typeOuvrage = document.querySelector('input[name="type_ouvrage"]:checked')?.value;

            if (entrepriseRadio.checked) {
                fetchMoeActeurs(typeOuvrage, 'Entreprise');
                moeEntrepriseFields.classList.remove('d-none');
                individuFields.classList.add('d-none');
            } else if (individuRadio.checked) {
                fetchMoeActeurs(typeOuvrage, 'Individu');
                individuFields.classList.remove('d-none');
                moeEntrepriseFields.classList.add('d-none');
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



    ///////////////////////// CHEF DE PROJET ////////////////////////////////
    document.addEventListener("DOMContentLoaded", function () {
        const chefProjetInput = document.getElementById('chefProjetInput');
        const chefProjetList = document.getElementById('chefProjetList');

        chefProjetInput.addEventListener('keyup', function () {
            searchChefProjet();
        });
    });

    function searchChefProjet() {
        const input = document.getElementById('chefProjetInput');
        const list = document.getElementById('chefProjetList');
        const query = input.value.trim();

        if (query.length < 2) {
            list.innerHTML = '';
            list.classList.add('d-none');
            return;
        }

        fetch(`/get-chefs-projet?search=${query}`)
            .then(response => response.json())
            .then(data => {
                list.innerHTML = '';
                list.classList.remove('d-none');

                if (data.length === 0) {
                    let li = document.createElement('li');
                    li.classList.add('list-group-item', 'text-primary');
                    li.innerHTML = `<i class="fas fa-plus-circle"></i> Ajouter "${query}"`;
                    li.onclick = () => addNewChefProjet(query);
                    list.appendChild(li);
                } else {
                    data.forEach(acteur => {
                        let li = document.createElement('li');
                        li.classList.add('list-group-item', 'list-group-item-action');
                        li.textContent = acteur.libelle_long;
                        li.onclick = () => selectChefProjet(acteur);
                        list.appendChild(li);
                    });
                }
            })
            .catch(error => console.error("Erreur lors de la recherche :", error));
    }

    function selectChefProjet(acteur) {
        document.getElementById('chefProjetInput').value = acteur.libelle_long;
        document.getElementById('chefProjetList').innerHTML = '';
        document.getElementById('chefProjetList').classList.add('d-none');

        // Remplir les champs automatiquement
        document.getElementById('chefEmail').value = acteur.email || '';
        document.getElementById('chefTelephoneMobille').value = acteur.telephone || '';

        // Cacher le formulaire d'ajout
        document.getElementById('chefProjetFields').classList.add('d-none');
    }

    function addNewChefProjet(nom) {
        document.getElementById('chefProjetInput').value = nom;
        document.getElementById('chefProjetList').innerHTML = '';
        document.getElementById('chefProjetList').classList.add('d-none');

        // Afficher le formulaire pour renseigner les informations
        document.getElementById('chefProjetFields').classList.remove('d-none');
    }



</script>

@endsection
