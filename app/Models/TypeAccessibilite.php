<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeAccessibilite extends Model
{
    use HasFactory;


    protected $table ='type_accessibilite';
    protected $primaryKey ='code';
    protected $keyType = 'string';
}
