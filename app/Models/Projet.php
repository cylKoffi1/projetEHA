<?php
// app/Models/Projet.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projet extends Model
{
    // Nom de la table dans la base de données
    protected $table = 'projet_eha2';
    protected $primaryKey ='CodeProjet';

    // Liste des colonnes que vous souhaitez remplir (modifiable selon vos besoins)
    protected $fillable = [
        'CodeProjet',
        'code_domaine',
        'code_sous_domaine',
        'code_region',
        'Date_demarrage_prevue',
        'date_fin_prevue',
        'cout_projet',
        'code_devise',
        'code_district'
    ];


    public function Domaine()
    {
        return $this->belongsTo(Domaine::class, 'Domaine', 'code');
    }

    public function SousDomaine()
    {
        return $this->belongsTo(SousDomaine::class, 'Sous_domaine', 'code');
    }
}
