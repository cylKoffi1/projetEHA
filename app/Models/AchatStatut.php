<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchatStatut extends Model
{
    use HasFactory;

    protected $table = 'gf_achat_statuts';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];
}
