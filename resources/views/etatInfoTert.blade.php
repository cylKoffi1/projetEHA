@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
    alert("{{ session('success') }}");

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
                            <li class="breadcrumb-item"><a href="">Editions</a></li>
                            <li class="breadcrumb-item"><a href="">Annexe 1</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Informations supplémentaires</li>

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
                    <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                        <h5 class="card-title">
                            Annexe 1: Informations supplémentaires

                        </h5>

                    </div>
                    <div style="text-align: center;">
                        <h5 class="card-title"></h5>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body">


                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                            <thead>
                                <tr>
                                    <th>Code </th>
                                    <th>Date debut prévisionnelle</th>
                                    <th>Date fin prévisionnelle</th>
                                    <th>Date debut effective</th>
                                    <th>Date fin effective</th>
                                    <th>Infrastructures : quantité</th>
                                    <th>Bénéficiaires</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>

                            <tbody>
                            @foreach($projets as $projet)
                            <tr>
                                <td>{{ $projet->CodeProjet }}</td>
                                <td>{{ $projet->Date_demarrage_prevue }}</td>
                                <td>{{ $projet->date_fin_prevue }}</td>
                                <td>{{ $projet->Date_debut_effective }}</td>
                                <td>{{ $projet->Date_fin_effective }}</td>
                                <td>{{ $projet->Infrastructures_Quantites }}</td>
                                <td>{{ $projet->Beneficiaires }}</td>
                                <td>{{ $projet->Statuts }}</td>
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

        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Annexe 1: infomations supplémentaires');
    });
</script>

@endsection
