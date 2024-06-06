<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjetAgence extends Model
{
    protected $table = 'projet_agence';
    protected $primaryKey = 'code';
    public $timestamps = false;
    protected $fillable = [
        'code_projet',
        'code_agence',
        'niveau'
    ];
    public function agenceExecution()
    {
        return $this->belongsTo(AgenceExecution::class, 'code_agence', 'code_agence_execution');
    }
}
