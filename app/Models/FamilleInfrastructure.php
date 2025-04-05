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
    protected $fillable = ['code_domaine', 'code_sdomaine', 'libelleFamille'];
    
    public function domaine()
    {
        return $this->belongsTo(Domaine::class, 'code_domaine', 'code');
    }    
    public function sousdomaine()
    {
        return $this->belongsTo(SousDomaine::class, 'code_sdomaine', 'code_sous_domaine');
    }
    // Relation avec les infrastructures
    public function infrastructures()
    {
        return $this->hasMany(Infrastructure::class, 'code_famille_infrastructure', 'code_sdomaine');
    }
}
