<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntrepriseMorale extends Model
{
    use HasFactory;
    protected $table = 'entreprisemorale';
    protected $primaryKey = 'id';

    protected $fillable = [
        'code_acteur', 'DateCreation', 'raisonSociale', 'SecteurActivite', 'FormeJuridique',
        'numImmatriculation', 'nif', 'rccm', 'capital', 'NumeroAgrement', 'codePostal',
        'AdressPostale', 'AdresseSiege', 'RepresantLegal', 'EmailRepresantant', 'Telephone1Represantant',
        'Telephone2Represantant', 'PersonneContact', 'EmailPersonneContact', 'Telephone1PersonneContact',
        'Telephone2PersonneContact', 'IsActive', 'created_at', 'updated_at'
    ];
    // Les attributs de date
    protected $dates = ['created_at', 'updated_at'];

}
