@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('assets/compiled/css/projet.css')}}">
<style>
    .form-control {
    display: block;
    width: 114%;
    height: calc(1.5em + .75rem + 2px);
    padding: .375rem .75rem;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: .25rem;
    transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}
    .table-container {
    overflow-x: auto;

    border-collapse: collapse;
    }

    .table-container table {
    width: 100%;
    border-collapse: collapse;
    }

    .table-container th,
    .table-container td {
    padding: 0.5rem;
    border: 1px solid #ccc;
    text-align: left;
    }

    .table-container th {
    background-color: #f2f2f2;
    color: #000;
    font-weight: bold;
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
                            <li class="breadcrumb-item"><a href="">Réalisation de projet</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Cloture de projet</li>

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
                    <h4 class="card-title">Forcer la cloture de projet </h4>
                   <!-- @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif-->

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
                        <form class="form" id="personnelForm" method="POST" enctype="multipart/form-data" action="{{ route('personnel.store') }}">
                            @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                            <div class="row">
                                <div class="col">
                                    <div class="form-group">
                                        <div class="row">
                                            <div class="col text-center"><h5>---------------Prévisionnelles---------------</h5></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-3" style="width: 28%;">
                                                <label for="code_projet">Code du projet</label>
                                                <select name="code_projet" id="code_projet" class="form-select col-35" onchange="checkProjectDetails()" oninput="updateCodeProjetValue()">
                                                    <option value=""></option>
                                                    @foreach ($statutProjetStatut as $statutProjetStatu)
                                                            <option value="{{$statutProjetStatu->CodeProjet }}">{{ $statutProjetStatu->CodeProjet}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-2" style="width: 15%;">
                                                <label for="date_debut">Début:</label>
                                                <input type="date" class="form-control" style="width: 109%;" id="date_debut" name="date_debut">
                                            </div>
                                            <div class="col-2" style="width: 15%;">
                                                <label for="date_fin">Fin :</label>
                                                <input type="date" id="date_fin" class="form-control" style="width: 109%;" name="date_fin" width="10">
                                            </div>
                                            <div class="col-2" style="width: 17%;">
                                                <label for="cout">Coût :</label>
                                                <input type="text" class="form-control" style=" text-align: right; float: right; justify-content: right;" id="cout" name="cout">
                                            </div>
                                            <div class="col-1" style="width: 9%;">
                                                <label for="date_fin">Devise</label>
                                                <input type="text" id="devise" class="form-control" tyle="width: 109%;" name="date_fin" width="10" readonly>
                                            </div>
                                            <div class="col-2"style="width: 15%;">
                                                <label for="statut" style="text-align: right; justify-content: right;">Statut</label>
                                                <input type="text" name="statut" id="statutInput" class="form-control col-8" readonly>
                                                <input type="hidden" name="code_statut" id="codeStatutInput" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </form>
                        <!-- Ajoutez cet élément pour afficher la valeur du code projet -->
                        <div id="afficher_code_projet"></div>
                        <div class="table-container">
                            <div class="table-container">
                                <table id="actionTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 69px;">N° ordre</th>
                                            <th style="width: 180px;">Action à mener</th>
                                            <th style="width: 69px;">Quantité</th>
                                            <th style="width: 69px;">Unité de mésure</th>
                                            <th style="width: 180px;">Infrastructure</th>
                                            <th style="width: 100px;">Bénéficiaire</th>
                                        </tr>
                                    </thead>

                                    <tbody id="beneficiaire-table-body">
                                        <!-- Les données du tableau seront insérées ici dynamiquement -->
                                    </tbody>
                                </table>
                            </div>

                        </div><br>
                        <div class="row">
                            <div class="col-3">
                                <label for="dateCloture">Date cloture</label>
                                <input type="date" class="form-control" name="dateCloture" id="dateCloture">
                            </div>
                            <div class="col">
                                <br>
                                <button type="button" class="btn btn-danger" onclick="cloturerProjet()">Clôturer</button>
                            </div>
                        </div>

                       <!-- Modal pour la confirmation de la clôture du projet -->
                        <div id="confirmationModal" class="modal fade" tabindex="-1" style="background-color: transparent;" role="dialog">
                            <div class="modal-dialog" role="document" style="background-color: white;">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirmation de clôture du projet</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p id="confirmationMessage">Êtes-vous sûr de vouloir clôturer ce projet ?</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                        <button type="button" class="btn btn-danger" id="confirmCloture">Confirmer</button>
                                    </div>
                                </div>
                            </div>
                        </div>




                    </div>
                        <div class="row">
                            <h4><a href="#" id="voir-liste-link">Voir la liste</a></h4>
                        </div>

                        <div class="modal fade" id="largeModal" style="background-color: #DBECF8;" tabindex="-1" role="dialog" aria-labelledby="basicModal" aria-hidden="true">
                            <form action="{{ route('enregistrer.beneficiaires') }}" method="post" data-parsley-validate>
                                @csrf
                                <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                                <div class="modal-dialog modal-lg" style="background-color: #fff;">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h4 class="modal-title" id="myModalLabel">Bénéficiaires</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="row">
                                                    <label for="structure_ratache">Bénéficiaire :</label>
                                                    <input type="hidden" name="CodeProjetBene" id="CodeProjetBene">
                                                    <input type="hidden" name="numOrdreBene" id="numOrdreBene">

                                                    <div class="col-2" style="width: 16%;">
                                                        <label for="age">Localité :</label>
                                                        <input type="radio" name="beneficiaire_type[]" value="localite" id="age" checked="true" onclick="afficheSelect('localite')" style="margin-right: 15px;">
                                                    </div>
                                                    <div class="col-2" style="width: 24%;">
                                                        <label for="sousp">Sous-préfecture :</label>
                                                        <input type="radio" name="beneficiaire_type[]" value="sous_prefecture1" id="sousp" onclick="afficheSelect('sous_prefecture1')" style="margin-right: 15px;">
                                                    </div>
                                                    <div class="col-2" style="width: 21%;">
                                                        <label for="min">Département :</label>
                                                        <input type="radio" name="beneficiaire_type[]" value="departement" id="dep" onclick="afficheSelect('departement2')" style="margin-right: 15px;">

                                                    </div>
                                                    <div class="col-2" style="width: 15%;">
                                                        <label for="min">Region :</label>
                                                        <input type="radio" name="beneficiaire_type[]" value="region" id="reg" onclick="afficheSelect('region2')" style="margin-right: 15px;">

                                                    </div>
                                                    <div class="col-2" style="width: 14%;">
                                                        <label for="dis">District :</label>
                                                        <input type="radio" name="beneficiaire_type[]" value="district" id="dis" onclick="afficheSelect('district1')">
                                                    </div>
                                                    <div class="col-2" style="width: 22%;">
                                                        <label for="min">Etablissement :</label>
                                                        <input type="radio" name="beneficiaire_type[]" value="etablissement" id="min" onclick="afficheSelect('etablissement')" style="margin-right: 15px;">

                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <select name="beneficiaire_code[]" id="localite" class="form-select" style="display: none;">
                                                        <option value="">Sélectionner la localité</option>
                                                        @foreach ($localite as $loca)
                                                        <option value="{{$loca->code}}">{{$loca->libelle}}</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="beneficiaire_code[]" id="sous_prefecture1" class="form-select" style="display: none;">
                                                        <option value="">Sélectionner la sous préfecture</option>
                                                        @foreach ($sous_prefecture as $sous)
                                                        <option value="{{$sous->code}}">{{$sous->libelle}}</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="beneficiaire_code[]" id="departement2" class="form-select" style="display: none;">
                                                        <option value="">Sélectionner le departement</option>
                                                        @foreach ($departements as $depart)
                                                        <option value="{{$depart->code}}">{{$depart->libelle}}</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="beneficiaire_code[]" id="region2" class="form-select" style="display: none;">
                                                        <option value="">Sélectionner le region</option>
                                                        @foreach ($regions as $regio)
                                                        <option value="{{$regio->code}}">{{$regio->libelle}}</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="beneficiaire_code[]" id="district1" class="form-select" style="display: none;">
                                                        <option value="">Sélectionner le district</option>
                                                        @foreach ($districts as $dis)
                                                        <option value="{{$dis->code}}">{{$dis->libelle}}</option>
                                                        @endforeach
                                                    </select>
                                                    <select name="beneficiaire_code[]" id="etablissement" class="form-select" style="display: none;">
                                                        <option value="">Sélectionner l'établissement</option>
                                                        @foreach ($etablissements as $etab)
                                                        <option value="{{$etab->code}}">{{$etab->nom_etablissement}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col">
                                                    <button type="button" class="btn btn-secondary" id="addBtn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16">
                                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"></path>
                                                        </svg>
                                                        Ajouter
                                                    </button>
                                                </div>
                                                <div class="col">
                                                    <button type="button" class="btn btn-danger" style="width: 121px" id="deleteBtn">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="16" fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                                            <path d="M1.5 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2V3h2a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1h2V2zM1 5v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V5H1zm3 0v9a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V5H4z"></path>
                                                        </svg>
                                                        Supprimer
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="row" style="align-items: center;">
                                                <div class="col">
                                                    <div class="table-container">
                                                        <table id="beneficiaireTable">
                                                            <thead>
                                                                <tr>
                                                                    <th class="etablissement"><input type="checkbox"></th>
                                                                    <th class="etablissement">Code</th>
                                                                    <th class="etablissement">Libellé</th>
                                                                    <th class="etablissement">Type</th>

                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
                                            <button type="submit" class="btn btn-danger" data-dismiss="modal">Supprimer</button>
                                            <button type="submit" class="btn btn-primary" id="enregistrerBeneficiaire">Enregistrer</button>
                                        </div>
                                    </div>

                                </div>
                            </form>

                            <script>

                                //afficher les bénéficiaires (localité, etablissement et districts)
                                function afficheSelect(selectId) {
                                    // Hide all selects
                                    $('#localite,#sous_prefecture1 ,  #district1,  #etablissement, #departement2, #region2').hide();

                                    // Show the selected select
                                    $('#' + selectId).show();
                                }
                                $(document).ready(function() {
                                    // Set the 'bai' radio button as checked initially
                                    $("#age").prop("checked", true);
                                    // Call the afficheSelect function with 'district' as the argument
                                    afficheSelect('localite');
                                });
                                //Bouton ajouter des bénéficiaires
                                $(document).ready(function() {
                                    // Initialize your modal and table elements
                                    var modal = $('#largeModal');
                                    var table = $('#beneficiaireTable tbody');
                                    var data = [];


                                    $('#addBtn').on('click', function() {
                                        var code;
                                        var libelle;
                                        var type; // Ajout d'une variable pour le type de bénéficiaire

                                        if ($('#age').prop('checked')) {
                                            code = $('#localite').val();
                                            libelle = $('#localite option:selected').text();
                                            type = 'localite'; // Définition du type de bénéficiaire
                                        } else if ($('#sousp').prop('checked')) {
                                            code = $('#sous_prefecture1').val();
                                            libelle = $('#sous_prefecture1 option:selected').text();
                                            type = 'sous_prefecture';
                                        } else if ($('#min').prop('checked')) {
                                            code = $('#etablissement').val();
                                            libelle = $('#etablissement option:selected').text();
                                            type = 'etablissement'; // Définition du type de bénéficiaire
                                        } else if ($('#reg').prop('checked')) {
                                            code = $('#region2').val();
                                            libelle = $('#region2 option:selected').text();
                                            type = 'region'; // Définition du type de bénéficiaire
                                        } else if ($('#dis').prop('checked')) {
                                            code = $('#district1').val();
                                            libelle = $('#district1 option:selected').text();
                                            type = 'district'; // Définition du type de bénéficiaire
                                        } else if ($('#dep').prop('checked')) {
                                            code = $('#departement2').val();
                                            libelle = $('#departement2 option:selected').text();
                                            type = 'departement'; // Définition du type de bénéficiaire
                                        }

                                        if (code && libelle && type) {
                                            // Vérifier si l'élément existe déjà dans le tableau
                                            var exists = false;
                                            $('#beneficiaireTable tbody tr').each(function() {
                                                var existingCode = $(this).find('td:eq(1)').text();
                                                var existingType = $(this).find('td:eq(3)').text();
                                                if (existingCode === code && existingType === type) {
                                                    exists = true;
                                                    return false; // Sortir de la boucle each
                                                }
                                            });

                                            if (!exists) {
                                                // Ajouter un nouvel objet à la variable data avec les valeurs actuelles
                                                var newRow = {
                                                    code: code,
                                                    libelle: libelle,
                                                    type: type
                                                };
                                                data.push(newRow);

                                                // Ajouter une nouvelle ligne au tableau avec le type de bénéficiaire
                                                table.append('<tr><td><input type="checkbox"></td><td>' + code + '</td><td>' + libelle + '</td><td>' + type + '</td></tr>');
                                            } else {
                                                $('#alertMessage').text('Cet élément existe déjà dans le tableau.');
                                                $('#alertModal').modal('show');
                                            }
                                        }

                                        // Fermer la modal
                                        modal.modal('hide');
                                    });
                                    $('#enregistrerBeneficiaire').on('click', function() {
                                        // Envoi de la requête AJAX
                                        $.ajax({
                                            url: '{{ route("enregistrer.beneficiaires") }}',
                                            type: 'POST',
                                            data: {
                                                _token: '{{ csrf_token() }}',
                                                beneficiaire_code: $('#beneficiaireTable tbody tr').map(function() {
                                                    return $(this).find('td:eq(1)').text();
                                                }).get(),
                                                beneficiaire_type: $('#beneficiaireTable tbody tr').map(function() {
                                                    return $(this).find('td:eq(3)').text();
                                                }).get(),
                                                CodeProjetBene: $('#CodeProjetBene').val(),
                                                numOrdreBene: $('#numOrdreBene').val(),
                                            },
                                            success: function(response) {

                                                if (response.success) {
                                                    $('#alertMessage').text(response.message);
                                                    $('#alertModal').modal('show');
                                                }else if (response.error) {
                                                    $('#alertMessage').text(response.message);
                                                    $('#alertModal').modal('show');
                                                    return false;

                                                }
                                            },
                                            error: function(xhr, status, error) {
                                                if (response.error) {
                                                    $('#alertMessage').text(response.message);
                                                    $('#alertModal').modal('show');
                                                    return false;
                                                }

                                                console.error('Erreur lors de l\'enregistrement : ' + error);

                                            }
                                        });
                                    });




                                    // Gestionnaire du clic sur la checkbox d'en-tête pour sélectionner toutes les checkboxes
                                    $('#beneficiaireTable thead input[type="checkbox"]').on('click', function() {
                                        var isChecked = $(this).prop('checked');
                                        $('#beneficiaireTable tbody input[type="checkbox"]').prop('checked', isChecked);
                                    });

                                    // Gestionnaire du clic sur le bouton "Supprimer"
                                    $('#deleteBtn').on('click', function() {
                                        // Trouvez et supprimez les lignes sélectionnées
                                        $('#beneficiaireTable tbody input[type="checkbox"]:checked').closest('tr').remove();
                                    });

                                });


                                function loadBeneficiaires(codeProjet, numOrdre) {
                                    // Effectuer une requête AJAX pour récupérer les données des bénéficiaires
                                    $.ajax({
                                        url: '/recuperer-beneficiaires',
                                        type: 'GET',
                                        data: { CodeProjet: codeProjet, NumOrdre: numOrdre },
                                        success: function(response) {
                                            // Vider le corps du tableau
                                            $("#beneficiaireTable tbody").empty();

                                            // Parcourir les données et les ajouter au tableau
                                            for (var i = 0; i < response.length; i++) {
                                                var beneficiaire = response[i];
                                                var row = '<tr>';
                                                row += '<td><input type="checkbox"></td>';
                                                row += '<td>' + beneficiaire.code + '</td>';
                                                row += '<td>' + beneficiaire.libelle_nom_etablissement + '</td>';
                                                row += '<td>' + beneficiaire.type + '</td>';
                                                row += '</tr>';
                                                $("#beneficiaireTable tbody").append(row);
                                            }
                                        },
                                        error: function(xhr, status, error) {
                                            console.error('Erreur lors de la récupération des bénéficiaires : ' + error);
                                        }
                                    });
                                }



                            </script>
                        </div>

                    </div>
                </div>
                <!-- Ajoutez cette section après votre tableau existant -->
               <div id="liste-projets" style="display: none;">

                    <div class="card-body" >
                    <h4>Liste des projets clôturés (Clôturé)</h4>
                        <table class="table table-striped table-bordered" cellspacing="0" style="width: 100%" id="liste-projets-table" >
                            <thead>
                                <tr>
                                    <th>Code projet</th>
                                    <th>Domaine</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Coût</th>
                                    <th>Dévise</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($projets as $projet)
                                    @php
                                        // Filtrer les statuts pour le projet actuel
                                        $statutsProjet = $statuts->where('CodeProjet', $projet->CodeProjet);
                                        // Trouver le statut avec le code '04' pour ce projet
                                        $statut = $statutsProjet->firstWhere('codeSStatu', '04');
                                    @endphp

                                    @if ($statut)

                                        <tr>
                                            <td>{{ $projet->CodeProjet }}</td>
                                            <td>
                                                @if ($projet->Domaine)
                                                    {{ $projet->Domaine->libelle }}
                                                @endif
                                            </td>
                                            <td>{{ $projet->Date_demarrage_prevue }}</td>
                                            <td>{{ $projet->date_fin_prevue }}</td>
                                            <td class="formatted-number" style=" text-align: right; float: right; justify-content: right;">{{ $projet->cout_projet }}</td>
                                            <td>
                                                @if ($projet->devise)
                                                    {{ $projet->devise->code_long }}
                                                @endif
                                            </td>
                                            <td>{{ $statut->statut_libelle }}</td>
                                        </tr>
                                    @endif
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>

    </div>
    </div>
    </div>

<!-- Modal -->
<div class="modal fade" id="doubleFormModal" tabindex="-1" role="dialog" aria-labelledby="doubleFormModalLabel" aria-hidden="true" style="background-color: #DBECF8;">
    <div class="modal-dialog modal-lg" role="document">

        <div class="modal-content" style="background-color: white;">
            <div class="modal-header">

                <h5 class="modal-title" id="doubleFormModalLabel">Niveau d'avancement & Dates de fin effectives</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
                <div class="card-content" style="background-color: #EAF2F8;" >
                    <div class="modal-body" style="background-color: #EAF2F8;">
                        <ul class="nav nav-tabs" id="myTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="etatAvancement-tab" data-toggle="tab" href="#etatAvancement" role="tab" aria-controls="etatAvancement" aria-selected="true">État d'avancement</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="datesEffectives-tab" data-toggle="tab" href="#datesEffectives" role="tab" aria-controls="datesEffectives" aria-selected="false">Données Effectives</a>
                            </li>
                        </ul>
                        <br>
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
                                                <label for="nature_travaux">Nature des travaux:</label>
                                                <input type="text" class="form-control" id="nature_travaux_Modal" name="nature_travaux_Modal" readonly>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col">
                                                <label for="quantite_reel">Quantité Prévue :</label>
                                                <input type="text" readonly style=" text-align: right; float: right; justify-content: right;" class="form-control" name="quantite_provisionnel_Modal" id="quantite_provisionnel_Modal">
                                            </div>
                                            <div class="col">
                                                <label for="quantite_reel">Quantité réalisée:</label>
                                                <input type="text" class="form-control" style=" text-align: right; float: right; justify-content: right;" id="quantite_reel_Modal" name="quantite_reel_Modal">

                                            </div>
                                            <div class="col">
                                                <label for="pourcentage">Pourcentage:</label>
                                                <input type="text" class="form-control" id="pourcentage_Modal" name="pourcentage_Modal">
                                            </div>
                                            <div class="col">
                                                <label for="date_realisation">Date de réalisation:</label>
                                                <input type="date" class="form-control" id="date_realisation_Modal" name="date_realisation_Modal">
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

                                <!-- Deuxième formulaire - Dates effectives -->
                                <div class="tab-pane fade" id="datesEffectives" role="tabpanel" aria-labelledby="datesEffectives-tab" style="background-color: #EAF2F8;">
                                    <form id="datesEffectivesForm" method="POST" action="{{ route('enregistrer.dateFinEffective') }}" data-parsley-validate>
                                        @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>

                                        <input type="hidden" id="code_projetModal" name="code_projetModal">

                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-2" style="width: 25%;">
                                                    <label for="date_debut">Démarrage effectif:</label>
                                                    <input type="date" class="form-control" id="date_debut_Modal" name="date_debut_Modal" readonly>
                                                </div>
                                                <div class="col-2"style="width: 25%;">
                                                    <label for="date_fin">Clôture effective:</label>
                                                    <input type="date" class="form-control" id="date_fin_Modal" name="date_fin_Modal">
                                                </div>
                                                <div class="col-2"style="width: 35%;">
                                                    <label for="coutEffective">Coût effectif:</label>
                                                    <input type="text" class="form-control" style=" text-align: right; float: right; justify-content: right;" id="coutEffective_Modal" name="coutEffective_Modal">
                                                </div>
                                                <div class="col-2"style="width: 15%;">
                                                    <label for="devise">Devise:</label>
                                                    <input type="text" class="form-control" id="devise_Modal" name="devise_Modal" value="XOF">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="form-group">
                                                    <label for="commentaire">Commentaire:</label>
                                                    <textarea class="form-control" id="commentaire_Modal" name="commentaire_Modal" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Enregistrer Dates et coût Effectives</button>
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
</section>
<script>
    $(document).ready(function() {
        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'liste-projets-table', 'de projets clôturés');
    });

    ///////////////////CALCUL DE REALISATION//////////////////////
    // Attacher un gestionnaire d'événement au champ de la "Quantité réelle"
    var timeoutId;

    $("#quantite_reel_Modal").on("input", function() {
        clearTimeout(timeoutId); // Annuler le délai précédent

        timeoutId = setTimeout(function() {
            // Récupérer les valeurs des champs
            var quantitePre = parseFloat($("#quantite_provisionnel_Modal").val());
            var quantiteReelle = parseFloat($("#quantite_reel_Modal").val());

            // Vérifier si les valeurs sont valides pour le calcul
            if (!isNaN(quantitePre) && !isNaN(quantiteReelle) && quantitePre !== 0) {
                // Vérifier que la Quantité réelle ne dépasse pas la Quantité pré
                if (quantiteReelle <= quantitePre) {
                    // Vérifier si la Quantité prévisionnelle est nulle, égale à 0 ou égale à 1
                    if (quantitePre === 0 || quantitePre === 1) {
                        // Afficher un message à l'utilisateur pour entrer manuellement le pourcentage
                        $("#pourcentage_Modal").val("");
                        alert("Veuillez entrer manuellement le pourcentage estimé.");
                        $("#pourcentage_Modal").val("");
                    } else {
                        $("#pourcentage_Modal").val("");
                        // Calculer le pourcentage
                        var pourcentage = (quantiteReelle / quantitePre) * 100;

                        // Vérifier les cas particuliers
                        if (isFinite(pourcentage) && pourcentage > 0) {
                            // Afficher le pourcentage calculé dans le champ correspondant
                            $("#pourcentage_Modal").val(pourcentage.toFixed(0));
                        } else {
                            // Afficher un message à l'utilisateur pour entrer le pourcentage manuellement
                            $("#pourcentage_Modal").val("");
                            alert("Le calcul du pourcentage n'est pas possible ou le résultat est invalide. Veuillez entrer manuellement le pourcentage estimé.");
                        }
                    }
                } else {
                    // Quantité réelle supérieure à la Quantité pré
                    // Effacer la valeur du champ
                    $("#quantite_reel_Modal").val("");
                    // Afficher un message à l'utilisateur
                    alert("La Quantité réelle ne peut pas être supérieure à la Quantité prévisionnelle. La valeur a été effacée.");
                }
            } else if (isNaN(quantitePre)) {
                // Afficher un message à l'utilisateur pour entrer la quantité préalable
                $("#pourcentage_Modal").val("");
                alert("Veuillez entrer d'abord la quantité prévisionnelle.");
            } else if (quantitePre === 0 || quantitePre === 1) {
                // Afficher un message à l'utilisateur pour entrer manuellement le pourcentage
                $("#pourcentage_Modal").val("");
                alert("La quantité prévisionnelle est nulle, égale à 0 ou égale à 1. Veuillez entrer manuellement le pourcentage estimé.");
            } else {
                // Afficher un message à l'utilisateur pour entrer la quantité réelle
                $("#pourcentage_Modal").val("");
                alert("Veuillez entrer d'abord la quantité réelle.");
            }
        }, 500); // Délai de 500 millisecondes
    });










    ////////////////VOIR LA LISTE///////////////////////////
    $(document).ready(function() {
        $('#voir-liste-link').click(function() {
            $('#liste-projets').toggle(); // basculer la visibilité
        });
    });



    /////////////GESTION DE ACTIONS //////////
    // Fonction pour vérifier les détails du projet
    function checkProjectDetails() {
            // Récupérez les valeurs des champs
        var code_projet = $('#code_projet').val();

        // Effectuez une requête AJAX pour récupérer les détails du projet
        $.ajax({
            url: '/fetchProjectDetails',
            method: 'GET',
            data: {
                _token: '{{ csrf_token() }}', // CSRF token
                code_projet: code_projet
            },
            success: function (data) {
                // Mettez à jour les champs de devise et de code projet
                $('#date_debut').val(data.date_debut);
                $('#date_fin').val(data.date_fin);
                var formattedCout = number_format(data.cout, 0, ' ', ' ');
                $('#cout').val(formattedCout);

                $('#statutInput').val(data.statutInput);
                $('#codeProjetHidden').val(data.codeProjet);
                $('#devise').val(data.devise);

            },
            error: function (error) {
            }
        });
    }
    // Fonction pour mettre à jour les données du tableau


    // Attendez que le document soit prêt
    $(document).ready(function () {
        // Écoutez le changement de valeur du champ codeProjetHidden
        $('#codeProjetHidden').change(function () {
            // Récupérez la nouvelle valeur du champ codeProjetHidden
            var newCodeProjet = $(this).val();

        });
    });


    function updateTableData(data) {
        $("#beneficiaire-table-body").empty();

        for (var i = 0; i < data.length; i++) {
            var rowClass = "";

        // Vérifier l'existence du CodeProjet dans la table caracteristique
        $.ajax({
            url: '/check-code-projet', // Remplacez par l'URL de votre API qui vérifie l'existence
            type: 'GET',
            data: { CodeProjet: data[i].CodeProjet, Ordre: data[i].Num_ordre },
            async: false,  // Assurez-vous de la synchronisation pour la vérification avant d'ajouter la ligne
            success: function(response) {
                if (response.exists) {
                    // Si le CodeProjet existe, définir la classe de ligne en jaune clair
                    rowClass = "bg-light-warning"   ;
                } else {
                    // Si le CodeProjet n'existe pas, définir la classe de ligne en vert clair
                    rowClass = "bg-light-success";
                }
            },
            error: function(error) {
                console.error('Erreur lors de la vérification de l\'existence du CodeProjet : ', error);
            }
        });
        var row = '<tr class="action ' + rowClass + '" data-id="' + data[i].code + '">';
            row += '<td style="color: wi" class="code_projet_cell" hidden>' + data[i].CodeProjet + '</td>';
            row += '<td style="color: wi" class="num_ordre_cell">' + data[i].Num_ordre + '</td>';
            row += '<td>' + data[i].action_libelle + '</td>';
            row += '<td>' + data[i].Quantite + '</td>';
            row += '<td>' + data[i].Unite_mesure + '</td>';
            row += '<td>' + data[i].infrastructure_libelle + '</td>';
            row += '<td><a href="#" data-toggle="modal" data-target="#largeModal" onclick="loadBeneficiaires(\'' + data[i].CodeProjet + '\', \'' + data[i].Num_ordre + '\')"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"></path></svg>Bénéficiaire</a></td>';
            row += '</tr>';
            $("#beneficiaire-table-body").append(row);
            // Récupérer les données de la ligne


        }
        $(document).ready(function() {
        // Gestionnaire de clic pour le bouton "Bénéficiaire"
        $(document).on("click", "a[data-target='#largeModal']", function() {
            // Récupérer les données de la ligne
            var numOrdre = $(this).closest("tr").find(".num_ordre_cell").text();
            var codeProjet = $(this).closest("tr").find(".code_projet_cell").text();
            loadBeneficiaires(codeProjet, numOrdre);
            // Remplir les champs du modal avec les données de la ligne
            $("#CodeProjetBene").val(codeProjet);
            $("#numOrdreBene").val(numOrdre);


        });
        });
            $(document).on("click", ".btn-niveau-avancement", function() {
                var numOrdre = $(this).data("num-ordre");
                $("#ordre_Modal").val(numOrdre);
                var inputCodeProjet = document.getElementById('code_projet');
                var codeProjetValue = inputCodeProjet.value;
                $("#code_projetModal").val(codeProjetValue);
                $("#code_projet_Modal").val(codeProjetValue);
            openNiveauAvancementModal(); // Appeler la fonction pour ouvrir le modal si nécessaire
        });

    }
    function openNiveauAvancementModal() {
        console.log("Ouverture du modal");
        // Récupérer les valeurs du modal
        var numOrdre = $("#ordre_Modal").val();
        var codeProjet = $("#code_projet_Modal").val();

        // Effectuer la requête Ajax pour obtenir les données associées au code projet et à l'ordre
        $.ajax({
            url: '{{ route("get.donnees.formulaire")}}',
            type: 'GET',
            data: { code_projet_Modal: codeProjet, ordre_Modal: numOrdre },
            success: function(response) {
                // Remplir les champs du formulaire avec les données obtenues
                if (response.result.length > 0) {
                    var data = response.result[0];
                    $("#nature_travaux_Modal").val(data.libelle);
                    $("#quantite_provisionnel_Modal").val(data.Quantite);
                    $("#date_debut_Modal").val(data.date);
                    // Ajoutez d'autres champs si nécessaire
                } else {
                    // Gérer le cas où aucune donnée n'est retournée
                    console.error('Aucune donnée trouvée pour le code projet et l\'ordre spécifiés.');
                }
            },
            error: function(error) {
                console.error('Erreur lors de la récupération des données pour le formulaire : ', error);
            }
        });
        $('#doubleFormModal').modal('show');
        $('#niveauAvancementModal').modal('show');
    }
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
        document.getElementById('cout').addEventListener('input', function (event) {
            formatNumberInput(event.target);
        });
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


        // Fonction de formatage générique pour les champs de nombre
        function formatNumberColumn(className) {
                var elements = document.getElementsByClassName(className);
                for (var i = 0; i < elements.length; i++) {
                    var element = elements[i];
                    element.textContent = number_format(element.textContent, 0, ' ', ' ');
                }
            }

            // Appliquer le formatage après le chargement du document
            document.addEventListener('DOMContentLoaded', function() {
                formatNumberColumn('formatted-number');
        });
        // Gérer l'événement de saisie pour le champ coutEffective_Modal
        document.getElementById('coutEffective_Modal').addEventListener('input', function (event) {
            formatNumberInput(event.target, 0, ' ', ' ');
        });
        ////////////////////////////////////////////////////



        function updateCodeProjetValue() {
            var codeProjet = $("#code_projet").val();

            $.ajax({
                url: '/getProjetData',
                type: 'GET',
                data: { codeProjet: codeProjet },
                success: function(data) {
                    updateTableData(data);
                },
                error: function() {
                    console.log('Erreur lors de la récupération des données.');
                }
            });
        }





        $(document).ready(function () {
            // Écouter les changements dans les champs de date et de coût
            $('#date_debut, #date_fin, #cout').change(function () {
                // Appeler la fonction pour vérifier les détails du projet
                checkProjectDetails();
            });
        });


        function refreshActionList() {
        // Effectuer une requête Ajax pour récupérer les nouvelles données
        $.ajax({
            url: '/refreshActionList', // Assurez-vous que cette URL pointe vers votre nouvelle méthode dans le contrôleur
            method: 'GET',
            success: function (data) {
                // Mettez à jour le contenu de la table avec les nouvelles données
                $('#beneficiaire-table-body').html(data);
            },
            error: function (error) {
                console.log(error);
            }
        });
    }



    // Récupérer l'élément input par son id
    var inputCodeProjet = document.getElementById('code_projet');

    // Ajouter un écouteur d'événements pour le changement de la valeur de l'input
    inputCodeProjet.addEventListener('input', function () {
        // Récupérer la valeur de l'input
        var codeProjetValue = inputCodeProjet.value;

        // Afficher la valeur dans la console (vous pouvez la remplacer par une autre action)
        console.log("Code projet saisi : " + codeProjetValue);


        // Utilisez cette valeur dans votre condition (remplacez la console.log par votre condition)
        if (codeProjetValue == 'valeur_attendue') {
            console.log('La condition est vraie !');
        } else {
            console.log('La condition est fausse !');
        }
    });



    // Appeler la fonction checkProjectDetails lors du chargement de la page pour la première fois (si une option est déjà sélectionnée)
    $(document).ready(function() {
        checkProjectDetails();
    });


    function cloturerProjet() {
        var dateCloture = $('#dateCloture').val();
        var codeProjet = $('#code_projet').val();

        if (!codeProjet) {
            $('#alertMessage').text('Veuillez sélectionner un projet.');
            $('#alertModal').modal('show');
            return;
        }
        if (!dateCloture) {
            $('#alertMessage').text('Veuillez entrer la date de clôture du projet.');
            $('#alertModal').modal('show');
            return;
        }
        // Afficher le modal de confirmation
        $('#confirmationModal').modal('show');
    }

    $(document).ready(function() {
        $('#confirmCloture').click(cloturerProjetAJAX);

        // Fonction pour clôturer le projet avec AJAX
        function cloturerProjetAJAX() {
            var codeProjet = $('#code_projet').val(); // Assurez-vous de définir l'ID du projet
            var dateCloture = $('#dateCloture').val();

            if (!codeProjet) {
                $('#alertMessage').text('Veuillez sélectionner un projet.');
                $('#alertModal').modal('show');
                return;
            }

            if (!dateCloture) {
                $('#alertMessage').text('Veuillez entrer la date de clôture du projet.');
                $('#alertModal').modal('show');
                return;
            }

            $.ajax({
                url: '/cloturer-projet',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    code_projet: codeProjet,
                    date_cloture: dateCloture
                },
                success: function(response) {
                    $('#alertMessage').text('Le projet a été clôturé avec succès.');
                    $('#alertModal').modal('show');
                    // Actualiser la page pour afficher les modifications
                    location.reload();
                },
                error: function(error) {
                    console.error('Erreur AJAX:', error);
                    $('#alertMessage').text('Une erreur s\'est produite lors de la clôture du projet.');
                    $('#alertModal').modal('show');
                }
            });
        }

        // Afficher un message si le champ #code_projet est vide
        $('#code_projet').change(function() {
            var codeProjet = $(this).val();
            if (!codeProjet) {
                $('#alertMessage').text('Veuillez sélectionner un projet.');
                $('#alertModal').modal('show');
            }
        });
    });



</script>

@endsection
