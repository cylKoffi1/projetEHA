<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    protected $table = 'Station';

    // Indiquez les attributs qui peuvent être remplis en masse
    protected $fillable = [
        'Nom',
        'Code_international',
        'CodeProjet',
        'ID_Aquifère',
        'ID_Bassin',
        'ID_Cours_d_eau',
    ];
}
