<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UniteDistance extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'unite_distance';
    protected $keyType = 'string';
    protected $primaryKey = 'code';
}
