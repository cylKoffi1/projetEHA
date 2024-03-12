<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acquifere extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'acquifere'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
}
