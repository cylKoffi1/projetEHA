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
    <style>
        body{
            background-color: #DBECF8;
        }

    .card-body {
        background-color: #EAF2F8;
    }
    </style>
</head>

<body class="welcom-hero">
    @include('layouts.header')
    <div class="container mt-5" >

        <div class="row justify-content-center">
            <div class="col-md-6" style="margin-top: 100px;">
                <div class="card">
                    <div class="card-header">Mot de passe oublié</div>
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="card-body">
                        <form action="{{ route('password.email') }}" method="post">
                            @csrf
                           <div class="form-group">
                                <label for="email">Votre addresse Email</label>
                                <input type="email" class="form-control" placeholder="Entrer votre email" id="email" name="email" required>
                            </div>
                            <div class="col-12 text-end btn-page">
                                <button type="submit" class="btn btn-primary">Envoyer</button>
                            </div>

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

</body>
</html>
