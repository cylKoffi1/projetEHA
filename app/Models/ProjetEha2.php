<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjetEha2 extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'projet_eha2'; // Nom de la table
    protected $primaryKey = 'CodeProjet';
    protected $keyType = 'string';

    protected $fillable = [
        'CodeProjet',
        'code_ministere',
        'Objectif_global',
        'code_domaine',
        'code_sous_domaine',
        'code_region',
        'Date_demarrage_prevue',
        'date_fin_prevue',
        'cout_projet',
        'code_devise',
        'code_district'
    ];
    public function domaine()
    {
        return $this->belongsTo(Domaine::class, 'code_domaine', 'code');
    }

    public function devise()
    {
        return $this->belongsTo(Devise::class, 'code_devise', 'code');
    }

    public function sous_domaine()
    {
        return $this->belongsTo(SousDomaine::class, 'code_sous_domaine', 'code');
    }
    public function ministereProjet()
    {
        return $this->hasMany(MinistereProjet::class, 'codeProjet', 'CodeProjet');
    }
    public function projetStatutProjet()
    {
        return $this->hasMany(ProjetStatutProjet::class, 'code_projet', 'CodeProjet');
    }
    public function actionBeneficiaires()
    {
        return $this->hasMany(ActionBeneficiairesProjet::class, 'CodeProjet', 'CodeProjet');
    }
    public function projetActionAMener()
    {
        return $this->hasMany(ProjetActionAMener::class, 'CodeProjet', 'CodeProjet');
    }
    public function dateDebutEffective()
    {
        return $this->hasMany(DateDebutEffective::class, 'code_projet', 'CodeProjet');
    }
    public function dateFinEffective()
    {
        return $this->hasMany(DateFinEffective::class, 'code_projet', 'CodeProjet');
    }
    public function projetAgence()
    {
        return $this->hasMany(ProjetAgence::class, 'code_projet', 'CodeProjet');
    }
    public function bailleursProjets()
    {
        return $this->hasMany(BailleursProjet::class, 'code_projet', 'CodeProjet');
    }
    public function projetChefProjet()
    {
        return $this->hasMany(ProjetChefProjet::class, 'code_projet', 'CodeProjet');
    }

    public function latestStatutProjet()
    {
        return $this->hasOne(ProjetStatutProjet::class, 'code_projet')
            ->latest('date')
            ->orderBy('date', 'desc');
    }
}
