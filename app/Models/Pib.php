<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pib extends Model
{
    use HasFactory;

    protected $table = 'pib'; // Nom de la table
    protected $primaryKey = 'code';
    protected $fillable = ['code_pays', 'annee', 'montant_pib'];
    public function pays()
    {
        return $this->belongsTo(Pays::class, 'code_pays', 'id_pays');
    }
}
