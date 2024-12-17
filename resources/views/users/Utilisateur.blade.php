<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />
@extends('layouts.app')

@section('content')

@if (session('success'))
    <script>alert("{{ session('success') }}");</script>
@endif

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-md-6">
                <h3>Gestion des Utilisateurs</h3>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" id="toggle-form-btn">
                    <i class="bi bi-plus-circle"></i> Ajouter un Utilisateur
                </button>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <!-- Formulaire caché par défaut -->
    <div class="card mt-3" id="user-form-card" style="display: none;">
        <div class="card-header">
            <h5>Formulaire Utilisateur</h5>
        </div>
        <div class="card-body">
            <form id="user-form" action="{{ route('utilisateurs.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" value="POST" id="form-method">
                <input type="hidden" id="user-id" name="id">

                <div class="row">
                    <!-- Acteur -->
                    <div class="row">
                        <!-- Acteur -->
                        <div class="form-group col-md-4">
                            <label for="acteur_id">Acteur</label>
                            <select class="form-control" id="acteur_id" name="acteur_id" required>
                                <option value="">Sélectionnez un acteur</option>
                                @foreach ($acteurs as $acteur)
                                    <option value="{{ $acteur->code_acteur }}"
                                            data-code-acteur = "{{ $acteur->code_acteur }}"
                                            data-email="{{ $acteur->email }}"
                                            data-telephone="{{ $acteur->telephone }}"
                                            data-adresse="{{ $acteur->adresse }}"
                                            data-type-acteur="{{ $acteur->type_acteur }}">
                                            {{ $acteur->libelle_court }} {{ $acteur->libelle_long }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <!-- Email -->
                        <div class="form-group col-md-4">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" readonly>
                        </div>

                        <!-- Téléphone -->
                        <div class="form-group col-md-4">
                            <label for="telephone">Téléphone</label>
                            <input type="text" class="form-control" id="telephone" name="telephone" readonly>
                        </div>

                        <!-- Adresse -->
                        <div class="form-group col-md-4">
                            <label for="adresse">Adresse</label>
                            <input type="text" class="form-control" id="adresse" name="adresse" readonly>
                        </div>
                    </div>


                    <!-- Rôle -->
                    <div class="form-group col-md-4">
                        <label for="groupe_utilisateur_id">Rôle</label>
                        <select class="form-control" id="groupe_utilisateur_id" name="groupe_utilisateur_id" required>
                            <option value="">Sélectionnez un rôle</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->libelle_groupe }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Login -->
                    <div class="form-group col-md-4">
                        <label for="login">Login</label>
                        <input type="text" class="form-control" id="login" name="login" required>
                    </div>
                </div>

                <div class="row mt-3">
                    <!-- Fonction utilisateur -->
                    <div class="form-group col-md-4">
                        <label for="fonction_utilisateur">Fonction</label>
                        <select class="form-control" id="fonction_utilisateur" name="fonction_utilisateur" required>
                            <option value="">Sélectionnez une fonction</option>
                        </select>
                    </div>

                    <!-- Groupes Projets -->
                    <div class="form-group col-md-4">
                        <label for="groupe_projet_id">Groupes Projets</label>
                        <select  id="groupe_projet_id" name="groupe_projet_id[]" class="form-select js-select2" multiple="multiple" style="width: 100%;">
                            @foreach ($groupProjects as $project)
                                <option value="{{ $project->id }}">{{ $project->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <!-- Champs d'exercice -->
                    <div class="form-group col-md-6">
                        <label for="champs_exercice">Champs d'exercice</label>
                        <select id="champs_exercice" name="champs_exercice[]" class="form-select js-select2" multiple="multiple" style="width: 100%;">
                            @foreach ($champsExercice as $champ)
                                <option value="{{ $champ->code_decoupage }}">{{ $champ->libelle_decoupage }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Lieux d'exercice -->
                    <div class="form-group col-md-6">
                        <label for="lieux_exercice">Lieux d'exercice</label>
                        <select id="lieux_exercice" name="lieux_exercice[]" class="form-select js-select2" multiple="multiple" style="width: 100%;">


                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-success" id="submit-button">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="card mt-3">
        <div class="card-header">
            <h5>Liste des Utilisateurs</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" id="users-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Login</th>
                        <th>Rôle</th>
                        <th>Groupes Projets</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($utilisateurs as $utilisateur)
                        <tr>
                            <td>{{ $utilisateur->acteur->libelle_long }}</td>
                            <td>{{ $utilisateur->login }}</td>
                            <td>{{ $utilisateur->groupeUtilisateur->libelle_groupe }}</td>
                            <td>
                                @foreach ($utilisateur->groupeProjet as $groupeProjet)
                                    <span class="badge bg-primary">{{ $groupeProjet->libelle }}</span>
                                @endforeach
                            </td>
                            <td>
                                @if ($utilisateur->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <a href="#" class="edit-button" data-id="{{ $utilisateur->id }}">Modifier</a>
                                @if ($utilisateur->is_active)
                                    <a href="#" class="delete-button" data-id="{{ $utilisateur->id }}">Désactiver</a>
                                @else
                                    <a href="{{ route('utilisateurs.restore', $utilisateur->id) }}">Activer</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<script src="{{ asset('betsa/js/jquery.min.js')}} "></script>
    <script src="{{ asset('betsa/js/popper.js')}} "></script>
    <script src="{{ asset('betsa/js/bootstrap.min.js')}} "></script>
    <script src="{{ asset('betsa/js/main.js')}} "></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Quand un acteur est sélectionné
        document.getElementById('acteur_id').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];

            // Récupérer les données depuis l'option sélectionnée
            const email = selectedOption.getAttribute('data-email');
            const telephone = selectedOption.getAttribute('data-telephone');
            const adresse = selectedOption.getAttribute('data-adresse');

            // Mettre à jour les champs readonly
            document.getElementById('email').value = email ? email : '';
            document.getElementById('telephone').value = telephone ? telephone : '';
            document.getElementById('adresse').value = adresse ? adresse : '';
        });
    });
    document.addEventListener('DOMContentLoaded', function () {
        // Quand un acteur est sélectionné
        document.getElementById('acteur_id').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];

            // Récupérer le type d'acteur (via une nouvelle propriété `data-type-acteur`)
            const typeActeur = selectedOption.getAttribute('data-type-acteur');

            // Récupérer les fonctions disponibles pour ce type d'acteur
            const fonctionSelect = document.getElementById('fonction_utilisateur');
            fonctionSelect.innerHTML = '<option value="">Sélectionnez une fonction</option>'; // Réinitialiser les options

            if (typeActeur) {
                fetch(`/fonctions-par-type-acteur/${typeActeur}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(fonction => {
                            const option = document.createElement('option');
                            option.value = fonction.code;
                            option.textContent = fonction.libelle_fonction;
                            fonctionSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Erreur lors du chargement des fonctions:', error));
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Bouton pour afficher ou cacher le formulaire
        document.getElementById('toggle-form-btn').addEventListener('click', function () {
            const formCard = document.getElementById('user-form-card');
            formCard.style.display = formCard.style.display === 'none' ? 'block' : 'none';
        });

        // Modification d'un utilisateur
        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                // TODO: Charger les données de l'utilisateur via une requête AJAX si nécessaire
                // Mettre à jour le formulaire pour la modification
                document.getElementById('form-method').value = 'PUT';
                document.getElementById('user-form').action = `/utilisateurs/${id}`;
                document.getElementById('submit-button').textContent = 'Modifier';

                // Afficher le formulaire
                document.getElementById('user-form-card').style.display = 'block';
            });
        });

        // Désactivation ou réactivation
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                if (confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ?')) {
                    document.getElementById(`delete-form-${id}`).submit();
                }
            });
        });
    });
    document.addEventListener('DOMContentLoaded', function () {
        $('#groupe_projet_id').select2({
            rounded: true,
            shadow: true,
		    placeholder: "Clique pour selectionne les sous-domaines",
            tagColor: {
                textColor: '#327b2c',
                borderColor: '#92e681',
                bgColor: '#eaffe6',
            },
            onChange: function(values) {
                console.log(values)
            }
	    });

        // Initialisation pour d'autres champs (si nécessaire)
        $('#champs_exercice').select2({
            rounded: true,
            shadow: true,
		    placeholder: "Clique pour selectionne les sous-domaines",
            tagColor: {
                textColor: '#327b2c',
                borderColor: '#92e681',
                bgColor: '#eaffe6',
            },
            onChange: function(values) {
                console.log(values)
            }
	    });

        $('#lieux_exercice').select2({
            rounded: true,
            shadow: true,
		    placeholder: "Clique pour selectionne les sous-domaines",
            tagColor: {
                textColor: '#327b2c',
                borderColor: '#92e681',
                bgColor: '#eaffe6',
            },
            onChange: function(values) {
                console.log(values)
            }
	    });
    });
    $(document).ready(function () {
        // Préparer les données des lieux d'exercice pour un filtrage rapide
        const lieuxExerciceData = @json($lieuxExercice);

        $('#champs_exercice').on('change', function () {
            const selectedDecoupage = $(this).val(); // Découpages sélectionnés
            const lieuExerciceSelect = $('#lieux_exercice');

            // Réinitialiser la liste des lieux
            lieuExerciceSelect.empty();
            lieuExerciceSelect.append('<option value="">--- Sélectionnez un lieu ---</option>');

            if (selectedDecoupage) {
                // Ajouter les lieux correspondant aux champs sélectionnés
                lieuxExerciceData.forEach(function (lieu) {
                    if (selectedDecoupage.includes(lieu.code_decoupage)) {
                        lieuExerciceSelect.append(
                            `<option value="${lieu.id}">${lieu.libelle}</option>`
                        );
                    }
                });
            }

            // Réinitialiser l'affichage du multiselect
            lieuExerciceSelect.trigger('change');
        });

        // Initialisation des selects avec Select2
        $('#groupe_projet_id, #champs_exercice, #lieux_exercice').select2({
            placeholder: 'Sélectionnez une option',
            allowClear: true
        });
    });

</script>

@endsection
