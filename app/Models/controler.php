<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Controler extends Model
{
    use HasFactory;

    protected $table = 'controler'; // table inchangée
    protected $primaryKey = 'id';

    protected $fillable = [
        'code_projet',
        'code_acteur',
        'is_active',
        'date_debut',
        'date_fin',
        'created_at',
        'updated_at',
        'motif'
    ];

    public $timestamps = true;

    public function acteur()
    {
        return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur');
    }

    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }
}
