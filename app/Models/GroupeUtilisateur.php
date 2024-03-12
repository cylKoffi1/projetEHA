<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class GroupeUtilisateur extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'groupe_utilisateur'; // Nom de la table   
    protected $keyType = 'string';
    protected $primaryKey = 'code';
}
