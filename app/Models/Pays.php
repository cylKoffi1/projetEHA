<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pays extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'pays';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    // Définir les colonnes qui peuvent être remplies
    protected $fillable = [
        'code',
        'alpha2',
        'alpha3',
        'nom_en_gb',
        'nom_fr_fr',
        'codeTel',
        'armoirie',
        'flag',
        'code_devise'
    ];


    /**
     * Récupère les groupes projets associés à ce pays.
     */
    public function groupes()
    {
        return $this->hasMany(GroupeProjetPaysUser::class, 'pays_code', 'alpha3');
    }

    /**
     * Récupère les utilisateurs associés à ce pays.
     */
    public function utilisateurs()
    {
        return $this->groupes()->distinct('user_id')->pluck('user_id');
    }

    public static function getAlpha3Code($countryName)
    {
        return self::where('nom_fr_fr', $countryName)->value('alpha3');
    }
}
