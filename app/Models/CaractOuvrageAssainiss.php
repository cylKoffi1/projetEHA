<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaractOuvrageAssainiss extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'caractouvrageassainiss';
    protected $primaryKey = 'CodeCaractFamille';

    protected $fillable = [
        'CodeCaractFamille',
        'typeOuvrage',
        'capaciteVolume',
        'natureTraveaux'
    ];
    public function typeOuvrage(){
        return $this->belongsTo(Infrastructure::class, 'typeOuvrage', 'code');
    }
    public function natureTravaux(){
        return $this->belongsTo(NatureTravaux::class, 'natureTraveaux', 'code');
    }
    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'CodeCaractFamille');
    }
}
