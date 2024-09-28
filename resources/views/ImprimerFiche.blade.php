
@if (session('success'))
<script>
    alert("{{ session('success') }}");

</script>
@endif<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin-top: 0;
    margin-right:350px;
    margin-left: 350px;

        background-color: #f4f4f4;
    }
    .container {
        overflow: hidden;
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h1, h2, h3 {
        color: #333;
        text-align: center;
        margin-bottom: 20px;
    }
    label {
        font-weight: bold;
    }
    input[type="text"],
    input[type="email"],
    input[type="tel"],
    input[type="date"],
    select,
    textarea {
        width: 40%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
        height: 57px;
        font-size: 12px;
    }
    input[type="submit"] {
        background-color: #007bff;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        display: block;
        margin: auto;
    }
    input[type="submit"]:hover {
        background-color: #0056b3;
    }


    /* Ajout d'une bordure autour de la forme */
    form {
    border: 2px solid #333;
    border-radius: 10px;
    padding: 20px;
    }

    h2,h4{
    text-align: center;
    font-family: Arial, Helvetica, sans-serif;
    background-color: #333;
    color: white;

    }
    .centrale label {
    display: inline-block;
    width:  223px; /* Ajustez la largeur selon vos besoins */
    vertical-align: top; /* Alignement vertical en haut */
    }

    .centrale input[type="text"],input[type="date"],
    select,
    textarea  {
    width: 100% ;
    height: 50%; /* La largeur totale du conteneur moins la largeur de l'étiquette */
    }

    /* Ajustement pour les h2 dans la première section */

    .regions {
    border: 1px solid #ccc;
    display: grid;
    grid-template-columns: auto 1fr; /* Taille automatique pour les labels et espace restant pour les champs de saisie */
    gap: 10px;


    }
    .centrale {
    border: none/*1px solid #ccc*/;
    display: grid;
    grid-template-columns: auto 1fr; /* Taille automatique pour les labels et espace restant pour les champs de saisie */
    gap: 10px;
        height: 10%;
    }

    /* Ajustement pour le premier div afin de ne pas avoir de marge en haut */
    form > div:first-child {
    margin-top: 0;
    }
    li{
    list-style: none;
    }

    label{
    width: 300px;
    height: 100px;
    font-family: sans-serif;
    font-size: 15px;
    }
    input{
    background-color: #f2f2f2;
    }
    .coche{
    justify-self: auto;
    }
    .coche {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Colonnes de taille flexible */
    gap: 20px; /* Espacement entre les colonnes */
    }

    .coche ul {
    list-style-type: none; /* Supprimer les puces de la liste */
    padding: 0; /* Supprimer les marges intérieures de la liste */
    }

    .coche ul li {
    margin-bottom: 5px; /* Espacement entre les éléments de la liste */
    }

    a{
    margin-bottom: 30px;
    }
    .btn-imprimer {
        background-color: #007bff;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        display: block;
        margin: auto;
    }
    .btn-imprimer:hover {
        background-color: #0056b3;
    }
    header .row {
    height: 37px;
    }

    header img {
        height: 57px;
    }


</style>
<body>
<div class="container">
        <header>
            <div class="row" >
                <div class="col">
                    <img src="{{ asset('betsa/assets/images/ehaImages/armoirie.png')}}" alt="Logo" style="max-width: 100px;">
                </div>
                <div class="col" style="text-align: right;">
                    <p>Impression le 24/04/2024</p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <h3>Fiche Programme/Projet</h3>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p>Nom de l'entreprise: GERAC-EHA</p>
                </div>
                <div class="col" style="text-align: right;">
                    <p>Imprimé par: {{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}</p>
                </div>
            </div>
        </header>




        <form >
            <h4>I. Données générales sur le projet</h4>
            <div class="centrale">
                <label for="bailleur">1. Bailleur de Fonds:</label>
                <textarea type="text" id="bailleur" name="bailleur" readonly>@foreach ($donnees->bailleursProjets as $bailleurProjet)@foreach ($bailleurProjet->bailleurss as $bailleur){{ $bailleur->libelle_long ?? 'Aucun bailleur' }}@if (!$loop->last),
                            @endif
                        @endforeach
                    @endforeach
                </textarea>
            </div>

            @foreach ($donnees->agences as $agence)
                @if($agence->niveau == 1 || $agence->niveau == 2)
                    <div class="centrale">
                        <label for="agence{{ $agence->niveau }}">{{ $agence->niveau == 1 ? '2' : '3' }}. Agence(s) d'exécution Niveau ({{ $agence->niveau }}) :</label>
                        <textarea type="text" id="agence{{ $agence->niveau }}" name="agence{{ $agence->niveau }}" readonly>{{ $agence->agenceExecution->nom_agence ?? '' }}@if (!$loop->last),
                            @endif
                        </textarea>
                    </div>
                @endif
            @endforeach


            <div class="centrale">
                <label for="ministere">4. Ministère(s) de tutelle:</label>
                <textarea type="text" id="ministere" name="ministere" readonly>@php $ministeres = ''; @endphp@foreach($donnees->ministereProjet ?? [] as $ministere)@php $ministeres .= $ministere->ministere->libelle ?? ''; @endphp@if(!$loop->last)@php $ministeres .= ', '; @endphp @endif @endforeach{{ $ministeres }}</textarea>

            </div>


            <h4>II. Contacts</h4>


            <div class="centrale">
                <label for="nom_contact">5. Nom et prénoms:</label>
                <input type="text" id="nom_contact" name="nom_contact" readonly value="@if(isset($donnees->projetChefProjet) && !empty($donnees->projetChefProjet))@foreach ($donnees->projetChefProjet as $chef)@if(isset($chef->Personne) && !empty($chef->Personne))@foreach ($chef->Personne as $chefPer){{ $chefPer->nom }} {{ $chefPer->prenom }}
                                    @endforeach
                                @endif
                            @endforeach
                        @endif
                    ">

            </div>
            <div class="centrale">
                <label for="adresse_contact">6. Adresse :</label>
                <input type="text" id="adresse_contact" name="adresse_contact" readonly value="@if(isset($donnees->projetChefProjet) && !empty($donnees->projetChefProjet))@foreach ($donnees->projetChefProjet as $chef)@if(isset($chef->Personne) && !empty($chef->Personne))@foreach ($chef->Personne as $chefPer){{ $chefPer->adresse }}
                                @endforeach
                            @endif
                        @endforeach
                    @endif
                ">
            </div>

            <div class="centrale">
                <label for="tel_contact">7. Téléphone:</label>
                <input type="text" id="tel_contact" name="tel_contact" readonly value="@if(isset($donnees->projetChefProjet) && !empty($donnees->projetChefProjet))@foreach ($donnees->projetChefProjet as $chef)@if(isset($chef->Personne) && !empty($chef->Personne))@foreach ($chef->Personne as $chefPer){{ $chefPer->telephone }}
                                @endforeach
                            @endif
                        @endforeach
                    @endif
                ">
            </div>
            <div class="centrale">
                <label for="email_contact">8. Email :</label>
                <input type="text" id="email_contact" name="email_contact" readonly value="@if(isset($donnees->projetChefProjet) && !empty($donnees->projetChefProjet))@foreach ($donnees->projetChefProjet as $chef)@if(isset($chef->Personne) && !empty($chef->Personne))@foreach ($chef->Personne as $chefPer){{ $chefPer->email }}
                                @endforeach
                            @endif
                        @endforeach
                    @endif
                ">
            </div>
            <h4>III. Projet </h4>
            <div class="centrale">
                <label for="intitule">9. Code du Projet:</label>
                <input type="text" id="intitule" name="intitule" readonly value="{{ $donnees->CodeProjet }}">
            </div>
            <div class="centrale">
                <label for="statut">10. Statut du Projet:</label>
                <input type="text" id="statut" name="statut" readonly value="@foreach ($donnees->latestStatutProjet as $statuts){{ $statuts->statut->libelle ?? '' }}@endforeach">

            </div>

            <h4>IV. Niveau d'intervention </h4>
            @if ($donnees->code_district == 99 || $donnees->code_region ==99)
            <div class="centrale">
                <label for="niveau_mise_oeuvre">12. Niveau de mise en œuvre:</label>
                <input type="text" id="niveau_mise_oeuvre" name="niveau_mise_oeuvre" value="Nationnal" readonly>
            </div>
            <div class="centrale">
                <label for="">13. Régions </label>
                <input type="text" id="regional" name="regional" readonly value="Toutes les régions">
            </div>
            @else
            <div class="centrale">
                <label for="niveau_mise_oeuvre">12. Niveau de mise en œuvre:</label>
                <input type="text" id="niveau_mise_oeuvre" name="niveau_mise_oeuvre" value="Regional" readonly>
            </div>
            <div class="centrale">
                <label for="">13. Régions </label>
                <input type="text" id="regional" name="regional" readonly value="{{$donnees->region->libelle}}">
            </div>
            @endif



            <h4>V. Période d'activité </h4>
            <div class="centrale">
                <label for="">14. Date de démarrage prévue:</label>
                <input type="date" readonly value="{{$donnees->Date_demarrage_prevue}}">
            </div>
            <div class="centrale">
                <label for="">15. Date de démarrage effective:</label>
                <input type="date" readonly value="@foreach ($donnees->dateDebutEffective as $dates){{ $dates->date ?? '' }}@endforeach">
            </div>
            <div class="centrale">
                <label for="">16. Date de fin prévue:</label>
                <input type="date" readonly value="{{$donnees->date_fin_prevue}}">
            </div>
            <div class="centrale">
                <label for="">17. Date de fin effective:</label>
                <input type="date" readonly value="@foreach ($donnees->dateFinEffective as $dates){{ $dates->date ?? '' }}@endforeach">
            </div>
            <h4>VI. Budget </h4>
            <div class="centrale">
                <label for="">18. Engagement global coût du Projet</label>
                <input type="text" style="text-align: right;" readonly value="{{ number_format($donnees->cout_projet, 0, ',', ' ') }}">
            </div>
            <div class="centrale">
                <label for="">19. Monnaie</label>
                <input type="text" value="{{$donnees->devise->monnaie}}  ({{$donnees->devise->code_long}})">
            </div>
            <div class="centrale">
                <label for="">20. Commentaires sur la situation financière :</label>
                <input type="text">
            </div>
            <h4>VII. Commentaires </h4>
            <div class="centrale">
                <label for="">21. Commentaires d'ordre général:</label>
                <input type="text">
            </div>

        </form>
    </div>
</body>


    <center><input type="button" class="btn-imprimer" value="Imprimer"></center>


    <script src="{{ asset('betsa/js/html2pdf.bundle.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
  const { jsPDF } = window.jspdf;
  const codeProjet = "{{ $donnees->CodeProjet }}";
  window.onload = function() {
    const printButton = document.querySelector('.btn-imprimer');

    printButton.addEventListener('click', function() {
      // Masquer le bouton avant la capture
      printButton.style.display = 'none';

      // Capturer la page
      html2canvas(document.body).then(function(canvas) {
        const imgData = canvas.toDataURL('image/png');

        const pdf = new jsPDF();

        // Obtenir la taille du PDF (A4 par défaut : 210 x 297 mm)
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = pdf.internal.pageSize.getHeight();

        // Définir la largeur de l'image à 80% de la largeur du PDF
        const imgWidth = pdfWidth * 1.1; // 80% de la largeur du PDF

        // Obtenir la taille de l'image capturée
        const canvasWidth = canvas.width;
        const canvasHeight = canvas.height;

        // Calculer le ratio de l'image (largeur / hauteur)
        const imgRatio = canvasWidth / canvasHeight;

        // Calculer la hauteur correspondante pour maintenir les proportions
        const imgHeight = imgWidth / imgRatio;

        // Centrer l'image verticalement si nécessaire
        const xPos = (pdfWidth - imgWidth) / 2;
        const yPos = (pdfHeight - imgHeight) / 2;

        // Ajouter l'image avec la largeur de 80% et la hauteur proportionnelle
        pdf.addImage(imgData, 'PNG', xPos, yPos, imgWidth, imgHeight);

        // Réafficher le bouton après la capture
        printButton.style.display = 'block';

        // Télécharger le PDF
        pdf.save('fiche_collecte_' + codeProjet + '.pdf');
      });
    });
  };
</script>

<script>
    $(document).ready(function() {

        initDataTable('{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}', 'table1', 'Annexe 1: infomations secondaires');
    });
</script>
