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
    /* styles.css */

/* Style général pour le conteneur */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;

    border-radius: 10px;
}

/* Style pour la carte */
.card {
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    overflow: hidden;
    background-color: rgba(255, 255, 255, 0.8);
}

/* Style pour le titre de la carte */
.card-body h3 {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

/* Style pour les champs de saisie */
.form-control {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}


/* Style pour le bouton */
.btn-primary {
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: #0056b3;
}

/* Style pour les messages d'erreur */
.text-danger {
    font-size: 14px;
    color: #dc3545;
}

/* Style pour le lien "Mot de passe oublié" */
.link-secondary {
    color: #007bff;
    text-decoration: none;
}

.link-secondary:hover {
    text-decoration: underline;
}

/* Espacement pour les éléments */
.mb-5 {
    margin-bottom: 3rem;
}

.mt-5 {
    margin-top: 3rem;
}

.gy-3 {
    gap: 1rem; /* Espacement vertical */
}

.gy-md-4 {
    gap: 1.5rem; /* Espacement vertical pour les écrans moyens et plus */
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

        @include('layouts.menu')

        <section class="d-flex align-items-center justify-content-center" style="margin-top: 150px;">
            <div class="container" style="max-width: 800px;"> <!-- Limite la largeur globale -->
                <div class="card border-light-subtle shadow-sm">
                    <div class="row g-0">
                        <div class="col-12 col-md-6 text-bg-primary">
                            <div class="d-flex align-items-center justify-content-center h-100" style="margin-top: 50px;">
                                <div class="text-center">
                                    <img class="img-fluid rounded mb-4" loading="lazy" src="{{ asset('logoseul.png') }}" width="200" height="200" alt="Logo BTP Project">
                                    <h2 class="h1 mb-4 text-white">BTP Project</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6" style="background-color: #fff">
                            <br>
                            <div class="card-body p-4 col-md-12">
                                <div class="mb-4">
                                    <h3>Se connecter</h3>
                                </div><br>

                                <!-- Messages d'erreurs ou de succès -->
                                <span class="login100-form-title p-b-48">
                                    @if ($errors->has('login'))
                                        <i class="zmdi zmdi-font"></i>
                                        <p class="text-danger">{{ $errors->first('login') }}</p>
                                    @endif
                                    @if (session('success'))
                                        <p class="text-success">{{ session('success') }}</p>
                                    @endif
                                </span>

                                <form id="login-form" >
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label for="email">Adresse email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            name="email" id="email" placeholder="Votre email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="password">Mot de passe <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            name="password" id="password" placeholder="Votre mot de passe" required>
                                        @error('password')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>


                                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                                </form>


                                <hr class="mt-4">
                                <div class="text-end">
                                    <a href="{{ route('password.request') }}" class="link-secondary text-decoration-none">Mot de passe oublié ?</a>
                                </div><br>
                            </div>

                            <!-- Modal Pays -->
                            <div class="modal" id="choose-country-modal" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form id="choose-country-form">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Sélectionnez un Pays</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <label for="country-select">Pays :</label>
                                                <select name="pays_id" id="country-select" class="form-control"></select>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Continuer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Groupe Projet -->
                            <div class="modal" id="choose-group-modal" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <form id="choose-group-form">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Sélectionnez un Groupe Projet</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <label for="group-select">Groupe Projet :</label>
                                                <select name="projet_id" id="group-select" class="form-control"></select>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">Continuer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>




        </div>
    </section>
    <!--===============================================================================================-->
    <script src="{{asset('betsa/vend/jquery/jquery-3.2.1.min.js')}}"></script>
    <!--=============g==================================================================================-->
    <script src="{{asset('betsa/vend/animsition/js/animsition.min.js')}}"></script>
    <!--===============================================================================================-->
    <script src="{{asset('betsa/vend/bootstrap/js/popper.js')}}"></script>
    <script src="{{asset('betsa/vend/bootstrap/js/bootstrap.min.js')}}"></script>
    <!--===============================================================================================-->
    <script src="{{asset('betsa/vend/select2/select2.min.js')}}"></script>
    <!--===============================================================================================-->
    <script src="{{asset('betsa/vend/daterangepicker/moment.min.js')}}"></script>
    <script src="{{asset('betsa/vend/daterangepicker/daterangepicker.js')}}"></script>
    <!--===============================================================================================-->
    <script src="{{asset('betsa/vend/countdowntime/countdowntime.js')}}"></script>
    <!--===============================================================================================-->
    <script src="{{asset('betsa/assets/js/main.js')}}"></script>
</body>
</html>

@section('scripts')
<script>
    $('#login-form').submit(function(e) {
        e.preventDefault();

        $.post("{{ route('login.check') }}", $(this).serialize(), function(response) {
            if (response.step === 'choose_country') {
                $('#choose-country-modal').modal('show');
                populateCountries(response.data);
            } else if (response.step === 'choose_group') {
                $('#choose-group-modal').modal('show');
                populateGroups(response.data);
            } else if (response.step === 'finalize') {
                window.location.href = "{{ route('login.finalize') }}";
            }
        }).fail(function(xhr) {
            alert(xhr.responseJSON.error);
        });
    });

    function populateCountries(countries) {
        const select = $('#country-select');
        select.empty();
        countries.forEach(country => {
            select.append(`<option value="${country.id}">${country.pays.nom_fr_fr}</option>`);
        });
    }

    function populateGroups(groups) {
        const select = $('#group-select');
        select.empty();
        groups.forEach(group => {
            select.append(`<option value="${group.id}">${group.groupeProjet.libelle}</option>`);
        });
    }
</script>
@endsection
