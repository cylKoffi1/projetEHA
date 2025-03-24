<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jouir extends Model
{
    protected $table = 'jouir';

    protected $fillable = [
        'code_projet',
        'code_Infrastructure',
    ];

    public $timestamps = true; // created_at & updated_at
}
