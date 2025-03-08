@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Changer votre mot de passe</h2>
    <p>Vous devez changer votre mot de passe pour accéder à la plateforme.</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <div class="mb-3">
            <label for="password">Nouveau mot de passe</label>
            <input type="password" class="form-control" name="password" required>
        </div>

        <div class="mb-3">
            <label for="password_confirmation">Confirmer le mot de passe</label>
            <input type="password" class="form-control" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>
</div>
@endsection
