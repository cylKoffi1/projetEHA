<?php
// app/Models/GF/DecaissementStatut.php

namespace App\Models;

use App\Models\Decaissement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DecaissementStatut extends Model
{
    use HasFactory;

    protected $table = 'gf_decaissement_statuts';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = ['code','libelle','ordre','couleur','is_final'];

    // Si tu as un modèle Decaissement dans App\Models\GF\Decaissement
    public function decaissements()
    {
        return $this->hasMany(Decaissement::class, 'statut_id', 'id');
    }
}
