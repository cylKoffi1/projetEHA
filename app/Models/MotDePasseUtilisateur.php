<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;

class MotDePasseUtilisateur extends Authenticatable implements CanResetPasswordContract, MustVerifyEmail
{
    use Notifiable, CanResetPassword, MustVerifyEmailTrait;
    protected $table = 'mot_de_passe_utilisateur';
    protected $fillable = [
        'code_personnel', 'login', 'password', 'niveau_acces_id', 'email', 'email_verified_at', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

}
