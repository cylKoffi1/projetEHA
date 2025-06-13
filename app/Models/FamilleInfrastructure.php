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
    protected $fillable = ['code_famille', 'libelleFamille'];
 
    public function familleDomaine()
    {
        return $this->hasMany(FamilleDomaine::class, 'code_famille', 'code_famille');
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
