<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acteur extends Model
{
    use HasFactory;
    protected $table = 'acteur';
    protected $primaryKey = 'code_acteur';

    protected $fillable = [
        'code_acteur',
        'libelle_long',
        'libelle_court',
        'type_acteur',
        'email',
        'telephone',
        'adresse',
        'code_pays',
        'created_at',
        'updated_at',
        'is_active',
        'Photo'
    ];

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'code_pays', 'alpha3');
    }

    public function type(){
        return $this->belongsTo(TypeActeur::class, 'type_acteur', 'cd_type_acteur');
    }
    public function utilisateurs()
    {
        return $this->hasMany(User::class, 'code_acteur', 'acteur_id');
    }
    // Portée par défaut pour inclure uniquement les acteurs actifs
    protected static function booted()
    {
        static::addGlobalScope('active', function ($query) {
            $query->where('is_active', true);
        });
    }

    // Supprimer la portée par défaut pour inclure les acteurs inactifs
    public function scopeWithInactive($query)
    {
        return $query->withoutGlobalScope('active'); // Désactive la portée par défaut pour inclure les désactivés
    }

}
