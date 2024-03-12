<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiaire extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'beneficiaire'; // Nom de la table
    protected $primaryKey = 'code';
    

}
