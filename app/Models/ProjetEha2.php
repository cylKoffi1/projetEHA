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

    public function statuts()
    {
        return $this->belongsToMany(ProjetStatutProjet::class, 'projet_statut_projet', 'code_projet', 'code_statut_projet');
    }

    public function latestStatutProjet()
    {
        return $this->hasOne(ProjetStatutProjet::class, 'code_projet')
            ->latest('date')
            ->orderBy('date', 'desc');
    }
}
