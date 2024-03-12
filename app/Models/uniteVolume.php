<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class uniteVolume extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'unite_volume';
    protected $keyType = 'string';
    protected $primaryKey = 'code';
}
