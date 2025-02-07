<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntreprisePhysique extends Model
{
    use HasFactory;
    protected $table = 'entreprisephysique';
    protected $primaryKey = 'id';

    protected $fillable = [
        'code_acteur', 'nom', 'prenom', 'date_naissance', 'nationalite', 'secteur_activite',
        'email', 'Code_Postal', 'AdressePostale', 'AdresseSiege', 'TelephoneBureau', 'TelephoneMobile',
        'num_cni', 'DateValidite', 'num_fiscal', 'genreId', 'situationMatrimonialeId',
        'IsActive', 'created_at', 'updated_at'
    ];
    protected $dates = ['created_at', 'updated_at'];
}
