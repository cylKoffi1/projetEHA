<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StructureRattachement extends Model
{
    use HasFactory;
    public $timestamps = false; 

    protected $table = 'structure_rattachement'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code_personnel';
    protected $fillable = [
        'code_personnel',
        'code_structure',
        'type_structure',
        'date'
    ];
    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'code_personnel', 'code_personnel');
    }

    public function agence()
    {
        return $this->belongsTo(AgenceExecution::class, 'code_structure', 'code_agence_execution');
    }

    public function ministere()
    {
        return $this->belongsTo(Ministere::class, 'code_structure', 'code');
    }

    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'code_structure', 'code_bailleur');
    }
}
