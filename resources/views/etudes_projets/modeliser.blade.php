@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
    alert("{{ session('success') }}");

</script>
@endif

<style>
    .card-content {
        display: flex;
        justify-content: center;
        /*align-items: center;*/
        min-height: auto;
    }
    .form-container {
        width: 100%;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .form-step {
        display: none;
    }
    .form-step.active {
        display: block;
    }

    .form-step textarea {
        width: 100%;
        padding: 8px;
        margin: 8px 0;
        border: 1px solid #ccc;
        border-radius: 4px;
    }
    .step-indicator {
        text-align: center;
        margin-bottom: 20px;
    }
    .step-indicator span {
        display: inline-block;
        width: 20px;
        height: 20px;
        background: #ddd;
        color: #333;
        border-radius: 50%;
        margin-right: 5px;
        line-height: 20px;
    }
    .step-indicator .active {
        background: #4CAF50;
        color: white;
    }
    .form-navigation {
        display: flex;
        justify-content: space-between;
    }
    .btn {
        padding: 10px 15px;
        border: none;
        color: white;
        cursor: pointer;
        background-color: #4CAF50;
        border-radius: 5px;
    }
</style>

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
                            <li class="breadcrumb-item"><a href="">Editions</a></li>
                            <li class="breadcrumb-item"><a href="">Annexe 3</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Fiche de collecte</li>

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
                    <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                        <h5 class="card-title">Naissance: Formulaire de collecte de données</h5>
                    </div>
                    <div style="text-align: center;">
                        <h5 class="card-title"></h5>
                    </div>
                </div>
                <div class="card-content">
                    <div class="form-container">
                        <div class="step-indicator">
                            <span class="step active">1</span>
                            <span class="step">2</span>
                            <span class="step">3</span>
                            <span class="step">4</span>
                        </div>

                        <form id="registrationForm">
                            <!-- Étape 1: Informations Générales -->
                            <div class="form-step active">
                                <h2>Informations Générales</h2>




                                <div class="row">
                                    <div class="col">
                                        <div class="col">
                                            <label for="">Nom de l'entreprise</label>
                                            <input type="text" name="nom" class="form-control" required>
                                        </div>
                                        <div class="col">
                                            <label for="">Type entreprise</label>
                                            <select name="type_entreprise" required class="form-select">
                                                <option value="">Type d'entreprise</option>
                                                <option value="morale">Morale</option>
                                                <option value="physique">Physique</option>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label for="">Nature des travaux</label>
                                            <select name="nature_travaux" id="nature_travaux" class="form-control">
                                                <option value="">Sélectionner la nature</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="col">
                                            <label for="">Nature de l'entreprise</label>
                                            <select name="nature_entreprise" required class="form-select">
                                                <option value="">Nature de l'entreprise</option>
                                                <option value="publique">Publique</option>
                                                <option value="privée">Privée</option>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label for="">Forme juridique</label>
                                            <select name="forme_juridique" required class="form-select">
                                                <option value="">Forme juridique</option>
                                                <option value="sarl">SARL</option>
                                                <option value="sa">SA</option>
                                                <option value="ei">EI</option>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <label for="">Adresse du siège social</label>
                                            <input type="text" name="adresse" required class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <br>
                                <div class="form-navigation">
                                    <button type="button" class="btn next-btn">Suivant</button>
                                </div>
                            </div>

                            <!-- Étape 2: Informations sur l'Identité et Représentant -->
                            <div class="form-step">
                                <h2>Informations sur l'Identité et Représentant</h2>
                                <div class="row">
                                    <div class="col">
                                        <div class="col">
                                            <label for="">Numéro d'identification</label>
                                            <input type="text" name="numero_identification" class="form-control" required>
                                        </div>
                                        <div class="col">
                                            <label for="">Numéro de TVA (si applicable)</label>
                                            <input type="text" name="numero_tva" class="form-control" >
                                        </div>
                                        <div class="col">
                                            <label for="">Nom du représentant légal</label>
                                            <input type="text" name="representant_nom" class="form-control"  required>
                                        </div>
                                        <div class="col">
                                            <label for="">Prénom du représentant légal</label>
                                            <input type="text" name="representant_prenom" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="col">
                                            <label for="">Date de naissance</label>
                                            <input type="date" name="date_naissance" class="form-control" required>
                                        </div>
                                        <div class="col">
                                            <label for="">Nationalité</label>
                                            <input type="text" name="nationalite" class="form-control" required>
                                        </div>
                                        <div class="col">
                                            <label for="">Téléphone</label>
                                            <input type="tel" name="contact_telephone" class="form-control" required>
                                        </div>
                                        <div class="col">
                                            <label for="">E-mail</label>
                                            <input type="email" name="contact_email" class="form-control" required>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="form-navigation">
                                    <button type="button" class="btn prev-btn">Précédent</button>
                                    <button type="button" class="btn next-btn">Suivant</button>
                                </div>
                            </div>

                            <!-- Étape 3: Informations Financières et Activités -->
                            <div class="form-step">
                                <h2>Informations Financières et Activités</h2>
                                <div class="row">
                                    <div class="col">
                                        <div class="col">
                                            <label for="">Capital social</label>
                                            <input type="number" name="capital_social" class="form-control" required>
                                        </div>
                                        <div class="col">
                                            <label for="">Coordonnées bancaires</label>
                                            <input type="text" name="coordonneBancaire" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="col">
                                            <label for="">Effectif prévisionnel</label>
                                            <input type="number" name="effectif_previsionnel" class="form-control">
                                        </div>
                                        <div class="col">
                                            <label for="">Description de l'activité</label>
                                            <textarea name="description_activite" class="form-control" ></textarea>
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <div class="form-navigation">
                                    <button type="button" class="btn prev-btn">Précédent</button>
                                    <button type="button" class="btn next-btn">Suivant</button>
                                </div>
                            </div>

                            <!-- Étape 4: Documents à Fournir -->
                            <div class="form-step">
                                <h2>Documents à Fournir</h2>
                                <p>Veuillez télécharger les documents requis pour votre projet.</p>
                                <div class="col ">
                                    <button type="button" id="addFileType" class="btn btn-primary">
                                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

                                        <i class="fas fa-folder"></i>
                                        <i class="fas fa-plus" style="position: relative; left: -15px; top: 5px; font-size: 1em; color: black;"></i>
                                        pièce à fournir
                                    </button><br>
                                </div>
                                <div class="col">
                                    <label for="files" class="form-label"></label>
                                    <div class="file-card ms-auto" id="file-card-0">
                                        <div id="file-icon-0" class="file-icon">
                                            <span class="upload-icon" onclick="document.getElementById('files-0').click()">
                                                <img src="{{ asset('armoiries/files-data.png') }}" width="17%" height="17%" alt="File Icon" id="file-icon-img-0">
                                            </span>
                                        </div>
                                        <div id="file-name-0" class="file-name">Aucune pièce sélectionné</div>
                                    </div>
                                    <input class="form-control d-none" type="file" id="files-0" name="files[]" required>

                                </div>
                                <div id="additionalFileTypes" class="row row-cols-3"></div>

                                <br>
                                <div class="form-navigation">
                                    <button type="button" class="btn prev-btn">Précédent</button>
                                    <button type="submit" class="btn">Soumettre</button>
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
    //script pour le step by step du formulaire
    const steps = document.querySelectorAll(".form-step");
    const nextBtns = document.querySelectorAll(".next-btn");
    const prevBtns = document.querySelectorAll(".prev-btn");
    const stepIndicator = document.querySelectorAll(".step");

    let currentStep = 0;

    nextBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            steps[currentStep].classList.remove("active");
            stepIndicator[currentStep].classList.remove("active");
            currentStep++;
            steps[currentStep].classList.add("active");
            stepIndicator[currentStep].classList.add("active");
        });
    });

    prevBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            steps[currentStep].classList.remove("active");
            stepIndicator[currentStep].classList.remove("active");
            currentStep--;
            steps[currentStep].classList.add("active");
            stepIndicator[currentStep].classList.add("active");
        });
    });

    //Script pour les selections de fichiers
    document.getElementById('addFileType').addEventListener('click', function() {
        var additionalFileTypes = document.getElementById('additionalFileTypes');
        var index = additionalFileTypes.children.length;

        // Création du conteneur pour chaque fichier avec Bootstrap
        var newFileInputDiv = document.createElement('div');
        newFileInputDiv.className = 'col';

        var fileCard = document.createElement('div');
        fileCard.className = 'file-card';
        fileCard.id = 'file-card-' + index;

        var fileIcon = document.createElement('div');
        fileIcon.className = 'file-icon';
        fileIcon.id = 'file-icon-' + index;
        fileIcon.innerHTML = `
            <span class="upload-icon" onclick="document.getElementById('files-${index}').click()">
                <img src="{{ asset('armoiries/files-data.png') }}" width="50%" height="50%" alt="File Icon" id="file-icon-img-${index}">
            </span>
        `;

        var fileNameDiv = document.createElement('div');
        fileNameDiv.className = 'file-name mt-2';
        fileNameDiv.id = 'file-name-' + index;
        fileNameDiv.textContent = 'Aucune pièce sélectionnée';

        var newFileInput = document.createElement('input');
        newFileInput.className = 'form-control d-none';
        newFileInput.type = 'file';
        newFileInput.name = 'files[]';
        newFileInput.id = 'files-' + index;

        // Associer un événement de changement pour afficher le fichier sélectionné
        newFileInput.addEventListener('change', function(event) {
            displayFile(event.target.files, index);
        });

        // Ajout des éléments au DOM
        fileCard.appendChild(fileIcon);
        fileCard.appendChild(fileNameDiv);
        newFileInputDiv.appendChild(fileCard);
        newFileInputDiv.appendChild(newFileInput);
        additionalFileTypes.appendChild(newFileInputDiv);
    });

    function displayFile(files, index) {
        var fileIconImg = document.getElementById('file-icon-img-' + index);
        var fileNameDiv = document.getElementById('file-name-' + index);

        // Initialisation de l’icône et du texte par défaut
        fileIconImg.src = '{{ asset("armoiries/files-data.png") }}';
        fileNameDiv.textContent = 'Aucune pièce sélectionnée';

        if (files && files.length > 0) {
            var file = files[0];
            var fileType = file.type;
            var fileName = file.name;

            // Sélection d’icône basée sur le type de fichier
            if (fileType.includes('zip') || fileName.endsWith('.zip') || fileName.endsWith('.rar')) {
                fileIconImg.src = '{{ asset("armoiries/zip_image.jpg") }}';
            } else if (fileType === 'application/pdf' || fileName.endsWith('.pdf')) {
                fileIconImg.src = '{{ asset("armoiries/pdf-icon.png") }}';
            } else if (fileType.includes('word') || fileName.endsWith('.docx')) {
                fileIconImg.src = '{{ asset("armoiries/raw.png") }}';
            } else if (fileType.includes('excel') || fileName.endsWith('.xlsx') || fileName.endsWith('.csv')) {
                fileIconImg.src = '{{ asset("armoiries/excel.png") }}';
            } else {
                fileIconImg.src = '{{ asset("armoiries/files-data.png") }}';
            }

            fileNameDiv.textContent = fileName;
        }
    }


</script>
@endsection
