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
    public $incrementing = false;

    protected $fillable = ['code', 'libelle_groupe'];

    // Relation avec les utilisateurs
    public function utilisateurs()
    {
        return $this->hasMany(User::class, 'groupe_utilisateur_id', 'code');
    }


}
