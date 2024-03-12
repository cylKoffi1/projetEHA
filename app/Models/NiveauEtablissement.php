<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NiveauEtablissement extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table ='niveau_etablissement';
    protected $primaryKey ='code';
    protected $keyType = 'string';


    public function typeEtablissement()
    {
        return $this->belongsTo(TypeEtablissement::class, 'code_type_etablissement', 'code');
    }
}
