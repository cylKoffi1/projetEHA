<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Renforcement extends Model
{
    protected $table = 'renforcement_capacites';
      // Définition de la clé primaire
      protected $primaryKey = 'code_renforcement';
      public $incrementing = false; // Désactive l'auto-incrémentation de la clé primaire car elle est de type string
      protected $keyType = 'string'; // Le type de la clé est une chaîne

      // Définir les colonnes pouvant être massivement assignées
      protected $fillable = ['code_renforcement', 'titre', 'description', 'date_renforcement'];

      // Relation avec la table 'beneficiaires'
      public function beneficiaires()
      {
          return $this->belongsToMany(MotDePasseUtilisateur::class, 'renforcement_beneficiaire', 'renforcement_capacite', 'beneficiaire_id');
      }

      // Relation avec la table 'projet_eha'
      public function projets()
      {
          return $this->belongsToMany(ProjetEha2::class, 'renforcement_projet', 'renforcement_capacite', 'CodeProjet');
      }
}
