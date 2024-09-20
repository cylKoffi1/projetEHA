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
                            <li class="breadcrumb-item active" aria-current="page">Activité connexe</li>
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
    <div class="row ">
        <div class="col" style="justify-content: center;">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Activité connexe</h5>

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
                    <div class="col">
                        <form action="{{ route('travaux_connexes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>

                            <div class="row">
                                <div class="col-4">
                                    <label for="code_projet">Code Projet :</label>
                                    <select name="code_projet" id="code_projet" class="form-select" required>
                                        @foreach($projets as $projet)
                                            <option value="{{ $projet->CodeProjet }}">{{ $projet->CodeProjet }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="row ">

                                <div class="col">
                                    <label for="type_travaux_id">Type Travaux :</label>
                                    <select name="type_travaux_id" id="type_travaux_id" class="form-select" required>
                                        @foreach($typesTravaux as $type)
                                            <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="cout_projet">Coût du Projet (XOF) :</label>
                                    <input type="text" name="cout_projet" id="cout_projet" class="form-control text-end" required oninput="formatNumber(this)">
                                </div>

                                <div class="col">
                                    <label for="date_debut_previsionnelle">Date Début Prévisionnelle :</label>
                                    <input type="date" name="date_debut_previsionnelle" id="date_debut_previsionnelle" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="date_fin_previsionnelle">Date Fin Prévisionnelle :</label>
                                    <input type="date" name="date_fin_previsionnelle" id="date_fin_previsionnelle" class="form-control" required>
                                </div>

                                <div class="col">
                                    <label for="date_debut_effective">Date Début Effective :</label>
                                    <input type="date" name="date_debut_effective" id="date_debut_effective" class="form-control">
                                </div>

                                <div class="col">
                                    <label for="date_fin_effective">Date Fin Effective :</label>
                                    <input type="date" name="date_fin_effective" id="date_fin_effective" class="form-control">
                                </div>

                                <div class="col-12 mt-3">
                                    <label for="commentaire">Commentaire :</label>
                                    <textarea name="commentaire" id="commentaire" class="form-control"></textarea>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </div>
                        </form>

                        <!-- Formulaire de modification caché par défaut -->
                        <div id="editFormContainer" style="display: none;">

                        </div>


                    </div>
                </div>
            </div>

        </div>
    </div>


    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
            Liste des activités connexe
            </h5>
        </div>
        <div class="card-content">
            <div class="col-12">
                <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table">
                    <thead>
                        <tr>
                            <th>Code Projet</th>
                            <th>Type Travaux</th>
                            <th>Coût (XOF)</th>
                            <th>Date Début Prévisionnelle</th>
                            <th>Date Fin Prévisionnelle</th>
                            <th>Date Début Effective</th>
                            <th>Date Fin Effective</th>
                            <th>Commentaire</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($travaux as $travail)
                        <tr>
                            <td>{{ $travail->projet->code_projet }}</td>
                            <td>{{ $travail->typeTravaux->libelle }}</td>
                            <td style="text-align: right">{{ number_format($travail->cout_projet, 2) }}</td>
                            <td>{{ $travail->date_debut_previsionnelle }}</td>
                            <td>{{ $travail->date_fin_previsionnelle }}</td>
                            <td>{{ $travail->date_debut_effective }}</td>
                            <td>{{ $travail->date_fin_effective }}</td>
                            <td>{{ $travail->commentaire }}</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-warning">Modifier</a>
                                <form action="{{ route('travaux_connexes.destroy', $travail->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
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

        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table', 'Listes des activités connexes');
    });


    function formatNumber(input) {
        // Enlève tout caractère non numérique
        let value = input.value.replace(/\D/g, '');

        // Formate le nombre avec des espaces
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

        input.value = value;
    }
    window.addEventListener('DOMContentLoaded', function() {

        var startDateInput = document.getElementById('date_debut_previsionnelle');
        var endDateInput = document.getElementById('date_fin_previsionnelle');
        var startDateInputs = document.getElementById('date_debut_effective');
        var endDateInputs = document.getElementById('date_fin_effective');

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


</script>
@endsection
