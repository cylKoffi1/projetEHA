<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormeJuridique extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table ='forme_juridique';
    protected $primaryKey ='id';
}
