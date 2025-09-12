<?php

namespace App\Models\GF;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchatLigne extends Model
{
    use HasFactory;

    protected $table = 'gf_achat_lignes';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'achat_id',
        'materiau_id',
        'materiau_libelle',
        'quantite_prevue',
        'quantite_receptionnee', // si elle existe
        'quantite_recue',        // si c’est ce nom que tu as en DB
        'cout_unitaire',         // si présent
        'prix_unitaire',         // sinon
        'unite',
        'tva',
    ];

    protected $casts = [
        'quantite_prevue'        => 'decimal:3',
        'quantite_receptionnee'  => 'decimal:3',
        'quantite_recue'         => 'decimal:3',
        'cout_unitaire'          => 'decimal:3',
        'prix_unitaire'          => 'decimal:3',
        'tva'                    => 'decimal:2',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
    ];

    protected $appends = [
        'cout_unitaire_effectif',
        'quantite_effective',
        'montant_prevu',
        'montant_receptionne',
    ];

    /* Relations */
    public function achat()
    {
        return $this->belongsTo(Achat::class, 'achat_id', 'id');
    }

    public function materiau()
    {
        return $this->belongsTo(Materiau::class, 'materiau_id', 'id');
    }

    /* Scopes */
    public function scopeForYear($query, int $year)
    {
        return $query->whereHas('achat', function ($q) use ($year) {
            $q->whereYear('date_commande', $year);
        });
    }

    public function scopeForProjet($query, string $codeProjet)
    {
        return $query->whereHas('achat', function ($q) use ($codeProjet) {
            $q->where('code_projet', $codeProjet);
        });
    }

    /* Accessors calculés */
    public function getCoutUnitaireEffectifAttribute(): float
    {
        // 1) colonne l.cout_unitaire si dispo
        $val = $this->getAttribute('cout_unitaire');

        // 2) sinon l.prix_unitaire
        if ($val === null) {
            $val = $this->getAttribute('prix_unitaire');
        }

        // 3) sinon prix du matériau référentiel
        if (($val === null || $val == 0) && ($this->relationLoaded('materiau') || $this->materiau)) {
            $val = $this->materiau->prix_unitaire ?? $val;
        }

        return (float) ($val ?? 0);
    }

    public function getQuantiteEffectiveAttribute(): float
    {
        // 1) l.quantite_recue si présent
        $qte = $this->getAttribute('quantite_recue');

        // 2) sinon l.quantite_receptionnee
        if ($qte === null) {
            $qte = $this->getAttribute('quantite_receptionnee');
        }

        // 3) sinon l.quantite_prevue
        if ($qte === null) {
            $qte = $this->getAttribute('quantite_prevue');
        }

        return (float) ($qte ?? 0);
    }

    public function getMontantPrevuAttribute(): float
    {
        $qte = (float) ($this->getAttribute('quantite_prevue') ?? 0);
        return $qte * $this->cout_unitaire_effectif;
    }

    public function getMontantReceptionneAttribute(): float
    {
        $qte = (float) ($this->getAttribute('quantite_recue') ?? $this->getAttribute('quantite_receptionnee') ?? 0);
        return $qte * $this->cout_unitaire_effectif;
    }

    /* Helper */
    public function getMateriauLabel(): string
    {
        if ($this->relationLoaded('materiau') && $this->materiau) {
            return (string) ($this->materiau->libelle ?? $this->materiau->nom ?? $this->materiau->code ?? '');
        }
        return (string) ($this->materiau_libelle ?? '');
    }
}
