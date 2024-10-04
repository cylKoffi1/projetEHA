<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeStation extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'type_station'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
    protected $fillable = ['code', 'libelle'];
}
