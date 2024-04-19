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
                                    <label for="code_projet">Code du projet</label>
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
                        <div class="row">
                            <div class="col">
                                <fieldset >
                                    <legend class=" form-control text-center font-weight-bold">I. Données générales sur le programme/projet</legend>
                                    <div class="row">

                                        <div class="col-6">
                                            <label for="agence_execution_niveau1" >1. Agence d'exécution (1er):</label>
                                            <textarea id="agence_execution_niveau1" name="agence_execution_niveau1" style="height: 0;" class="form-control" ></textarea>
                                        </div>
                                        <div class="col-6">
                                            <label for="agence_execution_niveau2" >2. Agence d'exécution (2ème):</label>
                                            <textarea id="agence_execution_niveau2" name="agence_execution_niveau2" style="height: 0;" class="form-control"></textarea>
                                        </div>


                                    </div>
                                    <div class="row">
                                        <div class="col">
                                            <label for="bailleur_fonds">3. Bailleur de Fonds:</label>
                                            <textarea  id="bailleur_fonds" name="bailleur_fonds" style="height: 0;" class="form-control"></textarea>
                                        </div>
                                        <div class="col">
                                            <label for="ministere_tutelle">4. Ministère(s) de tutelle:</label>
                                            <textarea id="ministere_tutelle" name="ministere_tutelle" style="height: 0;" class="form-control"></textarea>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col">
                                <fieldset >
                                    <legend class=" form-control text-center font-weight-bold">III. Programme/Projet</legend>
                                    <div class="row">
                                        <div class="col">
                                            <label for="nom_contact">5. Nom & prénoms :</label>
                                            <input type="text" id="nom_contact" name="nom_contact" class="form-control">
                                        </div>
                                        <div class="col">
                                            <label for="adresse_contact">6. Adresse :</label>
                                            <input type="text" id="adresse_contact" name="adresse_contact" class="form-control">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col">
                                            <label for="tel_contact">7. Tél contact :</label>
                                            <input type="text" id="tel_contact" name="tel_contact" class="form-control">
                                        </div>
                                        <div class="col">
                                            <label for="email_contact">8. Email :</label>
                                            <input type="text" id="email_contact" name="email_contact" class="form-control">
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <fieldset >
                            <legend class=" form-control text-center font-weight-bold">III. Programme/Projet</legend>
                            <div class="row">
                                <div class="col">
                                    <label for="intitule_programme_projet">9. Intitulé du Programme/Projet:</label>
                                    <input type="text" id="intitule_programme_projet" name="intitule_programme_projet" class="form-control">
                                </div>
                                <div class="col">
                                    <label for="statut_programme_projet">10. Statut du Programme/Projet:</label>
                                    <input type="text" id="statut_programme_projet" name="statut_programme_projet" class="form-control">
                                </div>
                                <div class="col">
                                    <label for="objectif_global">11. Objectif global du Programme/Projet:</label>
                                    <textarea id="objectif_global" name="objectif_global" rows="4" class="form-control"></textarea>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset >
                            <legend class=" form-control text-center font-weight-bold">IV. Niveau d'intervention</legend>
                            <div class="row">
                                <div class="col-3">
                                    <label for="niveau_mise_oeuvre">12. Niveau de mise en œuvre:</label>
                                    <select id="niveau_mise_oeuvre" name="niveau_mise_oeuvre" class="form-select">
                                        <option value="">Selectionner</option>
                                        <option value="national">National</option>
                                        <option value="provincial">Provincial</option>
                                        <option value="regional">Nationnal et provincial</option>
                                    </select>
                                </div>
                            </div><br>
                            <div class="row">
                                <label for="regions">13. Régions (si niveau de mise en œuvre est provincial):</label>
                                <div id="checkbox-regions">
                                <div class="row">
                                    <div class="col">
                                        <label><input type="checkbox" name="regions[]" value="Agnéby-Tiassa"> Agnéby-Tiassa</label>
                                        <label><input type="checkbox" name="regions[]" value="Bagoué"> Bagoué</label>
                                        <label><input type="checkbox" name="regions[]" value="Bafing"> Bafing</label>
                                        <label><input type="checkbox" name="regions[]" value="Bélier"> Bélier</label>
                                        <label><input type="checkbox" name="regions[]" value="Béré"> Béré</label>
                                        <label><input type="checkbox" name="regions[]" value="Bounkani"> Bounkani</label>
                                        <label><input type="checkbox" name="regions[]" value="Cavally"> Cavally</label>
                                        <label><input type="checkbox" name="regions[]" value="Folon"> Folon</label>
                                    </div>
                                    <div class="col">
                                        <label><input type="checkbox" name="regions[]" value="Gbêkê"> Gbêkê</label>
                                        <label><input type="checkbox" name="regions[]" value="Gbôklé"> Gbôklé</label>
                                        <label><input type="checkbox" name="regions[]" value="Gôh"> Gôh</label>
                                        <label><input type="checkbox" name="regions[]" value="Grands Ponts"> Grands Ponts</label>
                                        <label><input type="checkbox" name="regions[]" value="Guémon"> Guémon</label>
                                        <label><input type="checkbox" name="regions[]" value="Hambol"> Hambol</label>
                                        <label><input type="checkbox" name="regions[]" value="Haut-Sassandra"> Haut-Sassandra</label>
                                        <label><input type="checkbox" name="regions[]" value="Iffou"> Iffou</label>
                                    </div>
                                    <div class="col">
                                        <label><input type="checkbox" name="regions[]" value="Indénié-Djuablin"> Indénié-Djuablin</label>
                                        <label><input type="checkbox" name="regions[]" value="Kabadougou"> Kabadougou</label>
                                        <label><input type="checkbox" name="regions[]" value="Lôh-Djiboua"> Lôh-Djiboua</label>
                                        <label><input type="checkbox" name="regions[]" value="Marahoué"> Marahoué</label>
                                        <label><input type="checkbox" name="regions[]" value="Mé"> Mé</label>
                                        <label><input type="checkbox" name="regions[]" value="Moronou"> Moronou</label>
                                        <label><input type="checkbox" name="regions[]" value="N’zi"> N’zi</label>
                                        <label><input type="checkbox" name="regions[]" value="Nawa"> Nawa</label>
                                    </div>
                                    <div class="col">
                                        <label><input type="checkbox" name="regions[]" value="Poro"> Poro</label>
                                        <label><input type="checkbox" name="regions[]" value="San-Pédro"> San-Pédro</label>
                                        <label><input type="checkbox" name="regions[]" value="Sud-Comoé"> Sud-Comoé</label>
                                        <label><input type="checkbox" name="regions[]" value="Tchologo"> Tchologo</label>
                                        <label><input type="checkbox" name="regions[]" value="Tonkpi"> Tonkpi</label>
                                        <label><input type="checkbox" name="regions[]" value="Worodougou"> Worodougou</label>
                                        <!-- Ajouter la région manquante ici -->
                                    </div>
                                </div>


                                </div>
                            </div>
                        </fieldset>
                        <div class="row">
                            <div class="col-3">
                                <fieldset >
                                    <legend class=" form-control text-center font-weight-bold">V. Période d'activité</legend>
                                    <div class="row">
                                        <div class="col">
                                            <label for="agence_execution_niveau2" >14. Date début prévue:</label>
                                            <input type="date" id="date_demarrage_prevue" name="date_demarrage_prevue" class="form-control">

                                            <label for="date_demarrage_effective" >15. Date début effective:</label>
                                            <input type="date" id="date_demarrage_effective" name="date_demarrage_effective" class="form-control">

                                            <label for="date_fin_prevue" >16. Date de fin prévue:</label>
                                            <input type="date" id="date_fin_prevue" name="date_fin_prevue" class="form-control">

                                            <label for="date_fin_effective" >17. Date de fin effective:</label>
                                            <input type="date" id="date_fin_effective" name="date_fin_effective" class="form-control">
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="col">
                                <fieldset >
                                    <legend class=" form-control text-center font-weight-bold">VI. Budget</legend>
                                    <div class="row">
                                        <div class="col">
                                            <label for="engagement_global">Engagement global /coût du Programme/Projet:</label>
                                            <input type="text" id="engagement_global" name="engagement_global" class="form-control">
                                        </div>
                                        <div class="col">
                                            <label for="monnaie">19. Monnaie (USD, EUR ou XOF):</label>
                                            <select id="monnaie" name="monnaie" class="form-control">
                                                <option value="USD">USD</option>
                                                <option value="EUR">EUR</option>
                                                <option value="XOF">XOF</option>
                                            </select>
                                        </div>
                                    </div><br><br>
                                    <div class="row">
                                        <div class="col">
                                            <label for="situation_financiere">18. Détails sur la situation financière du Programme/Projet:</label>
                                            <textarea id="situation_financiere" name="situation_financiere" rows="4" class="form-control"></textarea>
                                        </div>
                                        <div class="col">
                                            <label for="commentaires_financiers">20. Commentaires sur la situation financière (par exemple: taux d'exécution):</label>
                                            <textarea id="commentaires_financiers" name="commentaires_financiers" rows="4" class="form-control"></textarea>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                        <fieldset >
                            <legend class=" form-control text-center font-weight-bold">VII. Commentaires</legend>
                            <div class="row">
                                <div class="col">
                                    <label for="commentaires_generaux">21. Commentaires d'ordre général:</label>
                                    <textarea id="commentaires_generaux" name="commentaires_generaux" rows="4" class="form-control"></textarea>
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
