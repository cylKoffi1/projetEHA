<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur BTP-PROJECT</title>
</head>
<body>
    <h2>Bienvenue, {{ $name }} !</h2>
    <p>Votre compte a été créé avec succès sur la plateforme.</p>
    <p>Voici vos identifiants de connexion :</p>
    <ul>
        <li><strong>Email :</strong> {{ $email }}</li>
        <li><strong>Mot de passe temporaire :</strong> {{ $password }}</li>
    </ul>
    <p><strong>⚠️ Important :</strong> Vous devez changer votre mot de passe immédiatement après votre première connexion.</p>
    <p><a href="{{ $url }}">Se connecter</a></p>
    <p>Merci et à bientôt !</p>
</body>
</html>
