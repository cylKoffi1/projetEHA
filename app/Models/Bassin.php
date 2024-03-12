<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bassin extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'bassins'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
}
