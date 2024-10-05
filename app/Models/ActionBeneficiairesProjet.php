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
    protected $fillable = ['CodeProjet', 'numOrdre', 'beneficiaire_id', 'type_beneficiaire'];

    // Relations conditionnelles selon le type de bénéficiaire
    public function district()
    {
        return $this->belongsTo(District::class, 'beneficiaire_id');
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'beneficiaire_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'beneficiaire_id');
    }

    public function sousPrefecture()
    {
        return $this->belongsTo(Sous_prefecture::class, 'beneficiaire_id');
    }

    public function localite()
    {
        return $this->belongsTo(Localite::class, 'beneficiaire_id');
    }

    public function etablissement()
    {
        return $this->belongsTo(Etablissement::class, 'beneficiaire_id');
    }
}
