<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeCaracteristique extends Model
{
    protected $table = 'typecaracteristique';
    protected $primaryKey = 'idTypeCaracteristique';
    public $timestamps = false;

    protected $fillable = ['libelleTypeCaracteristique'];

    public function caracteristiques()
    {
        return $this->hasMany(Caracteristique::class, 'idTypeCaracteristique');
    }
}
