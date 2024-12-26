<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilisateurLieuExercice extends Model
{
    use HasFactory;

    protected $table = 'utilisateur_lieu_exercice';
    protected $fillable = ['utilisateur_code', 'lieu_exercice_id'];

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_code');
    }

    public function lieu()
    {
        return $this->belongsTo(LocalitesPays::class, 'lieu_exercice_id', 'id_pays');
    }
}
