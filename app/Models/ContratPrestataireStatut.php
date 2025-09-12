<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratPrestataireStatut extends Model
{
    use HasFactory;
    protected $table = 'contrat_prestataire_statuts';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];

}
