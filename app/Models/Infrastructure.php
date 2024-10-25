<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Infrastructure extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'infrastructures'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
    protected $fillable = ['code', 'libelle','code_domaine', 'code_famille_infrastructure'];

    public function caractReseauCollect()
    {
        return $this->hasMany(CaractReseauCollect::class, 'typeOuvrage', 'code');
    }

    // Méthode pour obtenir les infrastructures avec code_domaine == 02
    public static function getOuvragesByDomaine($codeDomaine = 02)
    {
        return self::where('code_domaine', $codeDomaine)->get();
    }
}
