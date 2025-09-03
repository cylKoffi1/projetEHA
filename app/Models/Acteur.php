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
    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute(): ?string
    {
        if (!empty($this->Photo) && ctype_digit((string)$this->Photo)) {
            return url('/fichiers/'.$this->Photo);
        }
        if (!empty($this->Photo)) {
            if (str_starts_with($this->Photo, 'http')) return $this->Photo;
            return url($this->Photo);
        }
        return null;
    }
    
    public function pays()
    {
        return $this->belongsTo(Pays::class, 'code_pays', 'alpha3');
    }

    public function type(){
        return $this->belongsTo(TypeActeur::class, 'type_acteur', 'cd_type_acteur');
    }
    public function utilisateurs()
    {
        return $this->belongsTo(User::class, 'code_acteur', 'acteur_id');
    }


    // ✅ Renforcements dont l’acteur est bénéficiaire
    public function renforcements()
    {
        return $this->belongsToMany(
            Renforcement::class,
            'renforcement_beneficiaire',
            'code_acteur',
            'code_renforcement'
        );
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

        public function bailleurs()
    {
        return $this->hasMany(Financer::class, 'code_acteur', 'code_acteur');
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

    public function projetsChef()
    {
        return $this->hasMany(Controler::class, 'code_acteur', 'code_acteur')->with('projet');
    }
    
    public function projetsOuvrage()
    {
        return $this->hasMany(Posseder::class, 'code_acteur', 'code_acteur')->with('projet');
    }
    
    public function projetsOeuvre()
    {
        return $this->hasMany(Executer::class, 'code_acteur', 'code_acteur')->with('projet');
    }
    
    public function projetsFinances()
    {
        return $this->hasMany(Financer::class, 'code_acteur', 'code_acteur')->with('projet');
    }
    
    public function projetsApprouves()
    {
        return $this->hasMany(ProjetApprobation::class, 'code_acteur', 'code_acteur')->with('etude.projet');
    }
    
}
