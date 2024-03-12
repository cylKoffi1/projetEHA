<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApartenirGroupeUtilisateur extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'appartenir_groupe_utilisateur'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $fillable = ['code_personnel', 'id', 'code_groupe_utilisateur', 'date'];


    public function groupeUtilisateur()
    {
        return $this->belongsTo(Role::class, 'code_groupe_utilisateur', 'id');
    }

}
