<!doctype html>
<html class="no-js" lang="en">

<head>
    <!-- meta data -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('layouts.lurl')
  {{-- <link rel="stylesheet" type="text/css" href="{{asset('betsa/vend/animate/animate.css')}}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{asset('betsa/vend/css-hamburgers/hamburgers.min.css')}}">
    <link rel="stylesheet" href="{{ asset('betsa/vend/bootstrap/css/bootstrap.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{asset('betsa/vend/animsition/css/animsition.min.css')}}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{asset('betsa/vend/select2/select2.min.css')}}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{asset('betsa/vend/daterangepicker/daterangepicker.css')}}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{asset('assets/compiled/css/util.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('assets/compiled/css/main.css')}}">--}}
    <!--===============================================================================================-->

<style>

/* Header de la carte */
.card-header {
    background-color:rgb(21, 96, 176);
    color: #fff;
    font-size: 1.25rem;
    text-align: center;
    padding: 10px;
    border-bottom: 1px solid #ddd;
}

/* Corps de la carte */
.card-body {
    background-color: #ffffff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Formulaire */
.form-group {
    margin-bottom: 15px;
}

label {
    font-weight: bold;
}

.form-control {
    width: 100%;
    padding: 10px;
    font-size: 1rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
}

.form-control:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
}

.invalid-feedback {
    color: #e3342f;
    font-size: 0.875rem;
}

.alert {
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-size: 1rem;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

/* Bouton */
.btn-primary {
    background-color: #007bff;
    color: #fff;
    border: none;
    padding: 10px 20px;
    font-size: 1rem;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-primary:hover {
    background-color: #0056b3;
}

/* Section principale */
.welcome-hero {
    padding: 50px 0;
}

.d-flex {
    display: flex;
    align-items: center;
    justify-content: center;
}

.container {
    padding: 15px;
}

.card {
    border: 1px solid #ddd;
    border-radius: 5px;
}

/* Responsive */
@media (max-width: 768px) {
    .col-md-4, .col-md-6, .col-md-8 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .text-md-right {
        text-align: left;
    }

    .offset-md-4 {
        margin-left: 0;
    }
}

</style>
</head>

<body>
    <!--[if lte IE 9]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
        <![endif]-->

    <!--welcome-hero start -->
    <!--/.welcome-hero-->
    <!--welcome-hero end -->

    <section id="home" class="welcome-hero">

        <!-- top-area Start -->
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        @include('layouts.menu')
        <section class="d-flex align-items-center justify-content-center" style="margin-top: 150px;">
            <div class="container"  style="max-width: 800px;">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">Réinitialiser le mot de passe</div>

                            <div class="card-body">
                                @if (session('status'))
                                    <div class="alert alert-success" role="alert">
                                        {{ session('status') }}
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('password.email') }}">
                                    @csrf

                                    <div class="form-group row">
                                        <label for="email" class="col-md-4 col-form-label text-md-right">Adresse e-mail</label>

                                        <div class="col-md-6">
                                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row mb-0">
                                        <div class="col-md-8 offset-md-4">
                                            <button type="submit" class="btn btn-primary">
                                                Envoyer le lien de réinitialisation
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
</body>

</html>


