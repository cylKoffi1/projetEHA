<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeActeur extends Model
{
    use HasFactory;
    protected $table = 'type_acteur';
    protected $fillable = ['cd_type_acteur','libelle_type_acteur'];

    protected $primaryKey = 'cd_type_acteur';

    public $incrementing = false;

    protected $keyType = 'string';


    public function fonctions()
    {
        return $this->belongsToMany(FonctionUtilisateur::class, 'fonction_type_acteur', 'type_acteur_code', 'fonction_code');
    }
}
