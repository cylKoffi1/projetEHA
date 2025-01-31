@extends('layouts.app')
<style>
    /* Header de la carte */
.card-header {
    background-color:rgb(21, 96, 176) !important;
    color: #fff !important;
    font-size: 1.25rem !important;
    text-align: center !important;
    padding: 10px !important;
    border-bottom: 1px solid #ddd !important;
}

/* Corps de la carte */
.card-body {
    background-color: #ffffff !important;
    padding: 20px !important;
    border-radius: 5px !important ;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
}

/* Formulaire */
.form-group {
    margin-bottom: 15px;
}

label {
    font-weight: bold;
}

.form-control {
    width: 100%;
    padding: 10px;
    font-size: 1rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
}

.form-control:focus {
    border-color: #007bff;
    outline: none;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
}

.invalid-feedback {
    color: #e3342f;
    font-size: 0.875rem;
}

.alert {
    padding: 10px 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    font-size: 1rem;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

/* Bouton */
.btn-primary {
    background-color: #007bff !important;
    color: #fff;
    border: none;
    padding: 10px 20px;
    font-size: 1rem;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-primary:hover {
    background-color: #0056b3;
}

/* Section principale */
.welcome-hero {
    padding: 50px 0;
}

.d-flex {
    display: flex;
    align-items: center;
    justify-content: center;
}

.container {
    padding: 15px;
}

.card {
    border: 1px solid #ddd;
    border-radius: 5px;
}
#main{
        margin-left: 0 !important;
    }
/* Responsive */
@media (max-width: 768px) {
    .col-md-4, .col-md-6, .col-md-8 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .text-md-right {
        text-align: left;
    }

    .offset-md-4 {
        margin-left: 0;
    }
}
</style>
@section('content')

<section class="d-flex align-items-center justify-content-center" >
<div class="container" style="max-width: 800px;">
    <div class="row justify-content-center">
        <div class="col justify-content-center">
            <div class="card">
                <div class="card-header">Réinitialiser le mot de passe</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">Adresse e-mail</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">Nouveau mot de passe</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">Confirmer le mot de passe</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Réinitialiser le mot de passe
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection
