<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class   SousDomaineTypeCaract extends Model
{
    protected $table = 'sousdomtypecaract';

    // Spécifie les colonnes qui peuvent être remplies en masse
    protected $fillable = [
        'CodeSousDomaine',
        'CaractTypeTable',
    ];
}
