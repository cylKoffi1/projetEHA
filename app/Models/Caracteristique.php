<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caracteristique extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'caracteristique';
    protected $primaryKey = 'code';

    protected $fillable = ['CodeProjet', 'Ordre', 'codeInfrastructure', 'codeFamille','CodeCaractFamille'];

    public function caractUniteTraitement()
    {
        return $this->hasOne(CaractUniteTraitement::class, 'CodeCaractFamille');
    }

    public function caractReservoir()
    {
        return $this->hasOne(CaractReservoir::class, 'CodeCaractFamille');
    }

    public function caractReseauCollectTransport()
    {
        return $this->hasOne(CaractReseauCollect::class, 'CodeCaractFamille');
    }

    public function caractOuvrageCaptage()
    {
        return $this->hasOne(CaractOuvrageCaptage::class, 'CodeCaractFamille');
    }

    public function caractOuvrageCaptageEau()
    {
        return $this->hasOne(CaractOuvrageCaptageEau::class, 'CodeCaractFamille');
    }

    public function caractOuvrageAssainissement()
    {
        return $this->hasOne(CaractOuvrageAssainiss::class, 'CodeCaractFamille');
    }

    public function caractOuvrage()
    {
        return $this->hasOne(CaractOuvrage::class, 'CodeCaractFamille');
    }

    public function caractInstrumentation()
    {
        return $this->hasOne(CaractInstrumentation::class, 'CodeCaractFamille');
    }
}
