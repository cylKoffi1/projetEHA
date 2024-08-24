@extends('layouts.app')
<style>
    .file-card {
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        margin-bottom: 15px;
        position: relative;
        width: 150px;
        height: 150px;
    }
    .file-card img {
        max-width: 100px;
        max-height: 100px;
    }
    .file-card .file-name {
        margin-top: 100px;
        font-size: 12px;
    }
    .file-card .upload-icon {
        position: absolute;
        top: 10px;
        right: 22px;
        font-size: 24px;
        cursor: pointer;
    }
    #file-display {
        display: flex;
        flex-wrap: wrap;
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

            </div>
            <div class="card-content">
                <div class="col-12">
                    <form action="{{ route('project.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-8">
                                <div class="row">
                                    <div class="col-4">
                                        <label for="code">Code</label>
                                        <input type="text" class="form-control" name="codeProjet" id="codeProjet" readonly value="{{ old('codeProjet', $generatedCodeProjet) }}">
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-5">
                                    <label for="title" class="form-label">Titre du Projet</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                    </div>

                                </div>
                            </div>
                            <div class="col-4">
                                <div class="col" style="text-align: right;">
                                    <label class="form-label">Type d'entreprise</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="typeDemandeur" id="radioEntreprise" value="entreprise" checked>
                                        <label class="form-check-label" for="radioEntreprise">Entreprise</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="typeDemandeur" id="radioParticulier" value="particulier">
                                        <label class="form-check-label" for="radioParticulier">Particulier</label>
                                    </div>
                                </div>
                            </div>

                        </div><br>
                        <div id="formEntreprise">
                            <div class="row">
                                <div class="col">
                                    <label for="companyName">Nom de l'entreprise :</label>
                                    <input type="text" class="form-control" id="companyName" name="companyName">
                                </div>
                                <div class="col">
                                    <label for="legalStatus">Raison sociale :</label>
                                    <input type="text" id="legalStatus" class="form-control" name="legalStatus">
                                </div>
                                <div class="col">
                                    <label for="registrationNumber">Numéro d'immatriculation :</label>
                                    <input type="text" class="form-control" id="registrationNumber" name="registrationNumber">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="headOfficeAddress">Adresse du siège social :</label>
                                    <input type="text" class="form-control" id="headOfficeAddress" name="headOfficeAddress">
                                </div>
                                <div class="col">
                                    <label for="phoneNumber">Numéro de téléphone :</label>
                                    <input type="text" class="form-control" id="phoneNumber" name="phoneNumber">
                                </div>
                                <div class="col">
                                    <label for="emailAddress">Adresse e-mail :</label>
                                    <input type="email" class="form-control" id="emailAddress" name="emailAddress">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="website">Site web :</label>
                                    <input type="url" class="form-control" id="website" name="website">
                                </div>
                                <div class="col">
                                    <label for="projectManager">Nom du responsable de projet :</label>
                                    <input type="text" class="form-control" id="projectManager" name="projectManager">
                                </div>
                                <div class="col">
                                    <label for="managerRole">Fonction du responsable :</label>
                                    <input type="text" class="form-control" id="managerRole" name="managerRole">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="capital">Capital social :</label>
                                    <input type="text" class="form-control" id="capital" name="capital">
                                </div>
                                <div class="col">
                                    <label for="additionalInfo1">Information supplémentaire 1 :</label>
                                    <input type="text" class="form-control" id="additionalInfo1" name="additionalInfo1">
                                </div>
                                <div class="col">
                                    <label for="additionalInfo2">Information supplémentaire 2 :</label>
                                    <input type="text" class="form-control" id="additionalInfo2" name="additionalInfo2">
                                </div>
                            </div>
                        </div>
                        <div id="formParticulier" style="display:none;">
                            <div class="row">
                                <div class="col">
                                    <label for="fullName">Nom et prénom :</label>
                                    <input type="text" class="form-control" id="fullName" name="fullName">
                                </div>
                                <div class="col">
                                    <label for="professionalStatus">Statut professionnel :</label>
                                    <input type="text" class="form-control" id="professionalStatus" name="professionalStatus">
                                </div>
                                <div class="col">
                                    <label for="individualRegistrationNumber">Numéro d'immatriculation :</label>
                                    <input type="text" class="form-control" id="individualRegistrationNumber" name="individualRegistrationNumber">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="individualAddress">Adresse de l'entreprise :</label>
                                    <input type="text" class="form-control" id="individualAddress" name="individualAddress">
                                </div>
                                <div class="col">
                                    <label for="individualPhone">Numéro de téléphone :</label>
                                    <input type="text" class="form-control" id="individualPhone" name="individualPhone">
                                </div>
                                <div class="col">
                                    <label for="individualEmail">Adresse e-mail :</label>
                                    <input type="email" class="form-control" id="individualEmail" name="individualEmail">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="mainActivity">Activité principale :</label>
                                    <input type="text" class="form-control" id="mainActivity" name="mainActivity">
                                </div>
                                <div class="col">
                                    <label for="tradeName">Nom commercial (si applicable) :</label>
                                    <input type="text" class="form-control" id="tradeName" name="tradeName">
                                </div>
                                <div class="col">
                                    <label for="bankDetails">Coordonnées bancaires :</label>
                                    <input type="text" class="form-control" id="bankDetails" name="bankDetails">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="references">Références professionnelles :</label>
                                    <input type="text" class="form-control" id="references" name="references">
                                </div>
                                <div class="col">
                                    <label for="additionalInfo3">Information supplémentaire 3 :</label>
                                    <input type="text" class="form-control" id="additionalInfo3" name="additionalInfo3">
                                </div>
                                <div class="col">
                                    <label for="additionalInfo4">Information supplémentaire 4 :</label>
                                    <input type="text" class="form-control" id="additionalInfo4" name="additionalInfo4">
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card-body">
                                <div class="col-6">
                                    <button type="button" id="addFileType" class="btn btn-primary">
                                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

                                        <i class="fas fa-folder"></i>
                                        <i class="fas fa-plus" style="position: relative; left: -15px; top: 5px; font-size: 1em; color: black;"></i>
                                        Ajouter fichier
                                    </button>
                                </div>
                                <div class="row align-items-center">

                                    <div class="col-6">
                                        <label for="files" class="form-label">Importer un fichier :</label>
                                        <div class="file-card" id="file-card-0">
                                            <div id="file-icon-0" class="file-icon">
                                                <span class="upload-icon" onclick="document.getElementById('files-0').click()">
                                                    <img src="{{ asset('armoiries/files-data.png') }}" alt="File Icon" id="file-icon-img-0">
                                                </span>
                                            </div>
                                            <div id="file-name-0" class="file-name">Aucun fichier sélectionné</div>
                                        </div>
                                        <input class="form-control d-none" type="file" id="files-0" name="files[]" multiple required>
                                    </div>
                                </div>
                                <div class="row" id="additionalFileTypes">
                                    <!-- Les inputs pour les autres types de fichiers seront ajoutés ici -->
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary -mb-2">Soumettre</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.getElementById('addFileType').addEventListener('click', function() {
        var additionalFileTypes = document.getElementById('additionalFileTypes');
        var index = additionalFileTypes.children.length + 1;

        var newFileInputDiv = document.createElement('div');
        newFileInputDiv.className = 'col';

        var newFileLabel = document.createElement('label');
        newFileLabel.className = 'form-label';
        newFileLabel.textContent = 'Importer un fichier :';

        var fileCard = document.createElement('div');
        fileCard.className = 'file-card';
        fileCard.id = 'file-card-' + index;

        var fileIcon = document.createElement('div');
        fileIcon.className = 'file-icon';
        fileIcon.id = 'file-icon-' + index;
        fileIcon.innerHTML = '<span class="upload-icon" onclick="document.getElementById(\'files-' + index + '\').click()">' +
            '<img src="{{ asset("armoiries/files-data.png") }}" alt="File Icon" id="file-icon-img-' + index + '">' +
            '</span>';

        var fileNameDiv = document.createElement('div');
        fileNameDiv.className = 'file-name';
        fileNameDiv.id = 'file-name-' + index;
        fileNameDiv.textContent = 'Aucun fichier sélectionné';

        var newFileInput = document.createElement('input');
        newFileInput.className = 'form-control d-none';
        newFileInput.type = 'file';
        newFileInput.name = 'files[]';
        newFileInput.id = 'files-' + index;

        newFileInputDiv.appendChild(newFileLabel);
        fileCard.appendChild(fileIcon);
        fileCard.appendChild(fileNameDiv);
        newFileInputDiv.appendChild(fileCard);
        newFileInputDiv.appendChild(newFileInput);
        additionalFileTypes.appendChild(newFileInputDiv);

        newFileInput.addEventListener('change', function(event) {
            displayFile(event.target.files, index);
        });
    });

    function displayFile(files, index) {
        var fileIconImg = document.getElementById('file-icon-img-' + index);
        var fileNameDiv = document.getElementById('file-name-' + index);

        fileIconImg.src = '';
        fileNameDiv.textContent = 'Aucun fichier sélectionné';

        if (files.length > 0) {
            var file = files[0];

            if (file.type === 'application/zip' || file.name.endsWith('.zip') || file.name.endsWith('.rar')) {
                fileIconImg.src = '{{ asset("armoiries/zip_image.jpg") }}';
            } else if (file.type === 'application/pdf' || file.name.endsWith('.pdf')) {
                fileIconImg.src = '{{ asset("armoiries/pdf-icon.png") }}';
            } else if (file.type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || file.name.endsWith('.docx')) {
                fileIconImg.src = '{{ asset("armoiries/raw.png") }}';
            } else if (file.type === 'application/csv' || file.name.endsWith('.xlsx') || file.name.endsWith('.csv')) {
                fileIconImg.src = '{{ asset("armoiries/excel.png") }}';
            } else {
                fileIconImg.src = '{{ asset("armoiries/files-data.png") }}';
            }

            fileNameDiv.textContent = file.name;
        }
    }

    document.getElementById('files-0').addEventListener('change', function(event) {
        displayFile(event.target.files, 0);
    });


    document.addEventListener('DOMContentLoaded', function () {
        const radioEntreprise = document.getElementById('radioEntreprise');
        const radioParticulier = document.getElementById('radioParticulier');
        const formEntreprise = document.getElementById('formEntreprise');
        const formParticulier = document.getElementById('formParticulier');

        radioEntreprise.addEventListener('change', function () {
            if (this.checked) {
                formEntreprise.style.display = 'block';
                formParticulier.style.display = 'none';
            }
        });

        radioParticulier.addEventListener('change', function () {
            if (this.checked) {
                formEntreprise.style.display = 'none';
                formParticulier.style.display = 'block';
            }
        });
    });
</script>

@endsection
