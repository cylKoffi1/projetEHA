<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeTravauxConnexes extends Model
{
    protected $table = 'types_travaux_connexes';

    protected $fillable = ['libelle'];
}
