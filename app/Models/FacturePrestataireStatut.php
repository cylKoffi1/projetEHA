<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturePrestataireStatut extends Model
{
    use HasFactory;
    protected $table = 'facture_prestataire_statuts';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];

}
