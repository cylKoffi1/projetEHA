<!-- resources/views/users/create.blade.php -->

@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
    $('#alertMessage').text("{{ session('success') }}");
    $('#alertModal').modal('show');
</script>
@endif

<style>
    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 6px;
        font-size: 80%;
        color: #dc3545;
        /* Couleur du texte pour les messages d'erreur */
    }

</style>
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
                            <li class="breadcrumb-item"><a href="">Projet</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Consultation de projet</li>

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
                    <div  style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                        <h5 class="card-title">
                            Ajout d'un chef de projet
                            @can("ajouter_ecran_" . $ecran->id)
                            <a href="{{ route('projet') }}"  style="margin-left: 15px;"><i class="bi bi-plus-circle me-1"></i></a>
                            @endcan
                        </h5>

                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div style="text-align: center;">
                       <h5 class="card-title"> Liste des projets</h5>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body">
                    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                        <thead>
                            <tr>
                                <th style="width: 5%">Code</th>
                                <th style="width: 7%">Statut</th>
                                <th style="width: 10%">Domaine</th>
                                <th style="width: 10%">Sous-domaine</th>
                                <th style="width: 10%">Date début prévue</th>
                                <th style="width: 10%">Date fin prévue</th>
                                <th style="width: 20%">Coût</th>
                                <th style="width: 5%">Dévise</th>
                                <th style="width: 5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($projets as $projet)
                            @php
                                // Filtrer les statuts pour le projet actuel
                                $statutsProjet = $Statuts->where('CodeProjet', $projet->CodeProjet);
                            @endphp
                            <tr>
                                <td>{{ $projet->CodeProjet }}</td>
                                <td>
                                    @foreach ($statutsProjet as $statut)
                                        {{ $statut->statut_libelle }} <br>
                                    @endforeach
                                </td>
                                <td>{{ $projet->sousDomaine->Domaine->libelle }}</td>
                                <td>{{ $projet->sousDomaine->lib_sous_domaine }}</td>
                                <td>{{ date('d-m-Y', strtotime($projet->Date_demarrage_prevue)) }}</td>
                                <td>{{ date('d-m-Y', strtotime($projet->date_fin_prevue)) }}</td>
                                <td style="width: 12%; text-align: right;">{{ number_format($projet->cout_projet, 0, ',', ' ') }}</td>
                                <td>{{ $projet->devise->code_long ?? '' }}</td>
                                <td>
                                @can("consulter_ecran_" . $ecran->id)
                                <a href="{{ route('projets.show', $projet->CodeProjet) }}" class="btn btn-outline-primary btn-sm">
    <i class="bi bi-eye me-1"></i> Détails
</a>
                                @endcan
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
        initDataTable('{{ auth()->user()->acteur->libelle_court }} {{ auth()->user()->acteur->libelle_long }}', 'table1', 'Liste des projets')
    });

</script>
@endsection
