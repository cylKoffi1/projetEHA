<!-- resources/views/users/create.blade.php -->

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
        /* Couleur du texte pour les messages d'erreur */
    }

    fieldset {
        border: 1px solid gray;

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
                            <li class="breadcrumb-item"><a href="">Paramètre spécifiques</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Nouvelle personne</li>

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
                    <h4 class="card-title">Nouvelle personne</h4>
                    <span id="create_new"></span>
                    @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @elseif (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>

                    @endif

                </div>
                <div class="card-content">
                    <div class="card-body">
                        <form class="form" id="personnelForm" method="POST" enctype="multipart/form-data" action="{{ route('personnel.store') }}">
                            @csrf
                            <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                            <div class="row">

                                    <div class="col">
                                        <label for="pays">Pays :</label>
                                        <select name="pays_display" id="pays" class="form-select" disabled>
                                            <option value="">Sélectionner le pays</option>
                                            @foreach($pays as $ppay)
                                            <option value="{{ $ppay->id }}" {{ $ppay->alpha3 == $userCountryId ? 'selected' : '' }}>{{ $ppay->nom_fr_fr }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="pays" value="{{ $userCountryId }}">
                                        @error('pays')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col">
                                        <label for="pays">Groupe utilisateur :</label>
                                        <select name="pays_display" id="pays" class="form-select" >
                                            <option value="">Sélectionner le groupe</option>
                                            @foreach($grpUser as $grpUsers)
                                            <option value="{{ $grpUsers->code }}">{{ $grpUsers->libelle_groupe }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="pays" value="{{ $userCountryId }}">
                                        @error('pays')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col">
                                        <label for="pays">Groupe Projet :</label>
                                        <select name="pays_display" id="pays" class="form-select" >
                                            <option value="">Sélectionner le groupe projet</option>
                                            @foreach($groupe_projet as $groupe_proje)
                                            <option value="{{ $groupe_proje->code }}" >{{ $groupe_proje->libelle }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="pays" value="{{ $userCountryId }}">
                                        @error('pays')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col">
                                        <label>Nom</label>
                                        <div class="form-group position-relative has-icon-left">
                                            <input type="text" id="nom" name="nom" class="form-control" placeholder="Nom">
                                            <div class="form-control-icon">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div id="nom-error" class="invalid-feedback"></div>
                                            @error('nom')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Prénom</label>
                                        <div class="form-group position-relative has-icon-left">
                                            <input type="text" id="prenom" class="form-control" required name="prenom" placeholder="Prénom" />
                                            <div class="form-control-icon">
                                                <i class="bi bi-person"></i>
                                            </div>
                                        </div>
                                        @error('prenom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                            </div>
                            <div class="row">
                            <div class="col">
                                        <label for="structure_type">Type acteur :</label>
                                        <select name="structure_type" id="structure_type" class="form-select" onchange="showSelect(this.value)" required>
                                            <option value="">Sélectionner le type</option>
                                            <option value="bailleur">Bailleur</option>
                                            <option value="agence">Agence</option>
                                            <option value="ministere">Ministère</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label for="structure">Acteur :</label>
                                        <select name="bailleur" id="bailleur" class="form-select" style="display: none;" onclick="filterOptions('bailleurss')">
                                            <option value="">Sélectionner le bailleur</option>
                                            @foreach($bailleurs as $bailleur)
                                            <option value="{{ $bailleur->code_bailleur }}">{{ $bailleur->libelle_long }}</option>
                                            @endforeach
                                        </select>

                                        <select name="agence" id="agence" class="form-select" style="display: none;" onclick="filterOptions('agence_execution')">
                                            <option value="">Sélectionner l'agence</option>
                                            @foreach($agences as $agence)
                                            <option value="{{ $agence->code_agence_execution }}">{{ $agence->nom_agence }}</option>
                                            @endforeach
                                        </select>

                                        <select name="ministere" id="ministere" class="form-select" style="display: none;" onclick="filterOptions('ministere')">
                                            <option value="">Sélectionner le ministère</option>
                                            @foreach($ministeres as $ministere)
                                            <option value="{{ $ministere->code }}">{{ $ministere->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="fonction">Fonction :</label>
                                        <select name="fonction" id="fonction" class="form-select" required>
                                            <option value="">--- ---</option>
                                            @foreach($fonctions as $fonction)
                                                <option value="{{ $fonction->code }}" data-structure="{{ $fonction->code_structure }}">{{ $fonction->libelle_fonction }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('fonction')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="niveau_acces_id">Champ d'exercice :</label>
                                        <select name="niveau_acces_id" id="niveau_acces_id" class="form-select" required>
                                            <option value="">--- ---</option>
                                            @foreach($decoupages as $decoupage)
                                            <option value="{{ $decoupage->code_decoupage }}">{{ $decoupage->libelle_decoupage }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="lieu_exercice">Lieu d'exercice :</label>
                                        <select name="lieu_exercice" id="lieu_exercice" class="form-select" required>
                                            <option value="">--- ---</option>
                                            <option value="{{ $lieu->id }}">{{ $lieu->libelle }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <label>Email</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="email" name="email" class="form-control" placeholder="Email">
                                        <div class="form-control-icon">
                                            <i class="bi bi-envelope"></i>
                                        </div>
                                        <div id="email-error" class="invalid-feedback"></div>
                                        @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">Téléphone</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="tel" class="form-control" required name="tel" placeholder="Téléphone" />
                                        <div class="form-control-icon">
                                            <i class="bi bi-phone"></i>
                                        </div>
                                    </div>
                                    @error('tel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col">
                                    <label class="form-label">Adresse</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="adresse" class="form-control" required name="adresse" placeholder="Adresse" />
                                        <div class="form-control-icon">
                                            <i class="bi bi-house"></i>
                                        </div>
                                    </div>
                                    @error('adresse')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 d-flex justify-content-end">
                                    {{-- <button type="reset" class="btn btn-light-secondary me-1 mb-1">
                                        Annuler
                                    </button> --}}
                                    @can("ajouter_ecran_".$ecran->id)
                                    <button type="submit" id="soumettre_personnel" class="btn btn-primary me-1 mb-1">
                                        Enregistrer
                                    </button>
                                    @endcan
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <h4><a href="{{ route('users.personnel') }}?ecran_id={{ $ecran->id }}">Voir la liste</a></h4>
    </div>
</section>

<script>
    function filterOptions(structure) {
        var select = document.getElementById('fonction');
        var options = select.options;
        var selectedStructure = structure.toLowerCase();

        for (var i = 0; i < options.length; i++) {
            var option = options[i];
            var optionStructure = option.getAttribute('data-structure');

            if (optionStructure === selectedStructure || !selectedStructure) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        }
    }

    // Appel initial pour afficher les données de bailleur par défaut
    filterOptions('bailleurss');
    $(document).ready(function() {






        $('#personne').on('change', function() {
            updateEmail($(this));
        });
        $('#niveau_acces_id').on('change', function() {
            showSelect_r($(this).val());
            $('#na').val(110);
            $("#na").prop("disabled", false);
        });
        $('#niveau_acces_id').trigger('change');

        $('#bailleur').on('change', function () {
            showSelect_r('na');
            $("#niveau_acces_id").prop("disabled", false);
            $('#na').val(110);
        });
        $('#agence').on('change', function () {
            $("#niveau_acces_id").prop("disabled", false);
        });
        $('#ministere').on('change', function () {
            $("#niveau_acces_id").prop("disabled", false);
        });
    });




    document.getElementById('email').addEventListener('keyup', function() {
        var email = this.value;

        // Effectuer la requête AJAX
        $.ajax({
            url: '/check-email'
            , method: 'get'
            , data: {
                email: email
            }
            , success: function(response) {
                console.log(response);
                if (response.exists) {
                    document.getElementById('email-error').innerText = 'Cet eamil est déjà utilisé par un autre utilisateur.';
                    document.getElementById('email').classList.add('is-invalid');
                } else {
                    document.getElementById('email-error').innerText = '';
                    document.getElementById('email').classList.remove('is-invalid');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);

            }
        });
    });

    function showSelect(type) {
        // Cacher tous les selects
        document.getElementById('bailleur').style.display = 'none';
        document.getElementById('agence').style.display = 'none';
        document.getElementById('ministere').style.display = 'none';

        // Afficher le select correspondant au type choisi
        if (type) {
            document.getElementById(type).style.display = 'block';
        }
    }

    document.getElementById('structure_type').addEventListener('change', function() {
        showSelect(this.value);
    });


    $(document).ready(function() {
        $('#niveau_acces_id').on('change', function() {
            var selectedDecoupage = $(this).val();
            var lieuExerciceSelect = $('#lieu_exercice');

            // Vider les options actuelles
            lieuExerciceSelect.empty();
            lieuExerciceSelect.append('<option value="">--- ---</option>');

            if (selectedDecoupage) {
                // Filtrer les localités en fonction du découpage sélectionné
                @foreach($localites as $localite)
                if ('{{ $localite->code_decoupage }}' === selectedDecoupage) {
                    lieuExerciceSelect.append('<option value="{{ $localite->id }}">{{ $localite->libelle }}</option>');
                }
                @endforeach
            }
        });
    });
</script>
@endsection
