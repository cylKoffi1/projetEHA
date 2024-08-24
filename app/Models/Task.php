<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    // Table associée au modèle
    protected $table = 'tasks';

    // Champs qui peuvent être assignés en masse
    protected $fillable = [
        'project_id',
        'name',
        'description',
        'start_date',
        'end_date',
    ];

    // Définit la relation avec le modèle EtudeProject
    public function project()
    {
        return $this->belongsTo(EtudeProject::class, 'project_id');
    }
}
