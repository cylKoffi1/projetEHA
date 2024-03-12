

@foreach ($rubriques as $rubrique)
<li class="sidebar-item  has-sub">
    <a href="#" class='sidebar-link'>
        <i class="bi bi-people-fill"></i>
        <span>Utilisateurs</span>
    </a>

    <ul class="submenu ">

        <li class="submenu-item  ">
            <a href="/admin/users/create" class="submenu-link">Nouvel utilisateur</a>

        </li>
        <li class="submenu-item  ">
            <a href="/admin/users" class="submenu-link">Liste</a>

        </li>
        <li class="submenu-item  ">
            <a href="/admin/users/details-user/{{ auth()->user()->id }}" class="submenu-link">Mots de passes</a>

        </li>


    </ul>


</li>
@endforeach