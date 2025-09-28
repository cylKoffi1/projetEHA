<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VersionWorkflow extends Model
{
    use HasFactory;

    protected $table = 'versions_workflow';
    protected $fillable = [
        'workflow_id','numero_version','politique_changement',
        'metadonnees','publie'
    ];

    protected $casts = [
        'metadonnees' => 'array',
        'publie' => 'boolean',
    ];
    public function workflow()
    {
        return $this->belongsTo(WorkflowApprobation::class,'workflow_id');
    }

    public function etapes()
    {
        return $this->hasMany(EtapeWorkflow::class,'version_workflow_id');
    }

    public function liaisons()
    {
        return $this->hasMany(LiaisonWorkflow::class,'version_workflow_id');
    }
}
