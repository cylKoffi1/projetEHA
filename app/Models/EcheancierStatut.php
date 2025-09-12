<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcheancierStatut extends Model
{
    use HasFactory;
    protected $table = 'echeancier_statuts';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];

}
