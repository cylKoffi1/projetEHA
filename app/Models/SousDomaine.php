<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SousDomaine extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'sous_domaine'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';

    public function Domaine()
    {
        return $this->belongsTo(Domaine::class, 'code_domaine', 'code');
    }

}
