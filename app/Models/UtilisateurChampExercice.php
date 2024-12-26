<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtilisateurChampExercice extends Model
{
    use HasFactory;

    protected $table = 'utilisateur_champ_exercice';
    protected $fillable = ['utilisateur_code', 'champ_exercice_id'];

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_code');
    }

    public function champ()
    {
        return $this->belongsTo(DecoupageAdminPays::class, 'champ_exercice_id', 'num_niveau_decoupage');
    }
}
