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
            position: relative;
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
        @media screen and (max-width: 1440px) {
            .welcome-hero-txt {
                padding: 395px 0px 110px;
            }
        }
        @media screen and (max-width: 1024px) {
            .welcome-hero-txt {
                padding: 247px 0px 110px;
            }
        }
        @media screen and (max-width: 426px) {
            .welcome-hero-txt {
                padding: 247px 0px 110px;
            }
            .welcome-hero-txt h2 {
                    font-size: 22px;
                }
        }
        @media screen and (max-width: 2000px) {
            .welcome-hero-txt {
                padding: 300px 0px 110px;
            }
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
          

            <div class="container" >
                 <div class="welcome-hero-txt" >
                 <h2>GESPRO-INFRAS</h2>
                    <h2>DGeston</h2>
                </div>
            </div>
        </section>

    </main>

   <footer >
   
    
    </footer>


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
