<?php
// app/Models/EtudeProjet.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtudeProjet extends Model
{
    protected $table = 'etude_projets';
    protected $primaryKey = 'code_projet_etude';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code_projet_etude',
        'groupe_projet_code',
        'intitule',
        'description',
        'code_pays',
        'code_domaine',
        'code_sous_domaine',
        'date_debut_previsionnel',
        'date_fin_previsionnel',
        'code_devise',
        'montant_budget_previsionnel',
        'objectif_general',
        'type_etude_code',        
        'livrables_commentaires'  
    ];

    public function livrables()
    {
        return $this->belongsToMany(
            EtudeLivrable::class,
            'etude_projet_livrables',
            'code_projet_etude',  // clé de CE modèle dans le pivot
            'livrable_id',        // clé de l’autre modèle dans le pivot
            'code_projet_etude',
            'id'
        )->withTimestamps();
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
        return $this->hasMany(Executer::class, 'code_projet', 'code_projet_etude');
    }
    
    public function maitreOuvrage() {
        return $this->hasOne(Posseder::class, 'code_projet', 'code_projet_etude');
    }
    public function ChefProjet(){
        return $this->belongsTo(Controler::class, 'code_projet', 'code_projet_etude');
    }
    
    public function documents() {
        return $this->hasMany(ProjetDocument::class, 'code_projet', 'code_projet_etude');
    }
    public function statuts()
    {
        return $this->hasOne(ProjetStatut::class, 'code_projet', 'code_projet_etude')->latest('date_statut');
    }
    public function dernierStatut()
    {
        return $this->hasOne(ProjetStatut::class, 'code_projet', 'code_projet_etude')
                    ->latestOfMany('date_statut');
    } 
}
