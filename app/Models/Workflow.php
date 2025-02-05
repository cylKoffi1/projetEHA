<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;

    protected $table = 'workflows';
    protected $fillable = ['libelle', 'description'];

    public function etapes()
    {
        return $this->hasMany(WorkflowEtape::class, 'workflow_id');
    }
}
