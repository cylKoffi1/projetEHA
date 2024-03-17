<!-- confirm_identity_form.blade.php -->

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation d'identité</title>
</head>
<body>
    <div>
        <h2>Confirmer votre identité</h2>
        <p>Veuillez confirmer que vous êtes bien le propriétaire de cette adresse e-mail :</p>
        <form action="{{ route('password.confirm_identity') }}" method="post">
            @csrf
            <input type="hidden" name="confirm" value="true">
            <button type="submit">Confirmer</button>
        </form>
    </div>
</body>
</html>
