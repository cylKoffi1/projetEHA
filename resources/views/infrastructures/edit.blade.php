@extends('layouts.app')

@section('content')
<section id="multiple-column-form">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right;">
                        <span id="date-now" style="color: #34495E;"></span>
                    </li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion des infrastructures</h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Infrastructures</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Modification infrastructure</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row match-height">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Modification Infrastructure</h4>
                </div>
                <div class="card-body">
                    <form id="infrastructure-form" method="POST" action="{{ route('infrastructures.update', $infrastructure->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Domaine -->
                            <div class="col-md-3">
                                <div class="form-group mandatory">
                                    <label for="domaine">Domaine</label>
                                    <select class="form-control" name="domaine" id="domaineSelect" required>
                                        <option value="">Sélectionner</option>
                                        @foreach ($domaines as $domaine)
                                            <option value="{{ $domaine->code }}" {{ $selectedDomaineCode == $domaine->code ? 'selected' : '' }}>
                                                {{ $domaine->libelle }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Famille -->
                            <div class="col-md-3">
                                <div class="form-group mandatory">
                                    <label for="code_famille_infrastructure">Famille</label>
                                    <select class="form-control" name="code_famille_infrastructure" id="code_famille_infrastructure" required>
                                        <option value="">Sélectionner une famille</option>
                                        @foreach ($familles as $famille)
                                            <option value="{{ $famille->code_famille }}" {{ $infrastructure->code_famille_infrastructure == $famille->code_famille ? 'selected' : '' }}>
                                                {{ $famille->libelleFamille }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Libelle -->
                            <div class="col-md-3">
                                <div class="form-group mandatory">
                                    <label for="libelle">Nom infrastructure</label>
                                    <input type="text" class="form-control" name="libelle" id="libelle" value="{{ $infrastructure->libelle }}" required>
                                </div>
                            </div>

                            <!-- Date opération -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date_operation">Date création</label>
                                    <input type="date" class="form-control" name="date_operation" id="date_operation"
                                        value="{{ $infrastructure->date_operation ? \Carbon\Carbon::parse($infrastructure->date_operation)->format('Y-m-d') : '' }}">
                                </div>
                            </div>
                        </div>

                        <!-- Localité et niveau -->
                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-group mandatory">
                                    <label>Localité </label>
                                    <lookup-select id="niveau1Select" name="code_localite" value="{{ $infrastructure->code_localite }}">
                                        @foreach ($localites as $localite)
                                            <option value="{{ $localite->id }}" data-code="{{ $localite->code_rattachement }}">
                                                {{ $localite->libelle }}
                                            </option>
                                        @endforeach
                                    </lookup-select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Niveau</label>
                                    <select class="form-control" id="niveau2Select" disabled>
                                        @if($infrastructure->localisation)
                                            <option value="{{ $infrastructure->localisation->niveau }}" selected>{{ $infrastructure->localisation->niveau }}</option>
                                        @else
                                            <option value="">Sélectionnez un niveau</option>
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Découpage</label>
                                    <select class="form-control" id="niveau3Select" disabled>
                                        @if($infrastructure->localisation)
                                            <option value="{{ $infrastructure->localisation->code_decoupage }}" selected>{{ $infrastructure->localisation->libelle_decoupage }}</option>
                                        @else
                                            <option value="">Sélectionnez un découpage</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="text" class="form-control" name="latitude" value="{{ old('latitude', $infrastructure->latitude ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="text" class="form-control" name="longitude" value="{{ old('longitude', $infrastructure->longitude ?? '') }}">
                            </div>

                        </div>
                        <!-- Photo -->
                        <div class="form-group mt-3">
                            <label for="photo">Photo de l'infrastructure</label>
                            <div class="image-upload-container">
                                <div class="image-upload-area" onclick="document.getElementById('photo').click()">
                                    <div id="upload-text" class="{{ $infrastructure->imageInfras ? 'd-none' : '' }}">
                                        <i class="bi bi-cloud-upload" style="font-size: 2rem;"></i>
                                        <p>Cliquez pour sélectionner une image</p>
                                    </div>
                                    @if($infrastructure->imageInfras)
                                        <img id="image-preview" src="{{ asset($infrastructure->imageInfras) }}" style="max-width: 100%; max-height: 200px;">
                                    @else
                                        <img id="image-preview" class="d-none" style="max-width: 100%; max-height: 200px;">
                                    @endif
                                </div>
                                <input type="file" class="d-none" id="photo" name="photo" accept="image/*" onchange="previewImage(event)">
                            </div>
                            <small class="text-muted">Formats acceptés: JPG, PNG (Max 2MB)</small>

                            @if($infrastructure->imageInfras)
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="remove_photo" name="remove_photo">
                                    <label class="form-check-label" for="remove_photo">Supprimer la photo actuelle</label>
                                </div>
                            @endif
                        </div>

                        <!-- Hidden fields -->
                        <input type="hidden" id="niveau" name="niveau" value="{{ $infrastructure->localisation->niveau ?? '' }}">
                        <input type="hidden" id="code_decoupage" name="code_decoupage" value="{{ $infrastructure->localisation->code_decoupage ?? '' }}">

                        <!-- Buttons -->
                        <div class="row mt-4">
                            <div class="col-6">
                                <a href="{{ route('infrastructures.index') }}" class="btn btn-light-secondary">
                                    <i class="bi bi-x-circle"></i> Annuler
                                </a>
                            </div>
                            <div class="col-6 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Enregistrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Styles --}}
<style>
.image-upload-area {
    border: 2px dashed #ccc;
    border-radius: 6px;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    background-color: #f8f9fa;
    min-height: 150px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}
.image-upload-area:hover {
    border-color: #666;
}
#upload-text {
    color: #666;
}
</style>

{{-- Script --}}
<script>
// Soumission du formulaire en AJAX avec alertes simples
$('#infrastructure-form').on('submit', function (e) {
    e.preventDefault();
    const form = this;
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(async response => {
        if (!response.ok) throw await response.json();
        return response.json();
    })
    .then(data => {
        // ✅ Succès
        alert(data.success);
        if (data.redirect) {
            window.location.href = data.redirect;
        }
    })
    .catch(error => {
        // ⚠️ Gestion des erreurs
        let message = 'Une erreur inattendue est survenue.';
        if (error.errors) {
            message = Object.values(error.errors).flat().join('\n');
        } else if (error.error) {
            message = error.error;
        }
        alert(message, 'error');
    });
});

function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('image-preview');
    const uploadText = document.getElementById('upload-text');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
            uploadText.classList.add('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

$(document).ready(function () {
    // Domaine -> familles
    $('#domaineSelect').on('change', function () {
        const codeDomaine = $(this).val();
        $('#code_famille_infrastructure').html('<option value="">Chargement...</option>');

        if (codeDomaine) {
            fetch(`{{ url("/") }}/familles/${codeDomaine}`)
                .then(res => res.json())
                .then(data => {
                    $('#code_famille_infrastructure').empty().append('<option value="">Sélectionner une famille</option>');
                    data.forEach(famille => {
                        $('#code_famille_infrastructure').append(
                            `<option value="${famille.code_famille}" 
                             ${famille.code_famille === '{{ $infrastructure->code_famille_infrastructure }}' ? 'selected' : ''}>
                             ${famille.libelleFamille}</option>`
                        );
                    });
                });
        }
    });

    // Localité -> niveau/découpage
    document.getElementById("niveau1Select").addEventListener("change", function () {
        const selected = this.getSelected?.();
        const localiteId = selected?.value || this.value;

        if (localiteId) {
            $.ajax({
                url: '{{ url("/") }}/get-decoupage-niveau/' + localiteId,
                type: "GET",
                success: function (data) {
                    $("#niveau2Select").html(`<option value="${data.niveau}" selected>${data.niveau}</option>`).prop("disabled", false);
                    $("#niveau3Select").html(`<option value="${data.code_decoupage}" selected>${data.libelle_decoupage}</option>`).prop("disabled", false);
                    $("#niveau").val(data.niveau);
                    $("#code_decoupage").val(data.code_decoupage);
                }
            });
        }
    });

    // Précharger la localité (avec lookup-select)
    const paysCode = $("#paysSelect").val();
    if (paysCode) {
        fetch(`{{ url('/') }}/get-localites/${paysCode}`)
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById("niveau1Select");
                if (select?.setOptions) {
                    const options = data.map(loc => ({ value: loc.id, text: loc.libelle }));
                    select.setOptions(options);

                    const selectedLocalite = '{{ $infrastructure->code_localite }}';
                    if (selectedLocalite) {
                        setTimeout(() => {
                            select.setSelectedValue(selectedLocalite);
                            select.dispatchChangeEvent();
                        }, 200);
                    }
                }
            });
    }

    // Précharger familles si domaine déjà sélectionné
    const domaineSelected = $('#domaineSelect').val();
    if (domaineSelected) {
        $('#domaineSelect').trigger('change');
    }

    // Date en temps réel
    setInterval(() => {
        document.getElementById('date-now').textContent = new Date().toLocaleString();
    }, 1000);
});
</script>
@endsection
