<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ProjetLocalisation extends Model
{
    protected $table = 'projetLocalisation';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'code_projet',
        'pays_code',
        'code_localite',
        'niveau',
        'decoupage',
    ];

    // Exemple de relation (si Projet existe)
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }
}