<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caracteristique extends Model
{
    protected $table = 'caracteristiques';
    protected $primaryKey = 'idCaracteristique';
    public $timestamps = false;

    protected $fillable = ['libelleCaracteristique', 'idTypeCaracteristique'];

    public function type()
    {
        return $this->belongsTo(TypeCaracteristique::class, 'idTypeCaracteristique');
    }

    public function valeursPossibles()
    {
        return $this->hasMany(ValeurPossible::class, 'idCaracteristique');
    }

    public function familles()
    {
        return $this->belongsToMany(FamilleInfrastructure::class, 'famille_caracteristique', 'idCaracteristique', 'idFamille');
    }

    public function valeurs()
    {
        return $this->hasMany(ValeurCaracteristique::class, 'idCaracteristique');
    }
    public function unite()
    {
        return $this->hasOne(Unite::class, 'idCaracteristique');
    }
}

