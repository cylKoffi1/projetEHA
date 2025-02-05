<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupeProjetPaysUser extends Model
{
    protected $table = 'groupe_projet_pays_user';

    protected $fillable = ['user_id', 'pays_code', 'groupe_projet_id'];

    /**
     * Relation avec la table `utilisateurs`.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'acteur_id');
    }

    /**
     * Relation avec la table `pays`.
     */
    public function pays()
    {
        return $this->belongsTo(Pays::class, 'pays_code', 'alpha3');
    }


    /**
     * Relation avec la table `groupe_projet`.
     */
    public function groupeProjet()
    {
        return $this->belongsTo(GroupeProjet::class, 'groupe_projet_id', 'code');
    }
}
