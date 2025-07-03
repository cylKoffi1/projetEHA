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
                            <form method="POST" action="{{ route('infrastructures.caracteristiques.updateMultiple', $infrastructure->id) }}">
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
                                        <div class="caracteristique-group">
                                            <h5 class="caracteristique-group-title">{{ $groupe ?? 'Autres caractéristiques' }}</h5>
                                                @php
                                                if (!function_exists('afficherCaracEditable')) {
                                                    function afficherCaracEditable($carac, $valeurs, $level = 0) {
                                                        $valeur = $valeurs[$carac->idCaracteristique] ?? null;
                                                        $type = strtolower($carac->type->libelleTypeCaracteristique ?? '');
                                                        $name = "caracteristiques[" . ($valeur?->idValeurCaracteristique ?? 'new_' . $carac->idCaracteristique) . "]";
                                                        $val = $valeur?->valeur ?? '';
                                                        $valeursPossibles = $carac->valeursPossibles ?? [];
                                                        $unite = $valeur?->unite?->symbole  ?? $carac->unite?->symbole ?? null;

                                                        $hasChildren = $carac->enfants->isNotEmpty();
                                                        $toggleId = 'carac-children-' . $carac->idCaracteristique;

                                                        echo '<div class="caracteristique-card mb-2" style="margin-left:' . ($level * 20) . 'px;">';

                                                        // Header avec icône + libellé
                                                        echo '<div class="d-flex align-items-center mb-1">';
                                                        if ($hasChildren) {
                                                            echo '<i class="bi bi-caret-right toggle-btn text-primary me-2" data-target="#' . $toggleId . '" style="cursor: pointer;"></i>';
                                                        } else {
                                                            echo '<i class="bi bi-dot text-muted me-2"></i>';
                                                        }
                                                        echo '<span class="fw-bold" style="color: black;">' . e($carac->libelleCaracteristique) . '</span>';
                                                        echo '<span class="text-muted ms-2 small">Ordre: ' . $carac->ordre . '</span>';
                                                        echo '</div>';

                                                        // Champ de saisie
                                                        if ($type === 'liste') {
                                                            echo '<select name="' . $name . '" class="form-select">';
                                                            echo '<option value="">Sélectionner</option>';
                                                            foreach ($valeursPossibles as $option) {
                                                                $selected = $option->valeur == $val ? 'selected' : '';
                                                                echo '<option value="' . e($option->valeur) . '" ' . $selected . '>' . e($option->valeur) . '</option>';
                                                            }
                                                            echo '</select>';
                                                        } elseif ($type === 'boolean') {
                                                            echo '<div class="form-check form-switch">';
                                                            echo '<input type="hidden" name="' . $name . '" value="0">';
                                                            echo '<input type="checkbox" class="form-check-input" name="' . $name . '" value="1" ' . ($val == 1 ? 'checked' : '') . '>';
                                                            echo '<label class="form-check-label">Oui / Non</label>';
                                                            echo '</div>';
                                                        } elseif ($type === 'nombre') {
                                                            echo '<div class="input-group">';
                                                            echo '<input type="number" step="any" name="' . $name . '" value="' . e($val) . '" class="form-control">';
                                                            if ($unite) {
                                                                echo '<span class="input-group-text">' . e($unite) . '</span>';
                                                            }
                                                            echo '</div>';
                                                        } else {
                                                            echo '<input type="text" name="' . $name . '" value="' . e($val) . '" class="form-control">';
                                                        }

                                                        if ($unite && $type !== 'nombre') {
                                                            echo '<small class="text-muted">Unité: ' . e($unite->libelleUnite) . ' (' . e($unite->symbole) . ')</small>';
                                                        }

                                                        echo '</div>'; // .caracteristique-card

                                                        // Affichage des enfants (masqué par défaut)
                                                        if ($hasChildren) {
                                                            echo '<div id="' . $toggleId . '" class="carac-children" style="display: none;">';
                                                            foreach ($carac->enfants->sortBy('ordre') as $child) {
                                                                afficherCaracEditable($child, $valeurs, $level + 1);
                                                            }
                                                            echo '</div>';
                                                        }
                                                    }
                                                }
                                                @endphp

                                                @foreach($caracs->where('parent_id', null)->sortBy('ordre') as $carac)
                                                    @php afficherCaracEditable($carac, $valeurs); @endphp
                                                @endforeach

                                        </div>
                                    @endforeach

                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Enregistrer toutes les modifications
                                        </button>
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
                    <div class="qr-code-container">
                        <div class="qr-code-title">Scannez pour accéder aux détails</div>
                        <center><div id="qrCode" class="mb-2"></div></center>
                        <small class="text-muted">Code ID: {{ $infrastructure->code }}</small>
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


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const qrData = "{{ route('infrastructures.printNoConnect', $infrastructure->id) }}";

        new QRCode(document.getElementById("qrCode"), {
            text: qrData,
            width: 180,
            height: 180,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

    });

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