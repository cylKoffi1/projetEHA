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
        'isAssistant',
        'is_active',
        'created_at',
        'updated_at'
    ];
    protected $casts = [
        'isAssistant' => 'boolean',
        'is_active' => 'boolean',
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

    public function secteurActivite(){
        return $this->belongsTo(SecteurActivite::class, 'secteur_id', 'code');
    }

    /**
     * Scope pour récupérer uniquement les entrées actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
