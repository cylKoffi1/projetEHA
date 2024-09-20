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
    .select2-selection__choice {
        font-size: 12px;
    }
    .select2-selection__rendered{
        width: 300.663px !important;
    }
     .select2-container--default{
        width: 300.663px !important;
    }
</style>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

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
                            <li class="breadcrumb-item active" aria-current="page">Renforcement des capacités</li>
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
    <div class="row justify-content-center align-items-center my-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title text-center">Renforcement des capacités</h5>

                    @if (session('success'))
                        <div class="alert alert-success text-center">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger text-center">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    <form id="addForm" action="{{ route('renforcements.store') }}" method="POST">
                        @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}" name="ecran_id" required>

                        <!-- Row for Title and Dates -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="titre" class="form-label">Titre :</label>
                                <input type="text" name="titre" required class="form-control" value="{{ old('titre') }}">
                                @error('titre')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="date_renforcement" class="form-label">Date début :</label>
                                <input type="date" name="date_renforcement" id="date_renforcements" required class="form-control" value="{{ old('date_renforcement') }}">
                                @error('date_renforcement')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="date_fin" class="form-label">Date fin :</label>
                                <input type="date" name="date_fin" id="date_fins" required class="form-control" value="{{ old('date_fin') }}">
                                @error('date_fin')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Description Field -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="description" class="form-label">Description :</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Beneficiaires Selection -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <label for="beneficiaires" class="form-label">Sélectionnez les bénéficiaires :</label>
                                <select name="beneficiaires[]" multiple class="form-select" style="width: 100%">
                                    @foreach($beneficiaires as $beneficiaire)
                                        @if ($beneficiaire->personnel)
                                            <option value="{{ $beneficiaire->code_personnel }}">
                                                {{ $beneficiaire->personnel->nom }} {{ $beneficiaire->personnel->prenom }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="projets" class="form-label">Sélectionnez les projets :</label>
                                <select name="projets[]" multiple class="form-select" style="width: 100%">
                                    @foreach($projets as $projet)
                                        <option value="{{ $projet->CodeProjet }}">{{ $projet->CodeProjet }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="row text-center">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                            </div>
                        </div>
                    </form>

                    <!-- Hidden Edit Form -->
                    <div id="editFormContainer" style="display: none;">
                        <form id="formAction" action="" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}" name="ecran_id" required>

                            <input type="hidden" id="code" name="code">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="titre" class="form-label">Titre :</label>
                                    <input type="text" id="titre" name="titre" required class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_renforcement" class="form-label">Date début :</label>
                                    <input type="date" id="date_renforcement" name="date_renforcement" required class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_fin" class="form-label">Date fin :</label>
                                    <input type="date" id="date_fin" name="date_fin" required class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="description" class="form-label">Description :</label>
                                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label for="beneficiaires" class="form-label">Sélectionnez les bénéficiaires :</label>
                                    <select id="beneficiaires" name="beneficiaires[]" multiple class="form-select">
                                        @foreach($beneficiaires as $beneficiaire)
                                            @if ($beneficiaire->personnel)
                                                <option value="{{ $beneficiaire->code_personnel }}">
                                                    {{ $beneficiaire->personnel->nom }} {{ $beneficiaire->personnel->prenom }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="projets" class="form-label">Sélectionnez les projets :</label>
                                    <select id="projets" name="projets[]" multiple class="form-select">
                                        @foreach($projets as $projet)
                                            <option value="{{ $projet->CodeProjet }}">{{ $projet->CodeProjet }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row text-center">
                                <div class="col-12">
                                    <button type="submit" id="formButton" class="btn btn-primary">Modifier</button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>



    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
            Liste des Renforcements de Capacité
            </h5>
        </div>
        <div class="card-content">
            <div class="col-12">
                <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table">
                    <thead>
                        <tr>
                            <th>Code Renforcement</th>
                            <th>Code Projets</th>
                            <th>Titre</th>
                            <th>Date debut </th>
                            <th>Date fin </th>
                            <th>Bénéficiaires</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($renforcements as $renforcement)
                        <tr>
                            <td>{{ $renforcement->code_renforcement }}</td>

                            <!-- Affichage des codes de projets -->
                            <td>
                                @if($renforcement->projets->isNotEmpty())
                                    <ul>
                                        @foreach($renforcement->projets as $projet)
                                            <li>{{ $projet->CodeProjet }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <em>Aucun projet</em>
                                @endif
                            </td>

                            <td>{{ $renforcement->titre }}</td>
                            <td>{{ $renforcement->date_debut }}</td>
                            <td>{{ $renforcement->date_fin }}</td>

                            <td>
                                @if($renforcement->beneficiaires->isNotEmpty())
                                    <ul>
                                        @foreach($renforcement->beneficiaires as $beneficiaire)
                                            @if ($beneficiaire && $beneficiaire->personnel) <!-- Vérification de l'existence du bénéficiaire et de personnel -->
                                                <li>{{ $beneficiaire->personnel->nom }} {{ $beneficiaire->personnel->prenom }}</li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @else
                                    <em>Aucun bénéficiaire</em>
                                @endif
                            </td>

                            <td>
                                <div class="dropdown">
                                    <a href="#" class="btn btn-link dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown">
                                        <span style="color: white"></span>
                                    </a>
                                    <ul class="dropdown-menu z-3" aria-labelledby="userDropdown">
                                        <li>
                                            <a class="dropdown-item" href="#"
                                               onclick="editRenforcement('{{ $renforcement->code_renforcement }}',
                                               '{{ $renforcement->titre }}',
                                               '{{ $renforcement->description }}',
                                               '{{ $renforcement->date_debut }}',
                                               '{{ $renforcement->date_fin }}',
                                               {{ json_encode($renforcement->beneficiaires->pluck('code_personnel')->toArray()) }},
                                               {{ json_encode($renforcement->projets->pluck('CodeProjet')->toArray()) }})">
                                               <i class="bi bi-pencil-fill me-3"></i> Modifier
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" onclick="deleteRenforcement('{{ $renforcement->code_renforcement }}')">
                                                <i class="bi bi-trash3-fill me-3"></i> Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>


</section>
<script>
    $(document).ready(function() {
        $('.form-select').select2();
    });
    $(document).ready(function() {

        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table', 'Listes des renforcements de capacités');
    });
    window.addEventListener('DOMContentLoaded', function() {

        var startDateInput = document.getElementById('date_renforcement');
        var endDateInput = document.getElementById('date_fin');
        var startDateInputs = document.getElementById('date_renforcements');
        var endDateInputs = document.getElementById('date_fins');

        endDateInput.addEventListener('change', function() {
            var startDate = new Date(startDateInput.value);
            var endDate = new Date(endDateInput.value);

            if (endDate < startDate) {
                $('#alertMessage').text('La date de fin ne peut pas être antérieure à la date de début.');
                $('#alertModal').modal('show');
                endDateInput.value = startDateInput.value; // Réinitialiser la date de fin à la date de début
            }
        });

        endDateInputs.addEventListener('change', function() {
            var startDates = new Date(startDateInputs.value);
            var endDates = new Date(endDateInputs.value);

            if (endDates < startDates) {
                $('#alertMessage').text('La date de fin ne peut pas être antérieure à la date de début.');
                $('#alertModal').modal('show');
                endDateInputs.value = startDateInputs.value; // Réinitialiser la date de fin à la date de début
            }
        });
    });
    function deleteRenforcement(id) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ?")) {
            $.ajax({
                url: '/renforcementDelete/' + id,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(result) {
                    $('#alertMessage').text("Renforcement de capacité supprimé avec succès.");
                    $('#alertModal').modal('show');
                    window.location.reload(true);
                },
                error: function(xhr, status, error) {
                    $('#alertMessage').text('Erreur lors de la suppression : ' + error);
                    $('#alertModal').modal('show');
                }
            });
        }
    }

    function editRenforcement(code, titre, description, date,date_fin, beneficiaires, projets) {
        // Cacher le formulaire d'enregistrement
        document.getElementById('addForm').style.display = 'none';

        // Renseigner les champs du formulaire avec les données
        document.getElementById('code').value = code;
        document.getElementById('titre').value = titre;
        document.getElementById('description').value = description;
        document.getElementById('date_renforcement').value = date;
        document.getElementById('date_fin').value = date_fin;

        // Sélectionner les bénéficiaires
    let beneficiairesSelect = document.getElementById('beneficiaires');
    for (let option of beneficiairesSelect.options) {
        option.selected = beneficiaires.includes(option.value);
    }

    // Sélectionner les projets
    let projetsSelect = document.getElementById('projets');
    for (let option of projetsSelect.options) {
        option.selected = projets.includes(option.value);
    }

    // Rafraîchir les sélections avec select2
    $('#beneficiaires').trigger('change');
    $('#projets').trigger('change');

        // Modifier l'action du formulaire pour inclure l'ID (code_renforcement)
        document.getElementById('formAction').action = '/renforcements/' + code;

        // Afficher le formulaire de modification
        document.getElementById('editFormContainer').style.display = 'block';
    }


</script>
@endsection
