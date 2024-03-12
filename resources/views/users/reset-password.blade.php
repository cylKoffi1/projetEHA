</body>
</html>


<!doctype html>
<html class="no-js" lang="en">

<head>
    <!-- meta data -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    {{-- @include('layouts.lurl') --}}
    
</head>

<body class="welcom-hero">
    @include('layouts.header') 
    <div class="container mt-5" >
        <div class="row justify-content-center">
            <div class="col-md-6" style="margin-top: 100px;">
                <div class="card">
                    <div class="card-header">Créer un nouveaux mot de passe</div>
                    <div class="card-body">
                        <form action="#" method="post">
                            <div class="form-group">
                                <label for="email">Votre addresse Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Envoyer</button>
                        </form>
                        <div class="text-center p-t-115">
                            <div class="d-flex justify-content-between align-items-top mb-4 small">
                                <div>Vous connaissez votre mot de passe ? <a href="/connexion" class="text-right">Se connecter</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script src="{{ asset('assets/compiled/js/myjs.js')}}"></script>
    <script src="{{ asset('assets/compiled/js/app.js')}}"></script>

    </section>
</body>
</html>
