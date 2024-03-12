<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domaine extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'domaine_intervention'; // Nom de la table   
    protected $keyType = 'string';
    protected $primaryKey = 'code';
}
