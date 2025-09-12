<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livrable extends Model
{
    use HasFactory;
    protected $table = 'livrables';
    protected $fillable = ['contrat_id','libelle','date_reception','statut_id','fichier'];
    protected $casts = ['date_reception'=>'date'];
    public function contrat() { return $this->belongsTo(ContratPrestataire::class,'contrat_id'); }
    public function statut()  { return $this->belongsTo(LivrableStatut::class,'statut_id'); }

}
