<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeFinancement extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'type_financement'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code_type_financement';

    
}
