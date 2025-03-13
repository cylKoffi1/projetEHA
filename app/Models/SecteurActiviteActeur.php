<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecteurActiviteActeur extends Model
{
    use HasFactory;

    protected $table = 'secteuractiviteacteur'; // Nom de la table

    protected $primaryKey = 'id'; // Clé primaire

    public $timestamps = true; // Active automatiquement `created_at` et `updated_at`

    protected $fillable = [
        'code_acteur',
        'code_secteur',
        'created_at',
        'updated_at',
    ];

    // Définition de la relation avec le modèle Acteur 
    public function acteur()
    {
        return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur');
    }

    // Définition de la relation avec le modèle Secteur 
    public function secteur()
    {
        return $this->belongsTo(SecteurActivite::class, 'code_secteur', 'code');
    }
}
