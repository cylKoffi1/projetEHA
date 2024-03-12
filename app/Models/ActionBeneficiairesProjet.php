<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionBeneficiairesProjet extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'action_beneficiaires_projet'; // Nom de la table
    protected $primaryKey = 'code';
    protected $fillable = [ 'CodeProjet', 'numOrdre', 'beneficiaire_id', 'type_beneficiaire'];
}
