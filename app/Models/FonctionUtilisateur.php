<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FonctionUtilisateur extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'fonction_utilisateur'; // Nom de la table   
    protected $keyType = 'string';
    protected $primaryKey = 'code';
}
