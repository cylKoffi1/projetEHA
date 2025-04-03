<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Financer extends Model
{
    use HasFactory;

    protected $table = 'financer'; // Nom de la table dans la base de données
    protected $primaryKey = 'id'; // Clé primaire

    public $timestamps = true; // Active `created_at` et `updated_at`

    protected $fillable = [
        'code_projet',
        'code_acteur',
        'montant_finance',
        'devise',
        'financement_local',
        'date_engagement',
        'commentaire',
        'FinancementType',
        'is_active',
    ];

    /**
     * Relation avec le modèle Projet
     * Un financement appartient à un projet
     */
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }
    public function financements()
    {
        return $this->hasMany(Financer::class, 'code_projet', 'code_projet');
    }
    /**
    * Relation avec les bailleurs via la table financer
    */
   public function bailleurs()
   {
       return $this->belongsToMany(Acteur::class, 'financer', 'code_projet', 'code_acteur')
           ->using(Financer::class)
           ->withPivot(['montant_finance', 'devise', 'date_engagement']);
   }
    /**
     * Relation avec le modèle Acteur (Bailleur ou organisme financeur)
     * Un financement est accordé par un acteur
     */
    public function acteur()
    {
        return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur');
    }

    /**
     * Scope pour récupérer uniquement les financements actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
