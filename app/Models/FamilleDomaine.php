<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilleDomaine extends Model
{
    use HasFactory;

    protected $table = 'famille_domaine';

    protected $fillable = [
        'code_Ssys',
        'code_domaine',
        'code_sdomaine',
        'code_groupe_projet',
    ];

    public function famille()
    {
        return $this->belongsTo(FamilleInfrastructure::class, 'code_Ssys', 'code_Ssys');
    }

    public function groupeProjet(){
        return $this->belongsTo(GroupeProjet::class, 'code_groupe_projet', 'code');
    }
    public function domaine()
    {
        return $this->belongsTo(Domaine::class, 'code_domaine', 'code')
            ->where('groupe_projet_code', $this->code_groupe_projet);
    }   
     public function sousdomaine()
    {
        return $this->belongsTo(SousDomaine::class, 'code_sdomaine', 'code_sous_domaine')
            ->where('code_groupe_projet', $this->code_groupe_projet);
    }
}
