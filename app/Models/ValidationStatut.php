<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationStatut extends Model
{
    use HasFactory;

    protected $table = 'validation_statuts';
    protected $fillable = ['id', 'libelle'];
}
