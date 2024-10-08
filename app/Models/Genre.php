<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table ='genre';
    protected $primaryKey ='code_genre';
    protected $keyType = 'string';
}
