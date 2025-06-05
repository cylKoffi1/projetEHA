<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche Acteur - {{ $acteur->code_acteur }}</title>
    <style>
        @page {
            margin: 100px 30px 80px 30px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
        }

        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 80px;
            background-color: #000046;
            color: white;
            padding: 10px 20px;
        }

        footer {
            position: fixed;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 40px;
            font-size: 10px;
            text-align: center;
            color: #7f8c8d;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        .coat-of-arms {
            height: 35px;
        }

        h1 {
            font-size: 16px;
            text-align: center;
            margin: 5px 0;
            text-transform: uppercase;
        }

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #3498db;
            color: white;
            padding: 6px 10px;
            font-weight: bold;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th {
            background-color: #f8f9fa;
            text-align: left;
            padding: 6px;
            border: 1px solid #ddd;
        }

        td {
            padding: 6px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            color: #2c3e50;
            width: 30%;
        }

        .photo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 1px solid #ccc;
        }

        .info-grid td {
            border: none;
        }

        .contact-box {
            background-color: #f9f9f9;
            padding: 8px;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<!-- Header -->
<header>
    <table width="100%">
        <tr>
            <td style="font-weight: bold;">BTP-PROJECT</td>
            <td style="text-align: right;">Impression le : {{ now()->translatedFormat('d F Y à H:i') }}</td>
        </tr>
        <tr>
            <td>
                @if(auth()->user()?->paysSelectionne()?->armoirie)
                    <img src="{{ public_path(auth()->user()->paysSelectionne()->armoirie) }}" class="coat-of-arms">
                @endif
            </td>
            <td style="text-align: right;">
                Imprimé par : {{ auth()->user()->acteur->libelle_court ?? 'Système' }}
            </td>
        </tr>
    </table>
    <h1>FICHE ACTEUR</h1>
</header>

<!-- Footer -->
<footer>
    Document généré le {{ now()->format('d/m/Y à H:i') }} |
    Réf. ACT-{{ $acteur->code_acteur }} |
    Toute reproduction interdite
</footer>

<!-- Contenu principal -->
<main style="margin-top: 20px;">

    <!-- Infos générales -->
    <div class="section">
        <div class="section-title">INFORMATIONS GÉNÉRALES</div>
        <table class="info-grid">
            <tr>
                <td class="label">Code</td>
                <td>{{ $acteur->code_acteur }}</td>
                <td rowspan="4" style="text-align: right;">
                    @if($acteur->photo && file_exists(public_path($acteur->photo)))
                        <img src="{{ public_path($acteur->photo) }}" class="photo">
                    @endif
                </td>
            </tr>
            <tr><td class="label">Nom</td><td>{{ $acteur->libelle_long }}</td></tr>
            <tr><td class="label">Type</td><td>{{ $acteur->type->libelle ?? 'Non défini' }}</td></tr>
            <tr><td class="label">Statut</td><td>{{ $acteur->is_active ? 'Actif' : 'Inactif' }}</td></tr>
        </table>
        <div class="contact-box">
            <strong>Coordonnées :</strong><br>
            Adresse : {{ $acteur->adresse ?? '—' }}<br>
            Tél : {{ $acteur->telephone ?? '—' }}<br>
            Email : {{ $acteur->email ?? '—' }}
        </div>
    </div>

    <!-- Secteurs -->
    @if($acteur->secteurActiviteActeur->count())
    <div class="section">
        <div class="section-title">SECTEURS D'ACTIVITÉ</div>
        <table>
            <thead><tr><th>Secteur</th><th>Spécialité</th></tr></thead>
            <tbody>
                @foreach($acteur->secteurActiviteActeur as $secteur)
                <tr>
                    <td>{{ $secteur->secteur->libelle ?? '—' }}</td>
                    <td>{{ $secteur->specialite ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Projets associés -->
    @php
        $projetsAssocies = collect();

        foreach ($acteur->projetsChef as $item) {
            $projetsAssocies->push([
                'projet' => $item->projet,
                'role' => 'Chef de Projet',
                'date' => $item->date_debut ?? $item->projet->date_demarrage_prevue
            ]);
        }

        foreach ($acteur->projetsOuvrage as $item) {
            $projetsAssocies->push([
                'projet' => $item->projet,
                'role' => 'Maître d’Ouvrage',
                'date' => $item->created_at ?? $item->projet->date_demarrage_prevue
            ]);
        }

        foreach ($acteur->projetsOeuvre as $item) {
            $projetsAssocies->push([
                'projet' => $item->projet,
                'role' => 'Maître d’Œuvre',
                'date' => $item->created_at ?? $item->projet->date_demarrage_prevue
            ]);
        }

        foreach ($acteur->projetsFinances as $item) {
            $projetsAssocies->push([
                'projet' => $item->projet,
                'role' => 'Bailleur',
                'date' => $item->date_engagement ?? $item->created_at
            ]);
        }

        foreach ($acteur->projetsApprouves as $item) {
            $projetsAssocies->push([
                'projet' => $item->etude?->projet,
                'role' => 'Approbateur',
                'date' => $item->created_at ?? now()
            ]);
        }

        $projetsAssocies = $projetsAssocies->filter(fn($p) => $p['projet'])->unique(fn($p) => $p['projet']->code_projet . $p['role']);
    @endphp

    @if($projetsAssocies->count())
    <div class="section">
        <div class="section-title">PROJETS ASSOCIÉS</div>
        <table>
            <thead><tr><th>Code</th><th>Libellé</th><th>Rôle</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($projetsAssocies as $entry)
                <tr>
                    <td>{{ $entry['projet']->code_projet }}</td>
                    <td>{{ $entry['projet']->libelle_projet }}</td>
                    <td>{{ $entry['role'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($entry['date'])->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

</main>

</body>
</html>
