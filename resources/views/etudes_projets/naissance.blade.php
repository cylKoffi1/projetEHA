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
                            
                        @include('etudes_projets.steps.document')
                        @include('etudes_projets.steps.Financement')
                        @include('etudes_projets.steps.maitreOuvre')
                        @include('etudes_projets.steps.maitreOuvrages')
                            @include('etudes_projets.steps.Information_Generales')
                            @include('etudes_projets.steps.Infrastructure')
                            @include('etudes_projets.steps.actionAMener')
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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


@endsection
