<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-container {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .title {
            color: #007bff;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .content {
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background: linear-gradient(135deg, #0056b3, #004494);
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 class="title">GP-INFRAS</h1>
            <h2>Réinitialisation de mot de passe</h2>
        </div>

        <div class="content">
            <p>Bonjour,</p>
            
            <p>Vous avez demandé à réinitialiser votre mot de passe pour votre compte GP-INFRAS.</p>
            
            <p>Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
            
            <div style="text-align: center;">
                <a href="{{ $actionUrl ?? $url }}" class="button">
                    Réinitialiser mon mot de passe
                </a>
            </div>
            
            <p>Ou copiez et collez ce lien dans votre navigateur :</p>
            <p style="word-break: break-all; color: #007bff; font-size: 12px;">{{ $actionUrl ?? $url }}</p>
            
            <div class="warning">
                <strong>⚠️ Important :</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Ce lien est valable pendant <strong>60 minutes</strong> uniquement</li>
                    <li>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email</li>
                    <li>Ne partagez jamais ce lien avec quelqu'un d'autre</li>
                </ul>
            </div>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>&copy; {{ date('Y') }} GP-INFRAS. Tous droits réservés.</p>
        </div>
    </div>
</body>
</html>

