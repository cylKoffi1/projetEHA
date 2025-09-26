<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class Fonction_groupe_utilisateur extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'fonction_groupe_utilisateur'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';



    protected $fillable = [
        'code_fonction',
        'code_groupe_utilisateur',
    ];
    public function groupeUtilisateur()
    {
        return $this->belongsTo(Role::class, 'code_groupe_utilisateur', 'code');
    }

    public function fonction()
    {
        return $this->belongsTo(FonctionUtilisateur::class, 'code_fonction', 'code');
    }
}
