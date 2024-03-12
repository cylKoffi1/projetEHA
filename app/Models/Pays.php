<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pays extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'pays'; // Nom de la table   
    protected $primaryKey = 'id';
  
}
