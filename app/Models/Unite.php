<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unite extends Model
{
    use HasFactory;

    protected $table = 'unites';
    protected $primaryKey = 'idUnite';
    public $timestamps = false;

    protected $fillable = [
        'libelleUnite',
        'symbole', // Ajoutez cette ligne
        'idCaracteristique',
    ];

    // Relation avec la caractéristique
    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'idCaracteristique');
    }

    // Relation avec les valeurs de caractéristiques
    public function valeursCaracteristiques()
    {
        return $this->hasMany(ValeurCaracteristique::class, 'idUnite');
    }
}