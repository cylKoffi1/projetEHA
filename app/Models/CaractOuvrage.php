<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaractOuvrage extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'caractouvrage';
    protected $primaryKey = 'CodeCaractFamille';

    protected $fillable = [
        'CodeCaractFamille',
        'typeOuvrage',
        'nombre',
        'natureTraveaux'
    ];

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'CodeCaractFamille');
    }
}
