<?php

namespace App\Models;

use App\Models\GF\AchatLigne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achat extends Model
{
    use HasFactory;

    protected $table = 'gf_achats';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'code_projet',
        'fournisseur_id',
        'date_commande',
        'reference',
        'commentaire',
        // ... ajoute tes colonnes si tu en as d’autres
    ];

    protected $casts = [
        'date_commande' => 'date',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /** Lignes de l’achat */
    public function lignes()
    {
        return $this->hasMany(AchatLigne::class, 'achat_id', 'id');
    }

    /** Exemple d’accessor côté PHP (utile si tu as chargé les relations) */
    public function getMontantTotalAttribute(): float
    {
        if (!$this->relationLoaded('lignes')) {
            return 0.0;
        }

        return (float) $this->lignes->sum(function (AchatLigne $l) {
            return $l->quantite_effective * $l->cout_unitaire_effectif;
        });
    }
}
