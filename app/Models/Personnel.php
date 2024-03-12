<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'personnel'; // Nom de la table   
    protected $keyType = 'string';
    protected $primaryKey = 'code_personnel';
    protected $fillable = [
        'code_personnel',
        'nom',
        'prenom',
        'addresse',
        'telephone',
        'email',
        'code_structure_bailleur',
        'code_structure_agence',
        'code_structure_ministere',
        'domaine_activite',
        'photo',
    ];


    public function latestFonction()
    {
        return $this->hasOne(OccuperFonction::class, 'code_personnel', 'code_personnel')
            ->latest('date')
            ->orderBy('date', 'desc');
    }

    public function latestRegion()
    {
        return $this->hasOne(CouvrirRegion::class, 'code_personnel', 'code_personnel')
            ->latest('date')
            ->orderBy('date', 'desc');
    }
    public function bailleur()
    {
        return $this->belongsTo(Bailleur::class, 'code_structure_bailleur', 'code_bailleur');
    }

    public function agence()
    {
        return $this->belongsTo(AgenceExecution::class, 'code_structure_agence', 'code_agence_execution');
    }

    public function ministere()
    {
        return $this->belongsTo(Ministere::class, 'code_structure_ministere', 'code');
    }
    public function domaine()
    {
        return $this->belongsTo(Ministere::class, 'domaine_activite', 'code');
    }
}
