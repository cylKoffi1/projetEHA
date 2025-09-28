<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutEtapeInstance extends Model
{
    use HasFactory;
    protected $table = 'ref_etape_instance_statut';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];
}
