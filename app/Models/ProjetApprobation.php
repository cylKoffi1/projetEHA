<?php

namespace App\Models;

use App\Http\Controllers\EtudeProjet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjetApprobation extends Model
{
    use HasFactory;

    protected $table = 'project_approbation'; // Nom de la table
    protected $primaryKey = 'id';
    protected $fillable = [
        'codeEtudeProjets',
        'code_acteur',
        'num_ordre',
        'is_approved',
        'approved_at',
        'statut_validation_id',
        'commentaire_refus'
    ];
    // Relation avec le modèle Approbateur
    public function approbateur()
    {
        return $this->belongsTo(Approbateur::class, 'code_acteur', 'code_acteur');
    }

    public function etude(){
        return $this->belongsTo(EtudeProject::class, 'codeEtudeProjets', 'codeEtudeProjets');
    }

    public function statutValidation()
    {
        return $this->belongsTo(ValidationStatut::class, 'statut_validation_id', 'id');
    }
    protected static function booted()
    {
        static::saving(function ($model) {
            if ($model->statut_validation_id == 3 && empty($model->commentaire_refus)) {
                throw new \Exception('Un commentaire est obligatoire en cas de refus.');
            }
        });
    }
    
}
