@extends('layouts.app')

@section('content')
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Gestion des infrastructures </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Caractéristiques</a></li>

                            <li class="breadcrumb-item active" aria-current="page">Fiche technique</li>

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
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Fiche technique</h4>
                    <a href="{{ route('infrastructures.print', $infrastructure->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-printer"></i> Imprimer
                    </a>

                    <a href="{{ route('infrastructures.edit', $infrastructure->id) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-pencil-square"></i> Modifier
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item"><strong>Code:</strong> {{ $infrastructure->code }}</div>
                            <div class="info-item"><strong>Nom:</strong> {{ $infrastructure->libelle }}</div>
                            <div class="info-item"><strong>Famille:</strong> {{ $infrastructure->familleInfrastructure->libelleFamille ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item"><strong>Localisation:</strong> {{ $infrastructure->localisation->libelle ?? '-' }}</div>
                            <div class="info-item"><strong>Date création:</strong> {{ $infrastructure->date_operation ? \Carbon\Carbon::parse($infrastructure->date_operation)->format('d/m/Y') : '-' }}</div>
                            <div class="info-item"><strong>Latitude:</strong> {{ $infrastructure->latitude ?? '-' }}</div>
                            <div class="info-item"><strong>Longitude:</strong> {{ $infrastructure->longitude ?? '-' }}</div>
                            <div class="info-item"><strong>Projet:</strong> {{ $infrastructure->code_groupe_projet ?? '-' }}</div>

                        </div>
                    </div>
                    @if ($infrastructure->imageInfras)
                    <div class="text-center mt-3">
                        <img src="{{ asset($infrastructure->imageInfras) }}" class="img-fluid rounded" style="max-height: 250px;" alt="Photo de l'infrastructure">
                    </div>
                    @endif
                </div>
            </div>

            <!-- Caractéristiques -->
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="mb-0">Caractéristiques techniques</h4>
                </div>
                <div class="card-body">
                    @if($infrastructure->valeursCaracteristiques->isEmpty())
                        <div class="alert alert-light">Aucune caractéristique n'a été ajoutée.</div>
                    @else
                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="table1">
                            <thead>
                                <tr>
                                    {{--<th>Type</th>--}}
                                    <th>Caractéristique</th>
                                    <th>Valeur</th>
                                    <th>Unité</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($infrastructure->valeursCaracteristiques as $caract)
                                <tr>
                                    {{--<td>{{ $caract->caracteristique->type->libelleTypeCaracteristique ?? '-' }}</td>--}}
                                    <td>{{ $caract->caracteristique->libelleCaracteristique ?? '-' }}</td>
                                    <td>{{ $caract->valeur }}</td>
                                    <td>@if($caract->unite)
                                            {{ $caract->unite->libelleUnite }} ({{ $caract->unite->symbole }})
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('infrastructures.caracteristiques.destroy', $caract->idValeurCaracteristique) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Confirmer la suppression ?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="col-lg-4">
            <!-- Ajout caractéristique -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Ajouter une caractéristique</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('infrastructures.caracteristiques.store', $infrastructure->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label>Type</label>
                            <select class="form-select" name="idTypeCaracteristique" id="idTypeCaracteristique" required>
                                <option value="" disabled selected>Choisir...</option>
                                @foreach($typeCaracteristiques as $type)
                                    <option value="{{ $type->idTypeCaracteristique }}">{{ $type->libelleTypeCaracteristique }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Caractéristique</label>
                            <select class="form-select" name="idCaracteristique" id="idCaracteristique" required disabled>
                                <option value="">Sélectionnez un type</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Unité</label>
                            <select class="form-select" name="idUnite" id="idUnite" required disabled>
                                <option value="">Sélectionnez une caractéristique</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Valeur</label>
                            <input type="text" class="form-control" name="valeur" required>
                        </div>
                        <button class="btn btn-outline-primary w-100" type="submit">
                            <i class="bi bi-plus-circle"></i> Ajouter
                        </button>
                    </form>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Statistiques</h5>
                </div>
                <div class="card-body text-center">
                    <h3>{{ $infrastructure->valeursCaracteristiques->count() }}</h3>
                    <small class="text-muted">Caractéristiques ajoutées</small>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .return {
        cursor: pointer;
        margin-right: 10px;
    }
    .info-item {
        margin-bottom: 8px;
    }
</style>
<script>
 $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->libelle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'table1', 'Liste des caractéristiques')
    });
</script>
<script>
    // Dynamique : chargement des caractéristiques selon le type
    $('#idTypeCaracteristique').on('change', function () {
        let idType = $(this).val();
        $('#idCaracteristique').empty().prop('disabled', true);
        $('#idUnite').empty().prop('disabled', true);

        if (idType) {
            fetch('{{ url("/") }}/get-caracteristiques/' + idType)
                .then(res => res.json())
                .then(data => {
                    let options = '<option value="">Sélectionner</option>';
                    data.forEach(c => {
                        options += `<option value="${c.idCaracteristique}">${c.libelleCaracteristique}</option>`;
                    });
                    $('#idCaracteristique').html(options).prop('disabled', false);
                });
        }
    });

    // Dynamique : chargement des unités selon la caractéristique
    $('#idCaracteristique').on('change', function () {
        let id = $(this).val();
        $('#idUnite').empty().prop('disabled', true);

        if (id) {
            fetch('{{ url("/") }}/get-unites/' + id)
                .then(res => res.json())
                .then(data => {
                    let options = '<option value="">Sélectionner</option>';
                    data.forEach(u => {
                        options += `<option value="${u.idUnite}">${u.libelleUnite} (${u.symbole})</option>`;
                    });
                    $('#idUnite').html(options).prop('disabled', false);
                });
        }
    });
</script>
@endsection
