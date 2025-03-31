<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtudeProject extends Model
{
    use HasFactory;

    protected $table = 'etudeprojects'; // Nom de la table
    protected $fillable = ['codeEtudeProjets', 'code_projet', 'valider', 'is_deleted', 'created_at', 'updated_at']; // Champs remplissables
    

}
