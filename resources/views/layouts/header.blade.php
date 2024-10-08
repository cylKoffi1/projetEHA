<style>
    /* Profile Picture */
    .profile-pic {
        display: inline-block;
        vertical-align: middle;
        width: 50px;
        height: 50px;
        overflow: hidden;
        border-radius: 50%;
    }

    .profile-pic img {
        width: 100%;
        height: auto;
        object-fit: cover;
    }

    .profile-menu .dropdown-menu {
        right: 0;
        left: unset;
    }

    .profile-menu .fa-fw {
        margin-right: 10px;
    }

    .toggle-change::after {
        border-top: 0;
        border-bottom: 0.3em solid;
    }

    .navbar .navbar-nav {
        color: white;
    }

    .dropdown-menu a {
        color: #34495E;
    }

    .show {
        background-color: white;
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        color: white;
    }

    .toggle-change {
        color: #34495E
    }

    .show .nav-link {
        color: #34495E;
    }

</style>


<nav class="navbar navbar-expand-lg fixed-top navbar-light" style="z-index: 2000;  width: 100%; height: 90px; background-color: #435ebe;">
    <div class="container-fluid" style="align-items: center;">
        <a class="navbar-brand" href="{{ url('/')}}" style="color: white;">
            <img src="{{ asset('betsa/assets/images/ehaImages/armoirie.png')}}" style="width: 40px; height: auto; margin-right: 15px;" alt="" />GERAC-EHA
        </a>
        <span style="color: #F1C40F; display: flex; flex-direction: column; align-items: center;">

        <span>{{ auth()->user()?->personnel?->nom }} {{ auth()->user()?->personnel?->prenom }}</span>

        @if(auth()->user()?->personnel?->latestFonction)
            <span>{{ auth()->user()->personnel->latestFonction->fonctionUtilisateur->libelle_fonction ?? "" }}:  {{ $personnelAffiche }}</span>
        @endif

        </span>


        <header class="mb-3 navbar-toggler">
            <a href="{{ url('#')}}" class="burger-btn d-block d-xl-none">
                {{-- <i class="bi bi-justify fs-3"></i> --}}
                <span class="navbar-toggler-icon"></span>
            </a>
        </header>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        {{-- <div class="sidebar-toggler  x">
            <a href="{{ url('#')}}" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
        </div> --}}

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

            </ul>
            @if(auth()->check())
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 profile-menu" style="align-items: center">

                <li class="nav-item">
                    <a class="nav-link" style="color: white;" href="{{ url('/admin')}}">Accueil</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="{{ url('#')}}" id="navbarDropdown" style="display: flex; align-items: center;" role="button" data-bs-toggle="dropdown" aria-expanded="false">



                        <div style="display: flex; flex-direction: column; align-items: center; margin-right: 7px;">
                            <span style="color: #F1C40F;">{{ auth()->user()->login }} </span>
                            <span style="font-size: 13px; color: #F1C40F;">{{ auth()->user()->getRoleNames()->first() }}</span>
                        </div>
                        @if (auth()->user()->personnel->photo)
                            <div class="profile-pic">
                                <img src="{{ asset("users/".auth()->user()->personnel->photo) }}" alt="Profile Picture">
                            </div>

                        @else
                            <div class="profile-pic">
                                <img src="{{ asset("users/user.png") }}" alt="Profile Picture">
                            </div>
                        @endif


                        <!-- You can also use icon as follows: -->
                        <!--  <i class="fas fa-user"></i> -->
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="{{ url('/admin/users/details-user/' . auth()->user()->id . '?ecran_id=' . $ecran->id) }}"><i class="fas fa-sliders-h fa-fw"></i> Mon compte</a></li>
                        <li><a class="dropdown-item" href="{{ url('/admin/users/details-user/' . auth()->user()->id . '?ecran_id=' . $ecran->id) }}"><i class="fas fa-cog fa-fw"></i> Réglages</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="sidebar-item  ">
                            <a class="dropdown-item" href="{{ url('#')}}" onclick="logout('logout-form')">
                                <i class="bi bi-box-arrow-left"></i> Déconnexion
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                                <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>
                            </form>
                        </li>

                    </ul>
                </li>
            </ul>
            @else
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 profile-menu" style="align-items: center">
                <li class="nav-item">
                    <a class="nav-link" style="color: white;" href="{{ url('/sig')}}">SIG-EHA</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" style="color: white;" href="{{ url('/')}}">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" style="color: white;" href="{{ url('/connexion')}}">Connexion</a>
                </li>
            </ul>
            @endif
        </div>
    </div>
</nav>

<script>
    document.querySelectorAll('.dropdown-toggle').forEach(item => {
        item.addEventListener('click', event => {

            if (event.target.classList.contains('dropdown-toggle')) {
                event.target.classList.toggle('toggle-change');
            } else if (event.target.parentElement.classList.contains('dropdown-toggle')) {
                event.target.parentElement.classList.toggle('toggle-change');
            }
        })
    });

</script>
