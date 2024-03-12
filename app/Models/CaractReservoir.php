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

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'CodeCaractFamille');
    }
}
