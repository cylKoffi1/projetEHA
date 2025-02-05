<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValidationDecision extends Model
{
    use HasFactory;

    protected $table = 'validation_decisions';
    protected $fillable = ['demande_id', 'etape_id', 'valideur_id', 'statut_id', 'commentaire'];

    public function demande()
    {
        return $this->belongsTo(DemandeValidation::class, 'demande_id');
    }

    public function etape()
    {
        return $this->belongsTo(WorkflowEtape::class, 'etape_id');
    }

    public function statut()
    {
        return $this->belongsTo(ValidationStatut::class, 'statut_id');
    }
}
