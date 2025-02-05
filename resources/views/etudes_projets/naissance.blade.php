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
            background-color: black;
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
                          <!-- Étape 3 : Informations sur le Maître d’Ouvrage -->
                            <div class="step active" id="step-1">
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

                                <!-- ✅ Champs spécifiques pour Entreprise -->
                                <div class="row mt-3 d-none" id="entrepriseFields">
                                    <h6>Détails pour l’Entreprise</h6>
                                    <div class="col-md-6">
                                        <label>Nom de l'Entreprise :</label>
                                        <input type="text" class="form-control" placeholder="Nom de l'entreprise">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Adresse de l'Entreprise :</label>
                                        <input type="text" class="form-control" placeholder="Adresse">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label>Activité Principale :</label>
                                        <input type="text" class="form-control" placeholder="Activité principale">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label>Numéro d’Immatriculation :</label>
                                        <input type="text" class="form-control" placeholder="Numéro d'immatriculation">
                                    </div>
                                </div>

                                  <!-- ✅ Champs spécifiques pour Individu -->
                                <div class="row mt-3 d-none" id="individuFields">
                                    <h6>Détails pour l’Individu</h6>
                                    <div class="col-md-6">
                                        <label>Nom :</label>
                                        <input type="text" class="form-control" placeholder="Nom">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Prénom :</label>
                                        <input type="text" class="form-control" placeholder="Prénom">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label>Numéro de Téléphone :</label>
                                        <input type="text" class="form-control" placeholder="Téléphone">
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <label>Adresse E-mail :</label>
                                        <input type="email" class="form-control" placeholder="Email">
                                    </div>
                                </div>
                                <!-- ✅ Zone de description complémentaire -->
                                <div class="row">
                                    <label>Description / Observations</label>
                                    <textarea class="form-control" id="descriptionMO" rows="3" placeholder="Ajoutez des précisions sur le Maître d’Ouvrage (ex: Budget, contraintes, accords...)"></textarea>
                                </div><br>

                                <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
                            </div>
                            <!-- Étape : Informations sur le Maître d’Œuvre -->
                            <div class="step" id="step-2">
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
                                    <div class="col">
                                        <!-- Sélection de l’Acteur -->
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
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="entreprise-localisation-tab" data-bs-toggle="tab" data-bs-target="#entreprise-localisation" type="button" role="tab" aria-controls="entreprise-localisation" aria-selected="false">Localisation</button>
                                                    </li>
                                                </ul>
                                                <div class="tab-content mt-3" id="entrepriseTabsContent">
                                                    <!-- Tab 1: Informations Générales -->
                                                    <div class="tab-pane fade show active" id="entreprise-general" role="tabpanel" aria-labelledby="entreprise-general-tab">
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
                                                    <div class="tab-pane fade" id="entreprise-legal" role="tabpanel" aria-labelledby="entreprise-legal-tab">
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
                                                    <div class="tab-pane fade" id="entreprise-contact" role="tabpanel" aria-labelledby="entreprise-contact-tab">
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <label>Code postale</label>
                                                                <input type="text" class="form-control" name="CodePostaleEntreprise" placeholder="Code postale">
                                                            </div>
                                                            <div class="col-6">
                                                                <label>Adresse postale</label>
                                                                <input type="text" class="form-control" name="AdressePostaleEntreprise" placeholder="Code postale">
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
                                                    <!--Tab 4: Localisation-->
                                                    <div class="tab-pane fade" id="entreprise-localisation" role="tabpanel" aria-labelledby="entreprise-localisation-tab">
                                                        <div class="row">
                                                            <label>Pays de localisation</label>
                                                            <div class="col-6">
                                                                <label>Pays *</label>
                                                                <select class="form-control" id="paysSelect2">
                                                                    <option value="">Sélectionnez un pays</option>
                                                                    @foreach ($Pays as $alpha3 => $nom_fr_fr)
                                                                        <option value="{{ $alpha3 }}">{{ $nom_fr_fr }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="col-6">
                                                                <label id="niveau1Label2">Niveau 1 *</label>
                                                                <select class="form-control" id="niveau1Select2" disabled>
                                                                    <option value="">Sélectionnez un niveau</option>
                                                                </select>
                                                            </div>

                                                            <div class="col-6">
                                                                <label id="niveau2Label2">Niveau 2 *</label>
                                                                <select class="form-control" id="niveau2Select2" disabled>
                                                                    <option value="">Sélectionnez un niveau</option>
                                                                </select>
                                                            </div>

                                                            <div class="col-6">
                                                                <label id="niveau3Label2">Niveau 3 *</label>
                                                                <select class="form-control" id="niveau3Select2" disabled>
                                                                    <option value="">Sélectionnez un niveau</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- MOE Individu Fields -->
                                        <div class="row mt-3 d-none" id="moeIndividuFields">
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
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="individu-localisation-tab" data-bs-toggle="tab" data-bs-target="#individu-localisation" type="button" role="tab" aria-controls="individu-localisation" aria-selected="false">Localisation</button>
                                                    </li>
                                                </ul>
                                                <div class="tab-content mt-3" id="individuTabsContent">
                                                    <!-- Tab 1: Informations Personnelles -->
                                                    <div class="tab-pane fade show active" id="individu-general" role="tabpanel" aria-labelledby="individu-general-tab">
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
                                                    <div class="tab-pane fade" id="individu-contact" role="tabpanel" aria-labelledby="individu-contact-tab">
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
                                                            <div class="col-md-6">
                                                                <label>Téléphone Bureau:</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label>Téléphone mobile:</label>
                                                                <input type="text" class="form-control" placeholder="Téléphone">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Tab 3: Informations Administratives -->
                                                    <div class="tab-pane fade" id="individu-admin" role="tabpanel" aria-labelledby="individu-admin-tab">
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

                                                    <!--Tab 4: Localisation-->
                                                    <div class="tab-pane fade" id="individu-localisation" role="tabpanel" aria-labelledby="individu-localisation-tab">
                                                        <div class="row">
                                                            <label>Pays de localisation</label>
                                                            <div class="col-6">
                                                                <label>Pays *</label>
                                                                <select class="form-control" id="paysSelect3">
                                                                    <option value="">Sélectionnez un pays</option>
                                                                    @foreach ($Pays as $alpha3 => $nom_fr_fr)
                                                                        <option value="{{ $alpha3 }}">{{ $nom_fr_fr }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="col-6">
                                                                <label id="niveau1Label3">Niveau 1 *</label>
                                                                <select class="form-control" id="niveau1Select3" disabled>
                                                                    <option value="">Sélectionnez un niveau</option>
                                                                </select>
                                                            </div>

                                                            <div class="col-6">
                                                                <label id="niveau2Label3">Niveau 2 *</label>
                                                                <select class="form-control" id="niveau2Select3" disabled>
                                                                    <option value="">Sélectionnez un niveau</option>
                                                                </select>
                                                            </div>

                                                            <div class="col-6">
                                                                <label id="niveau3Label3">Niveau 3 *</label>
                                                                <select class="form-control" id="niveau3Select3" disabled>
                                                                    <option value="">Sélectionnez un niveau</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                </div>                                <!-- Champs pour Entreprise -->



                                <div class="row mt-3">
                                    <label>Description / Observations</label>
                                    <textarea class="form-control" id="descriptionMoe" rows="3" placeholder="Ajoutez des précisions sur le Maître d’œuvre"></textarea>
                                </div><br>

                                <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
                            </div>


                            <!-- 🟣 Étape 4 : Acteurs du projet -->
                            <div class="step" id="step-2">
                                <h5 class="text-secondary">👷 Acteurs</h5>
                                <div class="row">
                                    <!-- Sélection dynamique du maître d’œuvre -->
                                    <div class="col">
                                        <label>Maître d’œuvre *</label>

                                    </div>

                                    <!-- Sélection dynamique du chef de projet -->
                                    <div class="col">
                                        <label>Chef de projet *</label>
                                        <input type="text" id="chefProjetInput" class="form-control" placeholder="Rechercher un chef de projet...">
                                        <ul class="list-group" id="chefProjetList"></ul>
                                    </div>

                                </div><br>

                                <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
                            </div>


                            <!-- 🔵 Étape : Financement -->
                            <div class="step" id="step-3">
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

                                <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
                            </div>



                            <!-- 🟢 Étape 1 : Informations Générales -->
                            <div class="step" id="step-4">
                                <h5 class="text-secondary">📋 Informations Générales</h5>
                                <div class="row">
                                    <div class="col">
                                        <label>Nom du Projet *</label>
                                        <input type="text" class="form-control" placeholder="Nom du projet" required>
                                    </div>
                                    <div class="col">
                                        <label>Groupe de Projet *</label>
                                        <select class="form-control">
                                            <option>Sélectionner un groupe</option>
                                            @foreach ($GroupeProjets as $groupe)
                                            <option value="{{ $groupe->code }}">{{ $groupe->libelle }}</option>
                                            @endforeach
                                        </select>
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
                                </div>
                                <div class="mb-3">
                                    <label>Objectif du projet *</label>
                                    <textarea class="form-control" rows="3" placeholder="Décrivez l'objectif du projet"></textarea>
                                </div>

                                <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
                            </div>

                            <!-- 🟠 Étape 2 : Localisation -->
                            <div class="step" id="step-5">
                                <h5 class="text-secondary">🌍 Localisation</h5>
                                <div class="row">
                                    <div class="col">
                                        <!-- Inclure la bibliothèque Leaflet -->
                                        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                                        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

                                        <div class="mb-3">
                                            <label>Pays *</label>
                                            <select class="form-control" id="paysSelect">
                                                <option value="">Sélectionnez un pays</option>
                                                @foreach ($Pays as $alpha3 => $nom_fr_fr)
                                                    <option value="{{ $alpha3 }}">{{ $nom_fr_fr }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label id="niveau1Label">Niveau 1 *</label>
                                            <select class="form-control" id="niveau1Select" disabled>
                                                <option value="">Sélectionnez un niveau</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label id="niveau2Label">Niveau 2 *</label>
                                            <select class="form-control" id="niveau2Select" disabled>
                                                <option value="">Sélectionnez un niveau</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label id="niveau3Label">Niveau 3 *</label>
                                            <select class="form-control" id="niveau3Select" disabled>
                                                <option value="">Sélectionnez un niveau</option>
                                            </select>
                                        </div>


                                        <!-- Coordonnées GPS Automatiques -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Latitude</label>
                                                <input type="text" id="latitude" class="form-control" >
                                            </div>
                                            <div class="col-md-6">
                                                <label>Longitude</label>
                                                <input type="text" id="longitude" class="form-control" >
                                            </div>
                                        </div>

                                        <!-- Intégration du fichier JS -->
                                        <script src="{{ asset('geojsonCode/map.js') }}"></script>

                                    </div>
                                    <div class="col">
                                        <!-- Carte Interactive pour Sélectionner l'Emplacement -->
                                        <div class="mb-3">
                                            <label>📍 Sélectionner l'Emplacement sur la Carte</label>
                                            <div id="countryMap" style="height: 400px; border: 1px solid #ddd;"></div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
                            </div>

                            <!-- 🔵 Étape : Bénéficiaire -->
                            <div class="step" id="step-6">
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

                                <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>

                            </div>






                            <!-- 📜 Modal pour la liste des documents -->
                            <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
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
                            <!-- 🟡 Étape 5 : Documents -->
                            <div class="step" id="step-6">
                                <h5 class="text-secondary">📎 Documents et Pièces Justificatives</h5>
                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#documentModal">
                                    📜 Liste des documents à fournir
                                </button>
                                <div class="upload-box" onclick="document.getElementById('fileUpload').click();">
                                    <p><i class="fas fa-upload"></i> Cliquez ici ou glissez vos fichiers</p>
                                    <input type="file" id="fileUpload" class="d-none" multiple>
                                </div>
                                <div class="uploaded-files mt-2" id="uploadedFiles"></div>
                                <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
                                <button type="submit" class="btn btn-success">Soumettre</button>
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
    const totalSteps = 6;
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
        const fields = [
            { id: "bailleur", list: "bailleurList"},
            { id: "maitreOuvrageInput", list: "maitreOuvrageList" },
            { id: "maitreOeuvreInput", list: "maitreOeuvreList" },
            { id: "chefProjetInput", list: "chefProjetList"}
        ];

        fields.forEach(field => {
            let input = document.getElementById(field.id);
            let list = document.getElementById(field.list);

            input.addEventListener("keyup", function () {
                let searchValue = input.value.trim();
                if (searchValue.length > 1) {
                    fetch(`/api/acteurs?search=${searchValue}`)
                        .then(response => response.json())
                        .then(data => {
                            list.innerHTML = "";
                            data.forEach(item => {
                                let li = document.createElement("li");
                                li.classList.add("list-group-item", "list-group-item-action");
                                li.textContent = item.libelle_long;
                                li.textContent = item.libelle_court;
                                li.onclick = () => {
                                    input.value = item.libelle_long;
                                    input.value = item.libelle_court;
                                    list.innerHTML = "";
                                };
                                list.appendChild(li);
                            });

                            // Option pour ajouter une nouvelle personne
                            let addNewOption = document.createElement("li");
                            addNewOption.classList.add("list-group-item", "text-primary");
                            addNewOption.innerHTML = `<i class="fas fa-plus-circle"></i> Ajouter "${searchValue}"`;
                            addNewOption.onclick = () => {
                                addNewActor( searchValue);
                                input.value = searchValue;
                                list.innerHTML = "";
                            };
                            list.appendChild(addNewOption);
                        });
                } else {
                    list.innerHTML = "";
                }
            });
        });

        function addNewActor( name) {
            fetch('/api/acteurs', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({  name })
            })
                .then(response => response.json())
                .then(data => alert("Nouvel acteur ajouté avec succès !"));
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
    // Attendre le chargement complet du DOM
    document.addEventListener("DOMContentLoaded", function () {
        // Définition des coordonnées GPS des pays (alpha3 -> lat/lng)
        const paysCoordonnees = {
            "CIV": { lat: 7.539989, lng: -5.54708 },  // Côte d'Ivoire
            "SEN": { lat: 14.497401, lng: -14.452362 }, // Sénégal
            "GAB": { lat: -0.803689, lng: 11.609444 }, // Gabon
            "BDI": { lat: -3.373056, lng: 29.918886 }, // Burundi
            "COD": { lat: -4.038333, lng: 21.758664 }, // RDC
            "NER": { lat: 17.607789, lng: 8.081666 }, // Niger
            "MLI": { lat: 17.570692, lng: -3.996166 }, // Mali
            "BFA": { lat: 12.238333, lng: -1.561593 }, // Burkina Faso
            "TCD": { lat: 15.454166, lng: 18.732207 }, // Tchad
            "COG": { lat: -0.228021, lng: 15.827659 }  // Congo
        };

        // Initialisation de la carte Leaflet sur un point par défaut (Côte d'Ivoire)
        var map = L.map('countryMap').setView([7.539989, -5.54708], 5);

        // Ajout d'une couche OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Ajouter un marqueur initial (invisible au début)
        var marker = L.marker([7.539989, -5.54708], { draggable: true }).addTo(map);
        marker.setOpacity(0); // Rendre invisible tant qu'il n'est pas utilisé

        // Fonction pour centrer la carte sur un pays sélectionné
        function centrerCarteSurPays(alpha3) {
            if (paysCoordonnees[alpha3]) {
                var coords = paysCoordonnees[alpha3];

                // Déplacer la carte et zoomer sur le pays
                map.setView([coords.lat, coords.lng], 6);

                // Déplacer et afficher le marqueur
                marker.setLatLng([coords.lat, coords.lng]);
                marker.setOpacity(1); // Rendre visible

                // Mettre à jour les champs de latitude et longitude
                document.getElementById("latitude").value = coords.lat;
                document.getElementById("longitude").value = coords.lng;
            } else {
                console.warn("Pays non trouvé dans paysCoordonnees :", alpha3);
            }
        }

        // Événement : Quand on change de pays dans la liste déroulante
        document.getElementById("paysSelect").addEventListener("change", function () {
            var selectedPays = this.value;
            centrerCarteSurPays(selectedPays);
        });
        document.getElementById("paysSelect2").addEventListener("change", function () {
            var selectedPays = this.value;
            centrerCarteSurPays(selectedPays);
        });
        document.getElementById("paysSelect3").addEventListener("change", function () {
            var selectedPays = this.value;
            centrerCarteSurPays(selectedPays);
        });

        // Événement : Quand on clique sur la carte, ajouter un marqueur
        map.on('click', function (e) {
            var lat = e.latlng.lat.toFixed(6);
            var lng = e.latlng.lng.toFixed(6);

            // Déplacer le marqueur sur l'endroit cliqué
            marker.setLatLng([lat, lng]);
            marker.setOpacity(1); // Rendre visible

            // Remplir automatiquement les champs Latitude et Longitude
            document.getElementById("latitude").value = lat;
            document.getElementById("longitude").value = lng;
        });

        // Événement : Si on déplace le marqueur, mettre à jour les coordonnées
        marker.on('dragend', function (e) {
            var newCoords = e.target.getLatLng();
            document.getElementById("latitude").value = newCoords.lat.toFixed(6);
            document.getElementById("longitude").value = newCoords.lng.toFixed(6);
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
    document.addEventListener('DOMContentLoaded', function () {
        const paysSelect = document.getElementById('paysSelect');
        const paysSelect2 = document.getElementById('paysSelect2');
        const paysSelect3 = document.getElementById('paysSelect3');

        const niveau1Label = document.getElementById('niveau1Label');
        const niveau2Label = document.getElementById('niveau2Label');
        const niveau3Label = document.getElementById('niveau3Label');
        const niveau1Select = document.getElementById('niveau1Select');
        const niveau2Select = document.getElementById('niveau2Select');
        const niveau3Select = document.getElementById('niveau3Select');

        const niveau1Label2 = document.getElementById('niveau1Label2');
        const niveau2Label2 = document.getElementById('niveau2Label2');
        const niveau3Label2 = document.getElementById('niveau3Label2');
        const niveau1Select2 = document.getElementById('niveau1Select2');
        const niveau2Select2 = document.getElementById('niveau2Select2');
        const niveau3Select2 = document.getElementById('niveau3Select2');

        const niveau1Label3 = document.getElementById('niveau1Label3');
        const niveau2Label3 = document.getElementById('niveau2Label3');
        const niveau3Label3 = document.getElementById('niveau3Label3');
        const niveau1Select3 = document.getElementById('niveau1Select3');
        const niveau2Select3 = document.getElementById('niveau2Select3');
        const niveau3Select3 = document.getElementById('niveau3Select3');

        // 🟢 Lorsque l'utilisateur sélectionne un pays
        paysSelect.addEventListener('change', function () {
            const alpha3 = this.value;

            if (!alpha3) {
                resetLabelAndSelect(niveau1Label, niveau1Select, "Niveau 1 *");
                resetLabelAndSelect(niveau2Label, niveau2Select, "Niveau 2 *");
                resetLabelAndSelect(niveau3Label, niveau3Select, "Niveau 3 *");
                return;
            }

            // Récupérer les niveaux administratifs et charger les localités associées
            fetch(`/pays/${alpha3}/niveaux`)
                .then(response => response.json())
                .then(data => {
                    resetLabelAndSelect(niveau1Label, niveau1Select, "Niveau 1 *");
                    resetLabelAndSelect(niveau2Label, niveau2Select, "Niveau 2 *");
                    resetLabelAndSelect(niveau3Label, niveau3Select, "Niveau 3 *");

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
                })
                .catch(error => console.error('Erreur lors du chargement des niveaux :', error));
        });
        // 🟢 Lorsque l'utilisateur sélectionne un pays Entreprise
        paysSelect2.addEventListener('change', function () {
        const alpha3 = this.value;

        if (!alpha3) {
            resetLabelAndSelect(niveau1Label2, niveau1Select2, "Niveau 1 *");
            resetLabelAndSelect(niveau2Label2, niveau2Select2, "Niveau 2 *");
            resetLabelAndSelect(niveau3Label2, niveau3Select2, "Niveau 3 *");
            return;
        }

        // Récupérer les niveaux administratifs et charger les localités associées
        fetch(`/pays/${alpha3}/niveaux`)
            .then(response => response.json())
            .then(data => {
                resetLabelAndSelect(niveau1Label2, niveau1Select2, "Niveau 1 *");
                resetLabelAndSelect(niveau2Label2, niveau2Select2, "Niveau 2 *");
                resetLabelAndSelect(niveau3Label2, niveau3Select2, "Niveau 3 *");

                data.forEach(niveau => {
                    if (niveau.num_niveau_decoupage === 1) {
                        niveau1Label2.textContent = niveau.libelle_decoupage + " *";
                        niveau1Select2.disabled = false;
                        loadLocalites(alpha3, 1, null, niveau1Select2);
                    } else if (niveau.num_niveau_decoupage === 2) {
                        niveau2Label2.textContent = niveau.libelle_decoupage + " *";
                        niveau2Select2.disabled = true;
                    } else if (niveau.num_niveau_decoupage === 3) {
                        niveau3Label2.textContent = niveau.libelle_decoupage + " *";
                        niveau3Select2.disabled = true;
                    }
                });
            })
            .catch(error => console.error('Erreur lors du chargement des niveaux :', error));
        });
        // 🟢 Lorsque l'utilisateur sélectionne un pays Entreprise
        paysSelect3.addEventListener('change', function () {
        const alpha3 = this.value;

        if (!alpha3) {
            resetLabelAndSelect(niveau1Label3, niveau1Select3, "Niveau 1 *");
            resetLabelAndSelect(niveau2Label3, niveau2Select3, "Niveau 2 *");
            resetLabelAndSelect(niveau3Label3, niveau3Select3, "Niveau 3 *");
            return;
        }

        // Récupérer les niveaux administratifs et charger les localités associées
        fetch(`/pays/${alpha3}/niveaux`)
            .then(response => response.json())
            .then(data => {
                resetLabelAndSelect(niveau1Label3, niveau1Select3, "Niveau 1 *");
                resetLabelAndSelect(niveau2Label3, niveau2Select3, "Niveau 2 *");
                resetLabelAndSelect(niveau3Label3, niveau3Select3, "Niveau 3 *");

                data.forEach(niveau => {
                    if (niveau.num_niveau_decoupage === 1) {
                        niveau1Label3.textContent = niveau.libelle_decoupage + " *";
                        niveau1Select3.disabled = false;
                        loadLocalites(alpha3, 1, null, niveau1Select3);
                    } else if (niveau.num_niveau_decoupage === 2) {
                        niveau2Label3.textContent = niveau.libelle_decoupage + " *";
                        niveau2Select3.disabled = true;
                    } else if (niveau.num_niveau_decoupage === 3) {
                        niveau3Label3.textContent = niveau.libelle_decoupage + " *";
                        niveau3Select3.disabled = true;
                    }
                });
            })
            .catch(error => console.error('Erreur lors du chargement des niveaux :', error));
        });

        // 🟡 Lorsque le niveau 1 est sélectionné, charger les localités de niveau 2
        niveau1Select.addEventListener('change', function () {
            const alpha3 = paysSelect.value;
            const codeRattachement = this.value;
            if (codeRattachement) {
                loadLocalites(alpha3, 2, codeRattachement, niveau2Select);
            } else {
                resetSelect(niveau2Select);
            }
        });
        niveau1Select2.addEventListener('change', function () {
            const alpha3 = paysSelect2.value;
            const codeRattachement = this.value;
            if (codeRattachement) {
                loadLocalites(alpha3, 2, codeRattachement, niveau2Select2);
            } else {
                resetSelect(niveau2Select2);
            }
        });
        niveau1Select3.addEventListener('change', function () {
            const alpha3 = paysSelect3.value;
            const codeRattachement = this.value;
            if (codeRattachement) {
                loadLocalites(alpha3, 2, codeRattachement, niveau2Select3);
            } else {
                resetSelect(niveau2Select3);
            }
        });

        // 🟠 Lorsque le niveau 2 est sélectionné, charger les localités de niveau 3
        niveau2Select.addEventListener('change', function () {
            const alpha3 = paysSelect.value;
            const codeRattachement = this.value;
            if (codeRattachement) {
                loadLocalites(alpha3, 3, codeRattachement, niveau3Select);
            } else {
                resetSelect(niveau3Select);
            }
        });
        niveau2Select2.addEventListener('change', function () {
            const alpha3 = paysSelect2.value;
            const codeRattachement = this.value;
            if (codeRattachement) {
                loadLocalites(alpha3, 3, codeRattachement, niveau3Select2);
            } else {
                resetSelect(niveau3Select2);
            }
        });
        niveau2Select3.addEventListener('change', function () {
            const alpha3 = paysSelect3.value;
            const codeRattachement = this.value;
            if (codeRattachement) {
                loadLocalites(alpha3, 3, codeRattachement, niveau3Select3);
            } else {
                resetSelect(niveau3Select3);
            }
        });

        // 🔹 Fonction pour charger les localités d’un niveau donné
        function loadLocalites(alpha3, niveau, codeRattachement, selectElement) {
            let url = `/pays/${alpha3}/niveau/${niveau}/localites`;
            if (codeRattachement) {
                url += `?code_rattachement=${codeRattachement}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    resetSelect(selectElement);
                    data.forEach(localite => {
                        const option = document.createElement('option');
                        option.value = localite.code_rattachement; // Stocke le code_rattachement
                        option.textContent = localite.libelle;
                        selectElement.appendChild(option);
                    });
                    selectElement.disabled = false; // Active le select après chargement
                })
                .catch(error => console.error('Erreur lors du chargement des localités :', error));
        }

        // 🔹 Réinitialiser un label et un select
        function resetLabelAndSelect(labelElement, selectElement, defaultText) {
            labelElement.textContent = defaultText;
            resetSelect(selectElement);
        }

        // 🔹 Réinitialiser un select
        function resetSelect(selectElement) {
            selectElement.innerHTML = '<option value="">Sélectionnez</option>';
            selectElement.disabled = true;
        }
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


    ////////////////MAITRE D'OEUVRE
    document.addEventListener("DOMContentLoaded", function () {
        const type_ouvrage = document.querySelectorAll('input[name="type_ouvrage"]');
        type_ouvrage.forEach((checkbox) => {
            checkbox.addEventListener("change", function () {
                if (this.checked) {
                    type_ouvrage.forEach((cb) => {
                        if (cb !== this) cb.checked = false;
                    });
                }
            });
        });
    });
    // Gestion du Maître d’œuvre
    function toggleTypeMoe() {
        const publicRadio = document.getElementById('moePublic'); // Checkbox "Public"
        const priveRadio = document.getElementById('moePrive');   // Checkbox "Privé"
        const optionsMoePrive = document.getElementById('optionsMoePrive'); // Section pour "Entreprise" ou "Individu"
        const moeEntrepriseFields = document.getElementById('moeEntrepriseFields'); // Champs pour "Entreprise"
        const individuFields = document.getElementById('moeIndividuFields'); // Champs pour "Individu"
        const acteurMoeSelect = document.getElementById('acteurMoeSelect');

        // Si "Public" est sélectionné
        if (publicRadio.checked) {
            optionsMoePrive.classList.add('d-none'); // Cacher les options pour "Privé"
            moeEntrepriseFields.classList.add('d-none'); // Cacher les champs "Entreprise"
            individuFields.classList.add('d-none'); // Cacher les champs "Individu"
            fetchMoeActeurs('Public');
        }
        // Si "Privé" est sélectionné
        else if (priveRadio.checked) {
            optionsMoePrive.classList.remove('d-none'); // Afficher les options pour "Entreprise" ou "Individu"
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

        if (entrepriseRadio.checked) {
            fetchMoeActeurs('Privé', 'Entreprise');
            moeEntrepriseFields.classList.remove('d-none');
            individuFields.classList.add('d-none');
        } else if (individuRadio.checked) {
            fetchMoeActeurs('Privé', 'Individu');
            individuFields.classList.remove('d-none');
            moeEntrepriseFields.classList.add('d-none');
        }
    }

    function fetchMoeActeurs(type_ouvrage, priveType = null) {
        const acteurMoeSelect = document.getElementById('acteurMoeSelect');
        let url = `/get-acteurs?type_ouvrage=${type_ouvrage}`;

        if (priveType) {
            url += `&priveType=${priveType}`;
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

    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById('moePublic').addEventListener('change', toggleTypeMoe);
        document.getElementById('moePrive').addEventListener('change', toggleTypeMoe);
        document.getElementById('moeEntreprise').addEventListener('change', toggleMoeFields);
        document.getElementById('moeIndividu').addEventListener('change', toggleMoeFields);
    });

</script>

@endsection
