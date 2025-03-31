<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profiter extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $table = 'profiter'; // Nom de la table
    protected $primaryKey = 'id';
    protected $fillable = [
        'code_projet',
        'code_pays',
        'code_rattachement'
    ];
}
