<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceptionStatut extends Model {
    protected $table = 'reception_statuts';
    public $timestamps = false;
    protected $fillable = ['code','libelle'];
}