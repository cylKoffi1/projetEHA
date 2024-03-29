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

</style>
<section id="multiple-column-form">
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Modifier une personne </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/admin">Tableau de bord</a></li>

                            <li class="breadcrumb-item active" aria-current="page">Modifier personne</li>

                        </ol>
                    </nav>
                    <div class="row">
                        <span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-right: 10px;"></span>
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
                    <h4 class="card-title">Modifier personne</h4>
                    @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                </div>
                <div class="card-content">
                    <div class="card-body">
                        <form class="form" method="POST" enctype="multipart/form-data" action="{{ route('personne.update', ['personnelId' => $personne->code_personnel]) }}">
                            @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="nom" class="form-label">Nom</label>
                                        <input type="text" id="nom" class="form-control" required value="{{ $personne->nom }}" placeholder="Nom" name="nom" />
                                    </div>
                                    @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="prenom" class="form-label">Prénom</label>
                                        <input type="text" id="prenom" class="form-control" value="{{ $personne->prenom }}" required placeholder="Prénom" name="prenom" />
                                    </div>
                                    @error('prenom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col">
                                    @if($structureRattachement)
                                    <div class="form-group">
                                        <label for="structure_ratache">Structure :</label>
                                        <label for="bai">B :</label>
                                        <input type="radio" value="bai" name="structure" id="bai" onclick="showSelect('bailleur')" {{ $structureRattachement->type_structure == 'bailleurss' ? 'checked' : '' }} style="margin-right: 15px;">
                                        <label for="age">A :</label>
                                        <input type="radio" name="structure" value="age" id="age" onclick="showSelect('agence')" {{ $structureRattachement->type_structure == 'agence_execution' ? 'checked' : '' }} style="margin-right: 15px;">
                                        <label for="min">M :</label>
                                        <input type="radio" name="structure" value="min" id="min" onclick="showSelect('ministere')" {{ $structureRattachement->type_structure == 'ministere' ? 'checked' : '' }}>

                                        <select name="bailleur" id="bailleur" class="form-select" style="{{ $structureRattachement->type_structure == 'bailleurss' ? '' : 'display: none;' }}">
                                            <option value="">Selectionner le bailleur</option>
                                            @foreach($bailleurs as $bailleur)
                                            <option value="{{ $bailleur->code_bailleur }}" {{ $structureRattachement->code_structure == $bailleur->code_bailleur ? 'selected' : '' }}>{{ $bailleur->libelle_long }}</option>
                                            @endforeach
                                        </select>

                                        <select name="agence" id="agence" class="form-select" style="{{ $structureRattachement->type_structure == 'agence_execution' ? '' : 'display: none;' }}">
                                            <option value="">Selectionner l'agence</option>
                                            @foreach($agences as $agence)
                                            <option value="{{ $agence->code_agence_execution }}" {{ $structureRattachement->code_structure == $agence->code_agence_execution ? 'selected' : '' }}>{{ $agence->nom_agence }}</option>
                                            @endforeach
                                        </select>

                                        <select name="ministere" id="ministere" class="form-select" style="{{ $structureRattachement->type_structure == 'ministere' ? '' : 'display: none;' }}">
                                            <option value="">Selectionner le ministère</option>
                                            @foreach($ministeres as $ministere)
                                            <option value="{{ $ministere->code }}" {{ $structureRattachement->code_structure == $ministere->code ? 'selected' : '' }}>{{ $ministere->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @else
                                    <div class="form-group">
                                        <label for="structure_ratache">Structure :</label>
                                        <label for="bai">B :</label>
                                        <input type="radio" value="bai" name="structure" id="bai" onclick="showSelect('bailleur')" style="margin-right: 15px;">
                                        <label for="age">A :</label>
                                        <input type="radio" name="structure" value="age" id="age" onclick="showSelect('agence')" style="margin-right: 15px;">
                                        <label for="min">M :</label>
                                        <input type="radio" name="structure" value="min" id="min" onclick="showSelect('ministere')">

                                        <select name="bailleur" id="bailleur" class="form-select" style="display: block;">
                                            <option value="">Selectionner le bailleur</option>
                                            @foreach($bailleurs as $bailleur)
                                            <option value="{{ $bailleur->code_bailleur }}" >{{ $bailleur->libelle_long }}</option>
                                            @endforeach
                                        </select>

                                        <select name="agence" id="agence" class="form-select" style="display: none;">
                                            <option value="">Selectionner l'agence</option>
                                            @foreach($agences as $agence)
                                            <option value="{{ $agence->code_agence_execution }}" >{{ $agence->nom_agence }}</option>
                                            @endforeach
                                        </select>

                                        <select name="ministere" id="ministere" class="form-select" style="display: none;">
                                            <option value="">Selectionner le ministère</option>
                                            @foreach($ministeres as $ministere)
                                            <option value="{{ $ministere->code }}" >{{ $ministere->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row">

                                <div class="col">
                                    <div class="form-group">
                                        <label for="fonction">Fonction :</label>
                                        <select name="fonction" id="fonction" class="form-select" required>
                                            <option value="">Selectionner une fonction</option>
                                            @foreach($fonctions as $fonction)
                                            <option value="{{ $fonction->code }}" {{ optional($personne->latestFonction)->code_fonction == $fonction->code ? 'selected' : '' }}>
                                                {{ $fonction->libelle_fonction }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="niveau_acces_id">Champ d'exercice :</label>
                                        <select name="niveau_acces_id" id="niveau_acces_id" class="form-select" required>
                                            <option value="">--- ---</option>
                                            @foreach($niveauxAcces as $niveauAcces)
                                            <option value="{{ $niveauAcces->id }}">{{ $niveauAcces->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="niveau_acces_id" id="niveau_acces_id_label">Lieu d'exercice :</label>
                                        <select name="reg" id="reg" class="form-select" style="display: none;">
                                            <option value="">--- ---</option>
                                            @foreach($regions as $region)
                                            <option value="{{ $region->code }}" {{ optional(optional($personne->latestRegion)->region)->code == $region->code ? 'selected' : '' }}>{{ $region->libelle }}</option>

                                            @endforeach
                                        </select>
                                        <select name="dis" id="dis" class="form-select" style="display: none;">
                                            <option value="">--- ---</option>
                                            @foreach($districts as $district)
                                            <option value="{{ $district->code }}" {{ optional(optional($personne->latestRegion)->district)->code == $district->code ? 'selected' : '' }}>{{ $district->libelle }}</option>
                                            @endforeach
                                        </select>
                                        <select name="dep" id="dep" class="form-select" style="display: none;">
                                            <option value="">--- ---</option>
                                            @foreach($departements as $dep)
                                            <option value="{{ $dep->code }}" {{ optional(optional($personne->latestRegion)->departement)->code == $dep->code ? 'selected' : '' }}>{{ $dep->libelle }}</option>
                                            @endforeach
                                        </select>
                                        <select name="na" id="na" class="form-select" style="display: none;">
                                            <option value="">--- ---</option>
                                            @foreach($pays as $ppay)
                                            <option value="{{ $ppay->id }}" {{ optional(optional($personne->latestRegion)->pays)->id == $ppay->id ? 'selected' : '' }}>{{ $ppay->nom_fr_fr }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4">
                                    <label for="email-id-column" class="form-label">Email</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="email" id="email" class="form-control" name="email" value="{{ $personne->email }}" placeholder="Email" />
                                        <div class="form-control-icon">
                                            <i class="bi bi-envelope"></i>
                                        </div>
                                    </div>
                                    <div id="email-error" class="invalid-feedback"></div>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label for="city-column" class="form-label">Téléphone</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="tel" class="form-control" required value="{{ $personne->telephone }}" placeholder="Téléphone" name="tel" />
                                        <div class="form-control-icon">
                                            <i class="bi bi-telephone"></i>
                                        </div>
                                    </div>
                                    @error('tel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label for="country-floating" class="form-label">Adresse</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="adresse" class="form-control" name="adresse" value="{{ $personne->addresse }}" placeholder="Adresse" />
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
                                <div class="col-sm-4">
                                    <label for="photo" class="form-label">Photo</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="file" accept=".jpeg, .jpg, .png" id="photo" class="form-control" name="photo" />
                                        <div class="form-control-icon">
                                            <i class="bi bi-image-fill"></i>
                                        </div>
                                    </div>
                                    @error('tel')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label for="photo" class="form-label">Photo actuelle</label>
                                    <div class="form-group">
                                        @if ($personne->photo)
                                        <img style="width: 40px; height: 40px; border-radius: 50px;" src="{{ asset("users/".$personne->photo) }}" alt="Photo">
                                        @else
                                        <img style="width: 40px; height: 40px; border-radius: 50px;" src="{{ asset("users/user.png") }}" alt="Photo">
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 d-flex justify-content-end">
                                    {{-- <button type="reset" class="btn btn-light-secondary me-1 mb-1">
                                        Annuler
                                    </button> --}}
                                    <button type="submit" class="btn btn-primary me-1 mb-1">
                                        Valider
                                    </button>
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
    document.getElementById('email').addEventListener('keyup', function() {
        var email = this.value;

        // Effectuer la requête AJAX
        $.ajax({
            url: '/check-email-personne'
            , method: 'get'
            , data: {
                email: email
            }
            , success: function(response) {
                if (response.exists) {
                    document.getElementById('email-error').innerText = 'Cet eamil est déjà utilisé par un autre utilisateur.';
                    document.getElementById('email').classList.add('is-invalid');
                } else {
                    document.getElementById('email-error').innerText = '';
                    document.getElementById('email').classList.remove('is-invalid');
                }
            }
        });
    });

    $(document).ready(function() {

        if ('{{ $personne -> bailleur}}') {
            $("#bai").prop("checked", true);
            showSelect('bailleur');
        }

        if ('{{ $personne -> agence}}') {
            $("#age").prop("checked", true);
            showSelect('agence');
        }

        if ('{{ $personne -> ministere}}') {
            $("#min").prop("checked", true);
            showSelect('ministere');
        }

        @if($personne -> latestRegion)
            if ('{{ $personne->latestRegion->region }}') {
                showSelect_r('re');
            }
            if ('{{ $personne->latestRegion->pays }}') {
                showSelect_r('na');
            }
            if ('{{ $personne->latestRegion->district }}') {
                showSelect_r('di');
            }
            if ('{{ $personne->latestRegion->departement }}') {
                showSelect_r('de');
            }
        @endif

        $('#domaine').on('change', function() {
            updateSousDomaine($(this));
        });
        $('#bailleur').on('change', function () {
            showSelect_r('na');
            $("#niveau_acces_id").prop("disabled", true);
            $('#na').val(110);
        });
        $('#agence').on('change', function () {
            showSelect_r('na');
            $("#niveau_acces_id").prop("disabled", true);
            $('#na').val(110);
        });
        $('#ministere').on('change', function () {
            $("#niveau_acces_id").prop("disabled", false);
        });


        $('#personne').on('change', function() {
            updateEmail($(this));
        });

        $('#niveau_acces_id').on('change', function() {
            showSelect_r($(this).val());
        });
        $('#niveau_acces_id').trigger('change');
        var sous_dom = $('#sous_domaine').filterMultiSelect({

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

</script>
@endsection
