<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniteTraitement extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'unite_traitement'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
}
