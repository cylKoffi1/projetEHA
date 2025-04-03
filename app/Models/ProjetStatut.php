<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjetStatut extends Model
{
    protected $table = 'projet_statut';
    public $timestamps = true;
    protected $fillable = [
        'code_projet',
        'type_statut',
        'date_statut',
    ];
    protected $primaryKey = 'id'; // Specify the primary key column
    public function statut()
    {
        return $this->hasOne(TypeStatut::class, 'id', 'id');
    }
}
