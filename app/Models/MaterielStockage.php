<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterielStockage extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'materiel_stockage'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
}
