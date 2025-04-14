<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Legendecarte extends Model
{
    protected $table = 'legendecarte';

    protected $fillable = ['groupe_projet', 'label', 'typeFin'];

    public function seuils()
    {
        return $this->hasMany(Legende::class, 'idLegendeCarte');
    }
}

