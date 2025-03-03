
@if ($level == 1)
    @foreach ($sousMenus as $sous_menu)
    <tr class="rubrique_sub_row_{{ $sous_menu->code_rubrique }} sub-row" style="display: none;">
        <td></td>
        <td><i class="bi bi-arrow-right-circle-fill"></i> {{ $sous_menu->libelle }}</td>
        <td><button type="button" onclick="toggleSubMenu(this, 'sub_row_sub_row_{{ $sous_menu->code }}')">+</button></td>
        <td></td>
        <td></td>
        <td></td>
        <td><input type="checkbox" name="consulter_sous_menu" value="{{ $sous_menu->code }}"  id=""></td>
    </tr>

    @foreach ($sous_menu->ecrans as $ecran)
    <tr class="sub_row_sub_row_{{ $sous_menu->code }} sub-row" style="display: none;">
        <td>{{ $ecran->code }}</td>
        <td>{{ $ecran->libelle }}</td>
        <td></td>
        <td><input type="checkbox" class="ajouter_ecran_{{ $ecran->id }}" name="ajouter_sous_menu_ecran" value="{{ $ecran->id }}"  id=""></td>
        <td><input type="checkbox" class="modifier_ecran_{{ $ecran->id }}" name="modifier_sous_menu_ecran" value="{{ $ecran->id }}" id=""></td>
        <td><input type="checkbox" class="supprimer_ecran_{{ $ecran->id }}" name="supprimer_sous_menu_ecran" value="{{ $ecran->id }}" id=""></td>
        <td><input type="checkbox" name="consulter_sous_menu_ecran" value="{{ $ecran->id }}" id=""></td>
        <td></td>
    </tr>
    @endforeach

    @if ($sous_menu->sousSousMenusRecursive->count() > 0)
        @include('partials.row', ['sousMenus' => $sous_menu->sousSousMenusRecursive, 'level'=> $level +1])
    @endif


    @endforeach


@else

@foreach ($sousMenus as $sous_menu)
    <tr class="sub-row sub_row_sub_row_{{ $sous_menu->sous_menu_parent }}" style="display: none;">
        <td></td>
        <td><i class="bi bi-arrow-right-circle-fill"></i>{{ $sous_menu->libelle }}</td>
        <td><button type="button" onclick="toggleSubMenu(this, 'sub_row_sub_row_{{ $sous_menu->code }}')">+</button></td>
        <td></td>
        <td></td>
        <td></td>
        <td><input type="checkbox" name="consulter_sous_menu" value="{{ $sous_menu->code }}" id=""></td>

    </tr>

    @foreach ($sous_menu->ecrans as $ecran)
    <tr class="sub-row sub_row_sub_row_{{ $sous_menu->code }}" style="display: none;">
        <td>{{ $ecran->code }}</td>
        <td>{{ $ecran->libelle }}</td>
        <td></td>
        <td><input type="checkbox" class="ajouter_ecran_{{ $ecran->id }}" name="ajouter_sous_menu_ecran" value="{{ $ecran->id }}"  id=""></td>
        <td><input type="checkbox" class="modifier_ecran_{{ $ecran->id }}" name="modifier_sous_menu_ecran" value="{{ $ecran->id }}" id=""></td>
        <td><input type="checkbox" class="supprimer_ecran_{{ $ecran->id }}" name="supprimer_sous_menu_ecran" value="{{ $ecran->id }}" id=""></td>
        <td><input type="checkbox" name="consulter_sous_menu_ecran" value="{{ $ecran->id }}" id=""></td>
        <td></td>
    </tr>
    @endforeach
    @if ($sous_menu->sousSousMenusRecursive->count() > 0)
        @include('partials.row', ['sousMenus' => $sous_menu->sousSousMenusRecursive, 'level'=> $level +1])
    @endif


@endforeach



@endif
