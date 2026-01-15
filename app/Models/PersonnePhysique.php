<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonnePhysique extends Model
{
    use HasFactory;

    protected $table = 'personne_physique';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;
    protected $fillable = [
        'code_acteur', 'nom', 'prenom', 'date_naissance', 'nationalite',
        'email', 'code_postal', 'adresse_postale', 'adresse_siege',
        'telephone_bureau', 'telephone_mobile', 'date_validite',
        'num_fiscal', 'genre_id', 'situation_matrimoniale_id', 'is_active'
    ];

    public function acteur()
    {
        return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur');
    }

    public function genre()
    {
        return $this->belongsTo(Genre::class, 'genre_id');
    }

    public function situationMatrimoniale()
    {
        return $this->belongsTo(SituationMatrimonial::class, 'situation_matrimoniale_id');
    }
}
