<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiaisonWorkflow extends Model
{
    use HasFactory;

    protected $table = 'liaisons_workflow';
    protected $fillable = [
        'version_workflow_id','module_code','type_cible','id_cible','par_defaut'
    ];

    public function version()
    {
        return $this->belongsTo(VersionWorkflow::class,'version_workflow_id');
    }
}
