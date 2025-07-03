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

    protected $fillable = ['libelleUnite', 'symbole'];

    public function caracteristiques()
    {
        return $this->hasMany(Caracteristique::class, 'idUnite');
    }

    public function valeurs()
    {
        return $this->hasMany(ValeurCaracteristique::class, 'idUnite');
    }
}
