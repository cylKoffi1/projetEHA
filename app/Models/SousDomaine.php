<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SousDomaine extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'sous_domaine'; 
    protected $primaryKey = 'id';
    protected $fillable = ['code_sous_domaine', 'lib_sous_domaine', 'code_domaine', 'code_groupe_projet', 'type'];

    public function Domaine()
    {
        return $this->belongsTo(Domaine::class, 'domaine_intervention_code', 'code');
    }

}
