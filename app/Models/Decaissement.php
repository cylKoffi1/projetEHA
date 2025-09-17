<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Decaissement extends Model
{
    protected $table = 'gf_decaissements'; // adapte au nom réel de ta table
    public $timestamps = true;

    protected $fillable = [
        'code_projet',
        'code_acteur',         // bailleur
        'reference',
        'tranche_no',
        'montant',
        'devise',
        'date_decaissement',
        'commentaire',
        'created_by',
    ];

    protected $casts = [
        'tranche_no'        => 'integer', 
        'montant'           => 'decimal:2',
        'date_decaissement' => 'date',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    // Relations
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }

    public function financer()
    {
        return $this->belongsTo(Financer::class, 'code_acteur');
    }

    public function bailleur()
    {
        return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur');
    }

}
