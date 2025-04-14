<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Financer extends Model
{
    use HasFactory;

    protected $table = 'financer';
    protected $primaryKey = 'id';
    public $timestamps = true;

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
     * Le financement appartient à un projet
     */
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }

    /**
     * Le financement est fait par un acteur (bailleur)
     */
    public function bailleur()
    {
        return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur');
    }

 
    public function financements()
    {
        return $this->hasMany(Financer::class, 'code_projet', 'code_projet')
                    ->where('financer.is_active', true)
                    ->with('bailleur');
    }
    
    /**
     * Scope pour filtrer uniquement les financements actifs
     */
    public function scopeActifs($query)
    {
        return $query->where('financer.is_active', true);
    }
}
