<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcheancierPaiement extends Model
{
    use HasFactory;
    protected $table = 'echeanciers_paiement';
    protected $fillable = ['contrat_id','libelle','pourcentage','montant_prevu','date_prevue','statut_id','conditions'];
    protected $casts = ['pourcentage'=>'decimal:2','montant_prevu'=>'decimal:2','date_prevue'=>'date'];
    public function contrat() { return $this->belongsTo(ContratPrestataire::class,'contrat_id'); }
    public function statut()  { return $this->belongsTo(EcheancierStatut::class,'statut_id'); }
    public function factures(){ return $this->hasMany(FacturePrestataire::class,'echeancier_id'); }

}
