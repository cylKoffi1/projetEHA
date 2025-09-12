<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DecaissementEtat extends Model
{
    use HasFactory;

    protected $table = 'gf_decaissement_etats';
    protected $fillable = ['code', 'libelle'];
}
