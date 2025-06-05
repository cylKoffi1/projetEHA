@extends('layouts.app')

@section('content')
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
                            <li class="breadcrumb-item"><a href="">Gestion des exceptions</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Annuler projet</li>

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
    

<div class="container">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
        <div class="row match-height">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Annulation d’un projet</h3>
                    </div>
                    <div class="card-body">

                        <form method="POST" action="{{ route('projets.annulation.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-5">
                                    <label for="code_projet">Projet à annuler *</label>
                                    <select name="code_projet" class="form-control" required>
                                        <option value="">-- Sélectionnez un projet --</option>
                                        @foreach($projets as $projet)
                                            <option value="{{ $projet->code_projet }}">{{ $projet->code_projet }} </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-7">
                                    <label for="motif">Motif de l’annulation *</label>
                                    <textarea name="motif" class="form-control" rows="2" required placeholder="Expliquez la raison de l’annulation..."></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-danger mt-3">Annuler le projet</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
        <div class="row match-height mt-4">
            <div class="col-12">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h4>Redémarrer un projet annulé</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('projets.redemarrer') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-5">
                                    <label for="code_projet">Projet à redémarrer *</label>
                                    <select name="code_projet" class="form-control" required>
                                        <option value="">-- Sélectionnez un projet annulé --</option>
                                        @foreach($projetsAnnules as $projet)
                                            <option value="{{ $projet->code_projet }}">{{ $projet->code_projet }} - {{ $projet->libelle_projet }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-7">
                                    <label for="motif_redemarrage">Motif du redémarrage *</label>
                                    <textarea name="motif_redemarrage" class="form-control" rows="2" required placeholder="Expliquer pourquoi ce projet est redémarré..."></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success mt-3">Redémarrer le projet</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    <hr>

    <h5>📋 Projets annulés</h5>
    <table  class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="tableAnnules">
        <thead>
            <tr>
                <th>Code</th>
                <th>Libellé</th>
                <th>Date annulation</th>
                <th>Statut</th>

            </tr>
        </thead>
        <tbody>
            @foreach($projetsAnnules as $projet)
                <tr>
                    <td>{{ $projet->code_projet }}</td>
                    <td>{{ $projet->libelle_projet }}</td>
                    <td>{{ $projet->statuts->date_statut ?? '-' }}</td>
                    <td>{{ $projet->statuts->statut->libelle ?? '-' }}</td>

                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableAnnules', "Liste des projets annulés")
    });
    $(document).ready(function() {
        $('#tableAnnules').DataTable();
    });
</script>
@endsection
