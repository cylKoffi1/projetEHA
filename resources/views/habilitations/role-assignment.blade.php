@extends('layouts.app')

@section('content')
    <!-- Votre contenu ici -->

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Assignation des rôles aux groupes utilisateurs</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('role-assignment.assign') }}" method="post">
                @csrf
                        <input type="hidden" class="form-control" id="ecran_id" value="{{ $ecran->id }}"  name="ecran_id" required>

                <div class="mb-3">
                    <label for="group" class="form-label">Sélectionnez un groupe utilisateur</label>
                    <select class="form-select" id="group" name="group">
                        @foreach($groupes as $groupe)
                            <option value="{{ $groupe->id }}">{{ $groupe->libelle_groupe }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Sélectionnez un rôle</label>
                    <select class="form-select" id="role" name="role">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Assigner le rôle</button>
            </form>
        </div>
    </div>

    <!-- Votre contenu ici -->
@endsection
