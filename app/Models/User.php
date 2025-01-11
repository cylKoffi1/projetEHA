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
        'fonction_utilisateur',
        'groupe_projet_id',
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

/**
     * Relation avec la table `groupe_projet_pays_user`.
     */
    public function projetsPays()
    {
        return $this->hasMany(GroupeProjetPaysUser::class, 'user_id', 'acteur_id');
    }

    /**
     * Récupère les pays associés à l'utilisateur.
     */
    public function pays()
    {
        return $this->hasManyThrough(
            Pays::class,                  // Modèle cible
            GroupeProjetPaysUser::class,   // Modèle intermédiaire
            'user_id',                     // Clé étrangère sur `groupe_projet_pays_user`
            'alpha3',                      // Clé primaire sur `pays`
            'acteur_id',                   // Clé locale sur `users`
            'pays_code'                    // Clé étrangère sur `groupe_projet_pays_user`
        );
    }


    public function paysSelectionne()
    {
        $paysCode = session('pays_selectionne'); // Récupère le code du pays depuis la session
        return Pays::where('alpha3', $paysCode)->first();
    }

    /**
     * Récupère les groupes projets associés à l'utilisateur dans un pays donné.
     */
    public function groupesParPays($paysCode)
    {
        return $this->projetsPays()->where('pays_code', $paysCode)->with('groupeProjet')->get();
    }
    public function groupeProjetSelectionne()
    {
        $projetId = session('projet_selectionne'); // Récupère l'ID du projet depuis la session
        return GroupeProjet::where('code', $projetId)->first();
    }

    public function acteur()
    {
        return $this->belongsTo(Acteur::class, 'acteur_id', 'code_acteur');
    }
    public function rolePermission(){
        return $this->belongsTo(RolePermission::class, 'groupe_utilisateur_id', 'role_source');
    }

    public function groupeUtilisateur()
    {
        return $this->belongsTo(GroupeUtilisateur::class, 'groupe_utilisateur_id', 'code');
    }

    // Vérifier si un utilisateur a un rôle
    public function hasGroupe($groupeCode)
    {
        return $this->groupeUtilisateur->code === $groupeCode;
    }

    public function fonctionUtilisateur(){
        return $this->belongsTo(FonctionUtilisateur::class, 'fonction_utilisateur', 'code');
    }

    public function groupeProjets()
    {
        return $this->hasManyThrough(
            GroupeProjet::class,
            GroupeProjetPaysUser::class,
            'user_id',   // Clé étrangère sur `groupe_projet_pays_user`
            'code',      // Clé primaire sur `groupe_projet`
            'acteur_id', // Clé locale sur `users`
            'groupe_projet_id' // Clé étrangère sur `groupe_projet_pays_user`
        );
    }


    public function lieuxExercice()
    {
        return $this->hasMany(UtilisateurLieuExercice::class, 'utilisateur_code' , 'acteur_id');
    }

    public function champsExercice()
    {
        return $this->hasMany(UtilisateurChampExercice::class, 'utilisateur_code', 'acteur_id');
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
       /**
     * Inclure les utilisateurs inactifs.
     */
    public function scopeWithInactive($query)
    {
        return $query->where('is_active', false);
    }
}
