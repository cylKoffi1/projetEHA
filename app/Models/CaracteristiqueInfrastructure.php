<?php

// app/Models/CaracteristiqueInfrastructure.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaracteristiqueInfrastructure extends Model
{
    protected $fillable = [
        'infrastructure_id',
        'libelle',
        'type_id',
        'valeurs_possibles', // JSON
        'unite_libelle',
        'unite_symbole',
        'est_personnalisee'
    ];

    protected $casts = [
        'valeurs_possibles' => 'array'
    ];

    public function infrastructure()
    {
        return $this->belongsTo(Infrastructure::class);
    }

    public function type()
    {
        return $this->belongsTo(TypeCaracteristique::class, 'type_id');
    }
}
