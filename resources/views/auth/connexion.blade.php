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
.container-login100 {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

/* Style pour le formulaire */
.wrap-login100 {
    width: 100%;
    max-width: 400px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    padding: 40px;
}

/* Style pour le titre */
.login100-form-title {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    text-align: center;
    margin-bottom: 20px;
}

/* Style pour les champs de saisie */
.input100 {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
    transition: border-color 0.3s ease;
}

.input100:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

/* Style pour le bouton */
.login100-form-btn {
    width: 100%;
    padding: 10px;
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.login100-form-btn:hover {
    background-color: #0056b3;
}

/* Style pour le lien "Mot de passe oublié" */
.text-right {
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
}

.text-right:hover {
    text-decoration: underline;
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

        <div class="limiter">
            <div class="container-login100">
                <div class="wrap-login100">
                    <form class="login100-form validate-form" action="{{ route('login.login') }}" method="post">
                        @csrf
                        <span class="login100-form-title p-b-26">
                            BTP-PROJECT
                        </span>
                        <span class="login100-form-title p-b-48">
                            @if ($errors->has('login')) <i class="zmdi zmdi-font"></i>
                            <p class="text-danger">{{ $errors->first('login') }}</p>
                            @endif
                            @if (session('success'))
                            <p class="text-danger">{{ session('success') }}</p>
                            @endif
                        </span>

                        <div class="wrap-input100 validate-input" data-validate="Login invalide">
                            <input class="input100 @error('login') is-invalid @enderror" type="text" name="login">
                            <span class="focus-input100" data-placeholder="Login"></span>
                        </div>
                            </div>
                        </div>
                        <div class="col-8 col-md-6 ">
                            <div class="card-body col-md-10 p-3 p-md-6 p-xl-8">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-5">
                                            <h3>Se connecter</h3>
                                        </div>
                                    </div>
                                </div>
                                <form action="{{ route('login.login') }}" method="post">
                                    <input type="hidden" name="_token" value="I3KGMjGAJUhP6s5bPq8Xb4cg05xPoApajFW1sdCy" autocomplete="off">                                    <div class="row gy-3 gy-md-4 overflow-hidden">
                                        <div class="col-8">
                                            <label for="login" class="form-label">Login <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control " name="login" id="login" placeholder="Votre login" required="">
                                                                                    </div>

                        <div class="container-login100-form-btn">
                            <div class="wrap-login100-form-btn">
                                <div class="login100-form-bgbtn"></div>
                                <button class="login100-form-btn" type="submit">
                                    Se connecter
                                </button>
                            </div>
                        </div>


                        <div class="text-center p-t-115">
                            <div class="d-flex justify-content-between align-items-top mb-4">
                                <div><a href="{{ route('password.request') }}" class="small text-right">Mot de passe oublié ?</a></div>
                            </div>
                        </div>
                    </form>
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
</body>
</html>
