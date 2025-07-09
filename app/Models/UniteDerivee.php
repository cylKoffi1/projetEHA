<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UniteDerivee extends Model
{
    protected $table = 'unites_derivees';

    protected $fillable = [
        'code',
        'libelle',
        'formule',
        'id_unite_base',
    ];

    /**
     * Relation vers l'unité de base (mètre, seconde, kg, etc.)
     */
    public function uniteBase(): BelongsTo
    {
        return $this->belongsTo(Unite::class, 'id_unite_base', 'idUnite');
    }
}
