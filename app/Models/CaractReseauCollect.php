<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaractReseauCollect extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'caractreseaucollecttransport';
    protected $primaryKey = 'CodeCaractFamille';

    protected $fillable = [
        'CodeCaractFamille',
        'typeOuvrage',
        'typeReseau',
        'classe',
        'lineaire',
        'natureTraveaux'
    ];

    // Méthode pour récupérer les ouvrages par domaine
    public function typeOuvrage()
    {
        return $this->belongsTo(Infrastructure::class, 'typeOuvrage', 'code');
    }

    public function typeReseaux(){
        return $this->belongsTo(TypeResaux::class, 'typeReseau', 'code');
    }

    public function natureTravaux()
    {
        return $this->belongsTo(NatureTravaux::class, 'natureTraveaux', 'code');
    }

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'CodeCaractFamille');
    }
}
