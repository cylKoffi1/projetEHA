<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectApproval extends Model
{
    use HasFactory;

    protected $table = 'project_approbation'; // Nom de la table
    protected $primaryKey = 'id';
    protected $fillable =['codeEtudeProjets', 'codeAppro', 'is_approved', 'approved_at'];
    // Relation avec le modèle Approbateur
    public function approbateur()
    {
        return $this->belongsTo(Approbateur::class, 'codeAppro', 'codeAppro');
    }
}
