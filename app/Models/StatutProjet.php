<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutProjet extends Model
{
    use HasFactory;


    public $timestamps = false;
    protected $table = 'statut_projet'; // Nom de la table
    protected $primaryKey = 'code';
    protected $keyType = 'string';
}
