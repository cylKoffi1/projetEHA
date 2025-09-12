<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReglementStatut extends Model
{
    use HasFactory;

    protected $table = 'gf_reglement_statuts';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];
}
