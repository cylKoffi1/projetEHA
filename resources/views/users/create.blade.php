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
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Utilisateurs </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/admin">Utilisateurs</a></li>

                            <li class="breadcrumb-item active" aria-current="page">Nouvel utilisateur</li>

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
                    <h4 class="card-title">Nouvel utilisateur</h4>
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
                        <form class="form" method="POST" id="create-user" enctype="multipart/form-data" action="{{ route('users.store') }}">
                            @csrf
                            <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <label for="fonction">Personne :</label>
                                        <select name="personne" id="personne" class="form-select" required>
                                            <option value="">--- ---</option>
                                            @foreach($personnes as $personne)
                                            <option value="{{ $personne->code_personnel }}">{{ $personne->nom }} {{ $personne->prenom }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col">
                                    <label class="form-label">Nom</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="nom" class="form-control" name="nom" placeholder="Nom" readonly />
                                        <div class="form-control-icon">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    </div>
                                    @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col">
                                    <label class="form-label">Prénom</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="prenom" class="form-control" name="prenom" placeholder="Prénom" readonly />
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
                                        <label for="structure_ratache">Structure:</label>
                                        <label for="bai">B:</label>
                                        <input type="radio" value="bai" name="structure" id="bai" selected="true" onclick="showSelect('bailleur')" style="margin-right: 5px;">
                                        <label for="age">A:</label>
                                        <input type="radio" name="structure" value="age" id="age" onclick="showSelect('agence')" style="margin-right: 5px;">
                                        <label for="min">M:</label>
                                        <input type="radio" name="structure" value="min" id="min" onclick="showSelect('ministere')">

                                        <select name="bailleur" id="bailleur" class="form-select" style="display: none;">
                                            <option value="">Selectionner le bailleur</option>
                                            @foreach($bailleurs as $bailleur)
                                            <option value="{{ $bailleur->code_bailleur }}">
                                                {{ $bailleur->libelle_long }}
                                            </option>
                                            @endforeach
                                        </select>

                                        <select name="agence" id="agence" class="form-select" style="display: none;">
                                            <option value="">Selectionner l'agence</option>
                                            @foreach($agences as $agence)
                                            <option value="{{ $agence->code_agence_execution }}">
                                                {{ $agence->nom_agence }}
                                            </option>
                                            @endforeach
                                        </select>

                                        <select name="ministere" id="ministere" class="form-select" style="display: none;">
                                            <option value="">Selectionner le ministère</option>
                                            @foreach($ministeres as $ministere)
                                            <option value="{{ $ministere->code }}">
                                                {{ $ministere->libelle }}
                                            </option>
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
                                            <option value="">Selectionner une fonction</option>
                                            @foreach($fonctions as $fonction)
                                            <option value="{{ $fonction->code }}">
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
                                    <label class="form-label">Email ( Requis )*</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="email" id="email" class="form-control" required name="email" placeholder="Email" />
                                        <div class="form-control-icon">
                                            <i class="bi bi-envelope"></i>
                                        </div>
                                    </div>
                                    <div id="email-error" class="invalid-feedback"></div>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col">
                                    <label class="form-label">Téléphone</label>
                                    <div class="input-group"> <!-- Utilisation de la classe input-group pour regrouper les éléments -->
                                        <span class="input-group-text" id="indicatifPays">+XX</span> <!-- Balise span pour afficher l'indicatif du pays -->
                                        <input type="text" id="tel" class="form-control" name="tel" placeholder="Téléphone" /> <!-- Champ de téléphone -->
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
                                        <input type="text" id="adresse" class="form-control" name="adresse" placeholder="Adresse" readonly />
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
                                <div class="col">
                                    <div class="form-group">
                                        <label for="group_user">Groupe utilisateur :</label>
                                        <select name="group_user" id="group_user" class="form-select" required>
                                            <option value="">--- ---</option>
                                            @foreach($groupe_utilisateur as $groupe)
                                            <option value="{{ $groupe->id }}">{{ $groupe->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sous_domaine">Sous domaines autorisés :</label>
                                        <select id="sous_domaine" name="sous_domaine" multiple>
                                            @foreach ($sous_domaines as $sd)
                                            <option value="{{ $sd->code }}">{{ $sd->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="form-group">
                                        <label for="domaine">Domaine:</label>
                                        <select id="domaine" class="form-control" name="domaine" multiple>

                                            @foreach ($domaines as $domaine)
                                            <option value="{{ $domaine->code }}">{{ $domaine->libelle }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col">
                                    <label>Nom utilisateur</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="text" id="username" name="username" class="form-control" placeholder="Nom utilisateur">
                                        <div class="form-control-icon">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div id="username-error" class="invalid-feedback"></div>
                                        @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col">
                                    <label>Mot de passe</label>
                                    <div class="form-group position-relative has-icon-left">
                                        <input type="password" id="password" name="password" class="form-control" placeholder="Mot de passe">
                                        <div class="form-control-icon">
                                            <i class="bi bi-lock"></i>
                                        </div>
                                        <div id="username-error" class="invalid-feedback"></div>
                                        @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 d-flex justify-content-end">
                                    {{-- <button type="reset" class="btn btn-light-secondary me-1 mb-1">
                                                        Annuler
                                                    </button> --}}
                                    @can("ajouter_ecran_".$ecran->id)
                                    <button type="submit" class="btn btn-primary me-1 mb-1">
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
    document.addEventListener("DOMContentLoaded", function() {
        var niveauAccesSelect = document.getElementById("niveau_acces_id");
        var paysSelect = document.getElementById("na");

        niveauAccesSelect.addEventListener("change", function() {
            if (niveauAccesSelect.value == "na") {
                paysSelect.value = "110"; // La valeur de code d'Ivoire
                paysSelect.disabled = true; // Désactiver la sélection
            } else {
                paysSelect.value = ""; // Réinitialiser la valeur du pays si une autre option est sélectionnée
                paysSelect.disabled = false; // Activer la sélection
            }
        });
    });
    $(document).ready(function() {
        // Sélection de l'élément avec l'ID 'na'
        var paysSelect = document.getElementById('na');
        console.log("L'élément avec l'ID 'na' :"+paysSelect);
        // Vérification si l'élément existe
        if (paysSelect) {
            // Écoute des changements de sélection dans le champ du pays
            paysSelect.addEventListener('change', function() {
                // Récupération de la valeur sélectionnée
                var selectedPaysId = this.value; // Utilisation de 'this' pour faire référence à l'élément actuel

                // Déterminez l'indicatif du pays sélectionné
                getIndicatif(selectedPaysId);
            });
        } else {
            console.error("L'élément avec l'ID 'na' n'existe pas.");
        }

        // Définir l'indicatif par défaut pour l'ID de pays 110
        var defaultIndicatif = getIndicatif(110);
        $('#indicatifPays').text(defaultIndicatif); // Mettre à jour le texte de l'indicatif du pays
    });

// Fonction pour obtenir l'indicatif du pays en fonction de son ID
function getIndicatif(paysId) {
    // Effectuer une requête AJAX vers la route qui récupère l'indicatif du pays
    $.ajax({
        url: '/getIndicatif/' + paysId,
        type: 'GET',
        success: function(response) {
            // Mettre à jour le texte de l'indicatif avec l'indicatif du pays récupéré
            $('#indicatifPays').text(response.indicatif);
        },
        error: function(xhr, status, error) {
            console.error(xhr.responseText);
            // Gérer l'erreur en conséquence
        }
    });
}

</script>

<script>
    $(document).ready(function() {


        var domaines = $('#domaine').filterMultiSelect({

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


        $("#bai").prop("checked", true);
        showSelect('bailleur');

        $('#domaine').on('change', function() {
            updateSousDomaine($(this));
        });

        $('#personne').on('change', function() {
            updateEmail($(this));
        });
        $('#fonction').on('change', function() {
            getGroupeUserByFonctionId($(this));
        })
        $('#niveau_acces_id').on('change', function() {
            showSelect_r($(this).val());
        });
        $('#niveau_acces_id').trigger('change');


        $('#bailleur').on('change', function() {
            showSelect_r('na');
            $("#niveau_acces_id").prop("disabled", true);
            $('#na').val(110);
        });
        $('#agence').on('change', function() {
            showSelect_r('na');
            $("#niveau_acces_id").prop("disabled", true);
            $('#na').val(110);
        });
        $('#ministere').on('change', function() {
            $("#niveau_acces_id").prop("disabled", false);
        });


        $('#create-user').on('submit', function(event) {
            event.preventDefault(); // Empêcher la soumission par défaut du formulaire

            // Créer un objet FormData à partir du formulaire
            var formData = new FormData(this);

            // Ajouter des données supplémentaires à FormData si nécessaire
            formData.append("sd", sous_dom.getSelectedOptionsAsJson());
            formData.append("domS", domaines.getSelectedOptionsAsJson());

            // Construire l'URL correcte pour la requête AJAX
            var url = '/admin/users/store';

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {

                    console.log(response.donnees);
                    showPopup(response.success);
                    // Rediriger l'utilisateur après une requête réussie
                    window.location.href = "/admin/users?ecran_id=12";
                },
                error: function(xhr, status, error) {
                    var err = JSON.parse(xhr.responseText);
                    console.log(err); // Afficher les détails de l'erreur côté serveur dans la console
                    showPopup('Une erreur est survenue !');
                }
            });
        });
    });

    $(document).ready(function() {
        // Masquez les champs de région et affichez uniquement la Côte d'Ivoire dans le champ "Pays"
        $('#reg, #dis, #dep').hide();
    });
    function updateEmail(selectElement) {
        var selectedPersonne = selectElement.val();

        $('#dep, #dis, #reg, #na').val('');
        showSelect_r("");
        if (selectedPersonne === 'na') { // Si "National" est sélectionné
            // Masquer tous les champs de région et de pays sauf le champ Pays
            $('#na').show();
            $('#reg, #dis, #dep').hide();

        } else {
            // Afficher les champs de région par défaut
            $('#na').hide();
            $('#reg').show();
        }
        $('niveau_acces_id').val('de');
        $('#dep').val('');
        $('niveau_acces_id').val('di');
        $('#dis').val('');
        $('niveau_acces_id').val('re');
        $('#reg').val('');
        $('niveau_acces_id').val('na');
        $('#na').val('');
        $.ajax({
            type: 'GET'
            , url: '/admin/get-personne-email/' + selectedPersonne
            , success: function(data) {

                var emailParts = data.email.split('@');
                var usernameLeftPart = emailParts.length > 0 ? emailParts[0] : '';

                console.log(data);
                $('#email').val(data.email);
                $('#tel').val(data.telephone);
                $('#adresse').val(data.addresse);
                $('#nom').val(data.nom);
                $('#prenom').val(data.prenom);
                $('#username').val(usernameLeftPart);

                if (data.latest_region ? true : false) {
                    if (data.latest_region.code_departement ? true : false) {
                        showSelect_r("de");
                        $('#dep').val(data.latest_region.code_departement);
                        $('niveau_acces_id').val('de');
                    }
                    if (data.latest_region.code_district ? true : false) {
                        showSelect_r("di");
                        $('niveau_acces_id').val('di');
                        $('#dis').val(data.latest_region.code_district);
                    }
                    if (data.latest_region.code_region ? true : false) {
                        showSelect_r("re");
                        $('niveau_acces_id').val('re');
                        $('#reg').val(data.latest_region.code_region);
                    }
                    if (data.latest_region.id_pays ? true : false) {
                        showSelect_r("na");
                        $('niveau_acces_id').val('na');
                        $('#na').val(data.latest_region.id_pays);
                    }
                }
                if (data.latest_fonction ? true : false) {
                    $('#fonction').val(data.latest_fonction.code_fonction);
                    $('#fonction').trigger('change');
                } else {
                    $('#fonction').val("");
                    $('#fonction').trigger('change');
                }
                // Afficher la structure
                var latestStructure = data.structure;
                if (latestStructure) {
                    var structureType = latestStructure.type_structure;
                    var structureCode = latestStructure.code_structure;

                    // Mettre à jour les champs en fonction du type de structure
                    if (structureType === 'bailleur') {
                        $('#bai').prop('checked', true);
                        $('#bailleur').val(structureCode);
                        showSelect('bailleur');
                    } else if (structureType === 'agence_execution') {
                        $('#age').prop('checked', true);
                        $('#agence').val(structureCode);
                        showSelect('agence');
                    } else if (structureType === 'ministere') {
                        $('#min').prop('checked', true);
                        $('#ministere').val(structureCode);
                        showSelect('ministere');
                    }
                }
            }
        });
    }

    function showSelect(selectId) {
        // Hide all selects
        document.getElementById('bailleur').style.display = 'none';
        document.getElementById('agence').style.display = 'none';
        document.getElementById('ministere').style.display = 'none';

        // Show the selected select
        document.getElementById(selectId).style.display = 'block';
    }

    function showSelect_r(selectId) {
        $("#niveau_acces_id").prop("disabled", false);
        console.log(selectId);
        if (selectId === "na") {
            document.getElementById("reg").style.display = "none";
            document.getElementById("dis").style.display = "none";
            document.getElementById("dep").style.display = "none";
            // Show the selected select
            document.getElementById("na").style.display = "block";
            document.getElementById("niveau_acces_id_label").innerHTML = "Pays";
            $("#niveau_acces_id").val("na");
            $('#na').val(110);
        }
        if (selectId === "di") {
            document.getElementById("reg").style.display = "none";
            document.getElementById("na").style.display = "none";
            document.getElementById("dep").style.display = "none";
            // Show the selected select
            document.getElementById("dis").style.display = "block";
            document.getElementById("niveau_acces_id_label").innerHTML = "District";
            $("#niveau_acces_id").val("di");
        }
        if (selectId === "re") {
            document.getElementById("dis").style.display = "none";
            document.getElementById("na").style.display = "none";
            document.getElementById("dep").style.display = "none";
            // Show the selected select
            document.getElementById("reg").style.display = "block";
            document.getElementById("niveau_acces_id_label").innerHTML = "Région";
            $("#niveau_acces_id").val("re");
        }
        if (selectId === "de") {
            document.getElementById("dis").style.display = "none";
            document.getElementById("na").style.display = "none";
            document.getElementById("reg").style.display = "none";
            // Show the selected select
            document.getElementById("dep").style.display = "block";
            document.getElementById("niveau_acces_id_label").innerHTML =
                "Departement";
            $("#niveau_acces_id").val("de");
        }
    }

    function updateSousDomaine(selectElement) {
        var selectedDomaine = selectElement.val();

        // Effectuez une requête AJAX pour obtenir les sous-domaines
        $.ajax({
            type: 'GET'
            , url: '/admin/get-sous_domaines/' + selectedDomaine
            , success: function(data) {
                console.log(data);
                var sousDomainesSelect = $('#sous_domaine'); // Correction: Utilisation de l'ID directement

                sousDomainesSelect.empty(); // Effacez les options précédentes

                // Ajoutez les options des sous-domaines récupérés
                $.each(data.sous_domaines, function(key, value) {
                    sousDomainesSelect.append($('<option>', {
                        value: key
                        , text: value
                    }));
                });

                sousDomainesSelect.trigger('change');
            }
        });
    }

    function getGroupeUserByFonctionId(selectElement) {
        var selectedFonction = selectElement.val();

        if (selectedFonction != null || selectedFonction != "") {
            // Effectuez une requête AJAX pour obtenir les sous-domaines
            $.ajax({
                type: "GET"
                , url: "/admin/get-groupes/" + selectedFonction
                , success: function(data) {
                    console.log(data);
                    var groupess = $("#group_user"); // Correction: Utilisation de l'ID directement

                    groupess.empty(); // Effacez les options précédentes

                    // Ajoutez les options des sous-domaines récupérés
                    $.each(data.groupes, function(key, value) {
                        groupess.append(
                            $("<option>", {
                                value: key
                                , text: value
                            , })
                        );
                    });

                    groupess.trigger("change");
                }
            , });

        }
    }

    //Votre code JavaScript
    document.getElementById('username').addEventListener('keyup', function() {
        var username = this.value;

        // Effectuer la requête AJAX
        $.ajax({
            url: '/check-username'
            , method: 'GET'
            , data: {
                username: username
            }
            , success: function(response) {
                if (response.exists) {
                    document.getElementById('username-error').innerText = 'Le nom d\'utilisateur est déjà pris.';
                    document.getElementById('username').classList.add('is-invalid');
                } else {
                    document.getElementById('username-error').innerText = '';
                    document.getElementById('username').classList.remove('is-invalid');
                }
            }
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

</script>
@endsection
