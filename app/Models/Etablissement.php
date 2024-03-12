<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etablissement extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table ='etablissement';
    protected $primaryKey ='code';
    protected $keyType = 'string';

    public function genre()
    {
        return $this->belongsTo(Genre::class, 'code_genre', 'code_genre');
    }

    public function niveaux()
    {
        return $this->belongsTo(NiveauEtablissement::class, 'code_niveau', 'code');
    }

    public function localite()
    {
        return $this->belongsTo(Localite::class, 'code_localite', 'code');
    }
    public function accessibilite()
    {
        return $this->belongsTo(TypeAccessibilite::class, 'code_accessibilite', 'code');
    }
}
