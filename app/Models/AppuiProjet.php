<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppuiProjet extends Model
{
    use HasFactory;

    protected $table = 'appui_projets';
    protected $primaryKey = 'code_projet_appui';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code_projet_appui',
        'groupe_projet_code',
        'intitule',
        'description',
        'code_pays',
        'code_domaine',
        'code_sous_domaine',
        'date_debut_previsionnel',
        'date_fin_previsionnel',
        'montant_budget_previsionnel',
        'code_devise'
    ];



    // Many-to-many vers les projets soutenus
    public function projets()
    {
        return $this->belongsToMany(
            Projet::class,
            'projet_appui_projet',           // table pivot
            'code_projet_appui',             // clé FK côté appui
            'code_projet'                    // clé FK côté projet
        )->withPivot(['commentaire'])->withTimestamps();
    }

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'code_pays', 'alpha3');
    }
    public function localisations() {
        return $this->hasMany(ProjetLocalisation::class, 'code_projet', 'code_projet_appui');
    }
    

    public function sousDomaineSansSession()
    {
        // sans filtrage sur le groupe pour récupérer les libellés à coup sûr
        return $this->belongsTo(SousDomaine::class, 'code_sous_domaine', 'code_sous_domaine');
    }
    public function devise(){
        return $this->belongsTo(Devise::class, 'code_devise', 'code_long');
    }
    public function sousDomaine()
    {
        return $this->belongsTo(SousDomaine::class, 'code_sous_domaine', 'code_sous_domaine')
        ->where('code_groupe_projet', session('projet_selectionne'));
    }
    public function typeEtude() {
        return $this->hasMany(EtudeType::class, 'type_etude_code', 'code');
    }
    public function maitresOeuvre() {
        return $this->hasMany(Executer::class, 'code_projet', 'code_projet_appui');
    }
    
    public function maitreOuvrage() {
        return $this->hasOne(Posseder::class, 'code_projet', 'code_projet_appui');
    }
    public function ChefProjet(){
        return $this->belongsTo(Controler::class, 'code_projet', 'code_projet_appui');
    }
    
    public function documents() {
        return $this->hasMany(ProjetDocument::class, 'code_projet', 'code_projet_appui');
    }
    public function statuts()
    {
        return $this->hasOne(ProjetStatut::class, 'code_projet', 'code_projet_appui')->latest('date_statut');
    }
    public function dernierStatut()
    {
        return $this->hasOne(ProjetStatut::class, 'code_projet', 'code_projet_appui')
                    ->latestOfMany('date_statut');
    }
}
