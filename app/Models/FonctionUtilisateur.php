<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FonctionUtilisateur extends Model
{
    use HasFactory;

    protected $table = 'fonction_utilisateur';
    protected $primaryKey = 'code';
    public $incrementing = false; // Code n'est pas auto-incrémenté
    protected $fillable = ['code', 'libelle_fonction'];

    public function typesActeurs()
    {
        return $this->belongsToMany(TypeActeur::class, 'fonction_type_acteur', 'fonction_code', 'type_acteur_code');
    }
}
