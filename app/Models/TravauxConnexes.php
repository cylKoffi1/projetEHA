<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravauxConnexes extends Model
{
    protected $table = 'travaux_connexes';

    protected $primaryKey = 'codeActivite';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'codeActivite',
        'CodeProjet',
        'type_travaux_id',
        'cout_projet',
        'date_debut_previsionnelle',
        'date_fin_previsionnelle',
        'date_debut_effective',
        'date_fin_effective',
        'commentaire',
    ];

    // Relation avec le type de travaux
    public function typeTravaux()
    {
        return $this->belongsTo(TypeTravauxConnexes::class, 'type_travaux_id');
    }

    // Relation avec le projet
    public function projet()
    {
        return $this->belongsTo(ProjetEha2::class, 'CodeProjet', 'CodeProjet');
    }
    public static function generateCodeTravauxConnexe()
    {
        $latest = self::latest()->first();
        $orderNumber = $latest ? intval(substr($latest->codeActivite, -3)) + 1 : 1;
        $month = now()->format('m');
        $year = now()->format('Y');
        return 'EHA_TC_' . $month . '_' . $year . '_' . str_pad($orderNumber, 3, '0', STR_PAD_LEFT);
    }

}
