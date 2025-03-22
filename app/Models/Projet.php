<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projet extends Model
{
    use HasFactory;

    protected $table = 'projets';
    protected $primaryKey = 'code_projet'; 

    // Colonnes modifiables
    protected $fillable = [
        'code_alpha3_pays',
        'libelle_projet',
        'commentaire',
        'code_sous_domaine',
        'date_demarrage_prevue',
        'date_fin_prevue',
        'cout_projet',
        'code_devise',
        'created_at',
        'updated_at',
    ];

    public function sousDomaine()
    {
        return $this->belongsTo(SousDomaine::class, 'code_sous_domaine', 'code');
    }

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'code_alpha3_pays', 'alpha3');
    }
}
