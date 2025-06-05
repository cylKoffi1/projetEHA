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
    }

    .nav-tabs .nav-link {
        border: 1px solid #dee2e6;
        border-bottom: none;
        margin-right: 5px;
    }

    .nav-tabs .nav-link.active {
        background-color: #f8f9fa;
        border-color: #dee2e6 #dee2e6 #f8f9fa;
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Plateforme </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Paramètre généraux</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Caractéristiques & Unités</li>

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
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="type-tab" data-bs-toggle="tab" data-bs-target="#type" type="button" role="tab" aria-controls="type" aria-selected="true">Type de Caractéristique</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="caracteristique-tab" data-bs-toggle="tab" data-bs-target="#caracteristique" type="button" role="tab" aria-controls="caracteristique" aria-selected="false">Caractéristique</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="unite-tab" data-bs-toggle="tab" data-bs-target="#unite" type="button" role="tab" aria-controls="unite" aria-selected="false">Unité</button>
                        </li>
                    </ul>
                </div>

                <div class="card-content">
                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">
                            <!-- Onglet Type de Caractéristique -->
                            <div class="tab-pane fade show active" id="type" role="tabpanel" aria-labelledby="type-tab">
                                <form class="form" method="POST" action="{{ route('type-caracteristique.store') }}" data-parsley-validate>
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mandatory">
                                                <label class="form-label" for="libelleType">Libellé du Type :</label>
                                                <input type="text" class="form-control" id="libelleType" name="libelleType" placeholder="Ex: Longueur" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <input type="submit" class="btn btn-primary" value="Enregistrer">
                                    </div>
                                </form>

                                <!-- Tableau des Types de Caractéristiques -->
                                <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%"  id="tableType">
                                    <thead>
                                        <tr>
                                            <th>Libellé</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($typesCaracteristiques as $type)
                                        <tr>
                                            <td>{{ $type?->libelleTypeCaracteristique }}</td>
                                            <td>
                                                <i class="bi bi-pencil-square text-primary" style="font-size: 1.2rem; cursor: pointer;" 
                                                    onclick="editType('{{ $type->idTypeCaracteristique }}', '{{ $type?->libelleTypeCaracteristique }}')" 
                                                    title="Modifier"></i>
                                                <form method="POST" action="{{ route('type-caracteristique.delete', $type?->idTypeCaracteristique) }}" style="display: inline;" onsubmit="return confirm('Confirmer la suppression ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="border: none; background: none; padding: 0; margin-left: 8px;">
                                                        <i class="bi bi-x-circle" style="font-size: 1.2rem; color: red; cursor: pointer;" title="Supprimer"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Onglet Caractéristique -->
                            <div class="tab-pane fade" id="caracteristique" role="tabpanel" aria-labelledby="caracteristique-tab">
                                <form class="form" method="POST" action="{{ route('caracteristique.store') }}" data-parsley-validate>
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mandatory">
                                                <label class="form-label" for="typeCaracteristique">Type de Caractéristique :</label>
                                                <select class="form-control" id="typeCaracteristique" name="typeCaracteristique" required>
                                                    <option value="">Sélectionner un type</option>
                                                    @foreach ($typesCaracteristiques as $type)
                                                        <option value="{{ $type->idTypeCaracteristique }}">{{ $type?->libelleTypeCaracteristique }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mandatory">
                                                <label class="form-label" for="libelleCaracteristique">Libellé de la Caractéristique :</label>
                                                <input type="text" class="form-control" id="libelleCaracteristique" name="libelleCaracteristique" placeholder="Ex: Longueur totale" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <input type="submit" class="btn btn-primary" value="Enregistrer">
                                    </div>
                                </form>
                                <br><br>
                                <!-- Tableau des Caractéristiques -->
                                <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%"  id="tableCaracteristique">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Libellé</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($caracteristiques as $caracteristique)
                                        <tr>
                                            <td>{{ $caracteristique->typeCaracteristique?->libelleTypeCaracteristique }}</td>
                                            <td>{{ $caracteristique->libelleCaracteristique }}</td>
                                            <td>
                                                <i class="bi bi-pencil-square text-primary" style="font-size: 1.2rem; cursor: pointer;" 
                                                    onclick="editCaracteristique('{{ $caracteristique->idCaracteristique }}', '{{ $caracteristique->idTypeCaracteristique }}', '{{ $caracteristique->libelleCaracteristique }}')" 
                                                    title="Modifier"></i>
                                                <form method="POST" action="{{ route('caracteristique.delete', $caracteristique->idCaracteristique) }}" style="display: inline;" onsubmit="return confirm('Confirmer la suppression ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="border: none; background: none; padding: 0; margin-left: 8px;">
                                                        <i class="bi bi-x-circle" style="font-size: 1.2rem; color: red; cursor: pointer;" title="Supprimer"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Onglet Unité -->
                            <div class="tab-pane fade" id="unite" role="tabpanel" aria-labelledby="unite-tab">
                                <form class="form" method="POST" action="{{ route('unite.store') }}" data-parsley-validate>
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mandatory">
                                                <label class="form-label" for="caracteristiqueUnite">Caractéristique :</label>
                                                <select class="form-control" id="caracteristiqueUnite" name="caracteristiqueUnite" required>
                                                    <option value="">Sélectionner une caractéristique</option>
                                                    @foreach ($caracteristiques as $caracteristique)
                                                        <option value="{{ $caracteristique->idCaracteristique }}">{{ $caracteristique->libelleCaracteristique }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mandatory">
                                                <label class="form-label" for="libelleUnite">Libellé de l'Unité :</label>
                                                <input type="text" class="form-control" id="libelleUnite" name="libelleUnite" placeholder="Ex: mètres" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <input type="submit" class="btn btn-primary" value="Enregistrer">
                                    </div>
                                </form>

                                <!-- Tableau des Unités -->
                                <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%"  id="tableUnite">
                                    <thead>
                                        <tr>
                                            <th>Caractéristique</th>
                                            <th>Libellé</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($unites as $unite)
                                        <tr>
                                            <td>{{ $unite->caracteristique->libelleCaracteristique }}</td>
                                            <td>{{ $unite->libelleUnite }}</td>
                                            <td>
                                                <i class="bi bi-pencil-square text-primary" style="font-size: 1.2rem; cursor: pointer;" 
                                                    onclick="editUnite('{{ $unite->idUnite }}', '{{ $unite->idCaracteristique }}', '{{ $unite->libelleUnite }}')" 
                                                    title="Modifier"></i>
                                                <form method="POST" action="{{ route('unite.delete', $unite->idUnite) }}" style="display: inline;" onsubmit="return confirm('Confirmer la suppression ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="border: none; background: none; padding: 0; margin-left: 8px;">
                                                        <i class="bi bi-x-circle" style="font-size: 1.2rem; color: red; cursor: pointer;" title="Supprimer"></i>
                                                    </button>
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
        </div>
    </div>
</section>

<script>
function editType(id, libelle) {
    document.getElementById('libelleType').value = libelle;
    const form = document.querySelector('#type form');
    form.action = "{{ route('type-caracteristique.update') }}";
    let hidden = document.getElementById('type_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'id';
        hidden.id = 'type_id_hidden';
        form.appendChild(hidden);
    }
    hidden.value = id;
}

function editCaracteristique(id, typeId, libelle) {
    document.getElementById('typeCaracteristique').value = typeId;
    document.getElementById('libelleCaracteristique').value = libelle;
    const form = document.querySelector('#caracteristique form');
    form.action = "{{ route('caracteristique.update') }}";
    let hidden = document.getElementById('caracteristique_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'id';
        hidden.id = 'caracteristique_id_hidden';
        form.appendChild(hidden);
    }
    hidden.value = id;
}

function editUnite(id, caracteristiqueId, libelle) {
    document.getElementById('caracteristiqueUnite').value = caracteristiqueId;
    document.getElementById('libelleUnite').value = libelle;
    const form = document.querySelector('#unite form');
    form.action = "{{ route('unite.update') }}";
    let hidden = document.getElementById('unite_id_hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'id';
        hidden.id = 'unite_id_hidden';
        form.appendChild(hidden);
    }
    hidden.value = id;
}
</script>
<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->lieblle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableType', 'Liste des types de caractéristiques');
    });
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->lieblle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableCaracteristique', 'Liste des caractéristiques');
    });    
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur?->lieblle_court }} {{ auth()->user()->acteur?->libelle_long }}', 'tableUnite', 'Liste des unités');
    });
</script>
@endsection