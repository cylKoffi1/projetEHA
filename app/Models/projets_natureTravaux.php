<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class projets_natureTravaux extends Model
{
    use HasFactory;
    protected $table = 'projets_natureTravaux';
    protected $primaryKey = 'id_PNT'; 

    // Colonnes modifiables
    protected $fillable = [
        'code_projet',
        'code_nature',
        'date'
    ];
}
