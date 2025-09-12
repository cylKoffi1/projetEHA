<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratPrestataire extends Model
{
    use HasFactory;
    protected $table = 'contrats_prestataires';
    protected $fillable = ['code_projet','prestataire_id','objet','montant_ttc','devise','date_debut','date_fin','statut_id'];
    protected $casts = ['date_debut'=>'date','date_fin'=>'date','montant_ttc'=>'decimal:2'];
    public function projet()      { return $this->belongsTo(Projet::class,'code_projet','code_projet'); }
    public function prestataire() { return $this->belongsTo(Acteur::class,'prestataire_id','code_acteur'); }
    public function statut()      { return $this->belongsTo(ContratPrestataireStatut::class,'statut_id'); }
    public function echeanciers() { return $this->hasMany(EcheancierPaiement::class,'contrat_id'); }
    public function factures()    { return $this->hasMany(FacturePrestataire::class,'contrat_id'); }
    public function ordresService(){ return $this->hasMany(OrdreService::class,'contrat_id'); }
    public function livrables()   { return $this->hasMany(Livrable::class,'contrat_id'); }

}
