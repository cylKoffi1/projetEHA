<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements CanResetPasswordContract, MustVerifyEmail
{
    use Notifiable, CanResetPassword, MustVerifyEmailTrait;
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'utilisateurs';
    protected $fillable = [
        'acteur_id',
        'groupe_utilisateur_id',
        'groupe_projet_id',
        'fonction_utilisateur',
        'login',
        'password',
        'email_verified_at',
        'reset_password_token',
        'reset_password_expires_at',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'is_active',
        'email',
    ];
    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];
    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function pays()
    {
        return $this->hasMany(PaysUser::class, 'code_user', 'acteur_id');
    }

    public function paysUser()
    {
        return $this->hasOne(PaysUser::class, 'code_user', 'acteur_id');
    }
    public function acteur()
    {
        return $this->belongsTo(Acteur::class, 'acteur_id', 'code_acteur');
    }

    public function groupeUtilisateur()
    {
        return $this->belongsTo(GroupeUtilisateur::class, 'groupe_utilisateur_id', 'code');
    }

    public function groupeProjet()
    {
        return $this->belongsTo(GroupeProjet::class, 'groupe_projet_id', 'code');
    }

    public function lieuxExercice()
    {
        return $this->hasMany(UtilisateurLieuExercice::class, 'utilisatur_code' , 'acteur_id');
    }

    public function champsExercice()
    {
        return $this->hasMany(UtilisateurChampExercice::class, 'utilisatur_code', 'acteur_id');
    }
    public function resetPassword($newPassword)
    {
        $this->password = Hash::make($newPassword);
        $this->reset_token = null;
        $this->save();
    }

    public function generateResetToken()
    {
        $this->reset_token = Str::random(60);
        $this->save();
    }
}
