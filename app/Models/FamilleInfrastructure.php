<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilleInfrastructure extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'familleinfrastructure'; 
    protected $primaryKey = 'idFamille';
    protected $fillable = ['code_famille','code_domaine', 'code_sdomaine', 'libelleFamille', 'code_groupe_projet'];
    
   
    public function domaine()
    {
        return $this->belongsTo(Domaine::class, 'code_domaine', 'code')
                    ->where('groupe_projet_code', session('projet_selectionne'));
    }

    public function sousdomaine()
    {
        return $this->belongsTo(SousDomaine::class, 'code_sdomaine', 'code_sous_domaine')
                    ->where('code_groupe_projet', session('projet_selectionne'));
    }
    // Relation avec les infrastructures
    public function infrastructures()
    {
        return $this->hasMany(Infrastructure::class, 'code_famille_infrastructure', 'code_sdomaine');
    }

    public function caracteristiques()
    {
        return $this->belongsToMany(Caracteristique::class, 'famille_caracteristique', 'idFamille', 'idCaracteristique');
    }
}
