<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaractReseau extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'caractreseau';
    protected $primaryKey = 'CodeCaractFamille';

    protected $fillable = [
        'CodeCaractFamille',
        'typeTransport',
        'materiaux',
        'Diametre',
        'lineaire',
        'natureTravaux'
    ];

    public function ouvrageTransport(){
        return $this->belongsTo(OuvrageTransport::class, 'typeTransport', 'code');
    }

    public function materielStockage(){
        return $this->belongsTo(MaterielStockage::class, 'materiaux', 'code');
    }

    public function natureTravaux()
    {
        return $this->belongsTo(NatureTravaux::class, 'natureTravaux', 'code');
    }

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'CodeCaractFamille');
    }
}
