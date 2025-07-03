<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValeurPossible extends Model
{
    protected $table = 'valeurs_possibles_caracteristique';
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $fillable = ['idCaracteristique', 'valeur'];

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'idCaracteristique');
    }
}
