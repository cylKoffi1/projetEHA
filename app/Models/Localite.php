<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Localite extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'localite'; // Nom de la table
    protected $primaryKey = 'code'; 
    protected $keyType = 'string';

    public function Sous_prefecture()
    {
        return $this->belongsTo(Sous_prefecture::class, 'code_sous_prefecture');
    }
}
