<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjetDocument extends Model
{
    use HasFactory;

    protected $table = 'projet_documents'; // Nom de la table

    protected $fillable = [
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_at',
        'code_projet',
    ];

    public $timestamps = false; 
    // 🔁 Relation (si `code_projet` est lié à un autre modèle)
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet'); 
    }
}
