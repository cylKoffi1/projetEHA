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
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
    .text-center {
        background-color: #5c76cc;
        color: white;
    }
    fieldset .row .col label {
        width: 100%;
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
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Projet </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Editions</a></li>
                            <li class="breadcrumb-item"><a href="">Annexe 2</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Fiche de collecte</li>

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
                    <div style="display: flex; width: 100%; justify-content: space-between; align-items: center;">
                        <h5 class="card-title">Annexe 2: Formulaire de collecte de données</h5>


                    </div>
                    <div style="text-align: center;">
                        <h5 class="card-title"></h5>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <fieldset >
                            <div class="row">
                                <div class="col-3" style="width: 28%;">
                                    <label for="code_projet">Année</label>
                                    <select name="code_projet" id="code_projet" class="form-select col-35">
                                        <option value=""></option>
                                        @foreach ($projets as $projet)
                                        <option value="{{ $projet->CodeProjet }}">{{ $projet->CodeProjet }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-2">
                                    <button type="button" class="btn btn-primary" style="width: 125px;" onclick="loadProjectDetails()">
                                        <i class="bi bi-search"></i>Afficher
                                    </button>
                                </div>
                                <div class="col">
                                    <button type="button" class="btn btn-danger"  onclick="clearProjectDetails()">
                                        <i class="bi bi-trash"></i> Vider les champs
                                    </button>
                                </div>
                                <div class="col">
                                    <button type="button" class="btn btn-secondary" style="width: 125px;" onclick="printerDocument()">
                                        <i class="bi bi-printer"></i> imprimer
                                    </button>
                                </div>
                                <div class="col">
                                    <button type="button" class="btn btn-secondary" style="width: 125px;" onclick="generatePDF()">
                                        <i class="bi bi-download"></i> télécharger en Pdf
                                    </button>
                                </div>
                            </div>

                        </fieldset>

                    </div>
                </div>
            </div>
        </div>
    </div>

</section>
<script>
    $(document).ready(function() {

        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Annexe 1: infomations principales');
    });
    //fonction pour récuperer les informations liées au code projet
    function loadProjectDetails() {
        // Récupérer le code du projet sélectionné
        var selectedCodeProjet = document.getElementById('code_projet').value;

        // Vérifier si le code du projet est sélectionné
        if (selectedCodeProjet) {
            // Faire une requête AJAX pour récupérer les détails du projet à partir du fichier JSON
            var xhr = new XMLHttpRequest();
            xhr.open('GET', "/getProjectDetails?code_projet=" + selectedCodeProjet, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                       // Convertir la réponse JSON en objet JavaScript
                        var projetDetails = JSON.parse(xhr.responseText);

                        // Afficher les détails du projet dans les champs correspondants
                        document.getElementById('nom_contact').value = projetDetails[0]?.projet_chef_projet[0]?.personne[0]?.nom || '---';
                        document.getElementById('adresse_contact').value = projetDetails[0]?.projet_chef_projet[0]?.personne[0]?.addresse || '---';
                        document.getElementById('tel_contact').value = projetDetails[0]?.projet_chef_projet[0]?.personne[0]?.telephone || '---';
                        document.getElementById('email_contact').value = projetDetails[0]?.projet_chef_projet[0]?.personne[0]?.email || '---';

                        var bailleursString = projetDetails[0]?.bailleurs_projets.map(bailleur => bailleur.bailleurss[0]?.libelle_long).join(', ');
                        document.getElementById('bailleur_fonds').value = bailleursString || '---';
                        document.getElementById('objectif_global').value = projetDetails[0]?.Objectif_global || '---';
                        document.getElementById('statut_programme_projet').value = projetDetails[0]?.projet_statut_projet[0]?.statut[0]?.libelle || '---';

                        var ministereNonNuls = projetDetails[0]?.ministere_projet.filter(ministere => ministere.ministere !== null);
                        var ministereTutelleString = ministereNonNuls.map(ministere => ministere.ministere.libelle).join(', ');
                        document.getElementById('ministere_tutelle').value = ministereTutelleString || '---';
                        
                        // Affichage des agences d'exécution (niveau 1)
                        var agencesNiveau1String = projetDetails[0]?.projet_agence.filter(agence => agence.niveau === 1)
                            .map(agence => agence.agence_execution[0]?.nom_agence).join(', ');
                        document.getElementById('agence_execution_niveau1').value = agencesNiveau1String || '---';

                        // Affichage des agences d'exécution (niveau 2)
                        var agencesNiveau2String = projetDetails[0]?.projet_agence.filter(agence => agence.niveau === 2)
                            .map(agence => agence.agence_execution[0]?.nom_agence).join(', ');
                        document.getElementById('agence_execution_niveau2').value = agencesNiveau2String || '---';
                        document.getElementById('date_demarrage_prevue').value = projetDetails[0]?.Date_demarrage_prevue || '---';
                        document.getElementById('date_fin_prevue').value = projetDetails[0]?.date_fin_prevue || '---';
                    } else {
                        console.error('Erreur de chargement des détails du projet: ' + xhr.status);
                    }
                }
            };
            xhr.send();
        } else {
            // Afficher un message d'erreur si aucun projet n'est sélectionné
            alert('Veuillez sélectionner un code de projet.');
        }
    }

    function clearProjectDetails(){
        document.getElementById('nom_contact').value="";
        document.getElementById('adresse_contact').value = null;
        document.getElementById('tel_contact').value = null;
        document.getElementById('email_contact').value = null;
        document.getElementById('bailleur_fonds').value = null;
        document.getElementById('objectif_global').value = null;
        document.getElementById('statut_programme_projet').value = null;
        document.getElementById('ministere_tutelle').value = null;
        document.getElementById('agence_execution_niveau1').value = null;
        document.getElementById('agence_execution_niveau2').value = null;
        document.getElementById('date_demarrage_prevue').value = null;
        document.getElementById('date_fin_prevue').value = null;

    }
    function generatePDF() {
        // Créer une instance de jsPDF
        const doc = new jsPDF();

        // Récupérer le contenu de la section à convertir en PDF
        const pdfContent = document.getElementById('pdfContent');

        // Convertir le contenu HTML de la section en PDF
        doc.html(pdfContent, {
            callback: function (doc) {
                // Enregistrer le PDF
                doc.save('document.pdf');
            }
        });
    }

    function printerDocument(){
        {
            window.print(); 


        }
    }
</script>

@endsection
