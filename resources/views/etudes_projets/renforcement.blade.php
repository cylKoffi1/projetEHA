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
                    <form action="{{ route('renforcements.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-3">
                                <label for="titre">Titre :</label>
                                <input type="text" name="titre" required class="form-control">
                            </div>
                            <div class="col-2">
                                <label for="date_renforcement">Date :</label>
                                <input type="date" name="date_renforcement" required class="form-control">
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-5">
                                <label for="description">Description :</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="beneficiaires">Sélectionnez les bénéficiaires :</label>
                                <select name="beneficiaires[]" multiple class="form-select custom-select">
                                    @foreach($beneficiaires as $beneficiaire)
                                        @if ($beneficiaire->personnel)
                                        <option value="{{ $beneficiaire->code_personnel }}">
                                            {{ $beneficiaire->personnel->nom }} {{ $beneficiaire->personnel->prenom }}
                                        </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col">
                                <label for="projets">Sélectionnez les projets (facultatif) :</label>
                                <select name="projets[]" multiple class="form-select custom-select">
                                    @foreach($projets as $projet)
                                        <option value="{{ $projet->CodeProjet }}">{{ $projet->CodeProjet }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>







                        <button type="submit">Enregistrer</button>
                    </form>

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

                    <table>
                        <thead>
                            <tr>
                                <th>Code Renforcement</th>
                                <th>Titre</th>
                                <th>Date</th>
                                <th>Bénéficiaires</th>
                                <th>Projets</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($renforcements as $renforcement)
                            <tr>
                                <td>{{ $renforcement->code_renforcement }}</td>
                                <td>{{ $renforcement->titre }}</td>
                                <td>{{ $renforcement->date_renforcement }}</td>
                                <td>
                                    @foreach($renforcement->beneficiaires as $beneficiaire)
                                        {{ $beneficiaire->nom }},
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($renforcement->projets as $projet)
                                        {{ $projet->nom }},
                                    @endforeach
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

</script>
@endsection
