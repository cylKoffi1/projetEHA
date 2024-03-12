<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    //public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mot_de_passe_utilisateur';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code_personnel',
        'login',
        'email',
        'password',
        'niveau_acces_id',
        'remember_token',
        'email_verified_at',
        'created_at',
        'updated_at',
        'is_active', // Ajout du champ is_active
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean', // Cast is_active en tant que booléen
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
     * Get the associated personnel for the user.
     */
    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'code_personnel', 'code_personnel');
    }
    public function utilisateurDomaines()
    {
        return $this->hasMany(UtilisateurDomaine::class, 'code_personnel', 'code_personnel');
    }

    public function appartenirGroupeUtilisateurs()
    {
        return $this->hasMany(ApartenirGroupeUtilisateur::class, 'code_personnel', 'code_personnel');
    }

    public function avoirExpertises()
    {
        return $this->hasMany(AvoirExpertise::class, 'code_personnel', 'code_personnel');
    }

    public function domaine()
    {
        return $this->belongsTo(Personnel::class, 'domaine_activite', 'code');
    }
    /**
     * Get the level of access associated with the user.
     */
    public function niveauAcces()
    {
        return $this->belongsTo(NiveauAccesDonnees::class, 'niveau_acces_id', 'id');
    }
    /**
     * Get the latest group assignment for the user.
     */


    public function latestFonction()
    {
        return $this->hasOne(OccuperFonction::class, 'code_personnel', 'code_personnel')
            ->latest('date')
            ->orderBy('date', 'desc');
    }

    public function generateResetToken()
    {
        $this->reset_token = Str::random(60);
        $this->save();
    }

    public function resetPassword($newPassword)
    {
        $this->password = Hash::make($newPassword);
        $this->reset_token = null;
        $this->save();
    }

   

}
