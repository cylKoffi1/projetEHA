<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscaladeApprobation extends Model
{
    use HasFactory;

    protected $table = 'escalades_approbation';
    public $timestamps = false;
    protected $fillable = [
        'instance_etape_id','acteur_source','acteur_cible','raison','created_at'
    ];

    public function etapeInstance()
    {
        return $this->belongsTo(InstanceEtape::class,'instance_etape_id');
    }
}
