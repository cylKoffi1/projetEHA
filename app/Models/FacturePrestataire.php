<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturePrestataire extends Model
{
    use HasFactory;
    protected $table = 'factures_prestataires';
    protected $fillable = ['contrat_id','echeancier_id','numero','date_facture','montant','statut_id','fichier_pdf'];
    protected $casts = ['date_facture'=>'date','montant'=>'decimal:2'];
    public function contrat()    { return $this->belongsTo(ContratPrestataire::class,'contrat_id'); }
    public function echeancier() { return $this->belongsTo(EcheancierPaiement::class,'echeancier_id'); }
    public function statut()     { return $this->belongsTo(FacturePrestataireStatut::class,'statut_id'); }
    public function reglements() { return $this->hasMany(ReglementPrestataire::class,'facture_id'); }

}
