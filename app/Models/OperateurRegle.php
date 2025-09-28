<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperateurRegle extends Model
{
    use HasFactory;
    protected $table = 'ref_operateur_regle';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];
}
