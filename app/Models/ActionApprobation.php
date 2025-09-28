<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionApprobation extends Model
{
    use HasFactory;

    protected $table = 'actions_approbation';
    protected $fillable = [
        'instance_etape_id','code_acteur','action_type_id','commentaire','meta'
    ];
    protected $casts = [
        'meta' => 'array',
      ];
    public function etapeInstance()
    {
        return $this->belongsTo(InstanceEtape::class,'instance_etape_id');
    }

    public function type()
    {
        return $this->belongsTo(TypeAction::class,'action_type_id');
    }
}
