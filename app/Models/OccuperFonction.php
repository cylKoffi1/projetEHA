<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OccuperFonction extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'occuper_fonction'; // Nom de la table   
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    protected $fillable = ['code_personnel', 'id', 'code_fonction', 'date'];

    public function fonctionUtilisateur()
    {
        return $this->belongsTo(FonctionUtilisateur::class, 'code_fonction', 'code');
    }
}
