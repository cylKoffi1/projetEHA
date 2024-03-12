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

@php
    use App\Models\CouvrirRegion;
    use App\Models\Region;
    // Utiliser une requête SQL brute pour obtenir le montant total des projets par statut
    $resultats = DB::select("SELECT `code_statut_projet`, SUM(`projet_eha2`.`cout_projet`) as montant_total FROM `projet_statut_projet`
        INNER JOIN `projet_eha2` ON `projet_statut_projet`.`code_projet` = `projet_eha2`.`CodeProjet`
        GROUP BY `code_statut_projet`");

    // Convertir les résultats en tableau associatif
    $montantParStatut = [];
    foreach ($resultats as $resultat) {
        $montantParStatut[$resultat->code_statut_projet] = $resultat->montant_total;
    }

    // Récupérer les montants pour chaque statut de projet
    $projets_prevus = isset($montantParStatut['01']) ? $montantParStatut['01'] : 0;
    $projets_en_cours = isset($montantParStatut['02']) ? $montantParStatut['02'] : 0;
    $projets_annulé = isset($montantParStatut['03']) ? $montantParStatut['03'] : 0;
    $projets_cloture = isset($montantParStatut['04']) ? $montantParStatut['04'] : 0;
    $projets_suspendus = isset($montantParStatut['05']) ? $montantParStatut['05'] : 0;
    $projets_redemarrer = isset($montantParStatut['06']) ? $montantParStatut['06'] : 0;

    // Récupérer le code région de l'utilisateur
    $region = CouvrirRegion::where('code_personnel', auth()->user()->personnel->code_personnel)->first();
    $code_region = $region ? $region->code_region : null;

    // Déclaration de la variable $personnelAffiche
    $personnelAffiche = '';

    // Switch pour déterminer la valeur de $personnelAffiche en fonction du groupe utilisateur
    switch (auth()->user()->latestFonction->fonctionUtilisateur->code) {
        case 'ad': //admin
            $personnelAffiche ='';
            break;

        case 'cp': // Chef de projet
            $personnelAffiche = 'Personnel';
            break;
        case 'ba': // Bailleur
            // Récupérer les données du bailleur
            $bailleur = BailleursProjet::where('code_bailleur', auth()->user()->personnel->code)->first();
            $personnelAffiche = $bailleur ? $bailleur->libelle_long : '';
            break;
        case 'dc': // Directeur de cabinet
            // Récupérer le nom de la région de l'utilisateur
            $ministere = CouvrirRegion::where('code_personnel', auth()->user()->personnel->code_personnel)->first();
            if ($ministere) {
                // Si le ministere est trouvée, récupérer son libellé depuis la table Region
                $regionInfo = Region::where('code', $region->code_region)->first();
                $personnelAffiche = $regionInfo ? $regionInfo->libelle : 'Ministère';
            }
            break;
        case 'dr': // Directeur Régional
        // Récupérer le nom de la région de l'utilisateur
        $region = CouvrirRegion::where('code_personnel', auth()->user()->personnel->code_personnel)->first();
        if ($region) {
            // Si la région est trouvée, récupérer son libellé depuis la table Region
            $regionInfo = Region::where('code', $region->code_region)->first();
            $personnelAffiche = $regionInfo ? $regionInfo->libelle : 'Directeur Régional';
        }
    }
@endphp
<nav class="navbar navbar-expand-lg fixed-top navbar-light" style="z-index: 2000;  width: 100%; height: 90px; background-color: #435ebe;">
    <div class="container-fluid" style="align-items: center;">
        <a class="navbar-brand" href="/" style="color: white;">
            <img src="{{ asset('betsa/assets/images/ehaImages/armoirie.png')}}" style="width: 40px; height: auto; margin-right: 15px;" alt="" />GERAC-EHA
        </a>
        <span style="color: #F1C40F; display: flex; flex-direction: column; align-items: center;">
            <span>{{ auth()->user()->personnel->nom }} {{ auth()->user()->personnel->prenom }}</span>
            <span>{{ auth()->user()->personnel->latestFonction->fonctionUtilisateur->libelle_fonction ?? "" }} {{ $personnelAffiche }}</span>

        </span>
        <header class="mb-3 navbar-toggler">
            <a href="#" class="burger-btn d-block d-xl-none">
                {{-- <i class="bi bi-justify fs-3"></i> --}}
                <span class="navbar-toggler-icon"></span>
            </a>
        </header>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        {{-- <div class="sidebar-toggler  x">
            <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
        </div> --}}

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

            </ul>
            @if(auth()->check())
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 profile-menu" style="align-items: center">

                <li class="nav-item">
                    <a class="nav-link" style="color: white;" href="/admin">Accueil</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" style="display: flex; align-items: center;" role="button" data-bs-toggle="dropdown" aria-expanded="false">



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
                        <li><a class="dropdown-item" href="/admin/users/details-user/{{ auth()->user()->id }}"><i class="fas fa-sliders-h fa-fw"></i> Mon compte</a></li>
                        <li><a class="dropdown-item" href="/admin/users/details-user/{{ auth()->user()->id }}"><i class="fas fa-cog fa-fw"></i> Réglages</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li class="sidebar-item  ">
                            <a class="dropdown-item" href="#" onclick="logout('logout-form')">
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
                    <a class="nav-link" style="color: white;" href="/sig">SIG-EHA</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" style="color: white;" href="/">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" style="color: white;" href="/connexion">Connexion</a>
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
