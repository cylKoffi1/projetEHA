<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtudeProject extends Model
{
    use HasFactory;

    protected $table = 'etudeprojects'; // Nom de la table
    protected $fillable = ['codeEtudeProjets', 'code_projet', 'valider', 'is_deleted', 'created_at', 'updated_at']; // Champs remplissables
    
    protected $primaryKey = 'codeEtudeProjets';
    public $incrementing = false;
    protected $keyType = 'string';

    public function projet(){
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }
    public function approbations()
    {
        return $this->hasMany(ProjetApprobation::class, 'codeEtudeProjets', 'codeEtudeProjets');
    }

}
