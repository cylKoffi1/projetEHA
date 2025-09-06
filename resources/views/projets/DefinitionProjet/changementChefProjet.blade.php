@extends('layouts.app')


@section('content')

<div>
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
                            <li class="breadcrumb-item"><a href="">Définition de projet</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Changement de chef de projet</li>

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
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
@endif

    <div class="container">
        <div class="row match-height">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Changement de Chef de projet</h4>
                        <form action="{{ route('contrats.chef.update') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col">
                                    <label for="contrat_id_chef">Contrat concerné</label>
                                    <select name="contrat_id" id="contrat_id_chef" class="form-control" required>
                                        <option value="">-- Sélectionnez --</option>
                                        @foreach($contrats as $contrat)
                                        @if ($contrat->is_active)
                                            
                                        <option value="{{ $contrat->id }}">
                                                {{ $contrat->code_projet }} - {{ $contrat->acteur->libelle_court ?? '' }} {{ $contrat->acteur->libelle_long ?? '' }}
                                            </option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <label for="nouveau_chef">Nouveau chef de projet</label>
                                    <select name="nouveau_chef_id" id="nouveau_chef" class="form-control" required>
                                        <option value="">-- Sélectionnez --</option>
                                        @foreach($chefs as $chef)
                                            <option value="{{ $chef->code_acteur }}">{{ $chef->libelle_court }} {{ $chef->libelle_long }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label for="motif">Motif du changement</label>
                                <textarea name="motif" id="motif" class="form-control" rows="3" placeholder="Expliquer pourquoi ce changement est effectué..." required></textarea>
                            </div>

                            <button type="submit" class="btn btn-warning mt-3">Valider le changement</button>
                        </form>
                    </div>
                </div>                
            </div>
        </div>

                <div class="card-body">
                    <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                        <thead>
                            <tr>
                    
                                <th>Code projet</th>
                                <th>Chef projet</th>
                                <th>Date debut</th>
                                <th>Date de fin</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contrats as $contrat)
                                <tr>
                                    <td>{{ $contrat->code_projet }}</td>
                                    <td>{{ $contrat->acteur->libelle_court ?? '' }} {{ $contrat->acteur->libelle_long ?? '' }}</td>
                                    <td>{{ $contrat->date_debut }}</td>
                                    <td>{{ $contrat->date_fin }}</td>
                                    <td>
                                        @if($contrat->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-secondary">Inactif</span>
                                        @endif
                                    </td>


                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
    </div>


<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des chefs de projet')
    });

</script>


@endsection
