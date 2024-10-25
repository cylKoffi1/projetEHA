<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaractLatrinePublique extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'caractlatrinepublique';
    protected $primaryKey = 'CodeCaractFamille';

    protected $fillable = [
        'CodeCaractFamille',
        'nombre',
        'natureTraveaux'
    ];

    public function natureTravaux(){
        return $this->belongsTo(NatureTravaux::class, 'natureTraveaux', 'code');
    }

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'CodeCaractFamille');
    }
}
