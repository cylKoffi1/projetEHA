<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Infrastructure extends Model
{
    use HasFactory;

    protected $table = 'infrastructures';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'code',
        'libelle',
        'code_famille_infrastructure',
        'code_groupe_projet',
        'code_pays'
    ];

    // Relation avec la famille d'infrastructure
    public function familleInfrastructure()
    {
        return $this->belongsTo(FamilleInfrastructure::class, 'code_famille_infrastructure', 'code_sdomaine');
    }

    // Relation avec les valeurs de caractéristiques
    public function valeursCaracteristiques()
    {
        return $this->hasMany(ValeurCaracteristique::class, 'idInfrastructure');
    }
}