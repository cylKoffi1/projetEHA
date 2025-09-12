<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ProjetLocalisation extends Model
{
    protected $table = 'projetlocalisation';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'code_projet',
        'pays_code',
        'code_localite',
        'niveau',
        'decoupage',
    ];

    // Exemple de relation (si Projet existe)
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'code_projet', 'code_projet');
    }
    public function localite()
    {
        return $this->hasOne(LocalitesPays::class, 'code_rattachement', 'code_localite')
                    ->where('id_pays', session('pays_selectionne'));
    }
    public function pays(){
        return $this->belongsTo(Pays::class,  'pays_code', 'alpha3');
    }
    public function decoupageLibelle(){
        return $this->belongsTo(DecoupageAdministratif::class,  'decoupage', 'code_decoupage');
    }
}