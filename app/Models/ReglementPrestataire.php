<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReglementPrestataire extends Model
{
    use HasFactory;
    protected $table = 'gf_reglements';
    protected $fillable = [
        'code_projet','code_acteur',
        'reference_facture','date_facture','tranche_no',
        'montant_facture','montant_regle','devise',
        'mode_id','statut_id','date_reglement',
        'commentaire','created_by'
    ];

    protected $casts = [
        'date_facture'    => 'date',
        'date_reglement'  => 'date',
        'montant_facture' => 'decimal:2',
        'montant_regle'   => 'decimal:2',
        'tranche_no'      => 'integer',
      ];

    public function projet()      { return $this->belongsTo(Projet::class, 'code_projet', 'code_projet'); }
    public function prestataire() { return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur'); }
    public function mode()        { return $this->belongsTo(ModePaiement::class, 'mode_id'); }
    public function statut()      { return $this->belongsTo(ReglementStatut::class, 'statut_id'); }

    public function getSoldeAttribute()
    {
        if (is_null($this->montant_facture)) return null;
        return (float)$this->montant_facture - (float)$this->montant_regle;
    }
}
