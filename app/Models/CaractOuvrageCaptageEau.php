<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaractOuvrageCaptageEau extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'caractouvragecaptageeau';
    protected $primaryKey = 'CodeCaractFamille';

    protected $fillable = [
        'CodeCaractFamille',
        'typeCaptage',
        'debitCapacite',
        'profondeur',
        'natureTravaux'
    ];

    public function typeCaptage(){
        return $this->belongsTo(typeCaptage::class, 'typeCaptage', 'code');
    }

    public function natureTravaux(){
        return $this->belongsTo(NatureTravaux::class, 'natureTravaux', 'code');
    }           

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'CodeCaractFamille');
    }
}
