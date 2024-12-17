@extends('layouts.app')

@section('content')

@if (session('success'))
    <script>alert("{{ session('success') }}");</script>
@endif

<div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-12">
                    <li class="breadcrumb-item" style="list-style: none; text-align: right; padding: 5px; font-family: Arial, Helvetica, sans-serif;"><span id="date-now" style="color: #34495E; font-family: Verdana, Geneva, Tahoma, sans-serif; margin-left: 15px;"></span></li>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3><i class="bi bi-arrow-return-left return" onclick="goBack()"></i>Plateforme </h3>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="">Paramètre généraux</a></li>
                            <li class="breadcrumb-item active" aria-current="page">role permission</li>

                        </ol>
                    </nav>
                    <div class="row">
                        <script>
                            setInterval(function() {
                                document.getElementById('date-now').textContent = getCurrentDate();
                            }, 1000);

                            function getCurrentDate() {
                                // Implémentez la logique pour obtenir la date actuelle au format souhaité
                                var currentDate = new Date();
                                return currentDate.toLocaleString(); // Vous pouvez utiliser une autre méthode pour le formatage
                            }

                        </script>

                    </div>
                </div>
            </div>
        </div>
    </div>

<section class="section">
    <div class="card">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card-header">
            <h5>Gestion des Permissions de Rôles</h5>
        </div>
        <div class="card-body">
            <form id="role-permissions-form" action="{{ route('role_permissions.store') }}" method="POST">
                @csrf
                <input type="hidden" id="method" name="_method" value="POST">
                <input type="hidden" id="permission-id" name="id">

                <div class="row">
                    <!-- Rôle Source -->
                    <div class="form-group col-md-4">
                        <label for="role_source">Rôle Source</label>
                        <select class="form-control" id="role_source" name="role_source" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->code }}">{{ $role->libelle_groupe }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Rôle Cible -->
                    <div class="form-group col-md-4">
                        <label for="role_target">Rôle Cible</label>
                        <select class="form-control" id="role_target" name="role_target" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->code }}">{{ $role->libelle_groupe }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Permission -->
                    <div class="form-group col-md-4">
                        <label for="can_assign">Permission</label>
                        <select class="form-control" id="can_assign" name="can_assign" required>
                            <option value="1">Autorisé</option>
                            <option value="0">Interdit</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary mt-2" id="submit-button">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Liste des Permissions de Rôles</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped table-bordered" id="permissions-table">
                <thead>
                    <tr>
                        <th>Rôle Source</th>
                        <th>Rôle Cible</th>
                        <th>Permission</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                        <tr>
                            <td>{{ $permission->source->libelle_groupe }}</td>
                            <td>{{ $permission->target->libelle_groupe }}</td>
                            <td>
                                @if ($permission->can_assign)
                                    <span class="badge bg-success">Autorisé</span>
                                @else
                                    <span class="badge bg-danger">Interdit</span>
                                @endif
                            </td>
                            <td>
                                <a href="#" class="edit-button"
                                    data-id="{{ $permission->id }}"
                                    data-role-source="{{ $permission->role_source }}"
                                    data-role-target="{{ $permission->role_target }}"
                                    data-can-assign="{{ $permission->can_assign }}"
                                    title="Modifier">
                                    <i class="bi bi-pencil-square" style="font-size: 1.2rem; cursor: pointer;"></i>
                                </a>

                                <a href="#" class="delete-button"
                                    data-id="{{ $permission->id }}"
                                    title="Supprimer">
                                    <i class="bi bi-trash" style="font-size: 1.2rem; color: red; cursor: pointer;"></i>
                                </a>

                                <form id="delete-form-{{ $permission->id }}" action="{{ route('role_permissions.destroy', $permission->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Modifier une permission
        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const roleSource = this.getAttribute('data-role-source');
                const roleTarget = this.getAttribute('data-role-target');
                const canAssign = this.getAttribute('data-can-assign');

                document.getElementById('permission-id').value = id;
                document.getElementById('role_source').value = roleSource;
                document.getElementById('role_target').value = roleTarget;
                document.getElementById('can_assign').value = canAssign;

                const form = document.getElementById('role-permissions-form');
                form.action = `/role_permissions/${id}`;
                document.getElementById('method').value = 'PUT';

                document.getElementById('submit-button').textContent = 'Modifier';
            });
        });

        // Supprimer une permission
        document.querySelectorAll('.delete-button').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                if (confirm('Êtes-vous sûr de vouloir supprimer cette permission ?')) {
                    document.getElementById(`delete-form-${id}`).submit();
                }
            });
        });
    });
</script>

@endsection
