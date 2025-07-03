<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche Infrastructure</title>
    <style>
        html, body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
            padding-bottom: 80px;
        }
        .section {
            margin: 20px;
        }
        .section-title {
            background-color: #3498db;
            color: white;
            padding: 6px 10px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .info-grid td {
            padding: 6px 8px;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #2c3e50;
            width: 30%;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th, .table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }
        .table th {
            background-color: #eee;
        }
        .footer {
            position: fixed;
            bottom: 0;
            font-size: 10px;
            text-align: center;
            color: #777;
            border-top: 1px solid #ccc;
            padding: 8px 0;
            width: 100%;
        }
    </style>
</head>
<body>
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
                        FICHE TECHNIQUE - INFRASTRUCTURE
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


    <!-- INFRASTRUCTURE DETAILS -->
    <div class="section">
        <div class="section-title">INFORMATIONS GÉNÉRALES</div>
        <table class="info-grid">
            <tr>
                <td style="width: 70%;">
                    <table>
                        <tr><td class="label">Groupe projet :</td><td>{{ $infrastructure->groupeProjet?->libelle ?? '-' }}</td></tr>
                        <tr><td class="label">Famille d'infrastructure :</td><td>{{ $infrastructure->familleInfrastructure->libelleFamille ?? '-' }}</td></tr>
                        <tr><td class="label">Code :</td><td>{{ $infrastructure->code }}</td></tr>
                        <tr><td class="label">Libelle :</td><td>{{ $infrastructure->libelle }}</td></tr>
                        <tr><td class="label">Localisation :</td><td>{{ $infrastructure->localisation->libelle ?? '-' }}</td></tr>
                        <tr><td class="label">Date de mise en service :</td><td>{{ $infrastructure->date_operation ? \Carbon\Carbon::parse($infrastructure->date_operation)->format('d/m/Y') : '-' }}</td></tr>
                    </table>
                </td>
                <td style="width: 30%; text-align: center;">
                    <div class="qr-code-container">
                        <p style="font-weight: bold; margin-bottom: 8px;">QR Code d’identification</p>
                        <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code" style="width: 150px; height: 150px;"><br>
                        <small class="text-muted">Code ID : {{ $infrastructure->code }}</small>
                    </div>
                </td>
            </tr>
        </table>



    </div>

    <!-- CARACTÉRISTIQUES -->
    <div class="section">
        <div class="section-title">CARACTÉRISTIQUES TECHNIQUES</div>
        @if($infrastructure->valeursCaracteristiques->isEmpty())
            <p>Aucune caractéristique enregistrée.</p>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Caractéristique</th>
                        <th>Valeur</th>
                        <th>Unité</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($infrastructure->valeursCaracteristiques as $caract)
                        <tr>
                            <td>{{ $caract->caracteristique->type->libelleTypeCaracteristique ?? '-' }}</td>
                            <td>{{ $caract->caracteristique->libelleCaracteristique ?? '-' }}</td>
                            <td>{{ $caract->valeur }}</td>
                            <td>
                                {{ $caract->caracteristique->unite ? $caract->caracteristique->unite->libelleUnite . ' (' . $caract->caracteristique->unite->symbole . ')' : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="section">
        <div class="section-title">HISTORIQUES PROJETS</div>
       
            <table class="table"> 
                <thead>
                    <tr>
                        <th>Code projet</th>
                        <th>Nature des travaux</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Coût du projet</th>
                        <th>Devise</th>
                    </tr>
                </thead>
                @if(!empty($projets))
                <tbody>
                    @foreach($projets as $projet)
                        <tr>
                            <td>{{ $projet['code_projet'] }}</td>
                            <td>{{ $projet['nature'] }}</td>
                            <td>{{ $projet['date_debut'] }}</td>
                            <td>{{ $projet['date_fin'] }}</td>
                            <td>{{ $projet['cout'] }}</td>
                            <td>{{ $projet['devise'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @else
                    <p>Aucun projet associé.</p>
                @endif
            </table>
       
    </div>

    <!-- FOOTER -->
    <div class="footer">
        Document généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }} |
        Fiche infrastructure n°{{ 'INFRA-' . str_pad($infrastructure->id, 5, '0', STR_PAD_LEFT) }} |
        Toute reproduction interdite.
    </div>

</body>
</html>
