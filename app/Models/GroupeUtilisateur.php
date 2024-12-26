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

    // Relation pour les groupes qu'un groupe parent peut gérer
    public function groupesEnfants()
    {
        return $this->belongsToMany(
            GroupeUtilisateur::class,
            'role_permissions', // Table pivot
            'role_source',       // Colonne source
            'role_target'        // Colonne cible
        );
    }

    // Relation inverse pour récupérer le groupe parent
    public function groupesParents()
    {
        return $this->belongsToMany(
            GroupeUtilisateur::class,
            'role_permissions',
            'role_target',
            'role_source'
        );
    }
}
