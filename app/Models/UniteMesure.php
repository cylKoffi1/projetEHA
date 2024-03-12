<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniteMesure extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'unite_mesure';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
}
