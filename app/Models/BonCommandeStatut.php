<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonCommandeStatut extends Model {
    protected $table = 'bon_commande_statuts';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];
}