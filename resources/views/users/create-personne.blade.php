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
                    @endif

                </div>
                <div class="card-content">
                    <div class="card-body">
                        <form class="form" id="personnelForm" method="POST" enctype="multipart/form-data" action="{{ route('personnel.store') }}">
                            @csrf
                            <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                            <div class="row">

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
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="structure_ratache">Structure :</label>
                                            <label for="bai">B :</label>
                                            <input type="radio" value="bai" name="structure" id="bai" checked="true" onclick="showSelect('bailleur')" style="margin-right: 15px;">
                                            <label for="age">A :</label>
                                            <input type="radio" name="structure" value="age" id="age" onclick="showSelect('agence')" style="margin-right: 15px;">
                                            <label for="min">M :</label>
                                            <input type="radio" name="structure" value="min" id="min" onclick="showSelect('ministere')">

                                            <select name="bailleur" id="bailleur" class="form-select" onclick="filterOptions('bailleurss')"  style="display: none;">
                                                <option value="">Selectionner le bailleur</option>
                                                @foreach($bailleurs as $bailleur)
                                                <option value="{{ $bailleur->code_bailleur }}">{{ $bailleur->libelle_long }}</option>
                                                @endforeach
                                            </select>

                                            <select name="agence" id="agence" class="form-select" onclick="filterOptions('ministere')" style="display: none;">
                                                <option value="">Selectionner l'agence</option>
                                                @foreach($agences as $agence)
                                                <option value="{{ $agence->code_agence_execution }}">{{ $agence->nom_agence }}</option>
                                                @endforeach
                                            </select>

                                            <select name="ministere" id="ministere" class="form-select" onclick="filterOptions('agence_execution')" style="display: none;">
                                                <option value="">Selectionner le ministère</option>
                                                @foreach($ministeres as $ministere)
                                                <option value="{{ $ministere->code }}">{{ $ministere->libelle }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                            </div>
                            <div class="row">
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
                                            {{-- <option value="">--- ---</option> --}}
                                            @foreach($niveauxAcces as $niveauAcces)
                                            <option value="{{ $niveauAcces->id }}">{{ $niveauAcces->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="niveau_acces_id" id="niveau_acces_id_label">Région :</label>
                                        <select name="reg" id="reg" class="form-select" style="display: none;">
                                            <option value="">--- ---</option>
                                            @foreach($regions as $region)
                                            <option value="{{ $region->code }}">{{ $region->libelle }}</option>
                                            @endforeach
                                        </select>
                                        <select name="dis" id="dis" class="form-select" style="display: none;">
                                            <option value="">--- ---</option>
                                            @foreach($districts as $district)
                                            <option value="{{ $district->code }}">{{ $district->libelle }}</option>
                                            @endforeach
                                        </select>
                                        <select name="dep" id="dep" class="form-select" style="display: none;">
                                            <option value="">--- ---</option>
                                            @foreach($departements as $dep)
                                            <option value="{{ $dep->code }}">{{ $dep->libelle }}</option>
                                            @endforeach
                                        </select>
                                        <select name="na" id="na" class="form-select" style="display: none;">
                                            <option value="">--- ---</option>
                                            @foreach($pays as $ppay)
                                            <option value="{{ $ppay->id }}">{{ $ppay->nom_fr_fr }}</option>
                                            @endforeach
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
                                    <button type="submit" id="soumettre_personnel" class="btn btn-primary me-1 mb-1">
                                        Enregistrer
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



        // $('#personnelFormn').on('submit', function(event) {
        //     event.preventDefault();
        //     // var data = $('#personnelForm').serialize();
        //     // var expertises = sous_dom.getSelectedOptionsAsJson(); // Correction ici

        //     // Récupérer les données du formulaire
        //     var formData = $('#personnelForm').serializeArray();


        //     // Créer un objet JavaScript avec les données
        //     var requestData = {
        //         'data': formData
        //     };

        //     // Convertir l'objet JavaScript en chaîne JSON
        //     var jsonData = JSON.stringify(requestData);
        //     console.log(jsonData);
        //     $.ajax({
        //         type: 'post'
        //         , url: "{{ route('personnel.store') }}"
        //         , contentType: 'application/json', // Indiquer que vous envoyez du JSON
        //         data: jsonData
        //         , headers: {
        //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        //         }
        //         , beforeSend: function() {
        //             $('#create_new').html('....Please wait');
        //         }
        //         , success: function(response) {
        //             alert(response.success);
        //         }
        //         , complete: function(response) {
        //             $('#create_new').html('Create New');
        //         }
        //     });
        // });

        $("#bai").prop("checked", true);
        showSelect('bailleur');

        $('#personne').on('change', function() {
            updateEmail($(this));
        });
        $('#niveau_acces_id').on('change', function() {
            showSelect_r($(this).val());
            $('#na').val(110);
            $("#na").prop("disabled", true);
        });
        $('#niveau_acces_id').trigger('change');

        $('#bailleur').on('change', function () {
            showSelect_r('na');
            $("#niveau_acces_id").prop("disabled", true);
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

</script>
@endsection
