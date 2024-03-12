@if ($sousMenus->count() > 0)

    @foreach ($sousMenus as $sousMenu)
        @if($sousMenu->permission)
            @can($sousMenu->permission->name)
                <li class="nav-item">
                    <a href="#" class="nav-link nav-link-collapse" id="hasSubItems"
                    data-toggle="collapse"
                    data-target="#sous_sous_menu{{ $sousMenu->code }}"
                    aria-controls="sous_sous_menu{{ $sousMenu->code }}"
                    aria-expanded="false">{{ $sousMenu->libelle }}</a>
                    <ul class="nav-second-level collapse " data-parent="#sous_menu{{ $id_parent }}" id="sous_sous_menu{{ $sousMenu->code }}">
                        @include('partials.submenu', ['sousMenus' => $sousMenu->sousSousMenus, 'id_parent'=>$sousMenu->code])

                        @foreach ($sousMenu->ecrans as $ecran)
                            @if($ecran->permission)
                                @can($ecran->permission->name)
                                    <li class="nav-item">
                                        <a href="/admin/{{ $ecran->path }}?ecran_id={{ $ecran->id }}" class="nav-link">{{ $ecran->libelle }}</a>
                                    </li>                               
                                @endcan
                            @endif
                        @endforeach
                    </ul>
                </li>
            @endcan
        @endif
    @endforeach

@endif
