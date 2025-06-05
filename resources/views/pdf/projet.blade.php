<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche Projet - {{ $projet->code_projet }}</title>
    <style>
        @page {
            margin: 120px 30px 100px 30px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        header {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            height: 100px;
            background-color: #000046;
            color: white;
            padding: 10px 20px;
        }

        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 60px;
            font-size: 10px;
            text-align: center;
            color: #7f8c8d;
            background-color: white;
        }

        .page-number:after {
            content: "Page " counter(page) ;
        }

        .content-wrapper {
            padding: 0 20px;
        }

        .section {
            margin: 20px 0;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #3498db;
            color: white;
            padding: 5px 10px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th, td {
            padding: 6px;
            vertical-align: top;
        }

        th {
            background-color: #ecf0f1;
            font-weight: bold;
        }

        .label {
            font-weight: bold;
            width: 30%;
            color: #2c3e50;
            background-color: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-primary {
            background-color: #d4edda;
            color: #155724;
        }

        p {
            margin: 5px 0;
        }

        h4 {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

    <!-- Header global (répété) -->
    <header>
        <table width="100%">
            <tr>
                <td style="font-size: 12px; font-weight: bold;">BTP-PROJECT</td>
                <td style="text-align: right; font-size: 11px;">
                    Impression le : {{ \Carbon\Carbon::now()->translatedFormat('d F Y à H:i') }}
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center; font-size: 15px; font-weight: bold; padding-top: 5px;">
                    FICHE PROJET DÉTAILLÉE
                </td>
            </tr>
            <tr>
                <td>
                    @if(auth()->user()?->paysSelectionne()?->armoirie)
                        <img src="{{ public_path(auth()->user()?->paysSelectionne()?->armoirie) }}" style="height: 35px;">
                    @endif
                </td>
                <td style="text-align: right; font-size: 11px;">
                    Imprimé par : {{ auth()->user()->acteur?->libelle_court ?? '' }} {{ auth()->user()->acteur?->libelle_long ?? '' }}
                </td>
            </tr>
        </table>
    </header>

    <!-- Footer global (répété) -->
    <footer>
        <div class="page-number"></div>
        Document généré le {{ now()->format('d/m/Y à H:i') }} |
        Réf. PROJ-{{ $projet->code_projet }} |
        Toute reproduction interdite
    </footer>

    <!-- Contenu principal -->
    <div class="content-wrapper">

        <!-- Section 1: Informations générales -->
        <div class="section">
            <div class="section-title">INFORMATIONS GÉNÉRALES</div>
            <table>
                <tr><td class="label">Code projet</td><td>{{ $projet->code_projet }}</td></tr>
                <tr><td class="label">Intitulé</td><td>{{ $projet->libelle_projet }}</td></tr>
                <tr>
                    <td class="label">Statut</td>
                    <td><span class="badge badge-primary">{{ $projet->statuts->statut->libelle ?? 'Non défini' }}</span></td>
                </tr>
                <tr>
                    <td class="label">Période</td>
                    <td>
                        Du {{ \Carbon\Carbon::parse($projet->date_demarrage_prevue)->format('d/m/Y') }} 
                        au {{ \Carbon\Carbon::parse($projet->date_fin_prevue)->format('d/m/Y') }}
                        ({{ \Carbon\Carbon::parse($projet->date_demarrage_prevue)->diffInDays($projet->date_fin_prevue) }} jours)
                    </td>
                </tr>
                <tr>
                    <td class="label">Budget total</td>
                    <td>{{ number_format($projet->cout_projet, 0, ',', ' ') }} {{ $projet->code_devise }}</td>
                </tr>
                <tr>
                    <td class="label">Chef de projet</td>
                    <td>
                        {{ $projet->ChefProjet?->acteur->libelle_court ?? 'Non attribué' }}
                        @if($projet->ChefProjet)
                            (Tél: {{ $projet->ChefProjet?->acteur->telephone ?? 'NC' }})
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Description</td>
                    <td>{{ $projet->commentaire ?? 'Aucune description' }}</td>
                </tr>
            </table>
        </div>

        <!-- Section 2: Localisations -->
        <div class="section">
            <div class="section-title">LOCALISATIONS</div>
            @if($projet->localisations->count() > 0)
                <table>
                    <thead>
                        <tr><th>Localité</th><th>Niveau</th><th>Découpage</th><th>Pays</th></tr>
                    </thead>
                    <tbody>
                        @foreach($projet->localisations as $loc)
                        <tr>
                            <td>{{ $loc->localite->libelle ?? $loc->code_localite }}</td>
                            <td>{{ $loc->niveau }}</td>
                            <td>{{ $loc->decoupage ?? 'N/A' }}</td>
                            <td>{{ $loc->pays->nom_fr_fr ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Aucune localisation enregistrée pour ce projet.</p>
            @endif
        </div>

        <!-- Section 3: Intervenants -->
        <div class="section">
            <div class="section-title">INTERVENANTS</div>
            <table>
                <thead>
                    <tr><th>Rôle</th><th>Acteur</th><th>Contact</th><th>Secteur</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Maître d'ouvrage</td>
                        <td>{{ $projet->maitreOuvrage->acteur->libelle_court ?? 'Non défini' }}</td>
                        <td>
                            {{ $projet->maitreOuvrage->acteur->telephone ?? 'NC' }}<br>
                            {{ $projet->maitreOuvrage->acteur->email ?? 'NC' }}
                        </td>
                        <td>{{ $projet->maitreOuvrage->secteur->libelle ?? 'NC' }}</td>
                    </tr>
                    @foreach($projet->maitresOeuvre as $moe)
                    <tr>
                        <td>Maître d'œuvre</td>
                        <td>{{ $moe->acteur->libelle_court }}</td>
                        <td>
                            {{ $moe->acteur->telephone ?? 'NC' }}<br>
                            {{ $moe->acteur->email ?? 'NC' }}
                        </td>
                        <td>{{ $moe->secteur->libelle ?? 'NC' }}</td>
                    </tr>
                    @endforeach
                    <tr>
                        <td>Chef de projet</td>
                        <td>{{ $projet->ChefProjet?->acteur->libelle_court }}</td>
                        <td>
                            {{ $projet->ChefProjet?->acteur->telephone ?? 'NC' }}<br>
                            {{ $projet->ChefProjet?->acteur->email ?? 'NC' }}
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Section 4: Financements -->
        <div class="section">
            <div class="section-title">FINANCEMENTS</div>
            @if($projet->financements->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Bailleur</th><th>Montant</th><th>Devise</th><th>Type</th>
                            <th>Date engagement</th><th>Commentaire</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projet->financements as $financement)
                        <tr>
                            <td>{{ $financement->bailleur->libelle_court }}</td>
                            <td>{{ number_format($financement->montant_finance, 0, ',', ' ') }}</td>
                            <td>{{ $financement->devise }}</td>
                            <td>{{ $financement->financement_local ? 'Local' : 'Externe' }}</td>
                            <td>{{ $financement->date_engagement ? $financement->date_engagement->format('d/m/Y') : 'N/A' }}</td>
                            <td>{{ $financement->commentaire ?? 'Aucun' }}</td>
                        </tr>
                        @endforeach
                        <tr style="font-weight: bold;">
                            <td colspan="5" style="text-align: right;">Total</td>
                            <td>{{ number_format($projet->financements->sum('montant_finance'), 0, ',', ' ') }} {{ $projet->code_devise }}</td>
                        </tr>
                    </tbody>
                </table>
            @else
                <p>Aucun financement enregistré pour ce projet.</p>
            @endif
        </div>

        <!-- Section 5: Infrastructures -->
        <div class="section">
            <div class="section-title">INFRASTRUCTURES</div>
            @if($projet->infrastructures->count() > 0)
                @foreach($projet->infrastructures as $infras)
                    <div style="margin-bottom: 15px;">
                        <h4>{{ $infras->infra?->libelle }}</h4>
                        <p><strong>Famille:</strong> {{ $infras->infra?->familleInfrastructure->libelleFamille ?? 'N/A' }}</p>
                        @if($infras->valeursCaracteristiques->count() > 0)
                            <table>
                                <thead><tr><th>Caractéristique</th><th>Valeur</th><th>Unité</th></tr></thead>
                                <tbody>
                                    @foreach($infras->valeursCaracteristiques as $carac)
                                    <tr>
                                        <td>{{ $carac->caracteristique->nom }}</td>
                                        <td>{{ $carac->valeur }}</td>
                                        <td>{{ $carac->unite->symbole ?? '' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>Aucune caractéristique technique enregistrée.</p>
                        @endif
                    </div>
                @endforeach
            @else
                <p>Aucune infrastructure enregistrée pour ce projet.</p>
            @endif
        </div>

        <!-- Section 6: Documents -->
        <div class="section">
            <div class="section-title">DOCUMENTS ASSOCIÉS</div>
            @if($projet->documents->count() > 0)
                <table>
                    <thead><tr><th>Nom du fichier</th><th>Type</th><th>Taille</th><th>Date d'upload</th></tr></thead>
                    <tbody>
                        @foreach($projet->documents as $document)
                        <tr>
                            <td>{{ $document->file_name }}</td>
                            <td>{{ $document->file_type }}</td>
                            <td>{{ round($document->file_size / 1024, 2) }} KB</td>
                            <td>{{ $document->uploaded_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Aucun document associé à ce projet.</p>
            @endif
        </div>

    </div>
</body>
</html>
