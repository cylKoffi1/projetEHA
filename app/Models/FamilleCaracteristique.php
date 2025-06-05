<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilleCaracteristique extends Model
{
    protected $table = 'famille_caracteristique';
    public $timestamps = false;

    protected $fillable = ['idFamille', 'idCaracteristique'];

    public function famille()
    {
        return $this->belongsTo(FamilleInfrastructure::class, 'idFamille');
    }

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'idCaracteristique');
    }
}

