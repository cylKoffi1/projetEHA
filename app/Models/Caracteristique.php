<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Caracteristique extends Model
{
    use HasFactory;

    protected $table = 'caracteristiques';
    protected $primaryKey = 'idCaracteristique';
    public $timestamps = false;

    protected $fillable = [
        'libelleCaracteristique',
        'idTypeCaracteristique',
        'idUnite',
        'parent_id',
        'is_repetable',
        'required',
        'ordre',
        'description',
        'code_famille'
    ];

    /** Relations */
    public function type()
    {
        return $this->belongsTo(TypeCaracteristique::class, 'idTypeCaracteristique');
    }

    public function unite()
    {
        return $this->belongsTo(Unite::class, 'idUnite');
    }

    public function valeursPossibles()
    {
        return $this->hasMany(ValeurPossible::class, 'idCaracteristique', 'idCaracteristique');
    }

    public function valeursCaracteristiques(){
        return $this->hasMany(ValeurCaracteristique::class, 'idCaracteristique', 'idCaracteristique');
    }

    public function familles()
    {
        return $this->belongsToMany(FamilleInfrastructure::class, 'famille_caracteristique', 'idCaracteristique', 'idFamille');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function enfants()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** Logique métier */
    public function isRepetable(): bool
    {
        return (bool) $this->is_repetable;
    }

    public function hasChildren(): bool
    {
        return $this->enfants()->exists();
    }

    public function getTypeLibelleLower(): string
    {
        return strtolower($this->type->libelleTypeCaracteristique ?? '');
    }

    public function isListe(): bool
    {
        return $this->getTypeLibelleLower() === 'liste';
    }

    public function isNombre(): bool
    {
        return $this->getTypeLibelleLower() === 'nombre';
    }

    public function isBoolean(): bool
    {
        return $this->getTypeLibelleLower() === 'boolean';
    }

    public function isTexte(): bool
    {
        return $this->getTypeLibelleLower() === 'texte';
    }
    public function aDesValeurs(): bool
    {
        return $this->valeursPossibles()->exists();
    }
    
    /** Accessors pour JSON */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->getTypeLibelleLower()) {
            'Boolean' => '☑️ Boolean',
            'Texte'   => '✏️ Texte',
            'Nombre'  => '🔢 Nombre',
            'Liste'   => '📋 Liste',
            default   => ucfirst($this->type->libelleTypeCaracteristique ?? 'Inconnu'),
        };
    }

    public function getFormattedIdAttribute(): string
    {
        return 'char_' . $this->idCaracteristique;
    }

    public function getParentFormattedIdAttribute(): ?string
    {
        return $this->parent_id ? 'char_' . $this->parent_id : null;
    }
}
