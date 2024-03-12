<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sous_prefecture extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'sous_prefecture'; // Nom de la table
    protected $primaryKey = 'code';
    protected $keyType = 'string';

    public function Departement()
    {
        return $this->belongsTo(Departement::class, 'code_departement');
    }
}
