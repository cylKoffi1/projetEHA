<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactureAchatStatut extends Model
{
    use HasFactory;
    protected $table = 'facture_achat_statuts';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];
}
