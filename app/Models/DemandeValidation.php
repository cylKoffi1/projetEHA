<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeValidation extends Model
{
    use HasFactory;

    protected $table = 'demandes_validation';
    protected $fillable = ['type_id', 'workflow_id', 'element_id', 'statut_id', 'utilisateur_id'];

    public function type()
    {
        return $this->belongsTo(ValidationType::class, 'type_id');
    }

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function element()
    {
        return $this->belongsTo(ElementValidation::class, 'element_id');
    }

    public function statut()
    {
        return $this->belongsTo(ValidationStatut::class, 'statut_id');
    }

    public function decisions()
    {
        return $this->hasMany(ValidationDecision::class, 'demande_id');
    }
}
