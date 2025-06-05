<?php

namespace App\Models;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projet extends Model
{
    use HasFactory;

    protected $table = 'projets';
    protected $primaryKey = 'code_projet'; 
    protected $keyType = 'string';
    
    // Colonnes modifiables
    protected $fillable = [
        'code_projet',
        'code_alpha3_pays',
        'libelle_projet',
        'commentaire',
        'code_sous_domaine',
        'date_demarrage_prevue',
        'date_fin_prevue',
        'cout_projet',
        'code_devise',
        'created_at',
        'updated_at',
    ];

    public function sousDomaine()
    {
        return $this->belongsTo(SousDomaine::class, 'code_sous_domaine', 'code_sous_domaine')
        ->where('code_groupe_projet', session('projet_selectionne'));
    }

    public function etude(){
        return $this->belongsTo(EtudeProject::class, 'code_projet', 'code_projet');
    }
    public function projetNaturesTravaux() {
       return $this->hasOne(projets_natureTravaux::class, 'code_projet', 'code_projet')->latest('date');
    }
    

    public function pays()
    {
        return $this->belongsTo(Pays::class, 'code_alpha3_pays', 'alpha3');
    }

    public function financements()
    {
        return $this->hasMany(Financer::class, 'code_projet', 'code_projet');
    }
    
    public function statuts()
    {
        return $this->hasOne(ProjetStatut::class, 'code_projet', 'code_projet')->latest('date_statut');
    }
    
    public function dateEffective()
    {
        return $this->hasOne(DateEffectiveProjet::class, 'code_projet', 'code_projet');
    }
    public function localisations() {
        return $this->hasMany(ProjetLocalisation::class, 'code_projet');
    }
    
    public function infrastructures() {
        return $this->hasMany(ProjetInfrastructure::class, 'code_projet');
    }
    
    public function actions() {
        return $this->hasMany(ProjetActionAMener::class, 'code_projet');
    }
    
    public function maitresOeuvre() {
        return $this->hasMany(Executer::class, 'code_projet', 'code_projet');
    }
    
    public function maitreOuvrage() {
        return $this->hasOne(Posseder::class, 'code_projet', 'code_projet')->where('is_active', true);
    }
    
    public function ChefProjet(){
        return $this->belongsTo(Controler::class, 'code_projet', 'code_projet');
    }
    
    public function documents() {
        return $this->hasMany(ProjetDocument::class, 'code_projet');
    }

    public function devise(){
        return $this->belongsTo(Devise::class, 'code_devise', 'code_long');
    }
    public function dernierStatut()
    {
        return $this->hasOne(ProjetStatut::class, 'code_projet', 'code_projet')
                    ->latest('date_statut');
    }

}
