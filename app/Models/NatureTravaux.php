<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NatureTravaux extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'nature_traveaux'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
    // Ajoutez cette méthode pour récupérer le libellé à partir du code
    public static function getLibelleByCode($code)
    {
        return self::where('code', $code)->value('libelle'); // Retourne le libellé pour le code donné
    }
}
