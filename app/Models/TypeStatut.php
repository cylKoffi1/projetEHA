<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeStatut extends Model
{

    use HasFactory;
    public $timestamps = true;

    protected $table = 'type_statut'; // Nom de la table
    
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'libelle', 'description'];
}


