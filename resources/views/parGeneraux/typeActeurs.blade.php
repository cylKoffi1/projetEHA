@extends('layouts.app')

@section('content')
@if (session('success'))
<script>
    alert("{{ session('success') }}");
</script>
@endif


<section class="section">
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
                            <li class="breadcrumb-item active" aria-current="page">Type d'acteur</li>

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
    <div class="card">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @elseif (session('success'))
            <div class="alert alert-success">
                <ul>
                    <li>{{ session('success') }}</li>
                </ul>
            </div>
        @endif
        <div class="card-header">
            <h5>Type d'Acteur</h5>
        </div>
        <div class="card-body">

            <form id="type-acteur-form" action="{{ route('type-acteurs.store') }}" method="POST">
                @csrf
                <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                <input type="hidden" id="method" name="_method" value="POST">
                <div class="form-group">
                    <div class="row">
                        <div class="col">
                            <label for="cd_type_acteur">Code</label>
                            <input type="text" class="form-control" name="cd_type_acteur" id="cd_type_acteur" placeholder="Ex: BA" required>
                        </div>
                        <div class="col">
                            <label for="libelle_type_acteur">Libellé</label>
                            <input type="text" class="form-control" id="libelle_type_acteur" name="libelle_type_acteur" placeholder="Ex: Bailleur" required>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2" id="submit-button">Ajouter</button>
            </form>

        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Liste des Types d'Acteurs</h5>
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
                    @foreach ($typesActeurs as $typeActeur)
                    <tr>
                        <td>{{ $typeActeur->cd_type_acteur }}</td>
                        <td>{{ $typeActeur->libelle_type_acteur }}</td>
                        <td>
                            <a href="#" class="edit-button" data-id="{{ $typeActeur->id }}" data-cd="{{ $typeActeur->cd_type_acteur }}" data-libelle="{{ $typeActeur->libelle_type_acteur }}">
                                <i class="bi bi-pencil-square text-primary" style="font-size: 1.2rem; cursor: pointer;"></i>
                            </a>

                            <a  href="#" class="delete-button" data-cd="{{ $typeActeur->cd_type_acteur }}" data-toggle="modal" data-target="#deleteModal">
                                <i class="bi bi-x-circle" style="font-size: 1.2rem; color: red; cursor: pointer;"></i>
                            </a>


                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="" method="POST" id="delete-form">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Supprimer Type d'Acteur</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h6 class="danger">Êtes-vous sûr de vouloir supprimer ce type d'acteur ?</h6>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
        $(document).ready(function() {
        initDataTable('{{ auth()->user()->acteur->libelle_court }} {{ auth()->user()->acteur->libelle_long }}', 'table1', 'Liste des types acteurs')
    });
document.addEventListener('DOMContentLoaded', function() {
    // Modifier un type d'acteur
    document.querySelectorAll('.edit-button').forEach(button => {
        button.addEventListener('click', function() {
            const cd = this.getAttribute('data-cd');
            const libelle = this.getAttribute('data-libelle');

            document.getElementById('cd_type_acteur').value = cd;
            document.getElementById('libelle_type_acteur').value = libelle;

            const form = document.getElementById('type-acteur-form');
            form.action = `/type-acteurs/${cd}`;
            document.getElementById('method').value = 'PUT';

            document.getElementById('submit-button').textContent = 'Modifier';
        });
    });

    // Réinitialiser le formulaire pour la création
    document.getElementById('submit-button').addEventListener('click', function() {
        const form = document.getElementById('type-acteur-form');
        if (document.getElementById('method').value === 'POST') {
            form.action = '{{ route("type-acteurs.store") }}';
        }
    });

    // Suppression
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', function() {
            const cd = this.getAttribute('data-cd');
            const form = document.getElementById('delete-form');
            form.action = `/type-acteurs/${cd}`;
        });
    });
});

</script>
@endsection
