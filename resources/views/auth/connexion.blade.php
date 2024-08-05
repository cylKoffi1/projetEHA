<!doctype html>
<html class="no-js" lang="en">

<head>
    <!-- meta data -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('layouts.lurl')
    <link rel="stylesheet" type="text/css" href="{{asset('betsa/vend/animate/animate.css')}}">
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
    <link rel="stylesheet" type="text/css" href="{{asset('assets/compiled/css/main.css')}}">
    <!--===============================================================================================-->
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
                            GERAC-EHA
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

                        <div class="wrap-input100 validate-input" data-validate="Entrer le mot de passe">
                            <span class="btn-show-pass">
                                <i class="zmdi zmdi-eye"></i>
                            </span>
                            <input class="input100 @error('password') is-invalid @enderror" type="password" name="password" autocomplete="current-password">
                            <span class="focus-input100" data-placeholder="Mot de passe" autofocus></span>
                            @if ($errors->has('password'))
                            <span class="text-danger">{{ $errors->first('password') }}</span>
                            @endif
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
                                <div><a href="#" class="small text-right">Mot de passe oublié ?</a></div>
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
