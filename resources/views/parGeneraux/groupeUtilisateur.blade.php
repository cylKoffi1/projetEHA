@extends('layouts.app')

@section('content')

@if (session('success'))
    <script>toastr.success("{{ session('success') }}");</script>
@endif
@if (session('error'))
    <script>toastr.error("{{ session('error') }}");</script>
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
                            <li class="breadcrumb-item"><a href="">Paramètre générale</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Groupe utilisateur</li>

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
<!-- Formulaire d'ajout / modification -->
<div class="card mt-3">
    <div class="card-header">
        <h4 id="form-title">Ajouter un Groupe Utilisateur</h4>
    </div>
    <div class="card-body">
        <form id="groupeForm" action="{{ route('groupes-utilisateurs.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-2">
                    <label for="code">Code</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="libelle_groupe">Libellé</label>
                    <input type="text" name="libelle_groupe" class="form-control" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" id="submitButton" class="btn btn-primary">Enregistrer</button>
                    <button type="button" id="cancelButton" class="btn btn-secondary ms-2" style="display: none;">Annuler</button>
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
                    <th >Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupes as $groupe)
                    <tr>
                        <td>{{ $groupe->code }}</td>
                        <td>{{ $groupe->libelle_groupe }}</td>
                        <td>

                            <!-- Modifier -->
                            <a href="#" class="edit-button" data-id="{{ $groupe->code }}" style="display: inline;">
                                <i class="btn btn-link bi bi-pencil-square text-primary" style="font-size: 1.2rem; cursor: pointer;"></i>
                            </a>

                            <!--Supprimer -->
                            <form action="{{ route('groupes-utilisateurs.destroy', $groupe->code) }}" method="POST" class="delete-form" data-id="{{ $groupe->code }}">
                                @csrf
                                <button type="submit" class="btn btn-link" data-bs-toggle="tooltip" title="supprimer" onclick="return confirm('Voulez-vous vraiment supprimer ce groupe ?')">
                                    <i class="bi bi-x-circle" style="font-size: 1.2rem; color: red; cursor: pointer;"></i>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gérer le clic sur l'icône de modification
        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function () {
                const groupeId = this.getAttribute('data-id'); // Récupérer l'ID du groupe

                fetch(`{{ url('/groupeUtilisateur') }}/${groupeId}/edit`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            // Remplir le formulaire avec les données du groupe sélectionné
                            document.querySelector('input[name="code"]').value = data.code;
                            document.querySelector('input[name="code"]').readOnly = true; // Désactiver la modification du code
                            document.querySelector('input[name="libelle_groupe"]').value = data.libelle_groupe;

                            // Modifier l'action du formulaire pour passer en mode mise à jour
                            document.querySelector('#groupeForm').setAttribute('action', `{{ url('/groupeUtilisateur') }}/update/${groupeId}`);

                            // Modifier le texte et la classe du bouton pour "Modifier"
                            const submitButton = document.querySelector('#submitButton');
                            submitButton.textContent = "Modifier";
                            submitButton.classList.remove('btn-primary');
                            submitButton.classList.add('btn-warning');

                            // Afficher le bouton d'annulation si ce n'est pas déjà fait
                            document.querySelector('#cancelButton').style.display = 'inline-block';
                        }
                    })
                    .catch(error => console.error('Erreur lors de la récupération des données :', error));
            });
        });

        // Réinitialiser le formulaire lors d'un nouvel ajout
        document.querySelector('#cancelButton').addEventListener('click', function () {
            document.querySelector('#groupeForm').setAttribute('action', "{{ route('groupes-utilisateurs.store') }}");
            document.querySelector('#submitButton').textContent = "Enregistrer";
            document.querySelector('#submitButton').classList.remove('btn-warning');
            document.querySelector('#submitButton').classList.add('btn-primary');

            // Réactiver le champ "Code"
            document.querySelector('input[name="code"]').readOnly = false;
            document.querySelector('input[name="code"]').value = "";
            document.querySelector('input[name="libelle_groupe"]').value = "";

            // Cacher le bouton d'annulation
            document.querySelector('#cancelButton').style.display = 'none';
        });
    });
</script>


@endsection
