<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unite extends Model
{
    protected $table = 'unites';
    protected $primaryKey = 'idUnite';
    public $timestamps = false;

    protected $fillable = ['libelleUnite', 'symbole','idCaracteristique'];

    public function caracteristiques()
    {
        return $this->hasMany(Caracteristique::class, 'idCaracteristique', 'idCaracteristique');
    }

    public function valeurs()
    {
        return $this->hasMany(ValeurCaracteristique::class, 'idUnite');
    }
}

