<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutInstance extends Model
{
    use HasFactory;
    protected $table = 'ref_instance_statut';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];
}
