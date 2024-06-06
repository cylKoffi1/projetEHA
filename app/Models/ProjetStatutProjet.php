<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjetStatutProjet extends Model
{
    protected $table = 'projet_statut_projet';
    public $timestamps = false;
    protected $fillable = [
        'code_projet',
        'code_statut_projet',
        'date',
    ];
    protected $primaryKey = 'code'; // Specify the primary key column
    public function statut()
    {
        return $this->hasOne(StatutProjet::class, 'code', 'code_statut_projet');
    }
}
