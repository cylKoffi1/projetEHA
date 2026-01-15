<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EtudeType extends Model
{
    use HasFactory;

    protected $table = 'etude_types';

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    // Un type peut concerner plusieurs études
    public function etudes()
    {
        // EtudeProjet.type_etude_code -> EtudeType.code
        return $this->hasMany(EtudeProjet::class, 'type_etude_code', 'code');
    }
}
