<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstanceEtape extends Model
{
    use HasFactory;

    protected $table = 'instances_etapes';
    protected $fillable = [
        'instance_approbation_id','etape_workflow_id','statut_id',
        'quorum_requis','nombre_approbations','date_debut','date_fin'
    ];
    protected $casts = [
        'statut_id' => 'integer',
        'quorum_requis' => 'integer',
        'nombre_approbations' => 'integer',
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
      ];
    public function instance()
    {
        return $this->belongsTo(InstanceApprobation::class,'instance_approbation_id');
    }

    public function etape()
    {
        return $this->belongsTo(EtapeWorkflow::class,'etape_workflow_id');
    }

    public function statut()
    {
        return $this->belongsTo(StatutEtapeInstance::class,'statut_id');
    }

    public function actions()
    {
        return $this->hasMany(ActionApprobation::class,'instance_etape_id');
    }
}
