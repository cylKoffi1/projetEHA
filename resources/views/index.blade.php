<!doctype html>
<html class="no-js" lang="en">

<head>
    <!-- meta data -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('layouts.lurl')

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


        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Sélectionnez l'élément du lien "Projet"
                var projetDropdown = document.getElementById("projet-dropdown");

                // Sélectionnez l'élément de la liste déroulante
                var subMenu = document.querySelector("ul.hidden");

                // Ajoutez un gestionnaire d'événement pour le survol du lien "Projet"
                projetDropdown.addEventListener("mouseover", function() {
                    // Affichez la liste déroulante
                    subMenu.style.display = "block";
                });

                // Ajoutez un gestionnaire d'événement pour le déplacement de la souris hors du lien "Projet"
                projetDropdown.addEventListener("mouseout", function() {
                    // Masquez la liste déroulante
                    subMenu.style.display = "none";
                });
            });

        </script>

        <!-- top-area End -->

        <div class="container">
            <div class="welcome-hero-txt" >
                <h2>BTP-PROJECT</h2>
                <h2>
                    Bâtiment Travaux Public - Projects
                </h2>


                <!-- Ajoutez autant d'images que vous le souhaitez -->
            </div>
        </div>
        </div>




    </section>
    <!-- Include all js compiled plugins (below), or include individual files as needed -->

    <script src="{{ asset('betsa/assets/js/jquery.js')}}"></script>

    <!--modernizr.min.js-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>

    <!--bootstrap.min.js-->
    <script src="{{ asset('betsa/assets/js/bootstrap.min.js')}}"></script>

    <!-- bootsnav js -->
    <script src="{{ asset('betsa/assets/js/bootsnav.js')}}"></script>

    <!--owl.carousel.js-->
    <script src="{{ asset('betsa/assets/js/owl.carousel.min.js')}}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>

    <!--Custom JS-->
    <script src="{{ asset('betsa/assets/js/custom.js')}}"></script>


</body>

</html>
