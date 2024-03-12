<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StructureRattachement extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'structure_rattachement'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code_personnel';
    protected $fillable = [
        'code_personnel',
        'code_structure',
        'type_structure',
        'date'
    ];
}
