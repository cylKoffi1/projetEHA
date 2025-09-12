<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LivrableStatut extends Model
{
    use HasFactory;
    protected $table = 'livrable_statuts';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];

}
