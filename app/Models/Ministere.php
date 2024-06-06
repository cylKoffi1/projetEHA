<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ministere extends Model
{
    use HasFactory;


    public $timestamps = false;

    protected $table = 'ministere'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
}   
