<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class caractlatrinefamillial extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'caractlatrinefamillial';
    protected $primaryKey = 'CodeCaractFamille';

    protected $fillable = [
        'CodeCaractFamille',
        'nombre',
        'natureTraveaux'
    ];

    public function caracteristique()
    {
        return $this->belongsTo(Caracteristique::class, 'CodeCaractFamille');
    }
}
