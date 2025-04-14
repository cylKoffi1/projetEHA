<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Legende extends Model
{
    protected $table = 'legende';

    protected $fillable = ['groupe_projet', 'borneInf', 'borneSup', 'couleur'];

    public function legendecarte()
    {
        return $this->belongsTo(Legendecarte::class, 'idLegendeCarte');
    }
}
