<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domaine extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'domaine_intervention'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $fillable = ['code', 'libelle', 'groupe_projet_code'];
    public function groupeProjet()
    {
        return $this->belongsTo(GroupeProjet::class, 'groupe_projet_code', 'code');
    }
    public function sousdomaines()
    {
        return $this->hasMany(SousDomaine::class, 'code_domaine', 'code')
                    ->where('code_groupe_projet', $this->groupe_projet_code);
    }
    
    public function familleInfrastructure()
    {
        return $this->belongsTo(FamilleInfrastructure::class, 'code', 'code_domaine');
    }
}
