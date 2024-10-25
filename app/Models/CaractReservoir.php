<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaractReservoir extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'caractreservoir';
    protected $primaryKey = 'CodeCaractFamille';

    protected $fillable = [
        'CodeCaractFamille',
        'typeReservoir',
        'materiaux',
        'capacite',
        'natureTraveaux'
    ];
    public function typeCaptage()
    {
        return $this->belongsTo(typeCaptage::class, 'typeReservoir', 'code');
    }

    public function natureTravaux()
    {
        return $this->belongsTo(NatureTravaux::class, 'natureTraveaux', 'code');
    }
    
    public function materielStockage(){
        return $this->belongsTo(MaterielStockage::class, 'materiaux', 'code');
    }

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'CodeCaractFamille');
    }

}
