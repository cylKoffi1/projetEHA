<!doctype html>
<html class="no-js" lang="en">

<head>
    <!-- meta data -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('layouts.lurl')

    <!-- Inclure Bootstrap CSS -->
    <link href="{{ asset('betsa/assets/css/bootstrap.min.css') }}" rel="stylesheet">

    <style>
        .video-background {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transform: translate(-50%, -50%);
            z-index: -1;
        }

        .welcome-hero-txt {
            top: 50%; /* Ajustez cette valeur selon vos besoins */
            left: 50%;
            z-index: 1;
            color: white;
            text-align: center; /* Centrer le texte */
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Ajustez l'opacité selon vos besoins */
            z-index: 0;
        }
    </style>
</head>

<body>
    <!--[if lte IE 9]>
        <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
    <![endif]-->

    <!-- Header -->
    <header>
        @include('layouts.menu')
    </header>

    <!-- Main Content -->
    <main>
        <section id="home" >
            <!-- Ajout de la vidéo en arrière-plan -->
            <video class="video-background" autoplay loop muted id="background-video">
                <source src="{{ asset('assets/BTP-Image/logoVideo.mp4') }}" type="video/mp4">
                Votre navigateur ne supporte pas la balise vidéo.
            </video>



            <div class="container" >
                <!--<div class="welcome-hero-txt">
                    <h2>GESTION DE PROJETS</h2>
                    <h2>DU BÂTIMENT ET DES TRAVAUX PUBLICS</h2>
                </div>-->
            </div>
        </section>
    </main>



    <!-- Include all js compiled plugins (below), or include individual files as needed -->
    <script src="{{ asset('betsa/assets/js/jquery.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
    <script src="{{ asset('betsa/assets/js/bootstrap.min.js')}}"></script>
    <script src="{{ asset('betsa/assets/js/bootsnav.js')}}"></script>
    <script src="{{ asset('betsa/assets/js/owl.carousel.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>
    <script src="{{ asset('betsa/assets/js/custom.js')}}"></script>
</body>
<script>
    const video = document.getElementById('background-video');
    video.playbackRate = 0.5;
</script>
</html>
