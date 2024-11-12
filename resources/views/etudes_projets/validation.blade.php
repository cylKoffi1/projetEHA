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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Etudes projets </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Approbation</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Approuver</li>
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
                <h5 class="card-title">Approuver projets</h5>

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

                    <div class="container">
                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table">
                            <thead>
                                <tr>
                                    <th>Code Projet</th>
                                    <th>Nature des Travaux</th>
                                    <th>Statut</th>
                                    <th>Approver Actuel</th>
                                    <th>Date de Création</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($projects as $project)
                                    <tr>
                                        <td>{{ $project->codeEtudeProjets }}</td>
                                        <td>{{ $project->natureTravaux }}</td>
                                        <td>{{ $project->codeStatus }}</td>
                                        <td>{{ $project->current_approver }}</td>
                                        <td>{{ $project->created_at }}</td>
                                        <td>
                                            @can("ajouter_ecran_" . $ecran->id)
                                            <a href="{{ route('planning.show', $project->codeEtudeProjets) }}" class="btn btn-info">Détails</a>
                                            @endcan
                                            <form action="{{ route('projects.approve', $project->codeEtudeProjets) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @can("ajouter_ecran_" . $ecran->id)
                                                <button type="submit" class="btn btn-success">Valider</button>
                                                @endcan
                                            </form>
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
            initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table', 'Listes des renforcements de capacités');
        });
</script>
@endsection
