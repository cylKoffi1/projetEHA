<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class RoleHasRubrique extends Model
{
    // Nom de la table associée au modèle
    protected $table = 'roles_has_rubriques';

    // Clés primaires personnalisées
    protected $primaryKey = ['rubrique_id', 'role_id'];

    // Indique si les clés primaires sont auto-incrémentées
    public $incrementing = false;

    // Indique si les colonnes de timestamp (created_at et updated_at) doivent être gérées par Eloquent
    public $timestamps = true; // Mettre à true pour activer la gestion automatique des timestamps

    // Définition des relations
    public function rubrique()
    {
        return $this->belongsTo(Rubriques::class, 'rubrique_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
