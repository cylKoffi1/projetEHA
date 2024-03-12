<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgenceExecution extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'agence_execution'; // Nom de la table   
    protected $keyType = 'string';
    protected $primaryKey = 'code_agence_execution';
}
