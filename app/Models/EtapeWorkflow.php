<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapeWorkflow extends Model
{
    use HasFactory;

    protected $table = 'etapes_workflow';
    protected $fillable = [
        'version_workflow_id','position','mode_id','quorum',
        'sla_heures','delegation_autorisee','sauter_si_vide',
        'politique_reapprobation','metadonnees'
    ];
    protected $casts = [
        'quorum' => 'integer',
        'sla_heures' => 'integer',
        'delegation_autorisee' => 'boolean',
        'sauter_si_vide' => 'boolean',
        'politique_reapprobation' => 'array',
        'metadonnees' => 'array',
      ];
    public function version()
    {
        return $this->belongsTo(VersionWorkflow::class,'version_workflow_id');
    }

    public function approbateurs()
    {
        return $this->hasMany(EtapeApprobateur::class,'etape_workflow_id');
    }

    public function regles()
    {
        return $this->hasMany(EtapeRegle::class,'etape_workflow_id');
    }
}
