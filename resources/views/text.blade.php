<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naissance de Projet - BTP-PROJECT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">


    <!-- Styles de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <!-- Script de Leaflet -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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
        .step.active {
            display: block;
        }
        .progress {
            height: 5px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center mb-4 text-primary">📌 Demande de Projet - BTP-PROJECT</h2>

    <!-- Barre de progression -->
    <div class="progress mb-4">
        <div class="progress-bar bg-success" role="progressbar" style="width: 20%;" id="progressBar"></div>
    </div>

    <form id="projectForm">
        <!-- 🟢 Étape 1 : Informations Générales -->
        <div class="step active" id="step-1">
            <h5 class="text-secondary">📋 Informations Générales</h5>
            <div class="row">
                <div class="col">
                    <label>Nom du Projet *</label>
                    <input type="text" class="form-control" placeholder="Nom du projet" required>
                </div>
                <div class="col">
                    <label>Groupe de Projet *</label>
                    <select class="form-control">
                        <option>Bâtiment</option>
                        <option>Transport</option>
                        <option>Informatique & Télécom</option>
                        <option>Eau & Assainissement</option>
                        <option>Énergies</option>
                    </select>
                </div>

            </div>
            <div class="row">
                <div class="col">
                    <label for="Domaine">Domaine *</label>
                    <select name="domaine" id="domaine" class="form-control">
                        <option value=""></option>
                    </select>
                </div>
                <div class="col">
                    <label for="SousDomaine">Sous-Domaine *</label>
                    <select name="SousDomaine" id="SousDomaine" class="form-control">
                        <option value=""></option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label>Objectif du projet *</label>
                <textarea class="form-control" rows="3" placeholder="Décrivez l'objectif du projet"></textarea>
            </div>
            <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
        </div>

        <!-- 🟠 Étape 2 : Localisation -->
        <div class="step" id="step-2">
            <h5 class="text-secondary">🌍 Localisation</h5>
            <div class="row">
                <div class="col">
                    <!-- Inclure la bibliothèque Leaflet -->
                    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

                    <!-- Sélection du Pays -->
                    <div class="mb-3">
                        <label>Pays *</label>
                        <select class="form-control" id="paysSelect">
                            <option value="">Sélectionnez un pays</option>
                            <option value="CI">Côte d'Ivoire</option>
                            <option value="SN">Sénégal</option>
                            <option value="GA">Gabon</option>
                            <option value="BI">Burundi</option>
                            <option value="CD">RDC</option>
                        </select>
                    </div>

                    <!-- Sélection des Niveaux Administratifs -->
                    <div class="mb-3">
                        <label>Niveau 1 (Ex: Région) *</label>
                        <select class="form-control" id="niveau1Select" disabled>
                            <option value="">Sélectionnez un niveau</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Niveau 2 (Ex: Département) *</label>
                        <select class="form-control" id="niveau2Select" disabled>
                            <option value="">Sélectionnez un niveau</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Niveau 3 (Ex: Commune) *</label>
                        <select class="form-control" id="niveau3Select" disabled>
                            <option value="">Sélectionnez un niveau</option>
                        </select>
                    </div>



                    <!-- Coordonnées GPS Automatiques -->
                    <div class="row">
                        <div class="col-md-6">
                            <label>Latitude</label>
                            <input type="text" id="latitude" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label>Longitude</label>
                            <input type="text" id="longitude" class="form-control" readonly>
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
        <div class="step" id="step-3">
            <h5 class="text-secondary">🧍 Bénéficiaires</h5>
            <div class="row">
                <div class="col-md-1">
                    <label for="nOrdre">N° d’ordre</label>
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


        <!-- 🔵 Étape : Financement -->
        <div class="step" id="step-4">
            <h5 class="text-secondary">💰 Ressources Financières</h5>
            <div class="mb-3">
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


        <!-- 🟣 Étape 4 : Acteurs du projet -->
        <div class="step" id="step-5">
            <h5 class="text-secondary">👷 Informations Techniques et Acteurs</h5>
            <div class="row">
                <!-- Sélection dynamique du maître d’ouvrage -->
                <div class="col">
                    <label>Maître d’ouvrage *</label>
                    <input type="text" id="maitreOuvrageInput" class="form-control" placeholder="Rechercher un maître d’ouvrage...">
                    <ul class="list-group" id="maitreOuvrageList"></ul>
                </div>

                <!-- Sélection dynamique du maître d’œuvre -->
                <div class="col">
                    <label>Maître d’œuvre *</label>
                    <input type="text" id="maitreOeuvreInput" class="form-control" placeholder="Rechercher un maître d’œuvre...">
                    <ul class="list-group" id="maitreOeuvreList"></ul>
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
    document.addEventListener("DOMContentLoaded", function () {
        // Initialisation de la carte Leaflet
        let map = L.map('countryMap').setView([7.539989, -5.54708], 6); // Par défaut sur la Côte d'Ivoire

        // Ajouter un fond de carte (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Initialiser le marqueur (il sera mis à jour après un clic)
        let marker = null;

        // Gestion du clic sur la carte
        map.on('click', function (e) {
            let lat = e.latlng.lat.toFixed(6);
            let lng = e.latlng.lng.toFixed(6);

            // Mettre à jour les champs de latitude et longitude
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            // Ajouter ou déplacer le marqueur
            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });

        // 📌 Zoom automatique en fonction du pays sélectionné
        document.getElementById("paysSelect").addEventListener("change", function () {
            let selectedCountry = this.value;
            let countryCoordinates = {
                "CI": [7.539989, -5.54708],   // Côte d'Ivoire
                "SN": [14.4974, -14.4524],   // Sénégal
                "GA": [-0.8037, 11.6094],    // Gabon
                "BI": [-3.3731, 29.9189],    // Burundi
                "CD": [-4.0383, 21.7587]     // RDC
            };

            if (selectedCountry && countryCoordinates[selectedCountry]) {
                map.setView(countryCoordinates[selectedCountry], 7);
            }
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

</body>
</html>
