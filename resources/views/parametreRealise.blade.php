@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('assets/compiled/css/projet.css')}}">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<style>
    .inline {
        display: flex;
        align-items: flex-start;
        /* Aligner les éléments sur le côté gauche */
        margin-bottom: 10px;
        /* Espacement entre les champs */

    }

    .inline2 {
        display: flex;
        align-items: flex-start;
        /* Aligner les éléments sur le côté gauche */
        margin-top: 48px;
        /* Espacement entre les champs */

    }

    .gauche3,
    .droit3 {
        margin-left: 8px;
        width: 100%;
    }

    /* Style des étiquettes */
    .inline label {
        margin-bottom: 5px;
        /* Espacement entre les étiquettes */
    }

</style>
@section('content')

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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Réalisation de projet</a></li>

                            <li class="breadcrumb-item active" aria-current="page">Paramètre de réalisation</li>

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
                    <h4 class="card-title">Caractéristiques des infrastructures</h4>

                </div>
                <div class="card-content">
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{session('success')}}
                            </div>
                        @elseif (session('error'))
                            <div class="alert alert-danger">
                                {{session('error')}}
                            </div>
                        @endif
                        <ul class="nav nav-tabs" id="myTabs">
                            <li class="nav-item">
                                <a class="nav-link active" id="caracteristiques-tab" data-toggle="tab" href="#caracteristiques">Caractéristiques</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="dates-effectives-tab" data-toggle="tab" href="#dates-effectives">Dates Effectives</a>
                            </li>
                        </ul>
                        <div class="tab-content mt-2">
                            <div class="tab-pane fade show active" id="caracteristiques">
                                <form method="POST" action="{{ route('caracteristique.store') }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                                    
                                    <div id="caracteristique-container">
                                        <!-- Formulaire principal -->
                                        <div id="infrastructureForm">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Famille d'Infrastructure *</label>
                                                    <select class="form-control" id="FamilleInfrastruc">
                                                        <option value="">Sélectionnez </option>
                                                        <!-- dynamiquement rempli -->
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Nom de l'infrastructure *</label>
                                                    <input type="text" class="form-control" id="infrastructureName">
                                                </div>
                                            </div>

                                            <!-- Ajout de caractéristiques -->
                                            <div class="row mt-3">
                                                <div class="col-md-3">
                                                    <label>Type de caractéristique</label>
                                                    <select class="form-control" id="tyCaract">
                                                        <option value="">Sélectionner le type </option>
                                                        @foreach ($TypeCaracteristiques as $TypeCaracteristique)
                                                            <option value="{{ $TypeCaracteristique->idTypeCaracteristique }}">{{ $TypeCaracteristique->libelleTypeCaracteristique }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Caractéristique</label>
                                                    <select class="form-control" id="caract">
                                                        <option value="">Sélectionner la caractéristique</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Unité</label>
                                                    <select class="form-control" id="unitCaract">
                                                        <option value="">Sélectionner l'unité</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label>Valeur</label>
                                                    <input type="text" class="form-control" id="caractValue">
                                                </div>
                                            </div>
                                            <div class="text-end mt-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="addCaractToInfra">+ Ajouter caractéristique</button>
                                            </div>

                                            <!-- Liste temporaire des caractéristiques sous forme de tableau -->
                                            <div class="mt-3">
                                                <table class="table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Type de Caractéristique</th>
                                                            <th>Caractéristique</th>
                                                            <th>Unité</th>
                                                            <th>Valeur</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="caractListTable">
                                                        <!-- Les lignes seront ajoutées dynamiquement ici -->
                                                    </tbody>
                                                </table>
                                            </div>


                                            <div class="text-end mt-3">
                                                <button type="button" class="btn btn-secondary" id="addInfrastructureBtn">Ajouter l'infrastructure</button>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Liste finale des infrastructures -->
                                        <div class="row">
                                            <div class="col">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Nom</th>
                                                            <th>Famille</th>
                                                            <th>Caractéristiques</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tableInfrastructures">
                                                        <!-- Dynamically added rows -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Enregistrer les caractéristiques</button>
                                    </div>
                                </form>
                            </div>


                        </div>
                        <div class="tab-pane fade" id="dates-effectives">
                            <form method="POST" action="{{ route('enregistrer-dates-effectives') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="ecran_id" value="{{ $ecran->id }}">
                                <input type="hidden" id="code_projet2" name="code_projet2">

                                <div class="row mt-3">
                                    <div class="col-4">
                                        <label>Date effective de démarrage:</label>
                                        <input type="date" name="date_debut" class="form-control"
                                            value="{{ isset($dateEnregistree) ? \Carbon\Carbon::parse($dateEnregistree)->format('Y-m-d') : '' }}">
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col">
                                        <label>Commentaire:</label>
                                        <textarea name="commentaire" class="form-control" rows="3"></textarea>
                                    </div>
                                </div>

                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </div>
                            </form>
                        </div>

                </div>
            </div>
        </div>
    </div>

</section>
<div class="modal fade" id="doubleFormModal" tabindex="-1" role="dialog" aria-labelledby="doubleFormModalLabel" aria-hidden="true" style="background-color: #DBECF8;">
    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content" style="background-color: white;">
            <div class="modal-header">

                <h5 class="modal-title" id="doubleFormModalLabel">Niveau d'avancement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
                <div class="card-content" style="background-color: #EAF2F8;" >
                    <div class="modal-body" style="background-color: #EAF2F8;">

                        <div class="tab-content" id="myTabsContent" style="background-color: #EAF2F8;">
                            <!-- Premier formulaire - État d'avancement -->
                            <div class="tab-pane fade show active" id="etatAvancement" role="tabpanel" aria-labelledby="etatAvancement-tab" style="background-color: #EAF2F8;">
                                <form id="etatAvancementForm" method="POST" action="{{ route('enregistrer.niveauAvancement') }}" data-parsley-validate>
                                    @csrf
                                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>

                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col">
                                                <label for="code_projet">Code projet:</label>
                                                <input type="text" class="form-control" id="code_projet_Modal" name="code_projet_Modal" readonly>
                                            </div>
                                            <div class="col">
                                                <label for="code_projet">N° Ordre:</label>
                                                <input type="text" class="form-control" id="ordre_Modal" name="ordre_Modal" readonly>
                                            </div>
                                            <div class="col">
                                                <label for="date_realisation">Date de réalisation:</label>
                                                <input type="date" class="form-control" id="date_realisation_Modal" name="date_realisation_Modal">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col">
                                                <label for="quantite_reel">Quantité Prévue:</label>
                                                <input type="text" readonly class="form-control" name="quantite_provisionnel_Modal" id="quantite_provisionnel_Modal">
                                            </div>
                                            <div class="col">
                                                <label for="quantite_reel">Quantité réelle:</label>
                                                <input type="text" class="form-control" id="quantite_reel_Modal" name="quantite_reel_Modal">

                                            </div>
                                            <div class="col">
                                                <label for="pourcentage">Pourcentage:</label>
                                                <input type="text" class="form-control" id="pourcentage_Modal" name="pourcentage_Modal">
                                            </div>

                                            <div class="form-group">
                                                <label for="commentaire">Commentaire:</label>
                                                <textarea class="form-control" id="commentaire_Niveau_Modal" name="commentaire_Niveau_Modal" rows="3"></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Enregistrer État d'avancement</button>
                                </form>
                            </div>



                        </div>
                    </div>
                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                </div>
        </div>
    </div>
</div>
<script>
    
    document.getElementById('tyCaract').addEventListener('change', function() {
        let idType = this.value;

        // Si rien n’est sélectionné, vide simplement l'autre select
        if (!idType) {
            document.getElementById('tyCaract').innerHTML = '<option value=""></option>';
            return;
        }

        fetch('{{ url("/") }}/get-caracteristiques/' + idType)
            .then(response => response.json())
            .then(data => {
                let caractSelect = document.getElementById('caract');
                caractSelect.innerHTML = '<option value=""></option>';

                data.forEach(function(caract) {
                    let option = document.createElement('option');
                    option.value = caract.idCaracteristique;
                    option.text = caract.libelleCaracteristique;
                    caractSelect.appendChild(option);
                });
            });
    });

    const addInfrastructureBtn = document.getElementById('addInfrastructureBtn');
    if(addInfrastructureBtn){
        addInfrastructureBtn.addEventListener('click', function () {
            var familleText = $("#FamilleInfrastruc option:selected").text();
            var familleCode = $("#FamilleInfrastruc option:selected").val();
            var infraName = $("#infrastructureName").val();
            
            var caracteristiques = [];

            // Parcourir chaque ligne du tableau temporaire
            $("#caractListTable tr").each(function () {
                var typeCarac = $(this).find("td:eq(0)").text();
                var libelleCarac = $(this).find("td:eq(1)").text();
                var uniteCarac = $(this).find("td:eq(2)").text();
                var valeurCarac = $(this).find("td:eq(3)").text();

                caracteristiques.push({
                    type: typeCarac,
                    libelle: libelleCarac,
                    unite: uniteCarac,
                    valeur: valeurCarac
                });
            });

            if (infraName && familleCode && caracteristiques.length > 0) {
                // Construction de l'affichage des caractéristiques en UL
                var caracHTML = '<ul>';
                caracteristiques.forEach(function(carac) {
                    caracHTML += `<li>${carac.type} - ${carac.libelle} (${carac.unite}): ${carac.valeur}</li>`;
                });
                caracHTML += '</ul>';

                var newRow = `
                    <tr data-famille-code="${familleCode}">
                        <td>${infraName}</td>
                        <td>${familleText}</td>
                        <td>${caracHTML}</td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-infra">Supprimer</button></td>
                    </tr>

                `;

                // Ajouter l'infrastructure au tableau final
                $('#tableInfrastructures').append(newRow);

                // Nettoyer les champs
                $("#FamilleInfrastruc").val('');
                $("#infrastructureName").val('');
                $("#caractListTable").empty();
            } else {
                alert("Veuillez remplir tous les champs et ajouter au moins une caractéristique.");
            }
        });
    }
    // Gestion du bouton Supprimer une infrastructure
    $(document).on('click', '.remove-infra', function() {
        $(this).closest('tr').remove();
    });

    $(document).ready(function() {
        $('form').on('submit', function(e) {
            var infrastructures = [];

            $('#tableInfrastructures tr').each(function() {
                var infraName = $(this).find('td:eq(0)').text();
                var familleName = $(this).find('td:eq(1)').text();
                var familleCode = $(this).data('famille-code');


                var caracteristiques = [];
                $(this).find('ul li').each(function() {
                    var parts = $(this).text().split(':');
                    var libelleUnite = parts[0].split(' - ');
                    caracteristiques.push({
                        type: libelleUnite[0],
                        libelle: libelleUnite[1].split('(')[0].trim(),
                        unite: libelleUnite[1].split('(')[1].replace(')', ''),
                        valeur: parts[1].trim(),
                        id_caracteristique: null, // ID à envoyer dans ton JS si tu veux (par exemple caché dans une data-attribute)
                        id_unite: null // Pareil ici
                    });
                });

                infrastructures.push({
                    libelle: infraName,
                    famille: familleName,
                    famille_code: familleCode,
                    caracteristiques: caracteristiques
                });
            });

            var infrastructuresInput = $('<input>')
                .attr('type', 'hidden')
                .attr('name', 'infrastructures_json')
                .val(JSON.stringify(infrastructures));

            $(this).append(infrastructuresInput);
        });
    });

    document.getElementById('caract').addEventListener('change', function () {
        let idCaracteristique = this.value;

        fetch('{{ url("/") }}/get-unites/' + idCaracteristique)
            .then(response => response.json())
            .then(data => {
                console.log('Unites reçues:', data);

                let selectUnite = document.getElementById('unitCaract');
                selectUnite.innerHTML = '<option value="">Unité mésure</option>';

                data.forEach(function (unite) {
                    let option = document.createElement('option');
                    option.value = unite.idUnite;
                    option.text = unite.libelleUnite + (unite.symbole ? ' (' + unite.symbole + ')' : '');
                    selectUnite.appendChild(option);
                });
            });
    });
    document.getElementById('addCaractToInfra').addEventListener('click', function () {
        var typeCaractText = $("#tyCaract option:selected").text();
        var caractText = $("#caract option:selected").text();
        var unitText = $("#unitCaract option:selected").text();
        var value = $("#caractValue").val();

        if (typeCaractText && caractText && unitText && value) {
            var newRow = `
               <tr data-id-caracteristique="${$('#caract').val()}" data-id-unite="${$('#unitCaract').val()}">
                    <td>${typeCaractText}</td>
                    <td>${caractText}</td>
                    <td>${unitText}</td>
                    <td>${value}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-caract">Supprimer</button></td>
                </tr>

            `;
            $('#caractListTable').append(newRow);

            // Nettoyer les champs après ajout
            $("#tyCaract").val('');
            $("#caract").empty().append('<option value="">Sélectionner la caractéristique</option>');
            $("#unitCaract").empty().append('<option value="">Sélectionner l\'unité</option>');
            $("#caractValue").val('');
        } else {
            alert("Veuillez remplir tous les champs avant d'ajouter.");
        }
    });

    // Gestion du bouton Supprimer
    $(document).on('click', '.remove-caract', function() {
        $(this).closest('tr').remove();
    });

</script>
<script>
       // Utiliser la classe open-niveau-avancement-modal
       $(document).on("click", ".open-niveau-avancement-modal", function() {
            // Récupérer les valeurs du modal

            var numOrdre = $("#ordre_Modal").val();
            var codeProjet = $("#code_projet_input").val();
            console.log(numOrdre);
            console.log(codeProjet);

            // Effectuer la requête Ajax pour obtenir les données associées au code projet et à l'ordre
            $.ajax({
                url: '{{ route("get.donnees.formulaire")}}',
                type: 'GET',
                data: { code_projet_Modal: codeProjet, ordre_Modal: numOrdre },
                success: function(response) {
                    if (response.result && response.result.length > 0) {
                        var data = response.result[0];
                        if (data.Quantite !== undefined) {
                            $('#quantite_provisionnel_Modal').val(data.Quantite);
                            // Mettez à jour les autres champs au besoin
                        } else {
                            console.error('La propriété Quantite est indéfinie dans la réponse.');
                        }
                    } else {
                        console.error('Réponse vide ou inattendue.');
                    }
                },
                error: function(error) {
                    console.error('Erreur lors de la récupération des données pour le formulaire : ', error);
                }
            });

            // Afficher les modaux
            $('#doubleFormModal').modal('show');
            $('#niveauAvancementModal').modal('show');
        });
        const niveauAvancementBtn = document.getElementById('niveauAvancementBtn');
        if (niveauAvancementBtn) {
            niveauAvancementBtn.addEventListener('click', function () {
                $('#doubleFormModal').modal('show');
            });
        };
    $(document).ready(function() {
        $(".btn-navigate-form-step").click(function() {
            var stepNumber = $(this).attr("step_number");
            $(".form-step").addClass("d-none");
            $("#step-" + stepNumber).removeClass("d-none");
        });
    });
$(document).ready(function () {
    // Extraire les paramètres de l'URL
    var urlParams = new URLSearchParams(window.location.search);
    var codeProjet = urlParams.get('codeProjet');
    var codeActionMenerProjet = urlParams.get('codeActionMenerProjet')

    // Remplir les champs du formulaire avec les valeurs extraites
    $("#code_projet").val(codeProjet);
    $("#code_projet2").val(codeProjet);
    $("#code_action_mener_projet").val(codeActionMenerProjet);
});


$(document).ready(function () {
    // Extraire les paramètres de l'URL
    var urlParams = new URLSearchParams(window.location.search);
    var codeProjet = urlParams.get('codeProjet');
    var codeActionMenerProjet = urlParams.get('codeActionMenerProjet')

    // Remplir les champs du formulaire avec les valeurs extraites
    $("#code_projet").val(codeProjet);
    $("#code_action_mener_projet").val(codeActionMenerProjet);
    $("#code_projet_input").val(codeProjet);
    $("#code_projet_Modal").val(codeProjet);

    $.ajax({
        url: '{{ url("/") }}/getNumeroOrdre', // Remplacez cela par la route réelle dans votre application
        type: 'GET',
        data: {
            codeProjet: codeProjet,
            codeActionMenerProjet: codeActionMenerProjet
        },
        success: function (data) {
            // Remplir le champ d'ordre avec la valeur obtenue
            $("#ordre").val(data.numeroOrdre);
            $("#ordre_Modal").val(data.numeroOrdre);
            $("#infrastructure").val(data.libelleInfrastructure);
            $("#infrastructurecode").val(data.codeInfrastructure);
            $("#Famillecode").val(data.codeFamilleInfrastructure);
            $("#FamilleInfrastructure").val(data.libelleFamilleInfrastructure);
        },
        error: function () {
            console.log('Erreur lors de la récupération du numéro d\'ordre.');
        }
    });
});

    $(document).ready(function () {
        $("#infrastructure").on('input', function () {
            var infrastructureCode = $("#infrastructurecode").val();
            var infrastructureInput = $(this).val();

            // Effectuer une requête AJAX pour récupérer la famille d'infrastructure
            $.ajax({
                url: '{{ url("/") }}/getFamilleInfrastructure', // Remplacez cela par la route réelle dans votre application
                type: 'GET',
                data: {
                    infrastructureCode: infrastructureCode,
                    infrastructureInput: infrastructureInput
                },
                success: function (data) {
                    // Mettre à jour le champ de la famille d'infrastructure
                    $("#familleInfrastructure").val(data.familleInfrastructure);
                },
                error: function () {
                    console.log('Erreur lors de la récupération de la famille d\'infrastructure.');
                }
            });
        });
    });
    $(document).ready(function() {
        var codeProjet = new URLSearchParams(window.location.search).get('codeProjet');

        if (codeProjet) {
            $.ajax({
                url: '{{ url("/") }}/get-familles-by-projet',
                type: 'GET',
                data: { codeProjet: codeProjet },
                success: function(response) {
                    if (response.familles && response.familles.length > 0) {
                        var select = $('#FamilleInfrastruc');
                        select.empty();
                        select.append('<option value="">Sélectionnez</option>');

                        response.familles.forEach(function(famille) {
                            select.append(`<option value="${famille.codeFamilleInfrastructure}">${famille.libelleFamille}</option>`);
                        });
                    } else {
                        console.log('Aucune famille trouvée.');
                    }
                },
                error: function() {
                    console.error('Erreur lors de la récupération des familles.');
                }
            });
        }
    });

    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        var codeProjet = urlParams.get('codeProjet');

        if (codeProjet) {
            $.ajax({
                url: '{{ url("/") }}/getInfrastructuresByProjet',
                type: 'GET',
                data: { codeProjet: codeProjet },
                success: function(response) {
                    if (response.infrastructures && response.infrastructures.length > 0) {
                        
                        // Nettoyer le tableau avant remplissage
                        $('#tableInfrastructures').empty();

                        response.infrastructures.forEach(function(infra) {
                            var caracHTML = '<ul>';
                            infra.caracteristiques.forEach(function(carac) {
                                caracHTML += `<li>${carac.type} - ${carac.libelle} (${carac.unite}): ${carac.valeur}</li>`;
                            });
                            caracHTML += '</ul>';

                            var newRow = `
                                <tr data-famille-code="${infra.famille_code}">
                                    <td>${infra.nom_infrastructure}</td>
                                    <td>${infra.famille}</td>
                                    <td>${caracHTML}</td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-infra">Supprimer</button></td>
                                </tr>
                            `;

                            $('#tableInfrastructures').append(newRow);
                        });
                    } else {
                        console.log('Aucune infrastructure trouvée pour ce projet.');
                    }
                },
                error: function() {
                    console.error('Erreur lors du chargement des infrastructures.');
                }
            });
        }
    });


    // Utilisez cette fonction pour charger les données du projet via Ajax
        /*function chargerDonneesProjet(codeProjet) {
            $.ajax({
                url: '/obtenir-donnees-projet',
                method: 'GET',
                data: { code_projet2: codeProjet },
                success: function (response) {
                    // Remplissez les champs du formulaire avec les données reçues
                    $('#date_debut').val(response.date_debut);
                    $('#date_fin').val(response.date_fin);
                    var formattedCout = number_format(response.coutEffective, 0, ' ', ' ');
                    $('#quantite').val(formattedCout);
                    $('#devise').val(response.devise);
                    $('#commentaire').val(response.commentaire);
                },
                error: function (error) {
                    console.log('Une erreur s\'est produite lors du chargement des données du projet.');
                    console.log(error);
                }
            });
        }*/

    /* Utilisez cette fonction pour déclencher le chargement des données lorsqu'un code de projet est disponible
    function chargerDonneesProjetSiCodeExiste() {
        var codeProjet = $('#code_projet2').val();
        if (codeProjet) {
            chargerDonneesProjet(codeProjet);
        }
    }*/

    // Appelez la fonction lors du chargement de la page ou lors d'un événement approprié
   /* $(document).ready(function () {
        chargerDonneesProjetSiCodeExiste();

        // Assurez-vous également de lier cette fonction à tout événement qui change le code du projet
        $('#code_projet2').on('change', function () {
            chargerDonneesProjetSiCodeExiste();
        });
    });*/

</script>
<!-- Ajoutez ce script à votre page HTML ou à votre fichier JavaScript externe -->
<script>



        ////////////////////FORMATAGE DE CHAMP NUMBER/////////////////
        function formatNumberInput(input) {
            // Supprimer tout sauf les chiffres et le séparateur décimal
            var sanitizedValue = input.value.replace(/[^0-9.]/g, '');

            // Séparer la partie entière et la partie décimale
            var parts = sanitizedValue.split(' ');
            var integerPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

            // Recréer la valeur avec le séparateur de milliers
            var formattedValue = integerPart;
            if (parts.length > 1) {
                formattedValue += ' ' + parts[1];
            }

            // Mettre à jour la valeur du champ
            input.value = formattedValue;
        }

    // Gérer l'événement de saisie pour le champ cout
    const quantiteS = document.getElementById('quantite');
        if (quantiteS) {
            quantiteS.addEventListener('input', function (event) {
                formatNumberInput(event.target);
            });
        };
    // Fonction de formatage du nombre avec espaces comme séparateurs de milliers
    function number_format(number, decimals, decPoint, thousandsSep) {
        number = parseFloat(number);
        decimals = decimals || 0;
        var fixed = number.toFixed(decimals);
        var parts = fixed.split('.');
        var intPart = parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1" + thousandsSep);
        var decPart = parts.length > 1 ? (decPoint + parts[1]) : '';
        return intPart + decPart;
    }

    ////////////////////////////////////////////////////
</script>

@endsection
