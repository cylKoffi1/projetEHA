<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FonctionTypeActeur extends Model
{
    use HasFactory;

    protected $table = 'fonction_type_acteur';
    protected $fillable = ['fonction_code', 'type_acteur_code'];

    public function fonction()
    {
        return $this->belongsTo(FonctionUtilisateur::class, 'fonction_code', 'code');
    }

    public function typeActeur()
    {
        return $this->belongsTo(TypeActeur::class, 'type_acteur_code', 'cd_type_acteur');
    }
}
