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

    protected $fillable = ['code', 'libelle', 'icon', 'icon_color'];

    /**
     * Récupère les utilisateurs associés à ce groupe projet.
     */
    public function utilisateurs()
    {
        return $this->hasMany(GroupeProjetPaysUser::class, 'groupe_projet_id', 'code');
    }

    /**
     * Récupère les pays associés à ce groupe projet.
     */
    public function pays()
    {
        return $this->utilisateurs()->distinct('pays_code')->pluck('pays_code');
    }
    /**
     * Relation avec la table `domaine`.
     */
    public function domaine()
    {
        return $this->hasMany(Domaine::class, 'groupe_projet_code', 'code');
    }

}
