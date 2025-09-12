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
    protected $keyType = 'int';
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
        'code_devise',
        'minZoom',
        'maxZoom'
    ];
   /* protected $appends = ['armoirie_url','flag_url']; // ✅

    public function getArmoirieUrlAttribute(): ?string
    {
        if (!empty($this->armoirie) && ctype_digit((string)$this->armoirie)) {
            return url('/api/fichiers/'.$this->armoirie);
        }
        if (!empty($this->armoirie)) {
            return str_starts_with($this->armoirie, 'http') ? $this->armoirie : url($this->armoirie);
        }
        return null;
    }

    public function getFlagUrlAttribute(): ?string
    {
        if (!empty($this->flag) && ctype_digit((string)$this->flag)) {
            return url('/api/fichiers/'.$this->flag);
        }
        if (!empty($this->flag)) {
            return str_starts_with($this->flag, 'http') ? $this->flag : url($this->flag);
        }
        return null;
    }*/
    public static function idFromAlpha3(string $alpha3): ?int {
        return self::where('alpha3', $alpha3)->value('id');
    }
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

    public function niveau()
    {
        return $this->hasMany(DecoupageAdminPays::class, 'id_pays', 'id');
    }
}
