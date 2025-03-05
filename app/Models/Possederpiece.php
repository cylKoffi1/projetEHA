<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Possederpiece extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'possederpiece';
    protected $primaryKey = 'idPosseder';

    protected $fillable = [
        'idPieceIdent', 'idPersonnePhysique', 'NumPieceIdent',
        'DateExpiration', 'DateEtablissement'
    ];
    public function acteur()
    {
        return $this->belongsTo(Acteur::class, 'idPersonnePhysique', 'code_acteur');
    }
    public function piece()
    {
        return $this->belongsTo(Pieceidentite::class, 'idPieceIdent', 'idPieceIdent');
    }
}
