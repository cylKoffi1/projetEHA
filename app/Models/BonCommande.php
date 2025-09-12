<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonCommande extends Model
{
    use HasFactory;
    protected $table = 'bons_commandes';
    protected $fillable = ['code_projet','fournisseur_id','date_bc','reference','montant_estime','devise','statut_id'];
    protected $casts = ['date_bc'=>'date','montant_estime'=>'decimal:2'];
    public function projet()      { return $this->belongsTo(Projet::class,'code_projet','code_projet'); }
    public function fournisseur() { return $this->belongsTo(Acteur::class,'fournisseur_id','code_acteur'); }
    public function statut()      { return $this->belongsTo(BonCommandeStatut::class,'statut_id'); }
    public function lignes()      { return $this->hasMany(BcLigne::class,'bc_id'); }
    public function receptions()  { return $this->hasMany(BonReception::class,'bc_id'); }
    public function factures()    { return $this->hasMany(FactureAchat::class,'bc_id'); }

}
