<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalitesPays extends Model
{
    use HasFactory;

    protected $table = 'localites_pays';
    protected $fillable = ['id_pays', 'id_niveau', 'libelle', 'code_rattachement', 'code_decoupage'];

    public function decoupage()
    {
        return $this->belongsTo(DecoupageAdministratif::class, 'code_decoupage', 'code_decoupage');
    }

      /**
     * Récupère toutes les données d'une localité
     * @param string $codeLocalite
     * @return array
     */
    public static function getFullLocaliteData($codeLocalite)
    {
        return self::where('code_rattachement', $codeLocalite)
            ->first()
            ->toArray();
    }

    /**
     * Récupère les localités par pays
     * @param string $paysCode
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByPaysCode($paysCode)
    {
        return self::where('code_pays', $paysCode)
            ->orderBy('libelle')
            ->get(['id', 'code_rattachement', 'libelle', 'niveau', 'code_decoupage', 'libelle_decoupage']);
    }
}



