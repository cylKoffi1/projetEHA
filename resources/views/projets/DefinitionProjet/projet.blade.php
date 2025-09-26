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
                            <li class="breadcrumb-item active" aria-current="page">Nouveau projet</li>

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
        <div class="row match-height">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Chef de projet</h4>
                            <form id="contratForm" action="{{ route('contrats.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                                <input type="hidden" name="contrat_id" id="contrat_id">

                                <div class="row">
                                    <div class="col">
                                        <label for="projet">Projet</label>
                                        <select name="projet_id" class="form-control" required>
                                            <option value="">-- Sélectionnez --</option>
                                            @foreach($projets as $projet)
                                                <option value="{{ $projet->code_projet }}">{{ $projet->code_projet }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label for="chef_projet">Chef de projet</label>
                                        <select name="chef_projet_id" class="form-control" required>
                                            <option value="">-- Sélectionnez --</option>
                                            @foreach($chefs as $chef)
                                                <option value="{{ $chef->code_acteur }}">{{ $chef?->libelle_court }} {{ $chef?->libelle_long }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <label for="date_debut">Date début</label>
                                        <input type="date" name="date_debut" class="form-control" required>
                                    </div>

                                    <div class="col">
                                        <label for="date_fin">Date fin</label>
                                        <input type="date" name="date_fin" class="form-control" required>
                                    </div>
                                </div>
                                @can("ajouter_ecran_" . $ecran->id)
                                <button type="submit" id="formButton" class="btn btn-primary mt-3">Enregistrer</button>
                                @endcan

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
                                <th>Action</th>
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
                                        <div class="dropdown">
                                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownMenu{{ $contrat->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenu{{ $contrat->id }}">
                                                @can("modifier_ecran_" . $ecran->id)
                                                <li>
                                                <button class="dropdown-item text-warning" type="button" onclick="editContrat(@js([
                                                        'id' => $contrat->id,
                                                        'code_projet' => $contrat->code_projet,
                                                        'code_acteur' => $contrat->code_acteur,
                                                        'date_debut' => $contrat->date_debut,
                                                        'date_fin' => $contrat->date_fin,
                                                    ]))"
>
                                                    <i class="bi bi-pencil-square"></i> Modifier
                                                </button>

                                                </li>
                                                @endcan
                                                @can("supprimer_ecran_" . $ecran->id)
                                                <li>
                                                    <form action="{{ route('contrats.destroy', $contrat->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                                                        <button class="dropdown-item text-danger" type="submit">
                                                            <i class="bi bi-trash"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </li>
                                                @endcan
                                                @can("consulter_ecran_" . $ecran->id)
                                                <li>
                                                    <a class="dropdown-item text-info" href="{{ route('contrats.fiche', $contrat->id) }}">
                                                        <i class="bi bi-file-earmark-text"></i> Voir fiche
                                                    </a>
                                                </li>
                                                @endcan
                                                @can("consulter_ecran_" . $ecran->id)
                                                <li>
                                                    <a class="dropdown-item text-secondary" href="{{ route('contrats.pdf', $contrat->id) }}">
                                                        <i class="bi bi-download"></i> Télécharger
                                                    </a>
                                                </li>
                                                @endcan
                                            </ul>
                                        </div>
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
<script>
    $('#contratForm').on('submit', function(e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');
        let method = form.find('input[name="_method"]').val() || 'POST';

        $.ajax({
            url: url,
            method: method,
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    alert(response.success);
                    // Recharger tableau ou injecter nouvel élément ici
                    setTimeout(() => location.reload(), 1000); // ou mise à jour dynamique
                }
            },
            error: function(xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    let errs = Object.values(xhr.responseJSON.errors).flat();
                    alert("Erreurs :\n" + errs.join("\n"), 'error');
                } else {
                    let msg = xhr.responseJSON?.error || xhr.responseJSON?.message || 'Erreur inconnue.';
                    alert(msg, 'error');
                }
            }

        });
    });


    function editContrat(data) {
        $('#contrat_id').val(data.id);

        let projetSelect = $('select[name="projet_id"]');
        if (!projetSelect.find(`option[value="${data.code_projet}"]`).length) {
            projetSelect.append(`<option value="${data.code_projet}" selected>${data.code_projet}</option>`);
        }
        projetSelect.val(data.code_projet);

        $('select[name="chef_projet_id"]').val(data.code_acteur);
        $('input[name="date_debut"]').val(data.date_debut);
        $('input[name="date_fin"]').val(data.date_fin);

        // Supprimer un éventuel ancien _method
        $('#contratForm').find('input[name="_method"]').remove();
        $('#contratForm').attr('action', `{{url('/')}}/contrats/${data.id}`)
            .append('<input type="hidden" name="_method" value="PUT">');

        $('#formButton').text('Mettre à jour');
    }

</script>


@endsection
