<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeBailleur extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'type_bailleur'; // Nom de la table   
    protected $keyType = 'string';
    protected $primaryKey = 'code';

}
