<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caracteristique extends Model
{
    use HasFactory;

    protected $table = 'caracteristiques';
    protected $primaryKey = 'idCaracteristique';
    public $timestamps = false;

    protected $fillable = [
        'libelleCaracteristique',
        'idTypeCaracteristique',
    ];

    // Relation avec le type de caractéristique
    public function typeCaracteristique()
    {
        return $this->belongsTo(TypeCaracteristique::class, 'idTypeCaracteristique');
    }

    // Relation avec les unités
    public function unites()
    {
        return $this->hasMany(Unite::class, 'idCaracteristique');
    }

    // Relation avec les valeurs de caractéristiques
    public function valeursCaracteristiques()
    {
        return $this->hasMany(ValeurCaracteristique::class, 'idCaracteristique');
    }
}