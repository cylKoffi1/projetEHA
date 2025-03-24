<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posseder extends Model
{
    use HasFactory;

    protected $table = 'posseder';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'code_projet',
        'code_acteur',
        'secteur_id',
        'date',
        'is_active',
        'created_at',
        'updated_at'
    ];

     /**
     * Relation avec le modèle Projet
     * Un enregistrement dans `executer` appartient à un projet
     */
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }

    /**
     * Relation avec le modèle Acteur
     * Un enregistrement dans `executer` appartient à un acteur (maître d'œuvre)
     */
    public function acteur()
    {
        return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur');
    }

    /**
     * Scope pour récupérer uniquement les entrées actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
