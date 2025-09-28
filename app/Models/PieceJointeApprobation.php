<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PieceJointeApprobation extends Model
{
    use HasFactory;
    protected $table = 'pieces_jointes_approbation';
    public $timestamps = false;
    protected $fillable = [
        'action_approbation_id','chemin_fichier','nom_fichier','taille_octets','created_at'
    ];

    public function action()
    {
        return $this->belongsTo(ActionApprobation::class,'action_approbation_id');
    }
}
