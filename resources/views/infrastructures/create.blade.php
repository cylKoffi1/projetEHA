@extends('layouts.app')
<!-- GLightbox CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />

<!-- GLightbox JS -->
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>

@section('content')
<script>
    const IS_EDIT_MODE = {{ isset($infrastructure) ? 'true' : 'false' }};
</script>

<style>
    #map {
        height: 300px;
        width: 100%;
    }

    .card-infrastructure {
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: none;
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, #3a7bd5 0%, #00d2ff 100%);
        color: white;
        padding: 1.5rem;
        border-bottom: none;
    }
    
    .section-title {
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 1px solid #eee;
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
        position: relative;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 60px;
        height: 3px;
        background: #3a7bd5;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
        border-radius: 6px;
        padding: 0.6rem 1rem;
        border: 1px solid #e0e0e0;
        transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #3a7bd5;
        box-shadow: 0 0 0 0.25rem rgba(58, 123, 213, 0.15);
    }
    
    .image-upload-container {
        margin-top: 1rem;
    }    
    
    .caracteristique-card {
        background-color: #f8fafc;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1rem;
        border-left: 4px solid #3a7bd5;
    }
    #galleryPreview .btn-danger {
        background-color: rgba(220, 53, 69, 0.9);
        border: none;
        font-weight: bold;
        font-size: 1.2rem;
        line-height: 1;
        padding: 0.2rem 0.6rem;
    }

    .btn-primary {
        background-color: #3a7bd5;
        border-color: #3a7bd5;
        padding: 0.6rem 1.5rem;
        border-radius: 6px;
        font-weight: 500;
    }
    
    .btn-outline-secondary {
        border-radius: 6px;
        padding: 0.6rem 1.5rem;
        font-weight: 500;
    }
    
    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 500;
        border: none;
        padding: 0.75rem 1.5rem;
    }
    
    .nav-tabs .nav-link.active {
        color: #3a7bd5;
        background: transparent;
        border-bottom: 3px solid #3a7bd5;
    }
    
    .tab-content {
        padding: 1.5rem 0;
    }
    
    .map-container {
        height: 300px;
        border-radius: 8px;
        overflow: hidden;
        margin-top: 1rem;
        border: 1px solid #e0e0e0;
    }
    
    .coordinates-input {
        position: relative;
    }
    
    .coordinates-input .btn {
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        padding: 0.25rem 0.5rem;
    }
    
    .characteristics-table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .characteristics-table td, .characteristics-table th {
        vertical-align: middle;
    }



    /* Styles pour le tableau des caractéristiques */
#caracteristiquesTable {
    border-collapse: separate;
    border-spacing: 0;
}

#caracteristiquesTable thead th {
    position: sticky;
    top: 0;
    background-color: #f8f9fa;
    z-index: 10;
}

.carac-row {
    transition: background-color 0.2s;
}

.carac-row:hover {
    background-color: #f8f9fa;
}

.carac-row.level-1 {
    background-color: #f8fafc;
}

.carac-row.level-2 {
    background-color: #f1f8fe;
}

.expand-icon {
    cursor: pointer;
    font-size: 1rem;
    vertical-align: middle;
}

.child-content {
    padding-left: 20px;
}


.expand-icon:hover {
    color: #3a7bd5;
}

.child-container {
    background-color: #f8f9fa;
}

.child-content {
    border-left: 3px solid #dee2e6;
}

.toggle-children {
    padding: 0.15rem 0.3rem;
    font-size: 0.8rem;
}

/* Style pour les lignes enfants */
tr[data-parent-id] {
    background-color: rgba(58, 123, 213, 0.05);
}
.expand-icon {
    cursor: pointer;
    margin-right: 5px;
}
.carac-row[data-parent] {
    display: none;
}

</style>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-12">
                <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>
                        {{ isset($infrastructure) ? 'Modifier l\'infrastructure' : 'Nouvelle infrastructure' }}
                    </h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="">Infrastructures</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            
                                {{ isset($infrastructure) ? 'Modifier l\'infrastructure' : 'Nouvelle infrastructure' }}
                            

                        </li>

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
<section id="multiple-column-form">
    <div class="row match-height">
        <div class="col-12">
            <div class="card card-infrastructure">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title text-white mb-0">
                        {{ isset($infrastructure) ? 'Modification de l\'infrastructure' : 'Nouvelle infrastructure' }}
                    </h4>

                        <div class="col-2">
                            @foreach ($pays as $alpha3 => $nom_fr_fr)
                                <input type="text" value="{{ $nom_fr_fr }}" class="form-control" readonly>
                                <input type="hidden" value="{{ $alpha3 }}" id="paysSelect">
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="card-content">
                    <div class="card-body">
                        <form id="infrastructure-form" 
                            method="POST" 
                            action="{{ isset($infrastructure) ? route('infrastructures.update', $infrastructure->id) : route('infrastructures.store') }}" 
                            enctype="multipart/form-data" >
                            
                            @csrf
                            @if(isset($infrastructure))
                                @method('PUT')
                            @endif

                            <!-- Onglets -->
                            <ul class="nav nav-tabs" id="infraTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general" type="button">Informations générales</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#localisation" type="button">Localisation</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#Infrastructure_rattachement" type="button">Infrastructure rattachée</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#caracteristiques" type="button">Caractéristiques</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#media" type="button">Média</button>
                                </li>
                            </ul>

                            <!-- Contenu des onglets -->
                            <div class="tab-content mt-3" id="infraTabsContent">

                                <!-- Onglet Informations générales -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Domaine *</label>
                                            <select class="form-select" name="domaine" id="domaineSelect" data-url="{{ url('/get-sous-domaines') }}" required>
                                                <option value="">Sélectionner un domaine</option>
                                                @foreach ($domaines as $domaine)
                                                    <option value="{{ $domaine->code }}"
                                                        {{ (old('domaine') ?? $infrastructure->familleDomaine->code_domaine ?? '') == $domaine->code ? 'selected' : '' }}>
                                                        {{ $domaine->libelle }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Sous domaine *</label>
                                            <select class="form-select" name="sous_domaine" id="sous_domaineSelect" required>
                                                <option value="">Sélectionner un sous domaine</option>
                                                @if(old('sous_domaine') || isset($infrastructure))
                                                    <option value="{{ old('sous_domaine', $infrastructure->familleDomaine->code_sdomaine ?? '') }}" selected>
                                                        {{ old('lib_sous_domaine', $infrastructure->familleDomaine->sousdomaine->lib_sous_domaine ?? '') }}
                                                    </option>
                                                @endif
                                            </select>
                                        </div>


                                        <div class="col-md-4">
                                            <label class="form-label">Famille *</label>
                                            <select class="form-select" id="code_famille_infrastructure" name="code_famille_infrastructure" required>
                                                <option value="">Sélectionner une famille</option>
                                                @if(isset($infrastructure))
                                                    
                                                    <option 
                                                        value="{{ $infrastructure->code_Ssys }}" 
                                                        data-idfamille="{{ $infrastructure->familleInfrastructure->idFamille }}" 
                                                        selected>
                                                        {{ $infrastructure->familleInfrastructure->libelleFamille ?? 'Famille inconnue' }}
                                                    </option>
                                                @endif

                                            </select>
                                        </div>

                                        <div class="col-md-8">
                                            <label class="form-label">Nom de l'infrastructure *</label>
                                            <input type="text" class="form-control" id="libelle" name="libelle"
                                                value="{{ old('libelle', $infrastructure->libelle ?? '') }}" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Date de création</label>
                                            <input type="date" class="form-control" id="date_operation" name="date_operation"
                                                value="{{ old('date_operation', $infrastructure->date_operation ?? '') }}">
                                        </div>
                                    </div>
                                </div>

                                <!-- Onglet Localisation -->
                                <div class="tab-pane fade" id="localisation" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Localité *</label>
                                            <lookup-select name="code_localite" id="niveau1Select" required>
                                                @if(isset($infrastructure))
                                                    <option value="{{ $infrastructure->code_localite }}" selected>
                                                        {{ $infrastructure->localisation->libelle ?? 'Localité inconnue' }}
                                                    </option>
                                                @endif
                                            </lookup-select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Niveau</label>
                                            <select id="niveau2Select" class="form-select" disabled>
                                                @if(isset($infrastructure))
                                                    <option selected>{{ old('niveau', $infrastructure->localisation->id_niveau ?? '') }}</option>
                                                @else
                                                    <option value="">Sélectionnez un niveau</option>
                                                @endif
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Découpage</label>
                                            <select id="niveau3Select" class="form-select" disabled>
                                                @if(isset($infrastructure))
                                                    <option selected>{{ old('code_decoupage', $infrastructure->localisation->decoupage->libelle_decoupage ?? '') }}</option>
                                                @else
                                                    <option value="">Sélectionnez un découpage</option>
                                                @endif
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Latitude</label>
                                            <input type="text" class="form-control" name="latitude" id="latitude"
                                                value="{{ old('latitude', $infrastructure->latitude ?? '') }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Longitude</label>
                                            <input type="text" class="form-control" name="longitude" id="longitude"
                                                value="{{ old('longitude', $infrastructure->longitude ?? '') }}">
                                        </div>

                                        <div class="col-12">
                                            <div class="map-container" id="map"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="Infrastructure_rattachement" role="tabpanel">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <h5 class="section-title">Infrastructures rattachées</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Infrastructure de rattachement (géographique)</label>
                                        <select class="form-select" name="code_infras_rattacher">
                                            <option value="">Aucune</option>
                                            @foreach($infrasExistantes as $infra)
                                                <option value="{{ $infra->code }}" {{ old('code_infras_rattacher', $infrastructure->code_infras_rattacher ?? '') == $infra->code ? 'selected' : '' }}>
                                                    {{ $infra->libelle }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                </div>
                                @php
    function afficherCaracRow($carac, $valeurs, $niveau = 0, $parentId = null) {
        $valeur = $valeurs[$carac->idCaracteristique] ?? '';
        $type = strtolower($carac->type->libelleTypeCaracteristique ?? '');
        $unite = $valeur?->unite?->symbole ?? $carac->unite?->symbole ?? null;
        $hasChildren = $carac->enfants && count($carac->enfants);
        $padding = $niveau * 20;

        $rowId = "carac_row_" . $carac->idCaracteristique;
        $parentAttr = $parentId ? "data-parent='{$parentId}'" : '';

        echo "<tr id='{$rowId}' class='carac-row level-{$niveau}' {$parentAttr}>";
        echo "<td>" . ucfirst($type) . "</td>";
        echo "<td style='padding-left: {$padding}px;'>";
        if ($hasChildren) {
            echo "<span class='expand-icon' data-id='{$carac->idCaracteristique}'>&#9654;</span> ";
        }
        echo e($carac->libelleCaracteristique) . "</td>";
        echo "<td>";

        // Type de champ
        if ($type === 'liste') {
            echo "<select class='form-select form-select-sm' name='caracteristiques[{$carac->idCaracteristique}]'>";
            echo "<option value=''>-- Choisir --</option>";
            foreach ($carac->valeursPossibles as $opt) {
                $selected = $opt->valeur == $valeur ? 'selected' : '';
                echo "<option value='" . e($opt->valeur) . "' $selected>" . e($opt->valeur) . "</option>";
            }
            echo "</select>";
        } elseif ($type === 'boolean') {
            $checked = $valeur == 1 ? 'checked' : '';
            echo "<input type='hidden' name='caracteristiques[{$carac->idCaracteristique}]' value='0'>";
            echo "<input type='checkbox' class='form-check-input' name='caracteristiques[{$carac->idCaracteristique}]' value='1' $checked>";
        } elseif ($type === 'nombre') {
            echo "<div class='input-group'>";
            echo "<input type='number' step='any' name='caracteristiques[{$carac->idCaracteristique}]' value='" . e($valeur) . "' class='form-control form-control-sm'>";
            if ($unite) {
                echo "<span class='input-group-text'>" . e($unite) . "</span>";
            }
            echo "</div>";
        } else {
            echo "<input type='text' name='caracteristiques[{$carac->idCaracteristique}]' value='" . e($valeur) . "' class='form-control form-control-sm'>";
        }

        echo "</td>";
        echo "<td></td>";
        echo "</tr>";

        if ($hasChildren) {
            foreach ($carac->enfants as $enfant) {
                afficherCaracRow($enfant, $valeurs, $niveau + 1, $carac->idCaracteristique);
            }
        }
    }
@endphp

                                <!-- Onglet Caractéristiques -->
                                <div class="tab-pane fade" id="caracteristiques" role="tabpanel">
                                    <div id="caracteristiquesHeriteesSection">
                                        <h5 class="section-title">Caractéristiques de la famille</h5>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i> Les caractéristiques marquées d'une flèche peuvent être développées pour voir leurs sous-caractéristiques
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="caracteristiquesTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Type</th>
                                                        <th>Libellé</th>
                                                        <th>Valeur</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="caracteristiquesHeriteesContainer">
                                                    @php
                                                        $caracs = $caracs ?? [];
                                                        $valeurs = $valeursExistantes ?? [];
                                                    @endphp

                                                    @foreach($caracs->where('parent_id', null) as $carac)
                                                        @php afficherCaracRow($carac, $valeurs) @endphp
                                                    @endforeach
                                                </tbody>

                                            </table>

                                        </div>
                                    </div>
                                </div>

                                <!-- Onglet Média -->
                                <div class="tab-pane fade" id="media" role="tabpanel">
                                    <div class="mb-3">
                                        <label class="form-label">Galerie de photos</label>
                                        <input type="file" id="galleryInput" name="gallery[]" multiple accept="image/*" class="form-control" onchange="addToGallery(this)">

                                        <div id="galleryPreview" class="row mt-3" style="gap: 10px;"></div>
                                        @if(isset($infrastructure) && $infrastructure->InfrastructureImage->count())
                                            <div class="row mt-3" id="existingGallery">
                                                @foreach($infrastructure->InfrastructureImage as $image)
                                                    <div class="col-md-3 mb-3" id="image_{{ $image->id }}">
                                                        <div class="position-relative">
                                                            <a href="{{ asset($image->chemin_image) }}" class="glightbox" data-gallery="gallery1" data-title="{{ $infrastructure->libelle }}"  onclick="event.preventDefault();">
                                                                <img src="{{ asset($image->chemin_image) }}" class="img-thumbnail" style="width: 100%; height: 180px; object-fit: cover;">
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeExistingImage({{ $image->id }}, '{{ $image->infrastructure_code }}')">
                                                                &times;
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif



                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Champs cachés -->
                            <input type="hidden" id="niveau" name="niveau" value="{{ old('niveau', $infrastructure->localisation->niveau ?? '') }}">
                            <input type="hidden" id="code_decoupage" name="code_decoupage" value="{{ old('code_decoupage', $infrastructure->localisation->code_decoupage ?? '') }}">

                            <!-- Boutons -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('infrastructures.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-1"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i> {{ isset($infrastructure) ? 'Mettre à jour' : 'Créer' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.expand-icon').forEach(function (icon) {
            icon.addEventListener('click', function () {
                const id = this.dataset.id;
                const rows = document.querySelectorAll(`tr[data-parent='${id}']`);
                const expanded = this.innerHTML === '▼';

                this.innerHTML = expanded ? '▶' : '▼';

                rows.forEach(row => {
                    row.style.display = expanded ? 'none' : 'table-row';

                    // Si on ferme, fermer aussi les enfants des enfants
                    if (expanded) {
                        const childId = row.id.replace('carac_row_', '');
                        const subRows = document.querySelectorAll(`tr[data-parent='${childId}']`);
                        subRows.forEach(r => r.style.display = 'none');
                        const childIcon = row.querySelector('.expand-icon');
                        if (childIcon) childIcon.innerHTML = '▶';
                    }
                });
            });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const domaineSelect = document.getElementById('domaineSelect');
        const sousDomaineSelect = document.getElementById('sous_domaineSelect');

        domaineSelect.addEventListener('change', function () {
            const domaineCode = this.value;
            const url = this.getAttribute('data-url') + '/' + domaineCode;

            sousDomaineSelect.innerHTML = '<option value="">Chargement...</option>';

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    sousDomaineSelect.innerHTML = '<option value="">Sélectionner un sous domaine</option>';
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.code_sous_domaine;
                        option.textContent = item.lib_sous_domaine;
                        sousDomaineSelect.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erreur chargement sous-domaines :', error);
                    sousDomaineSelect.innerHTML = '<option value="">Erreur chargement</option>';
                });
        });
    });
</script>

<script>
    const valeursExistantes = @json($valeursExistantes ?? []);
// Fonction pour générer les champs de formulaire dynamiquement
function generateInputField(carac, valeur = '') {
    const name = `caracteristiques[${carac.idCaracteristique}]`;
    const type = (carac.type.libelleTypeCaracteristique || '').toLowerCase();

    switch (type) {
        case 'Liste':
            const options = carac.valeurs_possibles || [];
            return `
                <select name="${name}" class="form-select form-select-sm">
                    ${options.map(opt => `<option value="${opt}" ${opt === valeur ? 'selected' : ''}>${opt}</option>`).join('')}
                </select>`;
        case 'Nombre':
            return `
                <input type="number" name="${name}" value="${valeur}" class="form-control form-control-sm">`;
        case 'Boolean':
            return `
                <input type="checkbox" name="${name}" value="1" ${valeur == 1 ? 'checked' : ''}> Oui`;
        default:
            return `
                <input type="text" name="${name}" value="${valeur}" class="form-control form-control-sm">`;
    }
}

// Fonction pour afficher une caractéristique et ses enfants
function renderCaracteristiqueRow(carac, level = 0, parentId = null) {
    const valeur = valeursExistantes[carac.idCaracteristique] ?? '';
    const hasChildren = Array.isArray(carac.enfants) && carac.enfants.length > 0;
    const rowId = `carac_row_${carac.idCaracteristique}`;
    const childContainerId = `child_container_${carac.idCaracteristique}`;

    const row = document.createElement('tr');
    row.id = rowId;
    row.dataset.parentId = parentId ?? '';
    row.classList.add(`carac-level-${level}`);

    const indent = level * 20;

    row.innerHTML = `
        <td>${carac.type.libelleTypeCaracteristique}</td>
        <td style="padding-left: ${indent}px;">
            ${hasChildren ? `<span class="toggle-icon" data-id="${carac.idCaracteristique}">&#9654;</span>` : ''}
            ${carac.libelleCaracteristique}
        </td>
        <td>${generateInputField(carac, valeur)}</td>
        <td>${hasChildren ? `<button class="btn btn-sm btn-outline-secondary" onclick="toggleChildren(${carac.idCaracteristique})">▼</button>` : ''}</td>
    `;

    const rows = [row];

    if (hasChildren) {
        const childRow = document.createElement('tr');
        childRow.id = childContainerId;
        childRow.style.display = 'none';
        childRow.innerHTML = `<td colspan="4"><table class="table table-sm mb-0"><tbody></tbody></table></td>`;
        rows.push(childRow);
    }

    return rows;
}


// Fonction principale pour afficher toutes les caractéristiques
function renderCaracteristiquesTable(caracs) {
    const container = document.getElementById('caracteristiquesHeriteesContainer');
    container.innerHTML = '';
    window.caracteristiquesData = {};

    function buildRecursive(caracsArray, parentId = null, level = 0) {
        caracsArray.forEach(carac => {
            window.caracteristiquesData[carac.idCaracteristique] = carac;
            const rows = renderCaracteristiqueRow(carac, parentId, level);
            rows.forEach(r => container.appendChild(r));
        });
    }

    buildRecursive(caracs);

    document.querySelectorAll('.toggle-children, .expand-icon').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const carac = window.caracteristiquesData[id];
            const row = document.getElementById(`carac_row_${id}`);
            const childContainer = document.getElementById(`child_container_${id}`);
            const icon = row.querySelector('.expand-icon');

            if (childContainer.style.display === 'none') {
                const contentDiv = childContainer.querySelector('.child-content');
                if (!contentDiv.hasChildNodes()) {
                    const table = document.createElement('table');
                    table.className = 'table table-sm mb-0';
                    const tbody = document.createElement('tbody');
                    table.appendChild(tbody);
                    contentDiv.appendChild(table);

                    carac.enfants.forEach(enf => {
                        appendCaracteristiqueRecursive(enf, tbody, 1, carac.idCaracteristique);
                    });
                }
                childContainer.style.display = 'table-row';
                if (icon) {
                    icon.classList.remove('bi-chevron-right');
                    icon.classList.add('bi-chevron-down');
                }
            } else {
                childContainer.style.display = 'none';
                if (icon) {
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                }
            }
        });
    });
}
function appendCaracteristiqueRecursive(carac, tbody, level = 1, parentId = null) {
    const [row, childRow] = renderCaracteristiqueRow(carac, parentId, level);
    tbody.appendChild(row);
    if (childRow) tbody.appendChild(childRow);

    if (Array.isArray(carac.enfants)) {
        carac.enfants.forEach(enfant => {
            appendCaracteristiqueRecursive(enfant, tbody, level + 1, carac.idCaracteristique);
        });
    }
}


// Gestion du changement de famille
document.getElementById('code_famille_infrastructure').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const idFamille = selectedOption.dataset.idfamille;
    
    if (!idFamille) {
        document.getElementById('caracteristiquesHeriteesContainer').innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4">
                    <p class="text-muted">Sélectionnez une famille pour voir ses caractéristiques</p>
                </td>
            </tr>
        `;
        return;
    }
    
    fetch(`{{ url('/') }}/familles/${idFamille}/caracteristiques`)
        .then(res => res.json())
        .then(data => {
            console.log("Caractéristiques reçues:", data);
            //renderCaracteristiquesTable(data);
        })
        .catch(err => {
            console.error('Erreur lors du chargement des caractéristiques:', err);
            document.getElementById('caracteristiquesHeriteesContainer').innerHTML = `
                <tr>
                    <td colspan="4" class="text-center py-4">
                        <p class="text-danger">Erreur lors du chargement des caractéristiques</p>
                    </td>
                </tr>
            `;
        });
});

// Si on est en mode édition, charger les caractéristiques au démarrage
if (IS_EDIT_MODE) {
    document.addEventListener('DOMContentLoaded', function() {
        const familleSelect = document.getElementById('code_famille_infrastructure');
        if (familleSelect && familleSelect.value) {
            familleSelect.dispatchEvent(new Event('change'));
        }
    });
}

function renderCaracteristiquesTreeProgressif(caracs, container, niveau = 0) {
    caracs.forEach(carac => {
        const valeur = valeursExistantes[carac.idCaracteristique] ?? null;
        const indent = 20 * niveau;

        const wrapper = document.createElement('div');
        wrapper.className = 'col-12 mb-2';
        wrapper.style.marginLeft = `${indent}px`;

        const innerId = `children_of_${carac.idCaracteristique}`;

        wrapper.innerHTML = `
            <div class="caracteristique-card d-flex justify-content-between align-items-center">
                <div class="w-100">
                    <label class="form-label">${carac.libelleCaracteristique}</label>
                    ${generateInputField(carac, valeur)}
                </div>
                ${carac.enfants?.length ? `
                    <button class="btn btn-sm btn-outline-primary ms-2" onclick="afficherEnfants(${carac.idCaracteristique})">
                        OK
                    </button>
                ` : ''}
            </div>
            <div id="${innerId}" class="row mt-2" style="display: none;"></div>
        `;

        container.appendChild(wrapper);

        // Enfants déjà stockés pour usage plus tard dans onclick
        if (carac.enfants?.length) {
            window.__caracteristiqueChildrenMap = window.__caracteristiqueChildrenMap || {};
            window.__caracteristiqueChildrenMap[carac.idCaracteristique] = carac.enfants;
        }
    });
}
function afficherEnfants(parentId) {
    const enfants = window.__caracteristiqueChildrenMap?.[parentId] || [];
    const container = document.getElementById(`children_of_${parentId}`);
    if (!container || enfants.length === 0) return;

    container.style.display = 'flex'; // ou 'block' selon ton layout
    renderCaracteristiquesTreeProgressif(enfants, container, 1); // profondeur +1
}

function renderCaracteristiquesInTable(caracs, container, parentId = null, level = 0) {
    caracs.forEach(carac => {
        const id = carac.idCaracteristique;
        const valeur = valeursExistantes[id] ?? '';
        const children = carac.enfants ?? [];
        const row = document.createElement('tr');

        row.dataset.id = id;
        row.dataset.parentId = parentId;
        row.classList.add(`child-row-${parentId}`);
        if (parentId) row.style.display = 'none';

        row.innerHTML = `
            <td style="padding-left: ${20 * level}px;">${carac.libelleCaracteristique}</td>
            <td>${carac.type.libelleTypeCaracteristique}</td>
            <td>${generateInputField(carac, valeur)}</td>
            <td>
                ${children.length ? `<button class="btn btn-sm btn-outline-primary" onclick="toggleChildren(${id})">Afficher</button>` : ''}
            </td>
        `;

        container.appendChild(row);

        if (children.length) {
            renderCaracteristiquesInTable(children, container, id, level + 1);
        }
    });
}

function toggleChildren(id) {
    const childRow = document.getElementById(`child_container_${id}`);
    const icon = document.querySelector(`.toggle-icon[data-id="${id}"]`);
    
    if (!childRow) return;

    if (childRow.style.display === 'none') {
        // Affiche enfants
        const carac = window.caracteristiquesData[id];
        const tbody = childRow.querySelector('tbody');
        tbody.innerHTML = '';

        carac.enfants.forEach(enfant => {
            const rows = renderCaracteristiqueRow(enfant, 1, id);
            rows.forEach(r => tbody.appendChild(r));
        });

        childRow.style.display = '';
        icon.innerHTML = '&#9660;'; // flèche vers le bas
    } else {
        childRow.style.display = 'none';
        icon.innerHTML = '&#9654;'; // flèche vers la droite
    }
}



</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
// Gestion du changement de domaine pour charger les familles

document.getElementById('domaineSelect').addEventListener('change', function() {
    const codeDomaine = this.value;
    const familleSelect = document.getElementById('code_famille_infrastructure');

    if (familleSelect) {
            familleSelect.innerHTML = '<option value="">Chargement...</option>';
            familleSelect.disabled = true;
        } else {
            console.warn('L’élément #code_famille_infrastructure est introuvable dans le DOM');
        }


    if (codeDomaine) {
        fetch(`{{ url("/")}}/familles-by-domaine/${codeDomaine}`)
            .then(res => res.json())
            .then(data => {
                familleSelect.innerHTML = '<option value="">Sélectionner une famille</option>';
                data.forEach(famille => {
                    const option = document.createElement('option');
                    option.value = famille.code_Ssys;
                    option.textContent = famille.libelleFamille;
                    option.dataset.idfamille = famille.idFamille;
                    familleSelect.appendChild(option);
                });
                familleSelect.disabled = false;

                if (IS_EDIT_MODE && familleSelect.value) {
                    familleSelect.dispatchEvent(new Event('change'));
                } 
            })
            .catch(() => {
                familleSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                familleSelect.disabled = false;
            });
    } else {
        familleSelect.innerHTML = '<option value="">Sélectionner une famille</option>';
        familleSelect.disabled = false;
    }
});
});
// Gestion de la localisation
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                document.getElementById('latitude').value = position.coords.latitude.toFixed(6);
                document.getElementById('longitude').value = position.coords.longitude.toFixed(6);
                updateMap(position.coords.latitude, position.coords.longitude);
            },
            error => {
                alert('Impossible d\'obtenir votre position : ' + error.message, 'error');
            }
        );
    } else {
        alert('La géolocalisation n\'est pas supportée par votre navigateur', 'error');
    }
}

// Initialisation de la carte (Leaflet.js serait nécessaire)
function initMap() {
    // Initialiser la carte ici
    console.log('Map would be initialized here');
}

function updateMap(lat, lng) {
    // Mettre à jour la carte avec les nouvelles coordonnées
    console.log('Map would be updated to:', lat, lng);
}

let map;
let marker;

function initMap() {
    const paysCode = document.getElementById('paysSelect').value;
    const nomPays = `{!! $nomPays !!}`; // Ce que tu as déjà
    const lat = parseFloat(document.getElementById('latitude').value);
    const lng = parseFloat(document.getElementById('longitude').value);

    if (!isNaN(lat) && !isNaN(lng)) {
        map = L.map('map').setView([lat, lng], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 18,
        }).addTo(map);
        marker = L.marker([lat, lng]).addTo(map);
        return;
    }

    if (!nomPays) {
        // Si aucun nom, on centre sur Afrique
        map = L.map('map').setView([0, 20], 3); // Vue Afrique entière
        return;
    }

    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(nomPays)}`)
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);
                const zoom = 6;

                map = L.map('map').setView([lat, lon], zoom);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 18,
                }).addTo(map);
            } else {
                // Si pays introuvable : fallback
                map = L.map('map').setView([0, 20], 3);
            }
        })
        .catch(err => {
            console.error("Erreur lors de la localisation du pays :", err);
            map = L.map('map').setView([0, 20], 3);
        });
}


function updateMap(lat, lng) {
    if (!map) return;

    const latNum = parseFloat(lat);
    const lngNum = parseFloat(lng);

    if (!isNaN(latNum) && !isNaN(lngNum)) {
        map.setView([latNum, lngNum], 12);

        // Supprimer l'ancien marqueur si présent
        if (marker) {
            map.removeLayer(marker);
        }

        marker = L.marker([latNum, lngNum]).addTo(map);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    initMap(); // Initialiser la carte

    const currentLocaliteId = '{{ $infrastructure->code_localite ?? '' }}';
    const currentLocaliteLabel = '{{ $infrastructure->localisation->libelle ?? '' }}';
    const selectEl = document.getElementById("niveau1Select");

    if (currentLocaliteId && currentLocaliteLabel && selectEl && typeof selectEl.setOptions === 'function') {
        fetch(`{{ url('/') }}/get-localites/${paysCode}`)
            .then(res => res.json())
            .then(data => {
                const options = data.map(localite => ({
                    value: localite.id,
                    text: localite.libelle,
                }));

                // Injecte manuellement la localité si absente
                if (!options.some(opt => opt.value == currentLocaliteId)) {
                    options.unshift({
                        value: currentLocaliteId,
                        text: currentLocaliteLabel
                    });
                }

                selectEl.setOptions(options);
                selectEl.setSelected(currentLocaliteId);
            });
    }



    


    const paysCode = document.getElementById('paysSelect').value;

    if (paysCode) {
        fetch(`{{ url('/') }}/get-localites/${paysCode}`)
            .then(res => res.json())
            .then(data => {
                const options = data.map(localite => ({
                    value: localite.id,
                    text: localite.libelle,
                    codeRattachement: localite.code_rattachement,
                    nom: localite.libelle
                }));

                const selectEl = document.getElementById("niveau1Select");
                selectEl.setOptions(options);

                selectEl.addEventListener('change', async function () {
                    const selectedValue = this.value;
                    const selectedOption = options.find(opt => opt.value == selectedValue);
                    const localiteName = selectedOption?.nom;
                    const pays = `{!! $nomPays !!}`;

                    if (!localiteName || !pays) return;

                    try {
                        const query = encodeURIComponent(`${localiteName}, ${pays}`);
                        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${query}`;

                        const response = await fetch(url, { headers: { 'Accept-Language': 'fr' } });
                        const results = await response.json();

                        if (results.length > 0) {
                            const first = results[0];
                            document.getElementById('latitude').value = parseFloat(first.lat).toFixed(6);
                            document.getElementById('longitude').value = parseFloat(first.lon).toFixed(6);
                            updateMap(first.lat, first.lon);
                        } else {
                            alert("Coordonnées introuvables pour cette localité.", 'error');
                        }
                    } catch (err) {
                        console.error("Erreur lors de la recherche des coordonnées :", err);
                        alert("Erreur lors de la récupération des coordonnées.", 'error');
                    }
                });

                selectEl.addEventListener("change", function () {
                    const localiteId = this.value;
                    if (localiteId) {
                        fetch(`{{ url('/') }}/get-decoupage-niveau/${localiteId}`)
                            .then(res => res.json())
                            .then(data => {
                                document.getElementById("niveau2Select").innerHTML = `<option value="${data.niveau}">${data.niveau}</option>`;
                                document.getElementById("niveau3Select").innerHTML = `<option value="${data.code_decoupage}">${data.libelle_decoupage}</option>`;

                                document.getElementById("niveau2Select").disabled = false;
                                document.getElementById("niveau3Select").disabled = false;

                                document.getElementById("niveau").value = data.niveau;
                                document.getElementById("code_decoupage").value = data.code_decoupage;
                            })
                            .catch(err => {
                                console.error('Erreur récupération découpage:', err);
                                document.getElementById("niveau2Select").innerHTML = `<option value="">Erreur</option>`;
                                document.getElementById("niveau3Select").innerHTML = `<option value="">Erreur</option>`;
                            });
                    }
                });
            });
    }

        const lightbox = GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true,
            zoomable: true
        });
});




// Forcer le redimensionnement de Leaflet à l'ouverture de l'onglet "Localisation"
document.querySelector('button[data-bs-target="#localisation"]').addEventListener('shown.bs.tab', function () {
    if (map) {
        map.invalidateSize();
    }
});

</script>
<script>
let selectedGalleryFiles = [];

function addToGallery(input) {
    const newFiles = Array.from(input.files);

    newFiles.forEach(file => {
        if (!file.type.startsWith('image/')) return;

        const id = `img_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`;
        selectedGalleryFiles.push({ id, file });

        console.log('Fichier chargé :', file.name); // 🔍

        const reader = new FileReader();
        reader.onload = function (e) {
            const container = document.createElement('div');
            container.classList.add('col-md-2', 'mb-3');
            container.id = id;
            container.innerHTML = `
                <div class="position-relative">
                    <a href="${e.target.result}" class="glightbox" data-gallery="newUploads" data-title="Nouvelle image">
                        <img src="${e.target.result}" class="img-thumbnail" style="width: 100%; height: 150px; object-fit: cover;">
                    </a>
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeFromGallery('${id}')">
                        &times;
                    </button>
                </div>
            `;
            document.getElementById('galleryPreview').appendChild(container);

            // Réinitialise GLightbox pour activer le zoom
            setTimeout(() => {
                GLightbox({
                    selector: '.glightbox',
                    touchNavigation: true,
                    loop: true,
                    zoomable: true
                });
            }, 100);
        };
        reader.readAsDataURL(file);
    });

    input.value = ''; // réinitialise l'input file
}



function removeFromGallery(id) {
    selectedGalleryFiles = selectedGalleryFiles.filter(item => item.id !== id);
    const imgDiv = document.getElementById(id);
    if (imgDiv) imgDiv.remove();
}

document.getElementById('infrastructure-form').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    selectedGalleryFiles.forEach((item, index) => {
        console.log('Image ajoutée :', item.file.name);
        formData.append('gallery[]', item.file);
    });

    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Enregistrement...';
    }


    fetch(form.action, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(async response => {
        if (!response.ok) throw await response.json();
        return response.json();
    })
    .then(data => {
        alert(data.success || 'Enregistrement réussi');
        if (data.redirect) {
            window.location.href = data.redirect;
        }
    })
    .catch(async error => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Enregistrer l\'infrastructure';
        }


        console.error("Erreur JS complète :", error);

        if (error.errors) {
            let messages = Object.values(error.errors).flat().join('\n');
            alert("Erreur(s) :\n" + messages, 'error');
        } else if (typeof error === 'object') {
            alert("Erreur : " + JSON.stringify(error), 'error');
        } else {
            alert("Erreur : " + (error.message || 'Une erreur inconnue est survenue'), 'error');
        }
    });

});
</script>
<script>
function removeExistingImage(id, code) {
    let baseUrl = `{{ route('infrastructure.image.delete', ['id' => '__ID__', 'code' => '__CODE__']) }}`;
    let url = baseUrl.replace('__ID__', id).replace('__CODE__', code);

    confirmDelete(url, () => {
        const div = document.getElementById(`image_${id}`);
        if (div) div.remove();
    }, {
        title: 'Supprimer cette image ?',
        text: 'Cette image sera définitivement supprimée.',
        successMessage: 'Image supprimée avec succès.',
        errorMessage: 'Impossible de supprimer l’image.'
    });
}
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(() => {
            GLightbox({
                selector: '.glightbox',
                touchNavigation: true,
                loop: true,
                zoomable: true,
                openEffect: 'zoom',
                closeEffect: 'fade'
            });
        }, 200); // Attend que le DOM des images soit bien prêt
    });
</script>

@endsection