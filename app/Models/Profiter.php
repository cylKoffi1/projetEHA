<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profiter extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $table = 'profiter'; // Nom de la table
    protected $primaryKey = 'id';
    protected $fillable = [
        'code_projet',
        'code_pays',
        'code_rattachement'
    ];
    public function localite()
    {
        return $this->belongsTo(LocalitesPays::class, 'code_rattachement', 'code_rattachement')
        ->where('id_pays', session('pays_selectionne'));
    }
    

}
