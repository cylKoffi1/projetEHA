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
    fieldset {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
    .text-center {
        background-color: #5c76cc;
        color: white;
    }
    fieldset .row .col label {
        width: 100%;
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
                            <li class="breadcrumb-item"><a href="">Annexe 2</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Fiche de collecte</li>

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
                        <h5 class="card-title">Annexe 2: Fiche de collecte de données</h5>


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
                                    <th style="width: 20%;">Code </th>
                                    <th style="width: 20%;">Domaine</th>
                                    <th style="width: 25%;">Sous-domaine</th>
                                    <th style="width: 10%;">District</th>
                                    <th style="width: 10%;">Region</th>
                                    <th style="width: 40%; ">Cout projet</th>
                                    <th style="width: 10%;">Devise</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($projets as $projet)
                                <tr>
                                    <td>{{ $projet->CodeProjet }}</td>
                                    <td>{{ $projet->domaine->libelle ?? '' }}</td>
                                    <td>{{ $projet->sous_domaine->libelle ?? '' }}</td>
                                    <td>{{ $projet->district->libelle ?? '' }}</td>
                                    <td>{{ $projet->region->libelle ?? '' }}</td>
                                    <td style="text-align: right;">{{  number_format($projet->cout_projet, 0, ',', ' ') }}</td>
                                    <td>{{$projet->devise->code_long ?? ''}}</td>
                                    <td><a href="{{ route("Annexe2.FicheCollecte", ['code' => $projet->CodeProjet]) }}" class="btn btn-primary">Imprimer</a></td>
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

        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Annexe 1: infomations secondaires');
    });
</script>
@endsection
