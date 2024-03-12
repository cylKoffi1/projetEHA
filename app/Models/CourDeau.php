<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourDeau extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'cours_eau'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
}
