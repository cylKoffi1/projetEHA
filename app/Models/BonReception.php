<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonReception extends Model
{
    use HasFactory;
    protected $table = 'bons_reception';
    protected $fillable = ['bc_id','date_reception','reference','statut_id'];
    protected $casts = ['date_reception'=>'date'];
    public function bc()     { return $this->belongsTo(BonCommande::class,'bc_id'); }
    public function statut() { return $this->belongsTo(ReceptionStatut::class,'statut_id'); }
    public function lignes() { return $this->hasMany(BrLigne::class,'br_id'); }

}
