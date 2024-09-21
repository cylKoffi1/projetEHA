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

    protected $table = 'mot_de_passe_utilisateur';

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
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

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
    public function structureRattachement()
    {
        return $this->hasOne(StructureRattachement::class, 'code_personnel', 'code_personnel');
    }
    public function approbateur(){
        return $this->belongsTo(Approbateur::class, 'code_personnel','code_personnel');
    }
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

    public function niveauAcces()
    {
        return $this->belongsTo(NiveauAccesDonnees::class, 'niveau_acces_id', 'id');
    }

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
    public function latestRegion()
    {
        return $this->hasOne(CouvrirRegion::class, 'code_personnel', 'code_personnel')
            ->latest('date')
            ->orderBy('date', 'desc');
    }

}
