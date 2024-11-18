<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalitesPays extends Model
{
    use HasFactory;

    protected $table = 'localites_pays';
    protected $fillable = ['id_pays', 'id_niveau', 'libelle', 'code_rattachement', 'code_decoupage'];

    public function decoupage()
    {
        return $this->belongsTo(DecoupageAdministratif::class, 'code_decoupage', 'code_decoupage');
    }
}
