<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtudeProject extends Model
{
    use HasFactory;

    protected $table = 'etudeprojects'; // Nom de la table
    protected $fillable = ['codeEtudeProjets', 'natureTravaux', 'codeStatus', 'current_approver', 'typeDemandeur','is_deleted']; // Champs remplissables
    public function approvals()
    {
        return $this->hasMany(ProjectApproval::class, 'codeEtudeProjets', 'codeEtudeProjets'); // Adaptez les clés selon votre schéma
    }
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
