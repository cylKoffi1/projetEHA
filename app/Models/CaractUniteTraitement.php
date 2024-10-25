<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaractUniteTraitement extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'caractunitetraitement';
    protected $primaryKey = 'CodeCaractFamille';

    protected $fillable = [
        'CodeCaractFamille',
        'typeUnite',
        'debitCapacite',
        'natureTravaux'
    ];
    public function uniteTraitement()
    {
        return $this->belongsTo(UniteTraitement::class, 'typeUnite', 'code');
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
