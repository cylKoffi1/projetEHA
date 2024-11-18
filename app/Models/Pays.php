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

    // Définir les relations si nécessaire
    public function utilisateurs()
    {
        return $this->hasMany(PaysUser::class, 'code_pays', 'alpha3');
    }

    // Vous pouvez ajouter d'autres relations ou méthodes ici

    public static function getAlpha3Code($countryName)
    {
        return self::where('nom_fr_fr', $countryName)->value('alpha3');
    }
}
