{{-- Sideb --}}
@if(auth()->check())
<style type="text/css">
    .nav-link:hover {
        transition: all 0.4s;
    }

    .nav-link-collapse:after {
        float: right;
        content: '\f067';
        font-family: 'FontAwesome';
        font-size: 10px;
        margin-left: 10px;
        padding-right: 15px;
        text-align: center;
    }

    .nav-link-show:after {
        float: right;
        content: '\f068';
        font-family: 'FontAwesome';
        font-size: 10px;
        margin-left: 10px;
        padding-right: 15px;
        text-align: center;
    }

    .nav-item ul.nav-second-level {
        padding-left: 0;
    }

    .nav-item a {
        text-align: center;
    }

    .nav-item ul.nav-second-level>.nav-item {
        padding-left: 20px;
    }

    @media (min-width: 992px) {
        .sidenav {
            position: absolute;
            top: 0;
            left: 0;
            padding-left: 20px;
            height: calc(100vh - 3.5rem);
            margin-top: 3.5rem;
            box-sizing: border-box;
        }

        .navbar-expand-lg .sidenav {
            flex-direction: column;
        }

        .content-wrapper {
            margin-left: 230px;
        }

        .footer {
            width: calc(100% - 230px);
            margin-left: 230px;
        }
    }
    .nav-second-level .collapse .show{
        background-color: #435EBE !important;
    }
    .show {
        background-color: white !important;
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        color: white !important;
    }

    .show li {
        list-style: none;
    }

    .navbar-nav .nav-link {
        padding-right: 3px;
        padding-left: 3px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .nav-link-content {
        display: flex;
        align-items: center;
        text-align: center;
        justify-content: space-between;
    }

    .bi {
        width: auto;
        height: auto;
        float: inline-start;
        margin-bottom: 7px;
        padding-right: 15px;
    }

    @media (min-width: 1200px) {

        .sidebar-toggle {
            display: none;
        }
    }
</style>


<div id="sidebar">
    <div class="sidebar-wrapper active" style="margin-top: 90px;" id="sidebar">
        <ul class="navbar-nav mr-auto sidenav" id="navAccordion" style="width: 100%;">
            @php
            $pays = session('pays_selectionne');

            $userPermissions = DB::table('role_permission_pays AS rpp')
                ->join('permissions AS p', 'p.id', '=', 'rpp.permission_id')
                ->where('rpp.role_code', auth()->user()->groupeUtilisateur->code)
                ->where('rpp.pays_alpha3', $pays)
                ->pluck('p.name');
            @endphp
           
            @foreach ($rubriquesByAuthRole as $rubrique)
                @if ($rubrique->permission && $userPermissions->contains($rubrique->permission->name))
                    <li class="nav-item">
                        <a href="#" class="nav-link nav-link-collapse" data-toggle="collapse" data-target="#rubrique_{{ $rubrique->code }}" aria-controls="rubrique_{{ $rubrique->code }}">
                            <div class="nav-link-content">
                                <i class="{{ $rubrique->class_icone ?? 'bi-gear' }}"></i>
                                <span>{{ $rubrique->libelle }}</span>
                            </div>
                        </a>
                        <ul class="nav-second-level collapse" data-parent="#navAccordion" id="rubrique_{{ $rubrique->code }}">
                            @foreach ($rubrique->sousMenus as $sousMenu)
                                @if ($sousMenu->permission && $userPermissions->contains($sousMenu->permission->name))
                                    <li class="nav-item">
                                        <a href="#" class="nav-link nav-link-collapse" data-toggle="collapse" data-target="#sous_menu{{ $sousMenu->code }}" aria-controls="sous_menu{{ $sousMenu->code }}">
                                            {{ $sousMenu->libelle }}
                                        </a>
                                        <ul class="nav-second-level collapse" id="sous_menu{{ $sousMenu->code }}">
                                            @include('partials.submenu', ['sousMenus' => $sousMenu->sousSousMenus, 'id_parent' => $sousMenu->code])
                                            @foreach ($sousMenu->ecrans as $ecran)
                                                @if ($ecran->permission && $userPermissions->contains($ecran->permission->name))
                                                    <li class="nav-item">
                                                        <a href="{{ url('/admin/' . $ecran->path . '?ecran_id=' . $ecran->id) }}" class="nav-link">{{ $ecran->libelle }}</a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                            @endforeach
                            @foreach ($rubrique->ecrans as $ecran)
                                @if ($ecran->permission && $userPermissions->contains($ecran->permission->name))
                                    <li class="nav-item">
                                        <a href="{{ url('/admin/' . $ecran->path . '?ecran_id=' . $ecran->id) }}" class="nav-link">{{ $ecran->libelle }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endforeach
            <li class="nav-link" onclick="logout('logout-form_side')" style="padding-bottom: 150px;">
                <a href="#" class="nav-link" onclick="logout('logout-form_side')">
                    <i class="bi bi-box-arrow-left" style="color: red;"></i>
                    <span style="color: red;">Déconnexion</span>
                </a>
                <form id="logout-form_side" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        </ul>
    </div>
    
   
</div>

<script>
    $(document).ready(function () {
        $('.nav-link-collapse').on('click', function () {
            $('.nav-link-collapse').not(this).removeClass('nav-link-show');
            $(this).toggleClass('nav-link-show');
        });
    });

    function logout(formId) {
        document.getElementById(formId).submit();
    }
</script>
<script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("active");
    }
</script>
@endif
