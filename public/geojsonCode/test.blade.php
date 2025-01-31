@extends('layouts.app')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Naissance de Projet - BTP-PROJECT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
    <h2 class="text-center mb-4 text-primary">📌 Naissance de Projet - BTP-PROJECT</h2>

    <!-- Barre de progression -->
    <div class="progress mb-4">
        <div class="progress-bar bg-success" role="progressbar" style="width: 20%;" id="progressBar"></div>
    </div>

    <form id="projectForm">
        <!-- 🟢 Étape 1 : Informations Générales -->
        <div class="step active" id="step-1">
            <h5 class="text-secondary">📋 Informations Générales</h5>
            <div class="mb-3">
                <label>Nom du Projet *</label>
                <input type="text" class="form-control" placeholder="Nom du projet" required>
            </div>
            <div class="mb-3">
                <label>Groupe de Projet *</label>
                <select class="form-control">
                    <option>Bâtiment</option>
                    <option>Transport</option>
                    <option>Informatique & Télécom</option>
                    <option>Eau & Assainissement</option>
                    <option>Énergies</option>
                </select>
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
            <div class="mb-3">
                <label>Pays *</label>
                <select class="form-control">
                    <option>Côte d'Ivoire</option>
                    <option>Sénégal</option>
                    <option>Gabon</option>
                    <option>Burundi</option>
                    <option>RDC</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Région/Département *</label>
                <input type="text" class="form-control" placeholder="Entrez la région">
            </div>
            <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
            <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
        </div>

        <!-- 🔵 Étape 3 : Financement -->
        <div class="step" id="step-3">
            <h5 class="text-secondary">💰 Informations Financières</h5>
            <div class="mb-3">
                <label>Type de Financement *</label>
                <select class="form-control">
                    <option>Public</option>
                    <option>Privé</option>
                    <option>Mixte</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Montant Estimé (FCFA) *</label>
                <input type="number" class="form-control" placeholder="Ex: 50000000" required>
            </div>
            <button type="button" class="btn btn-secondary" onclick="prevStep()">Précédent</button>
            <button type="button" class="btn btn-primary" onclick="nextStep()">Suivant</button>
        </div>

        <!-- 🟣 Étape 4 : Acteurs du projet -->
        <div class="step" id="step-4">
            <h5 class="text-secondary">👷 Informations Techniques et Acteurs</h5>
            <div class="row">
                <!-- Sélection dynamique des bailleurs -->
                <div class="col">
                    <label>Bailleur *</label>
                    <input type="text" id="bailleurInput" class="form-control" placeholder="Rechercher un bailleur...">
                    <ul class="list-group" id="bailleurList"></ul>
                </div>

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
        <div class="step" id="step-5">
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
    const totalSteps = 5;
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
            { id: "bailleurInput", list: "bailleurList"},
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

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
