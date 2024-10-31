<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtudeProject extends Model
{
    use HasFactory;

    protected $table = 'etudeprojects'; // Nom de la table
    protected $primaryKey = 'codeEtudeProjets'; // Clé primaire
    public $incrementing = false; // La clé primaire n'est pas incrémentée automatiquement
    protected $fillable = ['codeEtudeProjets', 'natureTravaux', 'codeStatus', 'current_approver', 'typeDemandeur','is_deleted']; // Champs remplissables

    public function files()
    {
        return $this->hasMany(EtudeProjectFile::class, 'codeEtudeProjets');
    }

    public function entreprise()
    {
        return $this->hasOne(EnntrepriseMorale::class, 'codeEtudeProjets', 'codeEtudeProjets');
    }

    public function particulier()
    {
        return $this->hasOne(EntrepriseParticulier::class, 'codeEtudeProjets', 'codeEtudeProjets');
    }

}
