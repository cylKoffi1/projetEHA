<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EtudeLivrableAttendu extends Model
{
    use HasFactory;

    protected $table = 'etude_livrables_attendus';

    protected $fillable = [
        'code_livrable',
        'libelle_livrable',
        'description',
        'format_attendu',
        'obligatoire',
        'actif',
    ];

    protected $casts = [
        'obligatoire' => 'boolean',
        'actif'       => 'boolean',
    ];

    // Livrable attendu référentiel <-> liaisons (instances sur des études)
    public function etudeLiaisons()
    {
        return $this->hasMany(EtudeLivrable::class, 'code_livrable', 'code_livrable');
    }

    // Accès direct aux études qui attendent ce livrable
    public function etudes()
    {
        return $this->belongsToMany(
            EtudeProjet::class,
            'etude_livrables',
            'code_livrable',         // FK sur la table de liaison pointant ce model
            'code_projet_etude',     // FK sur la table de liaison pointant EtudeProjet
            'code_livrable',         // clé locale de ce model
            'code_projet_etude'      // clé locale de EtudeProjet
        )->withPivot(['date_prevue', 'date_livraison', 'statut', 'commentaire'])
         ->withTimestamps();
    }
}
