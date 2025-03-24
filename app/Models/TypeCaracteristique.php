<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeCaracteristique extends Model
{
    use HasFactory;

    protected $table = 'typeCaracteristique';
    protected $primaryKey = 'idTypeCaracteristique';
    public $timestamps = false;

    protected $fillable = [
        'libelleTypeCaracteristique',
    ];

    // Relation avec les caractéristiques
    public function caracteristiques()
    {
        return $this->hasMany(Caracteristique::class, 'idTypeCaracteristique');
    }
}