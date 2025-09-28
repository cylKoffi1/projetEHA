<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstanceApprobation extends Model
{
    use HasFactory;

    protected $table = 'instances_approbation';
    protected $fillable = [
        'version_workflow_id','module_code','type_cible','id_cible',
        'statut_id','instantane'
    ];
    protected $casts = [
        'instantane' => 'array',
        'statut_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function version()
    {
        return $this->belongsTo(VersionWorkflow::class,'version_workflow_id');
    }

    public function statut()
    {
        return $this->belongsTo(StatutInstance::class,'statut_id');
    }

    public function etapes()
    {
        return $this->hasMany(InstanceEtape::class,'instance_approbation_id');
    }
}
