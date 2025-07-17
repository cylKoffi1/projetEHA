@extends('layouts.app')

@section('content')
<style>
    .info-table td {
        padding: 8px 12px;
        font-size: 14px;
        vertical-align: middle;
    }

    .info-table strong {
        font-size: 14px;
    }

    .caracteristique-card {
        border-left: 4px solid #3a7bd5;
        background-color: #f8fafc;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .qr-code-container {
        text-align: center;
        padding: 15px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .qr-code-title {
        font-weight: 600;
        margin-bottom: 10px;
    }

    .caracteristique-group {
        margin-bottom: 20px;
    }

    .caracteristique-group-title {
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 1px solid #eee;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }

    .nav-tabs .nav-link.active {
        font-weight: 600;
        color: #3a7bd5;
    }
</style>
<style>
/* Style pour les caractéristiques */
.caracteristique-item {
    padding: 12px;
    border-radius: 6px;
    background-color: #f8f9fa;
    transition: all 0.2s;
    border-left: 3px solid transparent;
}

.caracteristique-item:hover {
    background-color: #e9ecef;
    border-left-color: #0d6efd;
}

.caracteristique-item[data-level="1"] { margin-left: 20px; }
.caracteristique-item[data-level="2"] { margin-left: 40px; }
.caracteristique-item[data-level="3"] { margin-left: 60px; }

/* Style pour les indicateurs */
.change-indicator {
    opacity: 0;
    transition: opacity 0.3s;
}

/* Style pour le sticky bottom */
.sticky-bottom {
    position: sticky;
    bottom: 0;
    z-index: 1020;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
}

</style>

<!-- GLightbox CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />

<section id="multiple-column-form">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Fiche technique - {{ $infrastructure->libelle }}</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('infrastructures.index') }}">Infrastructures</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Fiche technique</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row match-height">
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Informations générales</h4>
                    <div>
                        <a href="{{ route('infrastructures.print', $infrastructure->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-printer"></i> Imprimer
                        </a>
                        <a href="{{ route('infrastructures.edit', $infrastructure->id) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-pencil-square"></i> Modifier
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless info-table">
                                <tr><td><strong>Famille</strong></td><td>: {{ $infrastructure->familleInfrastructure->libelleFamille ?? '-' }}</td></tr>
                                <tr><td><strong>Code</strong></td><td>: {{ $infrastructure->code }}</td></tr>
                                <tr><td><strong>Nom</strong></td><td>: {{ $infrastructure->libelle }}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless info-table">                                
                                <tr><td><strong>Date de mise en service</strong></td><td>: {{ $infrastructure->date_operation ? \Carbon\Carbon::parse($infrastructure->date_operation)->format('d/m/Y') : '-' }}</td></tr>
                                <tr><td><strong>Localisation</strong></td><td>: {{ $infrastructure->localisation->libelle ?? '-' }}</td></tr>
                                <tr><td><strong>Coordonnées</strong></td><td>: {{ $infrastructure->latitude ?? '-' }}, {{ $infrastructure->longitude ?? '-' }}</td></tr>
                            </table>
                        </div>
                    </div>

                    @if($infrastructure->InfrastructureImage && $infrastructure->InfrastructureImage->count())
                        <div class="row mt-3">
                            @foreach($infrastructure->InfrastructureImage as $img)
                                <div class="col-md-3 mb-3">
                                    <a href="{{ asset($img->chemin_image) }}" class="glightbox" data-gallery="gallery-view">
                                        <img src="{{ asset($img->chemin_image) }}" class="img-fluid rounded" style="width: 100%; height: 180px; object-fit: cover;" alt="Photo">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Caractéristiques -->
            <div class="card mt-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#view">Vue</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#edit">Modifier</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Vue des caractéristiques -->
                        <div class="tab-pane fade show active" id="view">
                            @php
                                $caracsFamille = $infrastructure->familleInfrastructure->caracteristiques ?? collect();
                                $valeurs = $infrastructure->valeursCaracteristiques->keyBy('idCaracteristique');
                                $groupedCaracs = $caracsFamille->groupBy('groupe');
                            @endphp

                            @if($caracsFamille->isEmpty())
                                <div class="alert alert-light">
                                    Aucune caractéristique définie pour cette famille d'infrastructure.
                                </div>
                            @else
                                @foreach($groupedCaracs as $groupe => $caracs)
                                    <div class="caracteristique-group">
                                        <h5 class="caracteristique-group-title">{{ $groupe ?? 'Autres caractéristiques' }}</h5>
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered" style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40%;">Caractéristique</th>
                                                        <th style="width: 30%;">Valeur</th>
                                                        <th style="width: 30%;">Unité</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($caracs as $carac)
                                                        @php
                                                            $valeur = $valeurs[$carac->idCaracteristique] ?? null;
                                                            $unite = $valeur?->unite;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $carac->libelleCaracteristique }}</td>
                                                            <td>{{ $valeur?->valeur ?? '-' }}</td>
                                                            <td>
                                                                @if($unite)
                                                                    {{ $unite->libelleUnite }}
                                                                    @if($unite->symbole)
                                                                        ({{ $unite->symbole }})
                                                                    @endif
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <!-- Edition des caractéristiques -->
                        <div class="tab-pane fade" id="edit">
                            <form method="POST" action="{{ route('infrastructures.caracteristiques.updateMultiple', $infrastructure->id) }}" id="caracteristiques-form">
                                @csrf
                                @method('PUT')

                                @php
                                    $caracsFamille = $infrastructure->familleInfrastructure->caracteristiques ?? collect();
                                    $valeurs = $infrastructure->valeursCaracteristiques->keyBy('idCaracteristique');
                                    $groupedCaracs = $caracsFamille->groupBy('groupe');
                                @endphp

                                @if($caracsFamille->isEmpty())
                                    <div class="alert alert-info">Aucune caractéristique définie pour cette famille d'infrastructure.</div>
                                @else

                                    @foreach($groupedCaracs as $groupe => $caracs)
                                        <div class="card caracteristique-group mb-4">
                                        
                                            
                                            <div class="collapse show" id="groupe-{{ Str::slug($groupe) }}">
                                                <div class="card-body">
                                                    @foreach($caracs->where('parent_id', null)->sortBy('ordre') as $carac)
                                                        @include('infrastructures.partials.caracteristique-edit', [
                                                            'carac' => $carac,
                                                            'valeurs' => $valeurs,
                                                            'unitesDerivees' => $unitesDerivees,
                                                            'level' => 0
                                                        ])
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="sticky-bottom bg-white py-3 border-top">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="confirm-changes">
                                                <label class="form-check-label" for="confirm-changes">
                                                    Je confirme ces modifications
                                                </label>
                                            </div>
                                            <button type="submit" class="btn btn-primary" id="save-btn" disabled>
                                                <i class="bi bi-save"></i> Enregistrer toutes les modifications
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="col-lg-4">
            <!-- QR Code -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">QR Code d'identification</h5>
                </div>
                <div class="card-body">
                    <div class="qr-code-container text-center">
                        <div class="qr-code-title mb-2">Scannez pour accéder aux détails</div>

                        <!-- Canvas qui contiendra le QR code fusionné avec le logo -->
                        <canvas id="qrCanvas" width="180" height="180" style="image-rendering: pixelated;"></canvas>

                        <small class="text-muted d-block mt-2">Code ID: {{ $infrastructure->code }}</small>
                    </div>
                </div>

            </div>

            <!-- Statistiques -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h3>{{ $infrastructure->valeursCaracteristiques->count() }}</h3>
                            <small class="text-muted">Caractéristiques</small>
                        </div>
                        <div class="col-6">
                            <h3>{{ $infrastructure->InfrastructureImage->count() }}</h3>
                            <small class="text-muted">Photos</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Localisation -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Localisation</h5>
                </div>
                <div class="card-body">
                    @if($infrastructure->latitude && $infrastructure->longitude)
                        <div id="miniMap" style="height: 200px; width: 100%; border-radius: 4px;"></div>
                        <div class="text-center mt-2">
                            <a href="https://www.google.com/maps?q={{ $infrastructure->latitude }},{{ $infrastructure->longitude }}" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-map"></i> Voir sur Google Maps
                            </a>
                        </div>
                    @else
                        <div class="alert alert-warning">Aucune coordonnée géographique disponible</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<!-- Leaflet for Maps -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<!-- GLightbox JS -->
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activation des tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Gestion de l'affichage des enfants
    document.querySelectorAll('.toggle-children').forEach(btn => {
        btn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('bi-chevron-right')) {
                icon.classList.remove('bi-chevron-right');
                icon.classList.add('bi-chevron-down');
            } else {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-right');
            }
        });
    });


    // Détection des modifications
    document.querySelectorAll('#edit input, #edit select').forEach(el => {
        const initialValue = el.value;
        
        el.addEventListener('change', function() {
            if (this.value !== initialValue) {
                const indicator = this.closest('.d-flex').querySelector('.change-indicator');
                if (indicator) {
                    indicator.style.opacity = '1';
                    setTimeout(() => { indicator.style.opacity = '0'; }, 2000);
                }
            }
        });
        
    });

    // Activation du bouton de sauvegarde
    document.getElementById('confirm-changes').addEventListener('change', function() {
        document.getElementById('save-btn').disabled = !this.checked;
    });

    // Validation avant soumission
    document.getElementById('caracteristiques-form').addEventListener('submit', function(e) {
        // Ajouter ici toute validation supplémentaire si nécessaire
        if (!document.getElementById('confirm-changes').checked) {
            e.preventDefault();
            alert('Veuillez confirmer les modifications avant de soumettre.');
        };
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.toggle-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const target = document.querySelector(this.dataset.target);
                if (target.style.display === 'none') {
                    target.style.display = 'block';
                    this.classList.remove('bi-caret-right');
                    this.classList.add('bi-caret-down');
                } else {
                    target.style.display = 'none';
                    this.classList.remove('bi-caret-down');
                    this.classList.add('bi-caret-right');
                }
            });
        });
    });
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const qrData = "{{ route('infrastructures.printNoConnect', $infrastructure->id) }}";
        const logoSrc = "{{ asset(auth()->user()?->paysSelectionne()?->armoirie) }}";
        const canvas = document.getElementById("qrCanvas");
        const ctx = canvas.getContext("2d");

        // Dimensions QR haute définition
        const size = 150;
        canvas.width = size;
        canvas.height = size;

        // Génération du QR dans un canvas temporaire
        const qrTemp = document.createElement("div");
        new QRCode(qrTemp, {
            text: qrData,
            width: size,
            height: size,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        // Extraction QR en image
        setTimeout(() => {
            const qrImg = qrTemp.querySelector('img') || qrTemp.querySelector('canvas');
            const qrImage = new Image();
            qrImage.src = qrImg.src;

            qrImage.onload = function () {
                ctx.drawImage(qrImage, 0, 0, size, size);

                // Insertion logo HD
                const logo = new Image();
                logo.src = logoSrc;
                logo.onload = function () {
                    const logoSize = size * 0.3; // taille = 18% du QR
                    const x = (size - logoSize) / 2;
                    const y = (size - logoSize) / 2;

                    // Fond blanc pour contraste
                    ctx.fillStyle = "white";
                    ctx.fillRect(x - 5, y - 5, logoSize + 10, logoSize + 10);

                    ctx.drawImage(logo, x, y, logoSize, logoSize);
                };
            };
        }, 300);
    });
</script>


<script>
    // Initialisation des composants
    document.addEventListener('DOMContentLoaded', function() {


        // Mini Map
        @if($infrastructure->latitude && $infrastructure->longitude)
            const miniMap = L.map('miniMap').setView([
                {{ $infrastructure->latitude }}, 
                {{ $infrastructure->longitude }}
            ], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(miniMap);

            L.marker([
                {{ $infrastructure->latitude }}, 
                {{ $infrastructure->longitude }}
            ]).addTo(miniMap)
              .bindPopup("<b>{{ $infrastructure->libelle }}</b>");
        @endif

        // Lightbox
        GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            zoomable: true
        });

        // Onglets
        const tabElms = document.querySelectorAll('a[data-bs-toggle="tab"]');
        tabElms.forEach(tabEl => {
            tabEl.addEventListener('shown.bs.tab', function (event) {
                if (event.target.getAttribute('href') === '#view' && miniMap) {
                    setTimeout(() => miniMap.invalidateSize(), 100);
                }
            });
        });
    });

    function goBack() {
        window.history.back();
    }
</script>
@endsection