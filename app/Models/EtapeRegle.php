<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapeRegle extends Model
{
    use HasFactory;

    protected $table = 'etape_regles';
    protected $fillable = [
        'etape_workflow_id','champ','operateur_id','valeur'
    ];
    protected $casts = [
        'valeur' => 'array', // important: tes contrôleurs lisent ça comme array
      ];
    public function etape()
    {
        return $this->belongsTo(EtapeWorkflow::class,'etape_workflow_id');
    }

    public function operateur()
    {
        return $this->belongsTo(OperateurRegle::class,'operateur_id');
    }

}
