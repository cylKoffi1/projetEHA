<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaractInstrumentation extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'caractinstrumentation';
    protected $primaryKey = 'CodeCaractFamille';
    protected $fillable = [
        'CodeCaractFamille',
        'typeInstrument',
        'nombre',
        'natureTraveaux'
    ];

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'CodeCaractFamille');
    }
}
