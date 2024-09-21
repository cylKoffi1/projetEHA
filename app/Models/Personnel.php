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
        'domaine_activite',
        'photo',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'code_personnel', 'code_personnel');
    }
    public function lastStructure()
    {
        return $this->hasOne(StructureRattachement::class, 'code_personnel', 'code_personnel')
                    ->latest('date', 'desc');
    }
    public function approbateur(){
        return $this->belongsTo(Approbateur::class, 'code_personnel','code_personnel');
    }
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

    public function renforcements()
    {
        return $this->belongsToMany(Renforcement::class, 'renforcement_beneficiaire', 'code_personnel', 'renforcement_capacite');
    }
}
