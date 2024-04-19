<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class BailleursProjet extends Model
{
    protected $table = 'bailleurs_projets';
    protected $primaryKey = 'id';
    public $timestamps = false; // Si vos tables n'ont pas de colonnes
    protected $fillable = [ 'code_bailleur', 'code_projet', 'code_devise', 'montant','commentaire','partie','type_financement','Num_ordre'];
    public function bailleurss()
    {
        return $this->hasMany(Bailleur::class, 'code_bailleur', 'code_bailleur');
    }
}
