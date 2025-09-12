@extends('layouts.app')
<style>
    :root {
        --primary-color: #435ebe;
        --secondary-color: #2c3e50;
        --warning-color: #ffc107;
        --success-color: #28a745;
        --info-color: #17a2b8;
        --danger-color: #dc3545;
        --light-bg: #f8f9fa;
        --dark-bg: #343a40;
    }

    .card {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(78, 70, 70, 0.1);
        border: none;
    }

    .card-header {
        background: linear-gradient(135deg, #a6b4f0, #a3b6c7);
        color: white;
        border-radius: 10px 10px 0 0 !important;
    }

    .form-control, .form-select {
        border-radius: 5px;
        border: 1px solid #ced4da;
        transition: all 0.3s;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(67, 94, 190, 0.25);
    }

    /* Styles pour les options du select */
    .avec-infrastructures {
        background-color: var(--warning-color);
        color: #000;
    }

    .plusieurs-actions {
        background-color: var(--primary-color);
        color: white;
    }

    .non-trouves {
        background-color: var(--danger-color);
        color: white;
    }

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-secondary {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
    }

    .btn-warning {
        background-color: var(--warning-color);
        border-color: var(--warning-color);
    }

    .btn-success {
        background-color: var(--success-color);
        border-color: var(--success-color);
    }

    .btn-info {
        background-color: var(--info-color);
        border-color: var(--info-color);
    }

    .btn-danger {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
    }

    .badge {
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: 500;
    }

    .modal-content {
        border-radius: 10px;
        border: none;
    }

    .modal-header {
        background: linear-gradient(135deg, #a6b4f0, #a3b6c7);
        color: white;
        border-radius: 10px 10px 0 0;
    }

    .date-display {
        font-size: 1rem;
        color: var(--secondary-color);
        font-weight: 500;
    }

    .action-btn {
        transition: all 0.3s;
        padding: 5px 10px;
        border-radius: 5px;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .return-icon {
        cursor: pointer;
        transition: all 0.3s;
        color: var(--primary-color);
    }
    .offcanvas-backdrop.show {
        opacity: .1 !important;
    }
    .return-icon:hover {
        transform: scale(1.1);
        color: var(--secondary-color);
    }

    .toggle-list-btn {
        color: var(--primary-color);
        cursor: pointer;
        transition: all 0.3s;
    }

    .toggle-list-btn:hover {
        color: var(--secondary-color);
        text-decoration: underline;
    }

    /* Animation pour les changements de données */
    @keyframes highlight {
        from { background-color: #ffff99; }
        to { background-color: transparent; }
    }

    .highlight {
        animation: highlight 1.5s;
    }
    input[type=range] {
        accent-color: var(--primary-color);
    }
    /* Ajouts pour l'offcanvas et le suivi */
    .offcanvas {
        width: 600px !important;
        max-width: 90vw;
    }

    #photos-preview img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 5px;
        margin-right: 5px;
        margin-bottom: 5px;
        border: 1px solid #ddd;
    }

    #finalisation-section {
        background-color: rgba(40, 167, 69, 0.1);
        padding: 15px;
        border-radius: 5px;
        border-left: 4px solid var(--success-color);
        margin-bottom: 15px;
    }

    .form-range::-webkit-slider-thumb {
        background: var(--primary-color);
    }

    .form-range::-moz-range-thumb {
        background: var(--primary-color);
    }

    .form-range::-ms-thumb {
        background: var(--primary-color);
    }

    @media (max-width: 768px) {
        .offcanvas {
            width: 85vw !important;
        }

        #photos-preview img {
            width: 80px;
            height: 80px;
        }
    }
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .form-control, .form-select {
            width: 100% !important;
        }

        .table-container {
            overflow-x: scroll;
        }
    }
</style>

@section('content')
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
                            <li class="breadcrumb-item"><a href="">Réalisation de projet</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Niveau d'avancement</li>

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
                    <h4 class="card-title text-black">
                        <i class="fas fa-tasks me-2"></i>
                        Réalisation: Projets en cours
                    </h4>
                </div>

                <div class="card-content">
                    <div class="card-body">
                        @if (session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                        </div>
                        @elseif (session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                        </div>
                        @endif

                        <form class="form" id="projectForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="ecran_id" value="{{ $ecran->id }}" name="ecran_id">
                            <input type="hidden" id="codeProjetHidden">

                            <div class="row mb-4">
                                <div id="info-projet-col" class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Section des informations effectives du projet
                                    </div>
                                </div>


                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="code_projet" class="form-label">Code projet</label>
                                    <select name="code_projet" id="code_projet" class="form-select" onchange="checkProjectDetails()">
                                            <option value="">Sélectionner un projet</option>
                                            @foreach ($projets as $projet)
                                                <option value="{{ $projet->code_projet }}">
                                                        {{ $projet->code_projet }}
                                                </option>
                                            @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label for="date_debut">Date début</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="far fa-calendar"></i></span>
                                        <input type="date" class="form-control" id="date_debut" name="date_debut">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label for="date_fin">Date fin</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="far fa-calendar"></i></span>
                                        <input type="date" id="date_fin" class="form-control" name="date_fin">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label for="cout">Coût</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-money-bill-wave"></i></span>
                                        <input type="text" class="form-control text-end" id="cout" name="cout">
                                    </div>
                                </div>

                                <div class="col-md-1">
                                    <label for="devise">Devise</label>
                                    <input type="text" id="devise" class="form-control" name="devise" readonly>
                                </div>

                                <div class="col-md-2">
                                    <label for="statut">Statut</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                        <input type="text" name="statut" id="statutInput" class="form-control" readonly>
                                        <input type="hidden" name="code_statut" id="codeStatutInput">
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="actionTable" class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>N° ordre</th>
                                                <th>Action à mener</th>
                                                <th>Quantité</th>
                                                <th>Infrastructures</th>
                                                <th>Bénéficiaires</th>
                                                <th>Caractéristiques</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="beneficiaire-table-body">
                                            <!-- Données chargées dynamiquement -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <a href="#" id="voir-liste-link" class="toggle-list-btn">
                                    <i class="fas fa-list me-2"></i>
                                    Voir la liste complète des projets
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Bénéficiaires -->
    <div class="modal fade" id="beneficiaireModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="beneficiaireForm" method="POST" data-parsley-validate>
                    @csrf
                    <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                    <input type="hidden" name="CodeProjetBene" id="CodeProjetBene">
                    <input type="hidden" name="numOrdreBene" id="numOrdreBene">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-users me-2"></i>
                            Gestion des bénéficiaires
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">


                        <div class="row">
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="beneficiaireTable">
                                        <thead>
                                            <tr>
                                                <th width="5%"><input type="checkbox" id="check-all"></th>
                                                <th width="20%">Code</th>
                                                <th width="50%">Libellé/Nom</th>
                                                <th width="25%">Type</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Fermer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Niveau d'avancement - Version corrigée -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="niveauAvancementModal" aria-labelledby="niveauAvancementLabel" style="width: 61% !important; height: calc(100vh - 90px); top: auto; overflow-y: auto;background-color: #e1e6f6 !important;">
        <div class="offcanvas-header" style="background-color: #a1a1ca !important;">
            <h5 class="offcanvas-title" id="niveauAvancementLabel">
                <i class="fas fa-chart-line me-2"></i>
                Suivi d'avancement
            </h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form id="avancementForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                <input type="hidden" id="code_projet_Modal" name="code_projet">
                <input type="hidden" id="ordre_Modal" name="num_ordre">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nature_travaux_Modal">Nature des travaux</label>
                        <input type="text" class="form-control" id="nature_travaux_Modal" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="quantite_provisionnel_Modal">Quantité prévue</label>
                        <input type="text" class="form-control" id="quantite_provisionnel_Modal" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="quantite_reel_Modal">Etat avancement</label>
                        <input type="range" class="form-range" id="quantite_reel_slider" min="0" max="100" step="1" oninput="updateProgressBar(this.value)">
                        <small class="text-muted">Progression : <span id="sliderValue">0%</span></small>
                        <input type="hidden" id="quantite_reel_Modal" name="quantite_reel">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="date_debut_Modal">Date début</label>
                        <input type="date" class="form-control" id="date_debut_Modal" readonly>
                    </div>
                    <div class="col-md-4">
                        <label for="pourcentage_Modal">Pourcentage</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="pourcentage_Modal" id="pourcentage_Modal" readonly>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="date_avancement_Modal">Date du suivi</label>
                        <input type="date" class="form-control" id="date_avancement_Modal" name="date_avancement"
                            value="{{ date('Y-m-d') }}">
                    </div>
                </div>

                <div class="row mb-3" id="finalisation-section" style="display: none;">
                    <div class="col-md-6">
                        <label for="date_fin_effective">Date effective de fin</label>
                        <input type="date" class="form-control" id="date_fin_effective" name="date_fin_effective">
                    </div>
                    <div class="col-md-6">
                        <label for="description_finale">Description finale</label>
                        <textarea class="form-control" id="description_finale" name="description_finale"
                                rows="2" placeholder="Décrivez l'état final"></textarea>
                    </div>
                </div>

                <div class="row mb-3 d-flex ">
                    <div class="col-8">
                        <label for="photos_avancement">Photos de l'avancement</label>
                        <input type="file" class="form-control" id="photos_avancement" name="photos_avancement[]"
                            multiple accept="image/*">
                        <small class="text-muted">Vous pouvez sélectionner jusqu'à 15 photos</small>
                    </div>
                    <div class="col-4 ">
                        <label for="">.</label>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-save me-1"></i> Enregistrer
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div id="photos-preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                    </div>
                </div>

                <!-- Historique des suivis -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="mb-3"><i class="fas fa-history me-2"></i>Historique des suivis</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="historiqueTable">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Avancement</th>
                                        <th>Photos</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Liste des projets (cachée par défaut) -->
    <div class="row mt-4" id="liste-projets" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-list-ol me-2"></i>
                        Liste des projets réalisés (En cours)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                            <thead>
                                <tr>
                                    <th>Code projet</th>
                                    <th>Domaine</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th class="text-end">Coût</th>
                                    <th>Dévise</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projets as $projet)
                                <tr>
                                    <td>{{ $projet->code_projet }}</td>
                                    <td>{{ $projet->sousDomaine?->Domaine?->libelle }}</td>
                                    <td>{{ $projet->date_demarrage_prevue ? date('d/m/Y', strtotime($projet->date_demarrage_prevue)) : '-' }}</td>
                                    <td>{{ $projet->date_fin_prevue ? date('d/m/Y', strtotime($projet->date_fin_prevue)) : '-' }}</td>
                                    <td class="text-end">{{ $projet->cout_projet ? number_format($projet->cout_projet, 0, ',', ' ') : '-' }}</td>
                                    <td>{{ $projet->devise?->code_long ?? '-' }}</td>
                                    <td>
                                        @if($projet->statuts?->statut)
                                        <span class="badge bg-primary">{{ $projet->statuts?->statut?->libelle }}</span>
                                        @else
                                        <span class="badge bg-secondary">Non défini</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des des projets en cours ')
    });
    function afficheSelect(selectId) {
        // Masquer tous les sélecteurs
        $('#select_acteur, #select_localite, #select_infra').hide();

        // Afficher le bon sélecteur
        $('#' + selectId).show();
    }
    function formatNumber(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }
    // Calcul du pourcentage d'avancement
    function calculatePercentage() {
        const quantitePrevue = parseFloat($('#quantite_provisionnel_Modal').val()) || 0;
        const quantiteReelle = parseFloat($('#quantite_reel_Modal').val()) || 0;

        if (quantitePrevue > 0) {
            const pourcentage = (quantiteReelle / quantitePrevue) * 100;
            $('#pourcentage_Modal').val(pourcentage.toFixed(2));

            // Afficher la section de finalisation si 100%
            if (pourcentage >= 100) {
                $('#finalisation-section').show();
            } else {
                $('#finalisation-section').hide();
            }

        }

    }



    // Mise à jour de la barre de progression
    function updateProgressBar(val) {
        $('#sliderValue').text(val + '%');
        $('#pourcentage_Modal').val(val);       // ← garder
        // NE PAS pousser val dans #quantite_reel_Modal (ou supprime ce champ)
        $('#quantite_reel_Modal').val(val);  // ← à enlever si on reste en modèle %
        if (parseInt(val) >= 100) {
            $('#finalisation-section').show();
        } else {
            $('#finalisation-section').hide();
        }
    }


    // Prévisualisation des photos avant upload
    $('#photos_avancement').on('change', function() {
        const files = this.files;
        const preview = $('#photos-preview');
        preview.empty();

        if (files.length > 15) {
            swalMsg('Limite de fichiers', 'Vous ne pouvez sélectionner que 15 photos maximum', 'warning');
            $(this).val('');
            return;
        }

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (!file.type.match('image.*')) continue;

            const reader = new FileReader();
            reader.onload = function(e) {
                preview.append(`
                    <div class="position-relative" style="width: 100px; height: 100px;">
                        <img src="${e.target.result}" class="img-thumbnail" style="width: 100%; height: 100%; object-fit: cover;">
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            onclick="removePhotoPreview(this)" style="cursor: pointer;">
                            ×
                        </span>
                    </div>
                `);
            }
            reader.readAsDataURL(file);
        }
    });

    function removePhotoPreview(element) {
        $(element).parent().remove();
        // Vous pouvez aussi mettre à jour l'input file ici si nécessaire
    }

    // Chargement de l'historique des suivis
    function loadHistorique(codeProjet, numOrdre) {
    $.ajax({
        url: '{{ route("get.historique.avancement") }}',
        type: 'GET',
        data: { code_projet: codeProjet, num_ordre: numOrdre },
        beforeSend: function() {
        $('#historiqueTable tbody').html('<tr><td colspan="4" class="text-center">Chargement...</td></tr>');
        },
        success: function(response) {
        const tbody = $('#historiqueTable tbody');
        tbody.empty();

        if (!response || response.length === 0) {
            tbody.append('<tr><td colspan="4" class="text-center text-muted">Aucun suivi enregistré</td></tr>');
            return;
        }

        response.forEach(item => {
            const photosHtml = (item.photos && item.photos.length > 0)
            ? `<a href="#" onclick="return showPhotos('${item.photos.join(',')}')">Voir photos (${item.photos.length})</a>`
            : 'Aucune photo';

            tbody.append(`
            <tr>
                <td>${item.date_avancement}</td>
                <td>${item.pourcentage}%</td>
                <td>${photosHtml}</td>
                <td>
                <button type="button" class="btn btn-sm btn-danger btn-delete-suivi" onclick="deleteSuivi(${item.id})">
                    <i class="fas fa-trash"></i>
                </button>
                </td>
            </tr>
            `);
        });
        },
        error: function() {
        swalMsg('Erreur', "Impossible de charger l'historique", 'error');
        }
    });
    }
    // Fonction pour afficher les photos en grand
    function showPhotos(photos) {
    const ids = (photos || '').split(',').map(s => s.trim()).filter(Boolean);
    if (!ids.length) return false;

    const modal = `
        <div class="modal fade" id="photosModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Photos de l'avancement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div id="carouselPhotos" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    ${ids.map((id, index) => `
                    <div class="carousel-item ${index === 0 ? 'active' : ''}">
                        <!-- Adapte la route si tu sers GridFS: /files/{id} -->
                        <img src="/Data/avancement/${id}" class="d-block w-100" alt="Photo avancement">
                    </div>
                    `).join('')}
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselPhotos" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Précédent</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselPhotos" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Suivant</span>
                </button>
                </div>
            </div>
            </div>
        </div>
        </div>`;
    $('body').append(modal);
    $('#photosModal').modal('show').on('hidden.bs.modal', function(){ $(this).remove(); });
    return false;
    }

    // Helpers SweetAlert2
    function swalConfirm(opts) {
    // opts: {title, text, icon='warning', confirmButtonText='Oui', cancelButtonText='Annuler'}
    return Swal.fire({
        title: opts.title || 'Confirmer',
        text: opts.text || '',
        icon: opts.icon || 'warning',
        showCancelButton: true,
        confirmButtonText: opts.confirmButtonText || 'OK',
        cancelButtonText: opts.cancelButtonText || 'Annuler'
    });
    }

    function swalMsg(title, text, icon) {
    // icon doit être: 'success' | 'error' | 'warning' | 'info' | 'question'
    return Swal.fire(title || '', text || '', icon || 'info');
    }

    // Suppression d'un suivi
    function deleteSuivi(id) {
    swalConfirm({
        title: 'Confirmer la suppression',
        text: 'Êtes-vous sûr de vouloir supprimer ce suivi ?',
        icon: 'warning',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
    }).then((res) => {
        if (!res.isConfirmed) return;

        $.ajax({
        url: '/delete-suivi/' + id,
        type: 'POST',
        data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
        success: function() {
            swalMsg('Succès', 'Le suivi a été supprimé', 'success');
            const codeProjet = $('#code_projet_Modal').val();
            const numOrdre   = $('#ordre_Modal').val();
            loadHistorique(codeProjet, numOrdre);
        },
        error: function() {
            swalMsg('Erreur', 'Une erreur est survenue pendant la suppression', 'error');
        }
        });
    });
    }

    $(document).on('click', '.btn-delete-suivi', function(e){
        e.preventDefault(); e.stopPropagation();
    });

    // Gestion de la soumission du formulaire d'avancement
    $('#avancementForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const pourcentage = parseInt($('#pourcentage_Modal').val());

        // Validation
        if (pourcentage > 100) {
            alert('Attention', 'Le pourcentage ne peut pas dépasser 100%', 'warning');
            return;
        }

        // Vérifier les fichiers

        const files = $('#photos_avancement')[0].files || [];
        if (files.length > 15) {
            alert('Vous ne pouvez sélectionner que 15 photos maximum', 'warning');
            return;
        }

        $.ajax({
            url: '{{ route("save.avancement") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
            $('button[type="submit"]').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin me-1"></i> Enregistrement...');
            },
            success: function() {
            swalMsg('Succès', 'Le suivi a été enregistré avec succès', 'success');

            // Recharge historique
            const codeProjet = $('#code_projet_Modal').val();
            const numOrdre   = $('#ordre_Modal').val();
            loadHistorique(codeProjet, numOrdre);

            // Reset partiel
            const nextMin = Math.min(100, pourcentage + 1);
            $('#quantite_reel_slider').attr({ min: nextMin, max: 100 }).val(nextMin);
            updateProgressBar(nextMin);
            $('#photos_avancement').val('');
            $('#photos-preview').empty();

            if (pourcentage >= 100) {
                $('#date_fin_effective').val('');
                $('#description_finale').val('');
                $('#finalisation-section').hide();
            }
            },
            error: function(xhr) {
            // Affiche erreurs de validation si présentes
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                const errs = xhr.responseJSON.errors;
                const firstKey = Object.keys(errs)[0];
                swalMsg('Validation', errs[firstKey][0], 'warning');
            } else {
                let errorMessage = 'Une erreur est survenue lors de l’enregistrement.';
                if (xhr.responseJSON && xhr.responseJSON.message) errorMessage = xhr.responseJSON.message;
                swalMsg('Erreur', errorMessage, 'error');
            }
            },
            complete: function() {
            $('button[type="submit"]').prop('disabled', false)
                .html('<i class="fas fa-save me-1"></i> Enregistrer');
            }
        });
    });

    function formatNumberInput(input) {
        let value = input.value.replace(/[^\d]/g, '');
        input.value = formatNumber(value);
    }

    $(document).ready(function() {
        // Initialisation DataTable


        // Initialisation des sélecteurs de bénéficiaires
        $("#type_acteur").prop("checked", true);
        afficheSelect('select_acteur');

        // Gestion de l'affichage de la liste des projets
        $('#voir-liste-link').on('click', function(e) {
            e.preventDefault();
            $('#liste-projets').slideToggle();
            $(this).find('i').toggleClass('fa-list fa-times');
        });

        // Formatage des nombres
        $('#cout, #coutEffective_Modal').on('input', function(e) {
            formatNumberInput(e.target);
        });

        // Calcul du pourcentage d'avancement
        $("#quantite_reel_Modal").on("input", function() {
            calculatePercentage();
        });
    });



    function checkProjectDetails() {
        const codeProjet = $('#code_projet').val();
        if (!codeProjet) return;

        $.ajax({
            url: '{{ url("/fetchProjectDetails")}}',
            method: 'GET',
            data: {
                _token: '{{ csrf_token() }}',
                code_projet: codeProjet
            },
            beforeSend: function() {
                // Afficher un indicateur de chargement
                $('#code_projet').addClass('loading');
            },
            success: function(response) {
                console.log('la reponse', response);
                $('#date_debut').val(response.date_debut);
                $('#date_fin').val(response.date_fin);
                $('#cout').val(formatNumber(response.cout));
                $('#statutInput').val(response.statutInput);
                $('#codeProjetHidden').val(response.codeProjet);
                $('#devise').val(response.devise);
                const codeProjet = response.codeProjet;
                // Mettre à jour le tableau des actions
                updateTableData(codeProjet, response.actions || []);
                $.ajax({
                    url: '{{ route("verifier.projet.finalisable") }}',
                    method: 'GET',
                    data: {
                        code_projet: codeProjet
                    },
                    success: function(response) {
                        if (response.finalisable === true) {
                            $('#finalisation-projet-container').show();
                            $('#info-projet-col').removeClass('col-12').addClass('col-9');
                        } else {
                            $('#finalisation-projet-container').hide();
                            $('#info-projet-col').removeClass('col-9').addClass('col-12');
                        }
                    },
                    error: function() {
                        $('#finalisation-projet-container').hide();
                    }
                });

                // Animation de mise à jour
                $('.form-control').addClass('highlight');
                setTimeout(() => $('.form-control').removeClass('highlight'), 1500);
            },
            complete: function() {
                $('#code_projet').removeClass('loading');
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Une erreur est survenue', 'error');
            }
        });
    }

    function updateTableData(codeProjet, data) {
    const tbody = $('#beneficiaire-table-body');
    tbody.empty();

    if (!data || data.length === 0) {
        tbody.append('<tr><td colspan="7" class="text-center text-muted">Aucune action disponible pour ce projet</td></tr>');
        return;
    }

    data.forEach(item => {
        const caracButton = item.infrastructure_idCode
        ? `<a href="{{ url('admin/infrastructures') }}/${item.infrastructure_idCode}" class="btn btn-sm btn-primary action-btn">
            <i class="fas fa-cog me-1"></i> Caractéristiques
            </a>`
        : `<button type="button" class="btn btn-sm btn-secondary no-carac">
            <i class="fas fa-ban me-1"></i> Caractéristiques
            </button>`;

        const row = `
        <tr class="action" data-id="${item.code}">
            <td class="num_ordre_cell">${item.Num_ordre}</td>
            <td>${item.action_libelle || '-'}</td>
            <td>${item.Quantite}</td>
            <td>${item.infrastructure_libelle || '-'}</td>
            <td>
            <button type="button" class="btn btn-sm btn-outline-primary beneficiaire-btn"
                    data-bs-toggle="modal" data-bs-target="#beneficiaireModal"
                    data-projet="${codeProjet}" data-ordre="${item.Num_ordre}">
                <i class="fas fa-user-plus me-1"></i> Bénéficiaires
            </button>
            </td>
            <td>${caracButton}</td>
            <td>
            <button type="button" class="btn btn-sm btn-success action-btn btn-niveau-avancement"
                    data-bs-toggle="offcanvas" data-bs-target="#niveauAvancementModal"
                    data-projet="${codeProjet}" data-ordre="${item.Num_ordre}" data-quantite="${item.Quantite}">
                <i class="fas fa-chart-line me-1"></i> Suivi
            </button>
            </td>
        </tr>`;
        tbody.append(row);
    });

    $(document).on('click', '.no-carac', function() {
        swalMsg('Info', "Aucune caractéristique disponible pour cette infrastructure.", 'info');
    });
    }

    // Gestion des bénéficiaires
    $(document).on('click', '.beneficiaire-btn', function() {
        const codeProjet = $(this).data('projet');
        const numOrdre = $(this).data('ordre');

        $('#CodeProjetBene').val(codeProjet);
        $('#numOrdreBene').val(numOrdre);

        loadBeneficiaires(codeProjet, numOrdre);
    });

    // Fonction pour ouvrir le suivi d'avancement
    $(document).on('click', '.btn-niveau-avancement', function() {
        const codeProjet = $(this).data('projet');
        const numOrdre   = $(this).data('ordre');

        // Reset UI
        $('#avancementForm')[0].reset();
        $('#photos-preview').empty();
        $('#finalisation-section').hide();

        // Valeurs cachées
        $('#code_projet_Modal').val(codeProjet);
        $('#ordre_Modal').val(numOrdre);

        // Précharge les données + dernier pourcentage
        $.ajax({
            url: '{{ route("get.donnees.suivi") }}',
            type: 'GET',
            data: { code_projet: codeProjet, num_ordre: numOrdre },
            beforeSend: function() {
            $('#nature_travaux_Modal').val('Chargement...');
            $('#quantite_provisionnel_Modal').val('...');
            $('#date_debut_Modal').val('');
            },
            success: function(response) {
            if (!response || !response.success || !response.result) {
                swalMsg('Info', (response && response.message) || 'Aucune information disponible.', 'info');
                return;
            }
            const data = response.result;

            $('#nature_travaux_Modal').val(data.nature_travaux || 'Non défini');
            $('#quantite_provisionnel_Modal').val(data.Quantite || 0);
            $('#date_debut_Modal').val(data.date_debut_effective || '');

            // Dernier % (retour API; défaut 0 si absent)
            const lastPct = Number(data.dernier_pourcentage || 0);
            const minNext = Math.min(100, lastPct + 1);

            $('#quantite_reel_slider').attr({ min: minNext, max: 100 }).val(minNext);
            updateProgressBar(minNext);

            if (lastPct >= 100) {
                $('#quantite_reel_slider').prop('disabled', true);
                $('button[type="submit"]').prop('disabled', true);
                $('#finalisation-section').hide();
                swalMsg('Terminé', "Cette action est déjà à 100%. Aucun nouveau suivi n'est possible.", 'info');
            } else {
                $('#quantite_reel_slider').prop('disabled', false);
                $('button[type="submit"]').prop('disabled', false);
            }

            loadHistorique(codeProjet, numOrdre);
            },
            error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Erreur inconnue lors du chargement des données.';
            swalMsg('Erreur', message, 'error');
            }
        });

        // Ouvre l'offcanvas
        const el = document.getElementById('niveauAvancementModal');
        if (!el) return console.error('Offcanvas non trouvé : #niveauAvancementModal');
        const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(el);
        offcanvas.show();
    });

    function loadBeneficiaires(codeProjet, numOrdre) {
        $.ajax({
            url: '{{ url("/recuperer-beneficiaires") }}',
            type: 'GET',
            data: {
                code_projet: codeProjet,
                NumOrdre: numOrdre
            },
            beforeSend: function() {
                $('#beneficiaireTable tbody').html('<tr><td colspan="4" class="text-center">Chargement...</td></tr>');
            },
            success: function(response) {
                const tbody = $('#beneficiaireTable tbody');
                tbody.empty();

                if (response.length === 0) {
                    tbody.append('<tr><td colspan="4" class="text-center text-muted">Aucun bénéficiaire enregistré</td></tr>');
                    return;
                }

                response.forEach(beneficiaire => {
                    const row = `
                        <tr>
                            <td><input type="checkbox"></td>
                            <td>${beneficiaire.code}</td>
                            <td>${beneficiaire.libelle_nom_etablissement}</td>
                            <td>${beneficiaire.type}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            },
            error: function(xhr) {
                alert('Impossible de charger les bénéficiaires', 'erreur');
            }
        });
    }

    // Gestion de l'ajout de bénéficiaires
    $('#addBtn').click(function() {
        let code, libelle, type;

        if ($('#type_acteur').is(':checked')) {
            code = $('#select_acteur').val();
            libelle = $('#select_acteur option:selected').text();
            type = 'acteur';
        } else if ($('#type_localite').is(':checked')) {
            code = $('#select_localite').val();
            libelle = $('#select_localite option:selected').text();
            type = 'localite';
        } else if ($('#type_infra').is(':checked')) {
            code = $('#select_infra').val();
            libelle = $('#select_infra option:selected').text();
            type = 'infrastructure';
        }

        if (!code) {
            alert( 'Veuillez sélectionner un élément à ajouter', 'warning');
            return;
        }

        // Vérifier si l'élément existe déjà
        const exists = $('#beneficiaireTable tbody tr').toArray().some(tr => {
            return $(tr).find('td:eq(1)').text() === code && $(tr).find('td:eq(3)').text() === type;
        });

        if (exists) {
            alert('Cet élément est déjà dans la liste', 'warning');
            return;
        }

        // Ajouter à la table
        $('#beneficiaireTable tbody').append(`
            <tr>
                <td><input type="checkbox"></td>
                <td>${code}</td>
                <td>${libelle}</td>
                <td>${type}</td>
            </tr>
        `);
    });

    // Suppression des bénéficiaires
    $('#deleteBtn').click(function() {
        const selected = $('#beneficiaireTable tbody input[type="checkbox"]:checked').closest('tr');

        if (selected.length === 0) {
            alert('Veuillez sélectionner au moins un élément à supprimer', 'warning');
            return;
        }

        alert({
            title: 'Confirmer la suppression',
            text: `Êtes-vous sûr de vouloir supprimer ${selected.length} élément(s) ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Oui, supprimer'
        }).then((result) => {
            if (result.isConfirmed) {
                selected.remove();

                if ($('#beneficiaireTable tbody tr').length === 0) {
                    $('#beneficiaireTable tbody').html('<tr><td colspan="4" class="text-center text-muted">Aucun bénéficiaire</td></tr>');
                }
            }
        });
    });

    // Sélectionner/Désélectionner tout
    $('#check-all').change(function() {
        $('#beneficiaireTable tbody input[type="checkbox"]').prop('checked', this.checked);
    });
</script>
@endsection
