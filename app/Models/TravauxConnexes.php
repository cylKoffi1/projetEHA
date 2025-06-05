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
        'code_projet',
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
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }
    public static function generateCodeTravauxConnexe($country, $group)
    {
        $month = now()->format('m');
        $year = now()->format('Y');
        $base = "{$country}_{$group}_TC_{$year}_{$month}_";
    
        // Recherche du dernier code avec ce préfixe
        $last = self::where('codeActivite', 'like', $base . '%')
                    ->orderByDesc('codeActivite')
                    ->first();
    
        if ($last) {
            $lastNumber = intval(substr($last->codeActivite, -3));
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
    
        return $base . $newNumber;
    }
    

}
