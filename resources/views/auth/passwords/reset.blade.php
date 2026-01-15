<!doctype html>
<html class="no-js" lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('layouts.lurl')

<style>
    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 15px;
    }

    .card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
        background-color: rgba(255, 255, 255, 0.95);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        font-size: 16px;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        outline: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #007bff, #0056b3);
        border: none;
        border-radius: 5px;
        color: #fff;
        font-size: 16px;
        padding: 12px 30px;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        width: 100%;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    .alert {
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        border-left: 4px solid;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-color: #28a745;
    }

    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-color: #dc3545;
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 14px;
        margin-top: -15px;
        margin-bottom: 15px;
        display: block;
    }

    .text-bg-primary {
        background: linear-gradient(135deg, #007bff, #0056b3) !important;
    }

    .link-secondary {
        color: #007bff;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .link-secondary:hover {
        color: #0056b3;
        text-decoration: underline;
    }

    .icon-wrapper {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(0, 123, 255, 0.1), rgba(0, 86, 179, 0.1));
        margin-bottom: 20px;
    }

    .icon-wrapper i {
        font-size: 28px;
        color: #007bff;
    }

    .password-strength {
        height: 4px;
        border-radius: 2px;
        margin-top: -15px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .password-strength.weak {
        background-color: #dc3545;
        width: 33%;
    }

    .password-strength.medium {
        background-color: #ffc107;
        width: 66%;
    }

    .password-strength.strong {
        background-color: #28a745;
        width: 100%;
    }
</style>
</head>

<body>
    @include('layouts.menu')
    <section class="d-flex align-items-center justify-content-center" style="margin-top: 150px;">
        <div class="container">
            <div class="card border-light-subtle shadow-sm">
                <div class="row g-0">
                    <div class="col-12 col-md-6 text-bg-primary">
                        <div class="d-flex align-items-center justify-content-center h-100" style="padding: 50px 20px;">
                            <div class="text-center">
                                <div class="icon-wrapper mx-auto">
                                    <i class="fas fa-lock"></i>
                                </div>
                                <h2 class="h3 mb-3 text-white">Nouveau mot de passe</h2>
                                <p class="text-white-50">Créez un mot de passe sécurisé pour votre compte. Assurez-vous qu'il contient au moins 8 caractères.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6" style="background-color: #fff">
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <h3 class="mb-1">Réinitialiser le mot de passe</h3>
                                <p class="text-muted small">Entrez votre nouveau mot de passe ci-dessous</p>
                            </div>

                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ session('status') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <strong>Erreur :</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.update') }}" id="reset-password-form">
                                @csrf

                                <input type="hidden" name="token" value="{{ $token }}">

                                <div class="form-group">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Adresse email
                                    </label>
                                    <input 
                                        id="email" 
                                        type="email" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        name="email" 
                                        value="{{ $email ?? old('email') }}" 
                                        required 
                                        autocomplete="email" 
                                        autofocus
                                        readonly
                                    >
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Nouveau mot de passe
                                    </label>
                                    <input 
                                        id="password" 
                                        type="password" 
                                        class="form-control @error('password') is-invalid @enderror" 
                                        name="password" 
                                        required 
                                        autocomplete="new-password"
                                        placeholder="Minimum 8 caractères"
                                    >
                                    <div class="password-strength" id="password-strength"></div>
                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="password-confirm" class="form-label">
                                        <i class="fas fa-lock me-2"></i>Confirmer le mot de passe
                                    </label>
                                    <input 
                                        id="password-confirm" 
                                        type="password" 
                                        class="form-control" 
                                        name="password_confirmation" 
                                        required 
                                        autocomplete="new-password"
                                        placeholder="Répétez le mot de passe"
                                    >
                                    <small class="text-muted" id="password-match"></small>
                                </div>

                                <hr class="mt-4">

                                <button type="submit" class="btn btn-primary w-100" id="submit-btn">
                                    <i class="fas fa-check me-2"></i>
                                    Réinitialiser le mot de passe
                                </button>

                                <div class="text-center mt-3">
                                    <a href="{{ route('login') }}" class="link-secondary text-decoration-none">
                                        <i class="fas fa-arrow-left me-1"></i>
                                        Retour à la connexion
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('betsa/vend/jquery/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('betsa/vend/bootstrap/js/bootstrap.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Vérification de la force du mot de passe
            $('#password').on('input', function() {
                const password = $(this).val();
                const strengthBar = $('#password-strength');
                
                if (password.length === 0) {
                    strengthBar.removeClass('weak medium strong').css('width', '0');
                    return;
                }
                
                let strength = 0;
                if (password.length >= 8) strength++;
                if (password.length >= 12) strength++;
                if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
                if (/[0-9]/.test(password)) strength++;
                if (/[^a-zA-Z0-9]/.test(password)) strength++;
                
                strengthBar.removeClass('weak medium strong');
                if (strength <= 2) {
                    strengthBar.addClass('weak');
                } else if (strength <= 3) {
                    strengthBar.addClass('medium');
                } else {
                    strengthBar.addClass('strong');
                }
            });

            // Vérification de la correspondance des mots de passe
            $('#password-confirm').on('input', function() {
                const password = $('#password').val();
                const confirm = $(this).val();
                const matchText = $('#password-match');
                
                if (confirm.length === 0) {
                    matchText.text('').removeClass('text-danger text-success');
                    return;
                }
                
                if (password === confirm) {
                    matchText.html('<i class="fas fa-check-circle me-1"></i>Les mots de passe correspondent').removeClass('text-danger').addClass('text-success');
                } else {
                    matchText.html('<i class="fas fa-times-circle me-1"></i>Les mots de passe ne correspondent pas').removeClass('text-success').addClass('text-danger');
                }
            });

            // Désactiver le bouton pendant la soumission
            $('#reset-password-form').on('submit', function() {
                const $btn = $('#submit-btn');
                const originalText = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Traitement en cours...');
                
                setTimeout(function() {
                    $btn.prop('disabled', false).html(originalText);
                }, 5000);
            });
        });
    </script>
</body>
</html>
