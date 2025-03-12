<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnntrepriseMorale extends Model
{
    use HasFactory;

    // Table associée au modèle
    protected $table = 'entreprisemorale';

    // Les attributs qui peuvent être assignés en masse
    protected $fillable = [
        'codeEtudeProjets',
        'nomEntreprise',
        'raisonSociale',
        'numeroImmatriculation',
        'adresseSiegeSocial',
        'numeroTelephone',
        'adresseEmail',
        'siteWeb',
        'nomResponsableProjet',
        'fonctionResponsable',
        'capitalSocial',
        'infoSupplementaire1',
        'infoSupplementaire2',
        'is_deleted'
    ];

    // Les attributs qui devraient être cachés dans les tableaux
    protected $hidden = [];

    // Les attributs de date
    protected $dates = ['created_at', 'updated_at'];

    // Les attributs de timestamp
    public $timestamps = true;

    // Définir la clé primaire si ce n'est pas 'id'
    protected $primaryKey = 'id';

    // La clé primaire est auto-incrémentée
    public $incrementing = true;

    // Le type de la clé primaire est 'int'
    protected $keyType = 'int';

    // Les relations éventuelles
    public function etudeProject()
    {
        return $this->belongsTo(EtudeProject::class, 'codeEtudeProjets', 'codeEtudeProjets');
    }
}
