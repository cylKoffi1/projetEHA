<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiaisonWorkflow extends Model
{
    protected $table = 'liaisons_workflow';

    protected $fillable = [
        'version_workflow_id','module_code','type_cible','id_cible',
        'par_defaut','module_type_id','code_pays','groupe_projet_id'
    ];

    public function version()
    {
        return $this->belongsTo(VersionWorkflow::class, 'version_workflow_id');
    }

    public function moduleType()
    {
        return $this->belongsTo(ModuleWorkflowDisponible::class, 'module_type_id');
    }
}
