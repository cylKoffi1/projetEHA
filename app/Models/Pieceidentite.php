<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pieceidentite extends Model
{
    use HasFactory;

    protected $table = 'pieceidentite'; // Nom de la table
    protected $primaryKey = 'idPieceIdent ';
    protected $fillable = ['libelle_court', 'libelle_long'];

}
