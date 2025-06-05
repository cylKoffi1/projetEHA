<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvancementProjet extends Model
{
    use HasFactory;

    protected $table = 'avancement_projets';

    protected $fillable = [
        'code_projet',
        'num_ordre',
        'quantite',
        'pourcentage',
        'date_avancement',
        'photos',
        'date_fin_effective',
        'description_finale',
        'code_acteur',
    ];

    protected $casts = [
        'date_avancement' => 'date',
        'date_fin_effective' => 'date',
        'quantite' => 'decimal:2',
        'pourcentage' => 'decimal:2',
    ];

    /**
     * Relation avec le projet (via code_projet)
     */
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }

        /**
     * Relation avec le projet (via code_projet)
     */
    public function projetInfrastructures()
    {
        return $this->belongsTo(ProjetInfrastructure::class, 'code_projet', 'code_projet');
    }

    /**
     * Relation avec l'acteur utilisateur (ingénieur, superviseur, etc.)
     */
    public function acteur()
    {
        return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur');
    }

    /**
     * Photos séparées par virgules => tableau
     */
    public function getPhotosListAttribute()
    {
        return $this->photos ? explode(',', $this->photos) : [];
    }

    public function setPhotosListAttribute($array)
    {
        $this->attributes['photos'] = implode(',', $array);
    }
}
