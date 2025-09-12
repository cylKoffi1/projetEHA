<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReglementAchat extends Model
{
    use HasFactory;
    protected $table = 'reglements_achats';
    protected $fillable = ['facture_achat_id','date_paiement','montant','mode_paiement_id','reference'];
    protected $casts = ['date_paiement'=>'date','montant'=>'decimal:2'];
    public function facture()    { return $this->belongsTo(FactureAchat::class,'facture_achat_id'); }
    public function modePaiement(){ return $this->belongsTo(ModePaiement::class,'mode_paiement_id'); }

}
