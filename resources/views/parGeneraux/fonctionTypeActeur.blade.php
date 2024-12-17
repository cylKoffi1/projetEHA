@extends('layouts.app')

@section('content')

@if (session('success'))
<script>alert("{{ session('success') }}");</script>
@endif

<div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Plateforme </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Paramètre spécifique</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Fonction type action</li>

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

<section class="section">
    <div class="card">
        <div class="card-header">
            <h5>Fonction Type d'Acteur</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('fonction-type-acteur.store') }}" method="POST">
                @csrf
                <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="fonction_code">Fonction</label>
                        <select name="fonction_code" id="fonction_code" class="form-control" required>
                            <option value="">-- Sélectionner une Fonction --</option>
                            @foreach ($fonctions as $fonction)
                                <option value="{{ $fonction->code }}">{{ $fonction->libelle_fonction }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="type_acteur_code">Type d'Acteur</label>
                        <select name="type_acteur_code" id="type_acteur_code" class="form-control" required>
                            <option value="">-- Sélectionner un Type d'Acteur --</option>
                            @foreach ($typesActeurs as $typeActeur)
                                <option value="{{ $typeActeur->cd_type_acteur }}">{{ $typeActeur->libelle_type_acteur }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col text-end">
                <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>
                </div>

            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Liste des Associations</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                <thead>
                    <tr>
                        <th>Fonction</th>
                        <th>Type d'Acteur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fonctionTypeActeurs as $assoc)
                    <tr>
                        <td>{{ $assoc->fonction ? $assoc->fonction->libelle_fonction : 'aucune fonction' }}</td>
                        <td>{{ $assoc->typeActeur ? $assoc->typeActeur->libelle_type_acteur : 'aucun type acteur' }}</td>
                        <td>
                            <form action="{{ route('fonction-type-acteur.destroy', $assoc->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Fonction à un type acteur')
    });
</script>
@endsection
