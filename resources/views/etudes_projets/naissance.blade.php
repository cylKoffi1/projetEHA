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
    .hidden {
        display: none;
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
                            <div class="col-12">
                                <div class="row">
                                        <label class="form-label">Maitre d'ouvrage</label>
                                        <div class="col" style="text-align: left;">
                                            <!-- Radio for Public -->
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="maitreOuvrage" id="public" value="1" checked onchange="updateMaitreOuvrage()">
                                                <label class="form-check-label" for="public">Public</label>
                                            </div>
                                            <!-- Radio for Private -->
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="maitreOuvrage" id="prive" value="2" onchange="updateMaitreOuvrage()">
                                                <label class="form-check-label" for="prive">Privé</label>
                                            </div>
                                        </div>

                                        <!-- Dynamic content based on selection -->

                                        <!-- For Public (Select fields) -->
                                        <div class="col hidden" id="publicFields">
                                            <div class="mb-3">
                                                <label for="ministere" class="form-label">Ministère</label>
                                                <select class="form-select" id="ministere">
                                                    <option value="">Sélectionner le ministère</option>
                                                    @foreach ($ministeres as $min)
                                                    <option value="{{$min->code}}">{{$min->libelle}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                            <label for="collectivite" class="form-label">Collectivité Territoriale</label>
                                                <select class="form-select" id="collectivite">
                                                    <option value="">Sélectionner la collectivité</option>
                                                    @foreach ($collectivites as $collectivite)
                                                        <option value="{{ $collectivite->code_bailleur }}">{{ $collectivite->libelle_long }}</option>
                                                    @endforeach
                                                </select>

                                            </div>
                                        </div>

                                        <!-- For Private (Radio buttons for Enterprise or Individual) -->
                                        <div class="col" id="priveFields">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="typeDemandeur" id="radioEntreprise" value="entreprise" checked>
                                                <label class="form-check-label" for="radioEntreprise">Morale</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="typeDemandeur" id="radioParticulier" value="particulier">
                                                <label class="form-check-label" for="radioParticulier">Physique</label>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <label for="code">Code</label>

                                            <input type="text" class="form-control" name="codeProjet" id="codeProjet" readonly value="{{ old('codeProjet', $generatedCodeProjet) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-4" style="text-align: rigth;">
                                    <label for="title" class="form-label">Nature des travaux</label>
                                    <select class="form-select" name="nature_travaux" id="nature_travaux">
                                        @foreach ($natures as $nature)
                                        <option value="{{ $nature->code}}">{{ $nature->libelle}}</option>
                                        @endforeach
                                    </select>
                                    </div>

                                </div>
                            </div>
                            <div class="col-4">

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
                                    <label for="name">Nom :</label>
                                    <input type="text" class="form-control" id="nom" name="nom">
                                </div>
                                <div class="col">
                                    <label for="prenom">Pénoms :</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom">
                                </div>
                                <div class="col">
                                    <label for="professionalStatus">Statut professionnel :</label>
                                    <input type="text" class="form-control" id="professionalStatus" name="professionalStatus">
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
                                    <label for="individualRegistrationNumber">Numéro d'immatriculation :</label>
                                    <input type="text" class="form-control" id="individualRegistrationNumber" name="individualRegistrationNumber">
                                </div>
                                <div class="col">
                                    <label for="references">Références professionnelles :</label>
                                    <input type="text" class="form-control" id="references" name="references">
                                </div>
                                <div class="col">
                                    <label for="additionalInfo3">Information supplémentaire  :</label>
                                    <input type="text" class="form-control" id="additionalInfo3" name="additionalInfo3">
                                </div>
                            </div>
                        </div>
                        <div class="col text-end">
                            <div class="card-body text-end">
                                <div class="col text-end">
                                    <button type="button" id="addFileType" class="btn btn-primary">
                                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

                                        <i class="fas fa-folder"></i>
                                        <i class="fas fa-plus" style="position: relative; left: -15px; top: 5px; font-size: 1em; color: black;"></i>
                                        pièce à fournir
                                    </button><br>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <div class="col text-end">
                                        <label for="files" class="form-label"></label>
                                        <div class="file-card ms-auto" id="file-card-0">
                                            <div id="file-icon-0" class="file-icon">
                                                <span class="upload-icon" onclick="document.getElementById('files-0').click()">
                                                    <img src="{{ asset('armoiries/files-data.png') }}" alt="File Icon" id="file-icon-img-0">
                                                </span>
                                            </div>
                                            <div id="file-name-0" class="file-name">Aucune pièce sélectionné</div>
                                        </div>
                                        <input class="form-control d-none" type="file" id="files-0" name="files[]" multiple required>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary -mb-2 text-end">Enregistrer</button>
                                </div>

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
        newFileLabel.textContent = '';

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
        fileNameDiv.textContent = 'Aucune pièce sélectionné';

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
<script>
        // Get the elements
        const publicRadio = document.getElementById('public');
        const priveRadio = document.getElementById('prive');
        const publicFields = document.getElementById('publicFields');
        const priveFields = document.getElementById('priveFields');

        // Function to toggle the fields based on selection
        function toggleFields() {
            if (publicRadio.checked) {
                publicFields.classList.remove('hidden');
                priveFields.classList.add('hidden');
            } else if (priveRadio.checked) {
                priveFields.classList.remove('hidden');
                publicFields.classList.add('hidden');
            }
        }

        // Add event listeners to the radio buttons
        publicRadio.addEventListener('change', toggleFields);
        priveRadio.addEventListener('change', toggleFields);

        // Initial toggle based on default selection
        window.onload = toggleFields;
    </script>
     <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Capture radio button changes
            const radios = document.querySelectorAll('input[name="maitreOuvrage"]');
            const codeProjetInput = document.getElementById('codeProjet');
            let location = 'CI';  // You can dynamically set this if needed
            let category = 'EHA'; // You can dynamically set this if needed

            radios.forEach(radio => {
                radio.addEventListener('change', function () {
                    const typeFinancement = this.value;  // Get the value 1 (Public) or 2 (Privé)
                    const year = new Date().getFullYear();

                    // Fetch the latest project number from the server
                    fetch(`/get-latest-project-number/${location}/${category}/${typeFinancement}`)
                        .then(response => response.json())
                        .then(data => {
                            const newNumber = data.newNumber;
                            // Update the code in the input field
                            codeProjetInput.value = `${location}${category}${typeFinancement}${year}${newNumber}`;
                        });
                });
            });
        });
    </script>
@endsection
