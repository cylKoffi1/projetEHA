<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdreService extends Model
{
    use HasFactory;
    protected $table = 'ordres_service';
    protected $fillable = ['contrat_id','numero','date_os','description'];
    protected $casts = ['date_os'=>'date'];
    public function contrat() { return $this->belongsTo(ContratPrestataire::class,'contrat_id'); }

}
