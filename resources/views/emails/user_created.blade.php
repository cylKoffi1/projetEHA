<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur GP INFRAS</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Bonjour {{ $name }},</h2>

    <p>Bienvenue sur la plateforme <strong>GP INFRAS</strong> !</p>
    <p>Gestion de projet et infrastructure de pays</p>
    <p>Votre compte a été créé avec succès. Vous pouvez dès à présent vous connecter avec les identifiants suivants :</p>

    <ul>
        <li><strong>Login :</strong> {{ $login }}</li>
        <li><strong>Mot de passe temporaire :</strong> {{ $password }}</li>
    </ul>

    <p style="color: #d9534f;"><strong>⚠️ Veuillez changer votre mot de passe dès votre première connexion.</strong></p>
    <p style="color: #d9534f;"><strong>À défaut, votre compte sera automatiquement bloqué après la prochaine tentative.</strong></p>

    <p>👉 <a href="{{ $url }}" style="color: #0275d8;">Cliquez ici pour vous connecter</a></p>

    <br>

    <p>Merci pour votre confiance,</p>
    <p>L’équipe GP INFRAS</p>
</body>
</html>
