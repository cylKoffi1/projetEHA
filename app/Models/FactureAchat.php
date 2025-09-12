<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactureAchat extends Model
{
    use HasFactory;
    protected $table = 'factures_achats';
    protected $fillable = ['bc_id','numero','date_facture','montant_ht','tva','autres_taxes','montant_ttc','statut_id','fichier_pdf'];
    protected $casts = ['date_facture'=>'date','montant_ht'=>'decimal:2','tva'=>'decimal:2','autres_taxes'=>'decimal:2','montant_ttc'=>'decimal:2'];
    public function bc()      { return $this->belongsTo(BonCommande::class,'bc_id'); }
    public function statut()  { return $this->belongsTo(FactureAchatStatut::class,'statut_id'); }
    public function reglements(){ return $this->hasMany(ReglementAchat::class,'facture_achat_id'); }

}
