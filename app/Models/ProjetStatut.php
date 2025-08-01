<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjetStatut extends Model
{
    protected $table = 'projet_statut';
    public $timestamps = true;
    protected $primaryKey = 'id'; 
    protected $fillable = [
        'code_projet',
        'type_statut',
        'date_statut',
        'motif'
    ];
    public function statut()
    {
        return $this->hasOne(TypeStatut::class, 'id', 'type_statut');
    }
}
