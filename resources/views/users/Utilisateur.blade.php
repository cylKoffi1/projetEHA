<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.4/css/select2.min.css" rel="stylesheet" />
@extends('layouts.app')

@section('content')

@if (session('success'))
    <script>toastr.success("{{ session('success') }}");</script>
@endif
@if (session('error'))
    <script>toastr.error("{{ session('error') }}");</script>
@endif

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <script>toastr.error("{{ $error }}");</script>
    @endforeach
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
            <h5>Utilisateur</h5>
        </div>
        <div class="card-body">
            <form id="user-form" action="{{ route('utilisateurs.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" value="POST" id="form-method">
                <input type="hidden" id="user-id" name="id">
                <div class="row">
                    <div class="form-pays col-md-3 text-start">
                        <label for="pays_id" class="text-start">Pays</label>
                        <input type="hidden" name="pays_id" id="pays_id" value="{{ $codePays->alpha3 }}">
                        <input type="text"  id="pays_libelle" name="pays_libelle[]" class="form-control" style="width: 100%;" value="{{ $codePays->nom_fr_fr }}" readonly>
                    </div>
                    <div class="col-5"></div>
                    <!-- Groupes Projets -->
                    <div class="form-group col-md-4">
                        <label for="groupe_projet_id">Groupe Projet</label>
                        <input type="hidden" name="groupe_projet_id" id="groupe_projet_id" value="{{ $groupProjects->code }}">
                        <input type="text"  id="groupe_projet_libelle" name="groupe_projet_libelle[]" class="form-control" value="{{ $groupProjects->libelle }}" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="row mt-3">
                        <div class="form-group col-md-4">
                            <label for="acteur_id">Acteur</label>

                            <!-- Sélection Acteur pour l'ajout -->
                            <div id="acteur-selection-ajout">
                                <select class="form-control" id="acteur_id" name="acteur_id" required>
                                    <option value="">Sélectionnez un acteur</option>
                                    @foreach ($acteurs as $acteur)
                                        <option value="{{ $acteur->code_acteur }}"
                                                data-code-acteur="{{ $acteur->code_acteur }}"
                                                data-email="{{ $acteur->email }}"
                                                data-telephone="{{ $acteur->telephone }}"
                                                data-adresse="{{ $acteur->adresse }}"
                                                data-type-acteur="{{ $acteur->type_acteur }}"
                                                data-type-nom="{{ $acteur->libelle_court }}">
                                            {{ $acteur->libelle_court }} {{ $acteur->libelle_long }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Sélection Acteur pour la modification (readonly) -->
                            <div id="acteur-selection-modifier" style="display: none;">
                                <select class="form-control" id="acteur_id_Modifier" name="acteur_id_Modifier" required readonly>
                                    @foreach ($acteurUpdate as $acteur)
                                        <option value="{{ $acteur->code_acteur }}"
                                                data-code-acteur="{{ $acteur->code_acteur }}"
                                                data-email="{{ $acteur->email }}"
                                                data-telephone="{{ $acteur->telephone }}"
                                                data-adresse="{{ $acteur->adresse }}"
                                                data-type-acteur="{{ $acteur->type_acteur }}"
                                                data-type-nom="{{ $acteur->libelle_court }}">
                                            {{ $acteur->libelle_court }} {{ $acteur->libelle_long }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
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
                        @if(auth()->user()->groupe_utilisateur_id != 'ab')
                        @if(auth()->user()->groupe_utilisateur_id != 'ad')

                            <div class="form-group col-md-4">
                                <label for="champs_exercice">Champs d'exercice</label>
                                <select id="champs_exercice" name="champs_exercice[]" class="form-select" multiple="multiple" style="width: 100%;" data-placeholder="Sélectionnez un champ">
                                    @foreach ($champsExercice as $champ)
                                        <option value="{{ $champ->code_decoupage }}">{{ $champ->libelle_decoupage }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label for="lieux_exercice">Lieux d'exercice</label>
                                <select id="lieux_exercice" name="lieux_exercice[]" class="form-select" multiple="multiple" style="width: 100%;" data-placeholder="Sélectionnez un lieu">
                                    @foreach ($lieuxExercice as $lieu)
                                        <option value="{{ $lieu->id }}">{{ $lieu->libelle }}</option>
                                    @endforeach
                                </select>
                            </div>


                        @endif

                        @endif

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
            <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="users-table">
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
                            <td>{{ $utilisateur?->acteur->pays?->nom_fr_fr ?? 'Aucun pays' }}</td>
                            <td>{{ $utilisateur?->acteur?->libelle_long }}</td>
                            <td>{{ $utilisateur->login }}</td>
                            <td>{{ $utilisateur?->groupeUtilisateur?->libelle_groupe }}</td>
                            <td>
                            @if ($utilisateur->is_blocked)
                                <span class="badge bg-warning">Bloqué</span>
                            @elseif ($utilisateur->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-danger">Inactif</span>
                            @endif

                            </td>
                            <td>
                                 <!-- Modifier -->
                                <a href="#" class="edit-button" data-id="{{ $utilisateur->id }}" data-bs-toggle="modal" style="display: inline;">
                                    <i class="btn btn-link bi bi-pencil-square text-primary" style="font-size: 1.2rem; cursor: pointer;"></i>
                                </a>

                                <!-- Désactiver si l'utilisateur est actif -->
                                @if ($utilisateur->is_active)
                                    <form action="{{ route('utilisateurs.disable', $utilisateur->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-link" data-bs-toggle="tooltip" title="Désactiver">
                                            <i class=" bi bi-x-circle" style="font-size: 1.2rem; color: red; cursor: pointer;"></i>
                                        </button>
                                    </form>
                                @else
                                    <!-- Réactiver si l'utilisateur est inactif -->
                                    <form action="{{ route('utilisateurs.restore', $utilisateur->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-link" data-bs-toggle="tooltip" title="Réactiver">
                                            <i class="bi bi-check-circle" style="font-size: 1.2rem; color: green; cursor: pointer;"></i>
                                        </button>
                                    </form>
                                @endif
                                @if ($utilisateur->is_blocked)
                                    <form action="{{ route('utilisateurs.debloquer', $utilisateur->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-link" data-bs-toggle="tooltip" title="Débloquer">
                                            <i class="bi bi-unlock" style="font-size: 1.2rem; color: orange;"></i>
                                        </button>
                                    </form>
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
        document.getElementById('toggle-form-btn').addEventListener('click', function () {
            document.getElementById('acteur-selection-ajout').style.display = 'block';
            document.getElementById('acteur-selection-modifier').style.display = 'none';

            // Activer required pour acteur_id et désactiver pour acteur_id_Modifier
            document.getElementById('acteur_id').setAttribute('required', 'true');
            document.getElementById('acteur_id_Modifier').removeAttribute('required');
        });
        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.getAttribute('data-id');

                fetch(`{{url('/utilisateurs')}}/${userId}`)
                    .then(response => {
                        if (!response.ok) {
                            //console.error('Erreur HTTP :', response.status);
                            throw new Error('Erreur lors de la récupération des données');
                        }
                        return response.json();
                    })
                    .then(data => {
                        //console.log('Données utilisateur :', data);

                        // Afficher le champ acteur_id_Modifier et cacher acteur_id
                        document.getElementById('acteur-selection-ajout').style.display = 'none';
                        document.getElementById('acteur-selection-modifier').style.display = 'block';

                        // Désactiver required pour acteur_id et activer pour acteur_id_Modifier
                        document.getElementById('acteur_id').removeAttribute('required');
                        document.getElementById('acteur_id_Modifier').setAttribute('required', 'true');

                        // Sélectionner l'acteur dans le champ de modification
                        const acteurSelect = document.getElementById('acteur_id_Modifier');
                        if (acteurSelect) {
                            acteurSelect.value = data.acteur_id;
                        }

                        // Remplir les autres champs
                        document.getElementById('user-id').value = data.id;
                        document.getElementById('email').value = data.email;
                        document.getElementById('telephone').value = data.acteur.telephone;
                        document.getElementById('adresse').value = data.acteur.adresse;
                        document.getElementById('groupe_utilisateur_id').value = data.groupe_utilisateur_id;
                        document.getElementById('login').value = data.login;
                        document.getElementById('password').value = ''; // Ne pas afficher l'ancien mot de passe

                        // Sélection des champs d'exercice
                        const champSelect = document.getElementById('champs_exercice');
                        if (champSelect) {
                            const champIds = data.champs_exercice.map(champ => champ.champ_exercice_id);
                            $(champSelect).val(champIds).trigger('change');
                        }

                        // Sélection des lieux d'exercice
                        const lieuSelect = document.getElementById('lieux_exercice');
                        if (lieuSelect) {
                            const lieuIds = data.lieux_exercice.map(lieu => lieu.lieu_exercice_id);
                            $(lieuSelect).val(lieuIds).trigger('change');
                        }

                        // Modifier l'action du formulaire pour la mise à jour
                        const form = document.getElementById('user-form');
                        if (form) {
                            form.setAttribute('action', `/utilisateurs/${userId}`);
                            document.getElementById('form-method').value = 'PUT';
                        }

                        // Changer le bouton "Enregistrer" en "Modifier"
                        const submitButton = document.getElementById('submit-button');
                        if (submitButton) {
                            submitButton.textContent = 'Modifier';
                            submitButton.classList.remove('btn-success');
                            submitButton.classList.add('btn-warning');
                        }

                        // Afficher le formulaire
                        document.getElementById('user-form-card').style.display = 'block';
                    })
                    .catch(error => {
                       // console.error('Erreur lors de la récupération des données :', error);
                        alert('Erreur lors du chargement des données utilisateur.');
                    });
            });
        });

        // Réinitialiser le formulaire pour l'ajout d'un nouvel utilisateur
        document.getElementById('toggle-form-btn').addEventListener('click', function () {
            document.getElementById('acteur-selection-ajout').style.display = 'block';
            document.getElementById('acteur-selection-modifier').style.display = 'none';
            document.getElementById('user-form').reset();
            document.getElementById('form-method').value = 'POST';
            document.getElementById('submit-button').textContent = 'Enregistrer';
            document.getElementById('submit-button').classList.remove('btn-warning');
            document.getElementById('submit-button').classList.add('btn-success');
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
<script>
$(document).ready(function () {
    let lieuxExerciceData = @json($lieuxExercice);

    function initSelect2WithCheckbox(selector) {
        $(selector).select2({
            closeOnSelect: false,
            allowClear: true,
            placeholder: $(selector).data('placeholder'),
            templateResult: formatOption,
            templateSelection: formatSelection
        });

        let selectElement = $(selector);

        // Ajouter une option "Tout sélectionner" en premier
        if (!selectElement.find('option[value="all"]').length) {
            selectElement.prepend('<option value="all">Tout sélectionner</option>');
        }

        function formatOption(option) {
            if (!option.id) return option.text;
            return $('<span><input type="checkbox" class="checkbox-select2"> ' + option.text + '</span>');
        }

        function formatSelection(option) {
            return option.text;
        }

        selectElement.on('select2:selecting', function (e) {
            if (e.params.args.data.id === "all") {
                let allOptions = $(this).find('option:not([value="all"])').map(function () {
                    return $(this).val();
                }).get();

                $(this).val(allOptions).trigger("change");
                return false;
            }
        });

        selectElement.on('select2:unselecting', function (e) {
            if (e.params.args.data.id === "all") {
                $(this).val([]).trigger("change");
                return false;
            }
        });
    }

    initSelect2WithCheckbox('#champs_exercice');
    initSelect2WithCheckbox('#lieux_exercice');

    // Auto-remplissage email, téléphone, adresse en fonction de l'acteur sélectionné
    $('#acteur_id').on('change', function () {
        let selected = $(this).find(':selected');
        $('#email').val(selected.data('email') || '');
        $('#telephone').val(selected.data('telephone') || '');
        $('#adresse').val(selected.data('adresse') || '');
    });

    // Accélération du filtrage des lieux d'exercice
    $('#champs_exercice').on('change', function () {
        const selectedDecoupage = $(this).val();
        const lieuExerciceSelect = $('#lieux_exercice');

        // Utilisation d'un Document Fragment pour améliorer la performance
        let fragment = document.createDocumentFragment();

        lieuExerciceSelect.empty();
        lieuExerciceSelect.append('<option value="all">Tout sélectionner</option>');

        if (selectedDecoupage) {
            lieuxExerciceData.forEach(function (lieu) {
                if (selectedDecoupage.includes(lieu.code_decoupage)) {
                    let option = document.createElement("option");
                    option.value = lieu.id;
                    option.textContent = lieu.libelle;
                    fragment.appendChild(option);
                }
            });
        }

        lieuExerciceSelect.append(fragment);
        lieuExerciceSelect.trigger('change');
    });
});
</script>
@endsection
