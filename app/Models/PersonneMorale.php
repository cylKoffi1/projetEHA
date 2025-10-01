<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonneMorale extends Model
{
    use HasFactory;

    protected $table = 'personne_morale';
    protected $primaryKey = 'id';
    public $timestamps = false; // adapte si tu as les timestamps

    protected $fillable = [
        'code_acteur',
        'raison_sociale',
        'date_creation',
        'forme_juridique',
        'num_immatriculation',
        'nif',
        'rccm',
        'capital',
        'numero_agrement',
        'code_postal',
        'adresse_postale',
        'adresse_siege',
        // + autres colonnes si présentes
    ];

    public function acteur()
    {
        return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur');
    }

    public function representants()
    {
        return $this->belongsToMany(Acteur::class, 'representants', 'entreprise_id', 'representant_id')
            ->withPivot('role');
    }
}
