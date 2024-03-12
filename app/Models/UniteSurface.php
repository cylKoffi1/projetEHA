<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniteSurface extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'unite_surface'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
}
