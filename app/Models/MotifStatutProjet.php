<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MotifStatutProjet extends Model
{
    use HasFactory;

    protected $table = 'motif_statut_projet';

    protected $fillable = [
        'code_projet',
        'type_statut',
        'motif',
        'code_acteur',
        'date_motif',
    ];

    protected $casts = [
        'date_motif' => 'date',
    ];

    /**
     * Relation avec le projet concerné
     */
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }

    /**
     * Relation avec le type de statut (ex: annulé, validé...)
     */
    public function statut()
    {
        return $this->belongsTo(TypeStatut::class, 'type_statut');
    }

    /**
     * Relation avec l’acteur ayant soumis le motif (optionnel)
     */
    public function acteur()
    {
        return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur');
    }
}
