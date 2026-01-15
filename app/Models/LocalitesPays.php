<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalitesPays extends Model
{
    use HasFactory;

    protected $table = 'localites_pays';
    protected $fillable = ['id_pays', 'id_niveau', 'libelle', 'code_rattachement', 'code_decoupage', 'latitude', 'longitude', 'geo_status'];

    public function decoupage()
    {
        return $this->belongsTo(DecoupageAdministratif::class, 'code_decoupage', 'code_decoupage');
    }
    public function pays()
    {
        return $this->belongsTo(Pays::class, 'id_pays', 'alpha3');
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
    public static function nextChildCode(string $alpha3, string $parentCode): string {
        $len = strlen($parentCode) + 2;
        $max = self::where('id_pays', $alpha3)
            ->where('code_rattachement', 'LIKE', $parentCode.'%')
            ->whereRaw('CHAR_LENGTH(code_rattachement) = ?', [$len])
            ->max('code_rattachement');

        $next2 = $max ? (int)substr($max, -2) + 1 : 1;
        return $parentCode . str_pad((string)$next2, 2, '0', STR_PAD_LEFT);
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



