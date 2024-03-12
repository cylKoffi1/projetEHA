<tr>
    <td></td>
    <td>{{ $sousMenu->libelle }}</td>
    <td><input type="checkbox" name="" id=""></td>
    <td><input type="checkbox" name="" id=""></td>
    <td><input type="checkbox" name="" id=""></td>
    <td><input type="checkbox" name="" id=""></td>
</tr>
@foreach ($sousMenu->sousSousMenus as $sousSousMenu)
    @include('partials.menu-item', ['sousMenu' => $sousSousMenu])
@endforeach
