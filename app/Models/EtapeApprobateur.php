<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtapeApprobateur extends Model
{
    use HasFactory;

    protected $table = 'etape_approbateurs';
    protected $fillable = [
        'etape_workflow_id','type_approbateur','reference_approbateur','obligatoire'
    ];

    public function etape()
    {
        return $this->belongsTo(EtapeWorkflow::class,'etape_workflow_id');
    }
}
