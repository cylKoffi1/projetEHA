<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowEtape extends Model
{
    use HasFactory;

    protected $table = 'workflow_etapes';
    protected $fillable = ['workflow_id', 'libelle', 'ordre', 'role_responsable'];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }
}
