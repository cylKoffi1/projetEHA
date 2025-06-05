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
        'code_famille_infrastructure',
        'code_groupe_projet',
        'code_pays',
        'code_localite',
        'date_operation',
        'imageInfras',
        'latitude',
        'longitude'
    ];
    

    // Relation avec la famille d'infrastructure
    public function familleInfrastructure()
    {
        return $this->belongsTo(FamilleInfrastructure::class, 'code_famille_infrastructure', 'code_famille');
    }

    // Relation avec les valeurs de caractéristiques
    public function valeursCaracteristiques()
    {
        return $this->hasMany(ValeurCaracteristique::class, 'idInfrastructure');
    }

    public function localisation(){
        return $this->belongsTo(LocalitesPays::class, 'code_localite', 'id')
        ->where('id_pays', session('pays_selectionne'));
    }
    
    public function projetInfra()
    {
        return $this->hasOne(ProjetInfrastructure::class, 'idInfrastructure');
    }
}