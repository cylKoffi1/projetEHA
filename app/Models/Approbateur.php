<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approbateur extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'approbateur';
    protected $primaryKey = 'codeAppro';
    protected $fillable = ['code_acteur', 'numOrdre', 'groupeProjetId', 'codePays'];

    public function Acteur()
    {
        return $this->belongsTo(Acteur::class, 'code_acteur', 'code_acteur');
    }

    public function GroupeProjet(){
        return $this->belongsTo(GroupeProjet::class, 'groupeProjetId', 'code')
        ->where('code', session('projet_selectionne'));
    }
      
}
