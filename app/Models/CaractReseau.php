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

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'CodeCaractFamille');
    }
}
