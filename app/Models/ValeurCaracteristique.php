<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValeurCaracteristique extends Model
{
    use HasFactory;

    protected $table = 'valeurcaracteristique';
    protected $primaryKey = 'idValeurCaracteristique';
    public $timestamps = false;

    protected $fillable = [
        'idInfrastructure',
        'idCaracteristique',
        'idUnite',
        'valeur',
    ];

    // Relation avec l'infrastructure
    public function infrastructure()
    {
        return $this->belongsTo(Infrastructure::class, 'idInfrastructure');
    }

    // Relation avec la caractéristique
    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'idCaracteristique');
    }

    // Relation avec l'unité
    public function unite()
    {
        return $this->belongsTo(Unite::class, 'idUnite');
    }
}