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
        'Photo',
        'is_user',
        'type_financement'
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

     /**
     * Relation avec `personne_physique`
     */
    public function personnePhysique()
    {
        return $this->hasOne(PersonnePhysique::class, 'code_acteur', 'code_acteur');
    }

    /**
     * Relation avec `personne_morale`
     */
    public function personneMorale()
    {
        return $this->hasOne(PersonneMorale::class, 'code_acteur', 'code_acteur');
    }

    /**
     * Relation avec `secteuractiviteacteur`
     */
    public function secteurActiviteActeur()
    {
        return $this->hasMany(SecteurActiviteActeur::class, 'code_acteur', 'code_acteur');
    }

    /**
     * Relation avec `representants`
     */
    public function representants()
    {
        return $this->hasMany(Representants::class, 'entreprise_id', 'code_acteur');
    }


    /**
     * Relation avec `possederpiece`
     */
    public function possederpiece()
    {
        return $this->hasMany(Possederpiece::class, 'idPersonnePhysique', 'code_acteur');
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
