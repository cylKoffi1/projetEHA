<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class typeCaptage extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'type_captage'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
    protected $fillable = ['code', 'libelle'];

    public static function getLibelleByCode($code)
    {
        return self::where('code', $code)->value('libelle') ?? 'Code inconnu'; // Retourne le libellé ou une valeur par défaut
    }

}
