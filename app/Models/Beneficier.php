<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beneficier extends Model
{
    protected $table = 'beneficier';

    protected $fillable = [
        'code_projet',
        'code_acteur',
        'is_active',
    ];

    public $timestamps = true;
}
