<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchatMateriauLigne extends Model
{
    use HasFactory;

    protected $table = 'gf_achat_lignes';
    public $timestamps = false;

    protected $fillable = [
        'achat_id', 'libelle_materiau', 'unite',
        'quantite_prevue', 'quantite_recue',
        'prix_unitaire', 'tva'
    ];

    public function achat() { return $this->belongsTo(AchatMateriau::class, 'achat_id'); }

    public function getMontantHtAttribute()
    {
        return ($this->quantite_prevue ?? 0) * ($this->prix_unitaire ?? 0);
    }

    public function getMontantTtcAttribute()
    {
        return $this->montant_ht * (1 + (($this->tva ?? 0)/100));
    }
}
