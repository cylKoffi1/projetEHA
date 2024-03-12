<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NiveauAvancement extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table ='niveau_avancement';
    protected $primaryKey ='code';
    protected $fillable = [
        'code_projet', // Assurez-vous que cette ligne est présente
        'numero_ordre',
        'qt_realisee',
        'niveaux',
        'date_realisation',
        'commentaire',
       
    ];
}
