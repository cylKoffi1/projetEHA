<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banque extends Model
{
    use SoftDeletes;

    protected $table = 'banques';

    protected $fillable = [
        'nom', 'sigle', 'code_pays', 'est_internationale',
        'code_swift', 'adresse', 'telephone', 'email', 'site_web', 'actif'
    ];

    protected $casts = [
        'est_internationale' => 'boolean',
        'actif' => 'boolean',
    ];

    /** Pays ISO par alpha-3 (ex: CIV) */
    public function pays(): BelongsTo
    {
        return $this->belongsTo(Pays::class, 'code_pays', 'alpha3');
    }

    /** Affichage rapide */
    public function getLibellePaysAttribute(): string
    {
        return $this->est_internationale ? 'Internationale' : ($this->code_pays ?: '');
    }

    /** Uppercase automatique du code pays */
    public function setCodePaysAttribute($value): void
    {
        $this->attributes['code_pays'] = $value ? strtoupper(trim($value)) : null;
    }

    /** Uppercase + trim automatique du SWIFT */
    public function setCodeSwiftAttribute($value): void
    {
        $this->attributes['code_swift'] = $value ? strtoupper(trim($value)) : null;
    }
}
