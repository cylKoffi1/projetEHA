<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjetInfrastructure extends Model
{
    protected $table = 'projetinfrastructure';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'idInfrastructure',
        'code_projet',
        'localisation_id',
    ];
    public function infra()
    {
        return $this->belongsTo(Infrastructure::class, 'idInfrastructure', 'id');
    }

    public function localisation()
    {
        return $this->belongsTo(ProjetLocalisation::class, 'localisation_id');
    }

    public function valeursCaracteristiques()
    {
        return $this->hasMany(ValeurCaracteristique::class, 'idInfrastructure');
    }

    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }
    public function statuts()
{
    return $this->hasMany(ProjetStatut::class, 'code_projet', 'code_projet');
}

}
