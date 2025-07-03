<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Infrastructure extends Model
{
    use HasFactory;

    protected $table = 'infrastructures';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'code',
        'libelle',
        'code_Ssys',
        'code_groupe_projet',
        'code_pays',
        'code_localite',
        'date_operation',
        'imageInfras',
        'latitude',
        'longitude'
    ];
    
    public function groupeProjet()
    {
        return $this->belongsTo(GroupeProjet::class, 'code_groupe_projet', 'code');
    }
    // Relation avec la famille d'infrastructure
    public function familleInfrastructure()
    {
        return $this->belongsTo(FamilleInfrastructure::class, 'code_Ssys', 'code_Ssys');
    }

    public function familleDomaine(){
        return $this->belongsTo(FamilleDomaine::class, 'code_Ssys', 'code_Ssys');
    }
    // Relation avec les valeurs de caractéristiques
    public function valeursCaracteristiques()
    {
        return $this->hasMany(ValeurCaracteristique::class, 'infrastructure_code', 'code');
    }

    public function localisation(){
        return $this->belongsTo(LocalitesPays::class, 'code_localite', 'id')
        ->where('id_pays', session('pays_selectionne'));
    }
    
    public function projetInfra()
    {
        return $this->hasOne(ProjetInfrastructure::class, 'idInfrastructure');
    }

    public function InfrastructureImage(){
        return $this->hasMany(InfrastructureImage::class, 'infrastructure_code', 'code'); 
    }
}