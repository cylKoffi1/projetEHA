<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de Contrat</title>
    <style>
        html, body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            height: 100%;
            margin: 0;
            padding: 0;
            position: relative;
            padding-bottom: 80px; /* pour laisser de la place au footer */
        }
        .info-section {
            margin: 20px;
        }
        /* Spécifique contrat */
        .contract-clauses {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
            border-left: 4px solid #2c3e50;
        }
        .clause-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .section-title {
            background-color: #3498db;
            color: white;
            padding: 5px 10px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .info-grid td {
            padding: 8px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            width: 30%;
            color: #2c3e50;
        }
        .signature-block {
            margin: 40px 20px 20px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            width: 45%;
            text-align: center;
            padding-top: 40px;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 80%;
            margin: 0 auto;
            padding-top: 5px;
        }
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            font-size: 10px;
            text-align: center;
            color: #7f8c8d;
            border-top: 1px solid #ccc;
            padding: 10px 0;
        }
    </style>
</head>
<body>

    <!-- HEADER style 'Liste des acteurs' -->
    <div style="background-color: #000046; color: white;">
        <table width="100%" style="background-color: #000046; color: white; padding: 10px 20px;">
            <tr>
                
                <td style="vertical-align: top;">
                    @if(auth()->user()?->paysSelectionne()?->armoirie)
                        <img src="{{ public_path(auth()->user()?->paysSelectionne()?->armoirie) }}" style="height: 50px; width:50px;">
                    @endif
                </td>
                <td style="width: 50%; text-align: right; font-size: 11px;">
                    Impression le : {{ \Carbon\Carbon::now()->translatedFormat('d F Y à H:i') }}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; padding-top: 10px;">
                    <span style="font-size: 15px; font-weight: bold;">
                        FICHE DE CONTRAT - CHEF DE PROJET
                    </span>
                </td>
            </tr>
            <tr>
                <td style="width: 50%; font-size: 12px; font-weight: bold;">
                    BTP-PROJECT
                </td>
                <td style="text-align: right; vertical-align: bottom; font-size: 11px; padding-top: 10px;">
                    Imprimé par : {{ auth()->user()->acteur?->libelle_court ?? '' }} {{ auth()->user()->acteur?->libelle_long ?? '' }}
                </td>
            </tr>
        </table>
    </div>


    <div class="info-section">
        <div class="section-title">INFORMATIONS DU PROJET</div>
        <table class="info-grid">
            <tr>
                <td class="label">Code projet :</td>
                <td>{{ $contrat?->code_projet }}</td>
            </tr>
            <tr>
                <td class="label">Intitulé :</td>
                <td>{{ $contrat?->projet->libelle_projet ?? '' }}
                </td>
            </tr>
            <tr>
                <td class="label">Client :</td>
                <td>{{ $contrat?->projet->maitreOuvrage?->acteur?->libelle_court ?? '' }} {{ $contrat?->projet->maitreOuvrage?->acteur?->libelle_long ?? '' }}</td>
            </tr>            
            <tr>
                <td class="label">Localisation</td>
                <td>
                    @foreach($contrat?->projet->localisations as $loc)
                        {{ $loc->localite?->libelle }} ({{ $loc->localite?->decoupage->libelle_decoupage }})<br>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <div class="section-title">CHEF DE PROJET</div>
        <table class="info-grid">
            <tr>
                <td class="label">Nom :</td>
                <td>{{ $contrat?->acteur->libelle_court ?? '' }} {{ $contrat?->acteur->libelle_long ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Fonction :</td>
                <td>Chef de Projet</td> 
            </tr>
            <tr>
                <td class="label">Date de fonction :</td>
                <td>Du: {{ $contrat?->date_debut ?? '' }} au {{ $contrat?->date_fin ?? '' }}</td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <div class="section-title">PÉRIODE DE CONTRAT</div>
        <table class="info-grid">
            <tr>
                <td class="label">Date de début :</td>
                <td>{{ \Carbon\Carbon::parse($contrat?->date_debut)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Date de fin :</td>
                <td>{{ \Carbon\Carbon::parse($contrat?->date_fin)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Durée :</td>
                <td>{{ \Carbon\Carbon::parse($contrat?->date_debut)->diffInDays(\Carbon\Carbon::parse($contrat?->date_fin)) }} jours</td>
            </tr>
        </table>
    </div>
    <div class="contract-clauses" style="margin-top: -1px; padding: 0 50px;">
            <div class="clause-title">Article 1 - Objet du contrat</div>
            <p>Le présent contrat a pour objet de définir les conditions dans lesquelles le Chef de Projet exercera ses fonctions pour le compte de l'Employeur sur le projet désigné ci-dessus.</p>
            
            <div class="clause-title">Article 2 - Missions</div>
            <p>Le Chef de Projet sera responsable de la bonne exécution du projet dans le respect des délais, coûts et qualités définis dans le cahier des charges.</p>
            
            <div class="clause-title">Article 3 - Confidentialité</div>
            <p>Le Chef de Projet s'engage à garder confidentielle toute information relative au projet et à l'Employeur.</p>
        </div>
    <!-- SIGNATURES -->
    <table width="100%" style="margin-top: 50px; padding: 0 20px;">
        <tr>
            <td style="width: 45%; text-align: center;">
                Le Chef de Projet<br>
                <div style="border-top: 1px solid #000; width: 80%; margin: 20px auto 5px;"></div>
                Nom et signature
            </td>
            <td style="width: 45%; text-align: center;">
                Le Représentant Légal<br>
                <div style="border-top: 1px solid #000; width: 80%; margin: 20px auto 5px;"></div>
                Nom et signature
            </td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div style="
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        font-size: 10px;
        text-align: center;
        color: #7f8c8d;
        border-top: 1px solid #ccc;
        padding: 10px 0;
        background-color: white;">
        Document généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }} |
        Fiche de contrat n°{{ 'C-' . str_pad($contrat?->id, 6, '0', STR_PAD_LEFT) }} |
        Toute reproduction interdite
    </div>

</body>
</html>
