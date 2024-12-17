<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupeProjet extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'groupe_projet'; // Nom de la table
    protected $keyType = 'string';
    protected $primaryKey = 'code';
    public $incrementing = false;

    protected $fillable = ['code', 'libelle'];

    public function utilisateurs()
    {
        return $this->hasMany(Users::class, 'groupe_projet_id');
    }
}
