<?php
// app/Models/AchatMateriau.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AchatMateriau extends Model
{
    protected $table = 'gf_achats';
    protected $fillable = [
        'code_projet', 'code_acteur', 'reference_bc', 'date_commande',
        'devise', 'statut_id', 'commentaire', 'created_by'
    ];

    protected $casts = [
        'date_commande' => 'date',
    ];

    public function projet()      { return $this->belongsTo(Projet::class, 'code_projet', 'code_projet'); }
    public function fournisseur() { return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur'); }
    public function statut()      { return $this->belongsTo(AchatStatut::class, 'statut_id'); }
    public function lignes()      { return $this->hasMany(AchatMateriauLigne::class, 'achat_id'); }

    public function getTotalHtAttribute()
    {
        return $this->lignes->sum(fn($l) => ($l->quantite_prevue ?? 0) * ($l->prix_unitaire ?? 0));
    }

    public function getTotalTvaAttribute()
    {
        return $this->lignes->sum(function($l){
            $ht = ($l->quantite_prevue ?? 0) * ($l->prix_unitaire ?? 0);
            return $ht * (($l->tva ?? 0) / 100);
        });
    }

    public function getTotalTtcAttribute()
    {
        return $this->total_ht + $this->total_tva;
    }
}
