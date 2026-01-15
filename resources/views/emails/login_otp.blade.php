<p>Bonjour {{ $user->login ?? $user->email }},</p>

<p>
    Voici votre code de vérification pour vous connecter à la plateforme
    <strong>GP-INFRAS</strong> :
</p>

<p style="font-size: 20px; font-weight: bold; letter-spacing: 2px;">
    {{ $code }}
</p>

<p>
    Ce code est valable pendant <strong>10 minutes</strong>.
    Si vous n'êtes pas à l'origine de cette tentative de connexion,
    ignorez cet email.
</p>

<p>
    Cordialement,<br>
    <strong>L'équipe GP-INFRAS</strong>
</p>