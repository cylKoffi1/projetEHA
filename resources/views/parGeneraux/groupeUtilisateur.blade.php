@extends('layouts.app')

@section('content')

@if (session('success'))
    <script>toastr.success("{{ session('success') }}");</script>
@endif
@if (session('error'))
    <script>toastr.error("{{ session('error') }}");</script>
@endif

<h3>Gestion des Groupes Utilisateurs</h3>

<!-- Formulaire d'ajout -->
<div class="card mt-3">
    <div class="card-header">
        <h4>Ajouter un Groupe Utilisateur</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('groupes-utilisateurs.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <label for="code">Code</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="libelle_groupe">Libellé</label>
                    <input type="text" name="libelle_groupe" class="form-control" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des groupes utilisateurs -->
<div class="card mt-3">
    <div class="card-header">
        <h4>Liste des Groupes Utilisateurs</h4>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%"  id="table1">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Libellé</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupes as $groupe)
                    <tr>
                        <td>{{ $groupe->code }}</td>
                        <td>{{ $groupe->libelle_groupe }}</td>
                        <td>

                            <!-- Modifier -->
                            <a href="#" action="{{ route('groupes-utilisateurs.update', $groupe->code) }}" class="edit-button" data-id="{{ $groupe->code }}" data-bs-toggle="modal" style="display: inline;">
                                <i class="btn btn-link bi bi-pencil-square text-primary" style="font-size: 1.2rem; cursor: pointer;"></i>
                            </a>
                            <!--Supprimer -->
                            <form action="{{ route('groupes-utilisateurs.destroy', $groupe->code) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-link" data-bs-toggle="tooltip" title="supprimer" onclick="return confirm('Voulez-vous vraiment supprimer ce groupe ?')">
                                    <i class=" bi bi-x-circle" style="font-size: 1.2rem; color: red; cursor: pointer;"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
     $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur->libelle_court }} {{ auth()->user()->acteur->libelle_long }}', 'table1', 'Liste des types acteurs')
    });
</script>
@endsection
