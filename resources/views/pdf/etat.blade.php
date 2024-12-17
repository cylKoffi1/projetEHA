<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>État des Heures Complémentaires</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <header>
        <img src="{{ $logo }}" alt="Logo" style="width: 100px;">
        <h1>État des Heures Complémentaires</h1>
        <p>Département : {{ $departement }}</p>
        <p>Établissement : {{ $etablissement }}</p>
        <p>Année Académique : {{ $annee_academique }}</p>
    </header>

    <table>
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Grade</th>
                <th>Statut</th>
                <th>Volume HC</th>
                <th>Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($enseignants as $enseignant)
                <tr>
                    <td>{{ $enseignant['matricule'] }}</td>
                    <td>{{ $enseignant['nom'] }}</td>
                    <td>{{ $enseignant['grade'] }}</td>
                    <td>{{ $enseignant['statut'] }}</td>
                    <td>{{ $enseignant['volume_hc'] }}</td>
                    <td>{{ number_format($enseignant['montant'], 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
