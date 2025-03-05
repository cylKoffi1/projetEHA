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
        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
        @include('layouts.menu')
        <section class="d-flex align-items-center justify-content-center" style="margin-top: 150px;">
            <div class="container" style="max-width: 800px;"> <!-- Limite la largeur globale -->
                <div class="card border-light-subtle shadow-sm">
                    <div class="row g-0">
                        <div class="col-12 col-md-6 text-bg-primary">
                            <div class="d-flex align-items-center justify-content-center h-100" style="margin-top: 50px;">
                                <div class="text-center">
                                    <img class="img-fluid rounded mb-4" loading="lazy" src="{{ asset('logoseul.png') }}" width="400" height="200" alt="Logo BTP Project">
                                    <h2 class="h1 mb-4 text-white" style="color:rgb(52, 96, 227);">BTP PROJECT</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6" style="background-color: #fff">
                            <br>
                            <div class="card-body p-4 col-md-12">
                                <div class="mb-4">
                                    <h3>Se connecter</h3>
                                </div><br>
                                <span class="login100-form-title p-b-48">
                                    @if ($errors->has('login'))
                                        <i class="zmdi zmdi-font"></i>
                                        <p class="text-danger">{{ $errors->first('login') }}</p>
                                    @endif
                                    @if (session('success'))
                                        <p class="text-success">{{ session('success') }}</p>
                                    @endif
                                </span>
                                <!-- Étape 1 : Identifiants -->
                                <div id="step-login">
                                    <div class="form-group">
                                        <label> <i class="fas fa-envelope"></i> Email :</label>
                                        <input type="email" id="email" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label><i class="fas fa-lock"></i> Mot de passe :</label>
                                        <input type="password" id="password" class="form-control" required>
                                    </div>

                                    <hr class="mt-4">
                                    <button type="submit" id="verify-login" class="btn btn-primary  w-100"  style="float: right;">Suivant</button><br>
                                    <div class="text-end">
                                        <a href="{{ route('password.request') }}" class="link-secondary text-decoration-none">Mot de passe oublié ?</a>
                                    </div><br>
                                </div>

                                <!-- Étape 2 : Sélection du Pays -->
                                <div id="step-country" class="hidden">
                                    <div class="form-group">
                                        <label><i class="fas fa-globe"></i> Pays :</label>
                                        <select id="country-select" class="form-control" style="height: 100%;">
                                            <option value="">Veuillez sélectionner un pays</option>
                                        </select>
                                    </div><br><br>
                                    <hr class="mt-4">
                                    <button id="prev-country" class="btn btn-primary w-100">Retour</button>
                                    <button id="next-country" class="btn btn-primary w-100" style="float: right;">Suivant</button><br>
                                    <div class="text-end">
                                        <br>
                                    </div><br>
                                </div>

                                <!-- Étape 3 : Sélection du Groupe Projet -->
                                <div id="step-group" class="hidden">
                                    <div class="form-group">
                                        <label><i class="fas fa-users"></i> Groupe Projet :</label>
                                        <select id="group-select" class="form-control" style="height: 100%;">
                                            <option value="">Veuillez sélectionner un groupe</option>
                                        </select>
                                    </div><br><br>
                                    <hr class="mt-4">
                                    <button id="prev-group" class="btn btn-primary w-100">Retour</button>
                                    <button id="next-group" class="btn btn-primary w-100" style="float: right;">Se connecter</button><br>
                                    <div class="text-end">
                                        <br>
                                    </div><br>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
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
<script src="{{ asset('betsa/vend/jquery/jquery-3.2.1.min.js') }}"></script>
<script src="{{ asset('betsa/vend/bootstrap/js/bootstrap.min.js') }}"></script>
<script>
    $(document).ready(function () {
        console.log('Vue initialisée : Étape 1 affichée.');

        // Étape 1 : Vérification des identifiants
        $('#verify-login').click(function () {
            const email = $('#email').val();
            const password = $('#password').val();

            if (!email || !password) {
                alert('Veuillez remplir les champs.');
                console.log('Erreur : Champs email ou mot de passe vide.');
                return;
            }

            console.log('Identifiants soumis :', { email, password });

            $.post("{{ route('login.check') }}", { email, password, _token: '{{ csrf_token() }}' }, function (response) {
                console.log('Réponse du serveur après vérification :', response);

                if (response.step === 'choose_country') {
                    console.log('Étape suivante : Sélection de pays.');
                    populateCountries(response.data);
                    $('#step-login').addClass('hidden');
                    $('#step-country').removeClass('hidden');
                } else if (response.step === 'finalize') {
                    console.log('Connexion finalisée. Redirection...');
                    window.location.href = "{{ route('projets.index') }}";
                }
            }).fail(function (xhr) {
                console.error('Erreur AJAX :', xhr.responseJSON.error);
                alert(xhr.responseJSON.error);
            });
        });

        // Étape 2 : Sélection d'un pays
        $('#next-country').click(function () {
            const selectedCountry = $('#country-select').val();

            if (!selectedCountry) {
                alert('Veuillez sélectionner un pays.');
                console.log('Erreur : Aucun pays sélectionné.');
                return;
            }

            console.log('Pays sélectionné :', selectedCountry);

            $.post("{{ route('login.selectCountry') }}", { pays_code: selectedCountry, _token: '{{ csrf_token() }}' }, function (response) {
                console.log('Réponse du serveur après sélection du pays :', response);

                if (response.step === 'choose_group') {
                    console.log('Étape suivante : Sélection de groupe projet.');
                    populateGroups(response.data);
                    $('#step-country').addClass('hidden');
                    $('#step-group').removeClass('hidden');
                } else if (response.step === 'finalize') {
                    console.log('Connexion finalisée. Redirection...');
                    window.location.href = "{{ route('projets.index') }}";
                }
            }).fail(function (xhr) {
                console.error('Erreur AJAX :', xhr.responseJSON.error);
                alert(xhr.responseJSON.error);
            });
        });

        // Étape 3 : Sélection d'un groupe projet
        $('#next-group').click(function () {
            const selectedGroup = $('#group-select').val();

            if (!selectedGroup) {
                alert('Veuillez sélectionner un groupe projet.');
                console.log('Erreur : Aucun groupe projet sélectionné.');
                return;
            }

            console.log('Groupe projet sélectionné :', selectedGroup);

            $.post("{{ route('login.selectGroup') }}", { projet_id: selectedGroup, _token: '{{ csrf_token() }}' }, function (response) {
                console.log('Réponse du serveur après sélection du groupe projet :', response);

                if (response.step === 'finalize') {
                    console.log('Connexion finalisée. Redirection...');
                    window.location.href = "{{ route('projets.index') }}";
                }
            }).fail(function (xhr) {
                console.error('Erreur AJAX :', xhr.responseJSON.error);
                alert(xhr.responseJSON.error);
            });
        });
        // Bouton Retour pour Pays -> Identifiants
        $('#prev-country').click(function () {
            $('#step-country').addClass('hidden');
            $('#step-login').removeClass('hidden');
        });

        // Bouton Retour pour Groupe Projet -> Pays
        $('#prev-group').click(function () {
            $('#step-group').addClass('hidden');
            $('#step-country').removeClass('hidden');
        });

        // Peupler les options de pays
        function populateCountries(countries) {
            console.log('Peuplement des pays :', countries);

            const select = $('#country-select');
            select.empty();
           // Trier le tableau des pays par ordre alphabétique
            countries.sort((a, b) => a.nom_fr_fr.localeCompare(b.nom_fr_fr));

            // Ajouter l'option par défaut
            select.append('<option value="" disabled selected>Veuillez sélectionner un pays</option>');

            // Ajouter les options triées
            countries.forEach(country => {
                select.append(`<option value="${country.alpha3}">${country.nom_fr_fr}</option>`);
            });

        }

        // Peupler les options de groupes projets
        function populateGroups(groups) {
            console.log('Peuplement des groupes projets :', groups);

            const select = $('#group-select');
            select.empty();
              // Trier le tableau des pays par ordre alphabétique
              groups.sort((a, b) => a.groupe_projet.libelle.localeCompare(b.groupe_projet.libelle));

            select.append('<option value="" disabled selected>Veuillez sélectionner un groupe projet</option>'); // Option par défaut

            groups.forEach(group => {
                if (group.groupe_projet && group.groupe_projet.libelle) {
                    // Si le libellé est présent, ajoutez l'option
                    select.append(`<option value="${group.groupe_projet_id}">${group.groupe_projet.libelle}</option>`);
                } else {
                    // Si les données sont invalides, loguez une erreur et affichez une option par défaut
                    console.error('Erreur : Données du groupe projet invalides.', group);
                    select.append(`<option value="${group.groupe_projet_id}">Groupe projet ID: ${group.groupe_projet_id}</option>`);
                }
            });
        }

    });
</script>
</body>
</html>
