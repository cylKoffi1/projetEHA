<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

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
                    <div class="form-pays col-md-3 text-start">
                        <label for="pays_id" class="text-start">Pays</label>
                        <input type="text"  id="pays_id" name="pays_id[]" class="form-control" style="width: 100%;" value="{{ $codePays->nom_fr_fr }}" readonly>
                    </div>
                    <div class="col-5"></div>
                    @if(auth()->user()->groupe_utilisateur_id != 'ag')
                    <!-- Groupes Projets -->
                    <div class="form-group col-md-4">
                        <label for="groupe_projet_id">Groupe Projet</label>
                        <input type="text"  id="groupe_projet_id" name="groupe_projet_id[]" class="form-control" value="{{ $groupProjects->libelle }}" readonly>
                    </div>
                    @endif
                </div>
                <div class="row">
                    <div class="row mt-3">
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
                                            data-type-acteur="{{ $acteur->type_acteur }}"
                                            data-type-nom = "{{ $acteur->libelle_court }}">
                                            {{ $acteur->libelle_court }} {{ $acteur->libelle_long }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    <div class="row mt-3">

                        <div class="form-group col-md-4">
                            <label for="groupe_utilisateur_id">Groupe utilisateur</label>
                            <select class="form-control" id="groupe_utilisateur_id" name="groupe_utilisateur_id" required>
                                <option value="">Sélectionnez un rôle</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->code }}">{{ $role->libelle_groupe }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="login">Login</label>
                            <input type="text" class="form-control" id="login" name="login" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="login">Mot de passe </label>
                            <input type="text" class="form-control" id="password" name="password" required>
                        </div>

                        <!-- Adresse -->
                        <div class="form-group col-md-4">
                            <label for="adresse">Adresse</label>
                            <input type="text" class="form-control" id="adresse" name="adresse" readonly>
                        </div>
                        @if(auth()->user()->groupe_utilisateur_id != 'ab')
                        @if(auth()->user()->groupe_utilisateur_id != 'ad')
                        <!-- Champs d'exercice -->
                        <div class="form-group col-md-4">
                            <label for="champs_exercice">Champs d'exercice</label>
                            <select id="champs_exercice" name="champs_exercice[]" class="form-select js-select2" multiple="multiple" style="width: 100%;">
                                @foreach ($champsExercice as $champ)
                                    <option value="{{ $champ->code_decoupage }}">{{ $champ->libelle_decoupage }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Lieux d'exercice -->
                        <div class="form-group col-md-4">
                            <label for="lieux_exercice">Lieux d'exercice</label>
                            <select id="lieux_exercice" name="lieux_exercice[]" class="form-select js-select2" multiple="multiple" style="width: 100%;">

                            </select>
                        </div>
                        @endif

                        @endif


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
                        <th>Pays</th>
                        <th>Nom</th>
                        <th>Login</th>
                        <th>Rôle</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($utilisateurs as $utilisateur)
                        <tr>
                        <td>
                            @foreach ($utilisateur->pays as $pays)
                                {{ $pays->nom_fr_fr }}<br>
                            @endforeach
                        </td>

                            <td>{{ $utilisateur->acteur->libelle_long }}</td>
                            <td>{{ $utilisateur->login }}</td>
                            <td>{{ $utilisateur->groupeUtilisateur->libelle_groupe }}</td>
                            <td>
                                @if ($utilisateur->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <!-- Modifier -->
                                <a href="#" class="edit-button" data-id="{{ $utilisateur->id }}" data-bs-tog gle="modal" data-bs-target="#editUserModal">
                                    <i class="bi bi-pencil-square text-primary" style="font-size: 1.2rem; cursor: pointer;"></i>
                                </a>

                                <!-- Désactiver ou Réactiver -->
                                @if ($utilisateur->is_active)
                                    <a href="#" class="delete-button" data-id="{{ $utilisateur->id }}" data-bs-toggle="tooltip" title="Désactiver">
                                        <i class="bi bi-x-circle" style="font-size: 1.2rem; color: red; cursor: pointer;"></i>
                                    </a>
                                @else
                                    <a href="{{ route('utilisateurs.restore', $utilisateur->id) }}"data-id="{{ $utilisateur->id }}" class="restore-button" data-bs-toggle="tooltip" title="Réactiver">
                                        <i class="bi bi-check-circle" style="font-size: 1.2rem; color: green; cursor: pointer;"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal pour modifier l'utilisateur -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">

        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Modifier un Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-user-form" method="POST" action="">
                        @csrf
                        @method('PUT')

                        <input type="hidden" id="edit-user-id" name="id">

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label for="edit-acteur_id">Acteur</label>
                                <select class="form-control" id="edit-acteur_id" name="acteur_id" disabled>
                                    <option value="">Sélectionnez un acteur</option>
                                    @foreach ($acteurs as $acteur)
                                        <option value="{{ $acteur->code_acteur }}">{{ $acteur->libelle_long }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="edit-email">Email</label>
                                <input type="email" class="form-control" id="edit-email" name="email" readonly>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="edit-telephone">Téléphone</label>
                                <input type="text" class="form-control" id="edit-telephone" name="telephone" readonly>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="edit-adresse">Adresse</label>
                                <input type="text" class="form-control" id="edit-adresse" name="adresse" readonly>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="edit-groupe_utilisateur_id">Rôle</label>
                                <select class="form-control" id="edit-groupe_utilisateur_id" name="groupe_utilisateur_id">
                                    <option value="">Sélectionnez un rôle</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->code }}">{{ $role->libelle_groupe }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="edit-fonction_utilisateur">Fonction</label>
                                <select class="form-control" id="edit-fonction_utilisateur" name="fonction_utilisateur">
                                    <option value="">Sélectionnez une fonction</option>
                                </select>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


</section>

<script src="{{ asset('betsa/js/jquery.min.js')}} "></script>
    <script src="{{ asset('betsa/js/popper.js')}} "></script>
    <script src="{{ asset('betsa/js/bootstrap.min.js')}} "></script>
    <script src="{{ asset('betsa/js/main.js')}} "></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

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
    });

    document.addEventListener('DOMContentLoaded', function () {
        // Gestion du bouton Modifier
            document.querySelectorAll('.edit-button').forEach(button => {
                console.log('Le modal s\'affiche correctement.');
                button.addEventListener('click', function () {
                    const userId = this.getAttribute('data-id');
                    console.log(`Chargement des données pour l'utilisateur ID : ${userId}`);

                    fetch(`{{ url('/utilisateurs')}}/${userId}`)
                        .then(response => {
                            if (!response.ok) {
                                console.error('Erreur HTTP :', response.status);
                                throw new Error('Erreur de récupération des données');
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Données utilisateur :', data);

                            // Remplir les champs
                            document.getElementById('edit-user-id').value = data.id || '';
                            document.getElementById('edit-acteur_id').value = data.acteur_id || '';
                            document.getElementById('edit-email').value = data.email || '';
                            document.getElementById('edit-telephone').value = data.telephone || '';
                            document.getElementById('edit-adresse').value = data.adresse || '';
                            document.getElementById('edit-groupe_utilisateur_id').value = data.groupe_utilisateur_id || '';
                            document.getElementById('edit-fonction_utilisateur').value = data.fonction_utilisateur || '';

                            // Gérer les groupes projets
                            const groupSelect = document.getElementById('edit-groupe_projet_id');
                            const projectCodes = data.groupe_projets.map(project => project.code);
                            $(groupSelect).val(projectCodes).trigger('change');

                            // Afficher le modal
                            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                            modal.show();
                        })
                        .catch(error => {
                            console.error('Erreur lors de la récupération des données :', error);
                            alert('Erreur lors du chargement des données utilisateur.');
                        });
                });

        });


        // Gestion des boutons Désactiver/Activer
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.getAttribute('data-id');
                const url = `{{ url('/utilisateurs')}}/${userId}`;

                if (confirm('Êtes-vous sûr de vouloir désactiver cet utilisateur ?')) {
                    fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                        .then(response => {
                            if (response.ok) {
                                alert('Utilisateur désactivé avec succès.');
                                location.reload();
                            } else {
                                location.reload();

                            }
                        })
                        .catch(error => console.error('Erreur lors de la désactivation de l\'utilisateur :', error));
                }
            });
        });

        document.querySelectorAll('.restore-button').forEach(button => {
            button.addEventListener('click', function (event) {
                event.preventDefault();

                const userId = this.getAttribute('data-id'); // Assurez-vous que cet ID est défini
                if (!userId) {
                    alert('L\'ID de l\'utilisateur est manquant.');
                    return;
                }

                fetch(`{{ url('/utilisateurs')}}/restore/${userId}`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        alert('Utilisateur réactivé avec succès.');
                        location.reload();
                    } else {
                        location.reload();
                        /*alert('Erreur lors de la réactivation de l\'utilisateur.');*/
                    }
                })
                .catch(error => console.error('Erreur lors de la réactivation :', error));
            });
        });

    });

    document.addEventListener('DOMContentLoaded', function () {
        $(document).ready(function () {
        $('#groupe_projet_id').select2({
            rounded: true,
            shadow: true,
            allowClear: true,
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
        var pays = $('#pays_id').filterMultiSelect({
            // displayed when no options are selected
            placeholderText: "0 sélection",
            // placeholder for search field
            filterText: "Filtrer",
            // Select All text
            selectAllText: "Tout sélectionner",
            // Label text
            labelText: "",
            // the number of items able to be selected
            // 0 means no limit
            selectionLimit: 0,
            // determine if is case sensitive
            caseSensitive: false,
            // allows the user to disable and enable options programmatically
            allowEnablingAndDisabling: true,

        });
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
    document.addEventListener('DOMContentLoaded', function () {
        // Quand un acteur est sélectionné
        document.getElementById('acteur_id').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];

            // Récupérer les informations de l'acteur
            const libelleCourt = selectedOption.getAttribute('data-type-nom');
            const codeActeur = selectedOption.getAttribute('data-code-acteur');

            // Générer automatiquement le login (exemple : libelleCourt + codeActeur)
            const login = libelleCourt.toLowerCase() + '_' + codeActeur;

            // Mettre à jour le champ login
            document.getElementById('login').value = login;

            // Attribuer le mot de passe par défaut
            document.getElementById('password').value = '123456789';
        });
    });

</script>

@endsection
